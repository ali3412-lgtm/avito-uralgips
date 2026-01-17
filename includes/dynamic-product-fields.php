<?php
/**
 * Динамическое создание полей для товаров на основе настроек
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавляем динамические поля товаров
 */
add_action('woocommerce_product_options_general_product_data', 'wc_avito_add_dynamic_product_fields');
add_action('woocommerce_process_product_meta', 'wc_avito_save_dynamic_product_fields');

function wc_avito_add_dynamic_product_fields() {
    global $post;
    
    echo '<div class="options_group">';
    echo '<h4 style="padding-left: 12px; margin-bottom: 0;">Поля для Avito</h4>';
    
    // Основное поле экспорта (всегда показывается)
    woocommerce_wp_checkbox(array(
        'id' => 'avito_export',
        'label' => 'Экспорт на Avito',
        'description' => 'Отметьте, если товар должен быть экспортирован на Avito'
    ));
    
    // Получаем настройки динамических полей
    $settings = wc_avito_get_field_settings();
    
    // Отображаем динамические поля, если они настроены
    if (!empty($settings['product_fields'])) {
        foreach ($settings['product_fields'] as $field) {
        if (empty($field['enabled'])) {
            continue;
        }
        
        $field_id = $field['key'];
        
        // Пропускаем avito_export, так как оно уже добавлено явно выше
        // Также пропускаем avito_title, если требуется (для предотвращения дублей)
        if ($field_id === 'avito_export') {
            continue;
        }
        
        $field_value = get_post_meta($post->ID, $field_id, true);
        $default_value = isset($field['default_value']) ? $field['default_value'] : '';
        
        // Подготавливаем placeholder
        $placeholder = !empty($default_value) ? 'По умолчанию: ' . $default_value : '';
        
        switch ($field['type']) {
            case 'textarea':
                woocommerce_wp_textarea_input(array(
                    'id' => $field_id,
                    'label' => $field['label'],
                    'placeholder' => $placeholder,
                    'description' => 'XML: <code>' . $field['xml_tag'] . '</code>',
                    'value' => $field_value
                ));
                break;
                
            case 'number':
                woocommerce_wp_text_input(array(
                    'id' => $field_id,
                    'label' => $field['label'],
                    'placeholder' => $placeholder,
                    'description' => 'XML: <code>' . $field['xml_tag'] . '</code>',
                    'type' => 'number',
                    'custom_attributes' => array(
                        'step' => 'any'
                    )
                ));
                break;
                
            case 'date':
                woocommerce_wp_text_input(array(
                    'id' => $field_id,
                    'label' => $field['label'],
                    'placeholder' => $placeholder,
                    'description' => 'XML: <code>' . $field['xml_tag'] . '</code>',
                    'type' => 'date'
                ));
                break;
                
            case 'checkbox':
                woocommerce_wp_checkbox(array(
                    'id' => $field_id,
                    'label' => $field['label'],
                    'description' => 'XML: <code>' . $field['xml_tag'] . '</code>'
                ));
                break;
                
            default: // text
                woocommerce_wp_text_input(array(
                    'id' => $field_id,
                    'label' => $field['label'],
                    'placeholder' => $placeholder,
                    'description' => 'XML: <code>' . $field['xml_tag'] . '</code>'
                ));
                break;
        }
        }
    } // end if (!empty($settings['product_fields']))
    
    // Получаем пользовательские поля товара
    $custom_fields = get_post_meta($post->ID, 'avito_product_custom_fields', true);
    if (!is_array($custom_fields)) {
        $custom_fields = array();
    }
    ?>
    <hr style="margin: 15px 12px; border-top: 1px solid #ddd;">
    <h4 style="padding-left: 12px; margin-bottom: 10px;">Пользовательские поля товара</h4>
    <p style="padding-left: 12px; color: #666; font-size: 12px;">Добавьте индивидуальные XML-поля для этого товара. Эти поля имеют приоритет над полями категории.</p>
    
    <div style="padding: 0 12px;">
        <table class="widefat avito-product-custom-fields" style="max-width: 100%;">
            <thead>
                <tr>
                    <th style="width: 25%;">XML тег</th>
                    <th style="width: 65%;">Значение</th>
                    <th style="width: 10%; text-align: center;">Удалить</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($custom_fields)) : ?>
                    <?php foreach ($custom_fields as $index => $field) :
                        $xml_tag = isset($field['xml_tag']) ? $field['xml_tag'] : '';
                        $value = isset($field['value']) ? $field['value'] : '';
                    ?>
                        <tr>
                            <td style="padding: 8px;">
                                <input type="text" name="avito_product_custom_fields[<?php echo esc_attr($index); ?>][xml_tag]" value="<?php echo esc_attr($xml_tag); ?>" placeholder="Например, Condition" style="width: 100%;" />
                            </td>
                            <td style="padding: 8px;">
                                <textarea name="avito_product_custom_fields[<?php echo esc_attr($index); ?>][value]" rows="2" style="width: 100%;" placeholder="Значение или плейсхолдеры"><?php echo esc_textarea($value); ?></textarea>
                            </td>
                            <td style="text-align: center; padding: 8px; vertical-align: middle;">
                                <button type="button" class="button avito-remove-product-field" title="Удалить поле" style="color: #b32d2e; font-weight: bold;">×</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="avito-no-product-fields">
                        <td colspan="3" style="text-align: center; color: #777; padding: 20px;">Пока нет пользовательских полей</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button" id="avito-add-product-field">Добавить поле</button>
        </p>
        <p class="description" style="font-size: 11px;">
            Поддерживаемые плейсхолдеры: <code>{product_name}</code>, <code>{product_sku}</code>, <code>{product_price}</code>, <code>{product_attributes_list}</code>, <code>{meta:field_name}</code> и др.
        </p>
    </div>

    <script type="text/html" id="tmpl-avito-product-custom-field-row">
        <tr>
            <td style="padding: 8px;">
                <input type="text" name="avito_product_custom_fields[{{data.index}}][xml_tag]" value="" placeholder="Например, Condition" style="width: 100%;" />
            </td>
            <td style="padding: 8px;">
                <textarea name="avito_product_custom_fields[{{data.index}}][value]" rows="2" style="width: 100%;" placeholder="Значение или плейсхолдеры"></textarea>
            </td>
            <td style="text-align: center; padding: 8px; vertical-align: middle;">
                <button type="button" class="button avito-remove-product-field" title="Удалить поле" style="color: #b32d2e; font-weight: bold;">×</button>
            </td>
        </tr>
    </script>

    <script>
    jQuery(document).ready(function($) {
        var $tableBody = $('.avito-product-custom-fields tbody');
        var template = wp.template('avito-product-custom-field-row');

        $('#avito-add-product-field').on('click', function() {
            var index = $tableBody.find('tr').length;
            if ($tableBody.find('.avito-no-product-fields').length) {
                $tableBody.find('.avito-no-product-fields').remove();
            }
            $tableBody.append(template({ index: index }));
        });

        $tableBody.on('click', '.avito-remove-product-field', function() {
            $(this).closest('tr').remove();
            if (!$tableBody.find('tr').length) {
                $tableBody.append('<tr class="avito-no-product-fields"><td colspan="3" style="text-align: center; color: #777; padding: 20px;">Пока нет пользовательских полей</td></tr>');
            }
        });
    });
    </script>

    <style>
    .avito-product-custom-fields {
        border-collapse: collapse;
        margin: 10px 0;
    }
    .avito-product-custom-fields thead th {
        background: #f9f9f9;
        font-weight: 600;
        padding: 10px 8px;
        border-bottom: 2px solid #ddd;
        text-align: left;
    }
    .avito-product-custom-fields tbody td {
        border-bottom: 1px solid #ddd;
    }
    .avito-product-custom-fields input[type="text"],
    .avito-product-custom-fields textarea {
        width: 100%;
        box-sizing: border-box;
    }
    .avito-product-custom-fields textarea {
        resize: vertical;
        min-height: 40px;
    }
    </style>
    <?php
    echo '</div>';
}

