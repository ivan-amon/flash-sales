<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\ProcessOrderPaymentAction;
use App\Exceptions\Orders\OrderExpiredException;
use App\Exceptions\Orders\OrderNotPendingException;
use App\Exceptions\Tickets\NotAvailableTicketsException;
use App\Exceptions\Tickets\TicketSalesNotStartedException;
use App\Http\Requests\ProcessOrderPaymentRequest;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);
        $orders = $request->user()->orders()->with('ticket.event')->get();
        return response()->json($orders);
    }

    /**
     * Store a newly created order by reserving an available ticket.
     */
    public function store(OrderStoreRequest $request, CreateOrderAction $createOrderAction): JsonResponse
    {
        $validated = $request->validated(); // Validates the request data and if the user is a regular user, not an organizer
        $data = array_merge($validated, ['user_id' => $request->user()->id]);

        try {
            $order = $createOrderAction($data);
            return response()->json($order, 201);
        } catch (TicketSalesNotStartedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (NotAvailableTicketsException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);
        $order->load('ticket.event');
        return response()->json($order);
    }

    /**
     * Process the payment for the specified order.
     */
    public function processPayment(ProcessOrderPaymentRequest $request, Order $order, ProcessOrderPaymentAction $processOrderPaymentAction): JsonResponse
    {
        try {
            $request_data  = $request->validated();
            $request_data['order_id'] = $order->id;
            $processed_order = $processOrderPaymentAction($request_data);
            return response()->json([
                'message' => 'Order processed successfully',
                'data' => [
                    'order_id' => $processed_order->id,
                    'status' => $processed_order->status,
                    'payment_method' => $request_data['payment_method'],
                    'updated_at' => $processed_order->updated_at,
                ],
            ]);
        } catch (OrderNotPendingException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        } catch (OrderExpiredException $e) {
            return response()->json(['error' => $e->getMessage()], 410);
        }
    }
}
