<template>
  <div class="flex-1 flex flex-col">
    <div class="text-center mb-8">
      <h1 class="text-2xl font-heading font-bold text-ink-primary mb-2">
        {{ isParent ? 'Добро пожаловать в Kinhold' : `Добро пожаловать в семью «${familyName || 'Kinhold'}»` }}
      </h1>
      <p class="text-base text-ink-secondary">
        {{ isParent ? "Настроим ваш семейный центр. Это займёт около 2 минут." : "Осталось сделать пару быстрых шагов, чтобы всё было готово." }}
      </p>
    </div>

    <div class="space-y-5">
      <KinInput
        v-model="familyName"
        type="text"
        label="Название семьи"
        placeholder="Например, Ивановы"
        :disabled="!isParent"
      />

      <KinSelect
        v-model="timezone"
        label="Часовой пояс"
        :options="timezoneOptions"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted } from 'vue'
import { useOnboardingStore } from '@/stores/onboarding'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import KinInput from '@/components/design-system/KinInput.vue'
import KinSelect from '@/components/design-system/KinSelect.vue'

const store = useOnboardingStore()
const authStore = useAuthStore()
const { setStepLoading, registerContinue } = inject('onboarding')

const isParent = authStore.isParent
// Pre-fill from auth store immediately (it's already loaded from /api/v1/user
// before the wizard mounts). store.status hydrates a moment later via the
// onMounted hook and refreshes this value if the server has a more recent
// name.
const familyName = ref(authStore.family?.name || '')
const timezone = ref(authStore.user?.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone)

const commonTimezones = [
  'America/New_York',
  'America/Chicago',
  'America/Denver',
  'America/Los_Angeles',
  'America/Anchorage',
  'Pacific/Honolulu',
  'America/Phoenix',
  'America/Toronto',
  'America/Vancouver',
  'Europe/London',
  'Europe/Paris',
  'Europe/Berlin',
  'Asia/Tokyo',
  'Asia/Shanghai',
  'Australia/Sydney',
]

const timezoneOptions = computed(() => commonTimezones.map(tz => ({ value: tz, label: formatTimezone(tz) })))

function formatTimezone(tz) {
  try {
    const offset = new Intl.DateTimeFormat('en-US', { timeZone: tz, timeZoneName: 'shortOffset' })
      .formatToParts(new Date())
      .find(p => p.type === 'timeZoneName')?.value || ''
    return `${tz.replace(/_/g, ' ').replace('/', ' / ')} (${offset})`
  } catch {
    return tz
  }
}

registerContinue(async () => {
  setStepLoading(true)
  try {
    if (isParent && familyName.value && familyName.value !== store.status?.family_name) {
      await api.put('/family', { name: familyName.value })
    }
    if (timezone.value) {
      await api.patch('/user', { timezone: timezone.value })
    }
    return true
  } catch {
    return false
  } finally {
    setStepLoading(false)
  }
})

onMounted(() => {
  if (store.status) {
    familyName.value = store.status.family_name || ''
    timezone.value = store.status.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone
  }
})
</script>
