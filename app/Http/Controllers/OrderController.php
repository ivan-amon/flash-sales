<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $event = Event::find($validated['event_id']);
        if ($event && $event->sale_starts_at && now()->lt($event->sale_starts_at)) {
            return response()->json([
                'message' => 'Ticket sales for this event have not started yet.'
            ], 422);
        }

        $ticket = Ticket::where('event_id', $validated['event_id'])
            ->where('status', TicketStatus::Available)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'No available tickets for this event.'], 422);
        }

        $ticket->update(['status' => TicketStatus::Sold]);

        $order = Order::create([
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
        ]);

        $order->load('ticket.event');

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
