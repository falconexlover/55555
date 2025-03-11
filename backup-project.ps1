# PowerShell скрипт для резервного копирования проекта

Write-Host "=== Резервное копирование проекта 'Лесной дворик' ===" -ForegroundColor Green
Write-Host ""

# Создание директории для резервных копий, если она не существует
$backupDir = "backups"
if (-not (Test-Path $backupDir)) {
    Write-Host "Создание директории для резервных копий..." -ForegroundColor Cyan
    New-Item -Path $backupDir -ItemType Directory | Out-Null
}

# Получение текущей даты и времени для имени файла
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$backupFileName = "backup_$timestamp.zip"
$backupPath = Join-Path $backupDir $backupFileName

# Список директорий и файлов для исключения из резервной копии
$excludeDirs = @(
    "node_modules",
    ".git",
    "backups",
    "uploads/temp"
)

# Создание временного файла со списком исключений
$excludeFile = Join-Path $env:TEMP "backup-exclude.txt"
$excludeDirs | Out-File -FilePath $excludeFile

Write-Host "Создание резервной копии проекта..." -ForegroundColor Cyan

# Проверка наличия 7-Zip
$7zipPath = "C:\Program Files\7-Zip\7z.exe"
if (Test-Path $7zipPath) {
    # Использование 7-Zip для создания архива
    Write-Host "Используем 7-Zip для создания архива..." -ForegroundColor Cyan
    & $7zipPath a -tzip $backupPath . -xr@"$excludeFile"
} else {
    # Использование встроенных средств PowerShell
    Write-Host "7-Zip не найден, используем встроенные средства PowerShell..." -ForegroundColor Yellow
    
    # Создание временной директории для файлов, которые будут включены в архив
    $tempDir = Join-Path $env:TEMP "backup-temp"
    if (Test-Path $tempDir) {
        Remove-Item -Path $tempDir -Recurse -Force
    }
    New-Item -Path $tempDir -ItemType Directory | Out-Null
    
    # Копирование файлов, исключая указанные директории
    Get-ChildItem -Path . -Recurse | ForEach-Object {
        $relativePath = $_.FullName.Substring((Get-Location).Path.Length + 1)
        $exclude = $false
        
        foreach ($dir in $excludeDirs) {
            if ($relativePath -like "$dir*") {
                $exclude = $true
                break
            }
        }
        
        if (-not $exclude -and -not $_.PSIsContainer) {
            $targetPath = Join-Path $tempDir $relativePath
            $targetDir = Split-Path -Path $targetPath -Parent
            
            if (-not (Test-Path $targetDir)) {
                New-Item -Path $targetDir -ItemType Directory -Force | Out-Null
            }
            
            Copy-Item -Path $_.FullName -Destination $targetPath -Force
        }
    }
    
    # Создание архива
    Compress-Archive -Path "$tempDir\*" -DestinationPath $backupPath -Force
    
    # Очистка временной директории
    Remove-Item -Path $tempDir -Recurse -Force
}

# Удаление временного файла со списком исключений
Remove-Item -Path $excludeFile -Force

# Проверка успешности создания архива
if (Test-Path $backupPath) {
    $backupSize = (Get-Item $backupPath).Length / 1MB
    Write-Host ""
    Write-Host "Резервная копия успешно создана!" -ForegroundColor Green
    Write-Host "Путь: $backupPath" -ForegroundColor Green
    Write-Host "Размер: $([math]::Round($backupSize, 2)) МБ" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "Ошибка при создании резервной копии." -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Резервное копирование завершено ===" -ForegroundColor Green 