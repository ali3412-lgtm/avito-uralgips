<?php
/**
 * Удаление данных плагина при его деинсталляции
 * 
 * Этот файл выполняется автоматически при удалении плагина через WordPress.
 * НЕ выполняется при деактивации плагина!
 * 
 * Что удаляется:
 * - Поле avito_export у категорий (текущая версия)
 * - Старые post_meta поля товаров (если остались от предыдущих версий)
 * - Старые term_meta поля категорий (если остались от предыдущих версий)
 * - Все опции плагина из wp_options
 * - Cron задачи автоматической генерации XML
 * - Сгенерированные XML файлы
 *
 * @package WC_Avito_VDOM
 */

// Если файл вызван не из WordPress, прерываем выполнение
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Удаляем все meta-данные товаров и категорий, созданные плагином
global $wpdb;

// Очищаем старые поля товаров (если остались от предыдущих версий плагина)
// В текущей версии индивидуальные поля товаров не используются
$old_product_fields = array('avito_export', 'avito_title', 'avito_description', 'deposit', 'available_from');

foreach ($old_product_fields as $meta_key) {
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $meta_key
        )
    );
}

// Очищаем поля категорий
// Текущие: avito_export, avito_title_template
// Старые (от предыдущих версий): avito_category, avitoid и т.д.
$old_category_fields = array('avito_export', 'avito_title_template', 'avito_category', 'avitoid', 'avito_title', 
                               'avito_description', 'avito_price', 'avito_contact_method',
                               'avito_listing_fee', 'avito_ad_status', 'avito_workwithlegalentities');

foreach ($old_category_fields as $meta_key) {
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->termmeta} WHERE meta_key = %s",
            $meta_key
        )
    );
}

// Удаляем все опции плагина
$options = array(
    'wc_avito_xml_enable_products',
    'wc_avito_xml_schedule_enabled',
    'wc_avito_xml_schedule_interval',
    'wc_avito_xml_enable_logging',
    'wc_avito_xml_notify_errors',
    'wc_avito_field_settings',
    'wc_avito_xml_cron_logs',
);

foreach ($options as $option) {
    delete_option($option);
}

// Удаляем cron-задачи
wp_clear_scheduled_hook('wc_avito_xml_cron_generate_event');

// Удаляем сгенерированные файлы
$upload_dir = wp_upload_dir();
$xml_file_path = $upload_dir['basedir'] . '/avito_products.xml';

if (file_exists($xml_file_path)) {
    @unlink($xml_file_path);
}

// Очищаем кэш
wp_cache_flush();
