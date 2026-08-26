# Предметная модель

## Диаграмма связей

```
User 1 ──────── * Project
                     │
                     │ 1
                     ▼
                   Room  * ──────── * Material     (через RoomMaterial)
                     │                    │
        ┌────────────┼────────────┐       │ 1
        │            │            │       ▼
        ▼            ▼            ▼   MaterialImage
    RoomWall     RoomJob    RoomMaterial
```

Каскадное удаление:

- удаление пользователя → проекты;
- удаление проекта → комнаты;
- удаление комнаты → стены, работы, `room_materials`;
- удаление материала → картинки и `room_materials`.

## User

Таблица `users` (стандарт Laravel).

| Поле | Смысл |
|---|---|
| `name`, `email`, `password` | Учётная запись. Пароль хешируется (`hashed` cast) |
| `email_verified_at` | Верификация почты (API есть, UI нет) |

Связи: `hasMany(Project)`.

Токены Sanctum — таблица `personal_access_tokens`.

## Project

Смета привязана к объекту ремонта.

| Поле | Тип | Смысл |
|---|---|---|
| `user_id` | FK users | Владелец |
| `name` | string(155) | Название |
| `description` | string, nullable | Описание |
| `created` | datetime | Дата, выставляется в `now()` при create/update v1 |
| `place_name` | string, nullable | Населённый пункт / адрес |
| `latitude` | decimal(10,8) | Широта −90…90 |
| `longitude` | decimal(11,8) | Долгота −180…180 |

Пагинация: `Project::PER_PAGE = 10`. Список v1 сортируется по `created desc`.

Связи: `belongsTo(User)`, `hasMany(Room)`.

## Room

Помещение внутри проекта.

| Поле | Тип | Смысл |
|---|---|---|
| `project_id` | FK projects | Родитель |
| `name` | string | Название |
| `description` | string, nullable | Описание |
| `height` | decimal, default 2.7 | Высота в **метрах**. Валидация store: 2…5 |

Пагинация: `Room::PER_PAGE = 10`. Список — `updated_at desc`.

Связи: `belongsTo(Project)`, `hasMany(RoomJob)`, `hasMany(RoomWall)` как `room_walls`, `hasMany(RoomMaterial)` как `room_materials`.

## RoomWall

Один сегмент контура комнаты. Контур — упорядоченная ломаная.

| Поле | Тип | Смысл |
|---|---|---|
| `room_id` | FK rooms | Комната |
| `length` | decimal(7,2) | Длина в **сантиметрах** |
| `angle` | decimal(5,2) | Угол направления сегмента, градусы от оси X, против часовой |
| `is_real` | boolean, default true | `1` — стена, `0` — проём (дверь/окно), не идёт в площадь стен ГКЛ |
| `order` | unsigned int | Порядок обхода; при сохранении пачки перенумеровывается с 1 |

Валидация пачки: `angle` −180…180, `is_real` 0 или 1, `length` integer.

Сохранение стен — **полная замена**: все старые стены комнаты удаляются, вставляется новый набор (`RoomWallController@store`).

Ресурс API отдаёт только `length`, `angle`, `is_real` (без `id`/`order`).

Пагинация index: `PER_PAGE = 7`.

## RoomJob

Позиция работы в смете комнаты. Сумма и детализация считаются на клиенте.

| Поле | Тип | Смысл |
|---|---|---|
| `room_id` | FK rooms | Комната |
| `title` | string(111) | Например «Натяжной потолок», «Гипсокартон, потолок» |
| `sum` | bigInteger | Итог работы, ₽ |
| `more_info` | jsonb | Массив строк `{name, amount, price, sum}` |

Cast: `more_info` → array.

Update-метода нет: работа создаётся или удаляется целиком.

## Material

Справочник товаров (общий, не per-user).

| Поле | Тип | Смысл |
|---|---|---|
| `parent_id` | unsigned bigint, nullable | Заготовка иерархии, почти не используется |
| `title` | string | Короткое имя |
| `description` | string(555) | Длинное описание |
| `product_code` | unsigned int | Код |
| `product_url` | string | Ссылка на товар (есть в миграции/request, в Resource не отдаётся) |
| `price` | decimal(12,2) | Цена |
| `is_free` | boolean | Бесплатный. Resource сейчас всегда отдаёт `false` |
| `characteristics` | jsonb | Объект ключ→значение |
| `advantages` | json | Массив строк |
| `packaging_info` | json | Массив строк (единица, вес, габариты…) |
| `brand` | string(111) | Бренд |
| `producing_country` | string(111) | Страна |

Связь: `hasMany(MaterialImage)` с `orderBy('sort')`.

Калькуляторы ГКЛ/крепежа завязаны на **конкретные id** в справочнике (см. [calculations.md](calculations.md)). `MaterialSeeder` кладёт 36 товаров в порядке id 1…36: профиль 3, направляющий 4, подвес 5, саморезы 6/11/14, дюбели 15/16, лента 28.

## MaterialImage

| Поле | Тип | Смысл |
|---|---|---|
| `material_id` | FK materials | Материал |
| `name` | string | Относительный путь на диске `public` |
| `sort` | unsigned int | Порядок галереи. При create, если 0/пусто, ставится `max(sort)+1` |

`toLeft` / `toRight` меняют `sort` с соседом (`lockForUpdate`).

## RoomMaterial

Позиция материала в смете комнаты.

| Поле | Тип | Смысл |
|---|---|---|
| `room_id` | FK rooms | Комната |
| `material_id` | FK materials | Товар из справочника |
| `amount` | unsigned int | Количество (упаковок / штук) |
| `sum` | decimal(12,2) | Стоимость позиции, ₽ (считает клиент) |
| `notice` | string, nullable | Заметка. Request валидирует поле `note`, в таблицу пишется `notice` — при bulk/store `note` сейчас не мапится |

Пагинация index: `PER_PAGE = 100`.

Resource добавляет `room_title` и `material_title` (подгружает связи без eager load).

Update не реализован: позиция создаётся (одна или пачка) или удаляется.

## Итог сметы комнаты

Отдельной таблицы «смета» нет. На странице спецификации:

```
totalSum = Σ room_materials.sum + Σ room_jobs.sum
```

Считается во фронтовых Pinia-сторах.
