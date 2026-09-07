<template>
  <span
    v-if="points"
    class="inline-flex items-center gap-0.5 text-xs font-semibold font-mono px-1.5 py-0.5 rounded-full bg-accent-lavender-soft/40 text-accent-lavender-bold"
  >
    {{ formatPts(points) }}
  </span>
</template>

<script setup>
import { formatPts } from '@/utils/plural'

defineProps({
  points: {
    type: Number,
    default: null,
  },
})
</script>
