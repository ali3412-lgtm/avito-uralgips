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
 */
add_action('woocommerce_product_options_general_product_data', 'wc_avito_add_export_checkbox');
add_action('woocommerce_process_product_meta', 'wc_avito_save_export_checkbox');

function wc_avito_add_export_checkbox() {
    echo '<div class="options_group">';
    
    woocommerce_wp_checkbox(array(
        'id' => 'avito_export',
        'label' => 'Экспорт на Avito',
        'description' => 'Отметьте, если товар должен быть экспортирован на Avito'
    ));
    
    echo '</div>';
}

function wc_avito_save_export_checkbox($post_id) {
    // Только явно установленное значение 'yes' позволяет экспорт
    if (isset($_POST['avito_export']) && $_POST['avito_export'] === 'yes') {
        update_post_meta($post_id, 'avito_export', 'yes');
    } else {
        // Явно сохраняем 'no' чтобы товар точно не экспортировался
        update_post_meta($post_id, 'avito_export', 'no');
    }
}
