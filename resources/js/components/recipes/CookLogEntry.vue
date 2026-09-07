<template>
  <KinModalSheet
    :model-value="show"
    title="Запись о приготовлении"
    size="sm"
    @update:model-value="(v) => !v && $emit('close')"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <KinInput
        v-model="form.cooked_at"
        type="date"
        label="Дата"
        required
      />

      <KinInput
        v-model.number="form.servings_made"
        type="number"
        label="Приготовлено порций"
        :min="1"
        placeholder="Необязательно"
      />

      <KinTextarea
        v-model="form.notes"
        label="Заметки"
        :rows="3"
        placeholder="Как получилось? Что-то меняли в рецепте?"
      />
    </form>

    <template #actions>
      <KinButton variant="secondary" @click="$emit('close')">Отмена</KinButton>
      <KinButton variant="primary" :loading="saving" @click="handleSubmit">
        {{ saving ? 'Сохранение…' : 'Сохранить' }}
      </KinButton>
    </template>
  </KinModalSheet>
</template>

<script setup>
import { ref, reactive } from 'vue'
import KinModalSheet from '@/components/design-system/KinModalSheet.vue'
import KinInput from '@/components/design-system/KinInput.vue'
import KinTextarea from '@/components/design-system/KinTextarea.vue'
import KinButton from '@/components/design-system/KinButton.vue'

defineProps({
  show: { type: Boolean, default: false },
  recipeId: { type: String, required: true },
})

const emit = defineEmits(['saved', 'close'])

const today = new Date().toISOString().split('T')[0]
const saving = ref(false)

const form = reactive({
  cooked_at: today,
  servings_made: null,
  notes: '',
})

const handleSubmit = () => {
  if (!form.cooked_at) return
  saving.value = true
  emit('saved', { ...form })
}

const resetForm = () => {
  form.cooked_at = today
  form.servings_made = null
  form.notes = ''
  saving.value = false
}

defineExpose({ resetForm })
</script>
