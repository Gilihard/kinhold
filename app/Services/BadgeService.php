<?php

namespace App\Services;

use App\Enums\BadgeTriggerType;
use App\Enums\PointTransactionType;
use App\Models\Badge;
use App\Models\PointTransaction;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BadgeService
{
    /**
     * The single source of truth for default badge definitions.
     */
    public static function getDefaultBadgeDefinitions(): array
    {
        return [
            // --- Tasks Completed progression ---
            ['name' => 'Первые шаги', 'description' => 'Выполните первое задание', 'icon' => 'rocket', 'color' => '#059669', 'trigger_type' => BadgeTriggerType::TasksCompleted, 'trigger_threshold' => 1, 'is_hidden' => false, 'sort_order' => 0],
            ['name' => 'Новичок', 'description' => 'Выполните 10 задач', 'icon' => 'target', 'color' => '#0284c7', 'trigger_type' => BadgeTriggerType::TasksCompleted, 'trigger_threshold' => 10, 'is_hidden' => false, 'sort_order' => 1],
            ['name' => 'Трудяга', 'description' => 'Выполните 50 задач', 'icon' => 'lightning', 'color' => '#7d57a8', 'trigger_type' => BadgeTriggerType::TasksCompleted, 'trigger_threshold' => 50, 'is_hidden' => false, 'sort_order' => 2],
            ['name' => 'Легенда задач', 'description' => 'Выполните 100 задач', 'icon' => 'crown', 'color' => '#d97706', 'trigger_type' => BadgeTriggerType::TasksCompleted, 'trigger_threshold' => 100, 'is_hidden' => true, 'sort_order' => 3],
            ['name' => 'Титан задач', 'description' => 'Выполните 250 задач', 'icon' => 'fist', 'color' => '#b91c1c', 'trigger_type' => BadgeTriggerType::TasksCompleted, 'trigger_threshold' => 250, 'is_hidden' => true, 'sort_order' => 4],

            // --- Task Streak progression ---
            ['name' => 'В ударе', 'description' => 'Выполняйте задачи 7 дней подряд', 'icon' => 'flame', 'color' => '#dc2626', 'trigger_type' => BadgeTriggerType::TaskStreak, 'trigger_threshold' => 7, 'is_hidden' => false, 'sort_order' => 5],
            ['name' => 'Неудержимый', 'description' => 'Выполняйте задачи 30 дней подряд', 'icon' => 'fire-ring', 'color' => '#e11d48', 'trigger_type' => BadgeTriggerType::TaskStreak, 'trigger_threshold' => 30, 'is_hidden' => true, 'sort_order' => 6],
            ['name' => 'Кремень', 'description' => 'Выполняйте задачи 60 дней подряд', 'icon' => 'shield', 'color' => '#4338ca', 'trigger_type' => BadgeTriggerType::TaskStreak, 'trigger_threshold' => 60, 'is_hidden' => true, 'sort_order' => 7],

            // --- Points Earned progression ---
            ['name' => 'Восходящая звезда', 'description' => 'Заработайте 100 баллов', 'icon' => 'star-burst', 'color' => '#0d9488', 'trigger_type' => BadgeTriggerType::PointsEarned, 'trigger_threshold' => 100, 'is_hidden' => false, 'sort_order' => 8],
            ['name' => 'Охотник за баллами', 'description' => 'Заработайте 500 баллов', 'icon' => 'gem', 'color' => '#7c49b6', 'trigger_type' => BadgeTriggerType::PointsEarned, 'trigger_threshold' => 500, 'is_hidden' => false, 'sort_order' => 9],
            ['name' => 'Властелин баллов', 'description' => 'Заработайте 1 000 баллов', 'icon' => 'trophy', 'color' => '#d97706', 'trigger_type' => BadgeTriggerType::PointsEarned, 'trigger_threshold' => 1000, 'is_hidden' => true, 'sort_order' => 10],
            ['name' => 'Коллекционер алмазов', 'description' => 'Заработайте 5 000 баллов', 'icon' => 'diamond', 'color' => '#06b6d4', 'trigger_type' => BadgeTriggerType::PointsEarned, 'trigger_threshold' => 5000, 'is_hidden' => true, 'sort_order' => 11],

            // --- Kudos Given progression ---
            ['name' => 'Рука помощи', 'description' => 'Дайте 10 похвал членам семьи', 'icon' => 'heart-fire', 'color' => '#db2777', 'trigger_type' => BadgeTriggerType::KudosGiven, 'trigger_threshold' => 10, 'is_hidden' => false, 'sort_order' => 12],
            ['name' => 'Группа поддержки', 'description' => 'Дайте 50 похвал членам семьи', 'icon' => 'thumbs-up', 'color' => '#ec4899', 'trigger_type' => BadgeTriggerType::KudosGiven, 'trigger_threshold' => 50, 'is_hidden' => false, 'sort_order' => 13],

            // --- Kudos Received progression ---
            ['name' => 'Любимец семьи', 'description' => 'Получите 10 похвал от членов семьи', 'icon' => 'medal', 'color' => '#f59e0b', 'trigger_type' => BadgeTriggerType::KudosReceived, 'trigger_threshold' => 10, 'is_hidden' => false, 'sort_order' => 14],
            ['name' => 'МВП семьи', 'description' => 'Получите 50 похвал от членов семьи', 'icon' => 'crown', 'color' => '#a855f7', 'trigger_type' => BadgeTriggerType::KudosReceived, 'trigger_threshold' => 50, 'is_hidden' => true, 'sort_order' => 15],

            // --- Rewards Purchased progression ---
            ['name' => 'Первая покупка', 'description' => 'Купите первую награду', 'icon' => 'gift', 'color' => '#10b981', 'trigger_type' => BadgeTriggerType::RewardsPurchased, 'trigger_threshold' => 1, 'is_hidden' => false, 'sort_order' => 16],
            ['name' => 'Охотник за наградами', 'description' => 'Купите 10 наград', 'icon' => 'cake', 'color' => '#f97316', 'trigger_type' => BadgeTriggerType::RewardsPurchased, 'trigger_threshold' => 10, 'is_hidden' => false, 'sort_order' => 17],

            // --- Login Streak progression ---
            ['name' => 'Упорный', 'description' => 'Входите в приложение 7 дней подряд', 'icon' => 'check-circle', 'color' => '#2563eb', 'trigger_type' => BadgeTriggerType::LoginStreak, 'trigger_threshold' => 7, 'is_hidden' => false, 'sort_order' => 18],
            ['name' => 'Преданный', 'description' => 'Входите в приложение 30 дней подряд', 'icon' => 'infinity', 'color' => '#7c3aed', 'trigger_type' => BadgeTriggerType::LoginStreak, 'trigger_threshold' => 30, 'is_hidden' => true, 'sort_order' => 19],

            // --- Easter Egg Discoveries (individual eggs — custom type, awarded directly) ---
            ['name' => 'Взломщик кода', 'description' => 'Взломал код Konami', 'icon' => 'key', 'color' => '#059669', 'trigger_type' => BadgeTriggerType::Custom, 'trigger_threshold' => null, 'is_hidden' => true, 'sort_order' => 20],
            ['name' => 'Мастер чисел', 'description' => 'Почему шестёрка боится семёрки?', 'icon' => 'hashtag', 'color' => '#f59e0b', 'trigger_type' => BadgeTriggerType::Custom, 'trigger_threshold' => null, 'is_hidden' => true, 'sort_order' => 21],
            ['name' => 'Душа компании', 'description' => 'Задал легендарную вечеринку', 'icon' => 'sun', 'color' => '#ec4899', 'trigger_type' => BadgeTriggerType::Custom, 'trigger_threshold' => null, 'is_hidden' => true, 'sort_order' => 22],
            ['name' => 'Зеркальце', 'description' => 'Увидел всё наоборот', 'icon' => 'eye', 'color' => '#06b6d4', 'trigger_type' => BadgeTriggerType::Custom, 'trigger_threshold' => null, 'is_hidden' => true, 'sort_order' => 23],
            ['name' => 'Красная таблетка', 'description' => 'Вошел в цифровой дождь', 'icon' => 'lightning', 'color' => '#22c55e', 'trigger_type' => BadgeTriggerType::Custom, 'trigger_threshold' => null, 'is_hidden' => true, 'sort_order' => 24],
            ['name' => 'Диско-инферно', 'description' => 'Поймал ритм', 'icon' => 'music-note', 'color' => '#a855f7', 'trigger_type' => BadgeTriggerType::Custom, 'trigger_threshold' => null, 'is_hidden' => true, 'sort_order' => 25],

            // --- Master Explorer (auto-triggered when all 6 eggs found) ---
            ['name' => 'Мастер-исследователь', 'description' => 'Нашёл все пасхалки!', 'icon' => 'compass', 'color' => '#d97706', 'trigger_type' => BadgeTriggerType::EasterEgg, 'trigger_threshold' => 6, 'is_hidden' => true, 'sort_order' => 26],
        ];
    }

    /**
     * Create default badges for a family. Idempotent — uses firstOrCreate
     * so it's safe to call on new families AND existing ones with partial sets.
     *
     * @return Collection<Badge> The badges, keyed by name.
     */
    public static function createDefaultBadges(string $familyId, ?string $createdBy = null): Collection
    {
        $badges = collect();

        foreach (static::getDefaultBadgeDefinitions() as $data) {
            $badge = Badge::firstOrCreate(
                [
                    'family_id' => $familyId,
                    'name' => $data['name'],
                ],
                array_merge($data, [
                    'family_id' => $familyId,
                    'created_by' => $createdBy,
                    'trigger_type' => $data['trigger_type']->value,
                ])
            );
            $badges->put($data['name'], $badge);
        }

        return $badges;
    }

    /**
     * Check all badges for a user and award any newly earned ones.
     * Returns an array of newly earned badges.
     */
    public function checkAndAwardBadges(User $user): array
    {
        $familyBadges = Badge::where('family_id', $user->family_id)
            ->where('is_active', true)
            ->where('trigger_type', '!=', BadgeTriggerType::Custom)
            ->whereNotNull('trigger_threshold')
            ->get();

        $earnedBadgeIds = $user->badges()->pluck('badges.id')->toArray();
        $newlyEarned = [];

        foreach ($familyBadges as $badge) {
            if (in_array($badge->id, $earnedBadgeIds)) {
                continue;
            }

            $currentValue = $this->getCurrentValueForTrigger($user, $badge->trigger_type);

            if ($currentValue >= $badge->trigger_threshold) {
                $user->badges()->attach($badge->id, [
                    'id' => Str::uuid(),
                    'earned_at' => now(),
                    'awarded_by' => null,
                ]);
                $newlyEarned[] = $badge;
            }
        }

        return $newlyEarned;
    }

    /**
     * Manually award a badge to a user.
     */
    public function manuallyAward(Badge $badge, User $user, User $awardedBy): void
    {
        if ($user->badges()->where('badges.id', $badge->id)->exists()) {
            return;
        }

        $user->badges()->attach($badge->id, [
            'id' => Str::uuid(),
            'earned_at' => now(),
            'awarded_by' => $awardedBy->id,
        ]);
    }

    /**
     * Revoke a badge from a user.
     */
    public function revokeBadge(Badge $badge, User $user): void
    {
        $user->badges()->detach($badge->id);
    }

    /**
     * Get the user's current progress toward a specific trigger type.
     */
    public function getCurrentValueForTrigger(User $user, BadgeTriggerType $triggerType): int
    {
        return match ($triggerType) {
            BadgeTriggerType::PointsEarned => $this->getLifetimePointsEarned($user),
            BadgeTriggerType::TasksCompleted => $this->getCompletedTaskCount($user),
            BadgeTriggerType::TaskStreak => $this->getTaskStreak($user),
            BadgeTriggerType::KudosReceived => $this->getKudosReceived($user),
            BadgeTriggerType::KudosGiven => $this->getKudosGiven($user),
            BadgeTriggerType::RewardsPurchased => $this->getRewardsPurchased($user),
            BadgeTriggerType::LoginStreak => $this->getLoginStreak($user),
            BadgeTriggerType::Custom => 0,
            BadgeTriggerType::EasterEgg => $this->getEasterEggsFound($user),
        };
    }

    private function getLifetimePointsEarned(User $user): int
    {
        return (int) $user->pointTransactions()
            ->where('points', '>', 0)
            ->sum('points');
    }

    private function getCompletedTaskCount(User $user): int
    {
        // Count tasks completed by this user (assigned_to or completed family tasks)
        return Task::where('family_id', $user->family_id)
            ->whereNotNull('completed_at')
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            })
            ->count();
    }

    private function getTaskStreak(User $user): int
    {
        // Count consecutive days (backwards from today) where the user completed at least 1 task
        $streak = 0;
        $date = Carbon::today();

        while (true) {
            $completed = PointTransaction::where('user_id', $user->id)
                ->where('type', PointTransactionType::TaskCompletion)
                ->whereDate('created_at', $date)
                ->exists();

            if (! $completed) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }

    private function getKudosReceived(User $user): int
    {
        return $user->pointTransactions()
            ->where('type', PointTransactionType::Kudos)
            ->count();
    }

    private function getKudosGiven(User $user): int
    {
        return PointTransaction::where('awarded_by', $user->id)
            ->where('type', PointTransactionType::Kudos)
            ->count();
    }

    private function getRewardsPurchased(User $user): int
    {
        return $user->pointTransactions()
            ->where('type', PointTransactionType::Redemption)
            ->count();
    }

    private function getEasterEggsFound(User $user): int
    {
        return count($user->easter_eggs_found ?? []);
    }

    private function getLoginStreak(User $user): int
    {
        // Simplified: count consecutive days with any transaction activity
        // A proper implementation would track login events separately
        $streak = 0;
        $date = Carbon::today();

        while (true) {
            $active = PointTransaction::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->exists();

            if (! $active) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }
}
