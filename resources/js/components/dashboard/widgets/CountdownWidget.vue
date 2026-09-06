<template>
  <div v-if="upcomingFeatured.length > 0" class="flex flex-col">
    <CountdownBanner
      v-for="event in upcomingFeatured"
      :key="event.id"
      :countdown-event="event"
    />
  </div>
  <div
    v-else
    class="rounded-card border border-border-subtle bg-surface-raised p-4 md:p-5 text-sm text-ink-secondary"
  >
    Отметьте событие календаря как избранное, чтобы увидеть здесь отсчёт.
    <router-link
      to="/calendar"
      class="ml-1 font-medium text-accent-peach-bold hover:underline"
    >
      Открыть календарь
    </router-link>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useFeaturedEventsStore } from "@/stores/featuredEvents";
import CountdownBanner from "@/components/featured-events/CountdownBanner.vue";

defineProps({
  config: { type: Object, required: true },
});

const featuredEventsStore = useFeaturedEventsStore();

onMounted(() => {
  if (featuredEventsStore.events.length === 0) {
    featuredEventsStore.fetchEvents();
  }
});

const upcomingFeatured = computed(() =>
  featuredEventsStore.upcomingEvents.slice(0, 3),
);
</script>
