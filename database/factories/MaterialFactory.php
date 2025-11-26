<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = [
            'Технониколь',
            'ВОЛМА',
            'Старатели',
            'Основит',
            'Петропласт',
            'ЛСР',
            'Бауцемент',
            'Руцем',
            'Гипсополимер',
            'Эталон'
        ];

        $countries = [
            'Россия',
            'Китай',
            'Германия',
            'Турция',
            'Польша',
            'Италия',
            'Беларусь'
        ];

        // Генерация advantages - случайное количество элементов (3-6) и случайные предложения
        $advantagesCount = fake()->numberBetween(3, 6);
        $advantages = [];
        for ($i = 0; $i < $advantagesCount; $i++) {
            $advantages[] = fake()->sentence(fake()->numberBetween(5, 12));
        }

        // Генерация packaging_info
        $packagingUnits = [
            'Упаковка {n} шт',
            'Штук',
            'Коробка {n} шт',
            'Палета {n} шт',
            'Блок {n} шт',
            'Набор {n} шт',
            'Комплект {n} шт',
            'Мешок {n} шт',
            'Рулон {n} шт',
            'Фасовка {n} шт'
        ];
        $unitTemplate = fake()->randomElement($packagingUnits);
        $unitString = str_replace('{n}', fake()->numberBetween(1, 1000), $unitTemplate);
        $packagingInfo = [
            'Единица товара: ' . $unitString,
            'Вес, кг: ' . fake()->randomFloat(2, 0.1, 50),
            'Длина, мм: ' . fake()->numberBetween(10, 1000),
            'Ширина, мм: ' . fake()->numberBetween(10, 1000),
            'Высота, мм: ' . fake()->numberBetween(10, 1000)
        ];

        // Случайно добавляем дополнительные параметры упаковки
        $additionalParams = [
            'Объем, л: ' . fake()->randomFloat(2, 0.1, 100),
            'Плотность, г/м²: ' . fake()->numberBetween(50, 500),
            'Материал упаковки: ' . fake()->randomElement(['Картон', 'Пленка', 'Пластик', 'Бумага', 'Фольга']),
            'Количество в коробе: ' . fake()->numberBetween(5, 100),
            'Срок годности: ' . fake()->numberBetween(12, 60) . ' месяцев'
        ];

        // Добавляем 1-2 дополнительных параметра
        $extraParamsCount = fake()->numberBetween(1, 2);
        $selectedExtraParams = fake()->randomElements($additionalParams, $extraParamsCount);
        foreach ($selectedExtraParams as $param) {
            $packagingInfo[] = $param;
        }

        // Генерация characteristics - 5-15 случайных характеристик
        $possibleCharacteristics = [
            // Основные характеристики
            'Количество рулонов' => fn() => fake()->numberBetween(1, 10) . ' шт',
            'Основной цвет' => fn() => fake()->randomElement(['бежевый', 'белый', 'серый', 'черный', 'коричневый', 'синий', 'зеленый', 'красный']),
            'Раппорт (шаг рисунка)' => fn() => fake()->randomElement(['64 см', '32 см', '50 см', '45 см', '70 см', 'нет']),
            'Длина' => fn() => fake()->numberBetween(5, 20) . ' м',
            'Ширина' => fn() => fake()->randomFloat(2, 0.5, 2) . ' м',

            // Материалы
            'Основа' => fn() => fake()->randomElement(['флизелин', 'бумага', 'стеклохолст', 'винил', 'текстиль']),
            'Материал верхнего слоя' => fn() => fake()->randomElement(['винил горячего тиснения', 'акрил', 'пробка', 'бамбук', 'текстиль']),
            'Покрытие' => fn() => fake()->randomElement(['матовое', 'глянцевое', 'сатиновое', 'полуматовое']),

            // Свойства
            'Стойкость к выцветанию' => fn() => fake()->randomElement(['хорошая', 'высокая', 'средняя', 'низкая']),
            'Степень влагостойкости' => fn() => fake()->randomElement(['влагостойкие', 'не влагостойкие', 'высокая влагостойкость']),
            'Моющиеся' => fn() => fake()->randomElement(['да', 'нет']),
            'Ударопрочность' => fn() => fake()->randomElement(['высокая', 'средняя', 'низкая']),
            'Термостойкость' => fn() => fake()->randomElement(['до 60°C', 'до 80°C', 'до 100°C', 'до 120°C']),

            // Монтаж
            'Нанесение клея' => fn() => fake()->randomElement(['на стену', 'на обои', 'на основание']),
            'Способ снятия' => fn() => fake()->randomElement(['снимаются без остатка', 'снимаются с остатком', 'не снимаются']),
            'Время высыхания' => fn() => fake()->numberBetween(1, 48) . ' часов',
            'Температура монтажа' => fn() => fake()->numberBetween(5, 30) . '°C',

            // Дизайн
            'Рисунок' => fn() => fake()->randomElement(['Узоры', 'Полоски', 'Цветы', 'Геометрия', 'Абстракция', 'Дерево', 'Камень', 'Орнамент']),
            'Фактура' => fn() => fake()->randomElement(['рельефная', 'гладкая', 'шероховатая', 'структурная', 'глубокая текстура']),
            'Стиль' => fn() => fake()->randomElement(['классический', 'современный', 'прованс', 'лофт', 'минимализм', 'скандинавский']),

            // Защита
            'Антивандальная защита' => fn() => fake()->randomElement(['да', 'нет']),
            'Огнестойкость' => fn() => fake()->randomElement(['класс КМ1', 'класс КМ2', 'класс КМ3', 'класс КМ4', 'не огнестойкие']),
            'УФ защита' => fn() => fake()->randomElement(['есть', 'нет']),

            // Дополнительно
            'Производитель' => fn() => fake()->company(),
            'Страна производства' => fn() => fake()->country(),
            'Срок службы' => fn() => fake()->numberBetween(5, 30) . ' лет',
            'Гарантия' => fn() => fake()->numberBetween(1, 10) . ' лет',
            'Экологичность' => fn() => fake()->randomElement(['эко-материал', 'без формальдегида', 'обычный']),
            'Артикул' => fn() => 'ART-' . fake()->numberBetween(1000, 9999),
            'Коллекция' => fn() => fake()->randomElement(['Классик', 'Модерн', 'Премиум', 'Эко', 'Винтаж']),
        ];
        // Выбираем случайное количество характеристик (5-15)
        $characteristicsCount = fake()->numberBetween(5, 15);
        $selectedKeys = fake()->randomElements(array_keys($possibleCharacteristics), $characteristicsCount);
        $characteristics = [];
        foreach ($selectedKeys as $key) {
            $characteristics[$key] = $possibleCharacteristics[$key]();
        }

        return [
            'parent_id' => null,
            'title' => ucfirst(implode(' ', $this->faker->words(rand(3, 11)))),
            'description' => fake()->text(255),
            'product_code' => fake()->numberBetween(10000000, 19999999), // целое число,
            'price' => round(fake()->randomFloat(2, 50, 10500)),
            'characteristics' => $characteristics,
            'advantages' => $advantages,
            'packaging_info' => $packagingInfo,
            'brand' => $brands[array_rand($brands)],
            'producing_country' => $countries[array_rand($countries)],
        ];
    }
}
