<?php

namespace Database\Seeders;

use App\Models\FamilyEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoCalendarSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $now = Carbon::now();

        // ─────────────────────────────────────────────
        //  FEATURED EVENTS
        // ─────────────────────────────────────────────

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'День рождения Маркуса',
            'description' => 'С днём рождения, Маркус!',
            'start_time' => $now->copy()->addDays(18)->startOfDay(),
            'all_day' => true,
            'icon' => 'cake',
            'color' => '#EC4899',
            'recurrence' => 'yearly',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Годовщина свадьбы',
            'description' => 'Отмечаем ещё один замечательный год вместе',
            'start_time' => $now->copy()->addDays(79)->startOfDay(),
            'all_day' => true,
            'icon' => 'heart',
            'color' => '#EF4444',
            'recurrence' => 'yearly',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Семейный игровой вечер',
            'description' => 'Настольные игры и веселье для всей семьи!',
            'start_time' => $now->copy()->next('Friday')->setHour(19)->setMinute(0),
            'all_day' => true,
            'icon' => 'game',
            'color' => '#8B5CF6',
            'recurrence' => 'weekly',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Весенние каникулы',
            'description' => 'Нет учёбы — семейная поездка на озеро!',
            'start_time' => $now->copy()->addDays(14)->startOfDay(),
            'all_day' => true,
            'icon' => 'palm',
            'color' => '#F59E0B',
            'recurrence' => 'none',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'День рождения Зары',
            'description' => 'С 17-летием, Зара!',
            'start_time' => $now->copy()->addDays(32)->startOfDay(),
            'all_day' => true,
            'icon' => 'cake',
            'color' => '#F472B6',
            'recurrence' => 'yearly',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Футбольный турнир',
            'description' => 'Региональный футбольный турнир Кенджи — вперёд, «Wildcats»!',
            'start_time' => $now->copy()->addDays(10)->setHour(9)->setMinute(0),
            'all_day' => true,
            'icon' => 'soccer',
            'color' => '#10B981',
            'recurrence' => 'none',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'title' => 'Научная ярмарка',
            'description' => 'Проект «вулкан» Кенджи — оценка работ в 14:00',
            'start_time' => $now->copy()->addDays(21)->setHour(14)->setMinute(0),
            'all_day' => true,
            'icon' => 'star',
            'color' => '#6366F1',
            'recurrence' => 'none',
            'featured_scope' => 'family',
        ]);

        FamilyEvent::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->mike->id,
            'title' => 'Художественная выставка Найи',
            'description' => 'Весенняя художественная выставка начальной школы Cedar Ridge — на ней представлены две работы Найи!',
            'start_time' => $now->copy()->addDays(28)->setHour(18)->setMinute(0),
            'all_day' => false,
            'icon' => 'star',
            'color' => '#EC4899',
            'recurrence' => 'none',
            'featured_scope' => 'family',
        ]);

        // ─────────────────────────────────────────────
        //  CALENDAR EVENTS
        // ─────────────────────────────────────────────

        $calendarEvents = [
            // ── Today ──
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => 0, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Урок фортепиано — Найя', 'creator' => $this->sarah, 'color' => '#EC4899', 'days' => 0, 'hour' => 14, 'duration' => 45, 'location' => 'Ms. Chen\'s Studio'],

            // ── This week ──
            ['title' => 'Собрание учителей и родителей', 'creator' => $this->sarah, 'color' => '#F59E0B', 'days' => 1, 'hour' => 15, 'duration' => 30, 'location' => 'Riverside Middle School'],
            ['title' => 'Стоматолог — Кенджи и Найя', 'creator' => $this->sarah, 'color' => '#EF4444', 'days' => 2, 'hour' => 10, 'duration' => 60, 'location' => 'Bright Smiles Family Dental'],
            ['title' => 'Свидание', 'creator' => $this->mike, 'color' => '#EF4444', 'days' => 2, 'hour' => 19, 'duration' => 180, 'location' => 'Trattoria Bella'],
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => 3, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Закупка продуктов', 'creator' => $this->sarah, 'color' => '#8B5CF6', 'days' => 3, 'hour' => 10, 'duration' => 60, 'location' => 'Costco'],
            ['title' => 'Зара — подготовка к SAT', 'creator' => $this->mike, 'color' => '#F59E0B', 'days' => 4, 'hour' => 17, 'duration' => 120, 'location' => 'Kumon Learning Center'],
            ['title' => 'Урок фортепиано — Найя', 'creator' => $this->sarah, 'color' => '#EC4899', 'days' => 4, 'hour' => 14, 'duration' => 45, 'location' => 'Ms. Chen\'s Studio'],
            ['title' => 'Найя — урок рисования', 'creator' => $this->sarah, 'color' => '#F472B6', 'days' => 5, 'hour' => 10, 'duration' => 60, 'location' => 'Cedar Ridge Art Center'],

            // ── Next week ──
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => 7, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Бисквит — осмотр у ветеринара', 'creator' => $this->sarah, 'color' => '#D97706', 'days' => 7, 'hour' => 9, 'duration' => 30, 'location' => 'Paws & Claws Veterinary'],
            ['title' => 'Зара — урок вождения', 'creator' => $this->mike, 'color' => '#6366F1', 'days' => 8, 'hour' => 15, 'duration' => 60],
            ['title' => 'Школа — ранний уход', 'creator' => $this->sarah, 'color' => '#F59E0B', 'days' => 9, 'all_day' => true],
            ['title' => 'Урок фортепиано — Найя', 'creator' => $this->sarah, 'color' => '#EC4899', 'days' => 9, 'hour' => 14, 'duration' => 45, 'location' => 'Ms. Chen\'s Studio'],
            ['title' => 'Футбольный матч против «Eagles»', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => 10, 'hour' => 10, 'duration' => 120, 'location' => 'County Sports Complex'],
            ['title' => 'В гости бабушка и дедушка', 'creator' => $this->mike, 'color' => '#EC4899', 'days' => 11, 'all_day' => true],
            ['title' => 'Найя — урок рисования', 'creator' => $this->sarah, 'color' => '#F472B6', 'days' => 12, 'hour' => 10, 'duration' => 60, 'location' => 'Cedar Ridge Art Center'],
            ['title' => 'Маркус — рабочая конференция', 'creator' => $this->sarah, 'color' => '#6B7280', 'days' => 12, 'hour' => 8, 'duration' => 480, 'location' => 'Downtown Convention Center'],

            // ── Two weeks out ──
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => 14, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Адаэзе — книжный клуб', 'creator' => $this->mike, 'color' => '#8B5CF6', 'days' => 15, 'hour' => 19, 'duration' => 120, 'location' => 'Community Library'],
            ['title' => 'Кенджи — IEP-собрание', 'creator' => $this->sarah, 'color' => '#F59E0B', 'days' => 16, 'hour' => 9, 'duration' => 60, 'location' => 'Riverside Middle School'],
            ['title' => 'Зара — подготовка к SAT', 'creator' => $this->mike, 'color' => '#F59E0B', 'days' => 18, 'hour' => 17, 'duration' => 120, 'location' => 'Kumon Learning Center'],
            ['title' => 'Урок фортепиано — Найя', 'creator' => $this->sarah, 'color' => '#EC4899', 'days' => 18, 'hour' => 14, 'duration' => 45, 'location' => 'Ms. Chen\'s Studio'],
            ['title' => 'Футбольный матч против «Falcons»', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => 19, 'hour' => 10, 'duration' => 120, 'location' => 'County Sports Complex'],
            ['title' => 'Семейный бранч', 'creator' => $this->mike, 'color' => '#EC4899', 'days' => 20, 'hour' => 10, 'duration' => 120],

            // ── Past events this month ──
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => -3, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Замена масла — минивэн', 'creator' => $this->mike, 'color' => '#6B7280', 'days' => -4, 'hour' => 8, 'duration' => 60, 'location' => 'Jiffy Lube'],
            ['title' => 'Урок фортепиано — Найя', 'creator' => $this->sarah, 'color' => '#EC4899', 'days' => -5, 'hour' => 14, 'duration' => 45, 'location' => 'Ms. Chen\'s Studio'],
            ['title' => 'Адаэзе — книжный клуб', 'creator' => $this->sarah, 'color' => '#8B5CF6', 'days' => -6, 'hour' => 19, 'duration' => 120, 'location' => 'Community Library'],
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => -7, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Зара — подготовка к SAT', 'creator' => $this->mike, 'color' => '#F59E0B', 'days' => -8, 'hour' => 17, 'duration' => 120, 'location' => 'Kumon Learning Center'],
            ['title' => 'Семейный киновечер', 'creator' => $this->mike, 'color' => '#8B5CF6', 'days' => -9, 'hour' => 19, 'duration' => 150],
            ['title' => 'Кенджи — футбольный матч против «Thunder»', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => -10, 'hour' => 10, 'duration' => 120, 'location' => 'County Sports Complex'],
            ['title' => 'Найя — репетиция спектакля', 'creator' => $this->sarah, 'color' => '#EC4899', 'days' => -12, 'hour' => 15, 'duration' => 120, 'location' => 'Cedar Ridge Auditorium'],
            ['title' => 'Найя — урок рисования', 'creator' => $this->sarah, 'color' => '#F472B6', 'days' => -14, 'hour' => 10, 'duration' => 60, 'location' => 'Cedar Ridge Art Center'],
            ['title' => 'Адаэзе — стоматолог', 'creator' => $this->mike, 'color' => '#EF4444', 'days' => -15, 'hour' => 11, 'duration' => 60, 'location' => 'Bright Smiles Family Dental'],
            ['title' => 'Маркус — рабочая конференция', 'creator' => $this->sarah, 'color' => '#6B7280', 'days' => -16, 'hour' => 8, 'duration' => 480, 'location' => 'Downtown Convention Center'],
            ['title' => 'Зара — урок вождения', 'creator' => $this->mike, 'color' => '#6366F1', 'days' => -18, 'hour' => 15, 'duration' => 60],
            ['title' => 'Футбольная тренировка', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => -21, 'hour' => 16, 'duration' => 90, 'location' => 'Riverside Park Field 3'],
            ['title' => 'Семейный ужин вне дома', 'creator' => $this->mike, 'color' => '#EF4444', 'days' => -22, 'hour' => 18, 'duration' => 90, 'location' => 'Pho Palace'],
            ['title' => 'Кенджи — футбольный матч против «Storm»', 'creator' => $this->sarah, 'color' => '#10B981', 'days' => -24, 'hour' => 10, 'duration' => 120, 'location' => 'Riverside Park Field 1'],
        ];

        foreach ($calendarEvents as $ce) {
            $isAllDay = $ce['all_day'] ?? false;
            $startDay = $now->copy()->addDays($ce['days']);

            if ($isAllDay) {
                $startTime = $startDay->copy()->startOfDay();
                $endTime = $startDay->copy()->endOfDay();
            } else {
                $startTime = $startDay->copy()->setHour($ce['hour'])->setMinute(0)->setSecond(0);
                $endTime = $startTime->copy()->addMinutes($ce['duration']);
            }

            FamilyEvent::create([
                'family_id' => $this->familyId(),
                'created_by' => $ce['creator']->id,
                'title' => $ce['title'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'all_day' => $isAllDay,
                'location' => $ce['location'] ?? null,
                'color' => $ce['color'],
            ]);
        }
    }
}
