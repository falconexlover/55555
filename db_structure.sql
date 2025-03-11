-- Структура базы данных для гостиницы "Лесной дворик"
-- Создание таблицы для бронирований

-- Удаление таблицы, если она существует
DROP TABLE IF EXISTS `bookings`;

-- Создание таблицы бронирований
CREATE TABLE `bookings` (
  `id` varchar(36) NOT NULL COMMENT 'Уникальный идентификатор бронирования',
  `name` varchar(255) NOT NULL COMMENT 'Имя клиента',
  `phone` varchar(20) NOT NULL COMMENT 'Телефон клиента',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email клиента',
  `arrival_date` date NOT NULL COMMENT 'Дата заезда',
  `departure_date` date NOT NULL COMMENT 'Дата выезда',
  `guests` int(11) NOT NULL DEFAULT '1' COMMENT 'Количество гостей',
  `room_type` varchar(50) NOT NULL COMMENT 'Тип номера',
  `comments` text COMMENT 'Комментарии к бронированию',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'Способ оплаты',
  `promo_code` varchar(50) DEFAULT NULL COMMENT 'Промокод',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT 'Стоимость бронирования',
  `status` varchar(20) NOT NULL DEFAULT 'Новое' COMMENT 'Статус бронирования',
  `created_at` datetime NOT NULL COMMENT 'Дата создания',
  `updated_at` datetime DEFAULT NULL COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  KEY `idx_arrival_date` (`arrival_date`),
  KEY `idx_departure_date` (`departure_date`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Таблица бронирований';

-- Создание таблицы для пользователей (администраторов)
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор пользователя',
  `username` varchar(50) NOT NULL COMMENT 'Логин пользователя',
  `password` varchar(255) NOT NULL COMMENT 'Хеш пароля',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email пользователя',
  `role` varchar(20) NOT NULL DEFAULT 'admin' COMMENT 'Роль пользователя',
  `last_login` datetime DEFAULT NULL COMMENT 'Время последнего входа',
  `created_at` datetime NOT NULL COMMENT 'Дата создания',
  `updated_at` datetime DEFAULT NULL COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Таблица пользователей';

-- Вставка тестового администратора
INSERT INTO `users` (`username`, `password`, `email`, `role`, `created_at`) VALUES
('admin', '$2y$10$8KzS.AzYAWM0VT29QsYJaO9ToRR2S8h.j9.nh.Qn8GQjR7QJy5n4e', 'admin@example.com', 'admin', NOW());
-- Пароль: password123 (захеширован с bcrypt)

-- Создание таблицы для номеров
DROP TABLE IF EXISTS `rooms`;

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор номера',
  `room_number` varchar(10) NOT NULL COMMENT 'Номер комнаты',
  `room_type` varchar(50) NOT NULL COMMENT 'Тип номера',
  `capacity` int(11) NOT NULL DEFAULT '2' COMMENT 'Вместимость номера',
  `price_per_night` decimal(10,2) NOT NULL COMMENT 'Цена за ночь',
  `description` text COMMENT 'Описание номера',
  `amenities` text COMMENT 'Удобства в номере',
  `status` varchar(20) NOT NULL DEFAULT 'Доступен' COMMENT 'Статус номера',
  `created_at` datetime NOT NULL COMMENT 'Дата создания',
  `updated_at` datetime DEFAULT NULL COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `idx_room_type` (`room_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Таблица номеров';

-- Вставка тестовых номеров
INSERT INTO `rooms` (`room_number`, `room_type`, `capacity`, `price_per_night`, `description`, `amenities`, `status`, `created_at`) VALUES
('101', 'Эконом', 2, 2500.00, 'Уютный номер эконом-класса с основными удобствами', 'Wi-Fi, телевизор, душ', 'Доступен', NOW()),
('102', 'Эконом', 2, 2500.00, 'Уютный номер эконом-класса с основными удобствами', 'Wi-Fi, телевизор, душ', 'Доступен', NOW()),
('201', 'Стандарт', 2, 3500.00, 'Стандартный номер с комфортными условиями', 'Wi-Fi, телевизор, душ, кондиционер, холодильник', 'Доступен', NOW()),
('202', 'Стандарт', 2, 3500.00, 'Стандартный номер с комфортными условиями', 'Wi-Fi, телевизор, душ, кондиционер, холодильник', 'Доступен', NOW()),
('301', 'Семейный', 4, 5000.00, 'Просторный номер для семейного отдыха', 'Wi-Fi, телевизор, душ, кондиционер, холодильник, диван', 'Доступен', NOW()),
('401', 'Комфорт', 2, 4500.00, 'Комфортабельный номер с дополнительными удобствами', 'Wi-Fi, телевизор, ванна, кондиционер, холодильник, мини-бар', 'Доступен', NOW()),
('501', 'Люкс', 2, 7000.00, 'Роскошный номер с максимальным комфортом', 'Wi-Fi, телевизор, джакузи, кондиционер, холодильник, мини-бар, сейф', 'Доступен', NOW());

-- Создание таблицы для отзывов
DROP TABLE IF EXISTS `reviews`;

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор отзыва',
  `name` varchar(255) NOT NULL COMMENT 'Имя клиента',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email клиента',
  `rating` int(11) NOT NULL COMMENT 'Оценка (от 1 до 5)',
  `comment` text NOT NULL COMMENT 'Текст отзыва',
  `status` varchar(20) NOT NULL DEFAULT 'На модерации' COMMENT 'Статус отзыва',
  `created_at` datetime NOT NULL COMMENT 'Дата создания',
  `updated_at` datetime DEFAULT NULL COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Таблица отзывов';

-- Вставка тестовых отзывов
INSERT INTO `reviews` (`name`, `email`, `rating`, `comment`, `status`, `created_at`) VALUES
('Иван Петров', 'ivan@example.com', 5, 'Отличная гостиница! Очень понравилось расположение и обслуживание. Обязательно приедем еще раз.', 'Опубликован', NOW()),
('Мария Сидорова', 'maria@example.com', 4, 'Хорошая гостиница, уютные номера, вкусные завтраки. Единственный минус - слабый Wi-Fi.', 'Опубликован', NOW()),
('Алексей Иванов', 'alexey@example.com', 5, 'Прекрасное место для отдыха! Чистые номера, вежливый персонал, красивая территория.', 'Опубликован', NOW()); 