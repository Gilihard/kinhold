<template>
  <div class="flex items-start gap-3 px-4 py-3">
    <UserAvatar :user="item.user" size="sm" />
    <div class="flex-1 min-w-0">
      <p class="text-sm text-ink-primary">
        <span class="font-semibold">{{ item.user?.name }}</span>
        {{ actionText }}
      </p>
      <p
        v-if="item.description"
        class="text-xs text-ink-tertiary mt-0.5 truncate"
      >
        {{ item.description }}
      </p>
      <p class="text-xs text-ink-tertiary mt-1">
        {{ formatTime(item.created_at) }}
      </p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      <button
        v-if="canStack"
        type="button"
        class="inline-flex items-center gap-1 min-h-[40px] px-3 rounded-full bg-accent-lavender-soft text-accent-lavender-bold hover:bg-accent-lavender-bold hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="isSubmitting"
        :aria-label="stackAriaLabel"
        @click="handleStack"
      >
        <HandThumbUpIcon class="w-4 h-4" />
        <span v-if="item.stacks_count" class="text-xs font-semibold">
          {{ item.stacks_count }}
        </span>
      </button>
      <span
        v-else-if="showStackedBadge"
        class="inline-flex items-center gap-1 min-h-[32px] px-2 rounded-full bg-surface-sunken text-ink-tertiary"
        :aria-label="`Вы поставили +1 к этой похвале${item.stacks_count > 1 ? `, всего ${item.stacks_count}` : ''}`"
      >
        <HandThumbUpSolidIcon class="w-4 h-4" />
        <span v-if="item.stacks_count" class="text-xs font-semibold">
          {{ item.stacks_count }}
        </span>
      </span>
      <span
        v-else-if="showStackCountOnly"
        class="inline-flex items-center gap-1 min-h-[32px] px-2 rounded-full bg-surface-sunken text-ink-tertiary"
        :aria-label="`Ещё ${item.stacks_count} ${pluralRu(item.stacks_count, 'человек поставил', 'человека поставили', 'человек поставили')} +1 к этой похвале`"
      >
        <HandThumbUpIcon class="w-4 h-4" />
        <span class="text-xs font-semibold">{{ item.stacks_count }}</span>
      </span>
      <span
        class="text-sm font-bold font-mono px-2 py-0.5 rounded-full"
        :class="
          item.points > 0
            ? 'text-status-success bg-status-success/10'
            : 'text-status-failed bg-status-failed/10'
        "
      >
        {{ item.points > 0 ? "+" : "" }}{{ item.points }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { HandThumbUpIcon } from "@heroicons/vue/24/outline";
import { HandThumbUpIcon as HandThumbUpSolidIcon } from "@heroicons/vue/24/solid";
import { pluralRu } from "@/utils/plural";
import UserAvatar from "@/components/common/UserAvatar.vue";
import { useAuthStore } from "@/stores/auth";
import { usePointsStore } from "@/stores/points";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const authStore = useAuthStore();
const pointsStore = usePointsStore();
const isSubmitting = ref(false);

const isOriginalKudos = computed(
  () => props.item.type === "kudos" && !props.item.stacked_from_transaction_id,
);

const currentUserId = computed(() => authStore.currentUser?.id);

const isRecipient = computed(() => props.item.user_id === currentUserId.value);
const isOriginalGiver = computed(
  () =>
    (props.item.awarded_by_user?.id || props.item.awarded_by?.id) ===
    currentUserId.value,
);

const canStack = computed(
  () =>
    isOriginalKudos.value &&
    !isRecipient.value &&
    !isOriginalGiver.value &&
    !props.item.stacked_by_me,
);

const showStackedBadge = computed(
  () => isOriginalKudos.value && props.item.stacked_by_me,
);

const showStackCountOnly = computed(
  () =>
    isOriginalKudos.value &&
    !canStack.value &&
    !showStackedBadge.value &&
    props.item.stacks_count > 0,
);

const stackAriaLabel = computed(() => {
  const awardedByName =
    props.item.awarded_by_user?.name || props.item.awarded_by?.name;
  const base = awardedByName
    ? `Поставить +1 к похвале от ${awardedByName}`
    : "Поставить +1 к этой похвале";
  return props.item.stacks_count
    ? `${base} (сейчас ${props.item.stacks_count} ${pluralRu(props.item.stacks_count, "человек", "человека", "человек")})`
    : base;
});

const handleStack = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  await pointsStore.stackKudos(props.item.id);
  isSubmitting.value = false;
};

const actionText = computed(() => {
  const type = props.item.type;
  const awardedBy =
    props.item.awarded_by_user?.name || props.item.awarded_by?.name;

  switch (type) {
    case "task_completion":
      return "выполнил(а) задачу";
    case "task_reversal":
      return "— баллы за задачу отменены";
    case "kudos":
      if (props.item.stacked_from_transaction_id) {
        return awardedBy
          ? `получил(а) дополнительную похвалу от ${awardedBy}`
          : "получил(а) дополнительную похвалу";
      }
      return awardedBy
        ? `получил(а) похвалу от ${awardedBy}`
        : "получил(а) похвалу";
    case "deduction":
      return awardedBy
        ? `— баллы списал(а) ${awardedBy}`
        : "— баллы списаны";
    case "redemption":
      return "обменял(а) баллы на награду";
    case "adjustment":
      return "— баллы скорректированы";
    default:
      return "";
  }
});

const formatTime = (dateStr) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  const now = new Date();
  const diff = now - d;
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return "только что";
  if (mins < 60) return `${mins} ${pluralRu(mins, "минуту", "минуты", "минут")} назад`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `${hours} ${pluralRu(hours, "час", "часа", "часов")} назад`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days} ${pluralRu(days, "день", "дня", "дней")} назад`;
  return d.toLocaleDateString("ru-RU", { day: "numeric", month: "short" });
};
</script>
