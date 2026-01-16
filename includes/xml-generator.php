<?php
/**
 * Функционал генерации XML для Avito
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Проверяет, отключён ли экспорт для категории (с учётом родительских категорий)
 * Если родительская категория отключена, все вложенные тоже считаются отключёнными
 *
 * @param int $category_id ID категории для проверки
 * @return bool true если экспорт отключён, false если разрешён
 */
function wc_avito_is_category_export_disabled($category_id) {
    // Проверяем текущую категорию
    $disabled = get_term_meta($category_id, 'avito_export_disabled', true);
    if ($disabled === '1') {
        return true;
    }
    
    // Получаем родительскую категорию
    $term = get_term($category_id, 'product_cat');
    if ($term && !is_wp_error($term) && $term->parent > 0) {
        // Рекурсивно проверяем родительскую категорию
        return wc_avito_is_category_export_disabled($term->parent);
    }
    
    return false;
}

/**
 * Основная функция генерации XML для Avito
 */
function generate_avito_xml() {
    global $avito_xml_errors;
    
    // Увеличиваем лимит памяти и времени выполнения
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 300);

    // Инициализируем XML с правильными атрибутами согласно документации Avito
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Ads formatVersion="3" target="Avito.ru"></Ads>');

    // Добавляем дату и время генерации файла
    $generation_time = new DateTime();
    $xml->addChild('GeneratedAt', $generation_time->format('Y-m-d H:i:s'));
    $xml->addChild('GeneratedDate', $generation_time->format('Y-m-d'));
    $xml->addChild('GeneratedTime', $generation_time->format('H:i:s'));

    if (!empty($avito_xml_errors)) {
        $errors_node = $xml->addChild('Errors');
        foreach ($avito_xml_errors as $error) {
            $errors_node->addChild('Error', htmlspecialchars($error));
        }
    }

    // Добавляем объявления только по товарам с пагинацией
    $products_active = get_option('wc_avito_xml_enable_products', '1') === '1';
    if ($products_active) {
        $batch_size = 20; // Обрабатываем по 20 товаров за раз
        $offset = 0;
        
        do {
            $products = wc_get_products(array(
                'status' => 'publish',
                'limit' => $batch_size,
                'offset' => $offset,
            ));
            
            foreach ($products as $product) {
                // По умолчанию все товары экспортируются
                $should_export = true;
                
                // Проверяем категории товара на флаг отключения экспорта (включая родительские)
                $product_categories = $product->get_category_ids();
                if (!empty($product_categories)) {
                    foreach ($product_categories as $cat_id) {
                        // Проверяем текущую категорию и всех её родителей
                        if (wc_avito_is_category_export_disabled($cat_id)) {
                            $should_export = false;
                            break; // Если хотя бы одна категория (или её родитель) отключена, товар не экспортируется
                        }
                    }
                }
                
                // Если включен режим индивидуального контроля, проверяем флаг товара
                if ($should_export) {
                    $individual_mode = get_option('wc_avito_individual_product_export', '0');
                    
                    if ($individual_mode === '1') {
                        // В индивидуальном режиме проверяем метаполе товара
                        $product_export_enabled = get_post_meta($product->get_id(), '_avito_export_enabled', true);
                        
                        // Экспортируем только если явно установлен флаг 'yes'
                        if ($product_export_enabled !== 'yes') {
                            $should_export = false;
                        }
                    }
                }
                
                // Проверяем наличие изображений (если настройка включена)
                if ($should_export) {
                    $skip_without_images = get_option('wc_avito_skip_products_without_images', '0');
                    
                    if ($skip_without_images === '1') {
                        // Проверяем основное изображение и галерею
                        $main_image_id = $product->get_image_id();
                        $gallery_image_ids = $product->get_gallery_image_ids();
                        
                        if (empty($main_image_id) && empty($gallery_image_ids)) {
                            $should_export = false;
                        }
                    }
                }
                
                // Экспортируем товар
                if ($should_export) {
                    add_product_ad($xml, $product, true);
                }
            }
            
            $offset += $batch_size;
            
            // Освобождаем память после каждой партии
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            
        } while (count($products) === $batch_size && $offset < 500); // Максимум 500 товаров
    }

    $xml_string = $xml->asXML();
    
    // Валидация XML согласно документации Avito
    validate_avito_xml($xml_string);

    // Сохраняем XML в файл
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/avito_products.xml';
    file_put_contents($file_path, $xml_string);

    if (!empty($avito_xml_errors)) {
        error_log('XML файл сгенерирован с ошибками. Пожалуйста, проверьте начало XML файла для подробностей об ошибках.');
    } else {
        error_log('XML файл успешно сгенерирован.');
    }
}

/**
 * Валидация XML согласно документации Avito
 */
