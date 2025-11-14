<?php
/**
 * Добавление поля "Экспорт на Avito" для категорий товаров
 * Контролирует экспорт всех товаров категории
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавляем поле при создании категории
 * ОТКЛЮЧЕНО - поля плагина показываются только при редактировании категории
 */
// add_action('product_cat_add_form_fields', 'wc_avito_add_category_export_field', 10, 2);

function wc_avito_add_category_export_field() {
    // Функция отключена - поля не показываются при создании категории
    // Настройки Avito доступны только при редактировании существующей категории
}

/**
 * Добавляем поле при редактировании категории
 */
add_action('product_cat_edit_form_fields', 'wc_avito_edit_category_export_field', 10, 2);

function wc_avito_edit_category_export_field($term) {
    $avito_export = get_term_meta($term->term_id, 'avito_export', true);
    $avito_title_template = get_term_meta($term->term_id, 'avito_title_template', true);
    $custom_fields = get_term_meta($term->term_id, 'avito_category_custom_fields', true);
    if (!is_array($custom_fields)) {
        $custom_fields = array();
    }
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label>Настройки Avito</label>
        </th>
        <td>
            <div style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                <div style="margin-bottom: 15px;">
                    <label for="avito_export">
                        <input type="checkbox" name="avito_export" id="avito_export" value="yes" <?php checked($avito_export, 'yes'); ?> />
                        <strong>Экспорт на Avito</strong>
                    </label>
                    <p class="description">Если отмечено, все товары из этой категории будут экспортироваться в XML для Avito. По умолчанию экспорт отключен.</p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="avito_title_template"><strong>Шаблон заголовка (Title)</strong></label><br>
                    <input type="text" name="avito_title_template" id="avito_title_template" value="<?php echo esc_attr($avito_title_template); ?>" class="regular-text" placeholder="{product_name}" style="width: 100%; max-width: 500px;" />
                    <p class="description">
                        Шаблон для генерации заголовка объявлений. Доступные плейсхолдеры:<br>
                        <code>{product_id}</code> - ID товара, 
                        <code>{product_name}</code> - название товара, 
                        <code>{category_name}</code> - название категории, 
                        <code>{product_sku}</code> - артикул,
                        <code>{product_attributes_list}</code> - список всех атрибутов в HTML<br>
                        Пример: <code>{product_name} - {category_name}</code>
                    </p>
                </div>
                
                <?php
                // Добавляем динамические поля категории
                $settings = wc_avito_get_field_settings();
                if (!empty($settings['category_fields'])) {
                    echo '<hr style="margin: 15px 0; border-top: 1px solid #ddd;">';
                    echo '<h4 style="margin: 10px 0;">Дополнительные поля</h4>';
                    
                    foreach ($settings['category_fields'] as $field) {
                        if (empty($field['enabled'])) {
                            continue;
                        }
                        
                        $field_id = $field['key'];
                        $field_value = get_term_meta($term->term_id, $field_id, true);
                        $default_value = isset($field['default_value']) ? $field['default_value'] : '';
                        $placeholder = !empty($default_value) ? $default_value : '';
                        $xml_tag = !empty($field['xml_tag']) ? $field['xml_tag'] : $field['label'];
                        ?>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="<?php echo esc_attr($field_id); ?>">
                                <strong><?php echo esc_html($field['label']); ?></strong>
                            </label><br>
                            
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" rows="3" style="width: 100%; max-width: 500px;" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_textarea($field_value); ?></textarea>
                            <?php elseif ($field['type'] === 'checkbox'): ?>
                                <label>
                                    <input type="checkbox" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" value="1" <?php checked($field_value, '1'); ?> />
                                    <?php echo !empty($placeholder) ? esc_html($placeholder) : 'Включить'; ?>
                                </label>
                            <?php else: ?>
                                <input type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($field_value); ?>" class="regular-text" placeholder="<?php echo esc_attr($placeholder); ?>" style="width: 100%; max-width: 500px;" />
                            <?php endif; ?>
                            
                            <p class="description">
                                XML тег: <code><?php echo esc_html($xml_tag); ?></code>
                                <?php if (!empty($default_value)): ?>
                                    | Значение по умолчанию: <strong><?php echo esc_html($default_value); ?></strong>
                                <?php endif; ?>
                                <br><small>Поддерживает плейсхолдеры: <code>{product_id}</code>, <code>{product_name}</code>, <code>{product_attributes_list}</code> и др.</small>
                            </p>
                        </div>
                        
                        <?php
                    }
                }
                ?>

                <hr style="margin: 15px 0; border-top: 1px solid #ddd;">
                <h4 style="margin: 10px 0;">Пользовательские поля категории</h4>
                <p>Добавьте индивидуальные XML-поля, которые будут применяться только к этой категории. Поддерживаются плейсхолдеры.</p>

                <table class="widefat avito-category-custom-fields" style="max-width: 700px;">
                    <thead>
                        <tr>
                            <th style="width: 30%;">XML тег</th>
                            <th>Значение</th>
                            <th style="width: 60px; text-align: center;">Удалить</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($custom_fields)) : ?>
                            <?php foreach ($custom_fields as $index => $field) :
                                $xml_tag = isset($field['xml_tag']) ? $field['xml_tag'] : '';
                                $value = isset($field['value']) ? $field['value'] : '';
                            ?>
                                <tr>
                                    <td>
                                        <input type="text" name="avito_category_custom_fields[<?php echo esc_attr($index); ?>][xml_tag]" value="<?php echo esc_attr($xml_tag); ?>" placeholder="Например, DeliveryCost" class="regular-text" />
                                    </td>
                                    <td>
                                        <textarea name="avito_category_custom_fields[<?php echo esc_attr($index); ?>][value]" rows="2" style="width: 100%;" placeholder="Значение или плейсхолдеры"><?php echo esc_textarea($value); ?></textarea>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="button button-link-delete avito-remove-custom-field">×</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr class="avito-no-custom-fields">
                                <td colspan="3" style="text-align: center; color: #777;">Пока нет пользовательских полей</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <p>
                    <button type="button" class="button" id="avito-add-custom-field">Добавить поле</button>
                </p>
                <p class="description">
                    Примеры значений: <code>{product_attributes_list}</code>, <code>{meta:_weight}</code>, <code>В наличии</code>. Значение поддерживает HTML.
                </p>
            </div>
        </td>
    </tr>

    <script type="text/html" id="tmpl-avito-category-custom-field-row">
        <tr>
            <td>
                <input type="text" name="avito_category_custom_fields[{{data.index}}][xml_tag]" value="" placeholder="Например, Condition" class="regular-text" />
            </td>
            <td>
                <textarea name="avito_category_custom_fields[{{data.index}}][value]" rows="2" style="width: 100%;" placeholder="Значение или плейсхолдеры"></textarea>
            </td>
            <td style="text-align: center;">
                <button type="button" class="button button-link-delete avito-remove-custom-field">×</button>
            </td>
        </tr>
    </script>

    <script>
    jQuery(document).ready(function($) {
        var $tableBody = $('.avito-category-custom-fields tbody');
        var template = wp.template('avito-category-custom-field-row');

        $('#avito-add-custom-field').on('click', function() {
            var index = $tableBody.find('tr').length;
            if ($tableBody.find('.avito-no-custom-fields').length) {
                $tableBody.find('.avito-no-custom-fields').remove();
            }
            $tableBody.append(template({ index: index }));
        });

        $tableBody.on('click', '.avito-remove-custom-field', function() {
            $(this).closest('tr').remove();
            if (!$tableBody.find('tr').length) {
                $tableBody.append('<tr class="avito-no-custom-fields"><td colspan="3" style="text-align: center; color: #777;">Пока нет пользовательских полей</td></tr>');
            }
        });
    });
    </script>

    <style>
    .avito-category-custom-fields textarea {
        resize: vertical;
    }
    .avito-category-custom-fields .button-link-delete {
        color: #b32d2e;
        font-size: 20px;
        line-height: 1;
        padding: 0;
    }
    .avito-category-custom-fields .button-link-delete:hover {
        color: #dc3232;
    }
    </style>
    <?php
}

