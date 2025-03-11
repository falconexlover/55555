<?php
/**
 * Конфигурационный файл для подключения к базе данных на хостинге Beget
 * Используется для управления бронированиями в гостинице "Лесной дворик"
 */

// Параметры подключения к базе данных на Beget
// ВНИМАНИЕ: Перед загрузкой на хостинг замените эти значения на реальные
$db_host = 'localhost'; // Обычно для Beget это localhost
$db_name = 'db_hotel'; // Замените на имя базы данных, обычно начинается с вашего логина на Beget
$db_user = 'db_user'; // Замените на пользователя БД (обычно также начинается с логина на Beget)
$db_pass = 'db_password'; // Замените на пароль от базы данных

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

// Функция для защиты от SQL-инъекций (дополнительная защита, PDO уже использует подготовленные запросы)
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Функция для проверки авторизации администратора
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Функция для добавления нового бронирования в БД
function addBooking($pdo, $bookingData) {
    try {
        $sql = "INSERT INTO bookings (
                id, name, phone, email, 
                arrival_date, departure_date, guests, 
                room_type, comments, payment_method, 
                promo_code, price, status, created_at
            ) VALUES (
                :id, :name, :phone, :email, 
                :arrival_date, :departure_date, :guests, 
                :room_type, :comments, :payment_method, 
                :promo_code, :price, :status, NOW()
            )";
            
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':id', $bookingData['id']);
        $stmt->bindParam(':name', $bookingData['name']);
        $stmt->bindParam(':phone', $bookingData['phone']);
        $stmt->bindParam(':email', $bookingData['email']);
        $stmt->bindParam(':arrival_date', $bookingData['arrival_date']);
        $stmt->bindParam(':departure_date', $bookingData['departure_date']);
        $stmt->bindParam(':guests', $bookingData['guests']);
        $stmt->bindParam(':room_type', $bookingData['room_type']);
        $stmt->bindParam(':comments', $bookingData['comments']);
        $stmt->bindParam(':payment_method', $bookingData['payment_method']);
        $stmt->bindParam(':promo_code', $bookingData['promo_code']);
        $stmt->bindParam(':price', $bookingData['price']);
        $stmt->bindParam(':status', $bookingData['status']);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        // Логирование ошибки
        error_log("Ошибка при добавлении бронирования: " . $e->getMessage());
        return false;
    }
}

// Функция для получения всех бронирований из БД
function getAllBookings($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Ошибка при получении бронирований: " . $e->getMessage());
        return [];
    }
}

// Функция для получения бронирования по ID
function getBookingById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Ошибка при получении бронирования: " . $e->getMessage());
        return null;
    }
}

// Функция для обновления статуса бронирования
function updateBookingStatus($pdo, $id, $status) {
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка при обновлении статуса бронирования: " . $e->getMessage());
        return false;
    }
}

// Функция для удаления бронирования
function deleteBooking($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка при удалении бронирования: " . $e->getMessage());
        return false;
    }
}

// Функция для получения статистики бронирований
function getBookingStats($pdo) {
    try {
        $stats = [];
        
        // Общее количество бронирований
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Количество новых бронирований
        $stmt = $pdo->prepare("SELECT COUNT(*) as new FROM bookings WHERE status = 'Новое'");
        $stmt->execute();
        $stats['new'] = $stmt->fetch()['new'];
        
        // Количество подтвержденных бронирований
        $stmt = $pdo->prepare("SELECT COUNT(*) as confirmed FROM bookings WHERE status = 'Подтверждено'");
        $stmt->execute();
        $stats['confirmed'] = $stmt->fetch()['confirmed'];
        
        // Общая сумма от бронирований
        $stmt = $pdo->prepare("SELECT SUM(price) as total_revenue FROM bookings WHERE status != 'Отменено'");
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch()['total_revenue'];
        
        // Самый популярный тип номера
        $stmt = $pdo->prepare("SELECT room_type, COUNT(*) as count FROM bookings GROUP BY room_type ORDER BY count DESC LIMIT 1");
        $stmt->execute();
        $popularRoom = $stmt->fetch();
        $stats['popular_room'] = $popularRoom ? $popularRoom['room_type'] : '';
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Ошибка при получении статистики: " . $e->getMessage());
        return [];
    }
}

// Функция для экспорта бронирований в CSV
function exportBookingsToCSV($pdo) {
    try {
        $filename = 'bookings_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Получение всех бронирований
        $stmt = $pdo->prepare("SELECT * FROM bookings ORDER BY created_at DESC");
        $stmt->execute();
        $bookings = $stmt->fetchAll();
        
        // Формирование заголовков CSV
        $headers = [
            'ID', 'Имя', 'Телефон', 'Email', 
            'Дата заезда', 'Дата выезда', 'Гостей', 
            'Тип номера', 'Комментарии', 'Способ оплаты', 
            'Промокод', 'Цена', 'Статус', 'Создано'
        ];
        
        // Открываем файл для записи
        $fp = fopen('php://temp', 'w');
        
        // Добавляем заголовки
        fputcsv($fp, $headers);
        
        // Добавляем данные
        foreach ($bookings as $booking) {
            fputcsv($fp, [
                $booking['id'],
                $booking['name'],
                $booking['phone'],
                $booking['email'],
                $booking['arrival_date'],
                $booking['departure_date'],
                $booking['guests'],
                $booking['room_type'],
                $booking['comments'],
                $booking['payment_method'],
                $booking['promo_code'],
                $booking['price'],
                $booking['status'],
                $booking['created_at']
            ]);
        }
        
        // Перемещаем указатель в начало
        rewind($fp);
        
        // Считываем содержимое
        $output = stream_get_contents($fp);
        fclose($fp);
        
        return [
            'filename' => $filename,
            'content' => $output
        ];
    } catch (PDOException $e) {
        error_log("Ошибка при экспорте бронирований: " . $e->getMessage());
        return null;
    }
}