function validate_avito_xml($xml_string) {
    global $avito_xml_errors;
    
    $xml = simplexml_load_string($xml_string);
    if (!$xml) {
        add_avito_xml_error('Невалидный XML-формат');
        return;
    }
    
    // Проверяем обязательные атрибуты корневого элемента
    if (!isset($xml['formatVersion']) || !isset($xml['target'])) {
        add_avito_xml_error('Отсутствуют обязательные атрибуты formatVersion или target');
    }
    
    // Проверяем каждое объявление
    foreach ($xml->Ad as $ad) {
        // Проверяем наличие обязательного поля Price
        if (empty((string)$ad->Price)) {
            add_avito_xml_error('Отсутствует обязательное поле: Price');
        } elseif ((float)$ad->Price <= 0) {
            add_avito_xml_error('Цена должна быть больше нуля. Указано: ' . (string)$ad->Price);
        }
        
        // Проверяем формат дат
        if (!empty($ad->DateBegin) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$ad->DateBegin)) {
            add_avito_xml_error('Неверный формат даты DateBegin: ' . (string)$ad->DateBegin);
        }
        if (!empty($ad->DateEnd) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$ad->DateEnd)) {
            add_avito_xml_error('Неверный формат даты DateEnd: ' . (string)$ad->DateEnd);
        }
    }
}

/**
 * Устанавливает общие настройки объявления
 */
function set_common_ad_settings($ad, $product = null, $is_active = true, $category_id = null) {
    // Добавляем все динамические поля (глобальные, категории и товара)
    wc_avito_add_dynamic_fields($ad, $product, $category_id);
}


/**
 * Добавляет объявление для продукта
 */
function add_product_ad($xml, $product, $is_active) {
    $ad = $xml->addChild('Ad');
    
    // Получаем категории товара для использования категорийных настроек
    $product_categories = $product->get_category_ids();
    $category_id = !empty($product_categories) ? $product_categories[0] : null;
    
    // 1. Images - изображения
    add_product_images($ad, $product);

    // 2. Price - цена (обязательное поле)
    // Приоритет: цена распродажи > обычная цена
    $sale_price = $product->get_sale_price();
    $regular_price = $product->get_regular_price();
    
    // Определяем цену (с приоритетом распродажи)
    if (!empty($sale_price) && $sale_price > 0) {
        $price = $sale_price;
    } elseif (!empty($regular_price) && $regular_price > 0) {
        $price = $regular_price;
    } else {
        $price = $product->get_price();
    }
    
    // Price - обязательное поле, всегда добавляем
    $ad->addChild('Price', $price);

    // 3. Остальные динамические поля (Id, Title, Description, Category, ContactMethod, Address и т.д.)
    set_common_ad_settings($ad, $product, $is_active, $category_id);
}

/**
 * Добавляет описание к объявлению
 */
function add_description_to_ad($ad, $description) {
    $description_node = $ad->addChild('Description');
    $description_cdata = dom_import_simplexml($description_node);
    $description_cdata->appendChild($description_cdata->ownerDocument->createCDATASection(prepare_description($description)));
}

/**
 * Добавляет изображения товара
 */
function add_product_images($ad, $product) {
    $max_images = 10;
    $image_ids = array();

    // Собираем основное изображение товара
    $main_image_id = $product->get_image_id();
    if ($main_image_id) {
        $image_ids[] = $main_image_id;
    }
    
    // Собираем изображения из галереи WooCommerce
    if (count($image_ids) < $max_images) {
        $gallery_image_ids = $product->get_gallery_image_ids();
        if (!empty($gallery_image_ids)) {
            foreach ($gallery_image_ids as $gallery_image_id) {
                if (count($image_ids) >= $max_images) break;
                $image_ids[] = $gallery_image_id;
            }
        }
    }
    
    // Создаем блок Images только если есть изображения
    if (!empty($image_ids)) {
        $images = $ad->addChild('Images');
        foreach ($image_ids as $image_id) {
            add_image_to_ad($images, $image_id);
        }
    }
}

/**
 * Добавляет отдельное изображение к объявлению
 */
function add_image_to_ad($images, $image_id) {
    $image_url = wp_get_attachment_image_url($image_id, 'full');
    if ($image_url) {
        $image = $images->addChild('Image');
        $image->addAttribute('url', $image_url);
    }
}

/**
 * Рассчитывает сумму залога (8 дневных цен)
 */
function calculate_deposit($product) {
    $custom_deposit = get_post_meta($product->get_id(), 'deposit', true);
    if (!empty($custom_deposit)) {
        return floatval($custom_deposit);
    }

    // Используем цену за сутки для расчета залога
    $daily_price = get_daily_price($product);
    return round($daily_price * 8, 2);
}



/**
 * Подготавливает описание, очищая от лишних тегов
 */
function prepare_description($description) {
    // Заменяем \n на <br>
    $description = nl2br($description);

    // Разрешенные теги
    $allowed_tags = '<p><br><strong><em><ul><ol><li>';

    // Очищаем описание, оставляя только разрешенные теги
    $description = strip_tags($description, $allowed_tags);

    return $description;
}
