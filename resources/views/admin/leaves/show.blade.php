<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leave Request Detail</h2>
            <a href="{{ route('admin.leaves.index') }}" class="text-sm text-indigo-600 hover:underline">← Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Employee</dt>
                        <dd class="font-medium">{{ $leave->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Leave Type</dt>
                        <dd class="font-medium">{{ $leave->leaveType->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Start Date</dt>
                        <dd class="font-medium">{{ $leave->start_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">End Date</dt>
                        <dd class="font-medium">{{ $leave->end_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Days</dt>
                        <dd class="font-medium">{{ $leave->total_days }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                @if($leave->status === 'approved') bg-green-100 text-green-700
                                @elseif($leave->status === 'rejected') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500">Reason</dt>
                        <dd class="font-medium">{{ $leave->reason }}</dd>
                    </div>
                    @if($leave->admin_note)
                        <div class="col-span-2">
                            <dt class="text-gray-500">Admin Note</dt>
                            <dd class="font-medium">{{ $leave->admin_note }}</dd>
                        </div>
                    @endif
                    @if($leave->reviewer)
                        <div class="col-span-2">
                            <dt class="text-gray-500">Reviewed By</dt>
                            <dd class="font-medium">{{ $leave->reviewer->name }} on {{ $leave->reviewed_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if($leave->status === 'pending')
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-gray-700 font-semibold mb-4">Review Request</h3>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-1">Admin Note (optional)</label>
                        <textarea id="admin_note_text" rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring focus:ring-indigo-200"
                            placeholder="Add a note for the employee..."></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}">
                            @csrf
                            <input type="hidden" name="admin_note" id="approve_note">
                            <button type="submit"
                                onclick="document.getElementById('approve_note').value = document.getElementById('admin_note_text').value"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                                Approve
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}">
                            @csrf
                            <input type="hidden" name="admin_note" id="reject_note">
                            <button type="submit"
                                onclick="document.getElementById('reject_note').value = document.getElementById('admin_note_text').value"
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
