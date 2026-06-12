<?php

namespace App\Actions\Orders;

use App\Contracts\PaymentGateway;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Exceptions\Orders\OrderExpiredException;
use App\Exceptions\Orders\OrderNotPendingException;
use App\Models\Order;

class ProcessOrderPaymentAction
{
    public function __construct(
        protected PaymentGateway $payment_gateway,
        protected ReleaseOrderTicketAction $releaseOrderTicket,
    ) {}

    /**
     * Process the payment for an order and update its status accordingly.
     *
     * @param  array  $data  Contains 'order_id', 'payment_successful', and 'payment_method'.
     * @return Order The updated order instance.
     *
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
            ($this->releaseOrderTicket)($order, OrderStatus::Expired);

            throw new OrderExpiredException("Order {$order->id} has expired and has been cancelled.");
        }

        $payment_successful = $this->payment_gateway->processPayment($data['payment_method']);

        if (! $payment_successful) {
            ($this->releaseOrderTicket)($order, OrderStatus::Cancelled);

            return $order;
        }

        $order->status = OrderStatus::Confirmed;
        $order->save();

        $ticket = $order->ticket;

        if ($ticket) {
            $ticket->status = TicketStatus::Sold;
            $ticket->save();
        }

        return $order;
    }
}
