<?php
// Файл для прямого обслуживания статических файлов на Vercel

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Получаем запрошенный путь и удаляем начальные слеши
$path = ltrim($_SERVER['REQUEST_URI'], '/');

// Определяем базовый путь
$base_path = __DIR__;

// Логирование для отладки
error_log("Static serve request: " . $path);

// Если запрос к корню, отдаем public/index.html
if (empty($path) || $path == 'index.html') {
    $file = $base_path . '/public/index.html';
    
    if (file_exists($file)) {
        header('Content-Type: text/html');
        error_log("Serving: " . $file);
        readfile($file);
        exit;
    }
}

// Пытаемся найти файл в директории public
$file = $base_path . '/public/' . $path;

// Логирование для отладки
error_log("Looking for file: " . $file);

if (file_exists($file) && !is_dir($file)) {
    // Определяем MIME-тип на основе расширения файла
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    
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
        case 'ico':
            header('Content-Type: image/x-icon');
            break;
        case 'pdf':
            header('Content-Type: application/pdf');
            break;
        default:
            header('Content-Type: application/octet-stream');
    }
    
    error_log("Serving file: " . $file);
    readfile($file);
    exit;
}

// Проверяем в других директориях (css, js, assets)
$possible_directories = [
    '/css/',
    '/js/',
    '/assets/',
    '/assets/images/',
    '/assets/fonts/',
    '/pages/'
];

foreach ($possible_directories as $dir) {
    $file = $base_path . $dir . $path;
    error_log("Checking in directory " . $dir . ": " . $file);
    
    if (file_exists($file) && !is_dir($file)) {
        // Определяем MIME-тип на основе расширения файла
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
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
            case 'ico':
                header('Content-Type: image/x-icon');
                break;
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'woff':
                header('Content-Type: font/woff');
                break;
            case 'woff2':
                header('Content-Type: font/woff2');
                break;
            case 'ttf':
                header('Content-Type: font/ttf');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }
        
        error_log("Serving file from " . $dir . ": " . $file);
        readfile($file);
        exit;
    }
}

// Если файл не найден, отдаем наш пользовательский 404.html
header('HTTP/1.0 404 Not Found');
$error_file = $base_path . '/404.html';

if (file_exists($error_file)) {
    error_log("Serving 404 page: " . $error_file);
    readfile($error_file);
} else {
    // Запасной вариант, если 404.html не найден
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>404 - Файл не найден</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            h1 { color: #d9534f; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        </style>
    </head>
    <body>
        <h1>404 - Файл не найден</h1>
        <p>Запрошенный файл не найден: " . htmlspecialchars($path) . "</p>
        <p><a href='/'>Вернуться на главную страницу</a></p>
    </body>
    </html>";
}

error_log("File not found: " . $path); 