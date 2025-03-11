#!/bin/bash
# Скрипт для сборки PHP-проекта на Vercel

echo "=== Начало сборки ==="

# Создаем директорию public, если ее нет
mkdir -p public

# Копируем статические файлы в public
cp -R api/public/* public/ 2>/dev/null || :
cp -R css public/ 2>/dev/null || :
cp -R js public/ 2>/dev/null || :
cp -R assets public/ 2>/dev/null || :

# Копируем index.html из api в public
echo "Копируем api/index.html в public"
cp api/index.html public/index.html 2>/dev/null || :

# Копируем index.html из api/public в public, если его нет
if [ ! -f "public/index.html" ]; then
    echo "Копируем index.html из api/public в public"
    cp api/public/index.html public/index.html 2>/dev/null || :
fi

echo "=== Статические файлы скопированы ==="

# Проверяем структуру директорий
echo "Структура public директории:"
ls -la public/

echo "Структура api директории:"
ls -la api/

# Убеждаемся, что PHP-файлы имеют правильные права
chmod -R 755 api/

echo "=== Права файлов обновлены ==="

# Проверяем наличие php.ini
if [ -f "api/php.ini" ]; then
  echo "PHP.ini найден"
else
  echo "PHP.ini не найден, создаем..."
  touch api/php.ini
fi

echo "=== Сборка завершена ===" 