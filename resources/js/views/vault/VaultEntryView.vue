<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="px-4 pt-3 pb-1 md:px-6 md:pt-6 md:pb-2">
      <div class="flex items-center gap-3">
        <button
          class="p-2 -ml-2 text-ink-tertiary hover:text-ink-primary hover:bg-surface-sunken rounded-xl transition-colors"
          @click="$router.back()"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <div class="flex-1 min-w-0">
          <h1 class="text-base md:text-xl font-bold font-heading text-ink-primary truncate">{{ currentEntry?.title || 'Запись' }}</h1>
          <p v-if="categoryName" class="hidden md:block text-xs text-ink-tertiary mt-0.5">{{ categoryName }}</p>
        </div>

        <ContextMenu v-if="isParent && currentEntry" :items="entryMenuItems" />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-16">
      <LoadingSpinner size="lg" />
    </div>

    <!-- Entry Content -->
    <div v-else-if="currentEntry" class="flex-1 overflow-y-auto px-4 md:px-6 pb-32 md:pb-6">
      <!-- Markdown Body -->
      <div
        v-if="entryBody"
        class="bg-surface-raised rounded-xl border border-border-subtle shadow-card overflow-hidden mt-2"
      >
        <div class="px-4 py-3 prose-vault" v-html="renderedBody"></div>
      </div>

      <!-- Legacy: flat key/value data (for old entries without body) -->
      <div
        v-else-if="legacyFields && Object.keys(legacyFields).length > 0"
        class="bg-surface-raised rounded-xl border border-border-subtle shadow-card overflow-hidden mt-2"
      >
        <div class="px-4 py-3 border-b border-border-subtle">
          <h2 class="text-xs font-semibold text-ink-tertiary uppercase tracking-wider">Информация</h2>
        </div>
        <div class="divide-y divide-border-subtle px-4">
          <SensitiveField
            v-for="(value, key) in legacyFields"
            :key="key"
            :label="formatKey(key)"
            :value="value"
            :sensitive="isSensitiveField(key)"
          />
        </div>
      </div>

      <!-- Sensitive Fields -->
      <div
        v-if="sensitiveFields && Object.keys(sensitiveFields).length > 0"
        class="bg-surface-raised rounded-xl border border-border-subtle shadow-card overflow-hidden mt-4"
      >
        <div class="px-4 py-3 border-b border-border-subtle flex items-center gap-2">
          <LockClosedIcon class="w-3.5 h-3.5 text-ink-tertiary" />
          <h2 class="text-xs font-semibold text-ink-tertiary uppercase tracking-wider">Конфиденциальная информация</h2>
        </div>
        <div class="divide-y divide-border-subtle px-4">
          <SensitiveField
            v-for="(value, key) in sensitiveFields"
            :key="key"
            :label="formatKey(key)"
            :value="value"
            :sensitive="true"
          />
        </div>
      </div>

      <!-- Notes (legacy — shown only if present and no body) -->
      <div v-if="currentEntry.notes && !entryBody" class="bg-surface-raised rounded-xl border border-border-subtle shadow-card overflow-hidden mt-4">
        <div class="px-4 py-3 border-b border-border-subtle">
          <h2 class="text-xs font-semibold text-ink-tertiary uppercase tracking-wider">Заметки</h2>
        </div>
        <div class="px-4 py-3">
          <p class="text-sm text-ink-secondary whitespace-pre-wrap">{{ currentEntry.notes }}</p>
        </div>
      </div>

      <!-- Documents -->
      <div v-if="isParent || currentEntry.documents?.length > 0" class="bg-surface-raised rounded-xl border border-border-subtle shadow-card overflow-hidden mt-4">
        <div class="px-4 py-3 border-b border-border-subtle flex items-center justify-between">
          <h2 class="text-xs font-semibold text-ink-tertiary uppercase tracking-wider">Документы</h2>
          <label v-if="isParent && !isDemoFamily" class="flex items-center gap-1.5 text-xs font-medium text-accent-lavender-bold hover:opacity-80 cursor-pointer transition-colors">
            <ArrowUpTrayIcon class="w-3.5 h-3.5" />
            Загрузить
            <input
              ref="fileInput"
              type="file"
              class="hidden"
              accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt,.csv"
              @change="handleFileUpload"
            />
          </label>
        </div>
        <div v-if="currentEntry.documents?.length > 0" class="divide-y divide-border-subtle">
          <div
            v-for="doc in currentEntry.documents"
            :key="doc.id"
            class="flex items-center gap-3 px-4 py-3"
          >
            <button
              type="button"
              class="flex-1 flex items-center gap-3 min-w-0 hover:bg-surface-sunken transition-colors rounded -ml-2 pl-2 py-1 text-left"
              @click="handleDocumentDownload(doc)"
            >
              <DocumentTextIcon class="w-5 h-5 text-ink-tertiary flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-ink-primary truncate">{{ doc.original_filename || doc.filename }}</p>
                <p class="text-xs text-ink-tertiary">{{ formatFileSize(doc.size) }}</p>
              </div>
              <ArrowDownTrayIcon class="w-4 h-4 text-ink-tertiary flex-shrink-0" />
            </button>
            <button
              v-if="isParent && !isDemoFamily"
              type="button"
              class="p-1.5 rounded hover:bg-status-failed/10 transition-colors flex-shrink-0"
              title="Удалить документ"
              @click="documentToDelete = doc; showDeleteDocConfirm = true"
            >
              <TrashIcon class="w-4 h-4 text-status-failed hover:opacity-80" />
            </button>
          </div>
        </div>
        <div v-else class="px-4 py-3">
          <p class="text-xs text-ink-tertiary">Документы пока не прикреплены.</p>
        </div>
        <div v-if="uploading" class="px-4 py-2 border-t border-border-subtle">
          <p class="text-xs text-accent-lavender-bold">Загрузка...</p>
        </div>
      </div>

      <!-- Shared With (Parent Only) -->
      <div v-if="isParent" class="bg-surface-raised rounded-xl border border-border-subtle shadow-card overflow-hidden mt-4">
        <div class="px-4 py-3 border-b border-border-subtle flex items-center justify-between">
          <h2 class="text-xs font-semibold text-ink-tertiary uppercase tracking-wider">Доступ</h2>
          <button
            class="flex items-center gap-1.5 text-xs font-medium text-accent-lavender-bold hover:opacity-80 transition-colors"
            @click="showShareModal = true"
          >
            <ShareIcon class="w-3.5 h-3.5" />
            Поделиться
          </button>
        </div>
        <div v-if="currentEntry.permissions?.length > 0" class="divide-y divide-border-subtle">
          <div
            v-for="perm in currentEntry.permissions"
            :key="perm.user_id"
            class="flex items-center gap-3 px-4 py-3"
          >
            <UserAvatar :user="perm.user" size="sm" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-ink-primary">{{ perm.user?.name }}</p>
              <p class="text-xs text-ink-tertiary capitalize">{{ permissionLevelLabel(perm.permission_level) }}</p>
            </div>
            <button
              class="p-1.5 text-ink-tertiary hover:text-status-failed hover:bg-status-failed/10 rounded-lg transition-colors"
              @click="handleRemovePermission(perm.user_id)"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div v-else class="px-4 py-3">
          <p class="text-xs text-ink-tertiary">Доступ пока никому не открыт.</p>
        </div>
      </div>

      <!-- Metadata -->
      <div class="mt-4 px-1">
        <p class="text-xs text-ink-tertiary">
          Создана {{ formatDate(currentEntry.created_at) }}
          <span v-if="currentEntry.updated_at !== currentEntry.created_at">
            &middot; Обновлена {{ formatDate(currentEntry.updated_at) }}
          </span>
        </p>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <ConfirmDialog
      :show="showDeleteConfirm"
      title="Удалить запись?"
      :message="`Запись «${currentEntry?.title}» и все её данные будут удалены навсегда.`"
      confirm-text="Удалить"
      @confirm="handleDeleteEntry"
      @cancel="showDeleteConfirm = false"
    />

    <!-- Delete Document Confirmation -->
    <ConfirmDialog
      :show="showDeleteDocConfirm"
      title="Удалить документ?"
      :message="`Документ «${documentToDelete?.original_filename || 'этот документ'}» будет удалён навсегда.`"
      confirm-text="Удалить"
      @confirm="handleDeleteDocument"
      @cancel="showDeleteDocConfirm = false; documentToDelete = null"
    />

    <!-- Share Modal -->
    <KinModalSheet
      :model-value="showShareModal"
      title="Поделиться записью"
      @close="showShareModal = false"
    >
      <div class="space-y-4">
        <KinSelect
          v-model="shareForm.userId"
          label="Член семьи"
          :options="[{ value: null, label: 'Выберите члена семьи…' }, ...shareableMembers.map((m) => ({ value: m.id, label: `${m.name} (${roleLabel(m.family_role || m.role)})` }))]"
        />

        <KinSelect
          v-model="shareForm.level"
          label="Уровень доступа"
          :options="[{ value: 'view', label: 'Только просмотр' }, { value: 'edit', label: 'Может редактировать' }]"
        />

        <div class="flex gap-2 pt-2">
          <KinButton type="button" variant="secondary" size="md" class="flex-1" @click="showShareModal = false">
            Отмена
          </KinButton>
          <KinButton
            variant="primary"
            size="md"
            class="flex-1"
            :disabled="!shareForm.userId || sharingEntry"
            @click="handleShareEntry"
          >
            {{ sharingEntry ? 'Открытие доступа…' : 'Поделиться' }}
          </KinButton>
        </div>
      </div>
    </KinModalSheet>

    <!-- Edit Entry Modal -->
    <KinModalSheet
      :model-value="showEditModal"
      title="Изменить запись"
      size="xl"
      @close="showEditModal = false"
    >
      <form class="space-y-5" @submit.prevent="handleUpdateEntry">
        <div class="flex gap-3">
          <KinInput
            v-model="editForm.title"
            label="Название"
            placeholder="Название записи"
            class="flex-1"
          />
          <KinSelect
            v-model="editForm.vault_category_id"
            label="Категория"
            class="w-40"
            :options="[{ value: null, label: 'Выберите…' }, ...categories.map((c) => ({ value: c.id, label: c.name }))]"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-ink-primary mb-1.5">Содержимое</label>
          <MarkdownEditor
            v-model="editForm.body"
            placeholder="Начните вводить текст…"
          />
        </div>

        <!-- Sensitive Fields (collapsible) -->
        <div>
          <button
            type="button"
            class="flex items-center gap-1.5 text-xs font-medium text-ink-tertiary hover:text-accent-lavender-bold transition-colors"
            @click="showEditSensitiveFields = !showEditSensitiveFields"
          >
            <LockClosedIcon class="w-3.5 h-3.5" />
            {{ showEditSensitiveFields ? 'Скрыть' : 'Показать' }} секретные поля
            <ChevronRightIcon class="w-3 h-3 transition-transform" :class="{ 'rotate-90': showEditSensitiveFields }" />
          </button>

          <div v-if="showEditSensitiveFields" class="mt-3 space-y-3">
            <div v-for="(field, i) in editForm.sensitiveFields" :key="i" class="flex gap-2">
              <input
                v-model="field.key"
                placeholder="Название (например, пароль)"
                class="input-base flex-1"
              />
              <input
                v-model="field.value"
                placeholder="Значение"
                type="password"
                class="input-base flex-1"
              />
              <button
                type="button"
                class="p-2 text-ink-tertiary hover:text-status-failed transition-colors"
                @click="editForm.sensitiveFields.splice(i, 1)"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
            <button
              type="button"
              class="flex items-center gap-1.5 text-xs font-medium text-accent-lavender-bold hover:opacity-80"
              @click="editForm.sensitiveFields.push({ key: '', value: '' })"
            >
              <PlusIcon class="w-3.5 h-3.5" />
              Добавить поле
            </button>
          </div>
        </div>

        <div class="flex gap-2 pt-2">
          <KinButton type="button" variant="secondary" size="md" class="flex-1" @click="showEditModal = false">
            Отмена
          </KinButton>
          <KinButton type="submit" variant="primary" size="md" class="flex-1" :disabled="!editForm.title?.trim() || savingEntry">
            {{ savingEntry ? 'Сохранение…' : 'Сохранить изменения' }}
          </KinButton>
        </div>
      </form>
    </KinModalSheet>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { marked } from 'marked'
