<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Partner Dashboard</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="bg-gray-50">

<div class="max-w-7xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="mb-10">
        <h1 class="text-4xl font-bold text-gray-900">
            Partner Dashboard
        </h1>

        <p class="text-gray-600 mt-2">
            Welcome, {{ auth()->user()->name }}
        </p>
    </div>

    <!-- Overall Statistics -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">

        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium mb-2">Total Pending Commission</p>
            <p class="text-3xl font-bold text-gray-900">₦{{ number_format($totalPending, 2) }}</p>
            <p class="text-gray-500 text-xs mt-2">Available for payout</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium mb-2">Total Paid Commission</p>
            <p class="text-3xl font-bold text-green-600">₦{{ number_format($totalPaid, 2) }}</p>
            <p class="text-gray-500 text-xs mt-2">Lifetime earnings</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium mb-2">Active Programs</p>
            <p class="text-3xl font-bold text-gray-900">{{ count($programStats) }}</p>
            <p class="text-gray-500 text-xs mt-2">Partnership programs</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-gray-600 text-sm font-medium mb-2">Total Sales</p>
            <p class="text-3xl font-bold text-gray-900">
                {{ array_sum(array_column($programStats, 'paid_orders')) ?? 0 }}
            </p>
            <p class="text-gray-500 text-xs mt-2">Completed orders</p>
        </div>

    </div>

    <!-- Program Details -->
    @forelse($programStats as $programStat)

        <div class="bg-white rounded-lg shadow-sm p-8 mb-8">

            <!-- Program Header -->
            <div class="flex justify-between items-start mb-6 pb-6 border-b">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $programStat['program']->name }}
                    </h2>
                    <p class="text-gray-600 mt-1">
                        Partnership Code: <span class="font-mono font-bold">{{ $programStat['partner']->partner_code }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                        Active
                    </span>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600 text-sm mb-1">Pending Commission</p>
                    <p class="text-2xl font-bold text-gray-900">
                        ₦{{ number_format($programStat['stats']['pending'], 2) }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        ({{ $programStat['stats']['pending_count'] }} commissions)
                    </p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600 text-sm mb-1">Paid Commission</p>
                    <p class="text-2xl font-bold text-green-600">
                        ₦{{ number_format($programStat['stats']['paid'], 2) }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        ({{ $programStat['stats']['paid_count'] }} commissions)
                    </p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600 text-sm mb-1">Total Sales</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $programStat['paid_orders'] }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        Completed orders
                    </p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600 text-sm mb-1">Recruited Partners</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $programStat['recruited_partners_count'] }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                        Active downline
                    </p>
                </div>

            </div>

            <!-- Referral Link Section -->
            <div class="mb-8 pb-8 border-b">
                <h3 class="text-lg font-bold mb-4">Your Referral Link</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Promote the product
                        </label>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="referral_link_{{ $programStat['partner']->id }}"
                                value="{{ route('product.show', ['slug' => 'ai-filmmaking-masterclass']) }}?ref={{ $programStat['partner']->partner_code }}"
                                readonly
                                class="flex-1 border rounded-lg px-4 py-2 bg-gray-50 font-mono text-sm"
                            >

                            <button
                                onclick="copyToClipboard('referral_link_{{ $programStat['partner']->id }}')"
                                class="bg-black text-white px-6 rounded-lg hover:bg-gray-900 transition font-semibold"
                            >
                                Copy
                            </button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600">
                        Share this link with your audience to earn commissions. 
                        When someone purchases using your link, you'll earn 
                        <strong>20%</strong>
                        commission on the sale.
                    </p>
                </div>
            </div>

            <!-- Recruitment Link -->
            <div class="mb-8 pb-8 border-b">
                <h3 class="text-lg font-bold mb-4">Recruit Other Partners</h3>

                <p class="text-gray-600 mb-4">
                    Invite other marketers to join your team. When they recruit customers, you'll earn 
                    <strong>5%</strong>
                    on each of their sales.
                </p>

                <div class="flex gap-2">
                    <input
                        type="text"
                        id="recruit_link_{{ $programStat['partner']->id }}"
                        value="{{ route('partner.apply') }}?recruiter_code={{ $programStat['partner']->partner_code }}"
                        readonly
                        class="flex-1 border rounded-lg px-4 py-2 bg-gray-50 font-mono text-sm"
                    >

                    <button
                        onclick="copyToClipboard('recruit_link_{{ $programStat['partner']->id }}')"
                        class="bg-black text-white px-6 rounded-lg hover:bg-gray-900 transition font-semibold"
                    >
                        Copy
                    </button>
                </div>
            </div>

            <!-- Commission Rules Info -->
            <div>
                <h3 class="text-lg font-bold mb-4">Commission Structure</h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">
                                Level 1 (Direct Sales)
                            </p>
                            <p class="text-sm text-gray-600">
                                Percentage of sale
                            </p>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">
                            20%
                        </p>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">
                                Level 2 (Recruiter Bonus)
                            </p>
                            <p class="text-sm text-gray-600">
                                Percentage of sale
                            </p>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">
                            5%
                        </p>
                    </div>
                </div>
            </div>

        </div>

    @empty

        <div class="bg-white rounded-lg shadow-sm p-12 text-center">

            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                No Active Partnerships
            </h2>

            <p class="text-gray-600 mb-6">
                You don't currently have an approved partnership. 
                Apply to join a partnership program to start earning commissions.
            </p>

            <a
                href="{{ route('partner.apply') }}"
                class="inline-block bg-black text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-900 transition"
            >
                Browse Partnership Programs
            </a>

        </div>

    @endforelse

</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.value;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show visual feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-green-600');
        
        setTimeout(function() {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
        }, 2000);
    });
}
</script>

</body>

</html>