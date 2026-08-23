@extends('business.portal')
@section('title','Manage Program')
@section('heading','Manage Affiliate Program')
@section('content')
<form method="POST" action="{{route('business.programs.update',$program)}}" class="space-y-6">
@csrf @method('PUT')
<div class="bg-white border rounded-2xl p-6 shadow-soft">
    <h2 class="font-black text-lg">Program details</h2>
    <div class="grid md:grid-cols-2 gap-5 mt-5">
        <label class="block md:col-span-2"><span class="text-sm font-bold">Program name</span><input name="name" value="{{old('name',$program->name)}}" required class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label class="block md:col-span-2"><span class="text-sm font-bold">Description</span><textarea name="description" rows="4" class="mt-2 w-full rounded-xl border-slate-200">{{old('description',$program->description)}}</textarea></label>
        <label><span class="text-sm font-bold">Status</span><select name="status" class="mt-2 w-full rounded-xl border-slate-200"><option value="draft" @selected($program->status==='draft')>Draft</option><option value="active" @selected($program->status==='active')>Active</option><option value="paused" @selected($program->status==='paused')>Paused</option></select></label>
        <label><span class="text-sm font-bold">Attribution window (days)</span><input type="number" min="1" name="attribution_window_days" value="{{old('attribution_window_days',$program->attribution_window_days)}}" class="mt-2 w-full rounded-xl border-slate-200"></label>
        <label><span class="text-sm font-bold">Minimum payout</span><input type="number" min="0" step="0.01" name="minimum_payout" value="{{old('minimum_payout',$program->minimum_payout)}}" class="mt-2 w-full rounded-xl border-slate-200"></label>
    </div>
</div>

<div class="bg-white border rounded-2xl p-6 shadow-soft">
    <h2 class="font-black text-lg">Partner application policy</h2>
    <p class="mt-1 text-sm text-slate-500">Choose the approval required to join this program. Email verification remains required for partner access.</p>
    <div class="mt-5 space-y-3">
        <label class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 cursor-pointer">
            <input type="checkbox" name="partner_business_approval_required" value="1" @checked(old('partner_business_approval_required', $program->settings['partner_business_approval_required'] ?? false)) class="mt-1 rounded text-emerald-600">
            <span><b class="block">Require my business to approve partners</b><small class="block text-slate-500 mt-1">Applications stay pending until your business approves them. Use this when you want to vet partners before activation.</small></span>
        </label>
        <label class="flex items-start gap-3 rounded-2xl border border-violet-100 bg-violet-50/60 p-4 cursor-pointer">
            <input type="checkbox" name="partner_super_admin_approval_required" value="1" @checked(old('partner_super_admin_approval_required', $program->settings['partner_super_admin_approval_required'] ?? false)) class="mt-1 rounded text-violet-600">
            <span><b class="block">Require Super Admin approval</b><small class="block text-slate-500 mt-1">Applications stay pending until a Super Admin approves them. Leave this off for faster adoption.</small></span>
        </label>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600"><b>Adoption-first default:</b> when both options are off, a verified existing partner is activated immediately after joining this program.</div>
    </div>
</div>

<div class="bg-white border rounded-2xl p-6 shadow-soft">
    <h2 class="font-black text-lg">Products in this program</h2>
    <div class="grid md:grid-cols-2 gap-3 mt-5">
        @forelse($products as $product)
            <label class="flex gap-3 items-center border rounded-xl p-4"><input type="checkbox" name="products[]" value="{{$product->id}}" @checked($program->products->contains($product->id)) class="rounded text-violet-600"><span><b class="block">{{$product->name}}</b><small class="text-slate-400">{{$product->currency}} {{number_format((float)$product->price,2)}}</small></span></label>
        @empty
            <p class="text-slate-500">Create a product first.</p>
        @endforelse
    </div>
</div>

<div class="bg-white border rounded-2xl p-6 shadow-soft">
    <div class="flex justify-between items-center"><div><h2 class="font-black text-lg">Commission structure</h2><p class="text-sm text-slate-500">Set the percentage affiliates earn at each level.</p></div><button type="button" onclick="addRule()" class="rounded-xl border px-4 py-2 text-sm font-black">+ Add level</button></div>
    <div id="rules" class="space-y-3 mt-5">@foreach($program->commissionRules as $i=>$rule)<div class="grid grid-cols-[1fr_1fr_auto] gap-3"><input name="commission_rules[{{$i}}][level]" value="{{$rule->level}}" type="number" min="1" placeholder="Level" class="rounded-xl border-slate-200"><input name="commission_rules[{{$i}}][value]" value="{{$rule->value}}" type="number" min="0" max="100" step="0.01" placeholder="Commission %" class="rounded-xl border-slate-200"><button type="button" onclick="this.parentElement.remove()" class="rounded-xl border px-3">×</button></div>@endforeach</div>
</div>

<div class="flex justify-end gap-3"><a href="{{route('business.programs.index')}}" class="rounded-xl border px-5 py-3 font-black">Cancel</a><button class="brand rounded-xl px-6 py-3 text-white font-black">Save program</button></div>
</form>
<script>let i={{$program->commissionRules->count()}};function addRule(){document.getElementById('rules').insertAdjacentHTML('beforeend',`<div class="grid grid-cols-[1fr_1fr_auto] gap-3"><input name="commission_rules[${i}][level]" value="${i+1}" type="number" min="1" class="rounded-xl border-slate-200"><input name="commission_rules[${i}][value]" type="number" min="0" max="100" step="0.01" placeholder="Commission %" class="rounded-xl border-slate-200"><button type="button" onclick="this.parentElement.remove()" class="rounded-xl border px-3">×</button></div>`);i++}</script>
@endsection
