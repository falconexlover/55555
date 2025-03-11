# PowerShell скрипт для очистки временных файлов проекта

Write-Host "=== Очистка временных файлов проекта 'Лесной дворик' ===" -ForegroundColor Green
Write-Host ""

# Список директорий для очистки
$cleanupDirs = @(
    "api/uploads/temp",
    "api/logs",
    "api/cache"
)

# Список расширений временных файлов для удаления
$tempExtensions = @(
    "*.tmp",
    "*.temp",
    "*.log",
    "*.bak",
    "*.cache"
)

# Очистка временных директорий
foreach ($dir in $cleanupDirs) {
    if (Test-Path $dir) {
        Write-Host "Очистка директории $dir..." -ForegroundColor Cyan
        
        # Удаление всех файлов в директории, но сохранение самой директории
        Get-ChildItem -Path $dir -File -Recurse | Remove-Item -Force
        
        # Создание файла .gitkeep, чтобы директория сохранялась в репозитории
        if (-not (Test-Path "$dir/.gitkeep")) {
            New-Item -Path "$dir/.gitkeep" -ItemType File -Force | Out-Null
        }
        
        Write-Host "Директория $dir очищена." -ForegroundColor Green
    } else {
        Write-Host "Директория $dir не существует. Создаем..." -ForegroundColor Yellow
        New-Item -Path $dir -ItemType Directory -Force | Out-Null
        New-Item -Path "$dir/.gitkeep" -ItemType File -Force | Out-Null
        Write-Host "Директория $dir создана." -ForegroundColor Green
    }
}

# Поиск и удаление временных файлов во всем проекте
Write-Host ""
Write-Host "Поиск и удаление временных файлов..." -ForegroundColor Cyan

$tempFilesCount = 0

foreach ($ext in $tempExtensions) {
    $files = Get-ChildItem -Path "." -Filter $ext -File -Recurse -Exclude ".gitkeep"
    $tempFilesCount += $files.Count
    
    foreach ($file in $files) {
        Write-Host "Удаление файла $($file.FullName)..." -ForegroundColor Yellow
        Remove-Item -Path $file.FullName -Force
    }
}

Write-Host ""
if ($tempFilesCount -gt 0) {
    Write-Host "Всего удалено временных файлов: $tempFilesCount" -ForegroundColor Green
} else {
    Write-Host "Временных файлов не найдено." -ForegroundColor Green
}

# Очистка кэша браузера (инструкция)
Write-Host ""
Write-Host "Для полной очистки рекомендуется также очистить кэш браузера:" -ForegroundColor Yellow
Write-Host "1. Chrome: Ctrl+Shift+Delete" -ForegroundColor Yellow
Write-Host "2. Firefox: Ctrl+Shift+Delete" -ForegroundColor Yellow
Write-Host "3. Edge: Ctrl+Shift+Delete" -ForegroundColor Yellow
Write-Host "4. Safari: Option+Command+E" -ForegroundColor Yellow

Write-Host ""
Write-Host "=== Очистка завершена ===" -ForegroundColor Green 