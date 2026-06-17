<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Str;

class SimulatedPaymentGateway implements PaymentGateway
{
    /**
     * Create a simulated payment intent for the given order.
     *
     * @return array{id: string, client_secret: string}
     */
    public function createPaymentIntent(Order $order): array
    {
        $id = 'pi_sim_'.Str::random(24);

        return [
            'id' => $id,
            'client_secret' => $id.'_secret_'.Str::random(24),
        ];
    }

    /**
     * Simulate verifying that the order's payment intent has succeeded.
     */
    public function verifyPayment(Order $order): bool
    {
        return $order->payment_intent_id !== null;
    }
}
