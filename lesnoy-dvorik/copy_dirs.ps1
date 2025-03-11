$directories = @("admin", "assets", "css", "js", "includes", "pages", "errors", "docs", "data", "php")

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        Write-Host "Copying $dir to api/"
        Copy-Item -Path $dir -Destination "api/" -Recurse -Force -ErrorAction SilentlyContinue
    } else {
        Write-Host "Directory $dir does not exist"
    }
}

# Копирование основных файлов
$files = @("index.html", "sitemap.xml", "robots.txt")

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "Copying $file to api/"
        Copy-Item -Path $file -Destination "api/" -Force -ErrorAction SilentlyContinue
    } else {
        Write-Host "File $file does not exist"
    }
}

# Создание index.php в api/ если его нет
if (-not (Test-Path "api/index.php")) {
    Write-Host "Creating api/index.php"
    @"
<?php
// Определяем базовый путь
\$base_path = __DIR__;

// Получаем запрошенный URI
\$request_uri = \$_SERVER['REQUEST_URI'];

// Если запрос идет к index.html, просто включаем его
if (\$request_uri == '/' || \$request_uri == '/index.html') {
    include \$base_path . '/index.html';
    exit;
}

// Проверяем, существует ли запрошенный файл
\$file_path = \$base_path . \$request_uri;
if (file_exists(\$file_path) && !is_dir(\$file_path)) {
    // Определяем MIME-тип на основе расширения файла
    \$extension = pathinfo(\$file_path, PATHINFO_EXTENSION);
    switch (\$extension) {
        case 'html':
            header('Content-Type: text/html');
            break;
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'json':
            header('Content-Type: application/json');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
        // Добавьте другие типы файлов по необходимости
    }
    
    // Включаем файл напрямую
    include \$file_path;
    exit;
}

// Если файл не найден, перенаправляем на index.html
include \$base_path . '/index.html';
"@ | Out-File -FilePath "api/index.php" -Encoding UTF8
}

Write-Host "All files and directories have been copied to api/" 