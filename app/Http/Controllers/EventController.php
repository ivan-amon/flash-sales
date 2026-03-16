<?php

namespace App\Http\Controllers;

use App\Actions\Events\AvailableTicketsAction;
use App\Actions\Events\CreateEventAction;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AvailableTicketsAction $availableTickets): JsonResponse
    {
        $events = Event::all()->map(function (Event $event) use ($availableTickets) {
            $eventArray = $event->toArray();
            $eventArray['available_tickets'] = $availableTickets($event);
            return $eventArray;
        });

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventStoreRequest $request, CreateEventAction $createEvent): JsonResponse
    {
        $validated = $request->validated(); // Validates the request data and if the user is an organizer

        $data = array_merge($validated, ['organizer_id' => $request->user()->id]);
        $event = $createEvent($data);

        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, AvailableTicketsAction $availableTickets): JsonResponse
    {
        $eventArray = $event->toArray();
        $eventArray['available_tickets'] = $availableTickets($event);
    
        return response()->json($eventArray);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventUpdateRequest $request, Event $event): JsonResponse
    {
        $validated = $request->validated(); // Validates the request data and if the user is an organizer
        Gate::authorize('update', $event); // Checks if the organizer is authorized to update the event
        $event->update($validated);
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        Gate::authorize('delete', $event); // Checks if the organizer is authorized to delete the event   
        $event->delete();
        return response()->json(null, 204);
    }
}
