import { reactive } from 'vue'

export const storeConfig = reactive({
  name: 'جراج أبو يوسف',
  nameEn: 'Abu Yousef Garage',
  tagline: 'ZONTES SPECIALIST',
  phone: '010 0000 0000',
  whatsapp: '201000000000',
  announcement: 'توصيل لكل المحافظات · استلام مجاني من الجراج · كلّمنا: 010 0000 0000',
  deliveryFee: 60,
  freeDeliveryThreshold: 3000,
  deliveryDaysCount: 3,
  deliveryEstimateDays: '2-4 أيام عمل',
  returnDays: 14,
  returnPolicyNote: 'لو العبوة ما اتفتحتش أو القطعة ما ركبتش',
  totalInspectedBikes: 22,
  defaultInstallmentMonthly: 10200,
  heroKitPrice: 5350,
  heroKitPriceFormatted: '5,350 ج.م',
  heroKitDiscount: '10%',
  models: [
    'ZT125-U',
    'ZT310-R',
    'ZT310-X',
    'ZT350-T',
    'ZT350-GK',
    'ZT350-D',
    'ZT703'
  ],
  years: [2026, 2025, 2024, 2023, 2022, 2021, 2020],
  paymentMethods: [
    { id: 'cod', title: 'كاش عند الاستلام', desc: 'ادفع للمندوب لما الطلب يوصلك' },
    { id: 'pickup', title: 'الدفع عند الاستلام في الجراج', desc: 'استلم طلبك وادفع كاش في مقر الجراج' }
  ],
  governorates: [
    {
      name: 'القاهرة',
      areas: ['مدينة نصر', 'مصر الجديدة', 'التجمع الخامس', 'المعادي', 'شبرا', 'الدقي', 'المهندسين']
    },
    {
      name: 'الجيزة',
      areas: ['الدقي', 'المهندسين', 'الهرم', 'فيصل', 'الشيخ زايد', '6 أكتوبر']
    },
    {
      name: 'الإسكندرية',
      areas: ['سموحة', 'سيدي جابر', 'لوران', 'ميامي', 'ستانلي', 'محرم بك']
    },
    {
      name: 'الشرقية',
      areas: ['الزقازيق', 'العاشر من رمضان', 'بلبيس', 'منيا القمح']
    }
  ],
  serviceFeatures: [
    { title: 'تغيير زيت وفلتر', price: '1,770 ج.م' },
    { title: 'صيانة شاملة', price: '2,450 ج.م' },
    { title: 'فحص قبل الشراء', price: '600 ج.م' },
    { title: 'ضبط بلوف', price: '900 ج.م' }
  ],
  trustItems: [
    { icon: 'truck', title: 'توصيل لكل المحافظات', desc: 'خلال 2-4 أيام عمل' },
    { icon: 'shield', title: 'قطع أصلية', desc: 'من الوكيل أو الشركة المصنعة' },
    { icon: 'cash', title: 'الدفع عند الاستلام', desc: 'كاش للمندوب أو في الجراج' },
    { icon: 'ret', title: 'استرجاع خلال 14 يوم', desc: 'لو القطعة ما ركبتش' }
  ],
  pickupTimes: 'جاهز النهارده بعد 3 العصر',
  deliveryEstimate: 'يوصلك 2-4 أيام عمل'
})

export function updateStoreConfig(newValues) {
  if (newValues && typeof newValues === 'object') {
    Object.assign(storeConfig, newValues)
  }
}
