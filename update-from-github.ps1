# PowerShell скрипт для обновления проекта с GitHub

Write-Host "=== Обновление проекта 'Лесной дворик' с GitHub ===" -ForegroundColor Green
Write-Host ""

# Проверка наличия git
$gitInstalled = $null
try {
    $gitInstalled = Get-Command git -ErrorAction SilentlyContinue
} catch {
    # Ничего не делаем, просто продолжаем
}

if ($null -eq $gitInstalled) {
    Write-Host "Git не установлен. Пожалуйста, установите Git и повторите попытку." -ForegroundColor Red
    Write-Host "Скачать Git можно с сайта: https://git-scm.com/download/win" -ForegroundColor Yellow
    exit 1
}

# Проверка, находимся ли мы в git репозитории
if (-not (Test-Path ".git")) {
    Write-Host "Текущая директория не является git репозиторием." -ForegroundColor Red
    Write-Host "Пожалуйста, перейдите в корневую директорию проекта и повторите попытку." -ForegroundColor Yellow
    exit 1
}

# Сохранение локальных изменений
Write-Host "Проверка локальных изменений..." -ForegroundColor Cyan
$status = git status --porcelain
if ($status) {
    Write-Host "Обнаружены локальные изменения. Сохраняем их..." -ForegroundColor Yellow
    git stash
    $stashed = $true
} else {
    $stashed = $false
}

# Получение последних изменений
Write-Host "Получение последних изменений с GitHub..." -ForegroundColor Cyan
git fetch origin

# Обновление локальной ветки
$currentBranch = git rev-parse --abbrev-ref HEAD
Write-Host "Обновление ветки $currentBranch..." -ForegroundColor Cyan
git pull origin $currentBranch

# Восстановление локальных изменений
if ($stashed) {
    Write-Host "Восстановление локальных изменений..." -ForegroundColor Yellow
    git stash pop
}

# Проверка наличия npm
$npmInstalled = $null
try {
    $npmInstalled = Get-Command npm -ErrorAction SilentlyContinue
} catch {
    # Ничего не делаем, просто продолжаем
}

if ($null -ne $npmInstalled) {
    # Обновление зависимостей, если есть package.json
    if (Test-Path "package.json") {
        Write-Host "Обновление npm зависимостей..." -ForegroundColor Cyan
        npm install
    }
}

Write-Host ""
Write-Host "Обновление проекта завершено!" -ForegroundColor Green
Write-Host ""
Write-Host "Для деплоя обновленной версии на Vercel выполните:" -ForegroundColor Yellow
Write-Host "    .\vercel-deploy.ps1" -ForegroundColor Yellow
Write-Host ""
Write-Host "=== Обновление завершено ===" -ForegroundColor Green 