import DOMPurify from 'dompurify'
import api from '@/services/api'
import { useVaultStore } from '@/stores/vault'
import { useAuthStore } from '@/stores/auth'
import { useNotification } from '@/composables/useNotification'
import SensitiveField from '@/components/vault/SensitiveField.vue'
import MarkdownEditor from '@/components/vault/MarkdownEditor.vue'
import ContextMenu from '@/components/common/ContextMenu.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import UserAvatar from '@/components/common/UserAvatar.vue'
import KinButton from '@/components/design-system/KinButton.vue'
import KinInput from '@/components/design-system/KinInput.vue'
import KinSelect from '@/components/design-system/KinSelect.vue'
import KinModalSheet from '@/components/design-system/KinModalSheet.vue'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  LockClosedIcon,
  PencilIcon,
  PlusIcon,
  ShareIcon,
  TrashIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const vaultStore = useVaultStore()
const authStore = useAuthStore()
const { success, error: notifyError } = useNotification()

const { currentEntry, isLoading, categories } = storeToRefs(vaultStore)
const { isParent, familyMembers, family } = storeToRefs(authStore)
const isDemoFamily = computed(() => family.value?.slug === 'q32-demo-family')

const entryId = route.params.id
const showDeleteConfirm = ref(false)
const showEditModal = ref(false)
const showEditSensitiveFields = ref(false)
const savingEntry = ref(false)
const showShareModal = ref(false)
const sharingEntry = ref(false)
const uploading = ref(false)
const fileInput = ref(null)
const showDeleteDocConfirm = ref(false)
const documentToDelete = ref(null)

