<?php

namespace Database\Seeders;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoPointsSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $now = Carbon::now();

        // ─────────────────────────────────────────────
        //  KUDOS (sprinkled throughout 3 months)
        // ─────────────────────────────────────────────

        // Compute "days ago" values that land within the current week so the
        // leaderboard (default = weekly period) has data to render.
        $startOfWeek = $now->copy()->startOfWeek();
        $daysSinceWeekStart = (int) $startOfWeek->diffInDays($now);
        $thisWeek0 = max(0, $daysSinceWeekStart);          // today
        $thisWeek1 = max(0, $daysSinceWeekStart - 1);      // yesterday (clamped)

        $kudosDefs = [
            // Parents giving kudos to kids
            ['from' => $this->mike, 'to' => $this->emma, 'reason' => 'Отличная подготовка к SAT!', 'days_ago' => 20],
            ['from' => $this->sarah, 'to' => $this->jake, 'reason' => 'Очень здорово, что ты убрался, хотя тебя не просили', 'days_ago' => 27],
            ['from' => $this->mike, 'to' => $this->lily, 'reason' => 'Сегодня ты прекрасно играла на фортепиано', 'days_ago' => 15],
            ['from' => $this->sarah, 'to' => $this->emma, 'reason' => 'Спасибо, что помогла с ужином', 'days_ago' => 45],
            ['from' => $this->mike, 'to' => $this->jake, 'reason' => 'Отличная работа над научным проектом', 'days_ago' => 58],
            ['from' => $this->sarah, 'to' => $this->lily, 'reason' => 'Так горжусь твоей подготовкой к конкурсу по правописанию', 'days_ago' => 25],
            ['from' => $this->mike, 'to' => $this->emma, 'reason' => 'Кухня была безупречно чистой, молодец!', 'days_ago' => 31],
            ['from' => $this->sarah, 'to' => $this->jake, 'reason' => 'Отлично пропылесосил!', 'days_ago' => 10],
            ['from' => $this->mike, 'to' => $this->lily, 'reason' => 'Молодец, что сама проявила инициативу с растениями', 'days_ago' => 19],
            ['from' => $this->sarah, 'to' => $this->emma, 'reason' => 'Отличный черновик сочинения', 'days_ago' => 5],
            // Kids giving kudos to each other
            ['from' => $this->emma, 'to' => $this->jake, 'reason' => 'Спасибо, что не шумел, пока я занималась', 'days_ago' => 14],
            ['from' => $this->jake, 'to' => $this->lily, 'reason' => 'Ты отлично играла на фортепиано!', 'days_ago' => 2],
            ['from' => $this->lily, 'to' => $this->emma, 'reason' => 'Спасибо, что помогла мне с домашним заданием', 'days_ago' => 33],
            ['from' => $this->emma, 'to' => $this->lily, 'reason' => 'Очень нравится открытка, которую ты сделала для бабушки', 'days_ago' => 8],
            // Parents to parents
            ['from' => $this->mike, 'to' => $this->sarah, 'reason' => 'Ты потрясающе всё приготовил на неделю', 'days_ago' => 24],
            ['from' => $this->sarah, 'to' => $this->mike, 'reason' => 'Дорожка выглядит потрясающе!', 'days_ago' => 22],
            // ── This week (so the weekly leaderboard has live data) ──
            ['from' => $this->mike,  'to' => $this->emma, 'reason' => 'Отлично справляешься со сборником по химии AP',  'days_ago' => $thisWeek0],
            ['from' => $this->sarah, 'to' => $this->lily, 'reason' => 'Позанималась фортепиано без напоминаний',       'days_ago' => $thisWeek0],
            ['from' => $this->mike,  'to' => $this->jake, 'reason' => 'Подстриг газон, не дожидаясь просьбы',        'days_ago' => $thisWeek1],
            ['from' => $this->emma,  'to' => $this->lily, 'reason' => 'Почитала мне свою книгу — это было очень мило',     'days_ago' => $thisWeek1],
            ['from' => $this->sarah, 'to' => $this->mike, 'reason' => 'Спасибо, что сегодня подвозила детей',        'days_ago' => $thisWeek0],
        ];

        $createdKudos = [];
        foreach ($kudosDefs as $i => $k) {
            $createdAt = $now->copy()->subDays($k['days_ago'])->setHour(rand(17, 21));

            $createdKudos[$i] = PointTransaction::create([
                'family_id' => $this->familyId(),
                'user_id' => $k['to']->id,
                'type' => PointTransactionType::Kudos->value,
                'points' => 1,
                'description' => "Благодарность от {$k['from']->name}: {$k['reason']}",
                'awarded_by' => $k['from']->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // ─────────────────────────────────────────────
        //  STACKED KUDOS — family members "+1"ing each other's kudos so
        //  the demo dashboard shows the new stack badges in action.
        // ─────────────────────────────────────────────

        $stackDefs = [
            // Sarah piggybacks on Mike's "SAT prep" kudo to Emma (index 0)
            ['source' => 0, 'stacker' => $this->sarah, 'hours_after' => 2],
            // Mike piggybacks on Sarah's "cleaned without being asked" kudo to Jake (index 1)
            ['source' => 1, 'stacker' => $this->mike, 'hours_after' => 3],
            // Emma also piggybacks on Sarah's kudo to Jake
            ['source' => 1, 'stacker' => $this->emma, 'hours_after' => 5],
            // Mike piggybacks on this-week kudo to Lily (index = count - 4) so the feed shows a current stack
            ['source' => count($kudosDefs) - 4, 'stacker' => $this->mike, 'hours_after' => 1],
        ];

        foreach ($stackDefs as $s) {
            $source = $createdKudos[$s['source']] ?? null;
            if (! $source) {
                continue;
            }
            // Don't let a stacker accidentally +1 their own kudo or one they received
            if ($source->awarded_by === $s['stacker']->id || $source->user_id === $s['stacker']->id) {
                continue;
            }
            $stackedAt = $source->created_at->copy()->addHours($s['hours_after']);

            PointTransaction::create([
                'family_id' => $this->familyId(),
                'user_id' => $source->user_id,
                'type' => PointTransactionType::Kudos->value,
                'points' => 1,
                'description' => $source->description,
                'awarded_by' => $s['stacker']->id,
                'stacked_from_transaction_id' => $source->id,
                'created_at' => $stackedAt,
                'updated_at' => $stackedAt,
            ]);
        }

        // ─────────────────────────────────────────────
        //  DEDUCTIONS (a couple for realism)
        // ─────────────────────────────────────────────

        $deductionAt = $now->copy()->subDays(40)->setHour(19);
        PointTransaction::create([
            'family_id' => $this->familyId(),
            'user_id' => $this->jake->id,
            'type' => PointTransactionType::Deduction->value,
            'points' => -5,
            'description' => 'Снова оставил велосипед на подъездной дорожке',
            'awarded_by' => $this->mike->id,
            'created_at' => $deductionAt,
            'updated_at' => $deductionAt,
        ]);

        $deductionAt2 = $now->copy()->subDays(15)->setHour(20);
        PointTransaction::create([
            'family_id' => $this->familyId(),
            'user_id' => $this->emma->id,
            'type' => PointTransactionType::Deduction->value,
            'points' => -5,
            'description' => 'Забыла выгулять Biscuit',
            'awarded_by' => $this->sarah->id,
            'created_at' => $deductionAt2,
            'updated_at' => $deductionAt2,
        ]);

        // ─────────────────────────────────────────────
        //  WELCOME BONUS (adjustment for each kid)
        // ─────────────────────────────────────────────

        foreach ($this->kids() as $kid) {
            $bonusAt = $now->copy()->subDays(90)->setHour(10);
            PointTransaction::create([
                'family_id' => $this->familyId(),
                'user_id' => $kid->id,
                'type' => PointTransactionType::Adjustment->value,
                'points' => 25,
                'description' => 'Приветственный бонус в Kinhold!',
                'awarded_by' => $this->mike->id,
                'created_at' => $bonusAt,
                'updated_at' => $bonusAt,
            ]);
        }
    }
}
