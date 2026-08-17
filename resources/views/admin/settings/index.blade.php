<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Platform Settings</h2>
            <p class="text-sm text-gray-500 mt-1">Control approvals, platform charges and when sale proceeds become eligible for payout.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3">{{ session('success') }}</div>@endif

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf @method('PUT')

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Partner applications</h3>
                            <p class="text-sm text-gray-500 mt-1 max-w-2xl">Email verification remains required. Super Admin approval stays a separate, configurable requirement.</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $partnerSuperAdminApprovalRequired ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600' }}">{{ $partnerSuperAdminApprovalRequired ? 'Super Admin approval ON' : 'Super Admin approval OFF' }}</span>
                    </div>

                    <label class="mt-7 flex items-start gap-4 rounded-2xl border p-5 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="partner_super_admin_approval_required" value="1" @checked($partnerSuperAdminApprovalRequired) class="mt-1 rounded text-violet-600">
                        <span>
                            <b class="block text-gray-900">Require Super Admin approval for partners</b>
                            <small class="block text-gray-500 mt-1">When OFF, email verification alone can satisfy the platform requirement unless the business requires its own approval.</small>
                        </span>
                    </label>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-black text-gray-900">Platform admin charge</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-2xl">Set the percentage the platform keeps from every paid sale. The charge is calculated from the gross order value and deducted from the business payout after partner and recruiter commissions.</p>

                    <div class="mt-6 max-w-sm">
                        <label class="block text-sm font-bold text-gray-700">Admin charge (%)</label>
                        <div class="relative mt-2">
                            <input type="number" min="0" max="100" step="0.01" name="admin_charge_percent" value="{{ old('admin_charge_percent', $adminChargePercent) }}" class="w-full rounded-xl border-gray-300 pr-10 focus:border-violet-500 focus:ring-violet-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">%</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-400">Example: 5% on a ₦100,000 sale = ₦5,000 platform charge. Maximum: 100%.</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-lg font-black text-gray-900">Payout protection period</h3>
                    <p class="text-sm text-gray-500 mt-1 max-w-2xl">After a paid sale, commissions and the business's net sale proceeds are held for this period before they can be requested for payout. This gives the platform time to handle cancellations, refunds and disputes.</p>

                    <div class="mt-6 max-w-sm">
                        <label class="block text-sm font-bold text-gray-700">Hold period (days)</label>
                        <input type="number" min="0" max="90" name="payout_delay_days" value="{{ old('payout_delay_days', $payoutDelayDays) }}" class="mt-2 w-full rounded-xl border-gray-300 focus:border-violet-500 focus:ring-violet-500">
                        <p class="mt-2 text-xs text-gray-400">0 = immediately eligible. Default: 7 days.</p>
                    </div>
                </div>

                <div class="flex justify-end"><button class="rounded-xl bg-violet-600 px-5 py-3 text-white font-bold">Save settings</button></div>
            </form>

            <div class="grid md:grid-cols-3 gap-4">
                <a href="{{ route('admin.partners.index') }}" class="bg-white border rounded-2xl p-5 hover:border-violet-200"><b>Partner applications</b><p class="text-sm text-gray-500 mt-1">Review partner approvals.</p></a>
                <a href="{{ route('admin.businesses.index') }}" class="bg-white border rounded-2xl p-5 hover:border-violet-200"><b>Business applications</b><p class="text-sm text-gray-500 mt-1">Approve businesses before onboarding.</p></a>
                <a href="{{ route('admin.payouts.index') }}" class="bg-white border rounded-2xl p-5 hover:border-violet-200"><b>Partner payouts</b><p class="text-sm text-gray-500 mt-1">Process partner commission payouts.</p></a>
            </div>
        </div>
    </div>
</x-app-layout>
