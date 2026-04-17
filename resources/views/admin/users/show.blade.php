<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}'s Leave History</h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:underline">← Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-5 mb-6 text-sm text-gray-600">
                <p>Email: <span class="font-medium text-gray-800">{{ $user->email }}</span></p>
                <p>Joined: <span class="font-medium text-gray-800">{{ $user->created_at->format('d M Y') }}</span></p>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leaves as $leave)
                            <tr>
                                <td class="px-6 py-4">{{ $leave->leaveType->name }}</td>
                                <td class="px-6 py-4">{{ $leave->start_date->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $leave->end_date->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $leave->total_days }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($leave->status === 'approved') bg-green-100 text-green-700
                                        @elseif($leave->status === 'rejected') bg-red-100 text-red-700
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">No leave requests.</td>
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
