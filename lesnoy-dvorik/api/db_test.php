<?php
/**
 * Тестовый файл для проверки подключения к базе данных
 * Используется для проверки правильности настройки переменных окружения на Vercel
 */

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
    <title>Проверка подключения к базе данных</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .success {
            color: #27ae60;
            background-color: #e8f8f5;
            padding: 10px;
            border-radius: 5px;
            border-left: 5px solid #27ae60;
        }
        .error {
            color: #c0392b;
            background-color: #f9ebea;
            padding: 10px;
            border-radius: 5px;
            border-left: 5px solid #c0392b;
        }
        .info {
            background-color: #eaf2f8;
            padding: 10px;
            border-radius: 5px;
            border-left: 5px solid #3498db;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Проверка подключения к базе данных</h1>
        
        <div class="info">
            <p><strong>Информация о среде:</strong></p>
            <p>PHP версия: <?php echo phpversion(); ?></p>
            <p>Сервер: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно'; ?></p>
            <p>Хост: <?php echo $_SERVER['HTTP_HOST'] ?? 'Неизвестно'; ?></p>
        </div>
        
        <?php
        // Получаем параметры подключения из переменных окружения
        $db_host = getenv('DB_HOST') ?: 'localhost';
        $db_name = getenv('DB_NAME') ?: 'db_hotel';
        $db_user = getenv('DB_USER') ?: 'db_user';
        $db_pass = getenv('DB_PASSWORD') ?: 'db_password';
        
        echo '<div class="info">';
        echo '<p><strong>Параметры подключения:</strong></p>';
        echo '<p>Хост: ' . $db_host . '</p>';
        echo '<p>База данных: ' . $db_name . '</p>';
        echo '<p>Пользователь: ' . $db_user . '</p>';
        echo '<p>Пароль: ' . str_repeat('*', strlen($db_pass)) . '</p>';
        echo '</div>';
        
        try {
            // Пытаемся подключиться к базе данных
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo '<div class="success">';
            echo '<p><strong>Успех!</strong> Подключение к базе данных установлено.</p>';
            echo '</div>';
            
            // Проверяем наличие таблицы bookings
            $stmt = $pdo->prepare("SHOW TABLES LIKE 'bookings'");
            $stmt->execute();
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                echo '<div class="success">';
                echo '<p><strong>Таблица bookings найдена.</strong></p>';
                
                // Получаем количество записей в таблице
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings");
                $stmt->execute();
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo '<p>Количество записей в таблице: ' . $count . '</p>';
                echo '</div>';
                
                // Если есть записи, выводим первые 5
                if ($count > 0) {
                    $stmt = $pdo->prepare("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute();
                    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<h2>Последние 5 бронирований:</h2>';
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Имя</th><th>Телефон</th><th>Дата заезда</th><th>Статус</th></tr>';
                    
                    foreach ($bookings as $booking) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($booking['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['phone']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['arrival_date']) . '</td>';
                        echo '<td>' . htmlspecialchars($booking['status']) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</table>';
                }
            } else {
                echo '<div class="error">';
                echo '<p><strong>Таблица bookings не найдена.</strong> Возможно, вам нужно создать структуру базы данных.</p>';
                echo '</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<p><strong>Ошибка!</strong> Не удалось подключиться к базе данных.</p>';
            echo '<p>Сообщение об ошибке: ' . $e->getMessage() . '</p>';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<p><strong>Рекомендации:</strong></p>';
            echo '<ol>';
            echo '<li>Проверьте правильность переменных окружения на Vercel.</li>';
            echo '<li>Убедитесь, что база данных доступна извне (разрешены внешние подключения).</li>';
            echo '<li>Проверьте, что пользователь имеет необходимые права доступа.</li>';
            echo '<li>Если вы используете PlanetScale, убедитесь, что вы используете правильную строку подключения.</li>';
            echo '</ol>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html> 