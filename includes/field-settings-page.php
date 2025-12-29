<?php
/**
 * Страница управления полями XML
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавление подменю для управления полями
 */
function wc_avito_fields_menu() {
    add_submenu_page(
        'wc-avito-xml',
        'Управление полями XML',
        'Поля XML',
        'manage_options',
        'wc-avito-fields',
        'wc_avito_fields_page'
    );
}
add_action('admin_menu', 'wc_avito_fields_menu', 20);

/**
 * Страница управления полями
 */
function wc_avito_fields_page() {
    if (isset($_POST['save_field_settings']) && check_admin_referer('wc_avito_field_settings', 'wc_avito_fields_nonce')) {
        wc_avito_handle_field_settings_save();
        echo '<div class="updated"><p>Настройки полей сохранены.</p></div>';
    }
    
    if (isset($_POST['add_field']) && check_admin_referer('wc_avito_add_field', 'wc_avito_add_field_nonce')) {
        wc_avito_handle_add_field();
        echo '<div class="updated"><p>Поле добавлено.</p></div>';
    }
    
    $settings = wc_avito_get_field_settings();
    ?>
    <div class="wrap">
        <h1>Управление полями XML для Avito</h1>
        <p>Здесь вы можете настроить, какие поля будут использоваться при генерации XML для Avito.</p>
        
        <!-- Информация о плейсхолдерах -->
        <div class="notice notice-info" style="margin: 15px 0; padding: 10px;">
            <h3 style="margin-top: 0;">📝 Доступные плейсхолдеры для полей</h3>
            <p>В значениях полей можно использовать следующие плейсхолдеры, которые будут автоматически заменены на реальные данные:</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Стандартные поля товара:</strong>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <li><code>{product_id}</code> - ID товара</li>
                        <li><code>{product_name}</code> - Название товара</li>
                        <li><code>{product_sku}</code> - Артикул товара</li>
                        <li><code>{product_price}</code> - Цена товара</li>
                        <li><code>{product_regular_price}</code> - Обычная цена</li>
                        <li><code>{product_sale_price}</code> - Цена со скидкой</li>
                        <li><code>{product_description}</code> - Полное описание</li>
                        <li><code>{product_short_description}</code> - Краткое описание</li>
                        <li><code>{category_name}</code> - Название категории</li>
                        <li><code>{product_attributes_list}</code> - Список всех атрибутов в HTML формате <code>&lt;ul&gt;&lt;li&gt;Свойство: значение&lt;/li&gt;&lt;/ul&gt;</code></li>
                    </ul>
                </div>
                <div>
                    <strong>Произвольные поля:</strong>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <li><code>{meta:field_name}</code> - Произвольное поле товара<br><small style="color: #666;">Например: {meta:_weight} для веса</small></li>
                        <li><code>{term_meta:field_name}</code> - Поле категории<br><small style="color: #666;">Например: {term_meta:delivery_info}</small></li>
                        <li><code>{attribute:attribute_name}</code> - Атрибут товара<br><small style="color: #666;">Например: {attribute:pa_color} для цвета</small></li>
                    </ul>
                </div>
            </div>
            <p style="margin-bottom: 0;">
                <strong>Примеры использования:</strong><br>
                • <code>Товар: {product_name}, цена {product_price} руб. SKU: {product_sku}</code><br>
                • <code>{product_name} &lt;br&gt; {product_attributes_list}</code> - название с атрибутами
            </p>
        </div>
        
        <div class="notice notice-success inline" style="margin: 15px 0; padding: 10px;">
            <p style="margin: 0;">
                <strong>✓ Поддержка HTML:</strong> Все поля поддерживают HTML теги. 
                Вы можете использовать теги форматирования: <code>&lt;br&gt;</code>, <code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;li&gt;</code> и другие безопасные HTML элементы.
            </p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('wc_avito_field_settings', 'wc_avito_fields_nonce'); ?>
            
            <!-- Глобальные поля -->
            <h2>Общие поля (для всех объявлений)</h2>
            <p>Эти поля будут использоваться для всех объявлений. Укажите значение, которое будет добавлено в XML.</p>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th width="5%">Вкл.</th>
                        <th width="20%">Название XML тега</th>
                        <th width="30%">Значение</th>
                        <th width="20%">Тип</th>
                        <th width="5%">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($settings['global_fields'])) {
                        foreach ($settings['global_fields'] as $index => $field): 
                            $field_value = isset($field['value']) ? $field['value'] : '';
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="global_fields[<?php echo $index; ?>][enabled]" value="1" <?php checked(!empty($field['enabled']), true); ?> />
                            </td>
                            <td>
                                <input type="text" name="global_fields[<?php echo $index; ?>][xml_tag]" value="<?php echo esc_attr(isset($field['xml_tag']) ? $field['xml_tag'] : $field['label']); ?>" class="regular-text" placeholder="ContactPhone" />
                                <input type="hidden" name="global_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" />
                                <input type="hidden" name="global_fields[<?php echo $index; ?>][key]" value="<?php echo esc_attr($field['key']); ?>" />
                            </td>
                            <td>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea name="global_fields[<?php echo $index; ?>][value]" rows="3" class="large-text" placeholder="Значение поля..." title="Значение, которое будет использоваться для всех объявлений"><?php echo esc_textarea($field_value); ?></textarea>
                                <?php elseif ($field['type'] === 'checkbox'): ?>
                                    <input type="checkbox" name="global_fields[<?php echo $index; ?>][value]" value="1" <?php checked($field_value, '1'); ?> title="Значение поля" />
                                <?php else: ?>
                                    <input type="<?php echo esc_attr($field['type']); ?>" name="global_fields[<?php echo $index; ?>][value]" value="<?php echo esc_attr($field_value); ?>" class="regular-text" placeholder="Значение поля..." title="Значение, которое будет использоваться для всех объявлений" />
                                <?php endif; ?>
                            </td>
                            <td>
                                <select name="global_fields[<?php echo $index; ?>][type]">
                                    <option value="text" <?php selected($field['type'], 'text'); ?>>Text</option>
                                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Textarea</option>
                                    <option value="number" <?php selected($field['type'], 'number'); ?>>Number</option>
                                    <option value="date" <?php selected($field['type'], 'date'); ?>>Date</option>
                                    <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Checkbox</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="button delete-field" aria-label="Удалить поле" data-section="global" data-index="<?php echo $index; ?>">×</button>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    }
                    ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button button-secondary add-field-btn" data-section="global">
                    <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span> Добавить поле
                </button>
            </p>
            
            <hr />
            
            <!-- Поля категорий -->
            <h2>Поля категорий WooCommerce</h2>
            <p>Эти поля будут доступны при редактировании категорий товаров. Общее значение используется, если поле не заполнено у категории.</p>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th width="5%">Вкл.</th>
                        <th width="20%">Название XML тега</th>
                        <th width="30%">Общее значение (по умолчанию)</th>
                        <th width="20%">Тип</th>
                        <th width="5%">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($settings['category_fields'])) {
                        foreach ($settings['category_fields'] as $index => $field): 
                            $default_value = isset($field['default_value']) ? $field['default_value'] : '';
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="category_fields[<?php echo $index; ?>][enabled]" value="1" <?php checked(!empty($field['enabled']), true); ?> />
                            </td>
                            <td>
                                <input type="text" name="category_fields[<?php echo $index; ?>][xml_tag]" value="<?php echo esc_attr(isset($field['xml_tag']) ? $field['xml_tag'] : $field['label']); ?>" class="regular-text" placeholder="Category" />
                                <input type="hidden" name="category_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" />
                                <input type="hidden" name="category_fields[<?php echo $index; ?>][key]" value="<?php echo esc_attr($field['key']); ?>" />
                            </td>
                            <td>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea name="category_fields[<?php echo $index; ?>][default_value]" rows="3" class="large-text" placeholder="Значение по умолчанию..." title="Используется, если поле не заполнено у категории"><?php echo esc_textarea($default_value); ?></textarea>
                                <?php elseif ($field['type'] === 'checkbox'): ?>
                                    <input type="checkbox" name="category_fields[<?php echo $index; ?>][default_value]" value="1" <?php checked($default_value, '1'); ?> title="Значение по умолчанию" />
                                <?php else: ?>
                                    <input type="<?php echo esc_attr($field['type']); ?>" name="category_fields[<?php echo $index; ?>][default_value]" value="<?php echo esc_attr($default_value); ?>" class="regular-text" placeholder="Значение по умолчанию..." title="Используется, если поле не заполнено у категории" />
                                <?php endif; ?>
                            </td>
                            <td>
                                <select name="category_fields[<?php echo $index; ?>][type]">
                                    <option value="text" <?php selected($field['type'], 'text'); ?>>Text</option>
                                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Textarea</option>
                                    <option value="number" <?php selected($field['type'], 'number'); ?>>Number</option>
                                    <option value="date" <?php selected($field['type'], 'date'); ?>>Date</option>
                                    <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Checkbox</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="button delete-field" aria-label="Удалить поле" data-section="category" data-index="<?php echo $index; ?>">×</button>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    }
                    ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button button-secondary add-field-btn" data-section="category">
                    <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span> Добавить поле
                </button>
            </p>
            
            <?php /* <hr />
            
            <!-- Поля товаров -->
            <h2>Поля товаров WooCommerce</h2>
            <p>Эти поля будут доступны при редактировании товаров. Общее значение используется, если поле не заполнено у товара.</p>
            
            <div class="notice notice-warning inline" style="margin: 10px 0; padding: 10px; background-color: #fffbf0; border-left: 4px solid #ffb900;">
                <p style="margin: 0;"><strong>⚠ Обязательные поля:</strong> <code>Title</code> (название объявления) и <code>AvitoExport</code> (контроль экспорта) не могут быть отключены или удалены. Чекбокс <code>AvitoExport</code> определяет, будет ли товар включен в XML файл для Avito.</p>
            </div>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th width="5%">Вкл.</th>
                        <th width="20%">Название XML тега</th>
                        <th width="30%">Общее значение (по умолчанию)</th>
                        <th width="20%">Тип</th>
                        <th width="5%">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($settings['product_fields'])) {
                        foreach ($settings['product_fields'] as $index => $field): 
                            $default_value = isset($field['default_value']) ? $field['default_value'] : '';
                            // Обязательные поля, которые нельзя отключить
                            $is_required = in_array($field['key'], array('avito_title', 'avito_export'));
                            $field_label = isset($field['xml_tag']) ? $field['xml_tag'] : $field['label'];
                    ?>
                        <tr<?php echo $is_required ? ' style="background-color: #fffbf0;"' : ''; ?>>
                            <td>
                                <?php if ($is_required): ?>
                                    <input type="checkbox" checked disabled title="Обязательное поле" />
                                    <input type="hidden" name="product_fields[<?php echo $index; ?>][enabled]" value="1" />
                                <?php else: ?>
                                    <input type="checkbox" name="product_fields[<?php echo $index; ?>][enabled]" value="1" <?php checked(!empty($field['enabled']), true); ?> />
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_required): ?>
                                    <strong style="color: #d63638;">⚠</strong>
                                <?php endif; ?>
                                <input type="text" name="product_fields[<?php echo $index; ?>][xml_tag]" value="<?php echo esc_attr($field_label); ?>" class="regular-text" placeholder="Title" <?php echo $is_required ? 'readonly style="background-color: #f0f0f0;"' : ''; ?> />
                                <input type="hidden" name="product_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" />
                                <input type="hidden" name="product_fields[<?php echo $index; ?>][key]" value="<?php echo esc_attr($field['key']); ?>" />
                            </td>
                            <td>
                                <?php if ($field['type'] === 'textarea'): ?>
                                    <textarea name="product_fields[<?php echo $index; ?>][default_value]" rows="3" class="large-text" placeholder="Значение по умолчанию..." title="Используется, если поле не заполнено у товара"><?php echo esc_textarea($default_value); ?></textarea>
                                <?php elseif ($field['type'] === 'checkbox'): ?>
                                    <input type="checkbox" name="product_fields[<?php echo $index; ?>][default_value]" value="1" <?php checked($default_value, '1'); ?> title="Значение по умолчанию" />
                                <?php else: ?>
                                    <input type="<?php echo esc_attr($field['type']); ?>" name="product_fields[<?php echo $index; ?>][default_value]" value="<?php echo esc_attr($default_value); ?>" class="regular-text" placeholder="Значение по умолчанию..." title="Используется, если поле не заполнено у товара" />
                                <?php endif; ?>
                            </td>
                            <td>
                                <select name="product_fields[<?php echo $index; ?>][type]" <?php echo $is_required ? 'disabled style="background-color: #f0f0f0;"' : ''; ?>>
                                    <option value="text" <?php selected($field['type'], 'text'); ?>>Text</option>
                                    <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Textarea</option>
                                    <option value="number" <?php selected($field['type'], 'number'); ?>>Number</option>
                                    <option value="date" <?php selected($field['type'], 'date'); ?>>Date</option>
                                    <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Checkbox</option>
                                </select>
                                <?php if ($is_required): ?>
                                    <input type="hidden" name="product_fields[<?php echo $index; ?>][type]" value="<?php echo esc_attr($field['type']); ?>" />
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_required): ?>
                                    <span style="color: #999; font-size: 12px;">Обязательное</span>
                                <?php else: ?>
                                    <button type="button" class="button delete-field" aria-label="Удалить поле" data-section="product" data-index="<?php echo $index; ?>">×</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    }
                    ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button button-secondary add-field-btn" data-section="product">
                    <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span> Добавить поле
                </button>
            </p>
            */ ?>
            
            <p class="submit">
                <input type="submit" name="save_field_settings" class="button button-primary" value="Сохранить настройки полей" />
            </p>
        </form>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Добавление нового поля
        $('.add-field-btn').on('click', function() {
            var section = $(this).data('section');
            var $button = $(this);
            
            // Ищем таблицу: сначала пробуем через parent().prev(), затем альтернативный способ
            var $table = $button.closest('p').prev('table');
            if ($table.length === 0) {
                // Альтернативный поиск по секции
                $table = $button.closest('form').find('table').eq(
                    section === 'global' ? 0 : (section === 'category' ? 1 : 2)
                );
            }
            
            var table = $table.find('tbody');
            var index = table.find('tr').length;
            
            var row = '';
            
            if (section === 'global') {
                // Для глобальных полей с колонкой value
                row = '<tr>' +
                    '<td><input type="checkbox" name="' + section + '_fields[' + index + '][enabled]" value="1" checked /></td>' +
                    '<td>' +
                        '<input type="text" name="' + section + '_fields[' + index + '][xml_tag]" value="" class="regular-text" placeholder="XMLTag" />' +
                        '<input type="hidden" name="' + section + '_fields[' + index + '][label]" value="" />' +
                        '<input type="hidden" name="' + section + '_fields[' + index + '][key]" value="" />' +
                    '</td>' +
                    '<td><input type="text" name="' + section + '_fields[' + index + '][value]" value="" class="regular-text" placeholder="Значение поля..." /></td>' +
                    '<td><select name="' + section + '_fields[' + index + '][type]">' +
                        '<option value="text">Text</option>' +
                        '<option value="textarea">Textarea</option>' +
                        '<option value="number">Number</option>' +
                        '<option value="date">Date</option>' +
                        '<option value="checkbox">Checkbox</option>' +
                    '</select></td>' +
                    '<td><button type="button" class="button delete-field" aria-label="Удалить поле" data-section="' + section + '" data-index="' + index + '">×</button></td>' +
                    '</tr>';
            } else {
                // Для категорий и товаров с колонкой default_value
                row = '<tr>' +
                    '<td><input type="checkbox" name="' + section + '_fields[' + index + '][enabled]" value="1" checked /></td>' +
                    '<td>' +
                        '<input type="text" name="' + section + '_fields[' + index + '][xml_tag]" value="" class="regular-text" placeholder="XMLTag" />' +
                        '<input type="hidden" name="' + section + '_fields[' + index + '][label]" value="" />' +
                        '<input type="hidden" name="' + section + '_fields[' + index + '][key]" value="" />' +
                    '</td>' +
                    '<td><input type="text" name="' + section + '_fields[' + index + '][default_value]" value="" class="regular-text" placeholder="Общее значение" /></td>' +
                    '<td><select name="' + section + '_fields[' + index + '][type]">' +
                        '<option value="text">Text</option>' +
                        '<option value="textarea">Textarea</option>' +
                        '<option value="number">Number</option>' +
                        '<option value="date">Date</option>' +
                        '<option value="checkbox">Checkbox</option>' +
                    '</select></td>' +
                    '<td><button type="button" class="button delete-field" aria-label="Удалить поле" data-section="' + section + '" data-index="' + index + '">×</button></td>' +
                    '</tr>';
            }
            
            table.append(row);
        });
        
        // Удаление поля
        $(document).on('click', '.delete-field', function() {
            if (confirm('Вы уверены, что хотите удалить это поле?')) {
                $(this).closest('tr').remove();
            }
        });
    });
    </script>
    
    <style>
    .widefat thead th {
        vertical-align: middle;
        padding: 12px;
    }
    
    .widefat tbody td {
        vertical-align: middle;
        padding: 12px;
    }
    
    .widefat input[type="text"],
    .widefat textarea,
    .widefat select {
        width: 100%;
        max-width: 100%;
    }
    
    .widefat textarea {
        resize: vertical;
        min-height: 60px;
    }
    
    .widefat input[type="checkbox"] {
        margin: 0;
        vertical-align: middle;
    }
    
    .widefat td:first-child,
    .widefat td:last-child {
        text-align: center;
    }
    
    .widefat th:first-child,
    .widefat th:last-child {
        text-align: center;
    }
    
    .delete-field {
        font-size: 24px;
        line-height: 1;
        color: #a00;
        padding: 0 8px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-weight: bold;
        display: inline-block;
    }
    
    .delete-field:hover {
        color: #dc3232;
        transform: scale(1.2);
    }
    
    .delete-field:focus {
        /* Restore focus for accessibility, using standard WP colors if needed */
        outline: 2px solid #2271b1;
        outline-offset: 2px;
        box-shadow: none;
    }
    
    .add-field-btn {
        margin-top: 10px !important;
    }
    
    h2 {
        margin-top: 30px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #0073aa;
    }
    
    .wrap > p {
        max-width: 800px;
        margin-bottom: 15px;
        color: #555;
    }
    
    hr {
        margin: 40px 0;
        border: none;
        border-top: 2px solid #ddd;
    }
    
    .submit {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }
    
    .widefat {
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    
    .widefat thead {
        background: linear-gradient(to bottom, #fcfcfc 0%, #f0f0f0 100%);
    }
    
    .widefat thead th {
        font-weight: 600;
        color: #333;
    }
    
    .widefat tbody tr:hover {
        background-color: #f5f9fc;
    }
    
    .widefat select {
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: #fff;
    }
    
    .widefat select:focus,
    .widefat input[type="text"]:focus,
    .widefat textarea:focus {
        border-color: #0073aa;
        box-shadow: 0 0 0 1px #0073aa;
        outline: none;
    }
    
    .button-primary {
        padding: 8px 20px !important;
        height: auto !important;
        font-size: 14px !important;
    }
    
    .button-secondary {
        padding: 6px 12px !important;
        height: auto !important;
    }
    
    .dashicons {
        width: 16px;
        height: 16px;
        font-size: 16px;
    }
    
    .widefat input::placeholder,
    .widefat textarea::placeholder {
        color: #999;
        font-style: italic;
    }
    
    .wrap h1 {
        margin-bottom: 20px;
    }
    </style>
    <?php
}

/**
 * Обработка сохранения настроек полей
 * Функция wc_avito_generate_field_key() объявлена в field-manager.php
 */
function wc_avito_handle_field_settings_save() {
    $settings = array(
        'global_fields' => array(),
        'category_fields' => array(),
        'product_fields' => array(),
    );
    
    // Обработка глобальных полей
    if (isset($_POST['global_fields'])) {
        foreach ($_POST['global_fields'] as $field) {
            $xml_tag = sanitize_text_field($field['xml_tag']);
            $existing_label = !empty($field['label']) ? sanitize_text_field($field['label']) : $xml_tag;
            $existing_key = !empty($field['key']) ? sanitize_text_field($field['key']) : '';
            // Разрешаем HTML теги в значении поля
            $field_value = isset($field['value']) ? wp_kses_post($field['value']) : '';
            
            $settings['global_fields'][] = array(
                'key' => wc_avito_generate_field_key($xml_tag, $existing_key),
                'label' => $existing_label,
                'xml_tag' => $xml_tag,
                'type' => sanitize_text_field($field['type']),
                'value' => $field_value,
                'enabled' => isset($field['enabled']) && $field['enabled'] == '1',
            );
        }
    }
    
    // Обработка полей категорий
    if (isset($_POST['category_fields'])) {
        foreach ($_POST['category_fields'] as $field) {
            $xml_tag = sanitize_text_field($field['xml_tag']);
            $existing_label = !empty($field['label']) ? sanitize_text_field($field['label']) : $xml_tag;
            $existing_key = !empty($field['key']) ? sanitize_text_field($field['key']) : '';
            // Разрешаем HTML теги в значении по умолчанию
            $default_value = isset($field['default_value']) ? wp_kses_post($field['default_value']) : '';
            
            $settings['category_fields'][] = array(
                'key' => wc_avito_generate_field_key($xml_tag, $existing_key),
                'label' => $existing_label,
                'xml_tag' => $xml_tag,
                'type' => sanitize_text_field($field['type']),
                'default_value' => $default_value,
                'enabled' => isset($field['enabled']) && $field['enabled'] == '1',
            );
        }
    }
    
    // Обработка полей товаров
    if (isset($_POST['product_fields'])) {
        foreach ($_POST['product_fields'] as $field) {
            $xml_tag = sanitize_text_field($field['xml_tag']);
            $existing_label = !empty($field['label']) ? sanitize_text_field($field['label']) : $xml_tag;
            $existing_key = !empty($field['key']) ? sanitize_text_field($field['key']) : '';
            // Разрешаем HTML теги в значении по умолчанию
            $default_value = isset($field['default_value']) ? wp_kses_post($field['default_value']) : '';
            
            $settings['product_fields'][] = array(
                'key' => wc_avito_generate_field_key($xml_tag, $existing_key),
                'label' => $existing_label,
                'xml_tag' => $xml_tag,
                'type' => sanitize_text_field($field['type']),
                'default_value' => $default_value,
                'enabled' => isset($field['enabled']) && $field['enabled'] == '1',
            );
        }
    }
    
    wc_avito_save_field_settings($settings);
}
