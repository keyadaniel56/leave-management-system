<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <p class="text-sm text-gray-500">Total Requests</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg shadow p-5 text-center">
                    <p class="text-sm text-yellow-600">Pending</p>
                    <p class="text-3xl font-bold text-yellow-700">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-green-50 rounded-lg shadow p-5 text-center">
                    <p class="text-sm text-green-600">Approved</p>
                    <p class="text-3xl font-bold text-green-700">{{ $stats['approved'] }}</p>
                </div>
                <div class="bg-red-50 rounded-lg shadow p-5 text-center">
                    <p class="text-sm text-red-600">Rejected</p>
                    <p class="text-3xl font-bold text-red-700">{{ $stats['rejected'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <p class="text-gray-600">Total Employees: <span class="font-semibold">{{ $stats['employees'] }}</span></p>
            </div>

        </div>
    </div>
</x-app-layout>
