@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-1">All Businesses</h1><p class="text-muted mb-0">Fast access to every business, its programs, partners and performance.</p></div>
    </div>
    <div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Business</th><th>Programs</th><th>Partners</th><th>Orders</th><th>Gross Sales</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($businesses as $business)
            <tr><td><strong>{{ $business->name }}</strong><br><small class="text-muted">{{ $business->email }}</small></td><td>{{ $business->analytics['programs'] }}</td><td>{{ $business->analytics['partners'] }}</td><td>{{ $business->analytics['orders'] }}</td><td>₦{{ number_format($business->analytics['sales'], 2) }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('admin.analytics.business', $business) }}">View business</a></td></tr>
        @empty <tr><td colspan="6" class="text-center py-4">No businesses found.</td></tr>@endforelse
        </tbody>
    </table></div></div>
</div>
@endsection
