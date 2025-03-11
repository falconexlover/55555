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

echo "=== Статические файлы скопированы ==="

# Убеждаемся, что PHP-файлы имеют правильные права
chmod -R 755 api/

echo "=== Права файлов обновлены ==="

# Проверяем наличие php.ini
if [ -f "api/php.ini" ]; then
  echo "PHP.ini найден"
else
  echo "PHP.ini не найден, создаем..."
  cp api/php.ini api/
fi

echo "=== Сборка завершена ===" 