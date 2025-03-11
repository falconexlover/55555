<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Логирование запроса
error_log("Запрос к index.php: " . $_SERVER['REQUEST_URI']);

// Путь к файлу index.html
$index_file = __DIR__ . '/public/index.html';

// Проверяем, существует ли файл
if (file_exists($index_file)) {
    // Выводим содержимое файла
    header('Content-Type: text/html');
    readfile($index_file);
    exit;
} else {
    // Если файл не найден, пробуем другой вариант
    $alternate_index = __DIR__ . '/index.html';
    
    if (file_exists($alternate_index)) {
        header('Content-Type: text/html');
        readfile($alternate_index);
        exit;
    } else {
        // Если и этот файл не найден, выводим запасную страницу
        header('Content-Type: text/html');
        echo '<!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Гостиница "Лесной дворик"</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 5px; }
                h1 { color: #2c3e50; }
                .message { background: #e8f8f5; padding: 15px; border-left: 5px solid #27ae60; margin: 20px 0; }
                .btn { display: inline-block; background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Гостиница "Лесной дворик"</h1>
                <div class="message">
                    <h2>Добро пожаловать!</h2>
                    <p>Наша гостиница предлагает комфортабельные номера и отличное обслуживание.</p>
                </div>
                <p>Если вы видите эту страницу, значит основной шаблон не был загружен.</p>
                <p><a href="/api/public/index.html" class="btn">Перейти на основную страницу</a></p>
            </div>
        </body>
        </html>';
    }
}

// Логирование завершения запроса
error_log("Запрос к index.php завершен");
?>
