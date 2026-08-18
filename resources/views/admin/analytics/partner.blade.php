@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
<a href="{{ route('admin.analytics.business', $business) }}" class="btn btn-sm btn-outline-secondary mb-3">← Business</a>
<h1 class="h3">{{ $programPartner->user->name }}</h1><p class="text-muted">{{ $programPartner->user->email }} · {{ $programPartner->program->name }}</p>
<div class="row g-3 mb-4"><div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Sales generated</small><div class="h4">₦{{ number_format($orders->sum('total'),2) }}</div></div></div></div><div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Sales count</small><div class="h4">{{ $orders->count() }}</div></div></div></div><div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Partners recruited</small><div class="h4">{{ $recruited->count() }}</div></div></div></div></div>
<div class="card shadow-sm mb-4"><div class="card-header"><strong>Partners recruited by {{ $programPartner->user->name }}</strong></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Partner</th><th>Email</th><th>Program</th><th>Joined</th></tr></thead><tbody>@forelse($recruited as $child)<tr><td>{{ $child->user->name }}</td><td>{{ $child->user->email }}</td><td>{{ $child->program->name }}</td><td>{{ optional($child->created_at)->format('d M Y') }}</td></tr>@empty<tr><td colspan="4" class="text-center py-3">No recruited partners.</td></tr>@endforelse</tbody></table></div></div>
<div class="card shadow-sm"><div class="card-header"><strong>Sales made by this partner</strong></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Date</th></tr></thead><tbody>@forelse($orders as $order)<tr><td>{{ $order->order_number }}</td><td>{{ optional($order->customer)->name ?? $order->customer_email }}</td><td>₦{{ number_format($order->total,2) }}</td><td>{{ $order->created_at->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="4" class="text-center py-3">No sales.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
