<template>
  <KinModalSheet
    :model-value="show"
    title="Импорт рецепта"
    size="lg"
    @update:model-value="(v) => !v && handleClose()"
  >
    <!-- Tab switcher (only before preview) -->
    <div v-if="!previewData" class="flex gap-1 mb-4 border-b border-border-subtle -mx-6 px-6">
      <button
        class="px-4 py-2.5 text-sm font-medium transition-colors relative"
        :class="activeTab === 'url'
          ? 'text-[#C4975A]'
          : 'text-ink-tertiary hover:text-ink-primary'"
        @click="activeTab = 'url'"
      >
        По ссылке
        <span v-if="activeTab === 'url'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#C4975A] rounded-full"></span>
      </button>
      <button
        class="px-4 py-2.5 text-sm font-medium transition-colors relative"
        :class="activeTab === 'photo'
          ? 'text-[#C4975A]'
          : 'text-ink-tertiary hover:text-ink-primary'"
        @click="activeTab = 'photo'"
      >
        По фото
        <span v-if="activeTab === 'photo'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#C4975A] rounded-full"></span>
      </button>
    </div>

    <!-- URL import tab -->
    <div v-if="activeTab === 'url' && !previewData">
      <div class="space-y-4">
        <KinInput
          v-model="importUrl"
          type="url"
          label="Ссылка на рецепт"
          placeholder="https://example.com/recipe/..."
          @keydown.enter.prevent="handleImportUrl"
        />

        <!-- Duplicate warning -->
        <div
          v-if="duplicateWarning"
          class="flex items-start gap-2 p-3 bg-[#C48B3F]/10 border border-[#C48B3F]/20 rounded-xl"
        >
          <ExclamationTriangleIcon class="w-5 h-5 text-[#C48B3F] flex-shrink-0 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-[#C48B3F]">Обнаружен дубликат</p>
            <p class="text-ink-secondary">
              С этой ссылки у вас уже импортирован рецепт «{{ duplicateWarning.title }}».
              Вы всё равно можете сохранить его как новый рецепт.
            </p>
          </div>
        </div>

        <!-- Error -->
        <div
          v-if="importError"
          class="flex items-start gap-2 p-3 bg-status-failed/10 border border-status-failed/20 rounded-xl"
        >
          <ExclamationCircleIcon class="w-5 h-5 text-status-failed flex-shrink-0 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-status-failed">Не удалось импортировать</p>
            <p class="text-ink-secondary">{{ importError }}</p>
          </div>
        </div>

        <p class="text-xs text-ink-tertiary">
          Большинство кулинарных сайтов импортируется бесплатно. Для страниц без структурированных данных используется ИИ (учитывается в использовании API).
        </p>

        <div class="flex gap-3">
          <KinButton
            variant="primary"
            :disabled="!importUrl || importing"
            :loading="importing"
            @click="handleImportUrl"
          >
            {{ importing ? 'Импорт…' : 'Предпросмотр рецепта' }}
          </KinButton>
        </div>
      </div>
    </div>

    <!-- Photo import tab -->
    <div v-if="activeTab === 'photo' && !previewData">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-ink-primary mb-1">Фото рецепта</label>
          <div
            class="border-2 border-dashed border-border-subtle rounded-xl p-8 text-center hover:border-[#C4975A] transition-colors cursor-pointer"
            @click="$refs.photoInput.click()"
          >
            <CameraIcon class="w-8 h-8 mx-auto text-ink-tertiary mb-2" />
            <p class="text-sm text-ink-tertiary">
              {{ selectedFile ? selectedFile.name : 'Нажмите, чтобы загрузить фото рецепта' }}
            </p>
            <p class="text-xs text-ink-tertiary mt-1">JPEG, PNG или HEIC · Макс. 10 МБ · Используется ИИ (учитывается в использовании API)</p>
          </div>
          <input
            ref="photoInput"
            type="file"
            accept="image/jpeg,image/png,image/heic,image/heif"
            class="hidden"
            @change="handleFileSelect"
          />
        </div>

        <!-- Error -->
        <div
          v-if="importError"
          class="flex items-start gap-2 p-3 bg-status-failed/10 border border-status-failed/20 rounded-xl"
        >
          <ExclamationCircleIcon class="w-5 h-5 text-status-failed flex-shrink-0 mt-0.5" />
          <div class="text-sm">
            <p class="font-medium text-status-failed">Не удалось импортировать</p>
            <p class="text-ink-secondary">{{ importError }}</p>
          </div>
        </div>

        <div class="flex gap-3">
          <KinButton
            variant="primary"
            :disabled="!selectedFile || importing"
            :loading="importing"
            @click="handleImportPhoto"
          >
            {{ importing ? 'Анализ…' : 'Предпросмотр рецепта' }}
          </KinButton>
        </div>
      </div>
    </div>

    <!-- Loading overlay -->
    <div v-if="importing" class="flex flex-col items-center justify-center py-8">
      <LoadingSpinner size="lg" />
      <p class="mt-3 text-sm text-ink-tertiary">
        {{ activeTab === 'url' ? 'Извлекаем данные рецепта…' : 'Анализируем фото с помощью ИИ…' }}
      </p>
    </div>

    <!-- Preview form — editable before saving -->
    <div v-if="previewData && !importing">
      <div class="flex items-center gap-2 mb-4 p-3 bg-[#5B8C6A]/10 border border-[#5B8C6A]/20 rounded-xl">
        <CheckCircleIcon class="w-5 h-5 text-[#5B8C6A] flex-shrink-0" />
        <p class="text-sm text-[#5B8C6A]">
          Рецепт извлечён! Проверьте и отредактируйте данные ниже, затем сохраните.
        </p>
      </div>
      <RecipeForm
        ref="importFormRef"
        :initial-data="previewData"
        @save="handleSaveImported"
        @cancel="resetPreview"
      />
    </div>
  </KinModalSheet>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRecipesStore } from '@/stores/recipes'
