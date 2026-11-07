export const bikes = [
  {
    id: 'b1',
    slug: 'zt350t-2023',
    model: 'Zontes ZT350-T',
    family: 'ZT350',
    year: 2023,
    km: '12,400',
    kmNum: 12400,
    price: 245000,
    installmentMonthly: 10200,
    inspectionScore: 92,
    col: '#9e2b25',
    owner: 'أول مالك',
    licenseUntil: 'مارس 2027',
    notes: 'الموتوسيكل نضيف ومتصان عند الجراج من أول 6,000 كم. لمبة الإشارة الخلفية هتتغير مجاناً قبل التسليم. الكاوتش الخلفي 70% ومحتاج يتغير قبل 16,000 كم.',
    specs: {
      'المحرك': '348cc · سلندر واحد',
      'القوة': '38 حصان',
      'ناقل الحركة': '6 سرعات',
      'الفرامل': 'ABS مزدوج',
      'الضمان': '3 شهور على المحرك'
    },
    inspection: [
      { section: 'المحرك', score: 100, note: 'سليم' },
      { section: 'الفرامل وABS', score: 100, note: 'سليم' },
      { section: 'الكهرباء', score: 94, note: 'لمبة إشارة خلفي', warning: true },
      { section: 'الشاسيه والرقم', score: 100, note: 'سليم' },
      { section: 'العفشة', score: 100, note: 'سليم' },
      { section: 'الكاوتش', score: 75, note: 'الخلفي 70%', warning: true }
    ],
    serviceHistory: [
      { date: '12 أكتوبر 2026', odo: '12,000 كم', service: 'تغيير زيت + فلتر', tech: 'أبو يوسف' },
      { date: '5 يوليو 2026', odo: '9,300 كم', service: 'تيل فرامل أمامي وخلفي', tech: 'محمد' },
      { date: '20 مارس 2026', odo: '6,000 كم', service: 'صيانة شاملة', tech: 'أبو يوسف' }
    ]
  },
  {
    id: 'b2',
    slug: 'zt125u-2024',
    model: 'Zontes ZT125-U',
    family: 'ZT125',
    year: 2024,
    km: '3,200',
    kmNum: 3200,
    price: 98000,
    installmentMonthly: 4100,
    inspectionScore: 97,
    col: '#2e2e2e',
    owner: 'أول مالك',
    licenseUntil: 'أغسطس 2027',
    notes: 'بحالة الزيرو تماماً بدون أي خدوش، تم عمل صيانة الألف كم في التوكيل ومعه كافة المفاتيح الأصلية.',
    specs: {
      'المحرك': '125cc · تبريد ماء',
      'القوة': '14.8 حصان',
      'ناقل الحركة': '6 سرعات',
      'الفرامل': 'ABS أمامي وخلفي Bosch',
      'الضمان': 'ساري ضمان الوكيل'
    },
    inspection: [
      { section: 'المحرك', score: 100, note: 'سليم بحالة الزيرو' },
      { section: 'الفرامل وABS', score: 100, note: 'سليم' },
      { section: 'الكهرباء', score: 100, note: 'سليم' },
      { section: 'الشاسيه والرقم', score: 100, note: 'سليم' },
      { section: 'العفشة', score: 100, note: 'سليم' },
      { section: 'الكاوتش', score: 95, note: 'بحالة المصنع' }
    ]
  },
  {
    id: 'b3',
    slug: 'zt350gk-2023',
    model: 'Zontes ZT350-GK',
    family: 'ZT350',
    year: 2023,
    km: '7,800',
    kmNum: 7800,
    price: 232000,
    installmentMonthly: 9600,
    inspectionScore: 94,
    col: '#56634a',
    owner: 'أول مالك',
    licenseUntil: 'يناير 2027',
    notes: 'موتوسيكل كافيه ريسر مميز جداً، تم تركيب حماية محرك جانبية وجراب موبايل أصلي.',
    specs: {
      'المحرك': '348cc · 38 حصان',
      'القوة': '38 حصان',
      'ناقل الحركة': '6 سرعات مع Slipper Clutch',
      'الفرامل': 'ديسك 320 مم كاليبر كبير',
      'الضمان': '3 شهور من الجراج'
    },
    inspection: [
      { section: 'المحرك', score: 100, note: 'سليم' },
      { section: 'الفرامل وABS', score: 95, note: 'تيل أمامي 80%' },
      { section: 'الكهرباء', score: 100, note: 'سليم' },
      { section: 'الشاسيه والرقم', score: 100, note: 'سليم' },
      { section: 'العفشة', score: 95, note: 'سليم' },
      { section: 'الكاوتش', score: 85, note: 'حالة جيدة جداً' }
    ]
  },
  {
    id: 'b4',
    slug: 'zt310r-2022',
    model: 'Zontes ZT310-R',
    family: 'ZT310',
    year: 2022,
    km: '18,900',
    kmNum: 18900,
    price: 189000,
    installmentMonthly: 7800,
    inspectionScore: 86,
    col: '#d6d6d6',
    owner: 'أول مالك',
    licenseUntil: 'مايو 2026',
    notes: 'حالة عامة جيدة جداً، به حكة بسيطة في الشكمان، تم عمل صيانة الـ 18 ألف بالكامل وتغيير الجنزير بـ DID ياباني جديد.',
    specs: {
      'المحرك': '312cc · سلندر واحد',
      'القوة': '35 حصان',
      'ناقل الحركة': '6 سرعات',
      'الفرامل': 'Bosch ABS',
      'الضمان': 'شهرين على المحرك'
    },
    inspection: [
      { section: 'المحرك', score: 92, note: 'سليم ولا توجد تسريبات' },
      { section: 'الفرامل وABS', score: 88, note: 'تيل بحالة 65%' },
      { section: 'الكهرباء', score: 90, note: 'سليم' },
      { section: 'الشاسيه والرقم', score: 100, note: 'سليم' },
      { section: 'العفشة', score: 85, note: 'سليم' },
      { section: 'الكاوتش', score: 70, note: 'ملاحظة: الخلفي قرب يخلص', warning: true }
    ]
  },
  {
    id: 'b5',
    slug: 'zt310x-2021',
    model: 'Zontes ZT310-X',
    family: 'ZT310',
    year: 2021,
    km: '26,000',
    kmNum: 26000,
    price: 162000,
    installmentMonthly: 6700,
    inspectionScore: 81,
    col: '#2d4f80',
    owner: 'ثاني مالك',
    licenseUntil: 'نوفمبر 2026',
    notes: 'موتوسيكل سفر مريح مع زجاج كهربائي متحرك، تم تغيير السير والزيوت ومستعد للسفر فوراً.',
    specs: {
      'المحرك': '312cc تورنج',
      'القوة': '35 حصان',
      'ناقل الحركة': '6 سرعات',
      'الفرامل': 'ABS مزدوج',
      'الضمان': 'شهر على المحرك'
    },
    inspection: [
      { section: 'المحرك', score: 85, note: 'سليم وصوته ناعم' },
      { section: 'الفرامل وABS', score: 80, note: 'تيل جديد' },
      { section: 'الكهرباء', score: 85, note: 'الزجاج الكهربائي يعمل بكفاءة' },
      { section: 'الشاسيه والرقم', score: 100, note: 'سليم' },
      { section: 'العفشة', score: 80, note: 'سليم' },
      { section: 'الكاوتش', score: 65, note: 'ملاحظة: الكاوتش يحتاج تغيير قريباً', warning: true }
    ]
  },
  {
    id: 'b6',
    slug: 'zt350d-2024',
    model: 'Zontes ZT350-D',
    family: 'ZT350',
    year: 2024,
    km: '1,900',
    kmNum: 1900,
    price: 268000,
    installmentMonthly: 11100,
    inspectionScore: 98,
    col: '#8a8a8a',
    owner: 'أول مالك',
    licenseUntil: 'أكتوبر 2027',
    notes: 'ماكسي سكوتر فخم وقوي جداً بحالة الوكالة تماماً، تم عمل صيانة التليين فقط.',
    specs: {
      'المحرك': '348cc سكوتر فائق القوة',
      'القوة': '36.8 حصان',
      'ناقل الحركة': 'CVT أوتوماتيك',
      'الفرامل': 'دبل ABS مع نظام تحكم بالجر TCS',
      'الضمان': 'ضمان الوكيل ساري'
    },
    inspection: [
      { section: 'المحرك', score: 100, note: 'بحالة الزيرو' },
      { section: 'الفرامل وABS', score: 100, note: 'سليم' },
      { section: 'الكهرباء', score: 100, note: 'سليم' },
      { section: 'الشاسيه والرقم', score: 100, note: 'سليم' },
      { section: 'العفشة', score: 100, note: 'سليم' },
      { section: 'الكاوتش', score: 98, note: 'بحالة المصنع' }
    ]
  }
]
