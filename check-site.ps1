# PowerShell скрипт для проверки работоспособности сайта

Write-Host "=== Проверка работоспособности сайта 'Лесной дворик' ===" -ForegroundColor Green
Write-Host ""

# Запрос URL сайта
$siteUrl = Read-Host "Введите URL вашего сайта (например, https://lesnoy-dvorik.vercel.app)"

if (-not $siteUrl) {
    Write-Host "URL не указан. Используем тестовый URL: https://lesnoy-dvorik.vercel.app" -ForegroundColor Yellow
    $siteUrl = "https://lesnoy-dvorik.vercel.app"
}

# Проверка доступности сайта
Write-Host ""
Write-Host "Проверка доступности сайта..." -ForegroundColor Cyan

try {
    $response = Invoke-WebRequest -Uri $siteUrl -UseBasicParsing -TimeoutSec 10
    $statusCode = $response.StatusCode
    
    if ($statusCode -eq 200) {
        Write-Host "Сайт доступен! Статус: $statusCode OK" -ForegroundColor Green
    } else {
        Write-Host "Сайт отвечает, но с необычным статусом: $statusCode" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Ошибка при проверке сайта: $_" -ForegroundColor Red
    Write-Host "Сайт недоступен или возникла ошибка при подключении." -ForegroundColor Red
    exit 1
}

# Проверка основных страниц
$pagesToCheck = @(
    "",                # Главная страница
    "rooms",           # Номера
    "services",        # Услуги
    "contacts",        # Контакты
    "booking"          # Бронирование
)

Write-Host ""
Write-Host "Проверка основных страниц сайта..." -ForegroundColor Cyan

foreach ($page in $pagesToCheck) {
    $pageUrl = if ($page) { "$siteUrl/$page" } else { $siteUrl }
    
    try {
        $response = Invoke-WebRequest -Uri $pageUrl -UseBasicParsing -TimeoutSec 5
        $statusCode = $response.StatusCode
        
        if ($statusCode -eq 200) {
            $pageTitle = if ($page) { $page } else { "Главная" }
            Write-Host "✓ Страница '$pageTitle' доступна!" -ForegroundColor Green
        } else {
            $pageTitle = if ($page) { $page } else { "Главная" }
            Write-Host "⚠ Страница '$pageTitle' отвечает с необычным статусом: $statusCode" -ForegroundColor Yellow
        }
    } catch {
        $pageTitle = if ($page) { $page } else { "Главная" }
        Write-Host "✗ Страница '$pageTitle' недоступна: $_" -ForegroundColor Red
    }
}

# Проверка загрузки изображений
Write-Host ""
Write-Host "Проверка загрузки изображений..." -ForegroundColor Cyan

try {
    $response = Invoke-WebRequest -Uri $siteUrl -UseBasicParsing
    $imgTags = ([regex]'<img[^>]+src="([^"]+)"').Matches($response.Content)
    
    if ($imgTags.Count -gt 0) {
        $imgCount = 0
        $successCount = 0
        
        foreach ($img in $imgTags) {
            $imgSrc = $img.Groups[1].Value
            $imgUrl = if ($imgSrc.StartsWith("http")) { $imgSrc } else { "$siteUrl/$($imgSrc.TrimStart('/'))" }
            $imgCount++
            
            try {
                $imgResponse = Invoke-WebRequest -Uri $imgUrl -UseBasicParsing -TimeoutSec 5 -Method Head
                if ($imgResponse.StatusCode -eq 200) {
                    $successCount++
                }
            } catch {
                # Ничего не делаем, просто продолжаем
            }
        }
        
        $successRate = [math]::Round(($successCount / $imgCount) * 100)
        
        if ($successRate -eq 100) {
            Write-Host "Все изображения ($imgCount) загружаются корректно!" -ForegroundColor Green
        } elseif ($successRate -ge 80) {
            Write-Host "Большинство изображений загружается корректно: $successCount из $imgCount ($successRate%)" -ForegroundColor Yellow
        } else {
            Write-Host "Проблемы с загрузкой изображений: только $successCount из $imgCount ($successRate%) загружаются корректно" -ForegroundColor Red
        }
    } else {
        Write-Host "На главной странице не найдено изображений." -ForegroundColor Yellow
    }
} catch {
    Write-Host "Ошибка при проверке изображений: $_" -ForegroundColor Red
}

# Проверка времени загрузки
Write-Host ""
Write-Host "Проверка времени загрузки сайта..." -ForegroundColor Cyan

$measure = Measure-Command {
    try {
        Invoke-WebRequest -Uri $siteUrl -UseBasicParsing -TimeoutSec 20 | Out-Null
    } catch {
        # Ничего не делаем, просто продолжаем
    }
}

$loadTime = $measure.TotalSeconds

if ($loadTime -lt 1) {
    Write-Host "Отличное время загрузки: $([math]::Round($loadTime, 2)) секунд!" -ForegroundColor Green
} elseif ($loadTime -lt 3) {
    Write-Host "Хорошее время загрузки: $([math]::Round($loadTime, 2)) секунд" -ForegroundColor Green
} elseif ($loadTime -lt 5) {
    Write-Host "Среднее время загрузки: $([math]::Round($loadTime, 2)) секунд" -ForegroundColor Yellow
} else {
    Write-Host "Медленное время загрузки: $([math]::Round($loadTime, 2)) секунд" -ForegroundColor Red
    Write-Host "Рекомендуется оптимизировать сайт для более быстрой загрузки." -ForegroundColor Yellow
}

# Общий результат проверки
Write-Host ""
Write-Host "=== Результаты проверки ===" -ForegroundColor Green
Write-Host ""
Write-Host "Сайт: $siteUrl" -ForegroundColor Cyan
Write-Host "Статус: " -NoNewline
if ($statusCode -eq 200) {
    Write-Host "Работает" -ForegroundColor Green
} else {
    Write-Host "Есть проблемы" -ForegroundColor Yellow
}
Write-Host "Время загрузки: $([math]::Round($loadTime, 2)) секунд" -ForegroundColor Cyan

Write-Host ""
Write-Host "=== Проверка завершена ===" -ForegroundColor Green 