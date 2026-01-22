<?php
/**
 * Динамическая генерация XML для Avito
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Применяет пользовательские правила замены символов к тексту
 *
 * @param string $text Исходный текст
 * @return string Текст с применёнными заменами
 */
function wc_avito_apply_character_replacements($text) {
    if (empty($text) || !is_string($text)) {
        return $text;
    }
    
    $replacements = get_option('wc_avito_character_replacements', array());
    
    if (empty($replacements) || !is_array($replacements)) {
        return $text;
    }
    
    foreach ($replacements as $rule) {
        $search = isset($rule['search']) ? $rule['search'] : '';
        $replace = isset($rule['replace']) ? $rule['replace'] : '';
        
        if (!empty($search)) {
            $text = str_replace($search, $replace, $text);
        }
    }
    
    return $text;
}

/**
 * Динамическое добавление полей в объявление
 */
function wc_avito_add_dynamic_fields($ad, $product, $category_id) {
    $settings = wc_avito_get_field_settings();
    
    // Проверяем, есть ли шаблон Title у категории
    $category_title_template = '';
    if ($category_id) {
        $category_title_template = get_term_meta($category_id, 'avito_title_template', true);
    }
    
    // Если у категории есть шаблон Title, добавляем его в первую очередь
    if (!empty($category_title_template)) {
        $value = wc_avito_process_placeholders($category_title_template, $product, $category_id);
        // Применяем замену символов к Title
        $value = wc_avito_apply_character_replacements($value);
        if (!empty($value)) {
            // Если значение содержит HTML теги, используем CDATA
            if (preg_match('/<[^>]+>/', $value)) {
                $node = $ad->addChild('Title');
                $cdata = dom_import_simplexml($node);
                $cdata->appendChild($cdata->ownerDocument->createCDATASection($value));
            } else {
                $ad->addChild('Title', htmlspecialchars($value));
            }
        }
    }
    
    // Добавляем глобальные поля
    if (!empty($settings['global_fields'])) {
        foreach ($settings['global_fields'] as $field) {
            if (empty($field['enabled'])) continue;

            // Получаем XML тег
            $xml_tag = !empty($field['xml_tag']) ? $field['xml_tag'] : (!empty($field['label']) ? $field['label'] : $field['key']);

            // Пропускаем Title, если он уже был добавлен из шаблона категории
            if ($xml_tag === 'Title' && !empty($category_title_template)) {
                continue;
            }

            // Проверяем условие, если оно задано
            if (!empty($field['condition_placeholder'])) {
                $condition_value = wc_avito_process_placeholders($field['condition_placeholder'], $product, $category_id);
                // Если условие не выполнено (плейсхолдер вернул пустое значение), пропускаем поле
                if (empty($condition_value)) {
                    continue;
                }
            }

            // Значение берется из настроек поля
            $value = isset($field['value']) ? $field['value'] : '';

            // Обрабатываем плейсхолдеры
            $value = wc_avito_process_placeholders($value, $product, $category_id);

            // Применяем замену символов к Title и Description
            if ($xml_tag === 'Title' || $xml_tag === 'Description') {
                $value = wc_avito_apply_character_replacements($value);
            }

            if (!empty($value)) {
                // Если значение содержит HTML теги, используем CDATA
                if (preg_match('/<[^>]+>/', $value)) {
                    $node = $ad->addChild($xml_tag);
                    $cdata = dom_import_simplexml($node);
                    $cdata->appendChild($cdata->ownerDocument->createCDATASection($value));
                } else {
                    $ad->addChild($xml_tag, htmlspecialchars($value));
                }
            }
        }
    }
    
    // Добавляем поля категории (если есть)
    if ($category_id && !empty($settings['category_fields'])) {
        foreach ($settings['category_fields'] as $field) {
            if (empty($field['enabled'])) continue;
            
            $value = get_term_meta($category_id, $field['key'], true);
            
            // Если значение не задано у категории, используем общее значение
            if (empty($value) && isset($field['default_value']) && !empty($field['default_value'])) {
                $value = $field['default_value'];
            }
            
            // Обрабатываем плейсхолдеры
            $value = wc_avito_process_placeholders($value, $product, $category_id);
            
            if (!empty($value)) {
                // Используем xml_tag, если он задан, иначе label
                $xml_tag = !empty($field['xml_tag']) ? $field['xml_tag'] : (!empty($field['label']) ? $field['label'] : $field['key']);
                
                // Применяем замену символов к Description
                if ($xml_tag === 'Description' || $field['key'] === 'avito_description') {
                    $value = wc_avito_apply_character_replacements($value);
                }
                
                // Специальная обработка для Description - используем CDATA и prepare_description
                if ($xml_tag === 'Description' || $field['key'] === 'avito_description') {
                    $description_node = $ad->addChild($xml_tag);
                    $description_cdata = dom_import_simplexml($description_node);
                    $description_cdata->appendChild($description_cdata->ownerDocument->createCDATASection(prepare_description($value)));
                } elseif (preg_match('/<[^>]+>/', $value)) {
                    // Если значение содержит HTML теги, используем CDATA
                    $node = $ad->addChild($xml_tag);
                    $cdata = dom_import_simplexml($node);
                    $cdata->appendChild($cdata->ownerDocument->createCDATASection($value));
                } else {
                    $ad->addChild($xml_tag, htmlspecialchars($value));
                }
            }
        }
    }
    
    // Добавляем пользовательские поля категории (с учётом иерархии родительских категорий)
    if ($category_id) {
        $custom_fields = wc_avito_get_category_custom_fields_hierarchy($category_id);
        
        if (!empty($custom_fields) && is_array($custom_fields)) {
            foreach ($custom_fields as $field) {
                $xml_tag = isset($field['xml_tag']) ? $field['xml_tag'] : '';
                $value = isset($field['value']) ? $field['value'] : '';
                
                // Пропускаем пустые поля
                if (empty($xml_tag) || empty($value)) {
                    continue;
                }
                
                // Обрабатываем плейсхолдеры
                $value = wc_avito_process_placeholders($value, $product, $category_id);
                
                // Применяем замену символов к Description
                if ($xml_tag === 'Description') {
                    $value = wc_avito_apply_character_replacements($value);
                }
                
                if (!empty($value)) {
                    // Специальная обработка для Description - используем CDATA и prepare_description
                    if ($xml_tag === 'Description') {
                        $description_node = $ad->addChild($xml_tag);
                        $description_cdata = dom_import_simplexml($description_node);
                        $description_cdata->appendChild($description_cdata->ownerDocument->createCDATASection(prepare_description($value)));
                    } elseif (preg_match('/<[^>]+>/', $value)) {
                        // Если значение содержит HTML теги, используем CDATA
                        $node = $ad->addChild($xml_tag);
                        $cdata = dom_import_simplexml($node);
                        $cdata->appendChild($cdata->ownerDocument->createCDATASection($value));
                    } else {
                        $ad->addChild($xml_tag, htmlspecialchars($value));
                    }
                }
            }
        }
    }
    
    // Добавляем пользовательские поля товара (имеют приоритет над полями категории)
    if ($product) {
        $product_custom_fields = get_post_meta($product->get_id(), 'avito_product_custom_fields', true);
        
        if (!empty($product_custom_fields) && is_array($product_custom_fields)) {
            foreach ($product_custom_fields as $field) {
                $xml_tag = isset($field['xml_tag']) ? $field['xml_tag'] : '';
                $value = isset($field['value']) ? $field['value'] : '';
                
                // Пропускаем пустые поля
                if (empty($xml_tag) || empty($value)) {
                    continue;
                }
                
                // Обрабатываем плейсхолдеры
                $value = wc_avito_process_placeholders($value, $product, $category_id);
                
                // Применяем замену символов к Description
                if ($xml_tag === 'Description') {
                    $value = wc_avito_apply_character_replacements($value);
                }
                
                if (!empty($value)) {
                    // Специальная обработка для Description - используем CDATA и prepare_description
                    if ($xml_tag === 'Description') {
                        $description_node = $ad->addChild($xml_tag);
                        $description_cdata = dom_import_simplexml($description_node);
                        $description_cdata->appendChild($description_cdata->ownerDocument->createCDATASection(prepare_description($value)));
                    } elseif (preg_match('/<[^>]+>/', $value)) {
                        // Если значение содержит HTML теги, используем CDATA
                        $node = $ad->addChild($xml_tag);
                        $cdata = dom_import_simplexml($node);
                        $cdata->appendChild($cdata->ownerDocument->createCDATASection($value));
                    } else {
                        $ad->addChild($xml_tag, htmlspecialchars($value));
                    }
                }
            }
        }
    }
    
    /* Поля товаров отключены - используются только глобальные настройки
    // Добавляем поля товара (если есть)
    if ($product && !empty($settings['product_fields'])) {
        foreach ($settings['product_fields'] as $field) {
            if (empty($field['enabled'])) continue;
            
            // Пропускаем специальные поля, которые обрабатываются отдельно
            $skip_fields = array('deposit', 'available_from', 'avito_export');
            if (in_array($field['key'], $skip_fields)) {
                continue;
            }
            
            $value = get_post_meta($product->get_id(), $field['key'], true);
            
            // Если значение не задано у товара, используем общее значение
            if (empty($value) && isset($field['default_value']) && !empty($field['default_value'])) {
                $value = $field['default_value'];
            }
            
            // Обрабатываем плейсхолдеры
            $value = wc_avito_process_placeholders($value, $product, $category_id);
            
            if (!empty($value)) {
                // Используем xml_tag, если он задан, иначе label
                $xml_tag = !empty($field['xml_tag']) ? $field['xml_tag'] : (!empty($field['label']) ? $field['label'] : $field['key']);
                
                // Специальная обработка для Description - используем CDATA
                if ($xml_tag === 'Description' || $field['key'] === 'avito_description') {
                    $description_node = $ad->addChild($xml_tag);
                    $description_cdata = dom_import_simplexml($description_node);
                    $description_cdata->appendChild($description_cdata->ownerDocument->createCDATASection(prepare_description($value)));
                } else {
                    $ad->addChild($xml_tag, htmlspecialchars($value));
                }
            }
        }
    }
    */ // Конец комментария полей товаров
}

