<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <title>Become an AIPM Marketer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .step { display: none; }
        .step.active { display: block; animation: enter .28s ease-out; }
        @keyframes enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .choice.selected { border-color: #0f766e; background: #f0fdfa; box-shadow: 0 0 0 3px rgba(13,148,136,.10); }
        .choice.selected .dot { background: #0f766e; border-color: #0f766e; box-shadow: inset 0 0 0 4px white; }
    </style>
</head>
<body class="min-h-screen bg-[#f3fbfa] text-slate-950">
<div class="min-h-screen lg:grid lg:grid-cols-[.9fr_1.1fr]">
    <aside class="hidden bg-teal-900 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div>
            <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-2xl bg-white text-sm font-black text-teal-800">AIPM</span><span class="text-lg font-black">AI Powered Marketing</span></div>
            <div class="mt-24 max-w-lg">
                <p class="text-sm font-black uppercase tracking-[.22em] text-teal-300">Marketer opportunity</p>
                <h1 class="mt-5 text-5xl font-black leading-[1.02] tracking-tight">Turn your network into income.</h1>
                <p class="mt-6 text-lg leading-8 text-teal-100/80">Tell us a little about yourself and your selling experience. It takes about a minute.</p>
                <div class="mt-10 grid gap-4">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><b>Performance-based</b><p class="mt-1 text-sm text-teal-100/70">Promote products and earn when results happen.</p></div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><b>Flexible</b><p class="mt-1 text-sm text-teal-100/70">Use WhatsApp, social media or your existing network.</p></div>
                </div>
            </div>
        </div>
        <p class="text-xs text-teal-200/60">AIPM · Marketer recruitment</p>
    </aside>

    <main class="flex min-h-screen items-center justify-center px-5 py-7 sm:px-8">
        <div class="w-full max-w-xl">
            <div class="mb-6 flex items-center justify-between lg:hidden">
                <div class="flex items-center gap-2"><span class="grid h-9 w-9 place-items-center rounded-xl bg-teal-700 text-xs font-black text-white">AIPM</span><span class="font-black">AIPM</span></div>
                <span class="text-xs font-bold text-slate-400">Marketer application</span>
            </div>

            <div class="mb-8">
                <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider text-slate-400"><span id="stepLabel">Step 1 of 6</span><span id="percent">17%</span></div>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-teal-100"><div id="progress" class="h-full rounded-full bg-teal-600 transition-all duration-300" style="width:16.66%"></div></div>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-black">Please check your answers.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form id="recruitmentForm" method="POST" action="{{ route('marketer.recruitment.store') }}" novalidate>
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                @foreach(['utm_source','utm_medium','utm_campaign','utm_content','utm_term'] as $field)
                    <input type="hidden" name="{{ $field }}" value="{{ old($field, $utm[$field] ?? '') }}">
                @endforeach

                <section class="step active" data-step="1">
                    <span class="text-sm font-black text-teal-700">Let's start</span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">What's your name?</h2>
                    <p class="mt-3 text-slate-500">Use the name you'd like us to call you.</p>
                    <input data-required type="text" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Your full name" class="mt-8 w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-lg outline-none transition focus:border-teal-600 focus:ring-4 focus:ring-teal-100">
                </section>

                <section class="step" data-step="2">
                    <span class="text-sm font-black text-teal-700">Stay connected</span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">What's your WhatsApp number?</h2>
                    <p class="mt-3 text-slate-500">We'll use WhatsApp if we'd like to discuss the opportunity with you.</p>
                    <input data-required type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" autocomplete="tel" placeholder="e.g. 08012345678" class="mt-8 w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-lg outline-none transition focus:border-teal-600 focus:ring-4 focus:ring-teal-100">
                </section>

                <section class="step" data-step="3">
                    <span class="text-sm font-black text-teal-700">Almost there</span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">What's your email address?</h2>
                    <p class="mt-3 text-slate-500">We'll only use it for relevant communication about this opportunity.</p>
                    <input data-required type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" class="mt-8 w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-lg outline-none transition focus:border-teal-600 focus:ring-4 focus:ring-teal-100">
                </section>

                <section class="step" data-step="4">
                    <span class="text-sm font-black text-teal-700">Your experience</span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Have you ever sold anything online?</h2>
                    <p class="mt-3 text-slate-500">There is no wrong answer. We want to understand where you're starting from.</p>
                    <div class="mt-8 grid gap-3">
                        <button type="button" data-choice="has_sold_online" data-value="1" class="choice flex w-full items-center justify-between rounded-2xl border-2 border-slate-200 bg-white p-5 text-left transition hover:border-teal-300"><span><b class="text-lg">Yes</b><small class="mt-1 block text-slate-500">I've sold something online.</small></span><span class="dot h-6 w-6 rounded-full border-2 border-slate-300"></span></button>
                        <button type="button" data-choice="has_sold_online" data-value="0" class="choice flex w-full items-center justify-between rounded-2xl border-2 border-slate-200 bg-white p-5 text-left transition hover:border-teal-300"><span><b class="text-lg">No</b><small class="mt-1 block text-slate-500">I haven't sold anything online yet.</small></span><span class="dot h-6 w-6 rounded-full border-2 border-slate-300"></span></button>
                    </div>
                    <input type="hidden" name="has_sold_online" value="{{ old('has_sold_online') }}">
                </section>

                <section class="step" data-step="5">
                    <span class="text-sm font-black text-teal-700">Tell us more</span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">What did you sell?</h2>
                    <p id="soldHint" class="mt-3 text-slate-500">Tell us briefly what you sold online. If you haven't sold anything yet, tell us what you'd like to sell.</p>
                    <textarea name="what_sold" rows="4" placeholder="e.g. clothes, digital products, courses, services..." class="mt-8 w-full resize-none rounded-2xl border border-slate-200 bg-white px-5 py-4 text-lg outline-none transition focus:border-teal-600 focus:ring-4 focus:ring-teal-100">{{ old('what_sold') }}</textarea>
                </section>

                <section class="step" data-step="6">
                    <span class="text-sm font-black text-teal-700">Final question</span>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">How was the sales?</h2>
                    <p class="mt-3 text-slate-500">Choose the option that best describes your online sales experience.</p>
                    <div class="mt-8 grid gap-3">
                        <button type="button" data-choice="sales_result" data-value="very_good" class="choice flex items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-5 text-left transition hover:border-teal-300"><span class="text-2xl">🚀</span><span class="flex-1"><b class="text-lg">Very good</b><small class="mt-1 block text-slate-500">I got strong results.</small></span><span class="dot h-6 w-6 rounded-full border-2 border-slate-300"></span></button>
                        <button type="button" data-choice="sales_result" data-value="good" class="choice flex items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-5 text-left transition hover:border-teal-300"><span class="text-2xl">👍</span><span class="flex-1"><b class="text-lg">Good</b><small class="mt-1 block text-slate-500">I made some sales and learned a lot.</small></span><span class="dot h-6 w-6 rounded-full border-2 border-slate-300"></span></button>
                        <button type="button" data-choice="sales_result" data-value="not_good" class="choice flex items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-5 text-left transition hover:border-teal-300"><span class="text-2xl">🌱</span><span class="flex-1"><b class="text-lg">Not good</b><small class="mt-1 block text-slate-500">It didn't work out as well as I hoped.</small></span><span class="dot h-6 w-6 rounded-full border-2 border-slate-300"></span></button>
                    </div>
                    <input type="hidden" name="sales_result" value="{{ old('sales_result') }}">
                </section>

                <div id="error" class="mt-4 hidden rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700"></div>
                <div class="mt-8 flex items-center justify-between gap-3">
                    <button type="button" id="back" class="rounded-xl px-4 py-3 font-bold text-slate-500 transition hover:bg-slate-100">← Back</button>
                    <button type="button" id="next" class="rounded-xl bg-teal-700 px-6 py-3.5 font-black text-white shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-800">Next →</button>
                    <button type="submit" id="submit" class="hidden rounded-xl bg-teal-700 px-6 py-3.5 font-black text-white shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-800">Submit application</button>
                </div>
            </form>
            <p class="mt-8 text-center text-xs leading-5 text-slate-400">Your details are used to review your marketer application and contact you about the opportunity.</p>
        </div>
    </main>
</div>
<script>
(() => {
    const form = document.getElementById('recruitmentForm');
    const steps = [...document.querySelectorAll('.step')];
    const next = document.getElementById('next');
    const back = document.getElementById('back');
    const submit = document.getElementById('submit');
    const error = document.getElementById('error');
    const label = document.getElementById('stepLabel');
    const percent = document.getElementById('percent');
    const progress = document.getElementById('progress');
    let current = 0;

    function update() {
        steps.forEach((step, i) => step.classList.toggle('active', i === current));
        const n = current + 1;
        label.textContent = `Step ${n} of 6`;
        percent.textContent = `${Math.round((n / 6) * 100)}%`;
        progress.style.width = `${(n / 6) * 100}%`;
        back.classList.toggle('invisible', current === 0);
        next.classList.toggle('hidden', current === 5);
        submit.classList.toggle('hidden', current !== 5);
        error.classList.add('hidden');
    }

    function validateStep() {
        const step = steps[current];
        const input = step.querySelector('[data-required]');
        if (input) {
            if (!input.value.trim() || (input.type === 'email' && !input.checkValidity())) {
                input.focus();
                error.textContent = input.type === 'email' ? 'Please enter a valid email address.' : 'Please answer this question before continuing.';
                error.classList.remove('hidden');
                return false;
            }
        }
        if (current === 3 && !form.querySelector('[name="has_sold_online"]').value) {
            error.textContent = 'Please choose Yes or No.';
            error.classList.remove('hidden');
            return false;
        }
        if (current === 5 && !form.querySelector('[name="sales_result"]').value) {
            error.textContent = 'Please choose one option.';
            error.classList.remove('hidden');
            return false;
        }
        return true;
    }

    document.querySelectorAll('[data-choice]').forEach(button => {
        button.addEventListener('click', () => {
            const name = button.dataset.choice;
            const value = button.dataset.value;
            form.querySelector(`[name="${name}"]`).value = value;
            document.querySelectorAll(`[data-choice="${name}"]`).forEach(el => el.classList.remove('selected'));
            button.classList.add('selected');
            error.classList.add('hidden');
            if (current === 3) setTimeout(() => { current++; update(); }, 180);
            if (current === 5) setTimeout(() => {}, 0);
        });
    });

    next.addEventListener('click', () => { if (validateStep()) { current++; update(); } });
    back.addEventListener('click', () => { if (current > 0) { current--; update(); } });
    form.addEventListener('submit', e => {
        if (!validateStep()) e.preventDefault();
        else { submit.disabled = true; submit.textContent = 'Submitting...'; }
    });
    update();
})();
</script>
</body>
</html>
