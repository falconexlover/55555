// Импортируем необходимые модули
const { MongoClient, ObjectId } = require('mongodb');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const { Handler } = require('@netlify/functions');

// Константы для конфигурации
const JWT_SECRET = process.env.JWT_SECRET || 'lesnoy-dvorik-secret-key';
const MONGODB_URI = process.env.MONGODB_URI;
const DB_NAME = 'lesnoy-dvorik';

// Подключение к MongoDB
let cachedDb = null;
async function connectToDatabase() {
  if (cachedDb) {
    return cachedDb;
  }
  
  const client = await MongoClient.connect(MONGODB_URI, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
  
  const db = client.db(DB_NAME);
  cachedDb = db;
  return db;
}

// Функция верификации JWT токена
const verifyToken = (token) => {
  try {
    return jwt.verify(token, JWT_SECRET);
  } catch (error) {
    return null;
  }
};

// Проверка авторизации
const checkAuth = (event) => {
  const authHeader = event.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return null;
  }
  
  const token = authHeader.split(' ')[1];
  return verifyToken(token);
};

// Получение HTML для входа в админку
const getLoginPage = () => {
  return `
    <!DOCTYPE html>
    <html lang="ru">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Вход в админ-панель | Лесной дворик</title>
      <link rel="stylesheet" href="/css/admin.css">
    </head>
    <body class="login-page">
      <div class="login-container">
        <h1>Лесной дворик</h1>
        <h2>Вход в админ-панель</h2>
        <div id="error-message" class="error-message"></div>
        <form id="login-form" class="login-form">
          <div class="form-group">
            <label for="username">Логин:</label>
            <input type="text" id="username" name="username" required>
          </div>
          <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary">Войти</button>
        </form>
      </div>
      <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
          e.preventDefault();
          
          const username = document.getElementById('username').value;
          const password = document.getElementById('password').value;
          
          try {
            const response = await fetch('/.netlify/functions/admin', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                action: 'login',
                username,
                password,
              }),
            });
            
            const data = await response.json();
            
            if (data.success) {
              localStorage.setItem('admin_token', data.token);
              window.location.href = '/admin';
            } else {
              document.getElementById('error-message').textContent = data.message || 'Ошибка авторизации';
            }
          } catch (error) {
            document.getElementById('error-message').textContent = 'Ошибка сервера. Попробуйте позже.';
          }
        });
      </script>
    </body>
    </html>
  `;
};

