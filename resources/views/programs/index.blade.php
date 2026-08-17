<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Partnership Programs</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="max-w-6xl mx-auto px-6 py-10">

        <h1 class="text-3xl font-bold mb-8">
            Partnership Programs
        </h1>

        @forelse ($programs as $program)

            <div class="border rounded-lg p-6 mb-4">

                <h2 class="text-xl font-semibold">
                    {{ $program->name }}
                </h2>

                <p class="text-gray-600 mt-2">
                    {{ $program->description }}
                </p>

                <div class="mt-4">
                    <span>
                        Status:
                        {{ $program->status }}
                    </span>
                </div>

            </div>
            <div class="flex justify-between">

            <div>
                <h2 class="text-xl font-semibold">
                    {{ $program->name }}
                </h2>

                <p class="text-gray-600 mt-2">
                    {{ $program->description }}
                </p>
            </div>

            <a
                href="{{ route('admin.programs.edit', $program) }}"
                class="text-blue-600 hover:underline"
            >
                Configure
            </a>

        </div>

        @empty

            <p>
                No partnership programs found.
            </p>

        @endforelse

    </div>

</body>
</html>