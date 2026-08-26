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

Одна команда заполняет все бизнес-таблицы:

```bash
sail artisan db:seed
# или с нуля:
sail artisan migrate:fresh --seed
```

`DatabaseSeeder` в начале делает `TRUNCATE … RESTART IDENTITY CASCADE` по users / projects / rooms / walls / jobs / materials / room_materials / картинкам и связанным токенам, удаляет файлы `storage/app/public/materials/seed-*`, затем вызывает:

1. `UserSeeder` — 6 пользователей, пароль у всех **`password`**. Демо: `demo@example.com`. Пустой список проектов: `empty@example.com`.
2. `ProjectSeeder` — 22 проекта, привязка по email (у демо 12 — вторая страница пагинации).
3. `RoomSeeder` — ~47 комнат с русскими именами.
4. `RoomWallSeeder` — замкнутые контуры: прямоугольник, проём (`is_real = 0`), Г-образная, коридор, пятиугольник; часть комнат без стен.
5. `MaterialSeeder` — 36 товаров из `Support/MaterialCatalog.php` (id 1…36). Калькулятор ГКЛ: **3** потолочный профиль, **4** направляющий, **5** подвес, **6** саморез металл, **11** саморез дерево, **14** семечка, **15/16** дюбель, **28** малярная лента. У 10 товаров — плейсхолдеры из `database/seeders/fixtures/materials/` (у id 1 и 3 по три кадра).
6. `RoomJobsSeeder` — `more_info` как массив `{name, amount, price, sum}` (натяжной / ГКЛ).
7. `RoomMaterialSeeder` — живые FK, `sum` с учётом фасовки `N шт` в title.

Повторный `db:seed` даёт тот же набор. Фабрики для Pest не трогаем.

Отдельный класс (`--class=RoomSeeder` и т.д.) по-прежнему можно вызвать вручную, но без truncate он ожидает уже существующие родители.

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
