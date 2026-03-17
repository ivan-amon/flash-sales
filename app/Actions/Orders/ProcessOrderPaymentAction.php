<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\Orders\OrderExpiredException;
use App\Exceptions\Orders\OrderNotPendingException;
use App\Models\Order;

class ProcessOrderPaymentAction
{
    /**
     * Process the payment for an order and update its status accordingly.
     *
     * @param array $data Contains 'order_id', 'payment_successful', and 'payment_method'.
     * @return Order The updated order instance.
     * @throws OrderNotPendingException
     * @throws OrderExpiredException
     */
    public function __invoke(array $data): Order
    {
        $order = Order::findOrFail($data['order_id']);

        if ($order->status !== OrderStatus::Pending) {
            throw new OrderNotPendingException("Order {$order->id} is not in pending status.");
        }

        if ($order->expires_at < now()) {
            $order->status = OrderStatus::Cancelled;
            $order->save();
            throw new OrderExpiredException("Order {$order->id} has expired and has been cancelled.");
        }

        $order->status = $data['payment_successful'] ? OrderStatus::Confirmed : OrderStatus::Cancelled;
        $order->save();
        return $order;
    }
}
