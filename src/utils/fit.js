/**
 * Checks if a product fits a specified motorcycle model.
 * Returns:
 * - true: product specifically fits this bike
 * - false: product specifies bikes, but not this one
 * - null: product is universal / multi-size (e.g. helmets, gloves)
 */
export function doesFit(product, bikeModel) {
  if (!product) return false
  if (!product.fits || product.fits.length === 0) {
    return null // universal / multi-fit
  }
  if (!bikeModel) return false
  const cleanBike = String(bikeModel).trim().toLowerCase()
  return product.fits.some((m) => {
    const cleanM = String(m).trim().toLowerCase()
    return cleanBike.includes(cleanM) || cleanM.includes(cleanBike)
  })
}
