# Фронтенд

Каталог: `quasar_frontend/`. Quasar CLI (`@quasar/app-vite` 2) + Vite 6, Vue 3 `<script setup>`, Pinia, Vue Router history mode.

Точка входа API: `src/config.js` → Axios instance в `src/boot/axios.js` (`app.config.globalProperties.$api` и named export `api`).

## Маршруты

`src/router/routes.js`, layout `MainLayout.vue`. Guard в `src/router/index.js`.

| path | name | meta | Страница |
|---|---|---|---|
| `/` | Index | — | Заглушка с логотипом. Авторизованный сразу уходит на Dashboard |
| `/login` | Login | `requiresGuest` | Вход |
| `/register` | Register | `requiresGuest` | Регистрация |
| `/dashboard` | Dashboard | `requiresAuth` | Имя пользователя + logout |
| `/projects` | Projects | `requiresAuth` | Список / CRUD проектов |
| `/projects/:projectId/rooms` | ProjectRooms | `requiresAuth` | Комнаты проекта |
| `/projects/:projectId/rooms/:roomId/specifications` | RoomSpecifications | `requiresAuth` | Главный экран сметы |
| `/materials` | Materials | `requiresAuth` | Справочник |
| `/material/:material_id` | MaterialDetail | `requiresAuth`, `props: true` | Карточка + галерея |
| `/test-css` | TestCss | без auth | Песочница CSS |
| `/test-walls-builder` | TestWallsBuilder | без auth | Песочница стен |
| `/:catchAll(.*)*` | — | — | 404 |

Guard:

- `/` + есть токен → `/dashboard`;
- `requiresAuth` без токена → `/login`;
- `requiresGuest` с токеном → `/dashboard`.

На каждом переходе вызывается `authStore.initializeAuth()` (проставляет Axios header из localStorage).

Боковое меню `MainLayout` пока содержит дефолтные ссылки Quasar, не пункты приложения. Dashboard также не содержит ссылок на рабочие разделы, поэтому `/projects` и `/materials` открывают напрямую; переходы из проектов в комнаты и спецификацию сделаны кнопками и breadcrumbs.

## Pinia stores

`src/stores/`

### `auth`

State: `user`, `token` из localStorage.  
Getter: `isAuthenticated` = наличие токена.

Login сохраняет и токен, и пользователя. Register сохраняет токен и обновляет state, но сейчас не записывает пользователя в localStorage, поэтому после reload зарегистрированный пользователь теряется из `currentUser` при сохранённой авторизации.

| action | HTTP |
|---|---|
| `register` | `POST /register` |
| `login` | `POST /login` |
| `logout` | `POST /logout` |
| `initializeAuth` | header Bearer |

### `projects`

Список + пагинация (`currentPage`, `lastPage`, `perPage`).  
`loadProjects` / `addProject` / `editProject` / `deleteProject` → `/v1/project`. Новый проект вставляется в начало списка.

### `rooms`

Аналогично для `/v1/project/{id}/rooms`. `currentRoom` фильтрует по `currentRoomId`.

### `roomJobs`

`roomJobs[]`. Getter `roomJobsSum` — сумма `sum`.  
`loadRoomJobs`, `addRoomJob`, `deleteRoomJob` → `/v1/room/{id}/roomJob`.

### `roomMaterials`

`items[]`. Getter `roomMaterialsSum`.  
`loadItems`, `addItem` (одна), `addItems` (пачка `POST .../roomMaterials`), `deleteItem`.

### `materials`

Справочник. Флаг `loaded`. `loadItems` тянет `/v1/material` (до 55 записей, без пагинации на клиенте). `showItem`, `deleteItem`. Action `addItem` ошибочно бьёт в `roomJob` — мёртвый код относительно справочника.

## Страницы

### Auth

- `LoginPage` — email/password, редирект `/dashboard`.
- `RegisterPage` — name, email, password + confirmation.
- `DashboardPage` — приветствие `currentUser.name`.

### Projects / Rooms

Списки `q-list` с пагинацией, спиннер, empty state, ошибка загрузки.

Диалоги: создать / смотреть / редактировать / подтвердить удаление.

С проекта — переход в комнаты (`arrow_forward`). С комнаты — в спецификацию.

