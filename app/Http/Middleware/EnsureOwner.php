<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'owner') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Halaman ini hanya bisa dibuka oleh pemilik.');
        }

        return $next($request);
    }
}