const shareForm = ref({ userId: null, level: 'view' })

const editForm = ref({
  vault_category_id: null,
  title: '',
  body: '',
  sensitiveFields: [{ key: '', value: '' }],
})

// Parse entry data — supports new format (body + sensitive_fields) and legacy (flat key/value)
const entryBody = computed(() => currentEntry.value?.data?.body || '')
const sensitiveFields = computed(() => currentEntry.value?.data?.sensitive_fields || {})
const legacyFields = computed(() => {
  const data = currentEntry.value?.data
  if (!data || data.body !== undefined) return null
  // Legacy format: flat key/value pairs (no body key)
  return data
})

const renderedBody = computed(() => {
  if (!entryBody.value) return ''
  return DOMPurify.sanitize(marked.parse(entryBody.value))
})

const categoryName = computed(() => {
  if (!currentEntry.value) return ''
  const catId = currentEntry.value.vault_category_id || currentEntry.value.category?.id
  const cat = categories.value.find((c) => c.id === catId)
  return cat?.name || currentEntry.value.category?.name || ''
})

const entryMenuItems = computed(() => [
  { label: 'Изменить', icon: PencilIcon, action: openEditModal },
  { divider: true },
  { label: 'Удалить', icon: TrashIcon, variant: 'danger', action: () => { showDeleteConfirm.value = true } },
])

