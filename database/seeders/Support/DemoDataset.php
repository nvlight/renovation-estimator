<?php

namespace Database\Seeders\Support;

class DemoDataset
{
    public const DEMO_EMAIL = 'demo@example.com';

    public const EMPTY_EMAIL = 'empty@example.com';

    public const PASSWORD = 'password';

    /**
     * @return list<array{name: string, email: string}>
     */
    public static function users(): array
    {
        return [
            ['name' => 'Иван Демо', 'email' => self::DEMO_EMAIL],
            ['name' => 'Анна Соколова', 'email' => 'anna@example.com'],
            ['name' => 'Пётр Новиков', 'email' => 'petr@example.com'],
            ['name' => 'Ольга Морозова', 'email' => 'olga@example.com'],
            ['name' => 'Сергей Кузнецов', 'email' => 'sergey@example.com'],
            ['name' => 'Мария Безпроектов', 'email' => self::EMPTY_EMAIL],
        ];
    }

    /**
     * Проекты с комнатами. У демо 12 проектов (пагинация), главный — первый.
     *
     * @return list<array<string, mixed>>
     */
    public static function projects(): array
    {
        return [
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Квартира на Ленина, 15',
                'description' => 'Трёхкомнатная квартира, капитальный ремонт после покупки.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.934280,
                'longitude' => 30.335099,
                'days_ago' => 20,
                'rooms' => self::mainApartmentRooms(),
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Дом в Пушкино',
                'description' => 'Первый этаж частного дома, выравнивание и потолки.',
                'place_name' => 'Пушкин',
                'latitude' => 59.722462,
                'longitude' => 30.416965,
                'days_ago' => 18,
                'rooms' => [
                    self::room('Гостиная', 'rectangle', [480, 420], 2.8, ['stretch', 'drywall_ceil'], [3, 4, 5, 11, 15]),
                    self::room('Кухня', 'opening', [360, 320, 90, 70], 2.7, ['stretch'], [29, 30, 31]),
                    self::room('Санузел', 'rectangle', [200, 180], 2.6, ['drywall_walls'], [2, 9, 10]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Офис на Невском',
                'description' => 'Open space 40 м², перегородки из ГКЛ.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.935000,
                'longitude' => 30.325000,
                'days_ago' => 16,
                'rooms' => [
                    self::room('Опенспейс', 'lShape', [800, 600, 250, 200], 3.2, ['drywall_ceil', 'drywall_walls'], [3, 4, 5, 7, 8, 14]),
                    self::room('Переговорная', 'rectangle', [400, 350], 3.2, ['stretch'], [29, 28]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Студия после застройщика',
                'description' => 'Предчистовая, нужна геометрия и смета потолка.',
                'place_name' => 'Мурино',
                'latitude' => 60.044800,
                'longitude' => 30.448700,
                'days_ago' => 14,
                'rooms' => [
                    self::room('Студия', 'opening', [520, 380, 90, 100], 2.7, ['stretch', 'drywall_walls'], [1, 6, 11, 29]),
                    self::room('Санузел', 'rectangle', [180, 160], 2.5, [], [2, 23]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Хрущёвка на Васильевском',
                'description' => 'Косметика двух комнат, низкие потолки.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.942000,
                'longitude' => 30.270000,
                'days_ago' => 12,
                'rooms' => [
                    self::room('Комната', 'rectangle', [310, 280], 2.5, ['stretch'], [29, 30]),
                    self::room('Кухня', 'opening', [240, 210, 80, 50], 2.5, ['drywall_ceil'], [3, 4, 5]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Дача в Зеленогорске',
                'description' => 'Черновик: стены ещё не замерены.',
                'place_name' => 'Зеленогорск',
                'latitude' => 60.200000,
                'longitude' => 29.700000,
                'days_ago' => 11,
                'rooms' => [
                    self::room('Веранда', 'empty', [], 2.6, [], []),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Таунхаус в Кудрово',
                'description' => 'Второй этаж, спальни и кабинет.',
                'place_name' => 'Кудрово',
                'latitude' => 59.908000,
                'longitude' => 30.512000,
                'days_ago' => 9,
                'rooms' => [
                    self::room('Спальня', 'rectangle', [420, 360], 2.8, ['stretch'], [29, 31]),
                    self::room('Кабинет', 'pentagon', [210], 2.8, ['drywall_ceil'], [3, 4, 5, 14, 15]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Коммерческое помещение на Садовой',
                'description' => 'Коридор под офисные кабинеты.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.928000,
                'longitude' => 30.318000,
                'days_ago' => 8,
                'rooms' => [
                    self::room('Коридор', 'corridor', [900, 140], 3.0, ['drywall_walls'], [9, 10, 17, 26]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Квартира родителей, Казань',
                'description' => 'Точечный ремонт гостиной и коридора.',
                'place_name' => 'Казань',
                'latitude' => 55.796127,
                'longitude' => 49.106414,
                'days_ago' => 7,
                'rooms' => [
                    self::room('Гостиная', 'lShape', [500, 380, 160, 140], 2.7, ['stretch', 'drywall_ceil'], [3, 4, 5, 6, 14]),
                    self::room('Коридор', 'corridor', [600, 110], 2.6, [], [21, 24]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Новостройка в Мурино',
                'description' => 'Свободная планировка, контуры черновые.',
                'place_name' => 'Мурино',
                'latitude' => 60.051000,
                'longitude' => 30.438000,
                'days_ago' => 5,
                'rooms' => [
                    self::room('Кухня-гостиная', 'empty', [], 2.7, [], []),
                    self::room('Спальня', 'rectangle', [340, 300], 2.7, ['stretch'], [29]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Мансарда на Петроградке',
                'description' => 'Скошенный контур упрощён пятиугольником.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.962000,
                'longitude' => 30.310000,
                'days_ago' => 3,
                'rooms' => [
                    self::room('Мансарда', 'pentagon', [280], 2.4, ['drywall_ceil', 'drywall_walls'], [1, 3, 4, 5, 11]),
                ],
            ],
            [
                'owner_email' => self::DEMO_EMAIL,
                'name' => 'Ремонт в съёмной на Гражданском',
                'description' => 'Без адреса на карте — только название.',
                'place_name' => null,
                'latitude' => null,
                'longitude' => null,
                'days_ago' => 1,
                'rooms' => [
                    self::room('Комната', 'opening', [360, 300, 90, 60], 2.55, ['stretch'], [28, 29, 31]),
                ],
            ],

            [
                'owner_email' => 'anna@example.com',
                'name' => 'Квартира на Академической',
                'description' => 'Косметический ремонт перед сдачей.',
                'place_name' => 'Москва',
                'latitude' => 55.808000,
                'longitude' => 37.608000,
                'days_ago' => 10,
                'rooms' => [
                    self::room('Гостиная', 'rectangle', [400, 330], 2.7, ['stretch'], [29, 30]),
                    self::room('Спальня', 'opening', [350, 300, 90, 70], 2.7, ['drywall_ceil'], [3, 4, 5]),
                ],
            ],
            [
                'owner_email' => 'anna@example.com',
                'name' => 'Офис на Тверской',
                'description' => 'Кабинет руководителя.',
                'place_name' => 'Москва',
                'latitude' => 55.764000,
                'longitude' => 37.605000,
                'days_ago' => 6,
                'rooms' => [
                    self::room('Кабинет', 'rectangle', [450, 380], 3.1, ['stretch', 'drywall_walls'], [9, 10, 29]),
                    self::room('Приёмная', 'corridor', [500, 160], 3.1, [], [26, 28]),
                ],
            ],
            [
                'owner_email' => 'anna@example.com',
                'name' => 'Кладовая на даче',
                'description' => null,
                'place_name' => 'Истра',
                'latitude' => 55.915000,
                'longitude' => 36.860000,
                'days_ago' => 2,
                'rooms' => [
                    self::room('Кладовая', 'empty', [], 2.4, [], []),
                ],
            ],

            [
                'owner_email' => 'petr@example.com',
                'name' => 'Дом в Всеволожске',
                'description' => 'Гостиная, кухня и санузел первого этажа.',
                'place_name' => 'Всеволожск',
                'latitude' => 60.021000,
                'longitude' => 30.645000,
                'days_ago' => 15,
                'rooms' => [
                    self::room('Гостиная', 'lShape', [600, 450, 200, 180], 2.9, ['stretch', 'drywall_ceil'], [3, 4, 5, 6, 11, 14, 15]),
                    self::room('Кухня', 'rectangle', [380, 300], 2.8, ['stretch'], [29, 31]),
                    self::room('Санузел', 'opening', [220, 180, 80, 40], 2.6, ['drywall_walls'], [2, 21, 24]),
                ],
            ],

            [
                'owner_email' => 'olga@example.com',
                'name' => 'Двушка в Купчино',
                'description' => 'Потолки и стены в жилых.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.850000,
                'longitude' => 30.370000,
                'days_ago' => 13,
                'rooms' => [
                    self::room('Комната 1', 'rectangle', [380, 320], 2.6, ['stretch'], [29]),
                    self::room('Комната 2', 'rectangle', [340, 300], 2.6, ['drywall_ceil'], [3, 4, 5]),
                ],
            ],
            [
                'owner_email' => 'olga@example.com',
                'name' => 'Ванная на ремонт',
                'description' => 'Влагостойкий ГКЛ.',
                'place_name' => null,
                'latitude' => null,
                'longitude' => null,
                'days_ago' => 8,
                'rooms' => [
                    self::room('Ванная', 'rectangle', [200, 170], 2.5, ['drywall_walls'], [2, 9, 10, 23]),
                ],
            ],
            [
                'owner_email' => 'olga@example.com',
                'name' => 'Коридор ЖК',
                'description' => 'Длинный коридор новостройки.',
                'place_name' => 'Парголово',
                'latitude' => 60.084000,
                'longitude' => 30.261000,
                'days_ago' => 4,
                'rooms' => [
                    self::room('Коридор', 'corridor', [850, 130], 2.7, ['drywall_walls'], [1, 9, 10]),
                ],
            ],
            [
                'owner_email' => 'olga@example.com',
                'name' => 'Черновик без стен',
                'description' => 'Замеры не сняты.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 59.940000,
                'longitude' => 30.320000,
                'days_ago' => 1,
                'rooms' => [
                    self::room('Комната', 'empty', [], 2.7, [], []),
                ],
            ],

            [
                'owner_email' => 'sergey@example.com',
                'name' => 'Квартира на Энгельса',
                'description' => 'Гостиная Г-образная и спальня.',
                'place_name' => 'Санкт-Петербург',
                'latitude' => 60.010000,
                'longitude' => 30.320000,
                'days_ago' => 9,
                'rooms' => [
                    self::room('Гостиная', 'lShape', [480, 400, 170, 150], 2.7, ['stretch', 'drywall_ceil'], [3, 4, 5, 15]),
                    self::room('Спальня', 'opening', [360, 320, 90, 80], 2.7, ['stretch'], [29, 28]),
                ],
            ],
            [
                'owner_email' => 'sergey@example.com',
                'name' => 'Кабинет на работе',
                'description' => null,
                'place_name' => 'Москва',
                'latitude' => 55.750000,
                'longitude' => 37.620000,
                'days_ago' => 2,
                'rooms' => [
                    self::room('Кабинет', 'pentagon', [240], 3.0, ['drywall_ceil'], [3, 4, 5, 14]),
                ],
            ],
        ];
    }

    /**
     * 12 комнат главного проекта — пагинация и все формы стен.
     *
     * @return list<array<string, mixed>>
     */
    private static function mainApartmentRooms(): array
    {
        return [
            self::room('Прихожая', 'opening', [180, 250, 90, 70], 2.7, [], [17, 28]),
            self::room('Кухня', 'rectangle', [340, 280], 2.7, ['stretch'], [29, 30, 31, 28]),
            self::room('Гостиная', 'lShape', [520, 410, 180, 140], 2.7, ['stretch', 'drywall_ceil'], [3, 4, 5, 6, 11, 14, 15]),
            self::room('Спальня', 'rectangle', [400, 350], 2.7, ['drywall_ceil', 'drywall_walls'], [1, 3, 4, 5, 6, 9, 10, 14]),
            self::room('Детская', 'rectangle', [320, 300], 2.6, ['stretch'], [29, 31]),
            self::room('Ванная', 'rectangle', [210, 170], 2.6, ['drywall_walls'], [2, 9, 10, 23, 24]),
            self::room('Туалет', 'empty', [], 2.6, [], []),
            self::room('Кабинет', 'pentagon', [220], 2.7, ['drywall_ceil'], [3, 4, 5, 14, 15]),
            self::room('Балкон', 'corridor', [380, 110], 2.5, [], []),
            self::room('Коридор', 'corridor', [720, 120], 2.7, ['drywall_walls'], [1, 9, 10, 26]),
            self::room('Гардероб', 'empty', [], 2.7, [], []),
            self::room('Кладовая', 'empty', [], 2.4, [], [21]),
        ];
    }

    /**
     * @param  list<string>  $jobs
     * @param  list<int>  $materialIds
     * @return array<string, mixed>
     */
    private static function room(
        string $name,
        string $shape,
        array $shapeArgs,
        float $height,
        array $jobs,
        array $materialIds,
        ?string $description = null,
    ): array {
        return [
            'name' => $name,
            'description' => $description,
            'height' => $height,
            'shape' => $shape,
            'shape_args' => $shapeArgs,
            'jobs' => $jobs,
            'material_ids' => $materialIds,
        ];
    }
}