/**
 * Обработка плейсхолдеров в значении поля
 * 
 * Поддерживаемые плейсхолдеры:
 * - {product_id} - ID товара (post ID)
 * - {product_name} - Название товара
 * - {product_sku} - Артикул товара
 * - {product_price} - Цена товара
 * - {product_regular_price} - Обычная цена
 * - {product_sale_price} - Цена со скидкой
 * - {product_description} - Описание товара
 * - {product_short_description} - Краткое описание
 * - {product_stock} - Количество товара на остатке
 * - {product_unit} - Единица измерения товара (из мета-поля _unit)
 * - {category_name} - Название категории товара
 * - {product_attributes_list} - Все атрибуты товара в виде HTML списка <ul><li>Свойство: значение</li></ul>
 * - {current_date} - Текущая дата в формате "19 января"
 * - {meta:field_name} - Произвольное поле товара
 * - {meta:field_name.key} - Ключ из сериализованного массива произвольного поля товара
 * - {term_meta:field_name} - Произвольное поле категории
 * - {attribute:attribute_name} - Атрибут товара
 */
function wc_avito_process_placeholders($value, $product = null, $category_id = null) {
    if (empty($value) || !is_string($value)) {
        return $value;
    }
    
    // Обработка плейсхолдера текущей даты - {current_date}
    if (strpos($value, '{current_date}') !== false) {
        $months_ru = array(
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        );
        $day = date('j');
        $month = (int)date('n');
        $current_date_str = $day . ' ' . $months_ru[$month];
        $value = str_replace('{current_date}', $current_date_str, $value);
    }
    
    // Обработка плейсхолдеров товара
    if ($product && is_a($product, 'WC_Product')) {
        // Стандартные поля WooCommerce
        $value = str_replace('{product_id}', $product->get_id(), $value);
        $value = str_replace('{product_name}', $product->get_name(), $value);
        $value = str_replace('{product_sku}', $product->get_sku(), $value);
        $value = str_replace('{product_price}', $product->get_price(), $value);
        $value = str_replace('{product_regular_price}', $product->get_regular_price(), $value);
        $value = str_replace('{product_sale_price}', $product->get_sale_price(), $value);
        $value = str_replace('{product_description}', $product->get_description(), $value);
        $value = str_replace('{product_short_description}', $product->get_short_description(), $value);
        
        // Количество товара на остатке
        $stock_quantity = $product->get_stock_quantity();
        $value = str_replace('{product_stock}', ($stock_quantity !== null ? $stock_quantity : ''), $value);
        
        // Единица измерения товара - {product_unit}
        if (strpos($value, '{product_unit}') !== false) {
            $unit_meta = get_post_meta($product->get_id(), '_unit', true);
            $unit_value = '';
            if (is_array($unit_meta) && isset($unit_meta['value'])) {
                $unit_value = $unit_meta['value'];
            }
            $value = str_replace('{product_unit}', $unit_value, $value);
        }
        
        // Категория товара
        $categories = $product->get_category_ids();
        if (!empty($categories)) {
            $category = get_term($categories[0], 'product_cat');
            if ($category && !is_wp_error($category)) {
                $value = str_replace('{category_name}', $category->name, $value);
            }
        }
        
        // Произвольные поля (meta) - {meta:field_name} или {meta:field_name.key}
        if (preg_match_all('/{meta:([^}]+)}/', $value, $matches)) {
            foreach ($matches[1] as $index => $field_expression) {
                // Проверяем, есть ли вложенный ключ (например, _unit.value)
                if (strpos($field_expression, '.') !== false) {
                    $parts = explode('.', $field_expression, 2);
                    $field_name = $parts[0];
                    $array_key = $parts[1];
                    
                    $meta_value = get_post_meta($product->get_id(), $field_name, true);
                    
                    // Если мета-значение - массив и ключ существует, извлекаем значение
                    if (is_array($meta_value) && isset($meta_value[$array_key])) {
                        $final_value = $meta_value[$array_key];
                    } else {
                        $final_value = '';
                    }
                } else {
                    $meta_value = get_post_meta($product->get_id(), $field_expression, true);
                    // Если значение массив, преобразуем в строку (для обратной совместимости)
                    if (is_array($meta_value)) {
                        $final_value = '';
                    } else {
                        $final_value = $meta_value;
                    }
                }
                
                $value = str_replace($matches[0][$index], $final_value, $value);
            }
        }
        
        // Атрибуты товара - {attribute:attribute_name}
        if (preg_match_all('/{attribute:([^}]+)}/', $value, $matches)) {
            foreach ($matches[1] as $index => $attribute_name) {
                $attribute_value = $product->get_attribute($attribute_name);
                $value = str_replace($matches[0][$index], $attribute_value, $value);
            }
        }
        
        // Список всех атрибутов товара - {product_attributes_list}
        if (strpos($value, '{product_attributes_list}') !== false) {
            $attributes_html = '';
            $attributes = $product->get_attributes();
            
            // Получаем список исключённых атрибутов
            $excluded_attributes = get_option('wc_avito_excluded_attributes', array());
            
            if (!empty($attributes)) {
                // Сначала собираем все элементы в массив
                $attributes_items = array();
                
                foreach ($attributes as $attribute) {
                    if ($attribute->is_taxonomy()) {
                        // Проверяем, не исключён ли этот атрибут
                        $taxonomy_name = $attribute->get_name();
                        if (in_array($taxonomy_name, $excluded_attributes)) {
                            continue; // Пропускаем исключённый атрибут
                        }
                        
                        // Атрибут из таксономии
                        $attribute_name = wc_attribute_label($taxonomy_name);
                        $terms = wp_get_post_terms($product->get_id(), $taxonomy_name, array('fields' => 'names'));
                        if (!empty($terms) && !is_wp_error($terms)) {
                            $attribute_value = implode(', ', $terms);
                            $attributes_items[] = esc_html($attribute_name) . ': ' . esc_html($attribute_value);
                        }
                    } else {
                        // Пользовательский атрибут
                        $attribute_name = $attribute->get_name();
                        $attribute_value = implode(', ', $attribute->get_options());
                        if (!empty($attribute_value)) {
                            $attributes_items[] = esc_html($attribute_name) . ': ' . esc_html($attribute_value);
                        }
                    }
                }
                
                // Формируем HTML список с точками и заголовком
                if (!empty($attributes_items)) {
                    $attributes_html = '<p><strong>Характеристики</strong></p><ul>';
                    $total = count($attributes_items);
                    foreach ($attributes_items as $index => $item) {
                        // Последний элемент заканчивается точкой, остальные - точкой с пробелом
                        $ending = ($index === $total - 1) ? '.' : '. ';
                        $attributes_html .= '<li>' . $item . $ending . '</li>';
                    }
                    $attributes_html .= '</ul>';
                }
            }
            
            $value = str_replace('{product_attributes_list}', $attributes_html, $value);
        }
    }
    
    // Обработка плейсхолдеров категории
    if ($category_id) {
        // Произвольные поля категории - {term_meta:field_name}
        if (preg_match_all('/{term_meta:([^}]+)}/', $value, $matches)) {
            foreach ($matches[1] as $index => $field_name) {
                $term_meta_value = get_term_meta($category_id, $field_name, true);
                $value = str_replace($matches[0][$index], $term_meta_value, $value);
            }
        }
    }
    
    return $value;
}

