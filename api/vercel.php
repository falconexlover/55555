<?php
// Файл для обработки PHP-файлов на Vercel
// Этот файл нужен для того, чтобы Vercel правильно обрабатывал PHP-файлы

// Получаем путь к запрошенному файлу
$path = $_SERVER['REQUEST_URI'];

// Если запрос идет к корню, перенаправляем на index.php
if ($path === '/' || $path === '') {
    include __DIR__ . '/index.php';
    exit;
}

// Удаляем начальный слеш
$path = ltrim($path, '/');

// Проверяем, существует ли запрошенный файл
$file = __DIR__ . '/' . $path;
if (file_exists($file) && !is_dir($file)) {
    // Если файл существует, включаем его
    include $file;
    exit;
}

// Если файл не найден, перенаправляем на index.php
include __DIR__ . '/index.php'; 