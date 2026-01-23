<?php
/**
 * Тестовый файл для проверки функции условий генерации полей
 *
 * Этот файл можно удалить после тестирования
 *
 * Для запуска теста добавьте этот код в functions.php вашей темы:
 * require_once WP_PLUGIN_DIR . '/avito-uralgips/test-conditions.php';
 */

// Защита от прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Тестирование функции проверки условий
 */
function test_wc_avito_conditions() {
    echo "<h2>Тестирование условий генерации полей</h2>";

    // Имитация данных товара
    $test_data = array(
        'product_name' => 'Кирпич керамический М-150',
        'product_price' => '1500',
        'category_name' => 'Керамзит',
        'product_sku' => 'BRICK-001',
        'meta_stock_status' => 'instock',
    );

    // Тестовые условия
    $test_conditions = array(
        // Тест 1: Равенство строк
        array(
            'condition' => '{category_name}=Керамзит',
            'expected' => true,
            'description' => 'Проверка равенства категории "Керамзит"'
        ),

        // Тест 2: Неравенство строк
        array(
            'condition' => '{category_name}!=Кирпич',
            'expected' => true,
            'description' => 'Проверка неравенства категории "Кирпич"'
        ),

        // Тест 3: Числовое сравнение (больше)
        array(
            'condition' => '{product_price}>1000',
            'expected' => true,
            'description' => 'Проверка цены больше 1000'
        ),

        // Тест 4: Числовое сравнение (меньше)
        array(
            'condition' => '{product_price}<2000',
            'expected' => true,
            'description' => 'Проверка цены меньше 2000'
        ),

        // Тест 5: Contains (содержит)
        array(
            'condition' => '{product_name} contains кирпич',
            'expected' => true,
            'description' => 'Проверка что название содержит "кирпич"'
        ),

        // Тест 6: Not contains (не содержит)
        array(
            'condition' => '{product_name} !contains песок',
            'expected' => true,
            'description' => 'Проверка что название не содержит "песок"'
        ),

        // Тест 7: Больше или равно
        array(
            'condition' => '{product_price}>=1500',
            'expected' => true,
            'description' => 'Проверка цены больше или равно 1500'
        ),

        // Тест 8: Меньше или равно
        array(
            'condition' => '{product_price}<=1500',
            'expected' => true,
            'description' => 'Проверка цены меньше или равно 1500'
        ),

        // Негативные тесты
        array(
            'condition' => '{category_name}=Кирпич',
            'expected' => false,
            'description' => 'Негативный тест: категория не равна "Кирпич"'
        ),

        array(
            'condition' => '{product_price}>2000',
            'expected' => false,
            'description' => 'Негативный тест: цена не больше 2000'
        ),
    );

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<thead><tr>";
    echo "<th>№</th>";
    echo "<th>Описание теста</th>";
    echo "<th>Условие</th>";
    echo "<th>Ожидаемый результат</th>";
    echo "<th>Фактический результат</th>";
    echo "<th>Статус</th>";
    echo "</tr></thead><tbody>";

    $passed = 0;
    $failed = 0;

    foreach ($test_conditions as $index => $test) {
        $num = $index + 1;

        // Заменяем плейсхолдеры на тестовые данные
        $processed_condition = str_replace(
            array(
                '{product_name}',
                '{product_price}',
                '{category_name}',
                '{product_sku}',
                '{meta:_stock_status}'
            ),
            array(
                $test_data['product_name'],
                $test_data['product_price'],
                $test_data['category_name'],
                $test_data['product_sku'],
                $test_data['meta_stock_status']
            ),
            $test['condition']
        );

        // Простая имитация функции проверки (без загрузки плагина)
        $result = evaluate_condition($processed_condition);

        $status = ($result === $test['expected']) ? '✅ PASSED' : '❌ FAILED';
        $status_color = ($result === $test['expected']) ? 'green' : 'red';

        if ($result === $test['expected']) {
            $passed++;
        } else {
            $failed++;
        }

        echo "<tr>";
        echo "<td>$num</td>";
        echo "<td>{$test['description']}</td>";
        echo "<td><code>{$test['condition']}</code></td>";
        echo "<td>" . ($test['expected'] ? 'true' : 'false') . "</td>";
        echo "<td>" . ($result ? 'true' : 'false') . "</td>";
        echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";

    echo "<h3>Итого: ✅ Успешно: $passed | ❌ Провалено: $failed</h3>";
}

/**
 * Упрощенная функция проверки условия для теста
 * (имитирует wc_avito_check_field_condition без загрузки WordPress)
 */
function evaluate_condition($condition) {
    $operators = array('!contains', 'contains', '!=', '>=', '<=', '=', '>', '<');

    foreach ($operators as $op) {
        $pos = strpos($condition, $op);
        if ($pos !== false) {
            $left = trim(substr($condition, 0, $pos));
            $right = trim(substr($condition, $pos + strlen($op)));

            switch ($op) {
                case '=':
                    return $left === $right;
                case '!=':
                    return $left !== $right;
                case '>':
                    return is_numeric($left) && is_numeric($right) && floatval($left) > floatval($right);
                case '<':
                    return is_numeric($left) && is_numeric($right) && floatval($left) < floatval($right);
                case '>=':
                    return is_numeric($left) && is_numeric($right) && floatval($left) >= floatval($right);
                case '<=':
                    return is_numeric($left) && is_numeric($right) && floatval($left) <= floatval($right);
                case 'contains':
                    return stripos($left, $right) !== false;
                case '!contains':
                    return stripos($left, $right) === false;
            }
        }
    }

    return false;
}

// Регистрируем хук для админки (можно вызвать из браузера)
add_action('admin_notices', function() {
    if (isset($_GET['test_avito_conditions']) && current_user_can('manage_options')) {
        test_wc_avito_conditions();
    }
});