const openEditModal = () => {
  if (!currentEntry.value) return
  const data = currentEntry.value.data || {}
  const catId = currentEntry.value.vault_category_id || currentEntry.value.category?.id

  // Handle both new format (body + sensitive_fields) and legacy (flat kv)
  const isNewFormat = data.body !== undefined
  const body = isNewFormat
    ? (data.body || '')
    : Object.entries(data).map(([key, value]) => `**${formatKey(key)}:** ${value}`).join('\n\n')

  const sensitiveFieldsList = isNewFormat && data.sensitive_fields
    ? Object.entries(data.sensitive_fields).map(([key, value]) => ({ key, value: String(value) }))
    : []

  if (sensitiveFieldsList.length === 0) {
    sensitiveFieldsList.push({ key: '', value: '' })
  }

  editForm.value = {
    vault_category_id: catId || null,
    title: currentEntry.value.title || '',
    body,
    sensitiveFields: sensitiveFieldsList,
  }
  showEditSensitiveFields.value = sensitiveFieldsList.some((f) => f.key.trim())
  showEditModal.value = true
}

const handleUpdateEntry = async () => {
  if (!editForm.value.title?.trim()) return
  savingEntry.value = true

  const sensitiveFieldsObj = {}
  editForm.value.sensitiveFields.forEach((f) => {
    if (f.key?.trim() && f.value?.trim()) {
      sensitiveFieldsObj[f.key.trim()] = f.value.trim()
    }
  })

  const payload = {
    vault_category_id: editForm.value.vault_category_id,
    title: editForm.value.title,
    data: {
      body: editForm.value.body || '',
      sensitive_fields: Object.keys(sensitiveFieldsObj).length > 0 ? sensitiveFieldsObj : undefined,
    },
  }

  const result = await vaultStore.updateEntry(entryId, payload)
  if (result.success) {
    success('Запись обновлена!')
    showEditModal.value = false
    await vaultStore.fetchEntry(entryId)
  } else {
    notifyError(result.error || 'Не удалось обновить запись')
  }
  savingEntry.value = false
}

const isSensitiveField = (key) => {
  const sensitivePatterns = ['ssn', 'password', 'pin', 'account', 'routing', 'secret', 'key', 'card', 'cvv', 'security']
  return sensitivePatterns.some((p) => key.toLowerCase().includes(p))
}

const formatKey = (key) =>
  key.replace(/_/g, ' ').split(' ').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }).replace(/ г\.$/, '')
}

