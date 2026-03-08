<?php

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
            'title' => 'sometimes|required|string|unique:events,title,' . $eventId,
            'total_tickets' => 'sometimes|required|integer|min:1',
            'sale_starts_at' => 'nullable|date',
        ];
    }
}
