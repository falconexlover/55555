<?php
/**
 * Конфигурационный файл для подключения к базе данных на Vercel
 * Используется для управления бронированиями в гостинице "Лесной дворик"
 */

// Получение параметров подключения из переменных окружения Vercel
// ВНИМАНИЕ: Необходимо добавить эти переменные в настройках проекта на Vercel
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'db_hotel';
$db_user = getenv('DB_USER') ?: 'db_user';
$db_pass = getenv('DB_PASSWORD') ?: 'db_password';

// Создание соединения с базой данных
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    // Установка режима обработки ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Установка режима получения данных по умолчанию
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // В реальном проекте лучше логировать ошибки, а не выводить их напрямую
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Подключение остальных функций из основной конфигурации
require_once 'db_functions.php'; 