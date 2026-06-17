<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct(
        protected StripeClient $stripe,
        protected string $currency,
    ) {}

    /**
     * Create a Stripe payment intent for the given order.
     *
     * @return array{id: string, client_secret: string}
     */
    public function createPaymentIntent(Order $order): array
    {
        $intent = $this->stripe->paymentIntents->create([
            'amount' => $order->amount,
            'currency' => $this->currency,
            'metadata' => ['order_id' => $order->id],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return [
            'id' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    /**
     * Retrieve the order's payment intent from Stripe and confirm it succeeded.
     */
    public function verifyPayment(Order $order): bool
    {
        if ($order->payment_intent_id === null) {
            return false;
        }

        $intent = $this->stripe->paymentIntents->retrieve($order->payment_intent_id);

        return $intent->status === PaymentIntent::STATUS_SUCCEEDED
            && $intent->amount_received === $order->amount;
    }
}
