<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Partners</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<div class="max-w-6xl mx-auto px-6 py-10">

    <h1 class="text-3xl font-bold mb-8">
        Partners
    </h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border rounded-lg overflow-hidden">

        <table class="w-full">

            <thead class="bg-gray-100">

                <tr>
                    <th class="text-left px-6 py-4">
                        Partner
                    </th>

                    <th class="text-left px-6 py-4">
                        Program
                    </th>

                    <th class="text-left px-6 py-4">
                        Code
                    </th>

                    <th class="text-left px-6 py-4">
                        Status
                    </th>

                    <th class="text-left px-6 py-4">
                        Action
                    </th>
                </tr>

            </thead>

            <tbody>

                @forelse($partners as $partner)

                    <tr class="border-t">

                        <td class="px-6 py-4">

                            <div class="font-medium">
                                {{ $partner->user->name }}
                            </div>

                            <div class="text-sm text-gray-500">
                                {{ $partner->user->email }}
                            </div>

                        </td>

                        <td class="px-6 py-4">
                            {{ $partner->program->name }}
                        </td>

                        <td class="px-6 py-4 font-mono">
                            {{ $partner->partner_code }}
                        </td>

                        <td class="px-6 py-4">
                            {{ ucfirst($partner->status) }}
                        </td>

                        <td class="px-6 py-4">

                            @if($partner->status === 'pending')

                                <div class="flex gap-2">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.partners.approve', $partner) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="bg-green-600 text-white px-4 py-2 rounded"
                                        >
                                            Approve
                                        </button>

                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.partners.reject', $partner) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="bg-red-600 text-white px-4 py-2 rounded"
                                        >
                                            Reject
                                        </button>

                                    </form>

                                </div>

                            @else

                                <span class="text-gray-500">
                                    No action
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="px-6 py-10 text-center text-gray-500"
                        >
                            No partners found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $partners->links() }}
    </div>

</div>

</body>
</html>