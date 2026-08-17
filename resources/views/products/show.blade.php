<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Partnership Program</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

<div class="max-w-4xl mx-auto px-6 py-12">

    <!-- Referral Banner -->
    @if($referralError)
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
            <strong>Referral Code Invalid:</strong> {{ $referralError }}
        </div>
    @elseif($referralProcessed && $referringPartner)
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
            <strong>Partnership Referral Active:</strong> 
            You are referred by <strong>{{ $referringPartner->user->name }}</strong>. 
            When you purchase, they will earn a referral commission.
        </div>
    @endif

    <!-- Product Details -->
    <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        
        <h1 class="text-4xl font-bold mb-4">
            {{ $product->name }}
        </h1>

        @if($product->description)
            <p class="text-gray-600 text-lg mb-6">
                {{ $product->description }}
            </p>
        @endif

        <!-- Features Section (placeholder) -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg">
            <h2 class="text-xl font-bold mb-4">What You'll Learn</h2>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    Professional video filmmaking techniques
                </li>
                <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    AI-powered editing and post-production
                </li>
                <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    Monetization strategies for content creators
                </li>
                <li class="flex items-start">
                    <span class="text-green-600 mr-2">✓</span>
                    Real-world project portfolio building
                </li>
            </ul>
        </div>

        <!-- Pricing and Purchase -->
        <div class="border-t pt-8">
            <div class="mb-6">
                <p class="text-gray-600">Price</p>
                <p class="text-5xl font-bold text-gray-900">
                    ₦{{ number_format($product->price, 2) }}
                </p>
                <p class="text-gray-500 mt-2">{{ $product->currency }}</p>
            </div>

            @if($referralProcessed && $referringPartner)
                <p class="mb-6 text-sm text-gray-600">
                    <strong>Referral Bonus:</strong> When you complete your purchase, 
                    {{ $referringPartner->user->name }} will receive a referral commission 
                    as configured by the partnership program.
                </p>
            @endif

            <!-- In this phase, we'll use a simple checkout simulation -->
            <form 
                action="{{ route('checkout.create') }}" 
                method="POST"
                class="space-y-4"
            >
                @csrf

                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <button 
                    type="submit"
                    class="w-full bg-black text-white py-4 rounded-lg text-lg font-semibold hover:bg-gray-900 transition"
                >
                    Proceed to Checkout
                </button>
            </form>

            <p class="text-center text-gray-500 text-sm mt-4">
                Secure payment processing • Money-back guarantee
            </p>
        </div>

    </div>

    <!-- Trust Indicators -->
    <div class="grid md:grid-cols-3 gap-6 text-center">
        <div>
            <p class="text-3xl font-bold text-gray-900">5000+</p>
            <p class="text-gray-600">Active Learners</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-gray-900">4.9/5</p>
            <p class="text-gray-600">Average Rating</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-gray-900">₦50M+</p>
            <p class="text-gray-600">Earned by Partners</p>
        </div>
    </div>

</div>

</body>
</html>
