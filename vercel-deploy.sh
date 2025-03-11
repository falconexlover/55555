#!/bin/bash
# Скрипт для деплоя проекта на Vercel

echo "=== Деплой проекта 'Лесной дворик' на Vercel ==="
echo ""

# Проверка наличия Vercel CLI
if ! command -v vercel &> /dev/null
then
    echo "Установка Vercel CLI..."
    npm install -g vercel
fi

# Проверка авторизации
echo "Проверка авторизации в Vercel..."
vercel whoami &> /dev/null
if [ $? -ne 0 ]; then
    echo "Требуется авторизация в Vercel..."
    vercel login
fi

# Деплой проекта
echo ""
echo "Начинаем деплой проекта..."
vercel --prod

# Проверка успешности деплоя
if [ $? -eq 0 ]; then
    echo ""
    echo "Деплой успешно завершен!"
    echo "Проверьте результат на вашем домене."
    echo ""
    echo "Не забудьте добавить переменные окружения в настройках проекта на Vercel:"
    echo "- DB_HOST"
    echo "- DB_NAME"
    echo "- DB_USER"
    echo "- DB_PASSWORD"
    echo "- YANDEX_MAPS_API_KEY (опционально)"
else
    echo ""
    echo "Ошибка при деплое. Пожалуйста, проверьте логи выше."
fi

echo ""
echo "=== Деплой завершен ===" 