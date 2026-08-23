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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private const MANAGEABLE_ROLES = ['customer', 'partner', 'program_manager'];

    public function index(Request $request)
    {
        $query = User::query()->with('roles')->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'));
        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('business_name', 'like', "%{$search}%")->orWhere('business_phone', 'like', "%{$search}%");
            });
        }
        if ($role = $request->input('role')) $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        if ($type = $request->input('registration_type')) $query->where('registration_type', $type);
        if ($request->boolean('needs_attention')) {
            $query->where(function ($q) {
                $q->whereNull('email_verified_at')->orWhere(function ($q) {
                    $q->where('registration_type', 'business')->where(function ($q) { $q->whereNull('business_super_admin_approved_at')->orWhereNull('business_name')->orWhereNull('business_phone'); });
                });
            });
        }
        $users = $query->latest()->paginate(30)->withQueryString();
        $stats = [
            'total' => User::query()->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))->count(),
            'unverified' => User::query()->whereNull('email_verified_at')->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))->count(),
            'business_issues' => User::query()->where('registration_type', 'business')->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'))->where(function ($q) { $q->whereNull('business_super_admin_approved_at')->orWhereNull('business_name')->orWhereNull('business_phone'); })->count(),
            'partners' => User::query()->role('partner')->count(),
            'customers' => User::query()->role('customer')->count(),
        ];
        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user)
    {
        $this->protectSuperAdmin($user);
        $user->load('roles', 'programPartners');
        $paymentSubmissions = PaymentSubmission::with('order')->whereHas('order', fn ($q) => $q->where('customer_id', $user->id))->latest()->limit(20)->get();
        $paymentDisputes = PaymentDispute::with('order')->where('customer_id', $user->id)->latest()->limit(20)->get();
        $data = [
            'orders' => Order::where('customer_id', $user->id)->latest()->limit(20)->get(),
            'orderCount' => Order::where('customer_id', $user->id)->count(),
            'paymentSubmissions' => $paymentSubmissions,
            'paymentDisputes' => $paymentDisputes,
            'pendingPayments' => $paymentSubmissions->where('status', 'pending')->values(),
            'openDisputes' => $paymentDisputes->where('status', '!=', 'resolved')->values(),
            'payoutCount' => Payout::where('partner_id', $user->id)->count(),
            'businessPayoutCount' => BusinessPayout::where('business_id', $user->id)->count(),
            'partnerCount' => ProgramPartner::where('user_id', $user->id)->count(),
        ];
        $issues = $this->issuesFor($user);
        if ($data['pendingPayments']->isNotEmpty()) $issues[] = ['severity' => 'high', 'label' => $data['pendingPayments']->count().' payment submission(s) awaiting verification', 'action' => 'Review the payment proof and confirm or reject each submission.'];
        if ($data['openDisputes']->isNotEmpty()) $issues[] = ['severity' => 'high', 'label' => $data['openDisputes']->count().' payment dispute(s) require resolution', 'action' => 'Review the dispute and record the resolution.'];
        return view('admin.users.show', compact('user', 'data', 'issues'));
    }

    public function edit(User $user)
    {
        $this->protectSuperAdmin($user);
        $user->load('roles');
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->protectSuperAdmin($user);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)], 'registration_type' => ['required', Rule::in(['customer', 'business'])],
            'business_name' => ['nullable', 'string', 'max:255'], 'business_website' => ['nullable', 'string', 'max:255'], 'business_industry' => ['nullable', 'string', 'max:255'], 'business_phone' => ['nullable', 'string', 'max:50'], 'roles' => ['array'], 'roles.*' => [Rule::in(self::MANAGEABLE_ROLES)],
        ]);
        $user->forceFill(collect($validated)->except('roles')->all())->save();
        $roles = collect($validated['roles'] ?? [])->unique()->values()->all();
        if ($user->registration_type === 'business' && ! in_array('program_manager', $roles, true)) $roles[] = 'program_manager';
        if ($user->registration_type === 'customer' && ! in_array('customer', $roles, true)) $roles[] = 'customer';
        $user->syncRoles($roles);
        return redirect()->route('admin.users.show', $user)->with('success', 'User account updated successfully.');
    }

    public function verifyEmail(User $user): RedirectResponse
    {
        $this->protectSuperAdmin($user);
        if (! $user->email_verified_at) $user->forceFill(['email_verified_at' => now()])->save();
        return back()->with('success', 'Email verification status repaired.');
    }

    public function repairRegistration(User $user): RedirectResponse
    {
        $this->protectSuperAdmin($user);
        if ($user->registration_type !== 'business') {
            if (! $user->email_verified_at) $user->forceFill(['email_verified_at' => now()])->save();
            return back()->with('success', 'Customer registration repaired: email verification is complete.');
        }
        if (! $user->hasRole('program_manager')) $user->assignRole('program_manager');
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?: now(), 'business_super_admin_approved_at' => $user->business_super_admin_approved_at ?: now(), 'business_rejected_at' => null])->save();
        return back()->with('success', 'Business registration repaired: verification, role and approval are complete.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->protectSuperAdmin($user);
        $activity = $this->activityFor($user);
        if ($activity['total'] > 0) return back()->with('error', "This account cannot be permanently deleted because it has associated activity: {$activity['summary']}. Resolve, refund or archive that activity first.");
        DB::transaction(function () use ($user) { $user->roles()->detach(); $user->permissions()->detach(); $user->delete(); });
        return redirect()->route('admin.users.index')->with('success', 'User account permanently deleted.');
    }

    private function protectSuperAdmin(User $user): void { abort_if($user->hasRole('super_admin'), 403, 'Super Admin accounts are protected from user-management operations.'); }

    private function issuesFor(User $user): array
    {
        $issues = [];
        if (! $user->email_verified_at) $issues[] = ['severity' => 'high', 'label' => 'Email is not verified', 'action' => 'Verify the email from this page.'];
        if ($user->registration_type === 'business') {
            if (! $user->business_name || ! $user->business_phone) $issues[] = ['severity' => 'high', 'label' => 'Business registration information is incomplete', 'action' => 'Edit the account and complete the missing business fields.'];
            if (! $user->hasRole('program_manager')) $issues[] = ['severity' => 'high', 'label' => 'Program manager role is missing', 'action' => 'Use Repair registration or edit roles.'];
            if (! $user->business_super_admin_approved_at) $issues[] = ['severity' => 'high', 'label' => 'Business registration is not approved', 'action' => 'Use Repair registration or the Registration Recovery workflow.'];
            if ($user->business_rejected_at) $issues[] = ['severity' => 'medium', 'label' => 'Business registration was previously rejected', 'action' => 'Review the registration information before repairing it.'];
        }
        return $issues;
    }

    private function activityFor(User $user): array
    {
        $checks = [
            'orders' => Order::where('customer_id', $user->id)->exists(), 'partner enrollments' => ProgramPartner::where('user_id', $user->id)->exists(), 'partner payouts' => Payout::where('partner_id', $user->id)->exists(), 'business payouts' => BusinessPayout::where('business_id', $user->id)->exists(),
            'payment submissions' => PaymentSubmission::whereHas('order', fn ($q) => $q->where('customer_id', $user->id))->exists(), 'payment disputes' => PaymentDispute::where('customer_id', $user->id)->exists(),
        ];
        $found = array_keys(array_filter($checks));
        return ['total' => count($found), 'summary' => implode(', ', $found)];
    }
}