// Получение HTML для админ-панели
const getAdminPage = (userData) => {
  return `
    <!DOCTYPE html>
    <html lang="ru">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Админ-панель | Лесной дворик</title>
      <link rel="stylesheet" href="/css/admin.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="admin-page">
      <header class="admin-header">
        <div class="logo">
          <img src="/assets/images/logo.png" alt="Лесной дворик">
        </div>
        <div class="user-info">
          <span>Добро пожаловать, ${userData.username}</span>
          <button id="logout-btn" class="btn btn-outline">Выйти</button>
        </div>
      </header>
      
      <div class="admin-container">
        <aside class="admin-sidebar">
          <nav>
            <ul>
              <li><a href="#" data-section="dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Панель управления</a></li>
              <li><a href="#" data-section="bookings"><i class="fas fa-calendar-check"></i> Бронирования</a></li>
              <li><a href="#" data-section="rooms"><i class="fas fa-bed"></i> Номера</a></li>
              <li><a href="#" data-section="reviews"><i class="fas fa-comments"></i> Отзывы</a></li>
              <li><a href="#" data-section="content"><i class="fas fa-edit"></i> Редактор контента</a></li>
              <li><a href="#" data-section="settings"><i class="fas fa-cog"></i> Настройки</a></li>
            </ul>
          </nav>
        </aside>
        
        <main class="admin-content">
          <div id="dashboard-section" class="content-section active">
            <h2>Панель управления</h2>
            <div class="dashboard-cards">
              <div class="card">
                <div class="card-value" id="total-bookings">...</div>
                <div class="card-label">Бронирований</div>
              </div>
              <div class="card">
                <div class="card-value" id="new-bookings">...</div>
                <div class="card-label">Новых</div>
              </div>
              <div class="card">
                <div class="card-value" id="completed-bookings">...</div>
                <div class="card-label">Завершенных</div>
              </div>
              <div class="card">
                <div class="card-value" id="total-revenue">...</div>
                <div class="card-label">Доход (₽)</div>
              </div>
            </div>
            
            <h3>Последние бронирования</h3>
            <div id="recent-bookings">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Даты</th>
                    <th>Номер</th>
                    <th>Статус</th>
                    <th>Действия</th>
                  </tr>
                </thead>
                <tbody id="recent-bookings-body">
                  <tr>
                    <td colspan="6" class="loading-data">Загрузка данных...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div id="bookings-section" class="content-section">
            <h2>Управление бронированиями</h2>
            <div class="section-controls">
              <div class="search-box">
                <input type="text" id="bookings-search" placeholder="Поиск по имени или номеру...">
                <button id="bookings-search-btn"><i class="fas fa-search"></i></button>
              </div>
              <div class="filter-box">
                <select id="bookings-status-filter">
                  <option value="">Все статусы</option>
                  <option value="Новое">Новое</option>
                  <option value="Подтверждено">Подтверждено</option>
                  <option value="Отменено">Отменено</option>
                  <option value="Завершено">Завершено</option>
                </select>
                <button id="bookings-export-btn" class="btn btn-outline"><i class="fas fa-download"></i> Экспорт</button>
              </div>
            </div>
            
            <div id="bookings-table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Заезд</th>
                    <th>Выезд</th>
                    <th>Номер</th>
                    <th>Гостей</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Действия</th>
                  </tr>
                </thead>
                <tbody id="bookings-table-body">
                  <tr>
                    <td colspan="10" class="loading-data">Загрузка данных...</td>
                  </tr>
                </tbody>
              </table>
              <div class="pagination" id="bookings-pagination"></div>
            </div>
          </div>
          
          <!-- Остальные секции будут подгружаться по запросу -->
        </main>
      </div>
      
      <script>
        // Базовый скрипт для админки
        document.addEventListener('DOMContentLoaded', () => {
          // Проверка авторизации
          const token = localStorage.getItem('admin_token');
          if (!token) {
            window.location.href = '/admin/login';
            return;
          }
          
          // Обработчик выхода
          document.getElementById('logout-btn').addEventListener('click', () => {
            localStorage.removeItem('admin_token');
            window.location.href = '/admin/login';
          });
          
          // Переключение между разделами
          const sidebarLinks = document.querySelectorAll('.admin-sidebar a');
          const contentSections = document.querySelectorAll('.content-section');
          
          sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
              e.preventDefault();
              
              // Убираем активный класс у всех ссылок и секций
              sidebarLinks.forEach(l => l.classList.remove('active'));
              contentSections.forEach(s => s.classList.remove('active'));
              
              // Добавляем активный класс к текущей ссылке
              link.classList.add('active');
              
              // Показываем соответствующую секцию
              const sectionId = link.getAttribute('data-section');
              const section = document.getElementById(sectionId + '-section');
              
              if (section) {
                section.classList.add('active');
              } else {
                // Если секция еще не загружена, загружаем её
                loadSection(sectionId);
              }
            });
          });
          
          // Загрузка данных для дашборда
          fetchDashboardData();
        });
        
        // Загрузка данных для дашборда
        async function fetchDashboardData() {
          try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch('/.netlify/functions/admin', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
              },
              body: JSON.stringify({
                action: 'getDashboardData'
              })
            });
            
            const data = await response.json();
            
            if (data.success) {
              // Обновляем данные на дашборде
              document.getElementById('total-bookings').textContent = data.data.totalBookings || 0;
              document.getElementById('new-bookings').textContent = data.data.newBookings || 0;
              document.getElementById('completed-bookings').textContent = data.data.completedBookings || 0;
              document.getElementById('total-revenue').textContent = formatCurrency(data.data.totalRevenue || 0);
              
              // Заполняем таблицу последних бронирований
              const tbody = document.getElementById('recent-bookings-body');
              tbody.innerHTML = '';
              
              if (data.data.recentBookings && data.data.recentBookings.length > 0) {
                data.data.recentBookings.forEach(booking => {
                  const row = document.createElement('tr');
                  
                  row.innerHTML = 
                    '<td>' + booking._id.substring(0, 8) + '...</td>' +
                    '<td>' + booking.name + '</td>' +
                    '<td>' + formatDate(booking.arrival_date) + ' - ' + formatDate(booking.departure_date) + '</td>' +
                    '<td>' + booking.room_type + '</td>' +
                    '<td><span class="status-badge status-' + booking.status.toLowerCase() + '">' + booking.status + '</span></td>' +
                    '<td>' +
                      '<button class="btn-icon" onclick="viewBooking(\'' + booking._id + '\')"><i class="fas fa-eye"></i></button>' +
                      '<button class="btn-icon" onclick="editBooking(\'' + booking._id + '\')"><i class="fas fa-edit"></i></button>' +
                    '</td>';
                  
                  tbody.appendChild(row);
                });
              } else {
                tbody.innerHTML = '<tr><td colspan="6" class="no-data">Нет данных о бронированиях</td></tr>';
              }
            } else {
              console.error('Ошибка загрузки данных:', data.message);
            }
          } catch (error) {
            console.error('Ошибка запроса:', error);
          }
        }
        
        // Форматирование суммы
        function formatCurrency(amount) {
          return new Intl.NumberFormat('ru-RU', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
          }).format(amount);
        }
        
        // Форматирование даты
        function formatDate(dateString) {
          const date = new Date(dateString);
          return date.toLocaleDateString('ru-RU');
        }
        
        // Загрузка секции по запросу
        async function loadSection(sectionId) {
          // Здесь код для асинхронной загрузки секций
          console.log('Загрузка секции:', sectionId);
        }
        
        // Просмотр бронирования
        function viewBooking(id) {
          // Здесь код для просмотра бронирования
          console.log('Просмотр бронирования:', id);
        }
        
        // Редактирование бронирования
        function editBooking(id) {
          // Здесь код для редактирования бронирования
          console.log('Редактирование бронирования:', id);
        }
      </script>
    </body>
    </html>
  `;
};

