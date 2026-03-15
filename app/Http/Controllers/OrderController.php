<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Actions\Orders\CreateOrderAction;
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
        $order = $createOrderAction($data);

        return response()->json($order, 201);
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
}
