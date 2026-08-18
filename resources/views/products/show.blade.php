<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Partnership Program</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%); }
        .gradient-accent { background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .btn-modern { transition: all 0.2s ease; }
        .btn-modern:hover { transform: scale(1.02); }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if($referralError)
        <div class="mb-8 animate-fade-in">
            <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-r-xl p-4 flex items-start gap-3 shadow-sm">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold text-red-900">Referral Code Invalid</p>
                    <p class="text-red-800 text-sm mt-1">{{ $referralError }}</p>
                </div>
            </div>
        </div>
    @elseif($referralProcessed && $referringPartner)
        <div class="mb-8 animate-fade-in">
            <div class="bg-gradient-to-r from-emerald-50 to-green-50 border-l-4 border-emerald-500 rounded-r-xl p-4 flex items-start gap-3 shadow-sm">
                <svg class="w-6 h-6 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div>
                    <p class="font-semibold text-emerald-900">Partnership Referral Active ✓</p>
                    <p class="text-emerald-800 text-sm mt-1">You are being referred by <span class="font-bold">{{ $referringPartner->user->name }}</span>. They'll earn a commission on your purchase.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg p-8 sm:p-12 mb-8 card-hover">
        <div class="mb-8">
            <span class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-semibold mb-4">Premium Course</span>
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4 leading-tight">{{ $product->name }}</h1>
            @if($product->description)
                <p class="text-lg text-gray-600 leading-relaxed">{{ $product->description }}</p>
            @endif
        </div>

        <div class="grid sm:grid-cols-2 gap-4 mb-10 p-6 bg-gradient-to-br from-slate-50 to-blue-50 rounded-xl">
            <div class="flex gap-3"><div class="flex-shrink-0"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100"><svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div></div><div><h3 class="font-semibold text-gray-900">Professional Techniques</h3><p class="text-sm text-gray-600">Industry-standard filmmaking practices</p></div></div>
            <div class="flex gap-3"><div class="flex-shrink-0"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100"><svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div></div><div><h3 class="font-semibold text-gray-900">AI-Powered Tools</h3><p class="text-sm text-gray-600">Cutting-edge editing technology</p></div></div>
            <div class="flex gap-3"><div class="flex-shrink-0"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100"><svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 01-1.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0l8-8z" clip-rule="evenodd"/></svg></div></div><div><h3 class="font-semibold text-gray-900">Monetization Strategies</h3><p class="text-sm text-gray-600">Turn creativity into income</p></div></div>
            <div class="flex gap-3"><div class="flex-shrink-0"><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100"><svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div></div><div><h3 class="font-semibold text-gray-900">Real Projects</h3><p class="text-sm text-gray-600">Build your portfolio</p></div></div>
        </div>

        <div class="grid sm:grid-cols-2 gap-8 sm:gap-12 items-center py-8 border-y border-gray-200">
            <div><p class="text-gray-600 text-sm font-medium mb-2">Investment</p><div class="flex items-baseline gap-2"><span class="text-5xl font-bold text-gray-900">₦{{ number_format($product->price / 1000, 0) }}</span><span class="text-gray-600">K</span></div><p class="text-gray-600 text-sm mt-3">One-time payment • Lifetime access</p></div>
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6"><h3 class="font-semibold text-gray-900 mb-3">What's Included</h3><ul class="space-y-2 text-sm text-gray-700"><li class="flex gap-2"><span class="text-emerald-600">•</span> Full video course</li><li class="flex gap-2"><span class="text-emerald-600">•</span> Resource files & assets</li><li class="flex gap-2"><span class="text-emerald-600">•</span> Community access</li><li class="flex gap-2"><span class="text-emerald-600">•</span> Certification</li></ul></div>
        </div>

        @if($referralProcessed && $referringPartner)
            <div class="mt-8 p-6 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-200"><p class="text-sm text-emerald-900"><span class="font-bold">Referred by {{ $referringPartner->user->name }}</span> — They'll earn a commission on this purchase, creating a win-win partnership.</p></div>
        @endif

        <form action="{{ route('checkout.start') }}" method="GET" class="mt-8">
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button type="submit" class="w-full gradient-primary text-white py-4 px-6 rounded-lg font-semibold text-lg btn-modern hover:shadow-xl transition-all">
                <span class="flex items-center justify-center gap-2">Proceed to Checkout <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span>
            </button>
            <p class="text-center text-gray-500 text-sm mt-4 flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>Secure checkout • 30-day money-back guarantee</p>
        </form>
    </div>

    <div class="grid md:grid-cols-3 gap-6 text-center"><div><p class="text-3xl font-bold text-gray-900">5000+</p><p class="text-gray-600">Active Learners</p></div><div><p class="text-3xl font-bold text-gray-900">4.9/5</p><p class="text-gray-600">Average Rating</p></div><div><p class="text-3xl font-bold text-gray-900">₦50M+</p><p class="text-gray-600">Earned by Partners</p></div></div>
</div>
</body>
</html>
