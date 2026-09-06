<template>
  <div class="sticky top-0 z-20 bg-surface-raised/90 backdrop-blur-sm border-b border-border-subtle px-4 py-3 -mx-4 md:-mx-6 mb-4 flex items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <PencilSquareIcon class="w-5 h-5 text-accent-lavender-bold" />
      <span class="text-sm font-semibold text-ink-primary">Редактирование дашборда</span>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <KinButton variant="secondary" size="sm" @click="$emit('add')">
        <template #leading>
          <PlusIcon class="w-4 h-4" />
        </template>
        Добавить виджет
      </KinButton>
      <KinButton variant="ghost" size="sm" @click="$emit('cancel')">
        Отмена
      </KinButton>
      <KinButton variant="primary" size="sm" :loading="saving" @click="$emit('save')">
        {{ saving ? 'Сохранение…' : 'Сохранить' }}
      </KinButton>
    </div>
  </div>
</template>

<script setup>
import { PencilSquareIcon, PlusIcon } from '@heroicons/vue/24/outline'
import KinButton from '@/components/design-system/KinButton.vue'

defineProps({
  saving: { type: Boolean, default: false },
})

defineEmits(['add', 'cancel', 'save'])
</script>
