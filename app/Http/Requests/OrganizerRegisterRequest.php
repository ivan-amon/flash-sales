<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'official_name' => 'required|string|max:255',
            'phone' => 'sometimes|nullable|string|unique:organizers,phone',
            'email' => 'required|string|email|unique:organizers,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
