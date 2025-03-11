# PowerShell скрипт для генерации отчета о состоянии проекта

Write-Host "=== Генерация отчета о состоянии проекта 'Лесной дворик' ===" -ForegroundColor Green
Write-Host ""

# Создание директории для отчетов, если она не существует
$reportsDir = "reports"
if (-not (Test-Path $reportsDir)) {
    Write-Host "Создание директории для отчетов..." -ForegroundColor Cyan
    New-Item -Path $reportsDir -ItemType Directory | Out-Null
}

# Получение текущей даты и времени для имени файла
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$reportFileName = "report_$timestamp.html"
$reportPath = Join-Path $reportsDir $reportFileName

# Получение информации о системе
$systemInfo = Get-ComputerInfo | Select-Object WindowsProductName, WindowsVersion, OsHardwareAbstractionLayer, CsManufacturer, CsModel

# Получение информации о проекте
$projectFiles = Get-ChildItem -Path "." -Recurse -File | Measure-Object
$projectDirs = Get-ChildItem -Path "." -Recurse -Directory | Measure-Object
$projectSize = (Get-ChildItem -Path "." -Recurse -File | Measure-Object -Property Length -Sum).Sum / 1MB

# Получение информации о git репозитории
$gitInfo = @{
    IsGitRepo = Test-Path ".git"
    Branch = ""
    LastCommit = ""
    CommitCount = 0
}

if ($gitInfo.IsGitRepo) {
    try {
        $gitInfo.Branch = git rev-parse --abbrev-ref HEAD
        $gitInfo.LastCommit = git log -1 --pretty=format:"%h - %an, %ar : %s"
        $gitInfo.CommitCount = git rev-list --count HEAD
    } catch {
        # Ничего не делаем, просто продолжаем
    }
}

# Получение информации о файлах проекта
$fileTypes = @{}
Get-ChildItem -Path "." -Recurse -File | ForEach-Object {
    $ext = $_.Extension.ToLower()
    if (-not $fileTypes.ContainsKey($ext)) {
        $fileTypes[$ext] = 0
    }
    $fileTypes[$ext]++
}

# Получение информации о последних изменениях
$recentChanges = @()
if ($gitInfo.IsGitRepo) {
    try {
        $recentChanges = git log -10 --pretty=format:"%h - %an, %ar : %s"
    } catch {
        # Ничего не делаем, просто продолжаем
    }
}

# Создание HTML отчета
$htmlReport = @"
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет о состоянии проекта "Лесной дворик"</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding: 8px 0;
        }
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
        }
        .info-value {
            text-align: right;
        }
        .chart {
            height: 200px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #7f8c8d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Отчет о состоянии проекта "Лесной дворик"</h1>
        <p>Отчет сгенерирован: $(Get-Date -Format "dd.MM.yyyy HH:mm:ss")</p>
        
        <div class="card">
            <h2>Общая информация</h2>
            <div class="info-row">
                <span class="info-label">Количество файлов:</span>
                <span class="info-value">$($projectFiles.Count)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Количество директорий:</span>
                <span class="info-value">$($projectDirs.Count)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Размер проекта:</span>
                <span class="info-value">$([math]::Round($projectSize, 2)) МБ</span>
            </div>
        </div>
        
        <div class="card">
            <h2>Информация о системе</h2>
            <div class="info-row">
                <span class="info-label">Операционная система:</span>
                <span class="info-value">$($systemInfo.WindowsProductName)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Версия Windows:</span>
                <span class="info-value">$($systemInfo.WindowsVersion)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Производитель:</span>
                <span class="info-value">$($systemInfo.CsManufacturer)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Модель:</span>
                <span class="info-value">$($systemInfo.CsModel)</span>
            </div>
        </div>
        
        <div class="card">
            <h2>Информация о Git репозитории</h2>
"@

if ($gitInfo.IsGitRepo) {
    $htmlReport += @"
            <div class="info-row">
                <span class="info-label">Текущая ветка:</span>
                <span class="info-value">$($gitInfo.Branch)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Последний коммит:</span>
                <span class="info-value">$($gitInfo.LastCommit)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Количество коммитов:</span>
                <span class="info-value">$($gitInfo.CommitCount)</span>
            </div>
"@
} else {
    $htmlReport += @"
            <p>Проект не является Git репозиторием.</p>
"@
}

$htmlReport += @"
        </div>
        
        <div class="card">
            <h2>Типы файлов</h2>
            <table>
                <tr>
                    <th>Расширение</th>
                    <th>Количество</th>
                </tr>
"@

foreach ($fileType in $fileTypes.GetEnumerator() | Sort-Object -Property Value -Descending) {
    $htmlReport += @"
                <tr>
                    <td>$($fileType.Key)</td>
                    <td>$($fileType.Value)</td>
                </tr>
"@
}

$htmlReport += @"
            </table>
        </div>
"@

if ($recentChanges.Count -gt 0) {
    $htmlReport += @"
        <div class="card">
            <h2>Последние изменения</h2>
            <table>
                <tr>
                    <th>Коммит</th>
                </tr>
"@

    foreach ($change in $recentChanges) {
        $htmlReport += @"
                <tr>
                    <td>$change</td>
                </tr>
"@
    }

    $htmlReport += @"
            </table>
        </div>
"@
}

$htmlReport += @"
        <div class="footer">
            <p>Отчет сгенерирован автоматически с помощью скрипта generate-report.ps1</p>
            <p>Гостиница "Лесной дворик" &copy; $(Get-Date -Format "yyyy")</p>
        </div>
    </div>
</body>
</html>
"@

# Сохранение отчета в файл
$htmlReport | Out-File -FilePath $reportPath -Encoding UTF8

# Проверка успешности создания отчета
if (Test-Path $reportPath) {
    Write-Host ""
    Write-Host "Отчет успешно создан!" -ForegroundColor Green
    Write-Host "Путь: $reportPath" -ForegroundColor Green
    
    # Открытие отчета в браузере
    $openReport = Read-Host "Открыть отчет в браузере? (y/n)"
    if ($openReport -eq "y" -or $openReport -eq "Y") {
        Start-Process $reportPath
    }
} else {
    Write-Host ""
    Write-Host "Ошибка при создании отчета." -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Генерация отчета завершена ===" -ForegroundColor Green 