const formatFileSize = (bytes) => {
  if (!bytes) return ''
  const k = 1024
  const sizes = ['Б', 'КБ', 'МБ', 'ГБ']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const roleLabels = { parent: 'Родитель', teen: 'Подросток', kid: 'Ребёнок' }
const roleLabel = (role) => roleLabels[String(role || '').toLowerCase()] || role

const permissionLevelLabel = (level) => (level === 'edit' ? 'Может редактировать' : 'Только просмотр')

// Members who don't already have permission on this entry
const shareableMembers = computed(() => {
  const existingUserIds = (currentEntry.value?.permissions || []).map((p) => p.user_id)
  return (familyMembers.value || []).filter((m) => !existingUserIds.includes(m.id))
})

const handleShareEntry = async () => {
  if (!shareForm.value.userId) return
  sharingEntry.value = true
  const result = await vaultStore.grantPermission(entryId, shareForm.value.userId, shareForm.value.level)
  if (result.success) {
    success('Доступ к записи открыт!')
    showShareModal.value = false
    shareForm.value = { userId: null, level: 'view' }
  } else {
    notifyError(result.error || 'Не удалось открыть доступ')
  }
  sharingEntry.value = false
}

const handleDeleteDocument = async () => {
  const doc = documentToDelete.value
  if (!doc) return
  const result = await vaultStore.deleteDocument(doc.id)
  if (result.success) {
    success('Документ удалён')
  } else {
    notifyError(result.error || 'Не удалось удалить документ')
  }
  showDeleteDocConfirm.value = false
  documentToDelete.value = null
}

const handleDocumentDownload = async (doc) => {
  try {
    const response = await api.get(doc.download_url, { responseType: 'blob' })
    const url = window.URL.createObjectURL(response.data)
    const link = document.createElement('a')
    link.href = url
    link.download = doc.original_filename || 'download'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch {
    notifyError('Не удалось скачать документ')
  }
}

const handleFileUpload = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  uploading.value = true
  const result = await vaultStore.uploadDocument(entryId, file)
  if (result.success) {
    success('Документ загружен!')
  } else {
    notifyError(result.error || 'Не удалось загрузить документ')
  }
  uploading.value = false
  if (fileInput.value) fileInput.value.value = ''
}

const handleDeleteEntry = async () => {
  const result = await vaultStore.deleteEntry(entryId)
  if (result.success) {
    success('Запись удалена!')
    router.push('/vault')
  } else {
    notifyError(result.error || 'Не удалось удалить')
  }
  showDeleteConfirm.value = false
}

const handleRemovePermission = async (userId) => {
  const result = await vaultStore.revokePermission(entryId, userId)
  if (result.success) {
    success('Доступ закрыт!')
  }
}

onMounted(async () => {
  if (categories.value.length === 0) {
    await vaultStore.fetchCategories()
  }
  await vaultStore.fetchEntry(entryId)
})
</script>

<style>
/* Rendered markdown body styling */
.prose-vault h1 { @apply text-xl font-bold font-heading mb-3 mt-4 first:mt-0 text-ink-primary; }
.prose-vault h2 { @apply text-lg font-bold font-heading mb-2 mt-3 first:mt-0 text-ink-primary; }
.prose-vault h3 { @apply text-base font-semibold mb-2 mt-3 first:mt-0 text-ink-primary; }
.prose-vault p { @apply mb-2 last:mb-0 leading-relaxed text-sm text-ink-secondary; }
.prose-vault strong { @apply font-semibold text-ink-primary; }
.prose-vault em { @apply italic; }
.prose-vault a { @apply text-accent-lavender-bold underline; }
.prose-vault ul { @apply list-disc pl-6 mb-2 text-sm text-ink-secondary; }
.prose-vault ol { @apply list-decimal pl-6 mb-2 text-sm text-ink-secondary; }
.prose-vault li { @apply mb-1; }
.prose-vault code { @apply text-xs bg-surface-sunken px-1.5 py-0.5 rounded font-mono; }
.prose-vault pre { @apply bg-surface-sunken rounded-lg p-3 mb-2 overflow-x-auto; }
.prose-vault pre code { @apply bg-transparent px-0 py-0; }
.prose-vault table { @apply w-full border-collapse mb-2; }
.prose-vault th { @apply bg-surface-sunken text-left px-3 py-2 text-xs font-semibold uppercase tracking-wider border border-border-subtle; }
.prose-vault td { @apply px-3 py-2 text-sm border border-border-subtle; }
.prose-vault blockquote { @apply border-l-4 border-accent-lavender-bold pl-4 italic text-ink-tertiary mb-2; }
.prose-vault hr { @apply border-border-subtle my-4; }
</style>
