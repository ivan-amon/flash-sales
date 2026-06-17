<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Create a payment intent for the given order.
     *
     * @return array{id: string, client_secret: string}
     */
    public function createPaymentIntent(Order $order): array;

    /**
     * Verify with the gateway that the order's payment intent has succeeded.
     */
    public function verifyPayment(Order $order): bool;
}
