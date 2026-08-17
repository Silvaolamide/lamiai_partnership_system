<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Partnership Program</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="max-w-5xl mx-auto px-6 py-10">

    <div class="mb-8">
        <a
            href="{{ route('admin.programs.index') }}"
            class="text-blue-600"
        >
            ← Back to Programs
        </a>
    </div>

    <h1 class="text-3xl font-bold mb-8">
        Edit Partnership Program
    </h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.programs.update', $program) }}"
        method="POST"
    >

        @csrf
        @method('PUT')

        {{-- PROGRAM DETAILS --}}

        <div class="border rounded-lg p-6 mb-8">

            <h2 class="text-xl font-semibold mb-6">
                Program Details
            </h2>

            <div class="space-y-5">

                <div>
                    <label class="block font-medium mb-2">
                        Program Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $program->name) }}"
                        class="w-full border rounded-lg px-4 py-3"
                        required
                    >
                </div>

                <div>
                    <label class="block font-medium mb-2">
                        Slug
                    </label>

                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug', $program->slug) }}"
                        class="w-full border rounded-lg px-4 py-3"
                        required
                    >
                </div>

                <div>
                    <label class="block font-medium mb-2">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="4"
                        class="w-full border rounded-lg px-4 py-3"
                    >{{ old('description', $program->description) }}</textarea>
                </div>

                <div>
                    <label class="block font-medium mb-2">
                        Status
                    </label>

                    <select
                        name="status"
                        class="w-full border rounded-lg px-4 py-3"
                    >
                        <option
                            value="active"
                            @selected(old('status', $program->status) === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(old('status', $program->status) === 'inactive')
                        >
                            Inactive
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-2">
                        Attribution Window (Days)
                    </label>

                    <input
                        type="number"
                        name="attribution_window_days"
                        value="{{ old('attribution_window_days', $program->attribution_window_days) }}"
                        min="1"
                        class="w-full border rounded-lg px-4 py-3"
                    >
                </div>

                <div>
                    <label class="block font-medium mb-2">
                        Minimum Payout (₦)
                    </label>

                    <input
                        type="number"
                        name="minimum_payout"
                        value="{{ old('minimum_payout', $program->minimum_payout) }}"
                        min="0"
                        step="0.01"
                        class="w-full border rounded-lg px-4 py-3"
                    >
                </div>

            </div>

        </div>


        {{-- PRODUCTS --}}

        <div class="border rounded-lg p-6 mb-8">

            <h2 class="text-xl font-semibold mb-2">
                Products
            </h2>

            <p class="text-gray-500 mb-6">
                Select the products that partners can promote.
            </p>

            <div class="space-y-4">

                @foreach($products as $product)

                    <label class="flex items-center gap-3">

                        <input
                            type="checkbox"
                            name="products[]"
                            value="{{ $product->id }}"
                            @checked($program->products->contains($product->id))
                        >

                        <span>
                            {{ $product->name }}
                            — ₦{{ number_format($product->price, 2) }}
                        </span>

                    </label>

                @endforeach

            </div>

        </div>


        {{-- COMMISSION RULES --}}

        <div class="border rounded-lg p-6 mb-8">

            <div class="flex justify-between items-center mb-6">

                <div>
                    <h2 class="text-xl font-semibold">
                        Commission Rules
                    </h2>

                    <p class="text-gray-500">
                        Configure commission percentages by level.
                    </p>
                </div>

                <button
                    type="button"
                    id="add-rule"
                    class="border px-4 py-2 rounded-lg"
                >
                    + Add Level
                </button>

            </div>

            <div id="commission-rules">

                @foreach($program->commissionRules as $index => $rule)

                    <div class="commission-rule flex gap-4 mb-4">

                        <div class="flex-1">

                            <label class="block text-sm mb-1">
                                Level
                            </label>

                            <input
                                type="number"
                                name="commission_rules[{{ $index }}][level]"
                                value="{{ $rule->level }}"
                                min="0"
                                class="w-full border rounded-lg px-4 py-3"
                            >

                        </div>

                        <div class="flex-1">

                            <label class="block text-sm mb-1">
                                Commission %
                            </label>

                            <input
                                type="number"
                                name="commission_rules[{{ $index }}][value]"
                                value="{{ $rule->value }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full border rounded-lg px-4 py-3"
                            >

                        </div>

                    </div>

                @endforeach

            </div>

        </div>


        <button
            type="submit"
            class="bg-black text-white px-6 py-3 rounded-lg"
        >
            Save Configuration
        </button>

    </form>

</div>


<script>

let ruleIndex = {{ $program->commissionRules->count() }};

document.getElementById('add-rule').addEventListener('click', function () {

    const container = document.getElementById('commission-rules');

    const html = `
        <div class="commission-rule flex gap-4 mb-4">

            <div class="flex-1">

                <label class="block text-sm mb-1">
                    Level
                </label>

                <input
                    type="number"
                    name="commission_rules[${ruleIndex}][level]"
                    value="${ruleIndex}"
                    min="0"
                    class="w-full border rounded-lg px-4 py-3"
                >

            </div>

            <div class="flex-1">

                <label class="block text-sm mb-1">
                    Commission %
                </label>

                <input
                    type="number"
                    name="commission_rules[${ruleIndex}][value]"
                    value="0"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full border rounded-lg px-4 py-3"
                >

            </div>

        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    ruleIndex++;

});

</script>

</body>
</html>