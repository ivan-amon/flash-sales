<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Events\AvailableTicketsAction;
use App\Actions\Events\CreateEventAction;
use App\Enums\TicketStatus;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the events located in the visitor's country.
     *
     * The country is resolved upstream by the ResolveCountry middleware and read
     * from the request attributes, so the listing is scoped server-side instead
     * of shipping every country's events to the frontend.
     */
    public function index(Request $request): JsonResponse
    {
        $countryCode = $request->attributes->get('country_code');

        // Optimizations: withCount avoids N+1 queries (available tickets counted in a single subquery),
        // and the tickets table is indexed on (event_id, status) for efficient availability counting.
        $events = Event::query()
            ->whereHas('city', function (Builder $query) use ($countryCode): void {
                $query->where('country_code', $countryCode);
            })
            ->with('city.country')
            ->withCount(['tickets as available_tickets' => function (Builder $query): void {
                $query->where('status', TicketStatus::Available);
            }])
            ->get();

        return response()->json($events);
    }

    /**
     * Display the authenticated organizer's own events.
     */
    public function organizerEvents(Request $request): JsonResponse
    {
        $events = Event::query()
            ->where('organizer_id', $request->user()->id)
            ->withCount(['tickets as available_tickets' => function (Builder $query): void {
                $query->where('status', TicketStatus::Available);
            }])
            ->get();

        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventStoreRequest $request, CreateEventAction $createEvent): JsonResponse
    {
        $validated = $request->validated(); // Validates the request data and if the user is an organizer
        unset($validated['cover_image']);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }

        $data = array_merge($validated, ['organizer_id' => $request->user()->id]);
        $event = $createEvent($data);

        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, AvailableTicketsAction $availableTickets): JsonResponse
    {
        $event->load('city.country');
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
        unset($validated['cover_image']);

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image_path) {
                Storage::disk('public')->delete($event->cover_image_path);
            }

            $validated['cover_image_path'] = $request->file('cover_image')->store('events', 'public');
        }

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        Gate::authorize('delete', $event); // Checks if the organizer is authorized to delete the event

        if ($event->cover_image_path) {
            Storage::disk('public')->delete($event->cover_image_path);
        }

        $event->delete();

        return response()->json(null, 204);
    }
}
