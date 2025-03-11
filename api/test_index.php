<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Выводим информацию о PHP
echo "<h1>PHP Тест</h1>";
echo "<p>PHP версия: " . phpversion() . "</p>";

// Показываем все переменные сервера
echo "<h2>Информация о сервере</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

// Проверка доступа к файлам
echo "<h2>Доступные файлы</h2>";
echo "<p>Текущая директория: " . __DIR__ . "</p>";

// Проверяем наличие файла index.html в public
$public_index = __DIR__ . '/public/index.html';
echo "<p>Файл public/index.html существует: " . (file_exists($public_index) ? 'Да' : 'Нет') . "</p>";

// Проверяем наличие файла index.html в api
$api_index = __DIR__ . '/index.html';
echo "<p>Файл index.html существует: " . (file_exists($api_index) ? 'Да' : 'Нет') . "</p>";

// Проверяем доступные файлы в директории public
if (is_dir(__DIR__ . '/public')) {
    echo "<p>Файлы в директории public:</p><ul>";
    foreach (scandir(__DIR__ . '/public') as $file) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
    echo "</ul>";
}

// Проверяем доступные файлы в директории api
echo "<p>Файлы в текущей директории:</p><ul>";
foreach (scandir(__DIR__) as $file) {
    echo "<li>" . htmlspecialchars($file) . "</li>";
}
echo "</ul>";

// Проверяем доступные директории от корня
$root_dir = dirname(__DIR__);
echo "<p>Файлы в корневой директории (" . $root_dir . "):</p><ul>";
foreach (scandir($root_dir) as $file) {
    echo "<li>" . htmlspecialchars($file) . "</li>";
}
echo "</ul>";
?>

<p><a href="/">Вернуться на главную страницу</a></p> 