/**
 * Получение значения глобального поля
 */
function wc_avito_get_global_field_value($field_key) {
    $option_name = 'wc_avito_xml_' . strtolower($field_key);
    return get_option($option_name, '');
}

/**
 * Получение значения поля категории
 */
function wc_avito_get_category_field_value($category_id, $field_key) {
    if (!$category_id) return '';
    return get_term_meta($category_id, $field_key, true);
}

/**
 * Получение значения поля товара
 */
function wc_avito_get_product_field_value($product, $field_key) {
    if (!$product) return '';
    return get_post_meta($product->get_id(), $field_key, true);
}

/**
 * Проверка, включено ли поле
 */
function wc_avito_is_field_enabled($section, $field_key) {
    $settings = wc_avito_get_field_settings();
    $fields = isset($settings[$section . '_fields']) ? $settings[$section . '_fields'] : array();
    
    foreach ($fields as $field) {
        if ($field['key'] === $field_key && !empty($field['enabled'])) {
            return true;
        }
    }
    
    return false;
}

/**
 * Получает пользовательские поля категории с учётом иерархии родительских категорий
 * Поля подкатегории имеют приоритет над полями родительских категорий
 *
 * @param int $category_id ID категории
 * @return array Массив пользовательских полей
 */
function wc_avito_get_category_custom_fields_hierarchy($category_id) {
    $all_fields = array();
    $xml_tags_added = array(); // Для отслеживания уже добавленных XML тегов
    
    // Рекурсивно собираем поля от текущей категории к корневой
    $current_category_id = $category_id;
    
    while ($current_category_id > 0) {
        $custom_fields = get_term_meta($current_category_id, 'avito_category_custom_fields', true);
        
        if (!empty($custom_fields) && is_array($custom_fields)) {
            foreach ($custom_fields as $field) {
                $xml_tag = isset($field['xml_tag']) ? $field['xml_tag'] : '';
                
                // Добавляем поле только если такой XML тег ещё не был добавлен
                // (поля подкатегории имеют приоритет над родительскими)
                if (!empty($xml_tag) && !in_array($xml_tag, $xml_tags_added)) {
                    $all_fields[] = $field;
                    $xml_tags_added[] = $xml_tag;
                }
            }
        }
        
        // Получаем родительскую категорию
        $term = get_term($current_category_id, 'product_cat');
        if ($term && !is_wp_error($term) && $term->parent > 0) {
            $current_category_id = $term->parent;
        } else {
            break;
        }
    }
    
    return $all_fields;
}
