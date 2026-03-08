<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $events = Event::all();

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Only authenticated organizers can create events
        $organizer = $request->user();
        if (!$organizer || !$organizer instanceof Organizer) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|unique:events,title',
            'total_tickets' => 'required|integer|min:1',
            'sale_starts_at' => 'nullable|date',
        ]);

        $event = Event::create([
            'title' => $validated['title'],
            'total_tickets' => $validated['total_tickets'],
            'organizer_id' => $organizer->id,
            'sale_starts_at' => $validated['sale_starts_at'] ?? null,
        ]);

        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): JsonResponse
    {
        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        // Only the event's organizer can update
        $organizer = $request->user();
        if (!$organizer || !$organizer instanceof Organizer || $event->organizer_id !== $organizer->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|unique:events,title,' . $event->id,
            'total_tickets' => 'sometimes|required|integer|min:1',
            'sale_starts_at' => 'nullable|date',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        // Only the event's organizer can delete
        $organizer = $request->user();
        if (!$organizer || !$organizer instanceof Organizer || $event->organizer_id !== $organizer->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $event->delete();
        return response()->json(null, 204);
    }
}
