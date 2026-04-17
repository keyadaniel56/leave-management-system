<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Apply for Leave</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('leave.store') }}">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="leave_type_id" value="Leave Type" />
                        <select id="leave_type_id" name="leave_type_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">
                            <option value="">-- Select Leave Type --</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (max {{ $type->max_days }} days)
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('leave_type_id')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-input-label for="start_date" value="Start Date" />
                            <x-text-input id="start_date" name="start_date" type="date"
                                class="mt-1 block w-full" :value="old('start_date')" />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="end_date" value="End Date" />
                            <x-text-input id="end_date" name="end_date" type="date"
                                class="mt-1 block w-full" :value="old('end_date')" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                        </div>
                    </div>

                    <div class="mb-6">
                        <x-input-label for="reason" value="Reason" />
                        <textarea id="reason" name="reason" rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('leave.index') }}"
                            class="text-sm text-gray-600 hover:underline self-center">Back to History</a>
                        <x-primary-button>Submit Request</x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
