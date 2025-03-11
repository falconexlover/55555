<?php
// Файл для отладки проблем в деплое на Vercel
// ВНИМАНИЕ: Этот файл следует удалить или защитить паролем в продакшене!

// Установка заголовка для вывода в формате text/plain
header('Content-Type: text/plain');

// Проверка, включен ли режим отладки через переменную окружения
$debug_enabled = getenv('DEBUG') === 'true';

if (!$debug_enabled) {
    echo "Отладка отключена. Установите переменную окружения DEBUG=true для просмотра информации.\n";
    exit;
}

echo "=== ОТЛАДОЧНАЯ ИНФОРМАЦИЯ VERCEL ===\n\n";

// Информация о PHP
echo "== PHP ==\n";
echo "PHP версия: " . phpversion() . "\n";
echo "Модули PHP: " . implode(", ", get_loaded_extensions()) . "\n\n";

// Переменные окружения
echo "== ПЕРЕМЕННЫЕ ОКРУЖЕНИЯ ==\n";
$env_vars = [
    'VERCEL_ENV', 'VERCEL_URL', 'VERCEL_REGION',
    'PHP_VERSION', 'PHP_MEMORY_LIMIT', 'DEBUG'
];

foreach ($env_vars as $var) {
    echo "$var: " . (getenv($var) ?: 'не установлено') . "\n";
}
echo "\n";

// Текущие пути
echo "== ПУТИ ==\n";
echo "Корневой путь: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Текущий скрипт: " . __FILE__ . "\n";
echo "Базовый путь API: " . dirname(__FILE__) . "\n\n";

// Структура директорий
echo "== СТРУКТУРА ДИРЕКТОРИЙ ==\n";
function list_directory($path, $indent = 0) {
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo str_repeat("  ", $indent) . $file;
            if (is_dir($path . '/' . $file)) {
                echo "/\n";
                if ($indent < 2) { // Ограничение глубины для экономии вывода
                    list_directory($path . '/' . $file, $indent + 1);
                }
            } else {
                echo " (" . filesize($path . '/' . $file) . " байт)\n";
            }
        }
    }
}

echo "Корневая директория API:\n";
list_directory(__DIR__);
echo "\n";

// Проверка наличия ключевых файлов
echo "== ПРОВЕРКА КЛЮЧЕВЫХ ФАЙЛОВ ==\n";
$key_files = [
    __DIR__ . '/vercel.php',
    __DIR__ . '/index.php',
    __DIR__ . '/public/index.html'
];

foreach ($key_files as $file) {
    echo basename($file) . ": " . (file_exists($file) ? "НАЙДЕН" : "ОТСУТСТВУЕТ") . "\n";
}
echo "\n";

// Информация о запросе
echo "== ИНФОРМАЦИЯ О ЗАПРОСЕ ==\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'не установлено') . "\n";
echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n\n";

// Заголовки запроса
echo "== ЗАГОЛОВКИ ЗАПРОСА ==\n";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}

echo "\n=== КОНЕЦ ОТЛАДОЧНОЙ ИНФОРМАЦИИ ===\n"; 