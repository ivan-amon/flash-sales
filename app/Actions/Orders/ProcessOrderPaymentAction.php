<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Exceptions\Orders\OrderExpiredException;
use App\Exceptions\Orders\OrderNotPendingException;
use App\Models\Order;
use \Illuminate\Http\JsonResponse;

class ProcessOrderPaymentAction
{
    // Todo: refactor status code management in Controllers, Actions should only manage business logic, not HTTP responses
    public function __invoke(array $data): JsonResponse
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

        if ($data['payment_successful']) {
            $order->status = OrderStatus::Confirmed;
        } else {
            $order->status = OrderStatus::Cancelled;
        }
        $order->save();

        return response()->json([
            'message' => 'Order processed successfully',
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'payment_method' => $data['payment_method'],
                'updated_at' => $order->updated_at,
            ],
        ]);
    }
}
