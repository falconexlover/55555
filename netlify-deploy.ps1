# Скрипт для деплоя проекта на Netlify
# Автор: Лесной дворик
# Дата создания: 11.03.2025

Write-Host "=== Запуск скрипта деплоя на Netlify ===" -ForegroundColor Green

# Проверка наличия Netlify CLI
try {
    $netlifyVersion = (netlify --version) 2>&1
    if ($netlifyVersion -match "netlify-cli") {
        Write-Host "Netlify CLI уже установлен: $netlifyVersion" -ForegroundColor Cyan
    } else {
        throw "Netlify CLI не найден"
    }
} catch {
    Write-Host "Установка Netlify CLI..." -ForegroundColor Yellow
    npm install -g netlify-cli
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Ошибка установки Netlify CLI. Убедитесь, что установлен Node.js." -ForegroundColor Red
        exit 1
    }
    Write-Host "Netlify CLI успешно установлен" -ForegroundColor Green
}

# Проверка авторизации Netlify
Write-Host "Проверка авторизации Netlify..." -ForegroundColor Cyan
$netlifyStatus = (netlify status) 2>&1
if ($netlifyStatus -match "Error: Not logged in") {
    Write-Host "Необходимо авторизоваться в Netlify. Запускаем процесс авторизации..." -ForegroundColor Yellow
    netlify login
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Ошибка авторизации в Netlify." -ForegroundColor Red
        exit 1
    }
}

# Установка зависимостей проекта
Write-Host "Установка зависимостей проекта..." -ForegroundColor Cyan
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "Ошибка установки зависимостей проекта." -ForegroundColor Red
    exit 1
}

# Проверка связи с сайтом Netlify
$siteStatus = (netlify status) 2>&1
if ($siteStatus -match "No site configured") {
    Write-Host "Сайт Netlify не настроен. Запускаем процесс инициализации..." -ForegroundColor Yellow
    netlify init
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Ошибка инициализации сайта Netlify." -ForegroundColor Red
        exit 1
    }
}

# Деплой на Netlify
Write-Host "Запуск деплоя на Netlify..." -ForegroundColor Cyan
netlify deploy --prod
if ($LASTEXITCODE -ne 0) {
    Write-Host "Ошибка деплоя на Netlify." -ForegroundColor Red
    exit 1
}

Write-Host "=== Деплой на Netlify завершен успешно! ===" -ForegroundColor Green 