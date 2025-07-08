<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('token')) {
            return redirect()->route('login');
        }

        $response = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/user');

        if (!$response->successful()) {
            session()->forget('token');

            return redirect()->route('login');
        }

        cache()->put('user', $response->json('user'));

        return $next($request);
    }
}
