<template>
  <div class="flex-1 flex flex-col">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-heading font-bold text-ink-primary mb-2">
        Организуйте задачи с помощью тегов
      </h1>
      <p class="text-base text-ink-secondary">
        Теги помогают фильтровать и группировать задачи. Выберите несколько, чтобы начать.
      </p>
    </div>

    <!-- How it works -->
    <KinFlatCard padding="sm" class="mb-6 bg-surface-sunken">
      <p class="text-xs text-ink-secondary leading-relaxed">
        У каждой задачи может быть один или несколько тегов. Используйте панель тегов вверху страницы задач, чтобы быстро отфильтровать нужное: нажмите «Продукты», чтобы открыть список покупок, или «Домашние дела», чтобы увидеть домашние задачи.
      </p>
    </KinFlatCard>

    <div class="grid grid-cols-2 gap-3">
      <button
        v-for="preset in presets"
        :key="preset.name"
        class="p-4 rounded-card border-2 transition-all duration-200 text-left cursor-pointer"
        :class="isSelected(preset.name)
          ? 'border-accent-lavender-bold bg-accent-lavender-soft/40'
          : 'border-border-subtle bg-surface-raised hover:border-border-strong'"
        @click="togglePreset(preset.name)"
      >
        <div class="flex items-center gap-2 mb-1">
          <span
            class="w-2.5 h-2.5 rounded-full flex-shrink-0"
            :style="{ backgroundColor: preset.color }"
          ></span>
          <p class="text-sm font-semibold text-ink-primary">{{ preset.name }}</p>
        </div>
        <p class="text-xs text-ink-secondary">{{ preset.description }}</p>
      </button>
    </div>

    <p v-if="error" class="text-sm text-status-failed text-center mt-4">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, inject, onMounted } from 'vue'
import { useOnboardingStore } from '@/stores/onboarding'
import api from '@/services/api'
import KinFlatCard from '@/components/design-system/KinFlatCard.vue'

const store = useOnboardingStore()
const { setStepLoading, registerContinue } = inject('onboarding')
const error = ref('')

const presets = [
  { name: 'Продукты', color: '#5B8C6A', description: 'Список продуктов на неделю' },
  { name: 'Домашние дела', color: '#C48B3F', description: 'Задачи по дому' },
  { name: 'Школа', color: '#5B7B9C', description: 'Уроки и школьные события' },
  { name: 'Дом', color: '#C45B5B', description: 'Ремонт и проекты' },
  { name: 'Еда', color: '#C4975A', description: 'Планирование и приготовление еды' },
  { name: 'Поручения', color: '#7B6B9C', description: 'Дела вне дома' },
]

// Track tags that already exist on the family so we (a) don't try to create
// duplicates on Continue and (b) show them as selected in the UI when the user
// re-runs onboarding from settings (#260).
const existingTagNames = ref(new Set())

function isSelected(name) {
  return store.selectedPresets.has(name) || existingTagNames.value.has(name)
}

function togglePreset(name) {
  // Tags that already exist on the family are read-only — toggling them off
  // would imply deletion, which onboarding shouldn't do.
  if (existingTagNames.value.has(name)) return

  const updated = new Set(store.selectedPresets)
  if (updated.has(name)) {
    updated.delete(name)
  } else {
    updated.add(name)
  }
  store.selectedPresets = updated
}

registerContinue(async () => {
  if (store.selectedPresets.size === 0) return true

  setStepLoading(true)
  error.value = ''
  try {
    for (const presetName of store.selectedPresets) {
      // Skip presets that already exist on the family (re-running onboarding).
      if (existingTagNames.value.has(presetName)) continue
      const preset = presets.find(p => p.name === presetName)
      if (preset) {
        await api.post('/tags', {
          name: preset.name,
          color: preset.color,
          scope: 'task',
        })
      }
    }
    return true
  } catch (err) {
    error.value = err.response?.data?.message || 'Не удалось создать теги.'
    return false
  } finally {
    setStepLoading(false)
  }
})

onMounted(async () => {
  try {
    const res = await api.get('/tags', { params: { scope: 'task' } })
    const tags = res.data?.data || res.data || []
    existingTagNames.value = new Set(tags.map(t => t.name))
  } catch {
    // Non-fatal — leave the existing-tag set empty and let the user start fresh.
  }
})
</script>
