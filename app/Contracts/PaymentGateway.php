<?php

namespace App\Contracts;

interface PaymentGateway
{
    /**
     * Process a payment for the given order and payment method.
     *
     * @param int $orderId The ID of the order to process payment for.
     * @param string $paymentMethod The payment method to use (e.g., 'credit_card', 'paypal').
     * @return bool Returns true if the payment was successful, false otherwise.
     */
    public function processPayment(string $paymentMethod): bool;
}