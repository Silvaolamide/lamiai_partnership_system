<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Partnership Program</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="max-w-3xl mx-auto px-6 py-10">

    <div class="mb-8">
        <a
            href="{{ route('admin.programs.index') }}"
            class="text-blue-600 hover:underline"
        >
            ← Back to Programs
        </a>
    </div>

    <h1 class="text-3xl font-bold mb-8">
        Create Partnership Program
    </h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6">

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    <form
        action="{{ route('admin.programs.store') }}"
        method="POST"
        class="space-y-6"
    >

        @csrf

        <div>
            <label class="block font-medium mb-2">
                Program Name
            </label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="w-full border rounded-lg px-4 py-3"
                placeholder="e.g. AI Filmmaking Partner Program"
            >

            @error('name')
                <p class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label class="block font-medium mb-2">
                Slug
            </label>

            <input
                type="text"
                name="slug"
                value="{{ old('slug') }}"
                class="w-full border rounded-lg px-4 py-3"
                placeholder="ai-filmmaking-partners"
            >

            @error('slug')
                <p class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label class="block font-medium mb-2">
                Description
            </label>

            <textarea
                name="description"
                rows="4"
                class="w-full border rounded-lg px-4 py-3"
                placeholder="Describe the partnership program..."
            >{{ old('description') }}</textarea>

            @error('description')
                <p class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label class="block font-medium mb-2">
                Attribution Window (Days)
            </label>

            <input
                type="number"
                name="attribution_window_days"
                value="{{ old('attribution_window_days', 30) }}"
                min="1"
                class="w-full border rounded-lg px-4 py-3"
            >

            @error('attribution_window_days')
                <p class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

         <div>
            <label class="block font-medium mb-2">
                Minimum Payout (₦)
            </label>

            <input
                type="number"
                name="minimum_payout"
                value="{{ old('minimum_payout', 10000) }}"
                min="0"
                step="0.01"
                class="w-full border rounded-lg px-4 py-3"
            >

            @error('minimum_payout')
                <p class="text-red-600 text-sm mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <button
            type="submit"
            class="bg-black text-white px-6 py-3 rounded-lg"
        >
            Create Program
        </button>

    </form>

</div>

</body>
</html>