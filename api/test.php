<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Заголовок для вывода в формате HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест PHP - Гостиница "Лесной дворик"</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .box {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .success {
            background-color: #e8f8f5;
            border-left: 5px solid #27ae60;
        }
        .info {
            background-color: #eaf2f8;
            border-left: 5px solid #3498db;
        }
        pre {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Тест PHP - Гостиница "Лесной дворик"</h1>
    
    <div class="box success">
        <h2>Поздравляем!</h2>
        <p>Если вы видите эту страницу, значит PHP-обработчик корректно работает на Vercel.</p>
        <p>Текущая версия PHP: <?php echo phpversion(); ?></p>
    </div>
    
    <div class="box info">
        <h3>Информация о сервере:</h3>
        <ul>
            <li>Сервер: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно'; ?></li>
            <li>Хост: <?php echo $_SERVER['HTTP_HOST'] ?? 'Неизвестно'; ?></li>
            <li>Запрошенный URI: <?php echo $_SERVER['REQUEST_URI'] ?? 'Неизвестно'; ?></li>
            <li>Документный корень: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Неизвестно'; ?></li>
            <li>Путь к скрипту: <?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Неизвестно'; ?></li>
        </ul>
    </div>
    
    <div class="box info">
        <h3>Доступные PHP-расширения:</h3>
        <pre><?php echo implode(", ", get_loaded_extensions()); ?></pre>
    </div>
    
    <div class="box">
        <h3>Содержимое текущей директории:</h3>
        <pre><?php echo implode("\n", scandir(__DIR__)); ?></pre>
    </div>
    
    <div class="box">
        <h3>Следующие шаги для отладки:</h3>
        <ol>
            <li>Проверьте доступность главной страницы по адресу <a href="/">Главная страница</a></li>
            <li>Проверьте доступность статических HTML-файлов по адресу <a href="/test.html">Тестовая HTML-страница</a></li>
            <li>Проверьте доступность тестов подключения к БД по адресу <a href="/db_test.php">Тест подключения к БД</a></li>
            <li>Проверьте доступность административной панели <a href="/admin">Административная панель</a></li>
        </ol>
    </div>
</body>
</html> 