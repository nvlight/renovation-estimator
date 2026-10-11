# Архитектура

## Обзор

Два независимых процесса, связанные по HTTP:

```
┌─────────────────────────┐         Bearer token          ┌─────────────────────────┐
│  Quasar SPA             │  ───────────────────────────► │  Laravel 12 API         │
│  Vue 3 + Pinia          │     /api/...                  │  Sanctum + Eloquent     │
│  localhost:{QUASAR_PORT}│ ◄───────────────────────────  │  localhost:{APP_PORT}   │
└─────────────────────────┘     JSON (JsonResource)       └───────────┬─────────────┘
                                                                      │
                                                                      ▼
                                                            PostgreSQL 17
                                                            Redis (Sail)
```

Фронтенд **не** рендерится Laravel. `routes/web.php` отдаёт только `{"Laravel": "<version>"}`. SPA живёт в `quasar_frontend/` и ходит в API через Axios (`quasar_frontend/src/boot/axios.js` + `src/config.js`).

Расчёты сметы — на клиенте. Сервер хранит уже посчитанные `sum` / `amount` / `more_info`.

## Контейнеры

`docker-compose.yml`:

| Сервис | Образ | Порт хоста (по умолчанию `.env.example`) | Назначение |
|---|---|---|---|
| `laravel.test` | `sail-8.4/app` | `APP_PORT` → 80 (`8008`) | `artisan serve` под Supervisor |
| `pgsql` | `postgres:17` | `FORWARD_DB_PORT` → 5432 (`54329`) | Основная БД |
| `redis` | `redis:alpine` | `FORWARD_REDIS_PORT` → 6379 (`63794`) | Доступный Redis-сервис; defaults приложения используют БД |
| `quasar-node` | `node:22-alpine` | `QUASAR_PORT` → 9000 (`9008`), `QUASAR_NODE` → 3000 (`3348`), `QUASAR_VITEST_UI` → 51204 (`51204`) | Dev-сервер Quasar и Vitest UI |

Quasar стартует командой:

```text
sh -c "npm ci || npm i; npx quasar dev --host 0.0.0.0 --port 9000"
```

Том: `./quasar_frontend` → `/usr/src/app`. `laravel.test` монтирует весь репозиторий в `/var/www/html`.

Сеть: bridge `sail`. Laravel видит Postgres как `pgsql` (`DB_HOST=pgsql`). В `.env.example` Redis не выбран как cache/queue backend; при его включении внутри Compose нужен `REDIS_HOST=redis`.

## Слои бэкенда

Laravel 12, маршруты регистрируются в `bootstrap/app.php`:

- `routes/web.php` — служебный корень;
- `routes/api.php` — auth;
- `routes/api_v1.php` — основной API;
- `routes/api_v2.php` — черновик v2 (только проекты);
- health: `GET /up`.

Префикс `/api` добавляет Laravel автоматически.

Типичный запрос:

```
HTTP → Route (auth:sanctum)
     → FormRequest (authorize + rules)
     → Controller
     → Policy (Gate / $this->authorize)
     → Eloquent model
     → JsonResource / JsonResponse
```

| Каталог | Роль |
|---|---|
| `app/Models` | Eloquent-модели, fillable, casts, связи |
| `app/Http/Controllers/Api/V1` | CRUD и bulk-операции |
| `app/Http/Controllers/Api/V2` | Урезанный ProjectController |
| `app/Http/Controllers/Auth` | login / register / logout / password / verify |
| `app/Http/Requests` | Валидация входа |
| `app/Http/Resources/V1` и `V2` | Форма JSON |
| `app/Policies` | Владение ресурсом через `user_id` проекта |
| `app/Exceptions/CustomExceptionHandlers.php` | 404 на русском для известных моделей |
| `app/Console/Commands` | Генерация тестовых проектов/комнат/стен |
| `app/Notifications` | Письмо сброса пароля |

`AppServiceProvider` явно вешает `ProjectPolicy` и `RoomPolicy`. Остальные policies (`RoomJob`, `RoomMaterial`, `RoomWall`) подхватываются соглашением имён Laravel.

