# Разработка

## Локальный цикл

См. быстрый старт в [README](../README.md). Кратко:

```bash
composer install
cp .env.example .env
# порты и DB_* как в README
sail up -d
sail artisan key:generate
sail artisan migrate
sail artisan storage:link
sail artisan db:seed
```

Фронт: http://localhost:{QUASAR_PORT}  
API: http://localhost:{APP_PORT}

После смены `APP_PORT` поправьте `quasar_frontend/src/config.js`.

Код Laravel монтируется в контейнер целиком, PHP подхватывает изменения без rebuild образа. Quasar — HMR Vite.

## Переменные окружения

Значимые для этого проекта (остальное — стандарт Laravel):

| Переменная | Зачем |
|---|---|
| `DB_*` | Postgres. В Docker `DB_HOST=pgsql` |
| `APP_PORT` | Публичный порт API и часть URL картинок |
| `FORWARD_DB_PORT` / `FORWARD_REDIS_PORT` | Проброс на хост |
| `QUASAR_PORT` | Dev-сервер UI |
| `QUASAR_NODE` | Доп. порт Node (Compose) |
| `QUASAR_VITEST_UI` | UI Vitest, если задан в env |
| `FILESYSTEM_DISK` | По умолчанию `local` (private). Картинки явно пишутся на диск `public` |

Тесты Pest переключают БД на `testing` (`phpunit.xml`). Sail кладёт init-скрипт создания testing-database в Postgres.

## Сидеры

`DatabaseSeeder` вызывает только:

1. `UserSeeder` — 10 пользователей (`UserFactory`, пароль `password`, locale `ru_RU`);
2. `ProjectSeeder` — 10 проектов, `user_id` случайный из существующих.

Остальные — вручную:

```bash
sail artisan db:seed --class=RoomSeeder
sail artisan db:seed --class=RoomWallSeeder
sail artisan db:seed --class=RoomJobsSeeder
sail artisan db:seed --class=MaterialSeeder
sail artisan db:seed --class=RoomMaterialSeeder
```

`MaterialSeeder` — 15 случайных товаров. Калькулятор ГКЛ ожидает конкретные **id** (профиль 3, направляющий 4, подвес 5, саморезы 6/11 и т.д.). После чистого seed нормы могут не совпасть с реальными товарами — для демо лучше завести материалы с нужными id или поправить константы в `DrywallBuildingMaterialsForCeil.vue`.

`RoomJobsSeeder` пишет `more_info` как текст Faker, тогда как API/UI ждут массив объектов — для ручного просмотра сметы лучше добавлять работы через калькулятор.

## Artisan-команды

| Команда | Что делает |
|---|---|
| `make:projects {user_id} {--count=1}` | Проекты фабрикой на пользователя |
| `make:rooms {project_id} {--count=1}` | Комнаты фабрикой |
| `room-walls:create {room_id} {--count=1}` | Стены фабрикой. **Контур не обязан быть замкнут** |

Примеры:

```bash
sail artisan make:projects 1 --count=5
sail artisan make:rooms 1 --count=3
sail artisan room-walls:create 1 --count=4
```

## Тесты бэкенда

Pest 3 + `RefreshDatabase` для Feature.

```
tests/Feature/Auth/
  LoginTest, RegisterTest, LogoutTest
  ForgotPasswordTest, ResetPasswordTest
  VerifyEmailTest, EmailVerificationNotificationTest

tests/Feature/Api/V1/
  ProjectControllerTest.php    # список, show, store, update, delete
  RoomControllerTest.php       # то же для комнат

tests/Feature/Api/V2/
  ProjectControllerTest.php
```

Нет feature-тестов на стены, работы, материалы комнаты, справочник и картинки.

```bash
sail artisan test
sail artisan test tests/Feature/Api/V1/ProjectControllerTest.php
sail artisan test --filter="can store a new project for v1"
```

В `phpunit.xml` указан suite `tests/Unit`. Каталог должен существовать (можно пустой), иначе раннер ругается.

## Тесты фронтенда

Vitest, Pinia testing, Vue Test Utils.

```bash
docker exec -it $(docker compose ps -q quasar-node) sh
npm run test:unit              # watch
npm run test:unit:ci           # один прогон
npm run test:unit -- -t LoginPage
npm run test:unit -- __tests__/projects/ProjectsPage.test.js
npm run test:unit:ui           # Vitest UI
npm run coverage               # coverage по src/pages
```

Покрыты Login / Register / Dashboard / Projects / Rooms. Калькуляторы и спецификация — нет.

## Стиль и линт

- PHP: Laravel Pint (`vendor/bin/pint`).
- JS/Vue: ESLint 9 + Prettier (`npm run lint`, `npm run format` в `quasar_frontend`).

## CI

`.github/workflows/` — ручные `workflow_dispatch` (успех/фейл, артефакт, custom action). **Автопрогона тестов на push нет.**

## Карта каталогов (важное)

```
app/Http/Controllers/Api/V1/   бизнес-API
app/Http/Controllers/Auth/     Sanctum auth
app/Http/Requests/             валидация
app/Http/Resources/V1|V2/      JSON
app/Models/                    8 сущностей
app/Policies/                  владение проектом/комнатой
database/migrations/           users … room_materials
quasar_frontend/src/pages/     экраны
quasar_frontend/src/stores/    Pinia
quasar_frontend/src/components/JobTypesCalcs/
quasar_frontend/src/components/BuildingMaterialCalcs/
```

## Известные ограничения

Зафиксировано по коду, не как backlog продукта:

1. **Расчёт только на клиенте.** Смена стен не обновляет уже сохранённые `RoomJob` / `RoomMaterial`.
2. **Жёсткие id материалов и цены** в калькуляторах ГКЛ и натяжного потолка.
3. **Справочник без ролей** — любой пользователь CRUD материалов и картинок. TODO в контроллере.
4. **`RoomController@index`** не проверяет, что проект принадлежит текущему пользователю.
5. **`RoomJobPolicy::viewAny/create`** = true для любого auth-пользователя; `room_id` в store приходит из тела.
6. **`RoomMaterialPolicy::create`** = false, но `store`/`bulkStore` authorize не вызывают.
7. **v2** — черновик, фронт не использует.
8. **`sleep(1)`** в v1 проектах и комнатах.
9. **Двери, окна, шпатлёвка** — пустые вкладки. Материалы ГКЛ для стен и крепёж натяжного — не доведены до записи в смету.
10. **`note` vs `notice`** у `RoomMaterial`: валидация `note`, колонка `notice`.
11. **`MaterialResource.is_free`** всегда `false`.
12. **`ProjectResource.created`** берётся из `created_at`, не из колонки `created`.
13. Меню layout — дефолт Quasar, не навигация приложения.
14. Кнопка «Добавить материал» на `/materials` без логики; update материала на бэкенде пустой.
15. GitHub Actions не гоняют Pest/Vitest.

## Полезные URL после `sail up`

| Что | URL |
|---|---|
| SPA | `http://localhost:9008` |
| API version | `http://localhost:8008/` |
| Health | `http://localhost:8008/up` |
| Картинки | `http://localhost:8008/storage/materials/...` |
