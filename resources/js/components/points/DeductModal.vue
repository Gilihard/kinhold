<template>
  <KinModalSheet :model-value="show" title="Списать баллы" size="sm" @close="$emit('close')">
    <div class="space-y-4">
      <KinSelect
        v-model="selectedUser"
        label="Участник"
        placeholder="Выберите участника…"
        :options="memberOptions"
      />

      <KinInput
        v-model.number="points"
        type="number"
        label="Баллы"
        placeholder="5"
      />

      <KinInput
        v-model="reason"
        type="text"
        label="Причина"
        placeholder="Причина списания…"
      />
    </div>

    <template #actions>
      <div class="flex justify-end gap-2">
        <KinButton variant="ghost" @click="$emit('close')">Отмена</KinButton>
        <KinButton
          variant="danger"
          :disabled="!selectedUser || !points || !reason.trim()"
          @click="submit"
        >
          Списать
        </KinButton>
      </div>
    </template>
  </KinModalSheet>
</template>

<script setup>
import { ref, computed } from 'vue'
import KinModalSheet from '@/components/design-system/KinModalSheet.vue'
import KinInput from '@/components/design-system/KinInput.vue'
import KinSelect from '@/components/design-system/KinSelect.vue'
import KinButton from '@/components/design-system/KinButton.vue'

const props = defineProps({
  show: Boolean,
  members: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'deduct'])

const selectedUser = ref('')
const points = ref(null)
const reason = ref('')

const memberOptions = computed(() =>
  props.members.map((m) => ({ value: m.id, label: m.name }))
)

const submit = () => {
  if (!selectedUser.value || !points.value || !reason.value.trim()) return
  emit('deduct', {
    userId: selectedUser.value,
    points: points.value,
    reason: reason.value.trim(),
  })
  selectedUser.value = ''
  points.value = null
  reason.value = ''
}
</script>
