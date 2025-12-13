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
            array('key' => 'Category', 'label' => 'Категория', 'xml_tag' => 'Category', 'type' => 'text', 'value' => 'Бытовая техника', 'enabled' => true),
            array('key' => 'GoodsType', 'label' => 'Вид товара', 'xml_tag' => 'GoodsType', 'type' => 'text', 'value' => 'Климатическое оборудование', 'enabled' => true),
            array('key' => 'TypeId', 'label' => 'Тип товара', 'xml_tag' => 'TypeId', 'type' => 'text', 'value' => 'Кондиционеры', 'enabled' => true),
            array('key' => 'Condition', 'label' => 'Состояние', 'xml_tag' => 'Condition', 'type' => 'text', 'value' => 'Новое', 'enabled' => true),
            array('key' => 'Brand', 'label' => 'Производитель', 'xml_tag' => 'Brand', 'type' => 'text', 'value' => '{attribute:pa_brand}', 'enabled' => true),
            array('key' => 'Model', 'label' => 'Модель', 'xml_tag' => 'Model', 'type' => 'text', 'value' => '{attribute:pa_model}', 'enabled' => true),
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

/**
 * Генерация ключа из названия поля
 */
function wc_avito_generate_field_key($label, $existing_key = '') {
    // Если ключ уже существует и не пустой, используем его
    if (!empty($existing_key)) {
        return $existing_key;
    }
    
    // Транслитерация русских букв в латиницу
    $transliteration = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
    );
    
    // Транслитерируем
    $key = strtr($label, $transliteration);
    
    // Заменяем пробелы и спецсимволы на подчеркивания
    $key = preg_replace('/[^a-zA-Z0-9]+/', '_', $key);
    
    // Убираем подчеркивания в начале и конце
    $key = trim($key, '_');
    
    // Приводим к нижнему регистру
    $key = strtolower($key);
    
    return $key;
}

// Регистрация полей теперь выполняется в файлах:
// - dynamic-category-fields.php - для категорий
// - dynamic-product-fields.php - для товаров
