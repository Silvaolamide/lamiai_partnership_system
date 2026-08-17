<?php

namespace App\Http\Middleware;

use App\Services\PartnerApprovalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerApproved
{
    public function __construct(private PartnerApprovalService $approvalService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $partners = $user->programPartners()->with('program')->get();
        foreach ($partners as $partner) {
            $this->approvalService->sync($partner);
        }

        if ($user->programPartners()->where('status', 'active')->exists()) {
            return $next($request);
        }

        return redirect()->route('partner.pending');
    }
}
