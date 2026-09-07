<template>
  <div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-heading font-bold text-ink-primary mb-2">
        Что вам доступно
      </h1>
      <p class="text-base text-ink-secondary">
        Для вашей семьи уже настроены эти функции.
      </p>
    </div>

    <div class="space-y-3 overflow-y-auto">
      <KinGradientCard
        v-for="feature in accessibleFeatures"
        :key="feature.key"
        :variant="featureVariant(feature.key)"
        padding="sm"
      >
        <div class="flex items-start gap-3">
          <div class="kin-icon-box flex-shrink-0 mt-0.5">
            <component :is="feature.icon" class="w-5 h-5" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-ink-primary">{{ feature.name }}</p>
            <p class="text-xs text-ink-secondary mt-1 leading-relaxed">{{ feature.explainer }}</p>
          </div>
        </div>
      </KinGradientCard>

      <!-- Locked features -->
      <KinFlatCard
        v-for="feature in lockedFeatures"
        :key="feature.key"
        padding="sm"
        class="opacity-50"
      >
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-lg bg-surface-sunken flex items-center justify-center flex-shrink-0 mt-0.5">
            <LockClosedIcon class="w-5 h-5 text-ink-tertiary" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-ink-tertiary">{{ feature.name }}</p>
            <p class="text-xs text-ink-tertiary mt-1">Управляется родителями.</p>
          </div>
        </div>
      </KinFlatCard>
    </div>

    <p v-if="accessibleFeatures.length === 0" class="text-sm text-ink-secondary text-center mt-4">
      Родители ещё не настроили функции. Они могут сделать это в «Настройках».
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  CalendarDaysIcon,
  ClipboardDocumentListIcon,
  TrophyIcon,
  StarIcon,
  ChatBubbleLeftRightIcon,
  LockClosedIcon,
} from '@heroicons/vue/24/outline'
import KinFlatCard from '@/components/design-system/KinFlatCard.vue'
import KinGradientCard from '@/components/design-system/KinGradientCard.vue'

const authStore = useAuthStore()

const variantMap = {
  calendar: 'sun',
  tasks: 'mint',
  points: 'warm',
  badges: 'sun',
  chat: 'cool',
  vault: 'lavender',
}
function featureVariant(key) {
  return variantMap[key] || 'iridescent'
}

const allFeatures = [
  {
    key: 'calendar',
    name: 'Календарь',
    icon: CalendarDaysIcon,
    explainer: 'Весь график семьи — в одном месте. События каждого участника выделены своим цветом, чтобы сразу было видно, у кого что запланировано.',
  },
  {
    key: 'tasks',
    name: 'Задачи',
    icon: ClipboardDocumentListIcon,
    explainer: 'Смотрите, что нужно сделать, отмечайте задачи выполненными и видите, что назначено вам. Задачи организованы по тегам — нажмите на тег, чтобы отфильтровать список.',
  },
  {
    key: 'points',
    name: 'Баллы и награды',
    icon: TrophyIcon,
    explainer: 'Получайте баллы за выполненные задачи. Баллы копятся в общем банке, и их можно тратить в магазине наград. Загляните в рейтинг, чтобы узнать своё место.',
  },
  {
    key: 'badges',
    name: 'Достижения',
    icon: StarIcon,
    explainer: 'Открывайте значки за достижения: выполняйте задачи, держите серии, зарабатывайте баллы. Некоторые значки скрыты, пока вы их не обнаружите.',
  },
  {
    key: 'chat',
    name: 'Ассистент',
    icon: ChatBubbleLeftRightIcon,
    explainer: 'Спрашивайте ИИ о расписании семьи, задачах или сохранённой информации. Например: «Что у нас на эти выходные?» или «Какие задачи нужно сделать сегодня?»',
  },
  {
    key: 'vault',
    name: 'Хранилище',
    icon: LockClosedIcon,
    explainer: 'Доступ к важной семейной информации, которой с вами поделились: пароли от Wi-Fi, данные аккаунтов или контакты для экстренных случаев.',
  },
]

const accessibleFeatures = computed(() => {
  return allFeatures.filter(f => authStore.userCanAccessModule(f.key))
})

const lockedFeatures = computed(() => {
  return allFeatures.filter(f => !authStore.userCanAccessModule(f.key))
})
</script>
