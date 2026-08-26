# Renovation Estimator

Веб-приложение для сметы ремонта: пользователь ведёт **проекты** (объекты), внутри них **комнаты**, задаёт геометрию стен, считает работы и материалы, получает итоговую стоимость.

Интерфейс — SPA на Quasar. Бэкенд — REST API на Laravel 12 (токены Sanctum). Расчёты (площади, периметр, нормы материалов, стоимость работ) выполняются на клиенте и затем сохраняются через API.

Подробности:

| Документ | О чём |
|---|---|
| [docs/README.md](docs/README.md) | Оглавление документации |
| [docs/architecture.md](docs/architecture.md) | Стек, контейнеры, слои, авторизация |
| [docs/domain-model.md](docs/domain-model.md) | Сущности и связи |
| [docs/api.md](docs/api.md) | REST API (auth, v1, v2) |
| [docs/frontend.md](docs/frontend.md) | Страницы, stores, маршруты |
| [docs/calculations.md](docs/calculations.md) | Формулы площадей, ГКЛ, натяжной потолок |
| [docs/development.md](docs/development.md) | Тесты, сидеры, artisan-команды, ограничения |

## Что умеет приложение

- Регистрация, вход, выход, сброс пароля (Sanctum Bearer-токен).
- CRUD проектов: название, описание, место, координаты.
- CRUD комнат внутри проекта (имя, описание, высота).
- Редактор стен комнаты: длина (см), угол (°), флаг «настоящая стена / проём».
- SVG-чертёж контура, проверка замкнутости многоугольника, периметр и площади.
- Калькуляторы работ:
  - **натяжной потолок** (типы полотна, багеты, люстры, светильники, доставка);
  - **гипсокартон** (потолок и/или стены, корректировка м²);
  - шпатлёвка — заготовка вкладки.
- Справочник строительных материалов (карточка, фото, сортировка изображений).
- Привязка материалов к комнате (по одному или пачкой) и сумма сметы: материалы + работы.

Типичный сценарий: **проект → комнаты → стены → калькулятор работы → материалы → итоговая сумма**.

## Стек

| Слой | Технологии |
|---|---|
| Backend | PHP 8.4, Laravel 12, Laravel Sanctum 4, Pest 3 |
| Frontend | Vue 3, Quasar 2, Pinia 3, Vue Router 4, Axios, Vitest |
| БД / кэш | PostgreSQL 17, Redis |
| Инфра | Laravel Sail, Docker Compose, Node 22 |

Сервисы в `docker-compose.yml`:

1. `laravel.test` — PHP 8.4 + Laravel (`sail-8.4/app`)
2. `pgsql` — PostgreSQL 17
3. `redis` — Redis Alpine
4. `quasar-node` — Node 22, `npx quasar dev --host 0.0.0.0 --port 9000`

## Быстрый старт

Нужны Docker, PHP 8.4 и Composer (удобно через WSL2 / Linux).

### 1. Зависимости PHP

```bash
composer install
```

Алиас Sail (если ещё нет):

```bash
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
```

### 2. Окружение

Скопируйте `.env.example` в `.env` и задайте БД и порты так, чтобы они не конфликтовали с другими контейнерами:

```bash
cp .env.example .env
```

Минимум для БД:

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

Порты хоста (значения из `.env.example`):

```env
APP_PORT=8008
FORWARD_DB_PORT=54329
FORWARD_REDIS_PORT=63794
QUASAR_PORT=9008
QUASAR_NODE=3348
```

Базовый URL API на фронте должен совпадать с `APP_PORT`. Файл:

`quasar_frontend/src/config.js`

```js
export const API_CONFIG = {
  baseURL: 'http://localhost:8008/api',
};
```

Если сменили `APP_PORT`, поменяйте и `baseURL`.

### 3. Контейнеры и Laravel

```bash
sail up -d
sail artisan key:generate
sail artisan migrate
sail artisan storage:link
sail artisan db:seed
```

`storage:link` нужен для публичных фото материалов (`storage/app/public/materials` → `/storage/...`).

Quasar поднимается вместе с Compose (`npm ci || npm i`, затем `quasar dev`). Отдельный `docker exec` + `npm run dev` обычно не нужен.

### 4. Открыть приложение

- Фронтенд: http://localhost:9008
- API: http://localhost:8008 (`GET /` вернёт версию Laravel, health — `GET /up`)

После `db:seed` в таблице `users` будут тестовые пользователи. Пароль у всех фабричных — **`password`**. Email смотрите в БД (`users.email`).

Создать дополнительные сущности:

```bash
sail artisan make:projects {user_id} --count=5
sail artisan make:rooms {project_id} --count=3
sail artisan room-walls:create {room_id} --count=4
```

Команда стен **не гарантирует замкнутый контур** — для сметы стены лучше задать в UI.

## Тесты

Backend (Pest, БД `testing` из `phpunit.xml`):

```bash
sail artisan test
sail artisan test tests/Feature/Api/V1/RoomControllerTest.php
sail artisan test tests/Feature/Api/V1/RoomControllerTest.php --filter="returns paginated room list"
```

Frontend (из контейнера `quasar-node`):

```bash
docker exec -it $(docker compose ps -q quasar-node) sh
npm run test:unit
npm run test:unit -- -t ProjectRoomsPage
npm run test:unit -- __tests__/rooms/ProjectRoomsPage.test.js
```

Каталог `tests/Unit` в корне Laravel нужен phpunit-сьюту (см. `phpunit.xml`). Если его нет — создайте пустой.

## Структура репозитория

```
app/                  Laravel: модели, контроллеры, policies, requests, resources
database/             миграции, фабрики, сидеры
routes/               api.php (auth), api_v1.php, api_v2.php, web.php
tests/                Pest feature-тесты
quasar_frontend/      SPA Quasar (страницы, stores, калькуляторы)
docker-compose.yml    Sail + Postgres + Redis + Quasar
docs/                 подробная документация
```

© 2026 nvlight. All rights reserved.