## Аутентификация

Laravel Sanctum, **stateless Bearer-токен** (не cookie SPA-сессия).

1. `POST /api/login` или `POST /api/register` → `createToken('auth-token')->plainTextToken`.
2. Login кладёт токен в `localStorage.auth_token`, пользователя — в `localStorage.user`. Register сохраняет токен, но сейчас не сохраняет `user` в localStorage.
3. Axios: `Authorization: Bearer <token>` (`stores/auth.js` + `initializeAuth()` на каждом navigation guard).
4. Logout: `currentAccessToken()->delete()`, чистка localStorage.

Гостевые маршруты: `guest:sanctum`. Защищённые: `auth:sanctum`.

Email-верификация и reset password имеют API endpoints и покрыты Pest-тестами, но в SPA-экранах не используются. Текущий `ResetPasswordNotification` не сохраняет переданный token, поэтому сформированная ссылка сброса нерабочая; тест forgot-password подменяет отправку уведомления и этого не обнаруживает.

Пароль фабричных пользователей — `password` (`UserFactory`).

## Авторизация (кто что видит)

Правило: **проект принадлежит пользователю; всё вложенное — через `room.project.user_id`**.

| Policy | Что проверяет |
|---|---|
| `ProjectPolicy` | `user.id === project.user_id` (view/update/delete) |
| `RoomPolicy` | `room.project.user.id === user.id` |
| `RoomWallPolicy` | через `roomWall.room.project.user` |
| `RoomJobPolicy` | `view`/`delete` — владелец комнаты; `viewAny` и `create` сейчас `true` для любого аутентифицированного |
| `RoomMaterialPolicy` | `viewAny($user, $room)` и `delete` — владелец; `create`/`update` возвращают `false` (store материалов authorize не вызывает) |

Справочник `Material` / `MaterialImage` **не** разграничен по ролям: любой залогиненный пользователь может читать, создавать и удалять. В контроллерах есть TODO про `user.role === ADMIN`.

Список комнат (`RoomController@index`) не вызывает `authorize` на проект: достаточно знать `project_id` и иметь токен.

## Исключения

`CustomExceptionHandlers.php` перехватывает `NotFoundHttpException` для JSON:

| Модель | Сообщение |
|---|---|
| Project | Проект не найден |
| Room | Комната не найдена |
| RoomWall | Стена комнаты не найдена |
| Material | Материал не найден |
| MaterialImage | Картинка материала не найдена |
| иное | Ресурс не найден |

## Файлы

Картинки материалов пишутся на диск `public`:

```text
$request->file('name')->store('materials', 'public')
```

Путь в БД: `materials/<filename>`. Публичный URL собирает `MaterialImageResource` из `APP_URL` + `APP_PORT` + `/storage/` + `name`, поэтому для картинок `APP_URL` должен быть без порта (как в `.env.example`). Это конфликтует с Laravel URL generation на нестандартном host-порту: notification links тогда не содержат `APP_PORT`. Нужна команда `sail artisan storage:link`.

Ограничения загрузки (`StoreMaterialImageRequest`): jpeg/png/jpg/webp, до 5 МБ, размер кадра 250×250 … 1200×925.

При прямом удалении картинки файл снимается с диска. Удаление материала каскадно удаляет строки `material_images`, но `MaterialController` не удаляет соответствующие физические файлы.

## Версии API

- **v1** — рабочая: проекты, комнаты, стены, работы, материалы комнаты, справочник, картинки.
- **v2** — только CRUD проектов, без поля `created` при store, другой `ProjectResource`. Используется тестами, фронт ходит в **v1**.

## Искусственные задержки

В v1 `ProjectController` и `RoomController` стоят `sleep(1)` на store/update/destroy (и index комнат). Это не бизнес-логика, а имитация «долгой» операции для спиннеров UI.

## Очереди и почта

По умолчанию `QUEUE_CONNECTION=database`, `MAIL_MAILER=log`. Письма (verify, reset) пишутся в лог, отдельный worker в Compose не запущен. Redis в стеке есть, но кэш/сессии в `.env.example` — `database`.
