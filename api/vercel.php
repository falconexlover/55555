<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Файл для обработки PHP-файлов на Vercel
// Этот файл нужен для того, чтобы Vercel правильно обрабатывал PHP-файлы

// Логирование запроса
error_log("Запрос к vercel.php: " . $_SERVER['REQUEST_URI']);

// Получаем запрошенный путь и удаляем начальные слеши
$path = ltrim($_SERVER['REQUEST_URI'], '/');

// Определяем базовый путь
$base_path = __DIR__;

// Если запрос к корню или index.html, показываем главную страницу
if (empty($path) || $path == 'index.html') {
    $index_file = $base_path . '/public/index.html';
    
    if (file_exists($index_file)) {
        header('Content-Type: text/html');
        error_log("Отдаем index.html из public");
        readfile($index_file);
        exit;
    } else {
        // Если файл public/index.html не найден, ищем в корне api
        $alternate_index = $base_path . '/index.html';
        
        if (file_exists($alternate_index)) {
            header('Content-Type: text/html');
            error_log("Отдаем index.html из корня api");
            readfile($alternate_index);
            exit;
        } else {
            // Генерируем страницу динамически
            error_log("Не найдены файлы index.html, генерируем HTML");
            generate_welcome_page();
            exit;
        }
    }
}

// Пытаемся найти файл в директории public
$file = $base_path . '/public/' . $path;
error_log("Ищем файл: " . $file);

if (file_exists($file) && !is_dir($file)) {
    // Определяем тип контента
    $mime_type = get_mime_type($file);
    header('Content-Type: ' . $mime_type);
    error_log("Отдаем файл из public: " . $file);
    readfile($file);
    exit;
}

// Проверяем в других директориях
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
    error_log("Проверяем в директории " . $dir . ": " . $file);
    
    if (file_exists($file) && !is_dir($file)) {
        // Определяем тип контента
        $mime_type = get_mime_type($file);
        header('Content-Type: ' . $mime_type);
        error_log("Отдаем файл из " . $dir . ": " . $file);
        readfile($file);
        exit;
    }
}

// Если это запрос к PHP файлу, обрабатываем его
if (preg_match('/\.php$/', $path)) {
    $php_file = $base_path . '/' . $path;
    error_log("Проверяем PHP файл: " . $php_file);
    
    if (file_exists($php_file) && !is_dir($php_file)) {
        error_log("Выполняем PHP файл: " . $php_file);
        include $php_file;
        exit;
    }
}

// Если файл не найден, показываем страницу 404
error_log("Файл не найден: " . $path);

$error_file = $base_path . '/404.html';
if (file_exists($error_file)) {
    header("HTTP/1.0 404 Not Found");
    readfile($error_file);
} else {
    generate_404_page($path);
}
exit;

// Функция для определения типа MIME
function get_mime_type($file) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    
    $mime_types = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'otf' => 'application/x-font-opentype'
    ];
    
    return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
}

// Функция для генерации страницы приветствия
function generate_welcome_page() {
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Гостиница "Лесной дворик"</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
            .container { background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
            h1 { color: #2c3e50; text-align: center; }
            .message { background-color: #e8f8f5; border-left: 5px solid #27ae60; padding: 15px; margin: 20px 0; }
            .btn { display: inline-block; background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
            .btn:hover { background-color: #2980b9; }
            .note { margin-top: 20px; font-size: 0.9em; color: #7f8c8d; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Гостиница "Лесной дворик"</h1>
            <div class="message">
                <h2>Добро пожаловать!</h2>
                <p>Наша гостиница предлагает комфортабельные номера и отличное обслуживание.</p>
                <p>Мы находимся в живописном месте, окруженном лесом и свежим воздухом.</p>
            </div>
            <div style="text-align: center;">
                <a href="/api/public/index.html" class="btn">Перейти на основную страницу</a>
            </div>
            <p class="note">Эта страница генерируется динамически, так как основные шаблоны не были найдены.</p>
        </div>
    </body>
    </html>';
}

// Функция для генерации страницы ошибки 404
function generate_404_page($path) {
    header("HTTP/1.0 404 Not Found");
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Страница не найдена - Гостиница "Лесной дворик"</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            .error-container {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                padding: 30px;
                margin-top: 50px;
                text-align: center;
            }
            h1 {
                color: #2c3e50;
                font-size: 2em;
            }
            .error-code {
                font-size: 5em;
                font-weight: bold;
                color: #e74c3c;
                margin: 20px 0;
            }
            .btn {
                display: inline-block;
                background-color: #3498db;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 20px;
            }
            .btn:hover {
                background-color: #2980b9;
            }
            .path {
                background-color: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                font-family: monospace;
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Страница не найдена</h1>
            <div class="error-code">404</div>
            <p>Извините, но запрошенная страница не существует.</p>
            <div class="path">Запрошенный путь: ' . htmlspecialchars($path) . '</div>
            <a href="/" class="btn">Вернуться на главную страницу</a>
        </div>
    </body>
    </html>';
}
?> 