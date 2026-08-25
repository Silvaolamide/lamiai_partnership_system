<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2></x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8"><div class="grid gap-6 md:grid-cols-2">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900"><p class="text-sm text-gray-500">{{ __("You're logged in!") }}</p><h3 class="mt-2 text-xl font-bold">Social Follow Gate</h3><p class="mt-2 text-sm text-gray-600">Create campaigns that ask customers to follow your social platforms before unlocking a resource.</p><a href="{{ route('business.social-follow.index') }}" class="mt-5 inline-block rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Manage Social Follow</a></div></div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900"><h3 class="text-xl font-bold">Partnerships</h3><p class="mt-2 text-sm text-gray-600">Manage your existing partnership activity and partner dashboard.</p><a href="{{ route('partner.dashboard') }}" class="mt-5 inline-block rounded-lg border px-4 py-2 text-sm font-semibold">Partner Dashboard</a></div></div>
    </div></div></div>
</x-app-layout>