function wc_avito_save_dynamic_product_fields($post_id) {
    // Сохраняем основное поле экспорта
    // Только явно установленное значение 'yes' позволяет экспорт
    if (isset($_POST['avito_export']) && $_POST['avito_export'] === 'yes') {
        update_post_meta($post_id, 'avito_export', 'yes');
    } else {
        // Явно сохраняем 'no' чтобы товар точно не экспортировался
        update_post_meta($post_id, 'avito_export', 'no');
    }
    
    // Получаем настройки динамических полей
    $settings = wc_avito_get_field_settings();
    
    if (empty($settings['product_fields'])) {
        return;
    }
    
    foreach ($settings['product_fields'] as $field) {
        if (empty($field['enabled'])) {
            continue;
        }
        
        $field_id = $field['key'];
        
        if ($field['type'] === 'checkbox') {
            $value = isset($_POST[$field_id]) ? 'yes' : 'no';
            update_post_meta($post_id, $field_id, $value);
        } elseif ($field['type'] === 'textarea') {
            $value = isset($_POST[$field_id]) ? wp_kses_post($_POST[$field_id]) : '';
            update_post_meta($post_id, $field_id, $value);
        } else {
            $value = isset($_POST[$field_id]) ? sanitize_text_field($_POST[$field_id]) : '';
            update_post_meta($post_id, $field_id, $value);
        }
    }
    
    // Сохраняем пользовательские поля товара
    if (isset($_POST['avito_product_custom_fields']) && is_array($_POST['avito_product_custom_fields'])) {
        $prepared_fields = array();

        foreach ($_POST['avito_product_custom_fields'] as $field) {
            $xml_tag = isset($field['xml_tag']) ? sanitize_text_field($field['xml_tag']) : '';
            $value = isset($field['value']) ? wp_kses_post($field['value']) : '';

            if (empty($xml_tag) && empty($value)) {
                continue;
            }

            $prepared_fields[] = array(
                'xml_tag' => $xml_tag,
                'value' => $value,
            );
        }

        if (!empty($prepared_fields)) {
            update_post_meta($post_id, 'avito_product_custom_fields', $prepared_fields);
        } else {
            delete_post_meta($post_id, 'avito_product_custom_fields');
        }
    } else {
        delete_post_meta($post_id, 'avito_product_custom_fields');
    }
}

