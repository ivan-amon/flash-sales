<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Actions\Orders\CreateOrderAction;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('ticket.event')
            ->get();

        return response()->json($orders);
    }

    /**
     * Store a newly created order by reserving an available ticket.
     */
    public function store(OrderStoreRequest $request, CreateOrderAction $createOrderAction): JsonResponse
    {
        $validated = $request->validated(); // Validates the request data and if the user is a regular user

        $data = array_merge($validated, ['user_id' => $request->user()->id]);
        $order = $createOrderAction($data);

        return response()->json($order, 201);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->load('ticket.event');

        return response()->json($order);
    }

    /**
     * Cancel the specified order and release the ticket.
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order->ticket->update(['status' => TicketStatus::Available]);
        $order->delete();

        return response()->json(null, 204);
    }
}
