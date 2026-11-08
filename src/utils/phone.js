/**
 * Phone number normalization & validation for Egyptian mobiles
 */
export function normalizePhone(value) {
  if (value === null || value === undefined) return ''
  let str = String(value).trim()

  const eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']
  const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  const western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']

  for (let i = 0; i < 10; i++) {
    str = str.replaceAll(eastern[i], western[i]).replaceAll(persian[i], western[i])
  }

  let digits = str.replace(/\D+/g, '')

  if (digits.startsWith('0020')) {
    digits = '0' + digits.slice(4)
  } else if (digits.startsWith('20') && digits.length >= 12) {
    digits = '0' + digits.slice(2)
  } else if (digits.startsWith('1') && digits.length === 10) {
    digits = '0' + digits
  }

  return digits
}

export function isValidEgyptianPhone(value) {
  const norm = normalizePhone(value)
  return /^01[0125]\d{8}$/.test(norm)
}
