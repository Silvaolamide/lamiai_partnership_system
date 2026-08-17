<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Platform Settings</h2>
                <p class="text-sm text-gray-500 mt-1">Control how partner applications are approved.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))<div class="rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3">{{session('success')}}</div>@endif
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-start justify-between gap-6">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Partner applications</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-2xl">Email verification is always required. This switch controls whether the LAMI AI Super Admin must also approve a partner before the partner can access the dashboard.</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $partnerSuperAdminApprovalRequired ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600' }}">{{ $partnerSuperAdminApprovalRequired ? 'Super Admin approval ON' : 'Super Admin approval OFF' }}</span>
                </div>

                <form method="POST" action="{{route('admin.settings.update')}}" class="mt-7">
                    @csrf @method('PUT')
                    <label class="flex items-start gap-4 rounded-2xl border p-5 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="partner_super_admin_approval_required" value="1" @checked($partnerSuperAdminApprovalRequired) class="mt-1 rounded text-violet-600">
                        <span>
                            <b class="block text-gray-900">Require Super Admin approval for partners</b>
                            <small class="block text-gray-500 mt-1">OFF = email verification alone can be enough unless a business has enabled its own partner approval. ON = Super Admin approval becomes an additional requirement.</small>
                        </span>
                    </label>
                    <div class="mt-5 flex justify-end"><button class="rounded-xl bg-violet-600 px-5 py-3 text-white font-bold">Save settings</button></div>
                </form>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <a href="{{route('admin.partners.index')}}" class="bg-white border rounded-2xl p-5 hover:border-violet-200"><b>Partner applications</b><p class="text-sm text-gray-500 mt-1">Review Super Admin approvals and rejections.</p></a>
                <a href="{{route('admin.businesses.index')}}" class="bg-white border rounded-2xl p-5 hover:border-violet-200"><b>Business applications</b><p class="text-sm text-gray-500 mt-1">Approve businesses before they can complete onboarding.</p></a>
            </div>
        </div>
    </div>
</x-app-layout>
