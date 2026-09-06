<?php

namespace Database\Seeders;

use App\Enums\ShoppingItemSource;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoShoppingSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $family = $this->family();
        $sarah = $this->sarah;
        $mike = $this->mike;

        $now = Carbon::now();

        // ─────────────────────────────────────────────
        //  ACTIVE WEEKLY GROCERIES LIST
        // ─────────────────────────────────────────────

        $weekly = ShoppingList::create([
            'family_id' => $family->id,
            'created_by' => $sarah->id,
            'name' => 'Еженедельные продукты',
            'store_name' => 'Costco',
            'is_active' => true,
        ]);

        $weeklyItems = [
            // Produce
            ['name' => 'Бананы',             'quantity' => '6',          'category' => 'produce', 'is_recurring' => true],
            ['name' => 'Шпинат',             'quantity' => '1 пакет',    'category' => 'produce'],
            ['name' => 'Клубника',           'quantity' => '1 фунт',     'category' => 'produce'],
            ['name' => 'Болгарский перец',   'quantity' => '3',          'category' => 'produce'],
            ['name' => 'Соцветия брокколи',  'quantity' => '2 стакана',  'category' => 'produce'],
            ['name' => 'Лимоны',             'quantity' => '4',          'category' => 'produce'],
            // Dairy
            ['name' => 'Цельное молоко',    'quantity' => '1 галлон',   'category' => 'dairy', 'is_recurring' => true],
            ['name' => 'Греческий йогурт',  'quantity' => '32 унции',   'category' => 'dairy'],
            ['name' => 'Острый чеддер',     'quantity' => '1 брусок',   'category' => 'dairy'],
            ['name' => 'Сливочное масло',   'quantity' => '1 фунт',     'category' => 'dairy', 'is_recurring' => true],
            // Meat / Seafood
            ['name' => 'Говяжий фарш',      'quantity' => '1 фунт',     'category' => 'meat'],
            ['name' => 'Куриная грудка',    'quantity' => '1.5 фунта',  'category' => 'meat'],
            ['name' => 'Филе лосося',       'quantity' => '4 (по 6 унций)', 'category' => 'meat'],
            // Pantry
            ['name' => 'Спагетти',          'quantity' => '1 фунт',     'category' => 'pantry'],
            ['name' => 'Протёртые томаты',  'quantity' => '28 унций',   'category' => 'pantry'],
            ['name' => 'Оливковое масло',   'quantity' => '1 бутылка',  'category' => 'pantry', 'is_recurring' => true],
            ['name' => 'Соевый соус',       'quantity' => '1 бутылка',  'category' => 'pantry'],
            // Bakery
            ['name' => 'Хлеб',              'quantity' => '1 буханка',  'category' => 'bakery', 'is_recurring' => true],
            ['name' => 'Тортильи',          'quantity' => '1 упаковка', 'category' => 'bakery'],
            // Snacks (already grabbed)
            ['name' => 'Мюсли-батончики',  'quantity' => '1 коробка',  'category' => 'snacks',  'is_checked' => true,
                'checked_by' => $mike->id, 'checked_at' => $now->copy()->subHours(2)],
            ['name' => 'Крекеры Goldfish',  'quantity' => '1 пачка',    'category' => 'snacks',  'is_checked' => true,
                'checked_by' => $mike->id, 'checked_at' => $now->copy()->subHours(2)],
        ];

        foreach ($weeklyItems as $idx => $def) {
            $isRecurring = $def['is_recurring'] ?? false;
            ShoppingItem::create([
                'shopping_list_id' => $weekly->id,
                'family_id' => $family->id,
                'added_by' => $sarah->id,
                'name' => $def['name'],
                'quantity' => $def['quantity'] ?? null,
                'category' => $def['category'] ?? null,
                'is_checked' => $def['is_checked'] ?? false,
                'checked_by' => $def['checked_by'] ?? null,
                'checked_at' => $def['checked_at'] ?? null,
                'source' => $isRecurring ? ShoppingItemSource::Staple : ShoppingItemSource::Manual,
                'is_recurring' => $isRecurring,
                'default_quantity' => $isRecurring ? ($def['quantity'] ?? null) : null,
                'sort_order' => $idx,
            ]);
        }

        // ─────────────────────────────────────────────
        //  SECONDARY LIST: TARGET RUN
        // ─────────────────────────────────────────────

        $target = ShoppingList::create([
            'family_id' => $family->id,
            'created_by' => $mike->id,
            'name' => 'Поездка в Target',
            'store_name' => 'Target',
            'is_active' => false,
        ]);

        $targetItems = [
            ['name' => 'Бумажные полотенца',   'quantity' => '6 рулонов', 'category' => 'household', 'is_recurring' => true],
            ['name' => 'Средство для мытья посуды', 'quantity' => '1',     'category' => 'household'],
            ['name' => 'Мусорные пакеты',       'quantity' => '1 коробка', 'category' => 'household'],
            ['name' => 'Зубная паста',          'quantity' => '2 тюбика',  'category' => 'personal-care'],
            ['name' => 'Шампунь',               'quantity' => '1',         'category' => 'personal-care'],
            ['name' => 'Школьный клей Лили',    'quantity' => '2 бутылочки', 'category' => 'school'],
            ['name' => 'Открытка на день рождения', 'quantity' => '1',     'category' => 'misc'],
        ];

        foreach ($targetItems as $idx => $def) {
            $isRecurring = $def['is_recurring'] ?? false;
            ShoppingItem::create([
                'shopping_list_id' => $target->id,
                'family_id' => $family->id,
                'added_by' => $mike->id,
                'name' => $def['name'],
                'quantity' => $def['quantity'] ?? null,
                'category' => $def['category'] ?? null,
                'is_checked' => false,
                'source' => $isRecurring ? ShoppingItemSource::Staple : ShoppingItemSource::Manual,
                'is_recurring' => $isRecurring,
                'default_quantity' => $isRecurring ? ($def['quantity'] ?? null) : null,
                'sort_order' => $idx,
            ]);
        }
    }
}
