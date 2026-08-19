<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class RegistrationController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'super_admin'))
            ->latest()
            ->paginate(25);

        return view('admin.registrations.index', compact('users'));
    }

    public function verifyEmail(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403);

        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return back()->with('success', "Email verified for {$user->email}.");
    }

    public function assignBusinessRole(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403);

        if (! $user->hasRole('program_manager')) {
            $user->assignRole('program_manager');
        }

        return back()->with('success', "Business role restored for {$user->email}.");
    }

    public function approveBusiness(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403);

        if (! $user->hasRole('program_manager')) {
            $user->assignRole('program_manager');
        }

        $user->forceFill([
            'business_super_admin_approved_at' => now(),
            'business_rejected_at' => null,
        ])->save();

        return back()->with('success', "{$user->business_name ?: $user->name} is now approved.");
    }

    public function rejectBusiness(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403);

        if (! $user->hasRole('program_manager')) {
            $user->assignRole('program_manager');
        }

        $user->forceFill([
            'business_super_admin_approved_at' => null,
            'business_rejected_at' => now(),
        ])->save();

        return back()->with('success', "{$user->business_name ?: $user->name} has been marked rejected.");
    }

    public function resendVerification(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403);

        if ($user->email_verified_at) {
            return back()->with('success', "{$user->email} is already verified.");
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "A new verification email was sent to {$user->email}.");
    }

    public function repair(User $user): RedirectResponse
    {
        abort_if($user->hasRole('super_admin'), 403);

        if (! $user->hasRole('program_manager')) {
            $user->assignRole('program_manager');
        }

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?: now(),
            'business_super_admin_approved_at' => $user->business_super_admin_approved_at ?: now(),
            'business_rejected_at' => null,
        ])->save();

        return back()->with('success', "Registration repaired for {$user->email}: business role, email verification and approval are complete.");
    }
}
