<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPropertyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip property access check for super admins
        if (Auth::check() && Auth::user()->hasRole('super_admin')) {
            return $next($request);
        }

        // Get the property from the route parameters
        $propertyId = $request->route('property') ?? $request->route('property_id');

        if ($propertyId) {
            // Check if the authenticated user has access to this property
            $user = Auth::user();

            if (!$user || !$user->properties->contains($propertyId)) {
                abort(403, 'You do not have access to this property.');
            }
        }

        return $next($request);
    }
}
