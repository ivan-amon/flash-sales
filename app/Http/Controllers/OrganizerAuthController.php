<?php

namespace App\Http\Controllers;

use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class OrganizerAuthController extends Controller
{
    /**
     * Register a new organizer and return a token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'official_name' => 'required|string|max:255',
            'phone' => 'sometimes|nullable|string|unique:organizers,phone',
            'email' => 'required|string|email|unique:organizers,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $organizer = Organizer::create([
            'official_name' => $validated['official_name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $organizer->createToken('auth_token', ['is_organizer'])->plainTextToken;

        return response()->json([
            'organizer' => $organizer,
            'token' => $token,
        ], 201);
    }

    /**
     * Login and return a token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $organizer = Organizer::where('email', $validated['email'])->first();

        if (!$organizer || !Hash::check($validated['password'], $organizer->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $organizer->createToken('auth_token', ['is_organizer'])->plainTextToken;

        return response()->json([
            'organizer' => $organizer,
            'token' => $token,
        ]);
    }

    /**
     * Logout and revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
