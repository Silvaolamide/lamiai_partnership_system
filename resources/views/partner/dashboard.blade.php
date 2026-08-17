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

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto px-6 py-10">

    <div class="mb-8">

        <h1 class="text-3xl font-bold">
            Partner Dashboard
        </h1>

        <p class="text-gray-600 mt-2">
            Welcome, {{ auth()->user()->name }}
        </p>

    </div>


    @forelse($partners as $partner)

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">

            <h2 class="text-xl font-bold mb-2">
                {{ $partner->program->name }}
            </h2>

            <p class="text-gray-500 mb-6">
                Your partnership information
            </p>


            <div class="grid md:grid-cols-2 gap-6">

                <div>

                    <p class="text-sm text-gray-500">
                        Your Referral Code
                    </p>

                    <p class="text-2xl font-bold mt-1">
                        {{ $partner->partner_code }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-gray-500">
                        Status
                    </p>

                    <p class="text-green-600 font-semibold mt-1">
                        Active
                    </p>

                </div>

            </div>


            <div class="mt-8">

                <p class="text-sm text-gray-500 mb-2">
                    Your Referral Link
                </p>

                @php

                    $referralLink =
                        url('/ai-video-creation') .
                        '?ref=' .
                        $partner->partner_code;

                @endphp


                <div class="flex gap-2">

                    <input
                        type="text"
                        value="{{ $referralLink }}"
                        readonly
                        class="flex-1 border rounded-lg px-4 py-3 bg-gray-50"
                    >

                    <button
                        onclick="navigator.clipboard.writeText('{{ $referralLink }}')"
                        class="bg-black text-white px-5 rounded-lg"
                    >
                        Copy
                    </button>

                </div>

            </div>

        </div>

    @empty

        <div class="bg-white rounded-xl p-8">

            <h2 class="text-xl font-bold">
                No active partnerships
            </h2>

            <p class="text-gray-600 mt-2">
                You don't currently have an approved partnership.
            </p>

        </div>

    @endforelse

</div>

</body>

</html>