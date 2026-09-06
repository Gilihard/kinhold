<template>
  <KinModalSheet :model-value="modelValue" title="Ещё" size="md" @update:model-value="$emit('update:modelValue', $event)">
    <ul class="divide-y divide-border-subtle">
      <li v-for="item in visibleItems" :key="item.key">
        <RouterLink
          :to="item.to"
          class="flex items-center gap-3 py-3 text-ink-primary hover:bg-surface-sunken rounded-md px-2"
          @click="$emit('update:modelValue', false)"
        >
          <component :is="item.icon" class="w-5 h-5 text-ink-secondary" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </li>
      <li>
        <button
          type="button"
          class="flex items-center gap-3 py-3 w-full text-status-failed hover:bg-status-failed/10 rounded-md px-2"
          @click="handleLogout"
        >
          <ArrowRightOnRectangleIcon class="w-5 h-5" />
          <span>Выйти</span>
        </button>
      </li>
    </ul>
  </KinModalSheet>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import KinModalSheet from '@/components/design-system/KinModalSheet.vue'
import {
  TrophyIcon,
  GiftIcon,
  ShieldCheckIcon,
  LockClosedIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  excludeKeys: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

const router = useRouter()
const authStore = useAuthStore()
const { enabledModules } = storeToRefs(authStore)

const ALL_ITEMS = [
  { key: 'points',   label: 'Баллы',   to: '/points',         icon: TrophyIcon,               module: 'points' },
  { key: 'rewards',  label: 'Награды',  to: '/points/rewards', icon: GiftIcon,                 module: 'points' },
  { key: 'badges',   label: 'Достижения', to: '/badges',     icon: ShieldCheckIcon,          module: 'badges' },
  { key: 'vault',    label: 'Хранилище',    to: '/vault',          icon: LockClosedIcon,           module: 'vault' },
  { key: 'settings', label: 'Настройки', to: '/settings',       icon: Cog6ToothIcon,            module: null },
]

const visibleItems = computed(() =>
  ALL_ITEMS.filter((item) =>
    !props.excludeKeys.includes(item.key) &&
    (!item.module || enabledModules.value[item.module] !== false)
  )
)

const handleLogout = async () => {
  await authStore.logout()
  emit('update:modelValue', false)
  router.push('/login')
}
</script>
