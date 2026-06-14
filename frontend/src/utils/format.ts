/**
 * Converts an ISO 3166-1 alpha-2 country code (e.g. "ES") into its flag emoji (🇪🇸)
 * by mapping each letter to its Unicode regional indicator symbol.
 */
export function flagEmoji(isoCode: string): string {
  return isoCode
    .toUpperCase()
    .replace(/./g, (char) => String.fromCodePoint(127397 + char.charCodeAt(0)))
}
