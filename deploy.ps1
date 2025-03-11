# Скрипт для деплоя проекта на Vercel
Write-Host "Начинаем деплой проекта на Vercel..." -ForegroundColor Green

# Проверяем наличие CLI Vercel
if (!(Get-Command vercel -ErrorAction SilentlyContinue)) {
    Write-Host "ОШИБКА: CLI Vercel не установлен. Устанавливаем..." -ForegroundColor Red
    npm install -g vercel
}

# Проверяем наличие файла .env
if (!(Test-Path -Path ".env")) {
    Write-Host "ПРЕДУПРЕЖДЕНИЕ: Файл .env отсутствует. Копируем из примера..." -ForegroundColor Yellow
    if (Test-Path -Path ".env.example") {
        Copy-Item -Path ".env.example" -Destination ".env"
        Write-Host "Скопирован .env.example в .env. Пожалуйста, отредактируйте файл .env с вашими настройками." -ForegroundColor Yellow
    } else {
        Write-Host "ОШИБКА: Файл .env.example не найден. Создайте файл .env вручную." -ForegroundColor Red
    }
}

# Проверяем, что мы находимся в корне проекта
if (!(Test-Path -Path "vercel.json")) {
    Write-Host "ОШИБКА: Файл vercel.json не найден. Убедитесь, что вы находитесь в корне проекта." -ForegroundColor Red
    Exit 1
}

# Запускаем деплой с переопределением некоторых настроек для отладки
Write-Host "Запускаем деплой на Vercel..." -ForegroundColor Green
vercel --prod --confirm --debug

# Проверяем статус деплоя
if ($LASTEXITCODE -eq 0) {
    Write-Host "Деплой успешно завершен!" -ForegroundColor Green
    
    # Получаем URL проекта
    $vercelInfo = vercel project ls --json | ConvertFrom-Json
    $projectName = $vercelInfo.name
    Write-Host "Проект доступен по адресу: https://$projectName.vercel.app" -ForegroundColor Cyan
    
    # Выводим справочную информацию
    Write-Host "`nПолезные команды:" -ForegroundColor Yellow
    Write-Host "- Просмотр логов: vercel logs" -ForegroundColor Yellow
    Write-Host "- Информация о деплое: vercel inspect" -ForegroundColor Yellow
    Write-Host "- Удаление деплоя: vercel remove" -ForegroundColor Yellow
} else {
    Write-Host "ОШИБКА при деплое. Проверьте вывод команды выше." -ForegroundColor Red
} 