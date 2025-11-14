<?php
/**
 * Управление динамическими полями для XML
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получение дефолтных настроек полей
 * Используется при активации плагина и как fallback
 */
function wc_avito_get_default_field_settings() {
    return array(
        'global_fields' => array(
            array('key' => 'Id', 'label' => 'ID товара', 'xml_tag' => 'Id', 'type' => 'text', 'value' => '{product_sku}', 'enabled' => true),
            array('key' => 'Title', 'label' => 'Заголовок объявления', 'xml_tag' => 'Title', 'type' => 'text', 'value' => '{product_name}', 'enabled' => true),
            array('key' => 'Description', 'label' => 'Описание', 'xml_tag' => 'Description', 'type' => 'textarea', 'value' => '{product_description}', 'enabled' => true),
            array('key' => 'ContactMethod', 'label' => 'Способ связи', 'xml_tag' => 'ContactMethod', 'type' => 'text', 'value' => 'По телефону и в сообщениях', 'enabled' => true),
            array('key' => 'ContactPhone', 'label' => 'Контактный телефон', 'xml_tag' => 'ContactPhone', 'type' => 'text', 'value' => '', 'enabled' => true),
            array('key' => 'ManagerName', 'label' => 'Имя менеджера', 'xml_tag' => 'ManagerName', 'type' => 'text', 'value' => '', 'enabled' => true),
            array('key' => 'Address', 'label' => 'Адрес', 'xml_tag' => 'Address', 'type' => 'text', 'value' => 'г. Ижевск, ул. Фурманова, 57', 'enabled' => true),
            array('key' => 'Latitude', 'label' => 'Широта', 'xml_tag' => 'Latitude', 'type' => 'text', 'value' => '', 'enabled' => true),
            array('key' => 'Longitude', 'label' => 'Долгота', 'xml_tag' => 'Longitude', 'type' => 'text', 'value' => '', 'enabled' => true),
            array('key' => 'Logo', 'label' => 'Логотип (URL)', 'xml_tag' => 'Logo', 'type' => 'text', 'value' => '', 'enabled' => true),
            array('key' => 'WorkDays', 'label' => 'Рабочие дни', 'xml_tag' => 'WorkDays', 'type' => 'text', 'value' => 'пн-пт: с 9.00 до 19.00, сб-вс: с 9.00 до 17.00', 'enabled' => true),
        ),
        'category_fields' => array(),
        'product_fields' => array(),
    );
}

/**
 * Получение настроек полей (из БД или дефолтных)
 */
function wc_avito_get_field_settings() {
    $default_settings = wc_avito_get_default_field_settings();
    $settings = get_option('wc_avito_field_settings', $default_settings);
    
    // Если настройки пустые, возвращаем значения по умолчанию
    if (empty($settings)) {
        $settings = $default_settings;
    }
    
    return $settings;
}

/**
 * Сохранение настроек полей
 */
function wc_avito_save_field_settings($settings) {
    return update_option('wc_avito_field_settings', $settings);
}

// Регистрация полей теперь выполняется в файлах:
// - dynamic-category-fields.php - для категорий
// - dynamic-product-fields.php - для товаров