### RoomSpecifications (ядро продукта)

URL: `/projects/:projectId/rooms/:roomId/specifications`.

При монтировании:

1. `GET /v1/project/{pid}/rooms/{rid}` — имя проекта, комнаты, высота;
2. `materialsStore.loadItems()`;
3. `GET /v1/roomWalls/{rid}` — стены;
4. `roomJobs` и `roomMaterials` подгружаются дочерними компонентами.

Липкая шапка:

- характеристики: высота, периметр, площадь стен (`периметр × высота`), площадь потолка и пола (одна и та же формула Гаусса);
- общая стоимость = материалы + работы.

Блоки `q-expansion-item`:

1. **Характеристики комнаты** — вкладки Стены / Двери / Окна. Двери и окна — заготовки.
2. **Добавление работ и материалов** — вкладки Натяжной потолок, Гипсокартон и Шпатлёвка.
3. **Добавленные работы** — `RoomJobs.vue`.
4. **Добавленные материалы** — `RoomMaterials.vue`.

Редактор стен:

- точечное добавление (`length`, `angle`, `is_real`);
- удаление последней, сброс к загруженному эталону;
- textarea со строками `ДЛИНА УГОЛ НЕ_ПРОЕМ` (сейчас `disable`);
- SVG: pan, wheel-zoom, кнопки масштаба, подписи длин, опционально углы в вершинах;
- сохранение только если контур замкнут (`stats.is_closed`), иначе Notify.

Геометрия и формулы — [calculations.md](calculations.md).

### Materials

Таблица справочника, удаление, переход на карточку. Кнопка «Добавить материал» без обработчика.

`MaterialView` — таблица ключ/значение полей + `MaterialImage.vue` (загрузка, влево/вправо, удаление).

## Компоненты калькуляторов

```
components/
  JobTypesCalcs/
    StretchCeilingCalc.vue
    DrywallCalc.vue
  BuildingMaterialCalcs/
    StretchCeilingBuildingMaterials.vue
    DrywallBuildingMaterials/
      DrywallBuildingMaterials.vue          # обёртка
      DrywallBuildingMaterialsForCeil.vue   # нормы ГКЛ потолка
      DrywallBuildingMaterialsForWalls.vue  # нормы ГКЛ стен
      DrywallMaterialInputs.vue             # общие параметры материалов
      DrywallMaterialsTable.vue             # общая таблица рекомендаций
      useDrywallMaterials.js                # строки, стоимость и добавление
      drywallMaterials.js                   # общие чистые расчёты
  RoomJobs.vue
  RoomMaterials.vue
```

`StretchCeilingCalc` / `DrywallCalc` получают `roomId`, копию `walls` (и высоту для ГКЛ). Локальный `etalonWalls` можно крутить, не меняя сохранённый контур, пока не нажато «Добавить работу».

«Добавить работу» пишет `RoomJob` через store.

Рекомендуемые материалы потолка и стен ГКЛ используют общую таблицу с кнопкой «+» по строке и
массовым добавлением через `POST .../roomMaterials`. Формулы количества остаются раздельными в
компонентах потолка и стен.

`RoomMaterials` умеет ручной поиск по справочнику, выбор, количество, сумма, добавление одной позиции и таблицу уже добавленных с удалением.

## Состояние и согласованность

- После login токен и user лежат в localStorage и переживают reload; у register есть описанное выше исключение.
- Списки проектов/комнат остаются в Pinia в течение текущей SPA-сессии; спецификация комнаты грузит данные заново в `onMounted`.
- Итог сметы реактивный: геттеры сторов работ и материалов.
- После bulk-add материалы дописываются в `roomMaterials.items`.

## Тесты UI

Vitest + Vue Test Utils + `@pinia/testing`. Каталог `quasar_frontend/test/vitest/__tests__/`:

- `auth/LoginPage`, `RegisterPage`, `DashboardPage`
- `projects/ProjectsPage`
- `rooms/ProjectRoomsPage`

Спецификация комнаты и калькуляторы автотестами не покрыты.

Текущий прогон `npm run test:unit:ci` выполняет 37 тестов в 6 файлах; benchmark `sort.bench.ts` в этот прогон не входит.

Команды — в корневом [README](../README.md#тесты) и [development.md](development.md).
