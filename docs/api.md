# HTTP API

Базовый URL: `{APP_URL}:{APP_PORT}/api`  
Пример локально: `http://localhost:8008/api`

Формат: JSON. Списки ресурсов Laravel — `{ data, links, meta }`.  
Защищённые маршруты: заголовок `Authorization: Bearer <token>`.

Ошибки:

- `401` — нет/невалидный токен;
- `403` — policy отказала;
- `404` — см. русские сообщения в [architecture.md](architecture.md);
- `422` — валидация FormRequest.

---

## Auth (`routes/api.php`)

Гость (`guest:sanctum`):

| Метод | Путь | Имя | Тело | Ответ |
|---|---|---|---|---|
| POST | `/register` | `register` | `name`, `email`, `password`, `password_confirmation` | `201` `{ user, token }` |
| POST | `/login` | `login` | `email`, `password` | `200` `{ user, token }` |
| POST | `/forgot-password` | `password.email` | `email` | `{ status }` |
| POST | `/reset-password` | `password.store` | стандартные поля Laravel reset | `{ ... }` |

Авторизован (`auth:sanctum`):

| Метод | Путь | Имя | Ответ |
|---|---|---|---|
| GET | `/user` | — | текущий пользователь |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | signed + throttle 6/мин |
| POST | `/email/verification-notification` | `verification.send` | повтор письма |
| POST | `/logout` | `logout` | `{ message: "Logged out" }`, токен удаляется |

Login с неверным паролем — ошибка валидации `LoginRequest`.

---

## API v1 (`routes/api_v1.php`)

Все маршруты: `auth:sanctum`.

### Проекты `v1/project`

`apiResource` → index, store, show, update, destroy.

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/project` | Только проекты текущего пользователя, `created desc`. Query: `per_page` (default 10), `page` |
| POST | `/v1/project` | `201` + `ProjectResource`. `user_id` и `created = now()` выставляет сервер. `sleep(1)` |
| GET | `/v1/project/{project}` | Policy `view` |
| PATCH/PUT | `/v1/project/{project}` | Policy `update`, `created` сбрасывается в `now()`, `sleep(1)` |
| DELETE | `/v1/project/{project}` | `{ message: "Проект удалён" }`, `sleep(1)` |

**Store / update body:**

```json
{
  "name": "Квартира на Ленина",
  "description": "косметический ремонт",
  "place_name": "Казань",
  "latitude": 55.79,
  "longitude": 49.12
}
```

Правила: `name` required, 3…155; `latitude` −90…90; `longitude` −180…180.

**ProjectResource:**

```json
{
  "id": 1,
  "user_id": 1,
  "name": "...",
  "description": "...",
  "created": "2026-01-15 12:00:00",
  "place_name": "...",
  "latitude": "55.79000000",
  "longitude": "49.12000000"
}
```

Поле `created` в ресурсе берётся из `created_at`, не из колонки `created`.

### Комнаты `v1/project/{project}/rooms`

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/project/{project}/rooms` | `updated_at desc`, `per_page` default 10, `sleep(1)`. Authorize проекта нет |
| POST | `/v1/project/{project}/rooms` | `project_id` обязателен в теле и должен существовать |
| GET | `/v1/project/{project}/rooms/{room}` | Policy `view` |
| PATCH | `/v1/project/{project}/rooms/{room}` | Policy `update` |
| DELETE | `/v1/project/{project}/rooms/{room}` | `{ message: "Комната удалёна" }` |

**Store body:**

```json
{
  "project_id": 1,
  "name": "Кухня",
  "description": "",
  "height": 2.7
}
```

`height` required, numeric, 2…5.

**RoomResource:** `id`, `project_id`, `project_name`, `name`, `description`, `height`, `updated_at`.

### Стены `v1/roomWalls/{room}`

Не `apiResource`.

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/roomWalls/{room}` | Policy `view` на Room. Сортировка `order`, затем `updated_at desc`. `per_page` default 7 |
| POST | `/v1/roomWalls/{room}` | Полная замена стен комнаты |

**Store body:**

```json
{
  "walls": [
    { "length": 400, "angle": 0, "is_real": 1 },
    { "length": 300, "angle": 90, "is_real": 1 },
    { "length": 400, "angle": 180, "is_real": 1 },
    { "length": 300, "angle": -90, "is_real": 1 }
  ]
}
```

Сервер проставляет `room_id`, `order` (1…n), timestamps и делает `delete` + `insert`.

Ответ:

```json
{
  "walls": [ /* вставленный массив */ ],
  "success": true,
  "message": "success store room walls"
}
```

**RoomWallResource:** `{ length, angle, is_real }` (числа).

Клиент **не сохраняет** незамкнутый контур (проверка на SPA). API замкнутость не валидирует.

### Работы комнаты `v1/room/{room}/roomJob`

`apiResource` внутри префикса `v1/room/{room}`. Реализованы index, store, destroy.

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/room/{room}/roomJob` | `viewAny` всегда true. Ответ `{ "data": [ ...модели ] }` **без** Resource (сырые поля, включая jsonb) |
| POST | `/v1/room/{room}/roomJob` | `201` `RoomJobResource`. `room_id` берётся из тела, не из URL |
| DELETE | `/v1/room/{room}/roomJob/{roomJob}` | `{ success, message }` |