// Функции для работы с контентом сайта

// Получение контента для конкретной страницы
function getPageContent($pdo, $page) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM content WHERE page = :page ORDER BY sort_order ASC");
        $stmt->bindParam(':page', $page);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Ошибка при получении контента страницы: " . $e->getMessage());
        return [];
    }
}

// Получение контента для конкретной секции страницы
function getSectionContent($pdo, $page, $section) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM content WHERE page = :page AND section = :section LIMIT 1");
        $stmt->bindParam(':page', $page);
        $stmt->bindParam(':section', $section);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Ошибка при получении контента секции: " . $e->getMessage());
        return null;
    }
}

// Обновление контента секции
function updateSectionContent($pdo, $id, $contentData) {
    try {
        $sql = "UPDATE content SET 
                title = :title, 
                content = :content, 
                image_path = :image_path, 
                updated_at = NOW() 
                WHERE id = :id";
                
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $contentData['title']);
        $stmt->bindParam(':content', $contentData['content']);
        $stmt->bindParam(':image_path', $contentData['image_path']);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка при обновлении контента: " . $e->getMessage());
        return false;
    }
}

// Добавление новой секции контента
function addSectionContent($pdo, $contentData) {
    try {
        $sql = "INSERT INTO content (
                page, section, title, content, 
                image_path, sort_order, created_at
            ) VALUES (
                :page, :section, :title, :content, 
                :image_path, :sort_order, NOW()
            )";
            
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':page', $contentData['page']);
        $stmt->bindParam(':section', $contentData['section']);
        $stmt->bindParam(':title', $contentData['title']);
        $stmt->bindParam(':content', $contentData['content']);
        $stmt->bindParam(':image_path', $contentData['image_path']);
        $stmt->bindParam(':sort_order', $contentData['sort_order']);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка при добавлении контента: " . $e->getMessage());
        return false;
    }
}

// Удаление секции контента
function deleteSectionContent($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM content WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка при удалении контента: " . $e->getMessage());
        return false;
    }
}

// Функции для работы с настройками сайта

// Получение всех настроек
function getAllSettings($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM settings ORDER BY setting_key ASC");
        $stmt->execute();
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Ошибка при получении настроек: " . $e->getMessage());
        return [];
    }
}

// Получение значения конкретной настройки
function getSetting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("Ошибка при получении настройки: " . $e->getMessage());
        return $default;
    }
}

// Обновление настройки
function updateSetting($pdo, $key, $value) {
    try {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key");
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Ошибка при обновлении настройки: " . $e->getMessage());
        return false;
    }
}

// Функция для загрузки изображений
function uploadImage($file, $targetDir = '../assets/images/') {
    // Проверяем, существует ли директория, если нет - создаем
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Генерируем уникальное имя файла
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Проверяем тип файла
    $allowTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if (in_array(strtolower($fileType), $allowTypes)) {
        // Загружаем файл
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            return '/assets/images/' . $fileName;
        }
    }
    
    return false;
}

// Функция для генерации HTML-страниц из шаблонов и контента
function generateHtmlPage($pdo, $page, $template, $outputPath) {
    try {
        // Получаем контент для страницы
        $content = getPageContent($pdo, $page);
        
        // Получаем настройки сайта
        $settings = getAllSettings($pdo);
        
        // Загружаем шаблон
        $html = file_get_contents($template);
        
        // Заменяем переменные в шаблоне
        foreach ($settings as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }
        
        // Обрабатываем контент
        foreach ($content as $section) {
            $sectionHtml = '';
            
            // В зависимости от типа секции формируем HTML
            switch ($section['section']) {
                case 'hero':
                    $sectionHtml = '<div class="hero" style="background-image: url(' . $section['image_path'] . ')">
                        <div class="hero-content">
                            <h1>' . $section['title'] . '</h1>
                            <p>' . $section['content'] . '</p>
                        </div>
                    </div>';
                    break;
                    
                case 'about':
                case 'services':
                    $sectionHtml = '<section class="' . $section['section'] . '">
                        <div class="container">
                            <h2>' . $section['title'] . '</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="' . $section['image_path'] . '" alt="' . $section['title'] . '" class="img-fluid">
                                </div>
                                <div class="col-md-6">
                                    <p>' . nl2br($section['content']) . '</p>
                                </div>
                            </div>
                        </div>
                    </section>';
                    break;
                    
                case 'description':
                    $sectionHtml = '<section class="description">
                        <div class="container">
                            <h2>' . $section['title'] . '</h2>
                            <p>' . nl2br($section['content']) . '</p>
                            <img src="' . $section['image_path'] . '" alt="' . $section['title'] . '" class="img-fluid">
                        </div>
                    </section>';
                    break;
                    
                case 'info':
                    $sectionHtml = '<section class="info">
                        <div class="container">
                            <h2>' . $section['title'] . '</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <p>' . nl2br($section['content']) . '</p>
                                </div>
                                <div class="col-md-6">
                                    <img src="' . $section['image_path'] . '" alt="' . $section['title'] . '" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </section>';
                    break;
            }
            
            // Заменяем плейсхолдер в шаблоне
            $html = str_replace('{{' . $section['section'] . '}}', $sectionHtml, $html);
        }
        
        // Записываем результат в файл
        file_put_contents($outputPath, $html);
        return true;
    } catch (Exception $e) {
        error_log("Ошибка при генерации HTML-страницы: " . $e->getMessage());
        return false;
    }
} 