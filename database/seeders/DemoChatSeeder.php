<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoChatSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $now = Carbon::now();

        // ─────────────────────────────────────────────
        //  CHAT MESSAGES (sample conversation)
        // ─────────────────────────────────────────────

        $chatConvo = [
            ['role' => 'user', 'user' => $this->mike, 'message' => 'Какие задачи нужно выполнить на этой неделе?', 'days_ago' => 5, 'hour' => 8],
            ['role' => 'assistant', 'user' => $this->mike, 'message' => "Вот задачи на эту неделю для семьи Джонсон:\n\n• **Emma** — Разобрать и подшить школьные бумаги (10 очков) — срок сегодня\n• **Jake** — Подмести гараж (10 очков) — срок через 2 дня\n• **Lily** — Потренировать пьесу к концерту на фортепиано (15 очков) — срок через 3 дня\n• **Sarah** — Составить план питания на неделю (10 очков) — срок завтра\n\nХотите, я добавлю новые задачи или проверю что-то ещё?", 'days_ago' => 5, 'hour' => 8],
            ['role' => 'user', 'user' => $this->sarah, 'message' => 'Кто лидирует в таблице лидеров?', 'days_ago' => 3, 'hour' => 19],
            ['role' => 'assistant', 'user' => $this->sarah, 'message' => "Вот текущая таблица лидеров за неделю:\n\n**1-е место — Emma** — 15 очков за неделю\n**2-е место — Jake** — 10 очков за неделю\n**3-е место — Lily** — 15 очков за неделю\n\nEmma и Lily идут вровень! Emma сейчас в ударе: готовится к SAT и справляется с домашними делами.", 'days_ago' => 3, 'hour' => 19],
            ['role' => 'user', 'user' => $this->mike, 'message' => 'Какой пароль от Wi-Fi?', 'days_ago' => 2, 'hour' => 14],
            ['role' => 'assistant', 'user' => $this->mike, 'message' => "Пароль вашего Wi-Fi: **B1scu1t-Loves-Treats!** (сеть: JohnsonFamily5G)\n\nГостевая сеть: **JohnsonGuest** / Welcome2024", 'days_ago' => 2, 'hour' => 14],
            ['role' => 'user', 'user' => $this->emma, 'message' => 'Сколько у меня очков?', 'days_ago' => 1, 'hour' => 16],
            ['role' => 'assistant', 'user' => $this->emma, 'message' => "Сейчас на твоём счету **185 очков**, Emma!\n\nВсего ты заработала 325 очков и потратила 140 на награды. Твоя последняя награда — **Movie Night Pick** (40 очков) на прошлой неделе.\n\nТебе уже недалеко до награды **Friend Sleepover** (100 очков)!", 'days_ago' => 1, 'hour' => 16],
            ['role' => 'user', 'user' => $this->sarah, 'message' => 'Когда у Jake следующий приём у стоматолога?', 'days_ago' => 0, 'hour' => 9],
            ['role' => 'assistant', 'user' => $this->sarah, 'message' => "Следующий приём Jake у стоматолога — **15 апреля** в Bright Smiles Family Dental, врач Dr. Maria Lopez. В тот же день приём и у Lily.\n\nЗаметка из хранилища: Jake, возможно, понадобится консультация по поводу брекетов этим летом.", 'days_ago' => 0, 'hour' => 9],
        ];

        foreach ($chatConvo as $msg) {
            $createdAt = $now->copy()->subDays($msg['days_ago'])->setHour($msg['hour'])->setMinute(rand(0, 30));
            ChatMessage::create([
                'family_id' => $this->familyId(),
                'user_id' => $msg['user']->id,
                'role' => $msg['role'],
                'message' => $msg['message'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
