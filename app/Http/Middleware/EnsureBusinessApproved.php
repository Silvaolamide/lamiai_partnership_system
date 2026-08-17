<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->business_rejected_at) {
            return redirect()->route('business.pending');
        }

        if (!$user->business_super_admin_approved_at) {
            return redirect()->route('business.pending');
        }

        return $next($request);
    }
}
