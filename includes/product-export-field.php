<?php
/**
 * Добавление простого поля "Экспорт на Avito" для товаров
 * Только чекбокс для контроля экспорта, без дополнительных полей
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавляем чекбокс "Экспорт на Avito" в карточку товара
 * Показывается только если включен режим индивидуального контроля экспорта
 */
add_action('woocommerce_product_options_general_product_data', 'wc_avito_add_export_checkbox');
add_action('woocommerce_process_product_meta', 'wc_avito_save_export_checkbox');

function wc_avito_add_export_checkbox() {
    // Показываем чекбокс только если включен режим индивидуального контроля
    $individual_mode = get_option('wc_avito_individual_product_export', '0');
    
    if ($individual_mode !== '1') {
        return; // Не показываем чекбокс если режим отключен
    }
    
    echo '<div class="options_group">';
    
    woocommerce_wp_checkbox(array(
        'id' => '_avito_export_enabled',
        'label' => 'Экспорт на Avito',
        'description' => 'Отметьте, если этот товар должен быть экспортирован в XML для Avito.',
        'desc_tip' => false
    ));
    
    echo '</div>';
}

function wc_avito_save_export_checkbox($post_id) {
    // Сохраняем только если режим индивидуального контроля включен
    $individual_mode = get_option('wc_avito_individual_product_export', '0');
    
    if ($individual_mode !== '1') {
        return; // Не сохраняем если режим отключен
    }
    
    // Только явно установленное значение 'yes' позволяет экспорт
    if (isset($_POST['_avito_export_enabled']) && $_POST['_avito_export_enabled'] === 'yes') {
        update_post_meta($post_id, '_avito_export_enabled', 'yes');
    } else {
        // Явно сохраняем 'no' чтобы товар точно не экспортировался
        update_post_meta($post_id, '_avito_export_enabled', 'no');
    }
}
