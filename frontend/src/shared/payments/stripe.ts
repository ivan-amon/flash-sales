import { loadStripe, type Stripe } from '@stripe/stripe-js'

const publishableKey = import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY as string | undefined

let stripePromise: Promise<Stripe | null> | null = null

/**
 * Lazily load and memoize the Stripe.js instance.
 * Returns null when no publishable key is configured, which lets the
 * checkout fall back to the backend's simulated payment gateway.
 */
export function getStripe(): Promise<Stripe | null> {
  if (!publishableKey) {
    return Promise.resolve(null)
  }

  if (!stripePromise) {
    stripePromise = loadStripe(publishableKey)
  }

  return stripePromise
}
