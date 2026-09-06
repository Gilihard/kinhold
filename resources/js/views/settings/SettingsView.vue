<template>
  <div class="p-4 md:p-6 max-w-4xl">
    <!-- Header -->
    <h1 class="text-2xl font-bold font-heading text-ink-primary mb-6">{{ isParent ? 'Настройки семьи' : 'Мои настройки' }}</h1>

    <!-- Kid-friendly Profile Section (shown for non-parent users) -->
    <div v-if="!isParent" class="card-lg mb-6">
      <h2 class="text-lg font-semibold font-heading text-ink-primary mb-4">Мой профиль</h2>
      <div class="flex items-center gap-4 p-4 bg-surface-sunken rounded-lg">
        <button class="flex-shrink-0 rounded-full hover:ring-2 hover:ring-[#C4975A] hover:ring-offset-2 dark:hover:ring-offset-surface-sunken transition-all" title="Сменить аватар" @click="openAvatarEditor(currentUser)">
          <UserAvatar :user="currentUser" size="lg" />
        </button>
        <div>
          <p class="text-lg font-semibold text-ink-primary">{{ currentUser?.name }}</p>
          <p v-if="currentUser?.email" class="text-sm text-ink-secondary">{{ currentUser?.email }}</p>
          <p v-if="family" class="text-sm text-ink-secondary mt-1">{{ family?.name }}</p>
        </div>
      </div>
    </div>

    <!-- ============================================ -->
    <!-- PARENT VIEW — Collapsible Sections           -->
    <!-- ============================================ -->
    <template v-if="isParent">
      <!-- GDPR mode: paywalled users land here with `?gdpr=1` from the
           SubscriptionPaywall escape hatch. Hide everything except the export
           and danger sections so they can't bypass the paywall via Settings. -->
      <div v-if="gdprMode" class="card-lg mb-6 p-4 bg-surface-sunken">
        <p class="text-sm font-medium text-ink-primary mb-1">Управление аккаунтом</p>
        <p class="text-xs text-ink-secondary">
          Ваша подписка закончилась. Вы всё ещё можете выгрузить копию своих данных или удалить здесь свой аккаунт.
        </p>
      </div>

      <template v-if="!gdprMode">
        <!-- Section 1: Family -->
        <SettingsSection
          id="family"
          title="Семья"
          description="Управляйте информацией о семье, участниках и приглашениях"
          :icon="UsersIcon"
          :model-value="expandedSections.has('family')"
          @update:model-value="val => toggleSection('family', val)"
        >
          <!-- Family Name -->
          <form class="space-y-4 mb-6" @submit.prevent="updateFamily">
            <BaseInput
              v-model="familyForm.name"
              label="Название семьи"
              placeholder="Ивановы"
              :error="familyErrors.name"
            />
            <div class="flex gap-3 justify-end">
              <BaseButton variant="ghost" @click="cancelEditFamily">Отмена</BaseButton>
              <BaseButton variant="primary" :loading="savingFamily">Сохранить изменения</BaseButton>
            </div>
          </form>

          <!-- Invite Code -->
          <div class="border-t border-border-subtle pt-4 mb-6">
            <h3 class="font-semibold text-ink-primary mb-2">Код приглашения семьи</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Поделитесь этим кодом с членами семьи, чтобы они могли присоединиться при регистрации.
            </p>

            <div class="flex items-center gap-3">
              <div class="flex-1 px-4 py-3 bg-surface-sunken rounded-lg font-mono text-lg tracking-widest text-ink-primary text-center">
                {{ inviteCode || '...' }}
              </div>
              <BaseButton variant="secondary" size="sm" :disabled="!inviteCode" @click="copyInviteCode">
                <ClipboardDocumentIcon class="w-4 h-4 mr-1" />
                {{ copied ? 'Скопировано!' : 'Копировать' }}
              </BaseButton>
            </div>

            <!-- Send invite by email -->
            <div class="mt-4 pt-4 border-t border-border-subtle">
              <p class="text-sm font-medium text-ink-secondary mb-2">Отправить приглашение по эл. почте</p>
              <form class="flex items-end gap-3" @submit.prevent="handleSendInviteEmail">
                <div class="flex-1">
                  <KinInput
                    v-model="inviteEmail"
                    type="email"
                    placeholder="name@example.com"
                    required
                  />
                </div>
                <BaseButton variant="secondary" size="sm" :loading="sendingInvite" :disabled="!inviteCode">
                  <EnvelopeIcon class="w-4 h-4 mr-1" />
                  Отправить
                </BaseButton>
              </form>
              <p v-if="inviteEmailSent" class="text-sm text-status-success dark:text-status-success mt-2">
                Приглашение отправлено!
              </p>
            </div>
          </div>

          <!-- Family Members -->
          <div class="border-t border-border-subtle pt-4 mb-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-ink-primary">Участники семьи</h3>
              <BaseButton variant="secondary" size="sm" @click="openAddMemberModal">
                <PlusIcon class="w-4 h-4 mr-2" />
                Добавить участника
              </BaseButton>
            </div>

            <div class="space-y-3">
              <div
                v-for="member in familyMembers"
                :key="member.id"
                class="flex items-center justify-between p-4 bg-surface-sunken rounded-lg"
              >
                <div class="flex items-center gap-3">
                  <button
                    class="flex-shrink-0 rounded-full hover:ring-2 hover:ring-[#C4975A] hover:ring-offset-2 dark:hover:ring-offset-surface-sunken transition-all"
                    title="Сменить аватар"
                    @click="openAvatarEditor(member)"
                  >
                    <UserAvatar :user="member" size="md" />
                  </button>
                  <div>
                    <p class="font-semibold text-ink-primary">{{ member.name }}</p>
                    <p v-if="member.email" class="text-xs text-ink-secondary">{{ member.email }}</p>
                    <p v-else class="text-xs text-ink-secondary italic">Управляемый аккаунт</p>
                    <div class="flex items-center gap-2 mt-1">
                      <span
                        :class="[
                          'text-xs px-2 py-0.5 rounded-full font-medium',
                          member.family_role === 'parent' || member.role === 'parent'
                            ? 'bg-accent-lavender-soft/40 text-accent-lavender-bold dark:bg-accent-lavender-soft/40 dark:text-accent-lavender-bold'
                            : 'bg-surface-sunken text-ink-secondary dark:bg-surface-overlay dark:text-ink-tertiary'
                        ]"
                      >
                        {{ (member.family_role || member.role) === 'parent' ? 'Родитель' : 'Ребёнок' }}
                      </span>
                      <span v-if="member.is_managed" class="text-xs px-2 py-0.5 rounded-full bg-accent-peach-soft/60 text-accent-peach-bold font-medium">
                        Управляемый
                      </span>
                    </div>
                  </div>
                </div>

                <div v-if="member.id !== currentUser?.id" class="flex items-center gap-1">
                  <button
                    v-if="member.is_managed"
                    class="p-2 hover:bg-accent-lavender-soft/40 rounded-lg transition-colors"
                    title="Переключиться на этот профиль"
                    @click="openSwitchToModal(member)"
                  >
                    <ArrowsRightLeftIcon class="w-4 h-4 text-accent-lavender-bold" />
                  </button>
                  <button
                    class="p-2 hover:bg-surface-overlay rounded-lg transition-colors"
                    title="Изменить участника"
                    @click="openEditMemberModal(member)"
                  >
                    <PencilIcon class="w-4 h-4 text-ink-secondary dark:text-ink-tertiary" />
                  </button>
                  <button
                    class="p-2 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    title="Удалить участника"
                    @click="confirmRemoveMember(member)"
                  >
                    <TrashIcon class="w-4 h-4 text-status-failed" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Setup Wizard (relocated here from bottom) -->
          <div class="border-t border-border-subtle pt-4">
            <h3 class="font-semibold text-ink-primary mb-2">Мастер настройки</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Повторно запустите мастер настройки, чтобы пригласить участников, подключить календари или настроить функции.
            </p>
            <BaseButton variant="secondary" @click="$router.push({ name: 'Onboarding' })">
              Повторно запустить мастер настройки
            </BaseButton>
          </div>
        </SettingsSection>

        <!-- Section 2: Tasks & Points -->
        <SettingsSection
          v-if="moduleToggles.tasks || moduleToggles.points"
          id="tasks-points"
          title="Задачи и баллы"
          description="Настройте поведение задач, баллы и награды"
          :icon="ClipboardDocumentListIcon"
          :model-value="expandedSections.has('tasks-points')"
          @update:model-value="val => toggleSection('tasks-points', val)"
        >
          <!-- Tasks module access -->
          <div class="mb-6">
            <div
              v-for="module in tasksPointsModules"
              :key="module.id"
              class="p-4 bg-surface-sunken rounded-lg mb-3"
            >
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div>
                  <p class="font-medium text-ink-primary">{{ module.name }}</p>
                  <p class="text-xs text-ink-secondary">{{ module.description }}</p>
                </div>
                <div class="flex gap-1.5 shrink-0">
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'all' ? 'bg-accent-lavender-bold text-ink-inverse shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'all')"
                  >
                    Все
                  </button>
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'roles' ? 'bg-accent-lavender-bold text-ink-inverse shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'roles', ['parent'])"
                  >
                    Только родители
                  </button>
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'off' ? 'bg-status-failed text-white shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'off')"
                  >
                    Выкл.
                  </button>
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'users' ? 'bg-accent-lavender-bold text-ink-inverse shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'users', getSelectedUserIds(module.id))"
                  >
                    Выборочно
                  </button>
                </div>
              </div>

              <!-- Per-member checkboxes -->
              <div v-if="moduleAccessState[module.id]?.mode === 'users'" class="mt-3 pt-3 border-t border-border-subtle dark:border-border-subtle">
                <p class="text-xs font-medium text-ink-secondary mb-2">Выберите участников семьи:</p>
                <div class="flex flex-wrap gap-2">
                  <label
                    v-for="member in familyMembers"
                    :key="member.id"
                    class="flex items-center gap-2 px-3 py-2 bg-surface-raised rounded-lg cursor-pointer hover:bg-surface-overlay transition-colors"
                  >
                    <input type="checkbox" :checked="isMemberSelected(module.id, member.id)" class="rounded" :disabled="(member.family_role || member.role) === 'parent'" @change="toggleMemberAccess(module.id, member.id)" />
                    <UserAvatar :user="member" size="xs" />
                    <span class="text-sm text-ink-primary">{{ member.name }}</span>
                    <span v-if="(member.family_role || member.role) === 'parent'" class="text-xs text-ink-tertiary italic">(всегда)</span>
                  </label>
                </div>
              </div>

              <p class="text-xs text-ink-primary mt-2">
                <template v-if="moduleAccessState[module.id]?.mode === 'all'">Все члены семьи могут пользоваться этой функцией.</template>
                <template v-else-if="moduleAccessState[module.id]?.mode === 'off'">Эта функция отключена для всех.</template>
                <template v-else-if="moduleAccessState[module.id]?.mode === 'roles'">Только родители могут пользоваться этой функцией.</template>
                <template v-else-if="moduleAccessState[module.id]?.mode === 'users'">{{ getSelectedMemberNames(module.id) || 'Никто не выбран (у родителей доступ есть всегда).' }}</template>
              </p>
            </div>
          </div>

          <!-- Leaderboard Period -->
          <div v-if="moduleToggles.points" class="border-t border-border-subtle pt-4 mb-4">
            <label class="block text-sm font-medium text-ink-secondary mb-2">
              Период сброса рейтинга
            </label>
            <KinSelect
              v-model="leaderboardPeriod"
              class="w-full max-w-xs"
              :options="leaderboardPeriodOptions"
            />
            <p class="text-xs text-ink-secondary mt-1">
              Как часто сбрасывается рейтинг. На баллы это не влияет.
            </p>

            <!-- Kudos Cost Toggle -->
            <div class="mt-4">
              <div class="flex items-center justify-between p-4 bg-surface-sunken rounded-lg gap-4">
                <div class="flex-1">
                  <p class="font-medium text-ink-primary">Похвала стоит баллов</p>
                  <p class="text-xs text-ink-secondary mt-0.5">Отправка похвалы списывает 1 балл со счёта отправителя. Не даёт обмениваться похвалой в обе стороны.</p>
                </div>
                <KinSwitch
                  :model-value="kudosCostEnabled"
                  color="lavender"
                  @update:model-value="kudosCostEnabled = $event"
                />
              </div>
            </div>
          </div>

          <!-- Default Task Points -->
          <div v-if="moduleToggles.tasks && moduleToggles.points" class="border-t border-border-subtle pt-4 mb-4">
            <h3 class="font-semibold text-ink-primary mb-2">Баллы по умолчанию за задачи</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Укажите, сколько баллов начисляется по умолчанию за каждый уровень приоритета. Задачи с явно заданными баллами не затрагиваются.
            </p>

            <div class="space-y-3">
              <div class="flex items-center gap-4 p-4 bg-surface-sunken rounded-lg">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-ink-secondary">Низкий приоритет</label>
                </div>
                <KinInput v-model.number="defaultPoints.low" type="number" min="0" max="1000" class="w-24 text-center" />
              </div>
              <div class="flex items-center gap-4 p-4 bg-surface-sunken rounded-lg">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-ink-secondary">Средний приоритет</label>
                </div>
                <KinInput v-model.number="defaultPoints.medium" type="number" min="0" max="1000" class="w-24 text-center" />
              </div>
              <div class="flex items-center gap-4 p-4 bg-surface-sunken rounded-lg">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-ink-secondary">Высокий приоритет</label>
                </div>
                <KinInput v-model.number="defaultPoints.high" type="number" min="0" max="1000" class="w-24 text-center" />
              </div>
            </div>
          </div>

          <!-- Task Assignment -->
          <div v-if="moduleToggles.tasks" class="border-t border-border-subtle pt-4 mb-4">
            <h3 class="font-semibold text-ink-primary mb-2">Назначение задач</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Управляйте тем, кто из членов семьи может назначать задачи другим. Родители могут назначать задачи кому угодно.
            </p>

            <div class="space-y-3">
              <label
                v-for="option in taskAssignmentOptions"
                :key="option.value"
                class="flex items-start gap-3 p-4 bg-surface-sunken rounded-lg cursor-pointer hover:bg-surface-overlay transition-colors"
              >
                <input v-model="taskAssignment.mode" type="radio" :value="option.value" name="task_assignment_mode" class="mt-0.5 rounded-full" />
                <div class="flex-1">
                  <p class="font-medium text-ink-primary">{{ option.label }}</p>
                  <p class="text-xs text-ink-secondary">{{ option.description }}</p>
                </div>
              </label>
            </div>

            <div v-if="taskAssignment.mode === 'users'" class="mt-4 pl-4 border-l-2 border-accent-lavender-soft">
              <p class="text-sm font-medium text-ink-secondary mb-3">Выберите, какие дети могут назначать задачи другим:</p>
              <div class="space-y-2">
                <label
                  v-for="child in childMembers"
                  :key="child.id"
                  class="flex items-center gap-3 p-3 bg-surface-sunken rounded-lg cursor-pointer hover:bg-surface-overlay transition-colors"
                >
                  <input v-model="taskAssignment.users" type="checkbox" :value="child.id" class="rounded" />
                  <span class="text-sm font-medium text-ink-primary">{{ child.name }}</span>
                </label>
                <p v-if="childMembers.length === 0" class="text-sm text-ink-secondary italic">
                  В семье пока нет детей.
                </p>
              </div>
            </div>
          </div>

          <!-- Save button -->
          <div class="flex justify-end pt-4 border-t border-border-subtle">
            <BaseButton variant="primary" :loading="savingTasksPoints" @click="saveTasksPointsSection">
              Сохранить изменения
            </BaseButton>
          </div>
        </SettingsSection>

        <!-- Section 3: AI & Integrations -->
        <SettingsSection
          id="ai-integrations"
          title="ИИ и интеграции"
          description="API-ключи, подключения календарей и MCP"
          :icon="CpuChipIcon"
          :model-value="expandedSections.has('ai-integrations')"
          @update:model-value="val => toggleSection('ai-integrations', val)"
        >
          <!-- AI Mode Toggle -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-ink-secondary mb-3">
              Доступ к ИИ
            </label>

            <!-- Two-tab selector — hidden on self-hosted (BYOK is the only option) -->
            <div v-if="billingEnabled" class="grid grid-cols-2 gap-3 mb-5">
              <!-- Kinhold AI tab -->
              <button
                :class="[
                  'relative flex flex-col items-start p-4 rounded-xl border-2 transition-all duration-200 text-left',
                  aiMode === 'kinhold'
                    ? 'border-accent-lavender-bold ring-2 ring-accent-lavender-bold/20 bg-surface-sunken dark:bg-surface-raised'
                    : 'border-border-subtle hover:border-border-strong bg-surface-raised/50',
                ]"
                @click="aiMode = 'kinhold'"
              >
                <div v-if="aiMode === 'kinhold'" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-accent-lavender-bold flex items-center justify-center">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div class="w-8 h-8 rounded-lg bg-accent-lavender-soft/40 flex items-center justify-center mb-2">
                  <svg class="w-4 h-4 text-accent-lavender-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <p class="text-sm font-semibold text-ink-primary">Использовать Kinhold AI</p>
                <p class="text-xs text-ink-secondary mt-0.5">На базе Claude · Входит в пробную версию</p>
              </button>

              <!-- BYOK tab -->
              <button
                :class="[
                  'relative flex flex-col items-start p-4 rounded-xl border-2 transition-all duration-200 text-left',
                  aiMode === 'byok'
                    ? 'border-accent-lavender-bold ring-2 ring-accent-lavender-bold/20 bg-surface-sunken dark:bg-surface-raised'
                    : 'border-border-subtle hover:border-border-strong bg-surface-raised/50',
                ]"
                @click="aiMode = 'byok'"
              >
                <div v-if="aiMode === 'byok'" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-accent-lavender-bold flex items-center justify-center">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div class="w-8 h-8 rounded-lg bg-golden-100 dark:bg-golden-900/40 flex items-center justify-center mb-2">
                  <svg class="w-4 h-4 text-golden-600 dark:text-golden-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                  </svg>
                </div>
                <p class="text-sm font-semibold text-ink-primary">Мой собственный API-ключ</p>
                <p class="text-xs text-ink-secondary mt-0.5">Anthropic Claude (остальные скоро появятся)</p>
              </button>
            </div>

            <!-- Kinhold AI panel — billing-only -->
            <div v-if="billingEnabled && aiMode === 'kinhold'" class="p-4 bg-accent-lavender-soft/30 border border-accent-lavender-soft rounded-lg">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-accent-lavender-bold mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <p class="text-sm font-medium text-accent-lavender-bold">Всё готово</p>
                  <p class="text-xs text-accent-lavender-bold mt-0.5">
                    Kinhold AI работает на базе Claude от Anthropic. API-ключ не нужен — мы обо всём позаботимся. Входит в вашу 14-дневную пробную версию бесплатно; чтобы продолжать пользоваться после неё, выберите платный тариф ИИ в разделе «Оплата».
                  </p>
                </div>
              </div>
            </div>

            <!-- BYOK panel — always shown on self-hosted -->
            <div v-if="!billingEnabled || aiMode === 'byok'" class="space-y-4">
              <!-- Security reassurance — keys are encrypted at rest with AES-256
                 (Laravel encrypt() / APP_KEY) and only decrypted in-memory at
                 the moment a chat request is made. We never log or display
                 raw keys; the UI only ever shows a masked preview. -->
              <div class="flex items-start gap-2.5 p-3 rounded-lg bg-status-success-soft/40 dark:bg-status-success-soft/20 border border-status-success-soft">
                <svg class="w-4 h-4 mt-0.5 shrink-0 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div class="text-xs text-ink-secondary leading-relaxed">
                  <span class="font-semibold text-ink-primary">Ваш ключ остаётся приватным.</span>
                  Он шифруется с помощью AES-256 до записи в базу данных, расшифровывается только в памяти в момент запроса в чате и никогда не сохраняется в журналах и не показывается вам открытым текстом — только в виде маскированного предпросмотра.
                </div>
              </div>

              <!-- Provider selection — single column when only one provider is
                 available (avoids 1-of-3 dead-space layout per #201) -->
              <div :class="['grid gap-3', aiProviders.length > 1 ? 'grid-cols-1 sm:grid-cols-3' : 'grid-cols-1']">
                <button
                  v-for="provider in aiProviders"
                  :key="provider.slug"
                  :class="[
                    'relative flex flex-col items-center p-3 rounded-xl border-2 transition-all duration-200 text-center',
                    aiConfig.provider === provider.slug
                      ? 'border-accent-lavender-bold ring-2 ring-accent-lavender-bold/20 bg-surface-sunken dark:bg-surface-raised'
                      : 'border-border-subtle hover:border-border-strong bg-surface-raised/50',
                  ]"
                  @click="selectAiProvider(provider.slug)"
                >
                  <div class="w-8 h-8 mb-1.5 flex items-center justify-center rounded-lg" :class="providerIconClass(provider.slug)">
                    <span class="text-base font-bold">{{ providerIcon(provider.slug) }}</span>
                  </div>
                  <p class="text-xs font-semibold text-ink-primary">{{ provider.name }}</p>
                </button>
              </div>

              <!-- API Key -->
              <div>
                <label class="block text-sm font-medium text-ink-secondary mb-1.5">
                  {{ selectedProviderName }} API-ключ
                </label>
                <div class="relative">
                  <KinInput
                    v-model="aiConfig.apiKey"
                    :type="showAiKey ? 'text' : 'password'"
                    :placeholder="selectedProviderPlaceholder"
                  />
                  <button
                    type="button" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 text-xs font-medium text-ink-secondary hover:text-ink-primary transition-colors z-10"
                    @click="showAiKey = !showAiKey"
                  >
                    {{ showAiKey ? 'Скрыть' : 'Показать' }}
                  </button>
                </div>
                <div class="flex items-center justify-between mt-1">
                  <p class="text-xs text-ink-secondary">
                    <template v-if="aiConfig.hasSavedKey && !aiConfig.apiKey">
                      Текущий ключ: <span class="font-mono">{{ aiConfig.maskedKey }}</span>
                    </template>
                    <template v-else>
                      Найдите свой ключ на <a href="https://console.anthropic.com" target="_blank" rel="noopener noreferrer" class="text-accent-lavender-bold hover:underline">console.anthropic.com</a>. Мы шифруем его при хранении и никогда не видим ваш ключ в открытом виде.
                    </template>
                  </p>
                  <a
                    :href="selectedProviderHelpUrl" target="_blank" rel="noopener noreferrer"
                    class="text-xs text-accent-lavender-bold hover:underline whitespace-nowrap ml-2"
                  >
                    Получить API-ключ →
                  </a>
                </div>

                <!-- Inline test-key status -->
                <div
                  v-if="aiTestStatus"
                  class="mt-2 flex items-start gap-2 text-xs"
                  :class="aiTestStatus.valid ? 'text-status-success' : 'text-status-danger'"
                  role="status"
                >
                  <svg v-if="aiTestStatus.valid" class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span>{{ aiTestStatus.valid ? 'Ключ действителен.' : aiTestStatus.error }}</span>
                </div>
              </div>

              <!-- Model override -->
              <div>
                <label class="block text-sm font-medium text-ink-secondary mb-1.5">
                  Переопределение модели <span class="font-normal text-ink-tertiary">(необязательно)</span>
                </label>
                <KinInput v-model="aiConfig.model" type="text" :placeholder="selectedProviderDefaultModel" />
                <p class="text-xs text-ink-secondary mt-1">
                  Оставьте пустым, чтобы использовать {{ selectedProviderDefaultModel }}.
                </p>
              </div>
            </div>

            <div class="flex flex-wrap gap-3 justify-end pt-4">
              <BaseButton
                v-if="(!billingEnabled || aiMode === 'byok') && aiConfig.hasSavedKey"
                variant="ghost"
                :loading="clearingAi"
                @click="showClearKeyModal = true"
              >
                Очистить сохранённый ключ
              </BaseButton>
              <BaseButton
                v-if="!billingEnabled || aiMode === 'byok'"
                variant="ghost"
                :disabled="!aiConfig.apiKey"
                :loading="testingAi"
                @click="testAiKey"
              >
                Проверить ключ
              </BaseButton>
              <BaseButton variant="ghost" @click="resetAiConfig">Сбросить</BaseButton>
              <BaseButton variant="primary" :loading="savingAi" @click="saveAiSettings">Сохранить настройки ИИ</BaseButton>
            </div>
          </div>

          <!-- Connect AI Assistant (MCP Token) -->
          <div class="border-t border-border-subtle pt-4 mb-6">
            <h3 class="font-semibold text-ink-primary mb-2">Подключить ИИ-ассистента</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Подключите любого совместимого с MCP ИИ-ассистента для управления семейным центром.
            </p>

            <!-- OAuth quick-connect info -->
            <div class="p-4 bg-accent-lavender-soft/30 border border-accent-lavender-soft rounded-lg mb-4">
              <p class="text-sm font-medium text-accent-lavender-bold mb-1">Быстрое подключение (Claude Desktop / ChatGPT)</p>
              <p class="text-xs text-accent-lavender-bold mb-2">
                Добавьте Kinhold как настраиваемый коннектор через OAuth — токен не нужен:
              </p>
              <div class="space-y-1 text-xs text-accent-lavender-bold dark:text-accent-lavender-bold">
                <div class="flex items-start gap-2">
                  <span class="font-medium min-w-[40px]">Название</span>
                  <code class="font-mono bg-accent-lavender-soft/40 px-1.5 py-0.5 rounded">Kinhold</code>
                </div>
                <div class="flex items-start gap-2">
                  <span class="font-medium min-w-[40px]">URL</span>
                  <code class="font-mono bg-accent-lavender-soft/40 px-1.5 py-0.5 rounded break-all">{{ mcpGenerated.mcpUrl || (appOrigin + '/mcp') }}</code>
                </div>
              </div>
              <p class="text-xs text-accent-lavender-bold dark:text-accent-lavender-bold mt-2">
                Оставьте поля OAuth пустыми. При подключении вам будет предложено войти через Google.
              </p>
            </div>

            <!-- Bearer token fallback (Claude Code / manual) -->
            <p class="text-xs text-ink-secondary mb-2 font-medium">Расширенно: Bearer-токен (для Claude Code / ручной настройки)</p>
            <div class="flex items-center justify-between p-4 bg-surface-sunken rounded-lg">
              <div>
                <div class="flex items-center gap-2">
                  <p class="font-medium text-ink-primary">MCP-подключение</p>
                  <span v-if="mcpToken.hasToken" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-status-success">
                    Подключено
                  </span>
                  <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-sunken text-ink-secondary dark:bg-surface-overlay dark:text-ink-tertiary">
                    Не подключено
                  </span>
                </div>
                <p v-if="mcpToken.lastUsedAt" class="text-xs text-ink-secondary mt-1">
                  Последнее использование: {{ new Date(mcpToken.lastUsedAt).toLocaleDateString() }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <BaseButton v-if="mcpToken.hasToken" variant="ghost" size="sm" :loading="mcpRevoking" @click="handleRevokeMcpToken">
                  Отозвать
                </BaseButton>
                <BaseButton variant="secondary" size="sm" :loading="mcpGenerating" @click="handleGenerateMcpToken">
                  {{ mcpToken.hasToken ? 'Пересоздать токен' : 'Создать токен' }}
                </BaseButton>
              </div>
            </div>

            <!-- One-time token display -->
            <div v-if="mcpGenerated.show" class="mt-4 space-y-4">
              <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">
                  Этот токен показывается только один раз. Скопируйте всё нужное сейчас — увидеть его снова будет нельзя.
                </p>
              </div>

              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-sm font-medium text-ink-secondary">Bearer-токен</label>
                  <BaseButton variant="ghost" size="sm" @click="copyMcpSnippet('token', mcpGenerated.plainToken)">
                    <ClipboardDocumentIcon class="w-4 h-4 mr-1" />
                    {{ mcpCopied.token ? 'Скопировано!' : 'Копировать' }}
                  </BaseButton>
                </div>
                <div class="font-mono text-sm bg-surface-raised dark:bg-surface-app text-status-success p-3 rounded-lg overflow-x-auto">
                  {{ mcpGenerated.plainToken }}
                </div>
              </div>

              <div>
                <div class="flex flex-wrap gap-1 border-b border-border-subtle mb-3">
                  <button
                    v-for="client in mcpGenerated.clients"
                    :key="client.id"
                    :class="[
                      'px-3 py-1.5 text-sm font-medium rounded-t-lg transition-colors -mb-px',
                      mcpActiveClient === client.id
                        ? 'border border-b-surface-raised border-border-subtle text-ink-primary bg-surface-raised'
                        : 'text-ink-secondary hover:text-ink-primary',
                    ]"
                    @click="mcpActiveClient = client.id"
                  >
                    {{ client.name }}
                  </button>
                </div>

                <div v-for="client in mcpGenerated.clients" v-show="mcpActiveClient === client.id" :key="client.id">
                  <p class="text-xs text-ink-secondary mb-2">{{ client.instructions }}</p>

                  <template v-if="client.steps">
                    <ol class="list-decimal list-inside space-y-2 text-sm text-ink-primary mb-3">
                      <li v-for="(step, i) in client.steps" :key="i" class="leading-relaxed">{{ step }}</li>
                    </ol>
                  </template>

                  <template v-else-if="client.details">
                    <div class="space-y-2 mb-2">
                      <div v-for="(value, label) in client.details" :key="label" class="flex items-start gap-2">
                        <span class="text-xs font-medium text-ink-secondary uppercase min-w-[80px]">{{ label.replace('_', ' ') }}</span>
                        <code class="text-sm font-mono text-ink-primary break-all">{{ value }}</code>
                      </div>
                    </div>
                    <BaseButton variant="ghost" size="sm" @click="copyMcpSnippet(client.id, Object.entries(client.details).map(([k, v]) => k + ': ' + v).join('\n'))">
                      <ClipboardDocumentIcon class="w-4 h-4 mr-1" />
                      {{ mcpCopied[client.id] ? 'Скопировано!' : 'Копировать данные' }}
                    </BaseButton>
                  </template>

                  <template v-else>
                    <div class="flex items-center justify-end mb-1">
                      <BaseButton variant="ghost" size="sm" @click="copyMcpSnippet(client.id, client.command || client.configJson)">
                        <ClipboardDocumentIcon class="w-4 h-4 mr-1" />
                        {{ mcpCopied[client.id] ? 'Скопировано!' : 'Копировать' }}
                      </BaseButton>
                    </div>
                    <pre class="font-mono text-sm bg-surface-raised dark:bg-surface-app text-ink-secondary p-3 rounded-lg overflow-x-auto whitespace-pre">{{ client.command || client.configJson }}</pre>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- Google Account Linking -->
          <div class="border-t border-border-subtle pt-4 mb-6">
            <h3 class="font-semibold text-ink-primary mb-3">Аккаунт Google</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Привяжите аккаунт Google для быстрого входа. Эл. почта Google должна совпадать с эл. почтой вашего аккаунта.
            </p>

            <div v-if="currentUser?.google_id" class="flex items-center justify-between p-3 bg-status-success/10 rounded-lg">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-status-success dark:text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span class="text-sm font-medium text-green-800 dark:text-green-300">Аккаунт Google привязан</span>
              </div>
              <BaseButton variant="ghost" size="sm" :loading="unlinkingGoogle" @click="handleUnlinkGoogle">Отвязать</BaseButton>
            </div>

            <div v-else class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg">
              <div>
                <p class="text-sm font-medium text-ink-primary">Не привязан</p>
                <p class="text-xs text-ink-secondary mt-0.5">Входите быстрее с помощью Google</p>
              </div>
              <BaseButton variant="secondary" size="sm" :loading="linkingGoogle" @click="handleLinkGoogle">Привязать Google</BaseButton>
            </div>

            <div v-if="googleLinkError" class="mt-2 p-2 bg-status-failed/10 border border-status-failed/30 rounded-lg">
              <p class="text-xs text-status-failed">{{ googleLinkError }}</p>
            </div>
            <div v-if="googleLinked" class="mt-2 p-2 bg-status-success/10 border border-green-200 dark:border-green-800 rounded-lg">
              <p class="text-xs text-status-success dark:text-green-300">Аккаунт Google успешно привязан!</p>
            </div>
          </div>

          <!-- Google Calendar -->
          <div class="border-t border-border-subtle pt-4 mb-6">
            <h3 class="font-semibold text-ink-primary mb-3">Синхронизация с Google Календарём</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Подключите свой Google Календарь, чтобы синхронизировать события в семейный центр.
            </p>

            <!-- Google OAuth not configured notice -->
            <div v-if="authStore.services && !authStore.services.google_calendar" class="p-4 bg-golden-50 dark:bg-golden-900/20 rounded-xl mb-4">
              <p class="text-sm font-medium text-golden-800 dark:text-golden-300 mb-1">Google Календарь не настроен</p>
              <p class="text-xs text-golden-700 dark:text-golden-400">
                На этом сервере не настроены учётные данные Google OAuth. Вы по-прежнему можете создавать события вручную на странице календаря.
                <template v-if="authStore.isParent"> Задайте <code class="bg-golden-100 dark:bg-golden-900/40 px-1 rounded">GOOGLE_CLIENT_ID</code> и <code class="bg-golden-100 dark:bg-golden-900/40 px-1 rounded">GOOGLE_CLIENT_SECRET</code> в переменных окружения, чтобы включить синхронизацию с Google Календарём.</template>
              </p>
            </div>

            <div v-else class="space-y-2">
              <div
                v-for="conn in userCalendarConnections"
                :key="conn.id"
                class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg"
              >
                <div>
                  <p class="font-medium text-ink-primary">{{ conn.calendar_name || 'Google Календарь' }}</p>
                  <p class="text-xs text-ink-secondary mt-0.5">{{ currentUser?.name }}</p>
                </div>
                <BaseButton variant="ghost" size="sm" @click="handleDisconnectCalendar(conn.id)">Отключить</BaseButton>
              </div>

              <div v-if="userCalendarConnections.length === 0" class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg">
                <div>
                  <p class="font-medium text-ink-primary">{{ currentUser?.name }}</p>
                  <p class="text-xs text-ink-secondary mt-0.5">
                    <span class="badge badge-warning">Не подключено</span>
                  </p>
                </div>
                <BaseButton variant="secondary" size="sm" :loading="connectingCalendar" @click="handleConnectCalendar">Подключить</BaseButton>
              </div>

              <div v-if="userCalendarConnections.length > 0" class="flex justify-end">
                <BaseButton variant="secondary" size="sm" :loading="connectingCalendar" @click="handleConnectCalendar">
                  Переподключить / Добавить календари
                </BaseButton>
              </div>

              <div
                v-for="conn in otherMemberConnections"
                :key="conn.id"
                class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg"
              >
                <div>
                  <p class="font-medium text-ink-primary">{{ conn.calendar_name || 'Google Календарь' }}</p>
                  <p class="text-xs text-ink-secondary mt-0.5">{{ conn.user?.name || 'Член семьи' }}</p>
                </div>
              </div>
            </div>

            <div v-if="calendarError" class="mt-3 p-3 bg-status-failed/10 border border-status-failed/30 rounded-lg">
              <p class="text-sm text-status-failed">{{ calendarError }}</p>
            </div>
          </div>

          <!-- ICS URL Subscription -->
          <div class="border-t border-border-subtle pt-4">
            <h3 class="font-semibold text-ink-primary mb-3">Подписка по URL</h3>
            <p class="text-sm text-ink-secondary mb-4">
              Добавьте календарь, вставив URL его ICS-ленты (работает с любой ссылкой на .ics-календарь).
            </p>

            <form class="space-y-3" @submit.prevent="handleSubscribeUrl">
              <div>
                <label class="block text-sm font-medium text-ink-secondary mb-1">URL календаря</label>
                <KinInput v-model="icsForm.url" type="url" placeholder="https://example.com/calendar.ics" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-ink-secondary mb-1">Название календаря (необязательно)</label>
                <KinInput v-model="icsForm.name" type="text" placeholder="Мой календарь" />
                <p class="text-xs text-ink-secondary mt-1">Если оставить пустым, название определится автоматически из данных календаря.</p>
              </div>
              <div class="flex justify-end">
                <BaseButton variant="secondary" size="sm" :loading="subscribingUrl">Подписаться</BaseButton>
              </div>
            </form>

            <div v-if="icsError" class="mt-3 p-3 bg-status-failed/10 border border-status-failed/30 rounded-lg">
              <p class="text-sm text-status-failed">{{ icsError }}</p>
            </div>

            <div v-if="icsConnections.length > 0" class="mt-4 space-y-2">
              <p class="text-sm font-medium text-ink-secondary">Календари по подписке</p>
              <div
                v-for="conn in icsConnections"
                :key="conn.id"
                class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg"
              >
                <div>
                  <p class="font-medium text-ink-primary">{{ conn.calendar_name || 'ICS-календарь' }}</p>
                  <p class="text-xs text-ink-secondary mt-0.5">Подписка по URL</p>
                </div>
                <BaseButton variant="ghost" size="sm" @click="handleDisconnectCalendar(conn.id)">Отписаться</BaseButton>
              </div>
            </div>
          </div>
        </SettingsSection>

        <!-- Section 4: Feature Access -->
        <SettingsSection
          id="feature-access"
          title="Доступ к функциям"
          description="Управляйте тем, к каким функциям имеет доступ каждый член семьи"
          :icon="ShieldCheckIcon"
          badge="Родитель"
          :model-value="expandedSections.has('feature-access')"
          @update:model-value="val => toggleSection('feature-access', val)"
        >
          <div class="space-y-4">
            <div
              v-for="module in otherModules"
              :key="module.id"
              class="p-4 bg-surface-sunken rounded-lg"
            >
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div>
                  <p class="font-medium text-ink-primary">{{ module.name }}</p>
                  <p class="text-xs text-ink-secondary">{{ module.description }}</p>
                </div>
                <div class="flex gap-1.5 shrink-0">
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'all' ? 'bg-accent-lavender-bold text-ink-inverse shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'all')"
                  >
                    Все
                  </button>
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'roles' ? 'bg-accent-lavender-bold text-ink-inverse shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'roles', ['parent'])"
                  >
                    Только родители
                  </button>
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'off' ? 'bg-status-failed text-white shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'off')"
                  >
                    Выкл.
                  </button>
                  <button
                    :class="['px-2.5 py-1 text-xs font-medium rounded-full transition-colors', moduleAccessState[module.id]?.mode === 'users' ? 'bg-accent-lavender-bold text-ink-inverse shadow-resting' : 'bg-surface-raised text-ink-secondary border border-border-subtle hover:bg-surface-overlay']"
                    @click="setModuleMode(module.id, 'users', getSelectedUserIds(module.id))"
                  >
                    Выборочно
                  </button>
                </div>
              </div>

              <div v-if="moduleAccessState[module.id]?.mode === 'users'" class="mt-3 pt-3 border-t border-border-subtle dark:border-border-subtle">
                <p class="text-xs font-medium text-ink-secondary mb-2">Выберите участников семьи:</p>
                <div class="flex flex-wrap gap-2">
                  <label
                    v-for="member in familyMembers"
                    :key="member.id"
                    class="flex items-center gap-2 px-3 py-2 bg-surface-raised rounded-lg cursor-pointer hover:bg-surface-overlay transition-colors"
                  >
                    <input type="checkbox" :checked="isMemberSelected(module.id, member.id)" class="rounded" :disabled="(member.family_role || member.role) === 'parent'" @change="toggleMemberAccess(module.id, member.id)" />
                    <UserAvatar :user="member" size="xs" />
                    <span class="text-sm text-ink-primary">{{ member.name }}</span>
                    <span v-if="(member.family_role || member.role) === 'parent'" class="text-xs text-ink-tertiary italic">(всегда)</span>
                  </label>
                </div>
              </div>

              <p class="text-xs text-ink-primary mt-2">
                <template v-if="moduleAccessState[module.id]?.mode === 'all'">Все члены семьи могут пользоваться этой функцией.</template>
                <template v-else-if="moduleAccessState[module.id]?.mode === 'off'">Эта функция отключена для всех.</template>
                <template v-else-if="moduleAccessState[module.id]?.mode === 'roles'">Только родители могут пользоваться этой функцией.</template>
                <template v-else-if="moduleAccessState[module.id]?.mode === 'users'">{{ getSelectedMemberNames(module.id) || 'Никто не выбран (у родителей доступ есть всегда).' }}</template>
              </p>
            </div>
          </div>

          <div class="flex gap-3 justify-end pt-4 border-t border-border-subtle mt-4">
            <BaseButton variant="primary" :loading="savingModules" @click="saveModuleSettings">
              Сохранить предпочтения
            </BaseButton>
          </div>
        </SettingsSection>

        <!-- Section 5: Food -->
        <SettingsSection
          id="food"
          title="Питание"
          description="Предпочтения планирования питания"
          :icon="FireIcon"
          :model-value="expandedSections.has('food')"
          @update:model-value="val => toggleSection('food', val)"
        >
          <div class="mb-4">
            <label class="block text-sm font-medium text-ink-secondary mb-2">
              Неделя начинается с
            </label>
            <KinSelect
              v-model="weekStartDay"
              class="w-full max-w-xs"
              :options="weekStartDayOptions"
            />
            <p class="text-xs text-ink-secondary mt-1">
              Определяет границы недели для календаря планировщика питания и еженедельного списка покупок.
            </p>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-ink-secondary mb-2">
              Отображаемые приёмы пищи
            </label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="slot in allMealSlots"
                :key="slot.key"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-medium transition-colors"
                :class="mealSlots.includes(slot.key)
                  ? 'bg-[#C4975A]/10 border-[#C4975A]/40 text-[#C4975A]'
                  : 'bg-surface-sunken border-border-subtle dark:border-border-subtle text-ink-tertiary hover:border-[#C4975A]/30'"
                @click="toggleMealSlot(slot.key)"
              >
                <component :is="slot.icon" class="w-3.5 h-3.5" />
                {{ slot.label }}
              </button>
            </div>
            <p class="text-xs text-ink-secondary mt-1">
              Выберите, какие приёмы пищи показывать в планере. Скрытые приёмы пищи сохраняют свои данные.
            </p>
          </div>
          <BaseButton variant="primary" size="sm" :loading="savingFood" @click="saveFoodSection">
            Сохранить
          </BaseButton>
        </SettingsSection>

        <!-- Section 5b: Allergens (food module only) -->
        <SettingsSection
          v-if="foodModuleEnabled"
          id="allergens"
          title="Аллергены"
          description="Аллергены семьи и профили аллергии участников"
          :icon="ShieldExclamationIcon"
          :model-value="expandedSections.has('allergens')"
          @update:model-value="val => toggleSection('allergens', val)"
        >
          <div class="space-y-8">
            <FamilyAllergenSettings />

            <div class="space-y-6 pt-2 border-t border-border-subtle">
              <div>
                <h3 class="text-base font-semibold text-ink-primary">Профили участников</h3>
                <p class="text-xs text-ink-secondary mt-1">
                  Отметьте аллергии каждого члена семьи, чтобы планировщик питания предупреждал вас, когда рецепт небезопасен.
                </p>
              </div>
              <div v-for="member in familyMembers" :key="member.id" class="p-4 bg-surface-sunken rounded-lg">
                <AllergyProfileEditor :user="member" :can-edit="true" />
              </div>
            </div>
          </div>
        </SettingsSection>

        <!-- Section 6: Appearance -->
        <SettingsSection
          id="appearance"
          title="Оформление"
          description="Тёмная тема и цветовые темы"
          :icon="SwatchIcon"
          :model-value="expandedSections.has('appearance')"
          @update:model-value="val => toggleSection('appearance', val)"
        >
          <!-- Dark Mode -->
          <div class="flex items-center justify-between p-4 bg-surface-sunken dark:bg-surface-raised rounded-lg">
            <div>
              <p class="font-medium text-ink-primary">Тёмная тема</p>
              <p class="text-xs text-ink-secondary mt-0.5">Переключайтесь между светлой и тёмной темами</p>
            </div>
            <ToggleSwitch :model-value="isDark" @update:model-value="toggleDarkMode">
              <template #thumb>
                <MoonIcon v-if="isDark" class="w-3.5 h-3.5 text-accent-lavender-bold" />
                <SunIcon v-else class="w-3.5 h-3.5 text-sand-500" />
              </template>
            </ToggleSwitch>
          </div>

          <!-- Color Theme Picker -->
          <div class="mt-4 pt-4 border-t border-border-subtle">
            <p class="font-medium text-ink-primary mb-1">Цветовая тема</p>
            <p class="text-xs text-ink-secondary mb-3">Выберите цветовую палитру приложения</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <button
                v-for="theme in availableThemes"
                :key="theme.id"
                :class="[
                  'relative flex flex-col p-3 rounded-xl border-2 transition-all duration-200 text-left',
                  currentTheme === theme.id
                    ? 'border-accent-lavender-bold ring-2 ring-accent-lavender-bold/20 dark:ring-accent-lavender-bold/20 bg-surface-sunken dark:bg-surface-raised'
                    : 'border-border-subtle hover:border-border-strong dark:hover:border-border-strong bg-surface-raised/50',
                ]"
                @click="selectTheme(theme.id)"
              >
                <div
                  v-if="currentTheme === theme.id"
                  class="absolute top-2 right-2 w-5 h-5 rounded-full bg-accent-lavender-bold flex items-center justify-center"
                >
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div class="flex gap-1.5 mb-2">
                  <span class="w-8 h-8 rounded-lg shadow-sm" :style="{ backgroundColor: theme.colors.primary }"></span>
                  <span class="w-8 h-8 rounded-lg shadow-sm" :style="{ backgroundColor: theme.colors.accent }"></span>
                  <span class="w-8 h-8 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600" :style="{ backgroundColor: theme.colors.surface }"></span>
                  <span class="w-8 h-8 rounded-lg shadow-sm" :style="{ backgroundColor: theme.colors.highlight }"></span>
                </div>
                <p class="text-sm font-semibold text-ink-primary">{{ theme.name }}</p>
                <p class="text-xs text-ink-secondary">{{ theme.description }}</p>
              </button>
            </div>
          </div>
        </SettingsSection>

        <!-- Section 6: Notifications -->
        <SettingsSection
          id="notifications"
          title="Уведомления"
          description="Push, эл. почта, тихие часы и настройки по типам уведомлений"
          :icon="BellIcon"
          :model-value="expandedSections.has('notifications')"
          @update:model-value="val => toggleSection('notifications', val)"
        >
          <NotificationsPanel />
        </SettingsSection>

        <!-- Section 6b: Billing (only visible to the family billing owner when BILLING_ENABLED) -->
        <SettingsSection
          v-if="canSeeBilling"
          id="billing"
          title="Оплата и подписка"
          description="Управление тарифом, способом оплаты и счетами"
          :icon="CreditCardIcon"
          :model-value="expandedSections.has('billing')"
          @update:model-value="val => toggleSection('billing', val)"
        >
          <BillingPanel />
        </SettingsSection>

        <!-- Section 7: About -->
        <SettingsSection
          id="about"
          title="О Kinhold"
          description="Информация о версии и обновлениях"
          :icon="InformationCircleIcon"
          :model-value="expandedSections.has('about')"
          @update:model-value="val => toggleSection('about', val)"
        >
          <!-- Update Available Banner -->
          <div
            v-if="updateAvailable && !updateDismissed"
            class="flex items-start gap-3 p-4 mb-4 bg-sand-50 dark:bg-sand-900/30 border border-sand-300 dark:border-sand-700 rounded-lg"
          >
            <div class="flex-1">
              <p class="font-semibold text-sand-800 dark:text-sand-200">
                Доступно обновление: v{{ updateAvailable.latest_version }}
              </p>
              <p class="text-sm text-sand-700 dark:text-sand-400 mt-1">
                У вас установлена версия v{{ appVersion }}. На GitHub доступна более новая версия.
              </p>
              <a
                :href="updateAvailable.url"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-sm font-medium text-accent-lavender-bold hover:underline mt-2"
              >
                Посмотреть заметки о выпуске
                <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
              </a>
            </div>
            <button
              class="p-1 text-sand-500 hover:text-sand-700 dark:text-sand-400 dark:hover:text-sand-200 rounded transition-colors"
              title="Скрыть"
              @click="dismissUpdate"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <!-- Version Info -->
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg">
              <span class="text-sm font-medium text-ink-primary">Версия</span>
              <span class="text-sm font-mono text-ink-secondary">v{{ appVersion }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg">
              <span class="text-sm font-medium text-ink-primary">Лицензия</span>
              <span class="text-sm text-ink-secondary">Elastic License 2.0</span>
            </div>
            <div class="flex items-center gap-4 pt-2">
              <a
                :href="`https://github.com/${githubRepo}`"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1.5 text-sm text-accent-lavender-bold hover:underline"
              >
                GitHub
                <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
              </a>
              <a
                :href="`https://github.com/${githubRepo}/releases`"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1.5 text-sm text-accent-lavender-bold hover:underline"
              >
                Заметки о выпуске
                <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
              </a>
              <a
                href="https://kinhold.app"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1.5 text-sm text-accent-lavender-bold hover:underline"
              >
                Сайт
                <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </SettingsSection>
      </template>

      <!-- Your Data (GDPR export) -->
      <SettingsSection
        id="your-data"
        title="Ваши данные"
        description="Скачайте копию своих данных"
        :icon="ArrowDownTrayIcon"
        :model-value="expandedSections.has('your-data')"
        @update:model-value="val => toggleSection('your-data', val)"
      >
        <div class="p-4 bg-surface-sunken rounded-lg">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="font-medium text-ink-primary">Выгрузить мои данные</p>
              <p class="text-sm text-ink-secondary mt-1">
                Скачайте ZIP-архив всего, что вы создали в Kinhold: задачи, записи сейфа, баллы, рецепты и многое другое.
              </p>
            </div>
            <BaseButton variant="primary" size="sm" :loading="exportingData" @click="openExportModal">
              Выгрузить мои данные
            </BaseButton>
          </div>
        </div>
      </SettingsSection>

      <!-- Section 8: Danger Zone -->
      <SettingsSection
        id="danger"
        title="Опасная зона"
        description="Необратимые действия — удаление аккаунта или семьи"
        :icon="ExclamationTriangleIcon"
        :model-value="expandedSections.has('danger')"
        @update:model-value="val => toggleSection('danger', val)"
      >
        <div class="space-y-4">
          <div class="p-4 border border-status-failed/30 rounded-lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="font-medium text-ink-primary">Удалить мой аккаунт</p>
                <p class="text-sm text-ink-secondary mt-1">
                  Навсегда удалить ваш аккаунт и все личные данные (задачи, записи сейфа, историю чатов).
                  <span v-if="managedChildrenNames.length"> Ваши управляемые дети ({{ managedChildrenNames.join(', ') }}) также будут удалены.</span>
                </p>
              </div>
              <BaseButton variant="danger" size="sm" @click="isDemoFamily ? showDemoDeletePopup = true : showDeleteAccountModal = true">
                Удалить аккаунт
              </BaseButton>
            </div>
          </div>

          <div class="p-4 border border-status-failed/30 rounded-lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="font-medium text-ink-primary">Удалить всю семью</p>
                <p class="text-sm text-ink-secondary mt-1">
                  Навсегда удалить семью <strong>{{ family?.name }}</strong>, всех участников и все общие данные. Это действие необратимо.
                </p>
              </div>
              <BaseButton variant="danger" size="sm" @click="isDemoFamily ? showDemoDeletePopup = true : showDeleteFamilyModal = true">
                Удалить семью
              </BaseButton>
            </div>
          </div>
        </div>
      </SettingsSection>
    </template>

    <!-- ============================================ -->
    <!-- CHILD VIEW — Flat cards (unchanged)          -->
    <!-- ============================================ -->
    <template v-if="!isParent">
      <!-- My Allergies (food module only) -->
      <div v-if="foodModuleEnabled" id="allergens" class="card-lg mb-6">
        <div class="flex items-center gap-2 mb-4">
          <ShieldExclamationIcon class="w-5 h-5 text-accent-lavender-bold" />
          <h2 class="text-lg font-semibold font-heading text-ink-primary">Мои аллергии</h2>
        </div>
        <AllergyProfileEditor :user="currentUser" :can-edit="true" />
      </div>

      <!-- Appearance -->
      <div class="card-lg mb-6">
        <h2 class="text-lg font-semibold font-heading text-ink-primary mb-4">Оформление</h2>
        <div class="flex items-center justify-between p-4 bg-surface-sunken dark:bg-surface-raised rounded-lg">
          <div>
            <p class="font-medium text-ink-primary">Тёмная тема</p>
            <p class="text-xs text-ink-secondary mt-0.5">Переключайтесь между светлой и тёмной темами</p>
          </div>
          <ToggleSwitch :model-value="isDark" @update:model-value="toggleDarkMode">
            <template #thumb>
              <MoonIcon v-if="isDark" class="w-3.5 h-3.5 text-accent-lavender-bold" />
              <SunIcon v-else class="w-3.5 h-3.5 text-sand-500" />
            </template>
          </ToggleSwitch>
        </div>
      </div>

      <!-- Email Notifications (if they have an email) -->
      <div class="card-lg mb-6">
        <div class="flex items-center gap-2 mb-4">
          <BellIcon class="w-5 h-5 text-accent-lavender-bold" />
          <h2 class="text-lg font-semibold font-heading text-ink-primary">Уведомления</h2>
        </div>
        <NotificationsPanel />
      </div>

      <!-- About (child version — just version number) -->
      <div class="card-lg mb-6">
        <div class="flex items-center gap-2 mb-3">
          <InformationCircleIcon class="w-5 h-5 text-accent-lavender-bold" />
          <h2 class="text-lg font-semibold font-heading text-ink-primary">О приложении</h2>
        </div>
        <div class="flex items-center justify-between p-3 bg-surface-sunken rounded-lg">
          <span class="text-sm font-medium text-ink-primary">Версия</span>
          <span class="text-sm font-mono text-ink-secondary">v{{ appVersion }}</span>
        </div>
      </div>

      <!-- Your Data (child version) -->
      <div class="card-lg mb-6">
        <div class="flex items-center gap-2 mb-3">
          <ArrowDownTrayIcon class="w-5 h-5 text-accent-lavender-bold" />
          <h2 class="text-lg font-semibold font-heading text-ink-primary">Ваши данные</h2>
        </div>
        <div class="flex items-start justify-between gap-4">
          <p class="text-sm text-ink-secondary">
            Скачайте ZIP-архив всего, что вы создали в Kinhold: задачи, баллы, значки, историю чатов и многое другое.
          </p>
          <BaseButton variant="primary" size="sm" :loading="exportingData" @click="openExportModal">
            Выгрузить мои данные
          </BaseButton>
        </div>
      </div>

      <!-- Danger Zone (child version — account deletion only, not managed accounts) -->
      <div v-if="!currentUser?.is_managed" class="card-lg mb-6 border border-status-failed/30">
        <div class="flex items-center gap-2 mb-3">
          <ExclamationTriangleIcon class="w-5 h-5 text-status-failed dark:text-status-failed" />
          <h2 class="text-lg font-semibold font-heading text-status-failed dark:text-status-failed">Опасная зона</h2>
        </div>
        <div class="flex items-start justify-between gap-4">
          <p class="text-sm text-ink-secondary">
            Навсегда удалить ваш аккаунт и все ваши данные. Это действие необратимо.
          </p>
          <BaseButton variant="danger" size="sm" @click="isDemoFamily ? showDemoDeletePopup = true : showDeleteAccountModal = true">
            Удалить аккаунт
          </BaseButton>
        </div>
      </div>
    </template>

    <!-- ============================================ -->
    <!-- MODALS (always at root level)                -->
    <!-- ============================================ -->

    <!-- Add/Edit Member Modal -->
    <BaseModal
      :show="showMemberModal"
      :title="editingMember ? 'Изменить участника семьи' : 'Добавить участника семьи'"
      @close="closeMemberModal"
    >
      <form class="space-y-4" @submit.prevent="handleSaveMember">
        <BaseInput
          v-model="memberForm.name"
          label="Имя"
          placeholder="Имя"
          required
          :error="memberErrors.name"
        />

        <BaseInput
          v-model="memberForm.email"
          label="Эл. почта (необязательно для управляемых аккаунтов)"
          type="email"
          placeholder="email@example.com"
          :error="memberErrors.email"
        />
        <p class="text-xs text-ink-secondary -mt-2">
          Оставьте пустым для маленьких детей — будет создан управляемый аккаунт, в который вы сможете переключаться.
        </p>

        <BaseInput
          v-if="!editingMember && memberForm.email"
          v-model="memberForm.password"
          label="Пароль (необязательно)"
          type="password"
          placeholder="Оставьте пустым, чтобы задать позже"
          :error="memberErrors.password"
        />

        <label
          v-if="!editingMember && memberForm.email"
          class="flex items-center gap-3 p-3 bg-surface-sunken rounded-lg cursor-pointer"
        >
          <input v-model="memberForm.sendEmail" type="checkbox" class="rounded" />
          <div>
            <p class="text-sm font-medium text-ink-primary">Отправить приветственное письмо</p>
            <p class="text-xs text-ink-secondary">Отправить эл. письмо с инструкциями для входа</p>
          </div>
        </label>

        <div>
          <label class="block text-sm font-medium text-ink-secondary mb-2">Роль</label>
          <KinSelect
            v-model="memberForm.role"
            class="w-full"
            required
            :options="memberRoleOptions"
          />
        </div>

        <BaseInput
          v-model="memberForm.date_of_birth"
          label="Дата рождения (необязательно)"
          type="date"
        />

        <div class="flex gap-2 justify-end pt-4">
          <BaseButton variant="ghost" @click="closeMemberModal">Отмена</BaseButton>
          <BaseButton variant="primary" :loading="savingMember">
            {{ editingMember ? 'Сохранить изменения' : 'Добавить участника' }}
          </BaseButton>
        </div>
      </form>
    </BaseModal>

    <!-- Remove Member Confirm -->
    <BaseModal
      :show="showRemoveConfirm"
      title="Удалить участника семьи"
      @close="showRemoveConfirm = false"
    >
      <p class="text-ink-primary dark:text-ink-tertiary">
        Вы уверены, что хотите удалить <strong>{{ removingMember?.name }}</strong> из семьи?
      </p>
      <p v-if="removingMember?.is_managed" class="text-sm text-status-failed dark:text-status-failed mt-2">
        Это управляемый аккаунт, и он будет удалён навсегда.
      </p>
      <p v-else class="text-sm text-ink-secondary mt-2">
        Его аккаунт будет отвязан от вашей семьи, но не удалён.
      </p>
      <div class="flex gap-2 justify-end pt-4">
        <BaseButton variant="ghost" @click="showRemoveConfirm = false">Отмена</BaseButton>
        <BaseButton variant="danger" :loading="removingLoading" @click="handleRemoveMember">Удалить</BaseButton>
      </div>
    </BaseModal>

    <!-- Switch To Child Confirmation Modal -->
    <BaseModal
      :show="showSwitchToModal"
      title="Переключиться на профиль ребёнка"
      @close="closeSwitchToModal"
    >
      <div class="space-y-3">
        <p class="text-sm text-ink-primary dark:text-ink-tertiary">
          Вы собираетесь переключить это устройство на профиль <strong>{{ switchingToMember?.name }}</strong>.
        </p>
        <div class="p-3 bg-sand-50 dark:bg-sand-900/20 border border-sand-200 dark:border-sand-800 rounded-lg">
          <p class="text-sm text-sand-800 dark:text-sand-200 font-medium">Что произойдёт:</p>
          <ul class="text-sm text-sand-700 dark:text-sand-300 mt-1 space-y-1 list-disc list-inside">
            <li>На этом устройстве будет выполнен вход как {{ switchingToMember?.name }}</li>
            <li>Чтобы вернуться, выйдите из аккаунта и снова войдите в родительский аккаунт</li>
          </ul>
        </div>
      </div>
      <div class="flex gap-2 justify-end pt-4">
        <BaseButton variant="ghost" @click="closeSwitchToModal">Отмена</BaseButton>
        <BaseButton variant="primary" :loading="switchingTo" @click="handleSwitchToProfile">
          Переключиться на {{ switchingToMember?.name }}
        </BaseButton>
      </div>
    </BaseModal>

    <!-- Avatar Editor Modal -->
    <AvatarEditor
      :show="showAvatarEditor"
      :target-user="avatarEditTarget"
      :can-change="isParent || childrenCanChangeAvatar"
      @close="showAvatarEditor = false"
      @updated="handleAvatarUpdated"
      @color-changed="authStore.fetchUser()"
    />

    <!-- Demo Family Deletion Popup -->
    <BaseModal
      :show="showDemoDeletePopup"
      title="Демо-семья"
      @close="showDemoDeletePopup = false"
    >
      <p class="text-sm text-ink-secondary">
        Удаление аккаунта и семьи отключено для демо-семьи.
      </p>
      <p class="text-sm text-ink-secondary mt-2">
        Создайте собственную семью, чтобы попробовать все функции!
      </p>
      <template #footer>
        <BaseButton variant="primary" @click="showDemoDeletePopup = false">Понятно</BaseButton>
      </template>
    </BaseModal>

    <!-- Export Data Modal -->
    <BaseModal
      :show="showExportDataModal"
      title="Выгрузить ваши данные"
      @close="closeExportModal"
    >
      <div class="space-y-4">
        <p class="text-sm text-ink-secondary">
          Download a ZIP of everything you've created in Kinhold: tasks, vault entries, points, recipes, and more.
        </p>
        <div class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-lg">
          <p class="text-sm text-amber-700 dark:text-amber-300">
            <strong>Внимание:</strong> файл содержит расшифрованные данные сейфа (пароли, номера SSN, медицинские данные). На вашем диске он хранится открытым текстом. Храните его в надёжном месте и удалите, когда он больше не нужен.
          </p>
        </div>
        <BaseInput
          v-if="currentUser?.has_password !== false"
          v-model="exportPassword"
          label="Подтвердите свой пароль"
          type="password"
          placeholder="Текущий пароль"
          :error="exportError"
          @keydown.enter="handleExportData"
        />
      </div>

      <div class="flex gap-2 justify-end pt-4">
        <BaseButton variant="ghost" @click="closeExportModal">Отмена</BaseButton>
        <BaseButton
          variant="primary"
          :loading="exportingData"
          :disabled="currentUser?.has_password !== false && !exportPassword"
          @click="handleExportData"
        >
          Скачать мои данные
        </BaseButton>
      </div>
    </BaseModal>

    <!-- Delete Account Modal -->
    <BaseModal
      :show="showDeleteAccountModal"
      title="Удалить ваш аккаунт"
      @close="closeDeleteAccountModal"
    >
      <div class="space-y-4">
        <div class="p-3 bg-status-failed/10 border border-status-failed/30 rounded-lg">
          <p class="text-sm text-status-failed font-medium">Это действие окончательное, его нельзя отменить.</p>
          <ul class="text-sm text-status-failed mt-2 space-y-1 list-disc list-inside">
            <li>Все ваши задачи, записи сейфа и история чатов будут удалены</li>
            <li>Подключения ваших календарей будут отозваны</li>
            <li>Ваши загруженные документы будут удалены навсегда</li>
            <li v-if="managedChildrenNames.length">
              Ваши управляемые дети ({{ managedChildrenNames.join(', ') }}) также будут удалены
            </li>
          </ul>
        </div>

        <BaseInput
          v-model="deleteAccountPassword"
          label="Введите ваш пароль для подтверждения"
          type="password"
          placeholder="Текущий пароль"
          :error="deleteAccountError"
        />
      </div>

      <div class="flex gap-2 justify-end pt-4">
        <BaseButton variant="ghost" @click="closeDeleteAccountModal">Отмена</BaseButton>
        <BaseButton
          variant="danger"
          :loading="deletingAccount"
          :disabled="!deleteAccountPassword"
          @click="handleDeleteAccount"
        >
          Навсегда удалить аккаунт
        </BaseButton>
      </div>
    </BaseModal>

    <!-- Delete Family Modal -->
    <BaseModal
      :show="showDeleteFamilyModal"
      title="Удалить всю семью"
      @close="closeDeleteFamilyModal"
    >
      <div class="space-y-4">
        <div class="p-3 bg-status-failed/10 border border-status-failed/30 rounded-lg">
          <p class="text-sm text-status-failed font-medium">
            Это действие навсегда удалит семью «{{ family?.name }}» и ВСЕ данные.
          </p>
          <ul class="text-sm text-status-failed mt-2 space-y-1 list-disc list-inside">
            <li>Будут удалены все участники семьи ({{ familyMembers?.length || 0 }})</li>
            <li>Все задачи, записи сейфа, события календаря и история чатов</li>
            <li>Все баллы, награды, значки и достижения</li>
            <li>Все загруженные документы и файлы</li>
          </ul>
        </div>

        <BaseInput
          v-model="deleteFamilyPassword"
          label="Введите ваш пароль"
          type="password"
          placeholder="Текущий пароль"
        />

        <BaseInput
          v-model="deleteFamilyConfirmation"
          :label="`Введите &quot;${family?.name}&quot; для подтверждения`"
          :placeholder="family?.name"
          :error="deleteFamilyError"
        />
      </div>

      <div class="flex gap-2 justify-end pt-4">
        <BaseButton variant="ghost" @click="closeDeleteFamilyModal">Отмена</BaseButton>
        <BaseButton
          variant="danger"
          :loading="deletingFamily"
          :disabled="!deleteFamilyPassword || deleteFamilyConfirmation !== family?.name"
          @click="handleDeleteFamily"
        >
          Навсегда удалить семью
        </BaseButton>
      </div>
    </BaseModal>

    <!-- Confirm clearing the saved BYOK Anthropic key. Encrypted blob is
         removed from families.settings; chat falls back to platform key. -->
    <BaseModal
      :show="showClearKeyModal"
      title="Удалить сохранённый API-ключ?"
      size="sm"
      @close="showClearKeyModal = false"
    >
      <p class="text-sm text-ink-secondary">
        Ваш зашифрованный ключ Anthropic будет удалён из этой семьи. Функции ИИ вернутся к размещённому ключу Kinhold (с учётом дневных лимитов).
      </p>
      <template #footer>
        <BaseButton variant="ghost" @click="showClearKeyModal = false">Отмена</BaseButton>
        <BaseButton variant="danger" :loading="clearingAi" @click="clearAiKey">Удалить ключ</BaseButton>
      </template>
    </BaseModal>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useCalendarStore } from '@/stores/calendar'