**Store body:**

```json
{
  "room_id": 1,
  "title": "Натяжной потолок",
  "sum": 25000,
  "more_info": [
    { "name": "Потолок + установка", "amount": 16, "price": 600, "sum": 9600 }
  ]
}
```

`more_info` required array; у каждой строки required `name`, `amount`, `price`, `sum` (integer).

**RoomJobResource:** `id`, `room_id`, `title`, `sum`, `more_info`.

### Материалы комнаты `v1/room/{room}/roomMaterial`

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/room/{room}/roomMaterial` | `viewAny(RoomMaterial, $room)`. Пагинация 100 |
| POST | `/v1/room/{room}/roomMaterial` | Одна позиция. Policy `create` не вызывается |
| POST | `/v1/room/{room}/roomMaterials` | Пачка (`bulkStore`), имя маршрута `roomMaterials.bulk` |
| DELETE | `/v1/room/{room}/roomMaterial/{roomMaterial}` | `{ message: "Строительный материал комнаты удален!" }` |

**Одна позиция:**

```json
{
  "room_id": 1,
  "material_id": 4,
  "amount": 8,
  "sum": 2400,
  "note": "опционально"
}
```

**Пачка:**

```json
{
  "materials": [
    { "material_id": 4, "amount": 8, "sum": 2400 },
    { "material_id": 5, "amount": 12, "sum": 600 }
  ]
}
```

`amount` ≥ 1; `sum` nullable, 0…99999999.99, до 2 знаков.

**RoomMaterialResource:** `id`, `room_id`, `material_id`, `room_title`, `material_title`, `amount`, `sum` (int), `notice`.

### Справочник материалов `v1/material`

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/material` | Все материалы, `title asc`, `perPage = 55`. Без ролей |
| POST | `/v1/material` | `{ success: 1 }`, `201` |
| GET | `/v1/material/{material}` | Карточка + `images` |
| DELETE | `/v1/material/{material}` | `{ message: "Материал удалён" }` |

Update пустой.

**Store body (основное):** `title` (3…255), опционально `parent_id`, `description`, `product_url`, `price`, `product_code`, `is_free`, `characteristics` (array), `advantages`, `packaging_info`, `brand`, `producing_country`.

**MaterialResource:** id, title, description, product_code, price, `is_free: false` (захардкожено), characteristics, advantages, packaging_info, brand, producing_country, `images` (коллекция `MaterialImageResource`).

### Картинки материалов `v1/material_image`

| Метод | Путь | Примечание |
|---|---|---|
| GET | `/v1/material_image` | Пагинация, `orderBy name` |
| POST | `/v1/material_image` | multipart: `material_id` + файл в поле `name` |
| PATCH | `/v1/material_image/{material_image}/to_left` | Сдвиг sort влево |
| PATCH | `/v1/material_image/{material_image}/to_right` | Сдвиг sort вправо |
| DELETE | `/v1/material_image/{material_image}` | БД + файл на диске |

POST успех: `{ success: 1, images: [ ...все картинки материала ] }`, `201`.

Сдвиг без соседа: `400` с сообщением «уже на крайней … позиции».

**MaterialImageResource:** `id`, `parent_id` (у модели поля нет — будет null), `sort`, `name`, `url` вида `{APP_URL}:{APP_PORT}/storage/{name}`.

---

## API v2 (`routes/api_v2.php`)

Только `apiResource('v2/project', ...)`. Те же CRUD, но:

- store не пишет `created`;
- нет `sleep(1)`;
- `App\Http\Resources\V2\ProjectResource`.

Фронтенд v2 не использует. Тесты: `tests/Feature/Api/V2/ProjectControllerTest.php`.

---

## Примеры curl

Логин:

```bash
curl -s -X POST http://localhost:8008/api/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"user@example.com","password":"password"}'
```

Список проектов:

```bash
curl -s http://localhost:8008/api/v1/project \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json'
```
