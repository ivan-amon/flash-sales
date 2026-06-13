<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|unique:events,title',
            'total_tickets' => 'required|integer|min:1',
            'city_id' => 'required|integer|exists:cities,id',
            'sale_starts_at' => 'nullable|date',
            'event_starts_at' => 'required|date',
        ];
    }
}
