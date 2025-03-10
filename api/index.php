<?php
// Определяем базовый путь
$base_path = __DIR__;

// Получаем запрошенный URI
$request_uri = $_SERVER['REQUEST_URI'];

// Если запрос идет к index.html, просто включаем его
if ($request_uri == '/' || $request_uri == '/index.html') {
    include $base_path . '/index.html';
    exit;
}

// Проверяем, существует ли запрошенный файл
$file_path = $base_path . $request_uri;
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
    
    // Включаем файл напрямую
    include $file_path;
    exit;
}

// Если файл не найден, перенаправляем на index.html
include $base_path . '/index.html';
