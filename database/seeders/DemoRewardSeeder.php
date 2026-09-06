<?php

namespace Database\Seeders;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\RewardPurchase;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoRewardSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $now = Carbon::now();

        // ─────────────────────────────────────────────
        //  REWARDS
        // ─────────────────────────────────────────────

        $sweets = Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Сладости',
            'description' => 'Выбери лакомство из запасов сладостей',
            'point_cost' => 10,
            'icon' => 'cookie',
            'sort_order' => 0,
        ]);

        $screenTime = Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Дополнительное время за экраном (30 минут)',
            'description' => 'Дополнительные 30 минут за экраном',
            'point_cost' => 20,
            'icon' => 'tv',
            'sort_order' => 1,
            'visibility' => 'child_only',
        ]);

        $pickDinner = Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Выбор ужина',
            'description' => 'Выбери, что семья будет есть на ужин',
            'point_cost' => 30,
            'icon' => 'pizza',
            'sort_order' => 2,
        ]);

        $moviePick = Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Выбор фильма на киновечер',
            'description' => 'Выбери фильм для семейного киновечера',
            'point_cost' => 40,
            'icon' => 'film',
            'sort_order' => 3,
            'quantity' => 2,
        ]);

        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Позже лечь спать',
            'description' => 'Можно не ложиться спать на час дольше обычного',
            'point_cost' => 75,
            'icon' => 'moon',
            'sort_order' => 4,
            'min_age' => 10,
            'visibility' => 'child_only',
        ]);

        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Ночёвка с другом',
            'description' => 'Пригласи друга на ночёвку в эти выходные',
            'point_cost' => 100,
            'icon' => 'house',
            'sort_order' => 5,
            'quantity' => 1,
            'expires_at' => now()->addDays(30),
        ]);

        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'День без домашних дел',
            'description' => 'Освобождение от домашних дел на один день',
            'point_cost' => 50,
            'icon' => 'confetti',
            'sort_order' => 6,
            'visibility' => 'specific',
            'visible_to' => [$this->emma->id, $this->jake->id],
        ]);

        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Выбрать новую книгу',
            'description' => 'Выбери любую книгу — мы закажем её',
            'point_cost' => 60,
            'icon' => 'book',
            'sort_order' => 7,
            'visibility' => 'child_only',
        ]);

        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Бюджет на художественные принадлежности ($15)',
            'description' => 'Выбери художественные принадлежности на $15',
            'point_cost' => 80,
            'icon' => 'palette',
            'sort_order' => 8,
            'visibility' => 'specific',
            'visible_to' => [$this->lily->id],
        ]);

        // Auction rewards
        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Выбор поездки на выходные',
            'description' => 'Выбери, куда семья поедет в эти выходные! Победит тот, кто предложит больше всех.',
            'point_cost' => 0,
            'icon' => 'car',
            'sort_order' => 9,
            'reward_type' => 'auction',
            'quantity' => 1,
            'min_bid' => 20,
            'bid_end_at' => now()->addDays(5),
        ]);

        Reward::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Карманные деньги ($5)',
            'description' => 'Сделай ставку на дополнительные карманные деньги — $5. Победителя объявит родитель!',
            'point_cost' => 0,
            'icon' => 'dollar-sign',
            'sort_order' => 10,
            'reward_type' => 'auction',
            'quantity' => 1,
            'min_bid' => 10,
            'visibility' => 'child_only',
        ]);

        // ─────────────────────────────────────────────
        //  REWARD PURCHASES (with matching point transactions)
        // ─────────────────────────────────────────────

        $purchases = [
            ['user' => $this->emma, 'reward' => $sweets, 'days_ago' => 70],
            ['user' => $this->jake, 'reward' => $sweets, 'days_ago' => 65],
            ['user' => $this->emma, 'reward' => $screenTime, 'days_ago' => 50],
            ['user' => $this->lily, 'reward' => $sweets, 'days_ago' => 45],
            ['user' => $this->jake, 'reward' => $screenTime, 'days_ago' => 35],
            ['user' => $this->emma, 'reward' => $pickDinner, 'days_ago' => 25],
            ['user' => $this->lily, 'reward' => $sweets, 'days_ago' => 18],
            ['user' => $this->jake, 'reward' => $sweets, 'days_ago' => 12],
            ['user' => $this->emma, 'reward' => $moviePick, 'days_ago' => 7],
        ];

        foreach ($purchases as $p) {
            $purchasedAt = $now->copy()->subDays($p['days_ago'])->setHour(rand(15, 19));

            RewardPurchase::create([
                'family_id' => $this->familyId(),
                'reward_id' => $p['reward']->id,
                'user_id' => $p['user']->id,
                'points_spent' => $p['reward']->point_cost,
                'purchased_at' => $purchasedAt,
                'created_at' => $purchasedAt,
                'updated_at' => $purchasedAt,
            ]);

            PointTransaction::create([
                'family_id' => $this->familyId(),
                'user_id' => $p['user']->id,
                'type' => PointTransactionType::Redemption->value,
                'points' => -$p['reward']->point_cost,
                'description' => "Куплено: {$p['reward']->title}",
                'source_type' => Reward::class,
                'source_id' => $p['reward']->id,
                'created_at' => $purchasedAt,
                'updated_at' => $purchasedAt,
            ]);
        }
    }
}
