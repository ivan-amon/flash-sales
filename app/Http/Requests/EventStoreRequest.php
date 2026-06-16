<?php

declare(strict_types=1);

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
            'description' => 'nullable|string|max:65535',
            'total_tickets' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'city_id' => 'required|integer|exists:cities,id',
            'sale_starts_at' => 'required|date',
            'event_starts_at' => 'required|date',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ];
    }
}
