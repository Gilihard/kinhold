<template>
  <div class="flex-1 flex flex-col items-center justify-center text-center">
    <!-- Success Icon -->
    <div class="w-16 h-16 rounded-full bg-status-success/10 flex items-center justify-center mb-6">
      <CheckIcon class="w-8 h-8 text-status-success" />
    </div>

    <h1 class="text-2xl font-heading font-bold text-ink-primary mb-2">
      Всё готово
    </h1>
    <p class="text-base text-ink-secondary max-w-xs">
      Ваш семейный центр готов. Настройки всегда можно изменить позже.
    </p>

    <!-- Summary -->
    <div class="mt-8 w-full space-y-2">
      <div
        v-for="item in summaryItems"
        :key="item.label"
        class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-surface-sunken"
      >
        <CheckCircleIcon v-if="item.done" class="w-5 h-5 text-status-success flex-shrink-0" />
        <MinusCircleIcon v-else class="w-5 h-5 text-ink-tertiary flex-shrink-0" />
        <span class="text-sm text-ink-primary">{{ item.label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useOnboardingStore } from '@/stores/onboarding'
import { useAuthStore } from '@/stores/auth'
import { useBillingStore } from '@/stores/billing'
import { CheckIcon, CheckCircleIcon, MinusCircleIcon } from '@heroicons/vue/24/outline'

const store = useOnboardingStore()
const authStore = useAuthStore()
const billing = useBillingStore()

const billingEnabled = computed(() => !!authStore.appConfig?.billing_enabled)
const isBillingOwner = computed(() => authStore.user?.id === authStore.family?.billing_owner_id)
const showBilling = computed(() => billingEnabled.value && isBillingOwner.value && !authStore.family?.is_demo)

const onTrial = computed(() => !!billing.summary?.on_trial)
const trialEndsAt = computed(() => billing.summary?.trial_ends_at)
const subStatus = computed(() => billing.summary?.status)
const aiTier = computed(() => billing.summary?.ai_tier?.plan || null)
const aiTierLabel = computed(() => {
  const slug = aiTier.value
  if (!slug) return null
  if (slug === 'off') return null
  const tiers = billing.summary?.ai_tier?.tiers || []
  const tier = tiers.find(t => t.slug === slug)
  if (!tier) {
    if (slug === 'byok') return 'Свой ключ (BYOK)'
    return slug.charAt(0).toUpperCase() + slug.slice(1)
  }
  return `AI ${tier.name} — ${tier.daily_messages} сообщ./день`
})

function formatDate(iso) {
  if (!iso) return ''
  try {
    return new Date(iso).toLocaleDateString('ru-RU', {
      year: 'numeric', month: 'short', day: 'numeric',
    })
  } catch {
    return iso
  }
}

const summaryItems = computed(() => {
  const items = [
    { label: 'Семья создана', done: true },
    { label: 'Календарь подключён', done: store.status?.calendar_connected || false },
    {
      label: store.selectedPresets.size > 0
        ? `Создано тегов: ${store.selectedPresets.size}`
        : 'Теги',
      done: store.selectedPresets.size > 0,
    },
  ]

  if (showBilling.value && (subStatus.value === 'trialing' || subStatus.value === 'active')) {
    if (onTrial.value && trialEndsAt.value) {
      items.push({
        label: `Пробная версия началась — действует до ${formatDate(trialEndsAt.value)}`,
        done: true,
      })
    } else if (subStatus.value === 'active') {
      items.push({ label: 'Подписка активна', done: true })
    }
    if (aiTierLabel.value) {
      items.push({ label: `${aiTierLabel.value} — активен`, done: true })
    }
  }

  return items
})

onMounted(async () => {
  if (showBilling.value) {
    try { await billing.fetch() } catch { /* surface inline if needed */ }
  }
})
</script>
