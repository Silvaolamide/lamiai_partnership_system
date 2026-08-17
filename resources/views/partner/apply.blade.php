<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Become a Partner</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="max-w-xl mx-auto px-6 py-10">

    <h1 class="text-3xl font-bold mb-2">
        Become a Partner
    </h1>

    <p class="text-gray-600 mb-8">
        Promote our products and earn commissions on successful sales.
    </p>
    @if(session('success'))

        <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
            {{ session('success') }}
        </div>

    @endif
    @if($errors->any())

        <div class="bg-red-100 text-red-700 p-4 rounded mb-6">

            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach

        </div>

    @endif

    <form
        method="POST"
        action="{{ route('partner.apply.store') }}"
        class="space-y-6"
    >

        @csrf

        <div>

            <label class="block font-medium mb-2">
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>

        <div>

            <label class="block font-medium mb-2">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>

        <div>

            <label class="block font-medium mb-2">
                Phone / WhatsApp
            </label>

            <input
                type="text"
                name="phone"
                value="{{ old('phone') }}"
                required
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>

        <div>

            <label class="block font-medium mb-2">
                Password
            </label>

            <input
                type="password"
                name="password"
                required
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>

        <div>

            <label class="block font-medium mb-2">
                Partnership Program
            </label>

            <select
                name="program_id"
                required
                class="w-full border rounded-lg px-4 py-3"
            >

                <option value="">
                    Select a program
                </option>

                @foreach($programs as $program)

                    <option
                        value="{{ $program->id }}"
                        @selected(old('program_id') == $program->id)
                    >
                        {{ $program->name }}
                    </option>

                @endforeach

            </select>

        </div>

        <button
            type="submit"
            class="w-full bg-black text-white py-3 rounded-lg"
        >
            Apply as Partner
        </button>

    </form>

</div>

</body>
</html>