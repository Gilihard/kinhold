<?php

/**
 * Kinhold notification type registry.
 *
 * Adding a new push/email notification type:
 * 1. Drop a class in app/Notifications/ that uses User::wants($channel, $key) inside via().
 * 2. Add an entry to 'types' below — the Settings UI and User::wants() pick it up automatically.
 * 3. Wire the dispatch site (controller, service, scheduled command, etc).
 *
 * Categories drive the grouped accordion in the Settings UI. Adding a new category
 * is just a new key here plus a translatable label.
 */
return [

    'categories' => [
        'tasks' => 'Задачи',
        'points' => 'Баллы и похвала',
        'shopping' => 'Покупки',
        'calendar' => 'Календарь',
        'food' => 'Питание',
        'family' => 'Активность семьи',
        'billing' => 'Оплата и подписка',
    ],

    /*
     * Each type entry:
     *   - category:        which group it appears under in Settings
     *   - label:           one-line user-facing description ("When ...")
     *   - description:     optional secondary line (sub-text under the toggle row)
     *   - channels:        which delivery channels are SUPPORTED (controls which switches render)
     *   - default_email:   on/off default for new users (only meaningful if 'email' in channels)
     *   - default_push:    on/off default for new users (only meaningful if 'push' in channels)
     *   - requires_module: optional Family module key ('tasks', 'points', etc) — type is hidden
     *                      from Settings if the family has the module disabled
     */
    'types' => [

        'task_assigned' => [
            'category' => 'tasks',
            'label' => 'Когда мне назначают задачу',
            'channels' => ['email', 'push'],
            'default_email' => true,
            'default_push' => true,
            'requires_module' => 'tasks',
        ],

        'task_completed' => [
            'category' => 'tasks',
            'label' => 'Когда завершают задачу, которую я создал(а)',
            'channels' => ['email'],
            'default_email' => true,
            'default_push' => false,
            'requires_module' => 'tasks',
        ],

        'kudos_received' => [
            'category' => 'points',
            'label' => 'Когда член семьи отправляет мне похвалу',
            'channels' => ['email', 'push'],
            'default_email' => false,
            'default_push' => true,
            'requires_module' => 'points',
        ],

        'weekly_digest' => [
            'category' => 'family',
            'label' => 'Еженедельный дайджест',
            'description' => 'Воскресное утреннее резюме вашей недели',
            'channels' => ['email'],
            'default_email' => true,
            'default_push' => false,
        ],

        'family_invite' => [
            'category' => 'family',
            'label' => 'Приглашения в семью',
            'channels' => ['email'],
            'default_email' => true,
            'default_push' => false,
        ],

        'task_due_soon' => [
            'category' => 'tasks',
            'label' => 'Когда срок моей задачи — сегодня',
            'description' => 'Напоминание в 8:00 в день, когда задача должна быть выполнена',
            'channels' => ['email', 'push'],
            'default_email' => false,
            'default_push' => true,
            'requires_module' => 'tasks',
        ],

        'shopping_item_added' => [
            'category' => 'shopping',
            'label' => 'Когда кто-то добавляет товар в общий список покупок',
            'channels' => ['push'],
            'default_email' => false,
            'default_push' => false,
            'requires_module' => 'shopping',
        ],

        'calendar_event_reminder' => [
            'category' => 'calendar',
            'label' => 'Напоминания о событиях календаря',
            'description' => 'Время напоминания задаётся при создании события',
            'channels' => ['email', 'push'],
            'default_email' => false,
            'default_push' => true,
        ],

        'dinner_reminder' => [
            'category' => 'food',
            'label' => 'Что сегодня на ужин',
            'description' => 'Ежедневное push-уведомление с запланированным на вечер блюдом',
            'channels' => ['push'],
            'default_email' => false,
            'default_push' => false,
            'requires_module' => 'food',
        ],

        // Billing lifecycle — sent only to the family's billing owner. These are
        // transactional (failed payments, cancellations, downgrades), so default
        // is on and most users won't toggle this. Opt-out remains possible.
        'billing' => [
            'category' => 'billing',
            'label' => 'Уведомления об оплате и подписке',
            'description' => 'Неудачные платежи, окончание пробного периода, отмены',
            'channels' => ['email'],
            'default_email' => true,
            'default_push' => false,
        ],

    ],

];
