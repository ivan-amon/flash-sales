<?php

namespace App\Services;

use App\Contracts\PaymentGateway;

class SimulatedPaymentGateway implements PaymentGateway
{
    /**
     * Simulate processing a payment for the given order and payment method.
     *
     * @param int $orderId The ID of the order to process payment for.
     * @param string $paymentMethod The payment method to use (e.g., 'credit_card', 'paypal').
     * @return bool Returns true if the payment was successful, false otherwise.
     */
    public function processPayment(string $paymentMethod): bool
    {
        // Simulate a delay for processing the payment
        sleep(rand(1, 3)); // Simulate a delay of 1 to 3 seconds

        // For simulation purposes, we'll randomly determine if the payment is successful or not
        return rand(0, 100) <= 90; // 90% chance of success
    }
}