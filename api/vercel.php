<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Файл для обработки PHP-файлов на Vercel
// Этот файл нужен для того, чтобы Vercel правильно обрабатывал PHP-файлы

// Получаем путь к запрошенному файлу
$path = $_SERVER['REQUEST_URI'];

// Логирование для отладки
error_log("Запрос: " . $path);

// Если запрос идет к корню, перенаправляем на index.php или public/index.html
if ($path === '/' || $path === '') {
    if (file_exists(__DIR__ . '/public/index.html')) {
        include __DIR__ . '/public/index.html';
        exit;
    } elseif (file_exists(__DIR__ . '/index.php')) {
        include __DIR__ . '/index.php';
        exit;
    } else {
        // Если нет ни index.php, ни public/index.html, выводим информационную страницу
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Гостиница Лесной Дворик - Информация</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
                h1 { color: #336633; }
                pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
            </style>
        </head>
        <body>
            <h1>Информация о Vercel PHP</h1>
            <p>Запрашиваемый путь: " . htmlspecialchars($path) . "</p>
            <h2>Переменные сервера:</h2>
            <pre>" . htmlspecialchars(print_r($_SERVER, true)) . "</pre>
            <h2>Доступные файлы:</h2>
            <pre>" . htmlspecialchars(print_r(scandir(__DIR__), true)) . "</pre>
        </body>
        </html>";
        exit;
    }
}

// Удаляем начальный слеш
$path = ltrim($path, '/');

// Проверяем, существует ли запрошенный файл
$file = __DIR__ . '/' . $path;
error_log("Проверяем файл: " . $file);

if (file_exists($file) && !is_dir($file)) {
    // Если файл существует, определяем его MIME-тип
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
    }
    
    // Включаем файл напрямую
    error_log("Подключаем файл: " . $file);
    include $file;
    exit;
}

// Проверяем, существует ли запрошенный файл в директории public
$publicFile = __DIR__ . '/public/' . $path;
error_log("Проверяем файл в public: " . $publicFile);

if (file_exists($publicFile) && !is_dir($publicFile)) {
    // Если файл существует, определяем его MIME-тип
    $extension = pathinfo($publicFile, PATHINFO_EXTENSION);
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
    }
    
    // Включаем файл напрямую
    error_log("Подключаем файл из public: " . $publicFile);
    include $publicFile;
    exit;
}

// Если файл не найден, выводим информацию об ошибке 404
header("HTTP/1.0 404 Not Found");
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
    <p>Запрашиваемый путь: " . htmlspecialchars($path) . "</p>
    <p>Абсолютный путь: " . htmlspecialchars($file) . "</p>
    <p>Путь в public: " . htmlspecialchars($publicFile) . "</p>
    <h2>Переменные сервера:</h2>
    <pre>" . htmlspecialchars(print_r($_SERVER, true)) . "</pre>
    <h2>Доступные файлы в корне API:</h2>
    <pre>" . htmlspecialchars(print_r(scandir(__DIR__), true)) . "</pre>
    <h2>Доступные файлы в public:</h2>
    <pre>" . htmlspecialchars(print_r(scandir(__DIR__ . '/public'), true)) . "</pre>
</body>
</html>";
error_log("Файл не найден: " . $path); 