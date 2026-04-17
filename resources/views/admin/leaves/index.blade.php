<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Leave Requests</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">{{ session('error') }}</div>
            @endif

            <!-- Filter Tabs -->
            <div class="flex space-x-2 mb-4">
                @foreach(['pending', 'approved', 'rejected', 'all'] as $tab)
                    <a href="{{ route('admin.leaves.index', ['status' => $tab]) }}"
                        class="px-4 py-2 rounded text-sm font-medium
                            {{ $status === $tab ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100' }}">
                        {{ ucfirst($tab) }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leaves as $leave)
                            <tr>
                                <td class="px-6 py-4">{{ $leave->user->name }}</td>
                                <td class="px-6 py-4">{{ $leave->leaveType->name }}</td>
                                <td class="px-6 py-4">
                                    {{ $leave->start_date->format('d M Y') }} —
                                    {{ $leave->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4">{{ $leave->total_days }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($leave->status === 'approved') bg-green-100 text-green-700
                                        @elseif($leave->status === 'rejected') bg-red-100 text-red-700
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.leaves.show', $leave) }}"
                                        class="text-indigo-600 hover:underline text-xs">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($leaves->hasPages())
                    <div class="px-6 py-4">{{ $leaves->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
