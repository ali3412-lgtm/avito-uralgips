<?php
/**
 * Функционал административного меню плагина
 *
 * @package WC_Avito_VDOM
 */

// Если этот файл вызван напрямую, прерываем выполнение
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавляем пункт меню в админ-панель
 */
add_action('admin_menu', 'wc_avito_xml_menu');

function wc_avito_xml_menu() {
    add_menu_page(
        'WC to Avito XML',
        'WC to Avito XML',
        'manage_options',
        'wc-avito-xml',
        'wc_avito_xml_page',
        'dashicons-media-spreadsheet',
        56
    );
}

/**
 * Страница генерации XML
 */
function wc_avito_xml_page() {
    if (isset($_POST['save_settings'])) {
        // Сохранение настроек экспорта товаров
        update_option('wc_avito_xml_enable_products', isset($_POST['wc_avito_xml_enable_products']) ? '1' : '0');
        
        // Настройка индивидуального контроля экспорта товаров
        update_option('wc_avito_individual_product_export', isset($_POST['wc_avito_individual_product_export']) ? '1' : '0');
        
        // Настройка пропуска товаров без изображений
        update_option('wc_avito_skip_products_without_images', isset($_POST['wc_avito_skip_products_without_images']) ? '1' : '0');
        
        // Настройка пропуска товаров без краткого описания
        update_option('wc_avito_skip_products_without_excerpt', isset($_POST['wc_avito_skip_products_without_excerpt']) ? '1' : '0');

        // Настройки расписания
        update_option('wc_avito_xml_schedule_enabled', isset($_POST['wc_avito_xml_schedule_enabled']) ? '1' : '0');
        update_option('wc_avito_xml_schedule_interval', sanitize_text_field($_POST['wc_avito_xml_schedule_interval']));
        
        // Настройки логирования и уведомлений
        update_option('wc_avito_xml_enable_logging', isset($_POST['wc_avito_xml_enable_logging']) ? '1' : '0');
        update_option('wc_avito_xml_notify_errors', isset($_POST['wc_avito_xml_notify_errors']) ? '1' : '0');
        
        // Настройки замены символов
        $character_replacements = array();
        if (isset($_POST['wc_avito_char_search']) && isset($_POST['wc_avito_char_replace'])) {
            $searches = $_POST['wc_avito_char_search'];
            $replaces = $_POST['wc_avito_char_replace'];
            
            for ($i = 0; $i < count($searches); $i++) {
                $search = isset($searches[$i]) ? $searches[$i] : '';
                $replace = isset($replaces[$i]) ? $replaces[$i] : '';
                
                // Сохраняем только непустые правила поиска
                if (!empty($search)) {
                    $character_replacements[] = array(
                        'search' => $search,
                        'replace' => $replace
                    );
                }
            }
        }
        update_option('wc_avito_character_replacements', $character_replacements);
        
        // Настройки исключаемых атрибутов
        $excluded_attributes = isset($_POST['wc_avito_excluded_attributes']) ? array_map('sanitize_text_field', $_POST['wc_avito_excluded_attributes']) : array();
        update_option('wc_avito_excluded_attributes', $excluded_attributes);

        echo '<div class="updated"><p>Настройки сохранены.</p></div>';
    }

    if (isset($_POST['generate_xml']) && check_admin_referer('generate_avito_xml', 'wc_avito_xml_nonce')) {
        generate_avito_xml();
    }
    
    if (isset($_POST['clear_logs']) && check_admin_referer('clear_avito_logs', 'wc_avito_logs_nonce')) {
        wc_avito_xml_clear_cron_logs();
        echo '<div class="updated"><p>Логи очищены.</p></div>';
    }

    // HTML код страницы настроек
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="">
            <h2>Настройки экспорта</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Активировать экспорт товаров</th>
                    <td><input type="checkbox" name="wc_avito_xml_enable_products" value="1" <?php checked(get_option('wc_avito_xml_enable_products', '1'), '1'); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Индивидуальный контроль экспорта</th>
                    <td>
                        <input type="checkbox" name="wc_avito_individual_product_export" value="1" <?php checked(get_option('wc_avito_individual_product_export', '0'), '1'); ?> />
                        <p class="description">
                            Если включено, для каждого товара потребуется отдельно установить флаг "Экспорт на Avito" на странице редактирования товара.<br>
                            <strong>Режим работы:</strong><br>
                            • <strong>Выключен</strong> (по умолчанию): Экспортируются все опубликованные товары<br>
                            • <strong>Включен</strong>: Экспортируются только товары с установленным флагом "Экспорт на Avito"
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Не формировать объявления без изображений</th>
                    <td>
                        <input type="checkbox" name="wc_avito_skip_products_without_images" value="1" <?php checked(get_option('wc_avito_skip_products_without_images', '0'), '1'); ?> />
                        <p class="description">
                            Если включено, товары без изображений не будут добавлены в XML-файл для Avito.
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Не формировать объявления без краткого описания</th>
                    <td>
                        <input type="checkbox" name="wc_avito_skip_products_without_excerpt" value="1" <?php checked(get_option('wc_avito_skip_products_without_excerpt', '0'), '1'); ?> />
                        <p class="description">
                            Если включено, товары без краткого описания не будут добавлены в XML-файл для Avito.
                        </p>
                    </td>
                </tr>
            </table>

            <h2>Общие настройки</h2>
            <p>Для управления полями XML перейдите в раздел <a href="<?php echo admin_url('admin.php?page=wc-avito-fields'); ?>"><strong>Поля XML</strong></a></p>

            <h2>Настройки автоматической генерации</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Автоматическая генерация XML</th>
                    <td>
                        <input type="checkbox" name="wc_avito_xml_schedule_enabled" value="1" <?php checked(get_option('wc_avito_xml_schedule_enabled', '1'), '1'); ?> />
                        <p class="description">Включить автоматическую генерацию XML по расписанию</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Интервал генерации XML</th>
                    <td>
                        <select name="wc_avito_xml_schedule_interval">
                            <option value="fifteen_minutes" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'fifteen_minutes'); ?>>Каждые 15 минут</option>
                            <option value="thirty_minutes" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'thirty_minutes'); ?>>Каждые 30 минут</option>
                            <option value="hourly" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'hourly'); ?>>Каждый час</option>
                            <option value="two_hours" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'two_hours'); ?>>Каждые 2 часа</option>
                            <option value="four_hours" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'four_hours'); ?>>Каждые 4 часа</option>
                            <option value="six_hours" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'six_hours'); ?>>Каждые 6 часов</option>
                            <option value="twelve_hours" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'twelve_hours'); ?>>Каждые 12 часов</option>
                            <option value="daily" <?php selected(get_option('wc_avito_xml_schedule_interval', 'thirty_minutes'), 'daily'); ?>>Ежедневно</option>
                        </select>
                        <p class="description">Как часто генерировать XML файл</p>
                    </td>
                </tr>
            </table>

            <h2>Настройки логирования и уведомлений</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Включить логирование</th>
                    <td>
                        <input type="checkbox" name="wc_avito_xml_enable_logging" value="1" <?php checked(get_option('wc_avito_xml_enable_logging', '1'), '1'); ?> />
                        <p class="description">Записывать логи выполнения cron-задач</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Уведомления об ошибках</th>
                    <td>
                        <input type="checkbox" name="wc_avito_xml_notify_errors" value="1" <?php checked(get_option('wc_avito_xml_notify_errors', '0'), '1'); ?> />
                        <p class="description">Отправлять email-уведомления администратору при ошибках</p>
                    </td>
                </tr>
            </table>

            <h2>Замена символов</h2>
            <p class="description">Укажите символы или текст, которые нужно заменить при генерации названий и описаний объявлений.</p>
            <table class="form-table" id="wc-avito-char-replacements-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Искомый текст</th>
                        <th style="width: 40%;">Заменить на</th>
                        <th style="width: 20%;"></th>
                    </tr>
                </thead>
                <tbody id="wc-avito-char-replacements-body">
                    <?php
                    $replacements = get_option('wc_avito_character_replacements', array());
                    if (empty($replacements)) {
                        $replacements = array(array('search' => '', 'replace' => ''));
                    }
                    foreach ($replacements as $index => $rule) :
                    ?>
                    <tr class="wc-avito-char-replacement-row">
                        <td>
                            <input type="text" name="wc_avito_char_search[]" value="<?php echo esc_attr($rule['search']); ?>" class="regular-text" placeholder="Например: ®" />
                        </td>
                        <td>
                            <input type="text" name="wc_avito_char_replace[]" value="<?php echo esc_attr($rule['replace']); ?>" class="regular-text" placeholder="Например: (R)" />
                        </td>
                        <td>
                            <button type="button" class="button wc-avito-remove-char-row">Удалить</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button button-secondary" id="wc-avito-add-char-row">+ Добавить правило замены</button>
            </p>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Добавление новой строки
                $('#wc-avito-add-char-row').on('click', function() {
                    var newRow = '<tr class="wc-avito-char-replacement-row">' +
                        '<td><input type="text" name="wc_avito_char_search[]" value="" class="regular-text" placeholder="Например: ®" /></td>' +
                        '<td><input type="text" name="wc_avito_char_replace[]" value="" class="regular-text" placeholder="Например: (R)" /></td>' +
                        '<td><button type="button" class="button wc-avito-remove-char-row">Удалить</button></td>' +
                        '</tr>';
                    $('#wc-avito-char-replacements-body').append(newRow);
                });
                
                // Удаление строки
                $(document).on('click', '.wc-avito-remove-char-row', function() {
                    var rows = $('.wc-avito-char-replacement-row').length;
                    if (rows > 1) {
                        $(this).closest('tr').remove();
                    } else {
                        // Если это последняя строка, просто очищаем поля
                        $(this).closest('tr').find('input').val('');
                    }
                });
            });
            </script>

            <h2>Исключение атрибутов</h2>
            <p class="description">Выберите атрибуты товаров, которые НЕ будут включаться в <code>{product_attributes_list}</code>.</p>
            
            <?php
            $all_attributes = wc_get_attribute_taxonomies();
            $excluded_attributes = get_option('wc_avito_excluded_attributes', array());
            $total_count = count($all_attributes);
            $excluded_count = count($excluded_attributes);
            
            if (!empty($all_attributes)) :
            ?>
            <div style="margin-bottom: 15px;">
                <input type="text" id="wc-avito-attr-search" placeholder="🔍 Поиск атрибутов..." style="width: 300px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                <span style="margin-left: 15px; color: #666;">
                    Исключено: <strong id="wc-avito-excluded-count"><?php echo esc_html($excluded_count); ?></strong> из <?php echo esc_html($total_count); ?>
                </span>
            </div>
            
            <div style="margin-bottom: 10px;">
                <button type="button" class="button" id="wc-avito-attr-select-all">Выбрать все</button>
                <button type="button" class="button" id="wc-avito-attr-deselect-all">Снять все</button>
            </div>
            
            <div id="wc-avito-attributes-container" style="max-height: 350px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px; background: #fafafa;">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px;">
                    <?php foreach ($all_attributes as $attribute) :
                        $attr_name = 'pa_' . $attribute->attribute_name;
                        $checked = in_array($attr_name, $excluded_attributes) ? 'checked' : '';
                    ?>
                    <label class="wc-avito-attr-label" style="display: flex; align-items: center; padding: 6px 10px; background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; cursor: pointer; transition: background 0.2s;" data-name="<?php echo esc_attr(mb_strtolower($attribute->attribute_label)); ?>">
                        <input type="checkbox" name="wc_avito_excluded_attributes[]" value="<?php echo esc_attr($attr_name); ?>" <?php echo $checked; ?> style="margin-right: 8px;" />
                        <span style="font-size: 13px;"><?php echo esc_html($attribute->attribute_label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <p class="description" style="margin-top: 10px;">Отмеченные атрибуты не будут отображаться при использовании плейсхолдера <code>{product_attributes_list}</code>.</p>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Поиск атрибутов
                $('#wc-avito-attr-search').on('input', function() {
                    var searchText = $(this).val().toLowerCase();
                    $('.wc-avito-attr-label').each(function() {
                        var attrName = $(this).data('name');
                        if (attrName.indexOf(searchText) !== -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
                
                // Обновление счётчика
                function updateExcludedCount() {
                    var count = $('#wc-avito-attributes-container input[type="checkbox"]:checked').length;
                    $('#wc-avito-excluded-count').text(count);
                }
                
                // Выбрать все (только видимые)
                $('#wc-avito-attr-select-all').on('click', function() {
                    $('.wc-avito-attr-label:visible input[type="checkbox"]').prop('checked', true);
                    updateExcludedCount();
                });
                
                // Снять все (только видимые)
                $('#wc-avito-attr-deselect-all').on('click', function() {
                    $('.wc-avito-attr-label:visible input[type="checkbox"]').prop('checked', false);
                    updateExcludedCount();
                });
                
                // Обновляем счётчик при изменении чекбоксов
                $('#wc-avito-attributes-container').on('change', 'input[type="checkbox"]', function() {
                    updateExcludedCount();
                });
                
                // Hover эффект для лейблов
                $('.wc-avito-attr-label').hover(
                    function() { $(this).css('background', '#f0f7ff'); },
                    function() { $(this).css('background', '#fff'); }
                );
            });
            </script>
            <?php else : ?>
            <p>Атрибуты товаров не найдены.</p>
            <?php endif; ?>

            <?php submit_button('Сохранить настройки', 'primary', 'save_settings'); ?>
        </form>

        <h2>Генерация XML</h2>
        <form method="post" action="">
            <?php wp_nonce_field('generate_avito_xml', 'wc_avito_xml_nonce'); ?>
            <p>Нажмите кнопку ниже, чтобы сгенерировать XML-файл для Avito.</p>
            <?php submit_button('Сгенерировать XML', 'secondary', 'generate_xml'); ?>
        </form>

        <?php
        // Добавляем ссылки на сгенерированные файлы
        $upload_dir = wp_upload_dir();
        
        // XML файл
        $xml_file_path = $upload_dir['basedir'] . '/avito_products.xml';
        $xml_file_url = $upload_dir['baseurl'] . '/avito_products.xml';

        if (file_exists($xml_file_path)) {
            echo '<h2>Сгенерированный XML-файл</h2>';
            $xml_file_time = filemtime($xml_file_path);
            echo '<p>Последнее обновление: ' . date('Y-m-d H:i:s', $xml_file_time) . '</p>';
            echo '<p><a href="' . esc_url($xml_file_url) . '" target="_blank" class="button">Скачать XML-файл</a></p>';
        }
        
        // Информация о расписании cron-задач
        echo '<h2>Информация о расписании</h2>';
        $cron_info = wc_avito_xml_get_next_cron_info();
        
        if (!empty($cron_info)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Тип</th><th>Статус</th><th>Расписание</th><th>Следующий запуск</th><th>Последний запуск</th></tr></thead>';
            echo '<tbody>';
            
            // XML
            if (isset($cron_info['xml'])) {
                $xml_last_run = get_option('wc_avito_xml_last_cron_run', 'Никогда');
                echo '<tr>';
                echo '<td><strong>XML генерация</strong></td>';
                echo '<td>' . ($cron_info['xml']['enabled'] ? '<span style="color: green;">Включено</span>' : '<span style="color: red;">Отключено</span>') . '</td>';
                echo '<td>' . esc_html($cron_info['xml']['schedule']) . '</td>';
                echo '<td>' . esc_html($cron_info['xml']['next_run']) . '</td>';
                echo '<td>' . esc_html($xml_last_run) . '</td>';
                echo '</tr>';
            } else {
                echo '<tr>';
                echo '<td><strong>XML генерация</strong></td>';
                echo '<td><span style="color: red;">Не запланировано</span></td>';
                echo '<td>-</td>';
                echo '<td>-</td>';
                echo '<td>-</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Нет активных cron-задач.</p>';
        }
        
        // Логи cron-задач
        if (get_option('wc_avito_xml_enable_logging', '1') === '1') {
            echo '<h2>Логи выполнения</h2>';
            $logs = get_option('wc_avito_xml_cron_logs', array());
            
            if (!empty($logs)) {
                // Показываем последние 20 записей
                $recent_logs = array_slice(array_reverse($logs), 0, 20);
                
                echo '<div style="background: #f1f1f1; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;">';
                foreach ($recent_logs as $log) {
                    $level_color = '';
                    switch ($log['level']) {
                        case 'error':
                            $level_color = 'color: red;';
                            break;
                        case 'warning':
                            $level_color = 'color: orange;';
                            break;
                        case 'info':
                            $level_color = 'color: blue;';
                            break;
                    }
                    echo '<div style="margin-bottom: 5px;">';
                    echo '<span style="color: #666;">[' . esc_html($log['timestamp']) . ']</span> ';
                    echo '<span style="' . $level_color . '">[' . strtoupper(esc_html($log['level'])) . ']</span> ';
                    echo esc_html($log['message']);
                    echo '</div>';
                }
                echo '</div>';
                
                // Кнопка очистки логов
                echo '<form method="post" action="" style="margin-top: 10px;">';
                wp_nonce_field('clear_avito_logs', 'wc_avito_logs_nonce');
                submit_button('Очистить логи', 'delete', 'clear_logs', false);
                echo '</form>';
            } else {
                echo '<p>Логи пусты.</p>';
            }
        }
        ?>
    </div>
    <?php
}
