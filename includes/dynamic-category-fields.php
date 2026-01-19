<?php
/**
 * Динамическое создание полей для категорий на основе настроек
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавляем динамические поля категорий
 * ОТКЛЮЧЕНО - поля теперь отображаются в блоке "Настройки Avito" в category-export-field.php
 */
// add_action('product_cat_edit_form_fields', 'wc_avito_add_dynamic_category_fields', 10, 2);
add_action('edited_product_cat', 'wc_avito_save_dynamic_category_fields', 10, 2);

function wc_avito_add_dynamic_category_fields($term, $taxonomy) {
    // Функция отключена - поля отображаются в блоке "Настройки Avito"
    return;
    
    foreach ($settings['category_fields'] as $field) {
        if (empty($field['enabled'])) {
            continue;
        }
        
        $field_id = $field['key'];
        $field_value = get_term_meta($term->term_id, $field_id, true);
        $default_value = isset($field['default_value']) ? $field['default_value'] : '';
        
        // Подготавливаем placeholder
        $placeholder = !empty($default_value) ? 'По умолчанию: ' . $default_value : '';
        
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html($field['label']); ?></label>
            </th>
            <td>
                <?php
                switch ($field['type']) {
                    case 'textarea':
                        ?>
                        <textarea name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" rows="4" style="width: 95%;" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_textarea($field_value); ?></textarea>
                        <?php
                        break;
                        
                    case 'number':
                        ?>
                        <input type="number" step="any" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($field_value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" style="width: 95%;" />
                        <?php
                        break;
                        
                    case 'date':
                        ?>
                        <input type="date" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($field_value); ?>" style="width: 95%;" />
                        <?php
                        break;
                        
                    case 'checkbox':
                        ?>
                        <input type="checkbox" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" value="1" <?php checked($field_value, '1'); ?> />
                        <?php
                        break;
                        
                    default: // text
                        ?>
                        <input type="text" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($field_value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" style="width: 95%;" />
                        <?php
                        break;
                }
                ?>
                <p class="description">XML тег: <code><?php echo esc_html($field['xml_tag']); ?></code><?php if (!empty($default_value)) echo ' | Значение по умолчанию: <strong>' . esc_html($default_value) . '</strong>'; ?></p>
            </td>
        </tr>
        <?php
    }
}

function wc_avito_save_dynamic_category_fields($term_id, $tt_id) {
    // Проверяем, что форма Авито была действительно отправлена
    // Это защищает от случайного удаления данных при конфликтах плагинов или проблемах с JS
    if (!isset($_POST['avito_category_form_submitted']) || $_POST['avito_category_form_submitted'] !== '1') {
        return; // Форма Авито не была отправлена, не трогаем данные
    }

    // Получаем настройки динамических полей
    $settings = wc_avito_get_field_settings();
    
    if (empty($settings['category_fields'])) {
        return;
    }
    
    foreach ($settings['category_fields'] as $field) {
        if (empty($field['enabled'])) {
            continue;
        }
        
        $field_id = $field['key'];
        
        if ($field['type'] === 'checkbox') {
            if (isset($_POST[$field_id])) {
                update_term_meta($term_id, $field_id, '1');
            } else {
                // Для чекбоксов сохраняем '0' вместо удаления
                update_term_meta($term_id, $field_id, '0');
            }
        } elseif ($field['type'] === 'textarea') {
            if (isset($_POST[$field_id])) {
                $value = wp_kses_post($_POST[$field_id]);
                // Сохраняем значение (даже пустое) вместо удаления
                update_term_meta($term_id, $field_id, $value);
            }
            // Если поле не isset, не трогаем существующее значение
        } else {
            if (isset($_POST[$field_id])) {
                // Разрешаем HTML теги
                $value = wp_kses_post($_POST[$field_id]);
                // Сохраняем значение (даже пустое) вместо удаления
                update_term_meta($term_id, $field_id, $value);
            }
            // Если поле не isset, не трогаем существующее значение
        }
    }
}