/**
 * Сохраняем значение поля при создании категории
 * ОТКЛЮЧЕНО - поля не показываются при создании, настройки по умолчанию устанавливаются при первом редактировании
 */
// add_action('created_product_cat', 'wc_avito_save_category_export_field', 10, 2);

function wc_avito_save_category_export_field($term_id) {
    // Функция отключена - настройки устанавливаются только при редактировании категории
    // По умолчанию категория создается без настроек Avito (экспорт отключен)
}

/**
 * Сохраняем значение поля при редактировании категории
 */
add_action('edited_product_cat', 'wc_avito_update_category_export_field', 10, 2);

function wc_avito_update_category_export_field($term_id) {
    // Сохраняем статус экспорта
    if (isset($_POST['avito_export']) && $_POST['avito_export'] === 'yes') {
        update_term_meta($term_id, 'avito_export', 'yes');
    } else {
        // Явно сохраняем 'no', если не отмечено
        update_term_meta($term_id, 'avito_export', 'no');
    }
    
    // Сохраняем шаблон заголовка (разрешаем HTML теги)
    if (isset($_POST['avito_title_template'])) {
        update_term_meta($term_id, 'avito_title_template', wp_kses_post($_POST['avito_title_template']));
    }

    // Сохраняем пользовательские поля
    if (isset($_POST['avito_category_custom_fields']) && is_array($_POST['avito_category_custom_fields'])) {
        $prepared_fields = array();

        foreach ($_POST['avito_category_custom_fields'] as $field) {
            $xml_tag = isset($field['xml_tag']) ? sanitize_text_field($field['xml_tag']) : '';
            $value = isset($field['value']) ? wp_kses_post($field['value']) : '';

            if (empty($xml_tag) && empty($value)) {
                continue;
            }

            $prepared_fields[] = array(
                'key' => wc_avito_generate_field_key($xml_tag),
                'xml_tag' => $xml_tag,
                'value' => $value,
            );
        }

        if (!empty($prepared_fields)) {
            update_term_meta($term_id, 'avito_category_custom_fields', $prepared_fields);
        } else {
            delete_term_meta($term_id, 'avito_category_custom_fields');
        }
    } else {
        delete_term_meta($term_id, 'avito_category_custom_fields');
    }
}

/**
 * Добавляем колонку "Экспорт на Avito" в список категорий
 */
add_filter('manage_edit-product_cat_columns', 'wc_avito_add_category_export_column');

function wc_avito_add_category_export_column($columns) {
    $columns['avito_export'] = 'Экспорт на Avito';
    return $columns;
}

/**
 * Заполняем колонку "Экспорт на Avito" в списке категорий
 */
add_filter('manage_product_cat_custom_column', 'wc_avito_fill_category_export_column', 10, 3);

function wc_avito_fill_category_export_column($content, $column_name, $term_id) {
    if ($column_name === 'avito_export') {
        $avito_export = get_term_meta($term_id, 'avito_export', true);
        if ($avito_export === 'yes') {
            $content = '<span style="color: green;">✓ Включен</span>';
        } else {
            $content = '<span style="color: #999;">✗ Отключен</span>';
        }
    }
    return $content;
}