import api from '@/services/api'
import { useNotification } from '@/composables/useNotification'
import { useDarkMode } from '@/composables/useDarkMode'
import { useTheme, themes as availableThemes } from '@/composables/useTheme'
import BaseButton from '@/components/common/BaseButton.vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import UserAvatar from '@/components/common/UserAvatar.vue'
import NotificationsPanel from '@/components/notifications/NotificationsPanel.vue'
import BillingPanel from '@/components/billing/BillingPanel.vue'
import AvatarEditor from '@/components/common/AvatarEditor.vue'
import ToggleSwitch from '@/components/common/ToggleSwitch.vue'
import KinInput from '@/components/design-system/KinInput.vue'
import KinSelect from '@/components/design-system/KinSelect.vue'
import KinSwitch from '@/components/design-system/KinSwitch.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import FamilyAllergenSettings from '@/components/allergens/FamilyAllergenSettings.vue'
import AllergyProfileEditor from '@/components/allergens/AllergyProfileEditor.vue'
import {
  PlusIcon,
  TrashIcon,
  SunIcon,
  MoonIcon,
  PencilIcon,
  ClipboardDocumentIcon,
  ArrowsRightLeftIcon,
  EnvelopeIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  CpuChipIcon,
  CloudIcon,
  CakeIcon,
  ShieldCheckIcon,
  ShieldExclamationIcon,
  SwatchIcon,
  BellIcon,
  CreditCardIcon,
  FireIcon,
  InformationCircleIcon,
  ArrowTopRightOnSquareIcon,
  ArrowDownTrayIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const appOrigin = window.location.origin
const authStore = useAuthStore()
const calendarStore = useCalendarStore()
const { success, error: notificationError } = useNotification()
const { isDark, toggle: toggleDarkMode } = useDarkMode()
const { currentTheme, setTheme: selectTheme } = useTheme()

const { family, familyMembers, currentUser, isParent, appConfig } = storeToRefs(authStore)
const { connections } = storeToRefs(calendarStore)

// ---- KinSelect option arrays ----
const leaderboardPeriodOptions = [
  { value: 'daily', label: 'Ежедневно' },
  { value: 'weekly', label: 'Еженедельно' },
  { value: 'monthly', label: 'Ежемесячно' },
]
const weekStartDayOptions = [
  { value: 'monday', label: 'Понедельник' },
  { value: 'sunday', label: 'Воскресенье' },
]
const memberRoleOptions = [
  { value: 'child', label: 'Ребёнок' },
  { value: 'parent', label: 'Родитель' },
]

// ---- Version & Update Check ----
const foodModuleEnabled = computed(() => authStore.userCanAccessModule('food'))
const appVersion = computed(() => appConfig.value?.version ?? '—')
const updateAvailable = computed(() => appConfig.value?.update_available ?? null)
const updateDismissed = ref(false)
const githubRepo = 'gregqualls/kinhold'

const checkDismissedUpdate = () => {
  const update = updateAvailable.value
  if (!update) return
  const key = `kinhold:dismissed_update:${update.latest_version}`
  updateDismissed.value = localStorage.getItem(key) === 'true'
}

const dismissUpdate = () => {
  const update = updateAvailable.value
  if (!update) return
  localStorage.setItem(`kinhold:dismissed_update:${update.latest_version}`, 'true')
  updateDismissed.value = true
}

// ---- Billing section visibility (70-B / #215) ----
// Section is hidden by default. Shows only when the BILLING_ENABLED gate is on
// AND the signed-in user is the family's billing owner. Other parents see no
// billing UI — only the designated owner manages the subscription.
const canSeeBilling = computed(() => {
  if (!appConfig.value?.billing_enabled) return false
  if (family.value?.is_demo) return false
  const ownerId = family.value?.billing_owner_id
  return !!ownerId && ownerId === currentUser.value?.id
})

// Self-hosted instances (BILLING_ENABLED=false) only ever offer BYOK — there's
// no managed-AI tier to opt into when no one is billing for token usage.
const billingEnabled = computed(() => !!appConfig.value?.billing_enabled)

// GDPR mode: when paywalled users navigate from SubscriptionPaywall via
// `?gdpr=1`, hide everything except export + delete so they can exercise their
// data rights without bypassing the paywall via other settings.
const gdprMode = computed(() => route.query.gdpr === '1')

// ---- Section expand/collapse state ----
const expandedSections = ref(new Set())

const toggleSection = (id, open) => {
  const next = new Set(expandedSections.value)
  if (open) next.add(id)
  else next.delete(id)
  expandedSections.value = next
}

// Avatar editor
const showAvatarEditor = ref(false)
const avatarEditTarget = ref(null)
const childrenCanChangeAvatar = computed(() => {
  const mode = moduleAccessState.avatars?.mode
  return mode === 'all' || mode === undefined
})

const openAvatarEditor = (user) => {
  avatarEditTarget.value = user
  showAvatarEditor.value = true
}

const handleAvatarUpdated = async (newAvatar) => {
  const targetId = avatarEditTarget.value?.id
  if (targetId && targetId !== currentUser.value?.id) {
    // Updated another member — refresh the member in the family list
    if (family.value?.members) {
      const member = family.value.members.find((m) => m.id === targetId)
      if (member) member.avatar = newAvatar
    }
    await authStore.fetchUser()
  } else {
    await authStore.updateUserAvatar(newAvatar)
  }
  showAvatarEditor.value = false
}

// Family form
const savingFamily = ref(false)
const familyForm = reactive({ name: family.value?.name || '' })
const familyErrors = reactive({ name: '' })

// AI config
const savingAi = ref(false)
const testingAi = ref(false)
const clearingAi = ref(false)
const showClearKeyModal = ref(false)
const showAiKey = ref(false)
const aiProviders = ref([])
const aiMode = ref('kinhold') // 'kinhold' = use our key, 'byok' = bring your own
const aiConfig = reactive({
  provider: 'anthropic',
  apiKey: '',
  model: '',
  maskedKey: '',
  hasSavedKey: false,
})
// Inline test result: { valid: true } or { valid: false, error: '...' }.
// Cleared automatically on next keystroke so old verdicts don't linger after
// the user pastes a different key.
const aiTestStatus = ref(null)

// MCP Token
const mcpLoading = ref(false)
const mcpGenerating = ref(false)
const mcpRevoking = ref(false)
const mcpToken = reactive({
  hasToken: false,
  createdAt: null,
  lastUsedAt: null,
})
const mcpGenerated = reactive({
  show: false,
  plainToken: '',
  clients: [],
  mcpUrl: '',
})
const mcpActiveClient = ref('claude_desktop')
const mcpCopied = reactive({})

// Module access (granular)
const savingModules = ref(false)
const moduleAccessState = reactive({
  calendar: { mode: 'all' },
  tasks: { mode: 'all' },
  vault: { mode: 'all' },
  chat: { mode: 'all' },
  points: { mode: 'all' },
  badges: { mode: 'all' },
  avatars: { mode: 'all' },
})
// Legacy compat: moduleToggles is derived from moduleAccessState
const moduleToggles = computed(() => {
  const result = {}
  for (const mod of Object.keys(moduleAccessState)) {
    result[mod] = moduleAccessState[mod]?.mode !== 'off'
  }
  return result
})
const leaderboardPeriod = ref('weekly')
const weekStartDay = ref('monday')
const mealSlots = ref(['breakfast', 'lunch', 'dinner', 'snack'])
const kudosCostEnabled = ref(false)

// Default task points
const defaultPoints = reactive({
  low: 5,
  medium: 10,
  high: 20,
})

// Task assignment
const taskAssignment = reactive({
  mode: 'all',
  users: [],
})
const taskAssignmentOptions = [
  { value: 'all', label: 'Все', description: 'Все члены семьи могут назначать задачи кому угодно.' },
  { value: 'parents_only', label: 'Только родители', description: 'Назначать задачи другим могут только родители. Дети могут создавать задачи только для себя.' },
  { value: 'users', label: 'Выборочно', description: 'Выберите, какие дети могут назначать задачи другим.' },
]
const childMembers = computed(() =>
  familyMembers.value.filter((m) => (m.family_role || m.role) === 'child')
)

// Split modules: tasks/points vs everything else
const availableModules = [
  { id: 'calendar', name: 'Календарь', description: 'Просмотр семейных событий и управление ими' },
  { id: 'tasks', name: 'Задачи', description: 'Создание и назначение задач' },
  { id: 'vault', name: 'Семейный сейф', description: 'Безопасное хранение информации' },
  { id: 'chat', name: 'Kinhold AI', description: 'ИИ-ассистент' },
  { id: 'points', name: 'Баллы и награды', description: 'Зарабатывайте баллы, отправляйте похвалу и покупайте награды' },
  { id: 'badges', name: 'Значки достижений', description: 'Выдаются за выполнение задач, серии и скрытые достижения' },
  { id: 'avatars', name: 'Смена аватаров', description: 'Кто может менять аватары в профилях' },
]
const tasksPointsModules = computed(() =>
  availableModules.filter((m) => m.id === 'tasks' || m.id === 'points')
)
const otherModules = computed(() =>
  availableModules.filter((m) => m.id !== 'tasks' && m.id !== 'points')
)

// Invite code
const inviteCode = ref(family.value?.invite_code || '')
const copied = ref(false)

// Invite email
const inviteEmail = ref('')
const sendingInvite = ref(false)
const inviteEmailSent = ref(false)

// Google account linking
const linkingGoogle = ref(false)
const unlinkingGoogle = ref(false)
const googleLinkError = ref('')
const googleLinked = ref(false)

// Calendar
const connectingCalendar = ref(false)
const disconnectingCalendar = ref(false)
const subscribingUrl = ref(false)
const calendarError = ref('')
const icsError = ref('')
const icsForm = reactive({ url: '', name: '' })

const userCalendarConnections = computed(() =>
  (connections.value || []).filter((c) => c.user_id === currentUser.value?.id && c.provider !== 'ics')
)
const otherMemberConnections = computed(() =>
  (connections.value || []).filter((c) => c.user_id !== currentUser.value?.id)
)
const icsConnections = computed(() =>
  (connections.value || []).filter((c) => c.user_id === currentUser.value?.id && c.provider === 'ics')
)

// Member management
const showMemberModal = ref(false)
const editingMember = ref(null)
const savingMember = ref(false)
const memberForm = reactive({ name: '', email: '', password: '', role: 'child', date_of_birth: '', sendEmail: false })
const memberErrors = reactive({ name: '', email: '', password: '' })

// Remove member
const showRemoveConfirm = ref(false)
const removingMember = ref(null)
const removingLoading = ref(false)

// Profile switching
const showSwitchToModal = ref(false)
const switchingToMember = ref(null)
const switchingTo = ref(false)

// Notifications: per-user preferences live in the unified
// notification_preferences column and are managed entirely by NotificationsPanel
// (which reads/writes through the notifications store).

// ---- Module access helpers ----
const setModuleMode = (moduleId, mode, extra = []) => {
  if (mode === 'all' || mode === 'off') {
    moduleAccessState[moduleId] = { mode }
  } else if (mode === 'roles') {
    moduleAccessState[moduleId] = { mode: 'roles', roles: extra.length ? extra : ['parent'] }
  } else if (mode === 'users') {
    const parentIds = (familyMembers.value || [])
      .filter((m) => (m.family_role || m.role) === 'parent')
      .map((m) => m.id)
    const existing = extra.length ? extra : parentIds
    moduleAccessState[moduleId] = { mode: 'users', users: [...new Set([...parentIds, ...existing])] }
  }
}

const getSelectedUserIds = (moduleId) => {
  const state = moduleAccessState[moduleId]
  if (state?.mode === 'users') return state.users || []
  return []
}

const isMemberSelected = (moduleId, memberId) => {
  const state = moduleAccessState[moduleId]
  if (state?.mode !== 'users') return false
  return (state.users || []).includes(memberId)
}

const toggleMemberAccess = (moduleId, memberId) => {
  const state = moduleAccessState[moduleId]
  if (state?.mode !== 'users') return
  const users = state.users || []
  const idx = users.indexOf(memberId)
  if (idx >= 0) {
    users.splice(idx, 1)
  } else {
    users.push(memberId)
  }
  moduleAccessState[moduleId] = { ...state, users: [...users] }
}

const getSelectedMemberNames = (moduleId) => {
  const state = moduleAccessState[moduleId]
  if (state?.mode !== 'users') return ''
  const userIds = state.users || []
  const members = (familyMembers.value || []).filter((m) => userIds.includes(m.id))
  if (members.length === 0) return ''
  const names = members.map((m) => m.name).join(', ')
  return `Доступ: ${names}`
}

// ---- Family name ----
const updateFamily = async () => {
  familyErrors.name = ''
  if (!familyForm.name) {
    familyErrors.name = 'Введите название семьи'
    return
  }
  savingFamily.value = true
  const result = await authStore.updateFamilyName(familyForm.name)
  if (result.success) {
    success('Название семьи обновлено!')
  } else {
    notificationError(result.error)
  }
  savingFamily.value = false
}

const cancelEditFamily = () => {
  familyForm.name = family.value?.name || ''
  familyErrors.name = ''
}

// ---- Invite code ----
const loadInviteCode = async () => {
  if (family.value?.invite_code) {
    inviteCode.value = family.value.invite_code
    return
  }
  const result = await authStore.getInviteCode()
  if (result.success) {
    inviteCode.value = result.invite_code
  }
}

const copyInviteCode = async () => {
  try {
    await navigator.clipboard.writeText(inviteCode.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    notificationError('Не удалось скопировать')
  }
}

// ---- Invite email ----
const handleSendInviteEmail = async () => {
  if (!inviteEmail.value) return
  sendingInvite.value = true
  inviteEmailSent.value = false
  try {
    await api.post('/family/invite', { email: inviteEmail.value })
    inviteEmailSent.value = true
    inviteEmail.value = ''
    success('Приглашение отправлено!')
    setTimeout(() => { inviteEmailSent.value = false }, 3000)
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось отправить приглашение по эл. почте')
  }
  sendingInvite.value = false
}

// ---- Member management ----
const openAddMemberModal = () => {
  editingMember.value = null
  memberForm.name = ''
  memberForm.email = ''
  memberForm.password = ''
  memberForm.role = 'child'
  memberForm.date_of_birth = ''
  memberForm.sendEmail = false
  memberErrors.name = ''
  memberErrors.email = ''
  memberErrors.password = ''
  showMemberModal.value = true
}

const openEditMemberModal = (member) => {
  editingMember.value = member
  memberForm.name = member.name
  memberForm.email = member.email || ''
  memberForm.password = ''
  memberForm.role = member.family_role || member.role || 'child'
  memberForm.date_of_birth = member.date_of_birth || ''
  memberErrors.name = ''
  memberErrors.email = ''
  memberErrors.password = ''
  showMemberModal.value = true
}

const closeMemberModal = () => {
  showMemberModal.value = false
  editingMember.value = null
}

const handleSaveMember = async () => {
  memberErrors.name = ''
  memberErrors.email = ''
  memberErrors.password = ''

  if (!memberForm.name) {
    memberErrors.name = 'Введите имя'
    return
  }

  savingMember.value = true

  if (editingMember.value) {
    const data = {
      name: memberForm.name,
      role: memberForm.role,
      date_of_birth: memberForm.date_of_birth || null,
    }
    if (memberForm.email) data.email = memberForm.email

    const result = await authStore.updateFamilyMember(editingMember.value.id, data)
    if (result.success) {
      success('Участник обновлён!')
      closeMemberModal()
    } else {
      notificationError(result.error)
    }
  } else {
    const data = {
      name: memberForm.name,
      role: memberForm.role,
      date_of_birth: memberForm.date_of_birth || null,
    }
    if (memberForm.email) data.email = memberForm.email
    if (memberForm.password) data.password = memberForm.password
    if (memberForm.sendEmail) data.send_email = true

    const result = await authStore.addFamilyMember(data)
    if (result.success) {
      success(result.message || 'Участник добавлен!')
      closeMemberModal()
    } else {
      notificationError(result.error)
    }
  }

  savingMember.value = false
}

// ---- Remove member ----
const confirmRemoveMember = (member) => {
  removingMember.value = member
  showRemoveConfirm.value = true
}

const handleRemoveMember = async () => {
  removingLoading.value = true
  const result = await authStore.removeFamilyMember(removingMember.value.id)
  if (result.success) {
    success('Участник удалён!')
    showRemoveConfirm.value = false
    removingMember.value = null
  } else {
    notificationError(result.error)
  }
  removingLoading.value = false
}

// ---- Profile switching ----
const openSwitchToModal = (member) => {
  switchingToMember.value = member
  showSwitchToModal.value = true
}

const closeSwitchToModal = () => {
  showSwitchToModal.value = false
  switchingToMember.value = null
}

const handleSwitchToProfile = async () => {
  switchingTo.value = true
  const result = await authStore.switchToProfile(switchingToMember.value.id)
  if (result.success) {
    success(result.message)
    closeSwitchToModal()
    router.push('/dashboard')
  } else {
    notificationError(result.error)
    closeSwitchToModal()
  }
  switchingTo.value = false
}

// ---- Calendar ----
const handleLinkGoogle = async () => {
  linkingGoogle.value = true
  googleLinkError.value = ''
  try {
    const response = await api.get('/auth/google/link')
    if (response.data.url) {
      window.location.href = response.data.url
    }
  } catch {
    googleLinkError.value = 'Не удалось начать привязку Google. Попробуйте ещё раз.'
  }
  linkingGoogle.value = false
}

const handleUnlinkGoogle = async () => {
  unlinkingGoogle.value = true
  googleLinkError.value = ''
  try {
    await api.delete('/auth/google/unlink')
    await authStore.fetchUser()
    success('Аккаунт Google отвязан')
  } catch (err) {
    googleLinkError.value = err.response?.data?.message || 'Не удалось отвязать аккаунт Google.'
  }
  unlinkingGoogle.value = false
}

const handleConnectCalendar = async () => {
  connectingCalendar.value = true
  calendarError.value = ''
  const result = await calendarStore.connect('google')
  if (result.success && result.authUrl) {
    window.location.href = result.authUrl
  } else {
    calendarError.value = result.error || 'Не удалось начать подключение Google Календаря.'
  }
  connectingCalendar.value = false
}

const handleDisconnectCalendar = async (connectionId) => {
  disconnectingCalendar.value = true
  calendarError.value = ''
  const result = await calendarStore.disconnect(connectionId)
  if (result.success) {
    success('Календарь отключён!')
  } else {
    calendarError.value = result.error || 'Не удалось отключить календарь'
  }
  disconnectingCalendar.value = false
}

const handleSubscribeUrl = async () => {
  subscribingUrl.value = true
  icsError.value = ''
  const result = await calendarStore.subscribeUrl(icsForm.url, icsForm.name || null)
  if (result.success) {
    success(result.message || 'Подписка на календарь оформлена!')
    icsForm.url = ''
    icsForm.name = ''
  } else {
    icsError.value = result.error || 'Не удалось оформить подписку на календарь'
  }
  subscribingUrl.value = false
}

// ---- AI Provider settings ----
const selectedProvider = computed(() =>
  aiProviders.value.find((p) => p.slug === aiConfig.provider) || aiProviders.value[0] || {}
)
const selectedProviderName = computed(() => selectedProvider.value?.name || 'AI')
const selectedProviderPlaceholder = computed(() => selectedProvider.value?.key_placeholder || 'API key...')
const selectedProviderDefaultModel = computed(() => selectedProvider.value?.default_model || '')
const selectedProviderHelpUrl = computed(() => selectedProvider.value?.help_url || '#')

const providerIcon = (slug) => {
  const icons = { anthropic: 'A', openai: 'O', google: 'G' }
  return icons[slug] || '?'
}
const providerIconClass = (slug) => {
  const classes = {
    anthropic: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
    openai: 'bg-emerald-100 text-status-success dark:bg-emerald-900/30 dark:text-emerald-300',
    google: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
  }
  return classes[slug] || 'bg-surface-sunken text-ink-secondary'
}

const selectAiProvider = (slug) => {
  aiConfig.provider = slug
}

const resetAiConfig = () => {
  aiConfig.apiKey = ''
  aiConfig.model = ''
  aiTestStatus.value = null
}

// Wipe stale verdicts the moment the user edits the field — otherwise a
// "Key is valid" badge could linger next to a brand-new untested paste.
watch(() => aiConfig.apiKey, () => { aiTestStatus.value = null })

const testAiKey = async () => {
  if (!aiConfig.apiKey) return
  testingAi.value = true
  aiTestStatus.value = null
  try {
    const { data } = await api.post('/settings/ai/test', { api_key: aiConfig.apiKey })
    aiTestStatus.value = data
  } catch (err) {
    aiTestStatus.value = {
      valid: false,
      error: err.response?.data?.message || 'Не удалось связаться с тестовым сервером.',
    }
  }
  testingAi.value = false
}

const clearAiKey = async () => {
  clearingAi.value = true
  try {
    const { data } = await api.put('/settings', { ai_api_key: '' })
    if (data.settings) {
      aiConfig.maskedKey = data.settings.ai_api_key_masked || ''
      aiConfig.hasSavedKey = data.settings.ai_has_key || false
    }
    aiConfig.apiKey = ''
    aiTestStatus.value = null
    showClearKeyModal.value = false
    await authStore.fetchUser()
    authStore.fetchAiReady()
    success('API-ключ удалён.')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось удалить API-ключ.')
  }
  clearingAi.value = false
}

const saveAiSettings = async () => {
  savingAi.value = true
  try {
    const payload = {
      ai_mode: aiMode.value,
      ai_provider: aiConfig.provider,
      ai_model: aiConfig.model || '',
    }
    if (aiMode.value === 'byok' && aiConfig.apiKey) {
      payload.ai_api_key = aiConfig.apiKey
    }
    const { data } = await api.put('/settings', payload)
    if (data.settings) {
      aiConfig.maskedKey = data.settings.ai_api_key_masked || ''
      aiConfig.hasSavedKey = data.settings.ai_has_key || false
      aiProviders.value = data.settings.ai_providers || aiProviders.value
    }
    aiConfig.apiKey = ''
    showAiKey.value = false
    await authStore.fetchUser()
    authStore.fetchAiReady()
    success('Настройки ИИ сохранены!')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось сохранить настройки ИИ')
  }
  savingAi.value = false
}

// ---- Module settings ----
const saveModuleSettings = async () => {
  savingModules.value = true
  try {
    const module_access = {}
    for (const mod of availableModules) {
      const state = moduleAccessState[mod.id]
      if (!state) continue
      const rule = { mode: state.mode }
      if (state.mode === 'roles') rule.roles = state.roles || ['parent']
      if (state.mode === 'users') rule.users = state.users || []
      module_access[mod.id] = rule
    }

    // Translate avatars module access to legacy boolean for backward compat
    const avatarMode = moduleAccessState.avatars?.mode
    const children_can_change_avatar = avatarMode === 'all' || avatarMode === undefined

    await api.put('/settings', {
      module_access,
      leaderboard_period: leaderboardPeriod.value,
      kudos_cost_enabled: kudosCostEnabled.value,
      children_can_change_avatar,
    })
    await authStore.fetchUser()
    success('Предпочтения сохранены!')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось сохранить предпочтения')
  }
  savingModules.value = false
}

// ---- Combined Tasks & Points save ----
const savingTasksPoints = ref(false)
const saveTasksPointsSection = async () => {
  savingTasksPoints.value = true
  try {
    // Build a single payload with all tasks/points settings
    const module_access = {}
    for (const mod of availableModules) {
      const state = moduleAccessState[mod.id]
      if (!state) continue
      const rule = { mode: state.mode }
      if (state.mode === 'roles') rule.roles = state.roles || ['parent']
      if (state.mode === 'users') rule.users = state.users || []
      module_access[mod.id] = rule
    }

    const avatarMode = moduleAccessState.avatars?.mode
    const children_can_change_avatar = avatarMode === 'all' || avatarMode === undefined

    const payload = {
      module_access,
      leaderboard_period: leaderboardPeriod.value,
      kudos_cost_enabled: kudosCostEnabled.value,
      children_can_change_avatar,
      default_points_low: defaultPoints.low,
      default_points_medium: defaultPoints.medium,
      default_points_high: defaultPoints.high,
      task_assignment: {
        mode: taskAssignment.mode,
        users: taskAssignment.users,
      },
    }

    await api.put('/settings', payload)
    await authStore.fetchUser()
    success('Настройки задач и баллов сохранены!')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось сохранить настройки')
  }
  savingTasksPoints.value = false
}



// ---- Food Settings ----
const allMealSlots = [
  { key: 'breakfast', label: 'Завтрак', icon: SunIcon },
  { key: 'lunch', label: 'Обед', icon: CloudIcon },
  { key: 'dinner', label: 'Ужин', icon: MoonIcon },
  { key: 'snack', label: 'Перекус', icon: CakeIcon },
]

const toggleMealSlot = (key) => {
  const idx = mealSlots.value.indexOf(key)
  if (idx === -1) {
    mealSlots.value.push(key)
  } else if (mealSlots.value.length > 1) {
    mealSlots.value.splice(idx, 1)
  }
}

const savingFood = ref(false)
const saveFoodSection = async () => {
  savingFood.value = true
  try {
    await api.put('/settings', { week_start_day: weekStartDay.value, meal_slots: mealSlots.value })
    await authStore.fetchUser()
    success('Настройки питания сохранены!')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось сохранить настройки питания')
  }
  savingFood.value = false
}

// ---- MCP Token ----
const fetchMcpTokenStatus = async () => {
  mcpLoading.value = true
  try {
    const { data } = await api.get('/mcp/token')
    mcpToken.hasToken = data.has_token
    mcpToken.createdAt = data.created_at
    mcpToken.lastUsedAt = data.last_used_at
  } catch {
    // Silent — defaults are fine
  }
  mcpLoading.value = false
}

const handleGenerateMcpToken = async () => {
  mcpGenerating.value = true
  try {
    const { data } = await api.post('/mcp/token')
    mcpToken.hasToken = true
    mcpToken.createdAt = data.created_at
    mcpToken.lastUsedAt = null
    mcpGenerated.show = true
    mcpGenerated.plainToken = data.plain_token
    mcpGenerated.mcpUrl = data.mcp_url
    mcpGenerated.clients = data.clients.map(c => ({
      ...c,
      configJson: c.config ? JSON.stringify(c.config, null, 2) : null,
    }))
    mcpActiveClient.value = data.clients[0]?.id || 'claude_desktop'
    mcpCopied.token = false
    data.clients.forEach(c => { mcpCopied[c.id] = false })
    success('MCP-токен создан!')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось создать токен')
  }
  mcpGenerating.value = false
}

const handleRevokeMcpToken = async () => {
  mcpRevoking.value = true
  try {
    await api.delete('/mcp/token')
    mcpToken.hasToken = false
    mcpToken.createdAt = null
    mcpToken.lastUsedAt = null
    mcpGenerated.show = false
    mcpGenerated.plainToken = ''
    success('MCP-токен отозван.')
  } catch (err) {
    notificationError(err.response?.data?.message || 'Не удалось отозвать токен')
  }
  mcpRevoking.value = false
}

const copyMcpSnippet = async (field, text) => {
  try {
    await navigator.clipboard.writeText(text)
    mcpCopied[field] = true
    setTimeout(() => { mcpCopied[field] = false }, 2000)
  } catch {
    notificationError('Не удалось скопировать в буфер обмена')
  }
}

// ---- Account & Family Deletion ----
const isDemoFamily = computed(() => family.value?.slug === 'q32-demo-family')
const showDemoDeletePopup = ref(false)
const showDeleteAccountModal = ref(false)
const showDeleteFamilyModal = ref(false)
const deleteAccountPassword = ref('')
const deleteAccountError = ref('')
const deletingAccount = ref(false)
const deleteFamilyPassword = ref('')
const deleteFamilyConfirmation = ref('')
const deleteFamilyError = ref('')
const deletingFamily = ref(false)

const managedChildrenNames = computed(() => {
  if (!familyMembers.value || !currentUser.value) return []
  return familyMembers.value
    .filter(m => m.is_managed && m.managed_by === currentUser.value.id)
    .map(m => m.name)
})

const closeDeleteAccountModal = () => {
  showDeleteAccountModal.value = false
  deleteAccountPassword.value = ''
  deleteAccountError.value = ''
}

const closeDeleteFamilyModal = () => {
  showDeleteFamilyModal.value = false
  deleteFamilyPassword.value = ''
  deleteFamilyConfirmation.value = ''
  deleteFamilyError.value = ''
}

const handleDeleteAccount = async () => {
  deleteAccountError.value = ''
  deletingAccount.value = true
  try {
    await api.delete('/settings/account', { data: { password: deleteAccountPassword.value } })
    authStore.logout()
    router.push('/login')
  } catch (err) {
    const msg = err.response?.data?.message || 'Не удалось удалить аккаунт'
    deleteAccountError.value = msg
    notificationError(msg)
  }
  deletingAccount.value = false
}

// ---- Data Export (GDPR Article 15) ----
const showExportDataModal = ref(false)
const exportPassword = ref('')
const exportError = ref('')
const exportingData = ref(false)

const openExportModal = () => {
  exportPassword.value = ''
  exportError.value = ''
  showExportDataModal.value = true
}

const closeExportModal = () => {
  showExportDataModal.value = false
  exportPassword.value = ''
  exportError.value = ''
}

const handleExportData = async () => {
  exportError.value = ''
  exportingData.value = true
  try {
    const payload = currentUser.value?.has_password !== false
      ? { password: exportPassword.value }
      : {}
    const res = await api.post('/settings/account/data-export', payload, { responseType: 'blob' })
    const url = URL.createObjectURL(res.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `kinhold-export-${new Date().toISOString().slice(0, 10)}.zip`
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
    closeExportModal()
  } catch (err) {
    let msg = 'Не удалось выгрузить данные'
    // Error response is a Blob (responseType=blob); parse it as JSON.
    if (err.response?.data instanceof Blob) {
      try {
        const text = await err.response.data.text()
        msg = JSON.parse(text)?.message || msg
      } catch { /* keep default */ }
    } else if (err.response?.data?.message) {
      msg = err.response.data.message
    }
    exportError.value = msg
    notificationError(msg)
  } finally {
    exportingData.value = false
  }
}

const handleDeleteFamily = async () => {
  deleteFamilyError.value = ''
  deletingFamily.value = true
  try {
    await api.delete('/family', {
      data: {
        password: deleteFamilyPassword.value,
        confirmation: deleteFamilyConfirmation.value,
      },
    })
    authStore.logout()
    router.push('/login')
  } catch (err) {
    const msg = err.response?.data?.message || 'Не удалось удалить семью'
    deleteFamilyError.value = msg
    notificationError(msg)
  }
  deletingFamily.value = false
}

// ---- Init ----
onMounted(async () => {
  familyForm.name = family.value?.name || ''

  // Initialize module access state from the API-provided module_access map
  const settings = family.value?.settings || {}
  const moduleAccessFromApi = family.value?.module_access || {}
  const legacyModules = settings.modules || {}

  for (const mod of availableModules) {
    if (moduleAccessFromApi[mod.id]) {
      moduleAccessState[mod.id] = { ...moduleAccessFromApi[mod.id] }
    } else {
      moduleAccessState[mod.id] = { mode: legacyModules[mod.id] === false ? 'off' : 'all' }
    }
  }
  leaderboardPeriod.value = settings.leaderboard_period || 'weekly'
  kudosCostEnabled.value = settings.kudos_cost_enabled ?? false
  weekStartDay.value = settings.week_start_day || 'monday'
  mealSlots.value = settings.meal_slots || ['breakfast', 'lunch', 'dinner', 'snack']

  // Initialize default task points
  defaultPoints.low = settings.default_points_low ?? 5
  defaultPoints.medium = settings.default_points_medium ?? 10
  defaultPoints.high = settings.default_points_high ?? 20

  // Initialize task assignment
  const ta = settings.task_assignment || {}
  taskAssignment.mode = ta.mode || 'all'
  taskAssignment.users = ta.users || []

  // Initialize avatar access from module_access or legacy boolean
  if (moduleAccessFromApi.avatars) {
    moduleAccessState.avatars = { ...moduleAccessFromApi.avatars }
  } else {
    // Convert legacy boolean to module access mode
    const canChange = settings.children_can_change_avatar ?? true
    moduleAccessState.avatars = { mode: canChange ? 'all' : 'roles', roles: canChange ? [] : ['parent'] }
  }

  // Load AI settings from the settings API
  if (isParent.value) {
    try {
      const { data } = await api.get('/settings')
      const s = data.settings || {}
      aiConfig.provider = s.ai_provider || 'anthropic'
      aiConfig.model = s.ai_model || ''
      aiConfig.maskedKey = s.ai_api_key_masked || ''
      aiConfig.hasSavedKey = s.ai_has_key || false
      aiProviders.value = s.ai_providers || []
      // Self-hosted has no Kinhold-managed AI tier — pin to BYOK regardless of stored mode.
      aiMode.value = !appConfig.value?.billing_enabled
        ? 'byok'
        : (s.ai_mode || (s.ai_has_key ? 'byok' : 'kinhold'))
    } catch {
      // Defaults are fine
    }
  }

  await calendarStore.fetchConnections()
  fetchMcpTokenStatus()

  // Check if update was previously dismissed
  checkDismissedUpdate()

  // Notification preferences are loaded inside NotificationsPanel on mount.

  // Load invite code for parents
  if (isParent.value) {
    await loadInviteCode()
  }

  // Handle OAuth redirect results
  if (route.query.google_linked) {
    googleLinked.value = true
    await authStore.fetchUser()
    success('Аккаунт Google привязан!')
    router.replace({ path: '/settings' })
  } else if (route.query.google_error) {
    googleLinkError.value = route.query.google_error
    router.replace({ path: '/settings' })
  } else if (route.query.calendar_connected) {
    success('Google Календарь успешно подключён!')
    router.replace({ path: '/settings' })
  } else if (route.query.calendar_error) {
    calendarError.value = route.query.calendar_error
    router.replace({ path: '/settings' })
  }

  // Handle hash deep-linking — expand the target section
  const hash = window.location.hash.slice(1)
  if (hash) {
    expandedSections.value.add(hash)
    await nextTick()
    document.getElementById(hash)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  if (gdprMode.value) {
    expandedSections.value.add('your-data')
    expandedSections.value.add('danger')
  }
})
</script>
