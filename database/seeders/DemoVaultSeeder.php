<?php

namespace Database\Seeders;

use App\Models\VaultCategory;
use App\Models\VaultEntry;
use App\Services\VaultEncryptionService;
use Illuminate\Database\Seeder;

class DemoVaultSeeder extends Seeder
{
    use DemoFamilyContext;

    public function run(): void
    {
        $this->loadDemoContext();

        $vault = new VaultEncryptionService;

        $categories = VaultCategory::where('family_id', $this->familyId())->get()->keyBy('slug');

        // ─────────────────────────────────────────────
        //  MEDICAL
        // ─────────────────────────────────────────────

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['medical']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Семейный педиатр',
            'encrypted_data' => $vault->encrypt([
                'Doctor' => 'Dr. Rebecca Chen',
                'Practice' => 'Sunshine Pediatrics',
                'Phone' => '(555) 234-5678',
                'Address' => '4521 Medical Center Dr, Suite 200',
                'Patient Portal' => 'sunshinepeds.myportal.com',
            ]),
            'notes' => 'Ежегодные осмотры в августе. У Найи повторный приём в апреле.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['medical']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Зара — аллергии и лекарства',
            'encrypted_data' => $vault->encrypt([
                'Allergies' => 'Пенициллин, древесные орехи',
                'EpiPen Rx' => 'EpiPen Jr Auto-Injector',
                'Pharmacy' => 'CVS #4892 — (555) 345-6789',
                'Allergist' => 'Dr. Alan Park — (555) 456-7890',
            ]),
            'notes' => 'EpiPen истекает в сентябре 2026. Переоформить в августе.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['medical']->id,
            'created_by' => $this->mike->id,
            'title' => 'Семейный стоматолог',
            'encrypted_data' => $vault->encrypt([
                'Dentist' => 'Dr. Maria Lopez',
                'Practice' => 'Bright Smiles Family Dental',
                'Phone' => '(555) 567-8901',
                'Next Appointments' => 'Кенджи и Найя: 15 апреля. Зара: 3 мая.',
            ]),
            'notes' => 'Кенджи, возможно, понадобится консультация по поводу брекетов этим летом.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['medical']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Кенджи — лекарства',
            'encrypted_data' => $vault->encrypt([
                'Medication' => 'Concerta 36mg (ежедневно)',
                'Prescribing Doctor' => 'Dr. Priya Sharma — (555) 678-1234',
                'Pharmacy' => 'Walgreens #2291 — (555) 234-8765',
                'Refill Due' => 'Первое число каждого месяца',
            ]),
            'notes' => 'Не пропускать в учебные дни. На каждом приёме запрашивается запас на 3 месяца.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['medical']->id,
            'created_by' => $this->mike->id,
            'title' => 'Зрение — рецепты на очки',
            'encrypted_data' => $vault->encrypt([
                'Zara (right)' => '-1.25 / -0.50 x 180',
                'Zara (left)' => '-1.50 / -0.25 x 175',
                'Naia (right)' => '-0.75 sphere',
                'Naia (left)' => '-0.75 sphere',
                'Optometrist' => 'Dr. Kim — Clear Vision Center (555) 789-3210',
            ]),
            'notes' => 'Обоим нужны новые очки. Следующий осмотр: сентябрь.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['medical']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Семейный врач (взрослые)',
            'encrypted_data' => $vault->encrypt([
                'Doctor' => 'Dr. James Okafor',
                'Practice' => 'Westside Primary Care',
                'Phone' => '(555) 321-4567',
                'Portal' => 'westsideprimary.com/portal',
                'Adaeze Login' => 'aellis_patient',
                'Marcus Login' => 'mellis_patient',
            ]),
            'notes' => 'Ежегодные медосмотры в октябре для обоих взрослых.',
        ]);

        // ─────────────────────────────────────────────
        //  FINANCIAL
        // ─────────────────────────────────────────────

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['financial']->id,
            'created_by' => $this->mike->id,
            'title' => 'Совместный расчётный счёт',
            'encrypted_data' => $vault->encrypt([
                'Bank' => 'First National Bank',
                'Account Number' => '****4829',
                'Routing Number' => '****7631',
                'Online Banking' => 'fnb.com',
                'Login' => 'aellis_primary',
            ]),
            'notes' => 'Основной семейный счёт. Автоплатёж за ипотеку и коммунальные услуги.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['financial']->id,
            'created_by' => $this->mike->id,
            'title' => 'Сбережения на колледж — планы 529',
            'encrypted_data' => $vault->encrypt([
                'Provider' => 'Vanguard 529',
                'Zara Account' => '****8812',
                'Kenji Account' => '****8813',
                'Naia Account' => '****8814',
                'Login' => 'vanguard.com — aellis',
            ]),
            'notes' => 'Ежемесячные взносы $200 на каждого ребёнка. Ежегодно пересматривать распределение.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['financial']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Автокредит — Honda Odyssey',
            'encrypted_data' => $vault->encrypt([
                'Lender' => 'Chase Auto Finance',
                'Account Number' => '****3341',
                'Monthly Payment' => '$487',
                'Payoff Date' => 'март 2027',
                'Online Portal' => 'chase.com/auto',
            ]),
            'notes' => 'Автоплатёж с общего счёта 3-го числа каждого месяца.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['financial']->id,
            'created_by' => $this->mike->id,
            'title' => 'Резервный фонд — высокодоходный накопительный счёт',
            'encrypted_data' => $vault->encrypt([
                'Bank' => 'Marcus by Goldman Sachs',
                'Account Number' => '****9201',
                'APY' => '4.5%',
                'Login' => 'marcus.com — aellis@email.com',
            ]),
            'notes' => 'Цель: 6 месяцев расходов ($28,000). Сейчас накоплено $22,500.',
        ]);

        // ─────────────────────────────────────────────
        //  INSURANCE
        // ─────────────────────────────────────────────

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['insurance']->id,
            'created_by' => $this->mike->id,
            'title' => 'Медицинская страховка',
            'encrypted_data' => $vault->encrypt([
                'Provider' => 'Blue Cross Blue Shield',
                'Policy Number' => 'BCBS-****3947',
                'Group Number' => 'GRP-****2281',
                'Member Services' => '1-800-555-0199',
                'Portal' => 'bcbs.com/members',
            ]),
            'notes' => 'Через работодателя Адаэзе. Открытая запись — в ноябре.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['insurance']->id,
            'created_by' => $this->mike->id,
            'title' => 'Автостраховка',
            'encrypted_data' => $vault->encrypt([
                'Provider' => 'State Farm',
                'Policy Number' => 'SF-****7722',
                'Agent' => 'Tom Bradley — (555) 678-9012',
                'Vehicles' => '2022 Honda Odyssey, 2020 Toyota RAV4',
            ]),
            'notes' => 'Продление в июле. Зару нужно будет добавить, когда она получит права.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['insurance']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Страховка жилья',
            'encrypted_data' => $vault->encrypt([
                'Provider' => 'State Farm',
                'Policy Number' => 'SF-HOME-****3318',
                'Agent' => 'Tom Bradley — (555) 678-9012',
                'Coverage' => '$350,000 жильё / $100,000 личное имущество',
            ]),
            'notes' => 'Продление в сентябре. Рассмотреть увеличение покрытия.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['insurance']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Стоматологическая страховка',
            'encrypted_data' => $vault->encrypt([
                'Provider' => 'Delta Dental',
                'Policy Number' => 'DD-****6614',
                'Group Number' => 'GRP-****5590',
                'Member Services' => '1-800-555-3344',
                'Annual Max' => '$1,500 на человека',
            ]),
            'notes' => 'Покрытие: 100% профилактика, 80% базовые процедуры, 50% крупные. На ортодонтию действует отдельный пожизненный максимум.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['insurance']->id,
            'created_by' => $this->mike->id,
            'title' => 'Страхование жизни',
            'encrypted_data' => $vault->encrypt([
                'Adaeze Policy' => 'Northwestern Mutual — ****2210 — $500k term (срочное)',
                'Marcus Policy' => 'Northwestern Mutual — ****2211 — $500k term (срочное)',
                'Agent' => 'Sandra Reyes — (555) 456-0011',
                'Beneficiaries' => 'Друг друга (основные), дети (резервные)',
            ]),
            'notes' => 'Оба полиса срочные, на 20 лет, действуют до 2038 года. Пересмотреть покрытие, когда Найе исполнится 18.',
        ]);

        // ─────────────────────────────────────────────
        //  LEGAL
        // ─────────────────────────────────────────────

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['legal']->id,
            'created_by' => $this->mike->id,
            'title' => 'Завещания и план наследования',
            'encrypted_data' => $vault->encrypt([
                'Attorney' => 'Jennifer Walsh, Esq.',
                'Firm' => 'Walsh & Associates',
                'Phone' => '(555) 789-0123',
                'Last Updated' => 'октябрь 2025',
                'Guardian Designee' => 'Дядя Дэвид и тётя Карен',
            ]),
            'notes' => 'Пересмотреть и обновить в 2027 году. Копии в банковской ячейке в First National.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['legal']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Загранпаспорта',
            'encrypted_data' => $vault->encrypt([
                'Adaeze' => 'Истекает в июне 2030 — хранится в несгораемом сейфе',
                'Marcus' => 'Истекает в феврале 2029 — хранится в несгораемом сейфе',
                'Zara' => 'Истекает в ноябре 2028',
                'Kenji' => 'Истекает в марте 2031',
                'Naia' => 'Истекает в августе 2030',
            ]),
            'notes' => 'Паспорт Маркуса нужно обновить до семейной поездки 2029 года. Найе, возможно, понадобится новая фотография.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['legal']->id,
            'created_by' => $this->mike->id,
            'title' => 'Документы на недвижимость и ипотека',
            'encrypted_data' => $vault->encrypt([
                'Lender' => 'Wells Fargo Home Mortgage',
                'Loan Number' => '****8847',
                'Rate' => '3.25% фиксированная (на 30 лет)',
                'Monthly Payment' => '$1,842',
                'Maturity Date' => 'апрель 2051',
            ]),
            'notes' => 'Копия документа на недвижимость в несгораемом сейфе. Цифровая копия в этом хранилище.',
        ]);

        // ─────────────────────────────────────────────
        //  EDUCATION
        // ─────────────────────────────────────────────

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['education']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Зара — Lakewood High School',
            'encrypted_data' => $vault->encrypt([
                'School' => 'Lakewood High School',
                'Student ID' => '2024-****8831',
                'Counselor' => 'Ms. Patricia Adams',
                'Parent Portal' => 'lakewood.powerschool.com',
                'Portal Login' => 'aellis_parent',
                'GPA' => '3.8',
            ]),
            'notes' => '11-й класс. SAT назначен на май. Этим летом будут смотреть колледжи.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['education']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Кенджи — Riverside Middle School',
            'encrypted_data' => $vault->encrypt([
                'School' => 'Riverside Middle School',
                'Student ID' => '2024-****5547',
                'Counselor' => 'Mr. James Rivera',
                'Parent Portal' => 'riverside.powerschool.com',
                'Portal Login' => 'aellis_parent',
            ]),
            'notes' => '7-й класс. Отбор в футбольную команду в августе. IEP-собрание в марте.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['education']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Найя — Cedar Ridge Elementary',
            'encrypted_data' => $vault->encrypt([
                'School' => 'Cedar Ridge Elementary',
                'Student ID' => '2024-****2293',
                'Teacher' => 'Mrs. Amanda Foster (4-й класс)',
                'Parent Portal' => 'cedarridge.powerschool.com',
                'Portal Login' => 'aellis_parent',
            ]),
            'notes' => '4-й класс. Выставка рисунков в мае. Чемпион конкурса по правописанию в 2025!',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['education']->id,
            'created_by' => $this->mike->id,
            'title' => 'Зара — подготовка к SAT и колледжу',
            'encrypted_data' => $vault->encrypt([
                'SAT Test Date' => 'май 2026',
                'Test Center' => 'Lakewood High School',
                'Khan Academy Login' => 'zara.ellis.sat',
                'College Board Login' => 'zellis2026',
                'Target Schools' => 'U of Michigan, Northwestern, UVA, Howard',
            ]),
            'notes' => 'Результаты пробных тестов: 1320 суммарно. Цель: 1400+. Пересдача в октябре при необходимости.',
        ]);

        // ─────────────────────────────────────────────
        //  PERSONAL
        // ─────────────────────────────────────────────

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['personal']->id,
            'created_by' => $this->mike->id,
            'title' => 'Wi-Fi и домашняя сеть',
            'encrypted_data' => $vault->encrypt([
                'Network Name' => 'EllisFam5G',
                'Password' => 'Tr33house-2025!',
                'Router Admin' => '192.168.1.1 — admin / ****router',
                'ISP' => 'Comcast Xfinity — аккаунт ****4412',
                'ISP Support' => '1-800-XFINITY',
            ]),
            'notes' => 'Гостевая сеть: EllisGuest / Welcome2025',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['personal']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Стриминговые сервисы',
            'encrypted_data' => $vault->encrypt([
                'Netflix' => 'adaeze.ellis@email.com (Premium)',
                'Disney+' => 'marcus.ellis@email.com (годовая)',
                'Spotify Family' => 'marcus.ellis@email.com (6 участников)',
                'YouTube Premium' => 'семейный план',
                'Apple TV+' => 'adaeze.ellis@email.com',
            ]),
            'notes' => 'Подписки Netflix и Disney+ годовые. Платёж за Spotify списывается ежемесячно 15-го числа.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['personal']->id,
            'created_by' => $this->mike->id,
            'title' => 'Экстренные контакты',
            'encrypted_data' => $vault->encrypt([
                'Grandma Joan' => '(555) 111-2222',
                'Uncle David' => '(555) 333-4444',
                'Aunt Karen' => '(555) 555-6666',
                'Neighbor (Sue Miller)' => '(555) 777-8888',
                'Poison Control' => '1-800-222-1222',
            ]),
            'notes' => 'Бабушка Джоан живёт в 15 минутах езды. Дэвид и Карен — назначенные опекуны.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['personal']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Документы на автомобиль — Honda Odyssey',
            'encrypted_data' => $vault->encrypt([
                'VIN' => '5FNRL6H79NB****312',
                'License Plate' => '****-7RK',
                'Oil Change Due' => 'Каждые 5,000 миль — следующая при пробеге 62,400',
                'Tire Rotation' => 'Каждые 7,500 миль',
                'Mechanic' => 'Garcia Auto — (555) 210-4488',
            ]),
            'notes' => 'Регистрация продлевается в августе. Страховой полис в бардачке.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['personal']->id,
            'created_by' => $this->mike->id,
            'title' => 'Документы на автомобиль — Toyota RAV4',
            'encrypted_data' => $vault->encrypt([
                'VIN' => 'JTMRWRFV4LD****889',
                'License Plate' => '****-4WP',
                'Oil Change Due' => 'Каждые 5,000 миль — следующая при пробеге 48,200',
                'Mechanic' => 'Garcia Auto — (555) 210-4488',
            ]),
            'notes' => 'Зара учится водить на этой машине. Отмечать новые царапины.',
        ]);

        VaultEntry::create([
            'family_id' => $this->familyId(),
            'vault_category_id' => $categories['personal']->id,
            'created_by' => $this->sarah->id,
            'title' => 'Гарантии на бытовую технику',
            'encrypted_data' => $vault->encrypt([
                'Refrigerator' => 'Samsung RF28 — гарантия до декабря 2026 — (800) 726-7864',
                'Washer/Dryer' => 'LG WM4000H — гарантия до октября 2025 — (800) 243-0000',
                'Dishwasher' => 'Bosch SHPM88Z — гарантия до августа 2026 — (800) 944-2904',
                'HVAC' => 'Carrier — запчасти 10 лет — (800) 227-7437',
            ]),
            'notes' => 'Гарантия на стиральную машину истекла. Рассмотреть расширенный план — LG начала издавать шум.',
        ]);
    }
}
