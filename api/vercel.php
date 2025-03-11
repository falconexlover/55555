<?php
// Файл для обработки PHP-файлов на Vercel
// Этот файл нужен для того, чтобы Vercel правильно обрабатывал PHP-файлы

// Получаем путь к запрошенному файлу
$path = $_SERVER['REQUEST_URI'];

// Логируем запрос для отладки
error_log("Vercel router: Requested path: " . $path);

// Если запрос идет к корню, перенаправляем на index.php или index.html
if ($path === '/' || $path === '') {
    // Сначала проверяем наличие index.php
    if (file_exists(__DIR__ . '/index.php')) {
        error_log("Vercel router: Serving /index.php");
        include __DIR__ . '/index.php';
        exit;
    } 
    // Затем проверяем public/index.html
    else if (file_exists(__DIR__ . '/public/index.html')) {
        error_log("Vercel router: Serving /public/index.html");
        echo file_get_contents(__DIR__ . '/public/index.html');
        exit;
    }
}

// Удаляем начальный слеш и параметры URL
$path = ltrim($path, '/');
$path = strtok($path, '?');

// Проверяем, существует ли запрошенный файл
$file = __DIR__ . '/' . $path;
error_log("Vercel router: Checking file: " . $file);

if (file_exists($file) && !is_dir($file)) {
    // Если файл существует, включаем его
    error_log("Vercel router: File found, serving: " . $file);
    
    // Обрабатываем различные типы файлов
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    
    if ($ext === 'php') {
        include $file;
    } 
    else if (in_array($ext, ['html', 'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'])) {
        // Устанавливаем правильный Content-Type
        $content_types = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        if (isset($content_types[$ext])) {
            header('Content-Type: ' . $content_types[$ext]);
        }
        
        echo file_get_contents($file);
    } else {
        // Для других типов файлов
        readfile($file);
    }
    exit;
}

// Также проверяем в директории public
$public_file = __DIR__ . '/public/' . $path;
error_log("Vercel router: Checking public file: " . $public_file);

if (file_exists($public_file) && !is_dir($public_file)) {
    error_log("Vercel router: Public file found, serving: " . $public_file);
    // Обрабатываем файл из public 
    $ext = pathinfo($public_file, PATHINFO_EXTENSION);
    
    if ($ext === 'php') {
        include $public_file;
    } else {
        // Для других типов файлов
        $content_types = [
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        if (isset($content_types[$ext])) {
            header('Content-Type: ' . $content_types[$ext]);
        }
        
        echo file_get_contents($public_file);
    }
    exit;
}

// Если файл не найден, проверяем index.php
if (file_exists(__DIR__ . '/index.php')) {
    error_log("Vercel router: File not found, falling back to index.php");
    include __DIR__ . '/index.php';
    exit;
} else if (file_exists(__DIR__ . '/public/index.html')) {
    error_log("Vercel router: File not found, falling back to public/index.html");
    echo file_get_contents(__DIR__ . '/public/index.html');
    exit;
} else {
    // Если ничего не найдено, отдаем 404
    error_log("Vercel router: No fallback file found, returning 404");
    http_response_code(404);
    echo "404 - Page not found";
} 