// Обновленный экспорт функции с использованием современного формата Netlify Functions
exports.handler = async (event, context) => {
  // Проверяем путь запроса
  const path = event.path.replace('/.netlify/functions/admin', '');
  
  // Обработка запросов на страницу входа
  if (path === '/login' && event.httpMethod === 'GET') {
    return {
      statusCode: 200,
      headers: { 'Content-Type': 'text/html; charset=utf-8' },
      body: getLoginPage(),
    };
  }
  
  // Обработка авторизации
  if (event.httpMethod === 'POST') {
    try {
      const body = JSON.parse(event.body);
      
      // Обработка входа
      if (body.action === 'login') {
        const { username, password } = body;
        
        // Проверка наличия обязательных полей
        if (!username || !password) {
          return {
            statusCode: 400,
            body: JSON.stringify({ success: false, message: 'Необходимо указать логин и пароль' }),
            headers: { 'Content-Type': 'application/json' },
          };
        }
        
        // Здесь должна быть проверка по базе данных
        // Для прототипа используем фиксированные значения
        const validUsername = 'admin';
        const validPasswordHash = '$2y$10$8KzS.AzYAWM0VT29QsYJaO9ToRR2S8h.j9.nh.Qn8GQjR7QJy5n4e'; // password123
        
        if (username === validUsername) {
          // Проверка пароля
          const isValidPassword = await bcrypt.compare(password, validPasswordHash);
          
          if (isValidPassword) {
            // Создаем JWT токен
            const token = jwt.sign(
              { id: '1', username, role: 'admin' },
              JWT_SECRET,
              { expiresIn: '24h' }
            );
            
            return {
              statusCode: 200,
              body: JSON.stringify({ 
                success: true, 
                message: 'Авторизация успешна', 
                token 
              }),
              headers: { 'Content-Type': 'application/json' },
            };
          }
        }
        
        // Если логин или пароль неверны
        return {
          statusCode: 401,
          body: JSON.stringify({ success: false, message: 'Неверный логин или пароль' }),
          headers: { 'Content-Type': 'application/json' },
        };
      }
      
      // Обработка запросов на получение данных (требуется авторизация)
      const userData = checkAuth(event);
      if (!userData) {
        return {
          statusCode: 401,
          body: JSON.stringify({ success: false, message: 'Требуется авторизация' }),
          headers: { 'Content-Type': 'application/json' },
        };
      }
      
      // Получение данных для дашборда
      if (body.action === 'getDashboardData') {
        // Здесь должно быть получение данных из базы
        // Для прототипа используем фиктивные данные
        const dashboardData = {
          totalBookings: 152,
          newBookings: 23,
          completedBookings: 98,
          totalRevenue: 576400,
          recentBookings: [
            {
              _id: '60d21b4667d0d8992e610c85',
              name: 'Иванов Иван',
              arrival_date: '2023-03-15',
              departure_date: '2023-03-18',
              room_type: 'Стандарт',
              status: 'Новое',
            },
            {
              _id: '60d21b4667d0d8992e610c86',
              name: 'Петрова Мария',
              arrival_date: '2023-03-17',
              departure_date: '2023-03-19',
              room_type: 'Люкс',
              status: 'Подтверждено',
            },
            {
              _id: '60d21b4667d0d8992e610c87',
              name: 'Сидоров Алексей',
              arrival_date: '2023-03-14',
              departure_date: '2023-03-16',
              room_type: 'Комфорт',
              status: 'Завершено',
            },
          ],
        };
        
        return {
          statusCode: 200,
          body: JSON.stringify({ success: true, data: dashboardData }),
          headers: { 'Content-Type': 'application/json' },
        };
      }
      
      // Для других действий
      return {
        statusCode: 400,
        body: JSON.stringify({ success: false, message: 'Неизвестное действие' }),
        headers: { 'Content-Type': 'application/json' },
      };
    } catch (error) {
      console.error('Ошибка обработки запроса:', error);
      
      return {
        statusCode: 500,
        body: JSON.stringify({ success: false, message: 'Внутренняя ошибка сервера' }),
        headers: { 'Content-Type': 'application/json' },
      };
    }
  }
  
  // Обработка GET-запросов к админ-панели (требуется авторизация)
  if (event.httpMethod === 'GET') {
    // Проверка авторизации через куки или заголовки не имеет смысла,
    // так как фронтенд сам проверяет наличие токена в localStorage
    // и перенаправляет на страницу входа, если токена нет
    
    // Возвращаем HTML админ-панели
    return {
      statusCode: 200,
      headers: { 'Content-Type': 'text/html; charset=utf-8' },
      body: getAdminPage({ username: 'Администратор' }),
    };
  }
  
  // Для неподдерживаемых методов
  return {
    statusCode: 405,
    body: JSON.stringify({ success: false, message: 'Метод не поддерживается' }),
    headers: { 'Content-Type': 'application/json' },
  };
}; 