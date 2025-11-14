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
    
    if (empty($settings['product_fields'])) {
        echo '</div>';
        return;
    }
    
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
}
