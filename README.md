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

- Регистрация, вход и выход в SPA; сброс пароля и верификация email доступны через API (Sanctum Bearer-токен).
- CRUD проектов: название, описание, место, координаты.
- CRUD комнат внутри проекта (имя, описание, высота).
- Редактор стен комнаты: длина (см), угол (°), флаг «настоящая стена / проём».
- SVG-чертёж контура, проверка замкнутости многоугольника, периметр и площади.
- Калькуляторы работ:
  - **натяжной потолок** (типы полотна, багеты, люстры, светильники, доставка);
  - **гипсокартон** (потолок и/или стены, корректировка м²);
  - шпатлёвка — расчёт работ и материала отдельно для потолка и выбранных стен.
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

Нужны Docker, Composer и PHP 8.2+ для первоначального `composer install` (удобно через WSL2 / Linux). Само приложение работает на PHP 8.4 внутри Sail.

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

Для картинок оставляйте `APP_URL` без порта (`http://localhost`): `MaterialImageResource` сам добавляет `APP_PORT`. У текущей реализации есть обратная сторона — Laravel-generated ссылки в письмах не получают нестандартный порт; сброс пароля также теряет token в notification-классе (см. [известные ограничения](docs/development.md#известные-ограничения)). Redis по умолчанию не используется; при переключении кэша или очереди на Redis задайте `REDIS_HOST=redis`.

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

После `db:seed` заполняются бизнес-таблицы (пользователи, проекты, комнаты, стены, справочник материалов, работы и позиции сметы). Повторный `db:seed` очищает эти таблицы и сеет заново.

Войти в UI:

| Email | Пароль | Что внутри |
|---|---|---|
| **`demo@example.com`** | **`password`** | 12 проектов, главный — 12 комнат с разными контурами и сметой |
| `anna@example.com`, `petr@example.com`, `olga@example.com`, `sergey@example.com` | `password` | меньшие сметы |
| `empty@example.com` | `password` | нет проектов (пустой список) |

Справочник материалов — 36 позиций с витринными названиями (Петрович, Лемана ПРО, OBI). Подключённый калькулятор материалов потолка ГКЛ использует id **3, 4, 5, 11, 14, 15**; черновики материалов стен и натяжного потолка дополнительно ссылаются на **6, 16, 28**.

Дополнительно точечно:

```bash
sail artisan make:projects {user_id} --count=5
sail artisan make:rooms {project_id} --count=3
sail artisan room-walls:create {room_id} --count=4
```

Команда стен **не гарантирует замкнутый контур** — сидер даёт замкнутые фигуры, эта artisan-команда нет.

## Тесты

Backend (Pest, БД `testing` из `phpunit.xml`):

```bash
sail artisan test
sail artisan test tests/Feature/Api/V1/RoomControllerTest.php
sail artisan test tests/Feature/Api/V1/RoomControllerTest.php --filter="returns paginated room list"
```

Frontend (команды выполняются в контейнере `quasar-node`):

```bash
docker compose exec quasar-node npm run test:unit:ci
docker compose exec quasar-node npm run test:unit:ci -- -t ProjectRoomsPage
docker compose exec quasar-node npm run test:unit:ci -- test/vitest/__tests__/rooms/ProjectRoomsPage.test.js
```

Пустой каталог `tests/Unit` сохранён через `.gitkeep`, потому что он указан как suite в `phpunit.xml`.

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
