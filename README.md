# Real-Time Auction Platform

Это учебный проект платформы для проведения онлайн аукционов с обновлением данных в реальном времени написанный на **Laravel 12**.
---

## Основные возможности

* **Real-time ставки:** Пополнение баланса и автоматическое ежедневное списание средств за активные сайты.
* **Система Anti-Sniping:** Автоматическое продление аукциона на 1 минуту, если ставка сделана менее чем за 30 секунд до конца.
* **Интерактивный Dashboard:** Отслеживание статуса своих ставок (Лидирую / Перебита / Победа) в реальном времени.
* **Автоматическое завершение:** Автоматическое закрытие лотов и определение победителей.
* **Авторизация:** Регистрация, вход и управление профилем (Laravel Breeze).

---

## Cтек

* **Framework:** Laravel 12
* **Frontend:** Blade, Tailwind CSS, Alpine.js
* **Database:** PostgreSQL
* **Real-time:** Laravel Reverb (WebSockets)
* **Environment:** Laravel Sail (Docker)

---

## Установка и запуск

1. **Клонируйте репозиторий и установите зависимости:**
   ```bash
    git clone https://github.com/diemock/auction-app
    cd auction-app
    cp .env.example .env
   ```

2. **Запустите окружение через Docker:**
   ```bash
   ./vendor/bin/sail up -d
   ```

3. **Установка зависимостей и ключей:**
   ```bash
    sail composer install
    sail artisan key:generate
    sail npm install
    sail npm run build
   ```

4. **Миграции и база данных:**
   ```bash
    sail artisan migrate
   ```
   
5. **Сервер очередей и сокетов:**
   ```bash
    sail artisan reverb:start
   ```
   
6. **Планировщик задач (для закрытия лотов):**
   ```bash
    sail artisan schedule:work
   ```
   
7. **Vite (для горячей перезагрузки стилей):**
   ```bash
    sail npm run dev
   ```

---

