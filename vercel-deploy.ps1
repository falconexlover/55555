# PowerShell скрипт для деплоя проекта на Vercel

Write-Host "=== Деплой проекта 'Лесной дворик' на Vercel ===" -ForegroundColor Green
Write-Host ""

# Проверка наличия Vercel CLI
$vercelInstalled = $null
try {
    $vercelInstalled = Get-Command vercel -ErrorAction SilentlyContinue
} catch {
    # Ничего не делаем, просто продолжаем
}

if ($null -eq $vercelInstalled) {
    Write-Host "Установка Vercel CLI..." -ForegroundColor Yellow
    npm install -g vercel
}

# Проверка авторизации
Write-Host "Проверка авторизации в Vercel..." -ForegroundColor Cyan
$whoamiResult = $null
try {
    $whoamiResult = vercel whoami 2>&1
} catch {
    # Ничего не делаем, просто продолжаем
}

if ($whoamiResult -like "*Error*" -or $whoamiResult -like "*not logged in*") {
    Write-Host "Требуется авторизация в Vercel..." -ForegroundColor Yellow
    vercel login
}

# Деплой проекта
Write-Host ""
Write-Host "Начинаем деплой проекта..." -ForegroundColor Cyan
$deployResult = vercel --prod

# Проверка успешности деплоя
if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Деплой успешно завершен!" -ForegroundColor Green
    Write-Host "Проверьте результат на вашем домене." -ForegroundColor Green
    Write-Host ""
    Write-Host "Не забудьте добавить переменные окружения в настройках проекта на Vercel:" -ForegroundColor Yellow
    Write-Host "- DB_HOST" -ForegroundColor Yellow
    Write-Host "- DB_NAME" -ForegroundColor Yellow
    Write-Host "- DB_USER" -ForegroundColor Yellow
    Write-Host "- DB_PASSWORD" -ForegroundColor Yellow
    Write-Host "- YANDEX_MAPS_API_KEY (опционально)" -ForegroundColor Yellow
} else {
    Write-Host ""
    Write-Host "Ошибка при деплое. Пожалуйста, проверьте логи выше." -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Деплой завершен ===" -ForegroundColor Green 