import { useNotification } from '@/composables/useNotification'
import KinModalSheet from '@/components/design-system/KinModalSheet.vue'
import KinInput from '@/components/design-system/KinInput.vue'
import KinButton from '@/components/design-system/KinButton.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import RecipeForm from './RecipeForm.vue'
import {
  CameraIcon,
  ExclamationTriangleIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: { type: Boolean, default: false },
  initialTab: { type: String, default: 'url' },
})

const emit = defineEmits(['saved', 'close'])

const recipesStore = useRecipesStore()
const { error: notifyError } = useNotification()

const activeTab = ref(props.initialTab)
const importUrl = ref('')
const selectedFile = ref(null)
const importing = ref(false)
const importError = ref(null)
const previewData = ref(null)
const duplicateWarning = ref(null)
const importFormRef = ref(null)

watch(() => props.initialTab, (tab) => {
  activeTab.value = tab
})

const handleImportUrl = async () => {
  if (!importUrl.value) return
  importing.value = true
  importError.value = null
  duplicateWarning.value = null

  const result = await recipesStore.importFromUrl(importUrl.value)
  importing.value = false

  if (result.success) {
    const data = result.preview
    // Check for duplicate
    if (data.duplicate_of) {
      duplicateWarning.value = { id: data.duplicate_of, title: data.duplicate_title }
    }
    // Add the source URL to the preview data
    previewData.value = { ...data, source_url: importUrl.value, source_type: 'url' }
  } else {
    importError.value = result.error
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    selectedFile.value = file
    importError.value = null
  }
}

const handleImportPhoto = async () => {
  if (!selectedFile.value) return
  importing.value = true
  importError.value = null

  const result = await recipesStore.importFromPhoto(selectedFile.value)
  importing.value = false

  if (result.success) {
    previewData.value = { ...result.preview, source_type: 'photo' }
  } else {
    importError.value = result.error
  }
}

const handleSaveImported = async (formData) => {
  const result = await recipesStore.createRecipe(formData)
  if (result.success) {
    emit('saved')
  } else {
    notifyError(result.error)
    if (importFormRef.value) importFormRef.value.saving = false
  }
}

const resetPreview = () => {
  previewData.value = null
  duplicateWarning.value = null
}

const handleClose = () => {
  importUrl.value = ''
  selectedFile.value = null
  importing.value = false
  importError.value = null
  previewData.value = null
  duplicateWarning.value = null
  emit('close')
}
</script>
