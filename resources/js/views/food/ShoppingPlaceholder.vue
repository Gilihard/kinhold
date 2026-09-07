<template>
  <EmptyState
    :icon="ShoppingCartIcon"
    title="Списки покупок скоро появятся"
    description="Автоматически формируются из вашего плана питания, плюс базовые и добавленные вручную товары. Отмечайте купленное во время покупок."
  />
</template>

<script setup>
import { ShoppingCartIcon } from '@heroicons/vue/24/outline'
import EmptyState from '@/components/common/EmptyState.vue'
</script>
