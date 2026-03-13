<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Event;

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
            'sale_starts_at' => 'nullable|date',
        ];
    }
}
