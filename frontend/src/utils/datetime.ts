export const MIN_EVENT_LEAD_MINUTES = 60

function pad(value: number): string {
  return String(value).padStart(2, '0')
}

/**
 * Combines separate `<input type="date">` and `<input type="time">` values into the
 * `YYYY-MM-DDTHH:mm` string the API accepts. Returns null when no date is set; a date
 * with no time defaults to midnight.
 */
export function combineDateTime(date: string, time: string): string | null {
  if (!date) {
    return null
  }

  return `${date}T${time || '00:00'}`
}

/**
 * Splits an ISO datetime (or null) into local `date` and `time` values suitable for
 * `<input type="date">` and `<input type="time">`.
 */
export function splitDateTime(value: string | null): { date: string; time: string } {
  if (!value) {
    return { date: '', time: '' }
  }

  const parsed = new Date(value)
  return {
    date: `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}`,
    time: `${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`,
  }
}
