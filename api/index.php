<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Определяем базовый путь
$base_path = __DIR__;

// Получаем запрошенный URI
$request_uri = $_SERVER['REQUEST_URI'];

// Для отладки
error_log("Request URI: " . $request_uri);
error_log("Base path: " . $base_path);

// Если запрос идет к index.html или корню, просто включаем index.html из директории public
if ($request_uri == '/' || $request_uri == '/index.html') {
    // Проверяем наличие файла
    if (file_exists($base_path . '/public/index.html')) {
        error_log("Serving public/index.html for root request");
        include $base_path . '/public/index.html';
        exit;
    } else {
        // Если public/index.html не существует, выводим информацию
        error_log("public/index.html not found, showing debug info");
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Отладка - Гостиница Лесной Дворик</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
                h1 { color: #336633; }
                pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
            </style>
        </head>
        <body>
            <h1>Информация об отладке корневого запроса</h1>
            <p>Запрошенный URI: " . htmlspecialchars($request_uri) . "</p>
            <p>Базовый путь: " . htmlspecialchars($base_path) . "</p>
            <h2>Доступные файлы в директории public:</h2>
            <pre>" . htmlspecialchars(print_r(scandir($base_path . '/public'), true)) . "</pre>
            <h2>Доступные файлы в корневой директории API:</h2>
            <pre>" . htmlspecialchars(print_r(scandir($base_path), true)) . "</pre>
        </body>
        </html>";
        exit;
    }
}

// Проверяем, существует ли запрошенный файл в директории public
$public_file_path = $base_path . '/public' . $request_uri;
error_log("Checking public file path: " . $public_file_path);

if (file_exists($public_file_path) && !is_dir($public_file_path)) {
    // Определяем MIME-тип на основе расширения файла
    $extension = pathinfo($public_file_path, PATHINFO_EXTENSION);
    switch ($extension) {
        case 'html':
            header('Content-Type: text/html');
            break;
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'json':
            header('Content-Type: application/json');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
        // Добавьте другие типы файлов по необходимости
    }
    
    error_log("Serving public file: " . $public_file_path);
    include $public_file_path;
    exit;
}

// Проверяем, существует ли запрошенный файл в корневой директории api
$file_path = $base_path . $request_uri;
error_log("Checking API file path: " . $file_path);

if (file_exists($file_path) && !is_dir($file_path)) {
    // Определяем MIME-тип на основе расширения файла
    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
    switch ($extension) {
        case 'html':
            header('Content-Type: text/html');
            break;
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'json':
            header('Content-Type: application/json');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
        // Добавьте другие типы файлов по необходимости
    }
    
    error_log("Serving API file: " . $file_path);
    include $file_path;
    exit;
}

// Если файл не найден, отправляем 404 и отладочную информацию
header("HTTP/1.0 404 Not Found");
error_log("File not found for URI: " . $request_uri);
echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Страница не найдена</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #d9534f; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>404 - Страница не найдена</h1>
    <p>Запрашиваемый URI: " . htmlspecialchars($request_uri) . "</p>
    <p>Путь к файлу в public: " . htmlspecialchars($public_file_path) . "</p>
    <p>Путь к файлу в API: " . htmlspecialchars($file_path) . "</p>
    <h2>Переменные сервера:</h2>
    <pre>" . htmlspecialchars(print_r($_SERVER, true)) . "</pre>
    <h2>Доступные файлы в директории public:</h2>
    <pre>" . htmlspecialchars(print_r(scandir($base_path . '/public'), true)) . "</pre>
    <h2>Доступные файлы в корневой директории API:</h2>
    <pre>" . htmlspecialchars(print_r(scandir($base_path), true)) . "</pre>
</body>
</html>";
