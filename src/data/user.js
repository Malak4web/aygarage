export const defaultUser = {
  id: 'u1',
  name: 'كريم محمود',
  phone: '010 1234 5678',
  email: 'karim@aygarage.com',
  address: {
    governorate: 'القاهرة',
    area: 'مدينة نصر',
    street: '12 شارع عباس العقاد، الدور 3، شقة 7'
  },
  savedBikes: [
    {
      id: 'ub1',
      model: 'Zontes ZT350-T',
      year: 2023,
      plate: 'س ط ع 1234',
      odo: 12400,
      nextService: 15000,
      lastVisit: '12 أكتوبر',
      license: 'مارس 2027',
      col: '#9e2b25'
    }
  ],
  orders: [
    {
      id: '1042',
      date: '3 نوفمبر 2026',
      status: 'تم التوصيل',
      statusClass: 'ok',
      total: '3,220 ج.م'
    },
    {
      id: '0987',
      date: '14 سبتمبر 2026',
      status: 'تم الاستلام',
      statusClass: 'ok',
      total: '4,200 ج.م'
    }
  ]
}
