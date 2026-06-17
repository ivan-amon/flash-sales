<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Country;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class ResolveCountry
{
    /**
     * The country code used when none can be resolved or the resolved one is invalid.
     */
    public const DEFAULT_COUNTRY_CODE = 'ES';

    /**
     * The request header an anonymous visitor uses to force a country choice.
     */
    public const COUNTRY_HEADER = 'X-Country-Code';

    /**
     * Resolve the visitor's country and expose it to the rest of the request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('country_code', $this->resolveCountryCode($request));

        return $next($request);
    }

    /**
     * Resolve the country code following the priority:
     * authenticated profile > explicit header > IP geolocation > default.
     */
    protected function resolveCountryCode(Request $request): string
    {
        $user = $request->user('sanctum');

        if ($user instanceof User && $user->country_code !== null) {
            return $user->country_code;
        }

        $candidate = $request->header(self::COUNTRY_HEADER) ?? $this->locateByIp($request);

        return $this->validate($candidate);
    }

    /**
     * Translate the request IP into a country code, or null on failure / local IP.
     */
    protected function locateByIp(Request $request): ?string
    {
        $position = Location::get($request->ip());

        return $position ? $position->countryCode : null;
    }

    /**
     * Ensure the candidate is a known country, otherwise fall back to the default.
     */
    protected function validate(?string $candidate): string
    {
        if ($candidate === null) {
            return self::DEFAULT_COUNTRY_CODE;
        }

        $candidate = strtoupper($candidate);

        return Country::whereKey($candidate)->exists()
            ? $candidate
            : self::DEFAULT_COUNTRY_CODE;
    }
}
