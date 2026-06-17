<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Contracts\PaymentGateway;
use App\Enums\OrderStatus;
use App\Exceptions\Orders\OrderExpiredException;
use App\Exceptions\Orders\OrderNotPendingException;
use App\Models\Order;

class CreatePaymentIntentAction
{
    public function __construct(
        protected PaymentGateway $payment_gateway,
        protected ReleaseOrderTicketAction $releaseOrderTicket,
    ) {}

    /**
     * Create a payment intent for the order and return its client secret.
     *
     * @throws OrderNotPendingException
     * @throws OrderExpiredException
     */
    public function __invoke(Order $order): string
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new OrderNotPendingException("Order {$order->id} is not in pending status.");
        }

        if ($order->expires_at < now()) {
            ($this->releaseOrderTicket)($order, OrderStatus::Expired);

            throw new OrderExpiredException("Order {$order->id} has expired and has been cancelled.");
        }

        $intent = $this->payment_gateway->createPaymentIntent($order);

        $order->payment_intent_id = $intent['id'];
        $order->save();

        return $intent['client_secret'];
    }
}
