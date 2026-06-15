/**
 * Converts an ISO 3166-1 alpha-2 country code (e.g. "ES") into its flag emoji (🇪🇸)
 * by mapping each letter to its Unicode regional indicator symbol.
 */
export function flagEmoji(isoCode: string): string {
  return isoCode
    .toUpperCase()
    .replace(/./g, (char) => String.fromCodePoint(127397 + char.charCodeAt(0)))
}

const priceFormatter = new Intl.NumberFormat(undefined, {
  style: 'currency',
  currency: 'USD',
})

/**
 * Formats an integer amount of cents into a localized currency string
 * (e.g. 4999 → "$49.99"). Money is stored as cents by the API to avoid
 * floating-point errors, so divide by 100 for display.
 */
export function formatPriceFromCents(cents: number): string {
  return priceFormatter.format(cents / 100)
}
