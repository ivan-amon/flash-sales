<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'title' => 'sometimes|required|string|unique:events,title,'.$eventId,
            'description' => 'sometimes|nullable|string|max:65535',
            'total_tickets' => 'sometimes|required|integer|min:1',
            'city_id' => 'sometimes|required|integer|exists:cities,id',
            'sale_starts_at' => 'sometimes|required|date',
            'event_starts_at' => 'sometimes|required|date',
            'cover_image' => 'sometimes|image|mimes:jpeg,png,webp|max:2048',
        ];
    }
}
