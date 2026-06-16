<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CityController extends Controller
{
    /**
     * Display a listing of the cities, optionally filtered by country.
     */
    public function index(Request $request): JsonResponse
    {
        $cities = City::query()
            ->when($request->filled('country_id'), function ($query) use ($request) {
                $query->where('country_id', $request->integer('country_id'));
            })
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }
}
