/**
 * Widget Registry — maps type keys to purpose-built Vue components.
 *
 * Each widget type has:
 *  - component: async import (code-split)
 *  - name: display name
 *  - description: short description for picker
 *  - icon: Heroicon name for picker
 *  - category: grouping for picker
 *  - supportedSizes: which sizes this widget renders well at
 *  - heights: CSS height per size (null = auto)
 *  - defaultSize: size to use when adding from picker
 */

export const widgetTypes = {
  'welcome': {
    component: () => import('./widgets/WelcomeWidget.vue'),
    name: 'Приветствие',
    description: 'Приветствие с датой и праздниками',
    icon: 'HandRaisedIcon',
    category: 'general',
    supportedSizes: ['lg'],
    heights: { lg: null },
    defaultSize: 'lg',
  },
  'countdown': {
    component: () => import('./widgets/CountdownWidget.vue'),
    name: 'Отсчёт',
    description: 'Отсчёт до ближайшего события',
    icon: 'ClockIcon',
    category: 'general',
    supportedSizes: ['lg'],
    heights: { lg: null },
    defaultSize: 'lg',
  },
  'my-tasks': {
    component: () => import('./widgets/MyTasksWidget.vue'),
    name: 'Мои задачи',
    description: 'Ваши задачи с чекбоксами',
    icon: 'CheckCircleIcon',
    category: 'tasks',
    supportedSizes: ['sm', 'md', 'lg'],
    heights: { sm: '280px', md: '280px', lg: '320px' },
    defaultSize: 'sm',
  },
  'family-tasks': {
    component: () => import('./widgets/FamilyTasksWidget.vue'),
    name: 'Задачи семьи',
    description: 'Открытые задачи, которые может выполнить любой',
    icon: 'UserGroupIcon',
    category: 'tasks',
    supportedSizes: ['sm', 'md'],
    heights: { sm: '280px', md: '280px' },
    defaultSize: 'md',
  },
  'todays-schedule': {
    component: () => import('./widgets/TodaysScheduleWidget.vue'),
    name: "Расписание на сегодня",
    description: "Сегодняшние события со временем",
    icon: 'CalendarDaysIcon',
    category: 'calendar',
    supportedSizes: ['sm', 'md'],
    heights: { sm: '280px', md: '280px' },
    defaultSize: 'md',
  },
  'points-summary': {
    component: () => import('./widgets/PointsSummaryWidget.vue'),
    name: 'Баланс баллов',
    description: 'Ваш текущий баланс баллов',
    icon: 'TrophyIcon',
    category: 'points',
    supportedSizes: ['sm'],
    heights: { sm: '280px' },
    defaultSize: 'sm',
  },
  'leaderboard': {
    component: () => import('./widgets/LeaderboardWidget.vue'),
    name: 'Рейтинг',
    description: 'Рейтинг по баллам семьи с пьедесталом',
    icon: 'TrophyIcon',
    category: 'points',
    supportedSizes: ['sm', 'md'],
    heights: { sm: '280px', md: '360px' },
    defaultSize: 'sm',
  },
  'activity-feed': {
    component: () => import('./widgets/ActivityFeedWidget.vue'),
    name: 'Лента активности',
    description: 'Недавние операции с баллами',
    icon: 'BellIcon',
    category: 'points',
    supportedSizes: ['sm', 'md'],
    heights: { sm: '280px', md: '280px' },
    defaultSize: 'sm',
  },
  'rewards-shop': {
    component: () => import('./widgets/RewardsWidget.vue'),
    name: 'Магазин наград',
    description: 'Избранные награды с ценами',
    icon: 'GiftIcon',
    category: 'rewards',
    supportedSizes: ['sm', 'md'],
    heights: { sm: '280px', md: '320px' },
    defaultSize: 'sm',
  },
  'badge-collection': {
    component: () => import('./widgets/BadgesWidget.vue'),
    name: 'Достижения',
    description: 'Коллекция значков с отметкой о получении',
    icon: 'ShieldCheckIcon',
    category: 'badges',
    supportedSizes: ['sm', 'md'],
    heights: { sm: '280px', md: '320px' },
    defaultSize: 'sm',
  },
  'filtered-tasks': {
    component: () => import('./widgets/FilteredTasksWidget.vue'),
    name: 'Задачи с фильтром',
    description: 'Задачи, отфильтрованные по тегам и диапазону дат',
    icon: 'FunnelIcon',
    category: 'tasks',
    supportedSizes: ['sm', 'md', 'lg'],
    heights: { sm: '280px', md: '280px', lg: '320px' },
    defaultSize: 'sm',
    configurable: true,
  },
  'quick-actions': {
    component: () => import('./widgets/QuickActionsWidget.vue'),
    name: 'Быстрые действия',
    description: 'Быстрые переходы',
    icon: 'Squares2X2Icon',
    category: 'general',
    supportedSizes: ['sm'],
    heights: { sm: '280px' },
    defaultSize: 'sm',
  },
}

/**
 * Get the async Vue component for a widget type.
 */
export function getWidgetComponent(type) {
  return widgetTypes[type]?.component || null
}

/**
 * Get the CSS height for a widget at a given size.
 * Returns null for auto-height widgets (welcome, countdown).
 */
export function getWidgetHeight(type, size) {
  const heights = widgetTypes[type]?.heights
  if (!heights) return '280px'
  // Explicit null means auto-height — preserve it
  if (size in heights) return heights[size]
  return '280px'
}

/**
 * Get supported sizes for a widget type.
 */
export function getSupportedSizes(type) {
  return widgetTypes[type]?.supportedSizes || ['sm']
}

/**
 * Get all widget types as an array with their keys (for picker).
 */
export function allWidgetTypes() {
  return Object.entries(widgetTypes).map(([key, value]) => ({
    key,
    ...value,
  }))
}

/**
 * Get widget types grouped by category (for picker).
 */
export function widgetTypesByCategory() {
  const grouped = {}
  for (const [key, value] of Object.entries(widgetTypes)) {
    const cat = value.category || 'other'
    if (!grouped[cat]) grouped[cat] = []
    grouped[cat].push({ key, ...value })
  }
  return grouped
}
