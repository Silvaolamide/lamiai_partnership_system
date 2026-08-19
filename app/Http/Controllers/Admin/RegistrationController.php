<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessPayout;
use App\Models\Order;
use App\Models\PaymentDispute;
use App\Models\PaymentSubmission;
use App\Models\Payout;
use App\Models\ProgramPartner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function index()
    {
        $users = User::query()->where('registration_type', 'business')->latest()->paginate(25);
        return view('admin.registrations.index', compact('users'));
    }

    public function verifyEmail(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        if (! $user->email_verified_at) $user->forceFill(['email_verified_at' => now()])->save();
        return back()->with('success', "Email verified for {$user->email}.");
    }

    public function assignBusinessRole(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        if (! $user->hasRole('program_manager')) $user->assignRole('program_manager');
        return back()->with('success', "Business role restored for {$user->email}.");
    }

    public function approveBusiness(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        if (! $user->hasRole('program_manager')) $user->assignRole('program_manager');
        $user->forceFill(['business_super_admin_approved_at' => now(), 'business_rejected_at' => null])->save();
        $name = $user->business_name ?: $user->name;
        return back()->with('success', "{$name} is now approved.");
    }

    public function rejectBusiness(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        if (! $user->hasRole('program_manager')) $user->assignRole('program_manager');
        $user->forceFill(['business_super_admin_approved_at' => null, 'business_rejected_at' => now()])->save();
        $name = $user->business_name ?: $user->name;
        return back()->with('success', "{$name} has been marked rejected.");
    }

    public function resendVerification(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        if ($user->email_verified_at) return back()->with('success', "{$user->email} is already verified.");
        $user->sendEmailVerificationNotification();
        return back()->with('success', "A new verification email was sent to {$user->email}.");
    }

    public function repair(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        if (! $user->hasRole('program_manager')) $user->assignRole('program_manager');
        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?: now(),
            'business_super_admin_approved_at' => $user->business_super_admin_approved_at ?: now(),
            'business_rejected_at' => null,
        ])->save();
        return back()->with('success', "Registration repaired for {$user->email}: business role, email verification and approval are complete.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureBusinessRegistration($user);
        $activity = $this->registrationActivity($user);
        if ($activity['total'] > 0) {
            return back()->with('error', "{$user->email} cannot be deleted because it has associated activity ({$activity['summary']}). Resolve or archive that activity first.");
        }
        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->permissions()->detach();
            $user->delete();
        });
        return back()->with('success', "Incomplete business registration for {$user->email} was permanently deleted.");
    }

    private function ensureBusinessRegistration(User $user): void
    {
        abort_unless($user->registration_type === 'business', 404);
        abort_if($user->hasRole('super_admin'), 403);
    }

    private function registrationActivity(User $user): array
    {
        $checks = [
            'orders' => fn () => Order::where('customer_id', $user->id)->exists(),
            'partners' => fn () => ProgramPartner::where('user_id', $user->id)->exists(),
            'payouts' => fn () => Payout::where('partner_id', $user->id)->exists(),
            'business payouts' => fn () => BusinessPayout::where('business_id', $user->id)->exists(),
            'payment submissions' => fn () => PaymentSubmission::whereHas('order', fn ($query) => $query->where('customer_id', $user->id))->exists(),
            'payment disputes' => fn () => PaymentDispute::where('customer_id', $user->id)->exists(),
        ];
        $found = [];
        foreach ($checks as $label => $check) if ($check()) $found[] = $label;
        return ['total' => count($found), 'summary' => implode(', ', $found)];
    }
}
