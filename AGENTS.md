# AGENTS.md — форк Gilihard/kinhold

Инструкция для агентов, работающих с ЭТИМ форком (ветка `main`, удалённый `origin` = https://github.com/Gilihard/kinhold.git).

> Рядом лежит апстрим-файл `CLAUDE.md` (владелец — gregqualls, dotclaude-пайплайн, деплой Upsun). Он описывает **чужой** продакшн-инстанс: разделы про Upsun, dotclaude-команды `/kickoff…/merge`, `CLAUDE.local.md` и `repo_owner: gregqualls` к этому чек-ауту **не относятся**. Не удаляйте и не «чините» его — он переживёт синки с апстримом. Всё, что специфично для форка, держите здесь или в gitignored-файлах.
> Апстрим-документацию (docs/REFERENCE.md, ARCHITECTURE.md, CONVENTIONS.md) при работе над логикой читать полезно — она валидна.

## Что это

Форк Greg Qualls / Kinhold — «семейный хаб» (Laravel 11 REST API + Vue 3 SPA, Pinia, Tailwind). Личная цель владельца форка: развернуть локально и **русифицировать** интерфейс и демо-данные. i18n-фреймворка нет и добавлять его не нужно — строки правятся напрямую (Vue-шаблоны и PHP display-строки).

## Локальный запуск (Windows + Docker)

```bash
# .env = копия .env.docker-simple (уже сделана, в git не идёт)
docker compose -f docker-compose.simple.yml -f docker-compose.local.yml up -d      # старт
docker compose -f docker-compose.simple.yml -f docker-compose.local.yml build     # пересборка образа
docker compose -f docker-compose.simple.yml -f docker-compose.local.yml exec app php artisan db:seed --force
```

- `docker-compose.local.yml` — локальный оверрайд: собирает образ `kinhold:local` из исходников вместо пулла `gregqualls/kinhold:latest`, `APP_ENV=local`, `APP_DEBUG=true`.
- БД — SQLite в volume (`/data/kinhold.sqlite`); entrypoint сам прогоняет миграции; `db:seed --force` пересоздаёт демо-данные.
- Приложение: http://localhost:8000.
- **Любое изменение JS или PHP требует `build` + `up -d`**: файлы копируются в образ слоем `COPY . .`, контейнер не видит хост-файлы. `npm run build` (vite) идёт внутри образа и ловит ошибки шаблонов («Invalid end tag» и т.п.). После пересборки полезно перезасеять демо, иначе в БД останутся старые (английские) строки.

## Демо-доступ

- Пароли демо-пользователей **случайные** (`bcrypt(Str::random(32))` в `DatabaseSeeder`) — логин из README НЕ работает. Вход: «Или попробуй демо» → выбрать члена семьи, либо зарегистрировать новую семью.
- Демо-семья: «Семья Эллис», slug `q32-demo-family`; люди: Adaeze (родитель, parent@demo.local), Marcus, Zara (подросток), Kenji, Naia; пёс Biscuit. Имена персонажей не переводим.

## Русификация — правила (нарушения ломают приложение)

1. **Ключи и значения кода не переводим никогда**: enum-значения (`parent`/`teen`/`kid`, `tasks_completed`…), slug-и, строки-сравнения, ключи массивов/объектов, css-классы, aria-роли, имена атрибутов. Переводим только display-текст.
2. **Локаль уже настроена** в `resources/js/app.js`: `Settings.defaultLocale = 'ru'` (Luxon) + `document.documentElement.lang = 'ru'`. `config/app.php` locale **не трогать** — `lang/ru` не существует, иначе ошибки валидации превратятся в «сырые» ключи.
3. **Плюрализация** — через `resources/js/utils/plural.js` (`pluralRu(n, one, few, many)`, `ptsWord(n)`, `formatPts(n)`). Склонение «балл/балла/баллов», «день/дня/дней», «событие/события/событий» и пр. Не склеивать «N баллов» строкой — для произвольных N это грамматически неверно.
4. **Даты**: Luxon с локалью ru; время 24-часовое (`toFormat('HH:mm')`). Нюанс падежей: токен `'MMMM'` у Luxon в ru даёт форму **после числа** («11 сентября 2026» — правильно), а для **отдельностоящего** месяца нужен именительный («сентябрь 2026») — через `Intl {month:'long', year:'numeric'}` (пример: `monthTitleRu()` в `resources/js/views/calendar/CalendarView.vue`). Порядок ru-дат: «EEEE, d MMMM yyyy», не en-порядок.
5. **Синхронные переименования** — строки, на которые ссылаются **по имени** (переводить одним маппингом во ВСЕХ файлах разом):
   - бейджи по умолчанию: `app/Services/BadgeService.php`, `app/Http/Controllers/Api/V1/BadgesController.php` (включая easter-egg мапы `$badgeNameMap`, `ensureEasterEggBadgesExist`, массивы создания), `database/seeders/DemoBadgeSeeder.php` (ключи массива тоже);
   - категории хранилища: `app/Models/VaultCategory.php` (`defaultCategories()`, ключи — slug-и, не трогать);
   - демо-теги: имена задаются в `DatabaseSeeder`, ссылки по имени — в `DemoTaskSeeder.php` / `DemoRecipeSeeder.php` / др.;
   - роли `family_role`: значения в БД остаются `parent/teen/kid`, русские подписи — маппинг в UI (`roleLabel` в `Sidebar.vue`, `DemoModal.vue` и т.п.).
6. **Серверные display-строки тоже русифицируем** (они попадают в UI через API): примеры уже сделаны — `Task.php::getRecurrenceLabelAttribute()` (RRULE → «Ежедневно», «Каждый вторник», «Каждые пн, ср, пт», «Каждый месяц 15-го числа»), описания транзакций баллов в `app/Services/PointsService.php` («Выполнено: …», «Отменено: …», «Стоимость похвалы: …», «Куплено: …») и `PointRequestController.php` («Запрос одобрен: …»).
7. **Распараллеливание агентов**: только по жёсткому списку файлов, с этим файлом как глоссарием; каждый агент получает явный запрет на пункты 1–4. После параллельной волны — обязательный центральный проход (импорты утилит, склонения, падежи, синхронные имена) и пересборка.

## Статус русификации (на момент этого файла)

- ✅ **Сделано, волна 1 (основные разделы)**: вход/регистрация + демо-диалог; каркас (Sidebar, TopBar, навигация, мобильное меню); дашборд и все виджеты (приветствие, отсчёты, задачи, баллы, рейтинг/подиум, награды, достижения, активность…); задачи; календарь; настройки; общие компоненты (модалки, ConfirmDialog, UndoToast, CountdownBanner…); демо-сидеры (задачи, баллы, награды, события, рецепты, план питания, покупки, vault, чат, каталог продуктов); бейджи/vault-категории по умолчанию; страница 404; aria/подписи design-system (Закрыть, Свернуть боковую панель…).
- ✅ **Сделано, волна 2 (страницы модулей и серверные строки)**: Питание (планы, рецепты, рестораны — FoodView/MealsTab/RecipesTab/RestaurantsTab/RecipeDetailView + компоненты meals/recipes/allergens/food); Покупки (ShoppingTab + компоненты shopping); Баллы (лента, история), Награды, Достижения (BadgesView + компоненты badges); Хранилище (страницы vault + компоненты vault); Ассистент/чат (ChatView); онбординг (все шаги); панель уведомлений и браузерные промпты (PWA-install, push-уведомления, лицензия); демо-страница `/demo`; Laravel-уведомления и письма (`app/Notifications`, `resources/views/emails/billing`, `config/notifications.php`, даты в `SendWeeklyDigest`/`StripeWebhookController`); градиент-маппинг тегов рецептов приведён к русским именам демо-тегов (английские ключи сохранены как fallback).
- ⏳ **Ещё не переведено (мелкие остатки)**: демо-витрина `/design-system/*` и дефолтные тексты Kin*-компонентов; easter-eggs в `EasterEggs.vue`; маркетинговая копия `BillingPanel.vue`/`SubscriptionPaywall.vue`; дефолтные футеры vendor-писем (Regards/subcopy из пакета `illuminate/mail`); страницы `PrivacyPolicyView`/`TermsView` (юридический текст).
- Правило: английский текст в зоне «✅» — недочёт, чинить; в зоне «⏳» — ожидаемо.

## Проверка изменений

- JS/Vue: успешная сборка образа = шаблоны синтаксически валидны. Затем браузер: http://localhost:8000 → демо-вход.
- PHP: `docker run --rm -v "D:/Projects/kinhold:/app" -w //app --entrypoint php kinhold:local -l <файл>` (или пересборка).
- Пересоздание контейнера сбрасывает PHP-сессии; старые вкладки браузера могут «залипать» — открывать свежую вкладку.

## Git-политика

- `.env` не коммитить (в .gitignore). `docker-compose.local.yml` и `resources/js/utils/` — локальные/новые, можно коммитить.
- Ветка `main`, push — в свой форк, только по явной просьбе владельца.
