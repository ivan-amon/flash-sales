<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;  
use Illuminate\Routing\Controller;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets for a given event.
     */
    public function index(Event $event): JsonResponse
    {
        $tickets = $event->tickets()->get();

        return response()->json($tickets);
    }

    /**
     * Store a newly created ticket for a given event.
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1|max:' . ($event->total_tickets - $event->tickets()->count()),
        ]);

        $quantity = $validated['quantity'] ?? 1;

        $tickets = collect();

        for ($i = 0; $i < $quantity; $i++) {
            $tickets->push($event->tickets()->create([
                'status' => TicketStatus::Available,
            ]));
        }

        return response()->json($tickets, 201);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Event $event, Ticket $ticket): JsonResponse
    {
        if ($ticket->event_id !== $event->id) {
            return response()->json(['message' => 'Ticket does not belong to this event.'], 404);
        }

        return response()->json($ticket);
    }

    /**
     * Update the specified ticket's status.
     */
    public function update(Request $request, Event $event, Ticket $ticket): JsonResponse
    {
        if ($ticket->event_id !== $event->id) {
            return response()->json(['message' => 'Ticket does not belong to this event.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:available,reserved,sold',
        ]);

        $ticket->update($validated);

        return response()->json($ticket);
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Event $event, Ticket $ticket): JsonResponse
    {
        if ($ticket->event_id !== $event->id) {
            return response()->json(['message' => 'Ticket does not belong to this event.'], 404);
        }

        $ticket->delete();

        return response()->json(null, 204);
    }
}
