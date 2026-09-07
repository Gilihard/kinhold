<template>
  <div class="kin-page min-h-screen flex items-center justify-center p-4 py-10">
    <div class="w-full max-w-3xl">
      <!-- Logo -->
      <div class="text-center mb-8">
        <router-link to="/login" class="inline-flex flex-col items-center gap-3">
          <img src="/images/logo-100.png" alt="Kinhold" class="w-16 h-16 rounded-2xl" />
          <h1 class="text-4xl font-heading font-bold text-kin-gold">Kinhold</h1>
        </router-link>
        <p class="kin-muted mt-2">Попробуй демо</p>
      </div>

      <KinFlatCard padding="lg">
        <!-- Intro -->
        <div class="text-center mb-6">
          <h2 class="text-2xl font-heading font-bold text-ink-primary mb-2">
            Знакомьтесь — семья Эллис
          </h2>
          <p class="kin-muted max-w-xl mx-auto">
            Демо — это полностью рабочий экземпляр Kinhold с семьёй из пяти человек:
            события календаря, задачи, записи в хранилище, баллы, достижения, рецепты — всё как в жизни.
            Выбери любого члена семьи ниже, чтобы войти под ним и посмотреть на приложение его глазами.
            Переключаться между аккаунтами можно в любой момент — выйди и вернись сюда.
          </p>
        </div>

        <!-- Member picker -->
        <div v-if="demoAvailable" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
          <button
            v-for="member in members"
            :key="member.key"
            :disabled="loadingMember !== null"
            class="relative flex flex-col items-center gap-2 p-4 rounded-xl border border-border-subtle hover:border-accent-lavender-bold hover:bg-surface-sunken transition-all text-left disabled:opacity-50 disabled:cursor-not-allowed"
            @click="handleSelect(member.key)"
          >
            <!-- Loading overlay -->
            <div
              v-if="loadingMember === member.key"
              class="absolute inset-0 flex items-center justify-center bg-surface-raised/60 rounded-xl"
            >
              <LoadingSpinner size="sm" />
            </div>

            <!-- Avatar circle -->
            <div
              class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-xl"
              :style="{ backgroundColor: member.color }"
            >
              {{ member.name[0] }}
            </div>

            <!-- Name & role -->
            <div class="text-center">
              <div class="font-semibold text-sm text-ink-primary">{{ member.name }}</div>
              <span
                class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full"
                :class="
                  member.role === 'parent'
                    ? 'bg-accent-lavender-soft/40 text-accent-lavender-bold'
                    : 'bg-accent-sun-soft/40 text-accent-sun-bold'
                "
              >
                {{ roleLabel(member.role) }}
              </span>
              <div class="text-xs kin-muted mt-1">{{ member.description }}</div>
            </div>
          </button>
        </div>

        <!-- Demo unavailable -->
        <div
          v-else
          class="p-4 bg-status-failed/10 border border-status-failed/30 rounded-[10px] text-center"
        >
          <p class="text-sm text-status-failed">
            Демо сейчас недоступно на этом экземпляре. Попробуй позже.
          </p>
        </div>

        <!-- Error -->
        <p v-if="errorMsg" class="text-sm text-status-failed text-center mb-4">
          {{ errorMsg }}
        </p>

        <!-- What you'll see -->
        <div class="border-t border-border-subtle pt-6 mt-2">
          <h3 class="text-sm font-semibold text-ink-primary mb-3 text-center">
            Что внутри демо
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div
              v-for="item in highlights"
              :key="item.title"
              class="p-3 rounded-[10px] bg-surface-sunken"
            >
              <div class="text-sm font-semibold text-ink-primary">{{ item.title }}</div>
              <div class="text-xs kin-muted mt-1">{{ item.description }}</div>
            </div>
          </div>
        </div>

        <!-- Footer links -->
        <div class="border-t border-border-subtle mt-6 pt-6 text-center">
          <p class="kin-muted text-sm">
            Уже есть аккаунт?
            <RouterLink to="/login" class="kin-link font-medium">Войти</RouterLink>
            &nbsp;·&nbsp;
            <RouterLink to="/register" class="kin-link font-medium">Создать аккаунт</RouterLink>
          </p>
        </div>
      </KinFlatCard>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import KinFlatCard from '@/components/design-system/KinFlatCard.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const authStore = useAuthStore()

const demoAvailable = computed(() => authStore.appConfig?.demo_available)
const loadingMember = ref(null)
const errorMsg = ref('')

const roleLabels = { parent: 'Родитель', teen: 'Подросток', kid: 'Ребёнок' }
const roleLabel = (role) => roleLabels[String(role || '').toLowerCase()] || role || 'Участник'

const members = [
  { key: 'adaeze', name: 'Adaeze', role: 'parent', description: 'Мама', color: '#7B5EA7' },
  { key: 'marcus', name: 'Marcus', role: 'parent', description: 'Папа', color: '#4A7B8C' },
  { key: 'zara',   name: 'Zara',   role: 'teen',   description: '16 лет', color: '#C25B8A' },
  { key: 'kenji',  name: 'Kenji',  role: 'kid',    description: '13 лет', color: '#5B7BC2' },
  { key: 'naia',   name: 'Naia',   role: 'kid',    description: '9 лет',  color: '#4A9C78' },
]

const highlights = [
  {
    title: 'Календарь и задачи',
    description: 'Заполненная неделя, повторяющиеся дела и детские задачи с баллами.',
  },
  {
    title: 'Хранилище и рецепты',
    description: 'Семейные документы, план питания, список покупок и библиотека рецептов.',
  },
  {
    title: 'Баллы и достижения',
    description: 'Живой рейтинг, похвала, магазин наград и полученные достижения.',
  },
]

const handleSelect = async (key) => {
  loadingMember.value = key
  errorMsg.value = ''

  const result = await authStore.demoLogin(key)

  if (result.success) {
    router.push({ name: 'Dashboard' })
  } else {
    errorMsg.value = result.error || 'Что-то пошло не так. Попробуй ещё раз.'
    loadingMember.value = null
  }
}
</script>
