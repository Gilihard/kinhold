<?php

namespace Database\Seeders;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoTaskSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $now = Carbon::now();

        // Helper to attach tags with UUID id
        $attachTags = function (Task $task, array $tagNames) {
            foreach ($tagNames as $tn) {
                if (isset($this->tags[$tn])) {
                    $task->tags()->attach($this->tags[$tn]->id, ['id' => Str::uuid()]);
                }
            }
        };

        // ─────────────────────────────────────────────
        //  RECURRING TASK TEMPLATES
        // ─────────────────────────────────────────────

        $recurringTemplates = [
            [
                'title' => 'Вынести мусор',
                'description' => 'Вынеси мусорные баки к дороге до 7 утра',
                'priority' => 'medium',
                'points' => 10,
                'is_family_task' => true,
                'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=TU',
                'tags' => ['Домашние дела'],
                'created_by' => $this->mike->id,
            ],
            [
                'title' => 'Разгрузить посудомойку',
                'description' => 'Достань чистую посуду и расставь её по местам',
                'priority' => 'low',
                'points' => 5,
                'is_family_task' => false,
                'assigned_to' => $this->jake->id,
                'recurrence_rule' => 'FREQ=DAILY',
                'tags' => ['Домашние дела'],
                'created_by' => $this->sarah->id,
            ],
            [
                'title' => 'Выгулять Biscuit',
                'description' => 'Прогуляйся с Biscuit 20 минут вокруг квартала',
                'priority' => 'medium',
                'points' => 10,
                'is_family_task' => false,
                'assigned_to' => $this->emma->id,
                'recurrence_rule' => 'FREQ=DAILY',
                'tags' => ['Питомцы'],
                'created_by' => $this->mike->id,
            ],
            [
                'title' => 'Прибраться в комнате',
                'description' => 'Заправь кровать, убери одежду, приведи в порядок стол',
                'priority' => 'medium',
                'points' => 10,
                'is_family_task' => true,
                'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=SA',
                'tags' => ['Домашние дела'],
                'created_by' => $this->sarah->id,
            ],
            [
                'title' => 'Подстричь газон',
                'description' => 'Передний и задний двор — сначала проверь уровень бензина',
                'priority' => 'high',
                'points' => 25,
                'is_family_task' => false,
                'assigned_to' => $this->emma->id,
                'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=SA',
                'tags' => ['Работа во дворе'],
                'created_by' => $this->mike->id,
            ],
            [
                'title' => 'Покормить Biscuit',
                'description' => 'Утром и вечером — по одной чашке сухого корма',
                'priority' => 'high',
                'points' => 5,
                'is_family_task' => false,
                'assigned_to' => $this->lily->id,
                'recurrence_rule' => 'FREQ=DAILY',
                'tags' => ['Питомцы'],
                'created_by' => $this->sarah->id,
            ],
        ];

        foreach ($recurringTemplates as $rt) {
            $tagNames = $rt['tags'];
            unset($rt['tags']);
            $task = Task::create(array_merge($rt, ['family_id' => $this->familyId()]));
            $attachTags($task, $tagNames);
        }

        // ─────────────────────────────────────────────
        //  3 MONTHS OF COMPLETED TASKS & POINT TRANSACTIONS
        // ─────────────────────────────────────────────

        $parents = $this->parents();

        // Generate realistic completed tasks spread over ~90 days
        $completedTaskDefs = [
            // ── Week 1-2 (about 75-90 days ago) ──
            ['title' => 'Завести аккаунты семьи в приложении', 'assignee' => $this->mike, 'points' => 15, 'days_ago' => 89, 'priority' => 'high', 'tags' => ['Семейные развлечения']],
            ['title' => 'Пропылесосить гостиную', 'assignee' => $this->emma, 'points' => 10, 'days_ago' => 88, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Сложить бельё', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 87, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Полить сад', 'assignee' => $this->lily, 'points' => 5, 'days_ago' => 86, 'priority' => 'low', 'tags' => ['Работа во дворе']],
            ['title' => 'Помочь Naia с домашним заданием по математике', 'assignee' => $this->emma, 'points' => 15, 'days_ago' => 85, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Разобрать гараж', 'assignee' => $this->mike, 'points' => 20, 'days_ago' => 84, 'priority' => 'high', 'tags' => ['Домашние дела']],
            ['title' => 'Навести порядок в кладовке', 'assignee' => $this->sarah, 'points' => 15, 'days_ago' => 83, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Собрать ветки во дворе', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 82, 'priority' => 'medium', 'tags' => ['Работа во дворе']],
            ['title' => 'Позаниматься фортепиано — 30 минут', 'assignee' => $this->lily, 'points' => 10, 'days_ago' => 81, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Протереть кухонные столешницы', 'assignee' => $this->emma, 'points' => 5, 'days_ago' => 80, 'priority' => 'low', 'tags' => ['Домашние дела']],
            ['title' => 'Починить протекающий кран', 'assignee' => $this->mike, 'points' => 20, 'days_ago' => 79, 'priority' => 'high', 'tags' => ['Домашние дела']],
            ['title' => 'Рассортировать вторсырьё', 'assignee' => $this->jake, 'points' => 5, 'days_ago' => 78, 'priority' => 'low', 'tags' => ['Домашние дела']],

            // ── Week 3-4 (about 65-77 days ago) ──
            ['title' => 'Помыть машину', 'assignee' => $this->emma, 'points' => 15, 'days_ago' => 76, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Вернуть книги в библиотеку', 'assignee' => $this->sarah, 'points' => 5, 'days_ago' => 75, 'priority' => 'low', 'tags' => ['Школа']],
            ['title' => 'Подмести крыльцо', 'assignee' => $this->lily, 'points' => 5, 'days_ago' => 74, 'priority' => 'low', 'tags' => ['Домашние дела']],
            ['title' => 'Домашнее задание — постер для научного проекта', 'assignee' => $this->jake, 'points' => 20, 'days_ago' => 73, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Сходить за продуктами', 'assignee' => $this->sarah, 'points' => 10, 'days_ago' => 72, 'priority' => 'medium', 'tags' => ['Покупки']],
            ['title' => 'Помыть пол на кухне', 'assignee' => $this->emma, 'points' => 10, 'days_ago' => 71, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Убрать в ванной', 'assignee' => $this->jake, 'points' => 15, 'days_ago' => 70, 'priority' => 'high', 'tags' => ['Домашние дела']],
            ['title' => 'Сгрести листья — передний двор', 'assignee' => $this->emma, 'points' => 15, 'days_ago' => 69, 'priority' => 'medium', 'tags' => ['Работа во дворе']],
            ['title' => 'Настроить таймер полива', 'assignee' => $this->mike, 'points' => 10, 'days_ago' => 68, 'priority' => 'medium', 'tags' => ['Работа во дворе']],
            ['title' => 'Потренироваться в правописании', 'assignee' => $this->lily, 'points' => 10, 'days_ago' => 67, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Протереть пыль со всех книжных полок', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 66, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Отвести Biscuit к ветеринару', 'assignee' => $this->sarah, 'points' => 15, 'days_ago' => 65, 'priority' => 'high', 'tags' => ['Питомцы']],

            // ── Month 2 (about 35-64 days ago) ──
            ['title' => 'Помыть окна на первом этаже', 'assignee' => $this->emma, 'points' => 15, 'days_ago' => 62, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Навести порядок на книжной полке', 'assignee' => $this->lily, 'points' => 10, 'days_ago' => 60, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Футбольная экипировка — почистить и разобрать', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 58, 'priority' => 'medium', 'tags' => ['Спорт']],
            ['title' => 'Подготовиться к контрольной по истории', 'assignee' => $this->emma, 'points' => 15, 'days_ago' => 56, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Испечь печенье на школьную ярмарку', 'assignee' => $this->sarah, 'points' => 15, 'days_ago' => 55, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Заменить воздушные фильтры', 'assignee' => $this->mike, 'points' => 10, 'days_ago' => 54, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Прополоть клумбы', 'assignee' => $this->emma, 'points' => 15, 'days_ago' => 52, 'priority' => 'medium', 'tags' => ['Работа во дворе']],
            ['title' => 'Помочь Kenji с отзывом о книге', 'assignee' => $this->mike, 'points' => 10, 'days_ago' => 50, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Пропылесосить лестницу', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 48, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Покрасить акцентную стену в спальне', 'assignee' => $this->emma, 'points' => 25, 'days_ago' => 46, 'priority' => 'high', 'tags' => ['Семейные развлечения']],
            ['title' => 'Подготовка к семейному игровому вечеру', 'assignee' => $this->lily, 'points' => 5, 'days_ago' => 44, 'priority' => 'low', 'tags' => ['Семейные развлечения']],
            ['title' => 'Забрать лекарства по рецепту', 'assignee' => $this->sarah, 'points' => 5, 'days_ago' => 42, 'priority' => 'low', 'tags' => ['Покупки']],
            ['title' => 'Подтянуть шатающийся стул', 'assignee' => $this->mike, 'points' => 5, 'days_ago' => 41, 'priority' => 'low', 'tags' => ['Домашние дела']],
            ['title' => 'Подстричь живую изгородь', 'assignee' => $this->mike, 'points' => 15, 'days_ago' => 40, 'priority' => 'medium', 'tags' => ['Работа во дворе']],
            ['title' => 'Прочитать 2 главы романа', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 39, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Разобрать холодильник', 'assignee' => $this->sarah, 'points' => 10, 'days_ago' => 38, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Разобрать спортивный инвентарь', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 36, 'priority' => 'medium', 'tags' => ['Спорт']],

            // ── Month 3 / Recent (0-34 days ago) ──
            ['title' => 'Написать благодарственные открытки', 'assignee' => $this->lily, 'points' => 10, 'days_ago' => 33, 'priority' => 'medium', 'tags' => ['Семейные развлечения']],
            ['title' => 'Сделать генеральную уборку кухни', 'assignee' => $this->emma, 'points' => 25, 'days_ago' => 31, 'priority' => 'high', 'tags' => ['Домашние дела']],
            ['title' => 'Починить колесо велосипеда', 'assignee' => $this->mike, 'points' => 10, 'days_ago' => 30, 'priority' => 'medium', 'tags' => ['Спорт']],
            ['title' => 'Подвезти на футбольную тренировку', 'assignee' => $this->sarah, 'points' => 10, 'days_ago' => 28, 'priority' => 'medium', 'tags' => ['Спорт']],
            ['title' => 'Убрать кухню после ужина', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 27, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Подготовиться к конкурсу по правописанию', 'assignee' => $this->lily, 'points' => 15, 'days_ago' => 25, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Приготовить обеды на неделю', 'assignee' => $this->sarah, 'points' => 15, 'days_ago' => 24, 'priority' => 'medium', 'tags' => ['Покупки']],
            ['title' => 'Помыть подъездную дорожку мойкой под давлением', 'assignee' => $this->mike, 'points' => 20, 'days_ago' => 22, 'priority' => 'high', 'tags' => ['Работа во дворе']],
            ['title' => 'Разобрать рюкзак', 'assignee' => $this->jake, 'points' => 5, 'days_ago' => 21, 'priority' => 'low', 'tags' => ['Школа']],
            ['title' => 'Тренировочный тест SAT — часть 1', 'assignee' => $this->emma, 'points' => 20, 'days_ago' => 20, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Полить комнатные растения', 'assignee' => $this->lily, 'points' => 5, 'days_ago' => 19, 'priority' => 'low', 'tags' => ['Домашние дела']],
            ['title' => 'Смазать скрипящие дверные петли', 'assignee' => $this->mike, 'points' => 5, 'days_ago' => 18, 'priority' => 'low', 'tags' => ['Домашние дела']],
            ['title' => 'Постирать и сложить полотенца', 'assignee' => $this->emma, 'points' => 10, 'days_ago' => 16, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Разобрать принадлежности для творчества', 'assignee' => $this->lily, 'points' => 10, 'days_ago' => 15, 'priority' => 'medium', 'tags' => ['Семейные развлечения']],
            ['title' => 'Домашнее задание — рабочий лист по математике', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 14, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Прочистить водосточные желоба', 'assignee' => $this->mike, 'points' => 20, 'days_ago' => 13, 'priority' => 'high', 'tags' => ['Работа во дворе']],
            ['title' => 'Отдать старую одежду на благотворительность', 'assignee' => $this->sarah, 'points' => 10, 'days_ago' => 12, 'priority' => 'medium', 'tags' => ['Покупки']],
            ['title' => 'Расчесать Biscuit', 'assignee' => $this->lily, 'points' => 5, 'days_ago' => 11, 'priority' => 'low', 'tags' => ['Питомцы']],
            ['title' => 'Пропылесосить все спальни', 'assignee' => $this->jake, 'points' => 15, 'days_ago' => 10, 'priority' => 'high', 'tags' => ['Домашние дела']],
            ['title' => 'Тренировочный тест SAT — часть 2', 'assignee' => $this->emma, 'points' => 20, 'days_ago' => 9, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Сделать бабушке открытку на день рождения', 'assignee' => $this->lily, 'points' => 10, 'days_ago' => 8, 'priority' => 'medium', 'tags' => ['Семейные развлечения']],
            ['title' => 'Навести порядок в сарае с инструментами', 'assignee' => $this->mike, 'points' => 15, 'days_ago' => 7, 'priority' => 'medium', 'tags' => ['Работа во дворе']],
            ['title' => 'Отчистить ванну', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 6, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Разобрать и подшить школьные бумаги', 'assignee' => $this->emma, 'points' => 10, 'days_ago' => 5, 'priority' => 'medium', 'tags' => ['Школа']],
            ['title' => 'Составить меню на неделю', 'assignee' => $this->sarah, 'points' => 10, 'days_ago' => 4, 'priority' => 'medium', 'tags' => ['Покупки']],
            ['title' => 'Подмести гараж', 'assignee' => $this->jake, 'points' => 10, 'days_ago' => 3, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Отрепетировать пьесу для концерта', 'assignee' => $this->lily, 'points' => 15, 'days_ago' => 2, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Протереть все дверные ручки', 'assignee' => $this->emma, 'points' => 5, 'days_ago' => 1, 'priority' => 'low', 'tags' => ['Домашние дела']],
        ];

        // Create completed tasks and matching point transactions
        foreach ($completedTaskDefs as $def) {
            $tagNames = $def['tags'];
            $assignee = $def['assignee'];
            $daysAgo = $def['days_ago'];
            $completedAt = $now->copy()->subDays($daysAgo)->setHour(rand(9, 20))->setMinute(rand(0, 59));

            $task = Task::create([
                'family_id' => $this->familyId(),
                'created_by' => collect($parents)->random()->id,
                'assigned_to' => $assignee->id,
                'title' => $def['title'],
                'priority' => $def['priority'],
                'points' => $def['points'],
                'completed_at' => $completedAt,
                'created_at' => $completedAt->copy()->subHours(rand(1, 48)),
                'updated_at' => $completedAt,
            ]);

            $attachTags($task, $tagNames);

            // Matching point transaction
            PointTransaction::create([
                'family_id' => $this->familyId(),
                'user_id' => $assignee->id,
                'type' => PointTransactionType::TaskCompletion->value,
                'points' => $def['points'],
                'description' => "Выполнено: {$def['title']}",
                'source_type' => Task::class,
                'source_id' => $task->id,
                'created_at' => $completedAt,
                'updated_at' => $completedAt,
            ]);
        }

        // ─────────────────────────────────────────────
        //  PENDING / UPCOMING TASKS
        // ─────────────────────────────────────────────

        $pendingTaskDefs = [
            ['title' => 'Купить новые бутсы', 'assignee' => $this->sarah, 'points' => 10, 'due_days' => 1, 'priority' => 'medium', 'tags' => ['Покупки', 'Спорт']],
            ['title' => 'Дописать сочинение по английскому', 'assignee' => $this->emma, 'points' => 20, 'due_days' => 2, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Убрать в машине', 'assignee' => $this->mike, 'points' => 10, 'due_days' => 3, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Вернуть посылки Amazon', 'assignee' => $this->sarah, 'points' => 5, 'due_days' => 3, 'priority' => 'low', 'tags' => ['Покупки']],
            ['title' => 'Научный проект — собрать модель', 'assignee' => $this->jake, 'points' => 25, 'due_days' => 5, 'priority' => 'high', 'tags' => ['Школа']],
            ['title' => 'Навести порядок в шкафу с игрушками', 'assignee' => $this->lily, 'points' => 10, 'due_days' => 4, 'priority' => 'medium', 'tags' => ['Домашние дела']],
            ['title' => 'Спланировать семейный киновечер', 'assignee' => null, 'points' => 5, 'due_days' => 6, 'priority' => 'low', 'tags' => ['Семейные развлечения'], 'is_family_task' => true],
            ['title' => 'Замульчировать клумбы', 'assignee' => $this->mike, 'points' => 20, 'due_days' => 7, 'priority' => 'high', 'tags' => ['Работа во дворе']],
            ['title' => 'Записать Biscuit на груминг', 'assignee' => $this->sarah, 'points' => 5, 'due_days' => 5, 'priority' => 'low', 'tags' => ['Питомцы']],
            ['title' => 'Подготовиться к контрольной по алгебре', 'assignee' => $this->jake, 'points' => 15, 'due_days' => 2, 'priority' => 'high', 'tags' => ['Школа']],
        ];

        // One overdue task for realism
        $overdueTask = Task::create([
            'family_id' => $this->familyId(),
            'created_by' => $this->sarah->id,
            'assigned_to' => $this->emma->id,
            'title' => 'Вернуть книги в библиотеку (просрочено!)',
            'priority' => 'high',
            'points' => 5,
            'due_date' => $now->copy()->subDays(2)->toDateString(),
            'created_at' => $now->copy()->subDays(9),
        ]);
        $attachTags($overdueTask, ['Школа']);

        foreach ($pendingTaskDefs as $def) {
            $tagNames = $def['tags'];
            unset($def['tags']);

            $task = Task::create([
                'family_id' => $this->familyId(),
                'created_by' => collect($parents)->random()->id,
                'assigned_to' => $def['assignee']?->id,
                'title' => $def['title'],
                'priority' => $def['priority'],
                'points' => $def['points'],
                'due_date' => $now->copy()->addDays($def['due_days'])->toDateString(),
                'is_family_task' => $def['is_family_task'] ?? false,
            ]);

            $attachTags($task, $tagNames);
        }
    }
}
