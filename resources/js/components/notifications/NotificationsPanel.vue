<template>
  <div class="space-y-5">
    <!-- Push permission row -->
    <div class="p-4 bg-surface-sunken rounded-lg">
      <div class="flex items-start justify-between gap-3">
        <div class="flex-1">
          <p class="text-sm font-semibold text-ink-primary">Push-уведомления на этом устройстве</p>
          <p class="text-xs text-ink-secondary mt-1">
            <template v-if="!notifications.isPushAvailable">
              Push недоступен в этом браузере, либо на сервере не настроены VAPID-ключи.
            </template>
            <template v-else-if="notifications.localPermission === 'denied'">
              В доступе отказано. Разрешите push в настройках сайта в браузере, затем вернитесь сюда.
            </template>
            <template v-else-if="notifications.isPushActive">
              Активно — подключено {{ notifications.pushStatus.subscriptions }} {{ pluralRu(notifications.pushStatus.subscriptions, 'устройство', 'устройства', 'устройств') }}.
            </template>
            <template v-else>
              Получайте уведомления о новых задачах, похвалах и напоминаниях, даже когда Kinhold не открыт.
            </template>
          </p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <BaseButton
            v-if="!notifications.isPushActive"
            variant="primary"
            :disabled="!notifications.isPushAvailable || notifications.localPermission === 'denied'"
            :loading="enabling"
            @click="enable"
          >
            Включить
          </BaseButton>
          <template v-else>
            <BaseButton variant="ghost" :loading="testing" @click="test">Отправить тест</BaseButton>
            <BaseButton variant="secondary" :loading="disabling" @click="disable">Выключить</BaseButton>
          </template>
        </div>
      </div>
      <p v-if="notifications.lastError" class="mt-2 text-xs text-rose-600 dark:text-rose-400">
        {{ notifications.lastError }}
      </p>
    </div>

    <!-- Global mute -->
    <div class="p-4 bg-surface-sunken rounded-lg">
      <KinSwitch
        :model-value="notifications.preferences.muted"
        label="Отключить все push-уведомления"
        description="Эл. почта по-прежнему приходит. Удобно, когда на время не нужны push-уведомления."
        color="lavender"
        @update:model-value="setMuted"
      />
    </div>

    <!-- Quiet hours -->
    <div class="p-4 bg-surface-sunken rounded-lg space-y-3">
      <KinSwitch
        :model-value="notifications.preferences.quiet_hours.enabled"
        label="Тихие часы"
        description="Отключить push (не эл. почту) в эти часы, по вашему часовому поясу."
        color="lavender"
        @update:model-value="setQuietEnabled"
      />
      <div v-if="notifications.preferences.quiet_hours.enabled" class="flex items-center gap-3 pl-1">
        <label class="flex items-center gap-2 text-sm text-ink-secondary">
          С
          <input
            type="time"
            class="px-2 py-1 rounded border border-border-subtle bg-surface text-ink-primary text-sm"
            :value="notifications.preferences.quiet_hours.start"
            @change="setQuietStart($event.target.value)"
          />
        </label>
        <label class="flex items-center gap-2 text-sm text-ink-secondary">
          до
          <input
            type="time"
            class="px-2 py-1 rounded border border-border-subtle bg-surface text-ink-primary text-sm"
            :value="notifications.preferences.quiet_hours.end"
            @change="setQuietEnd($event.target.value)"
          />
        </label>
      </div>
    </div>

    <!-- Per-category groups -->
    <div
      v-for="(types, categoryKey) in groupedTypes"
      :key="categoryKey"
      class="rounded-lg border border-border-subtle"
    >
      <header class="px-4 py-2 bg-surface-sunken/60 rounded-t-lg">
        <h3 class="text-sm font-semibold text-ink-primary">
          {{ notifications.registry.categories[categoryKey] || categoryKey }}
        </h3>
      </header>
      <div class="divide-y divide-border-subtle">
        <div
          v-for="type in types"
          :key="type.key"
          class="px-4 py-3 flex items-center justify-between gap-4"
        >
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-ink-primary truncate">{{ type.label }}</p>
            <p v-if="type.description" class="text-xs text-ink-secondary mt-0.5">{{ type.description }}</p>
          </div>
          <div class="flex items-center gap-4 flex-shrink-0">
            <label v-if="type.channels.includes('email')" class="flex items-center gap-1 text-xs">
              <input
                type="checkbox"
                class="rounded border-border-subtle disabled:opacity-40 disabled:cursor-not-allowed"
                :checked="emailValue(type.key, type.default_email)"
                :disabled="!hasEmail"
                @change="onEmailToggle(type.key, $event.target.checked)"
              />
              <span class="text-ink-secondary">Эл. почта</span>
            </label>
            <label v-if="type.channels.includes('push')" class="flex items-center gap-1 text-xs">
              <input
                type="checkbox"
                class="rounded border-border-subtle disabled:opacity-40 disabled:cursor-not-allowed"
                :checked="pushValue(type.key, type.default_push)"
                :disabled="!notifications.isPushActive"
                @change="onPushToggle(type.key, $event.target.checked)"
              />
              <span class="text-ink-secondary">Push</span>
            </label>
            <button
              v-if="canTest(type.key)"
              type="button"
              class="text-xs px-2 py-1 rounded border border-border-subtle text-ink-secondary hover:text-ink-primary hover:bg-surface-sunken/60 disabled:opacity-40 disabled:cursor-not-allowed"
              :disabled="!notifications.isPushActive || testingKey === type.key"
              :title="`Отправить тестовый push: ${type.label}`"
              @click="testForKey(type.key)"
            >
              {{ testingKey === type.key ? '…' : 'Тест' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="!hasEmail" class="text-xs text-ink-secondary">
      Каналы эл. почты отключены — у этого аккаунта нет адреса электронной почты.
    </p>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import KinSwitch from '@/components/design-system/KinSwitch.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { pluralRu } from '@/utils/plural'

const auth = useAuthStore()
const notifications = useNotificationsStore()

const hasEmail = computed(() => !!auth.user?.email)
const groupedTypes = computed(() => notifications.registry.types_by_category || {})

const enabling = ref(false)
const disabling = ref(false)
const testing = ref(false)
const testingKey = ref('')

// Registry keys that have a server-side sample dispatcher. Keep in sync with
// PushSubscriptionController::testType().
const TESTABLE_KEYS = new Set([
  'task_due_soon',
  'shopping_item_added',
  'calendar_event_reminder',
  'dinner_reminder',
])

function canTest(key) {
  return TESTABLE_KEYS.has(key)
}

async function testForKey(key) {
  testingKey.value = key
  try {
    await notifications.testPushForKey(key)
  } finally {
    testingKey.value = ''
  }
}

function emailValue(key, def) {
  const v = notifications.preferences.email?.[key]
  return v === undefined ? def : !!v
}

function pushValue(key, def) {
  const v = notifications.preferences.push?.[key]
  return v === undefined ? def : !!v
}

async function onEmailToggle(key, value) {
  await notifications.setChannelKey('email', key, value)
}

async function onPushToggle(key, value) {
  await notifications.setChannelKey('push', key, value)
}

async function setMuted(value) {
  await notifications.setMuted(value)
}

async function setQuietEnabled(value) {
  await notifications.setQuietHours({ enabled: value })
}

async function setQuietStart(value) {
  await notifications.setQuietHours({ start: value })
}

async function setQuietEnd(value) {
  await notifications.setQuietHours({ end: value })
}

async function enable() {
  enabling.value = true
  try {
    await notifications.enablePush()
  } finally {
    enabling.value = false
  }
}

async function disable() {
  disabling.value = true
  try {
    await notifications.disablePush()
  } finally {
    disabling.value = false
  }
}

async function test() {
  testing.value = true
  try {
    await notifications.testPush()
  } finally {
    testing.value = false
  }
}

onMounted(async () => {
  try {
    await notifications.fetch()
  } catch {
    /* error surfaced via store.lastError */
  }
})
</script>
