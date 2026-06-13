<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CountryController extends Controller
{
    /**
     * Display a listing of the countries.
     */
    public function index(): JsonResponse
    {
        return response()->json(Country::orderBy('name')->get());
    }
}
