<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Leave Types</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 text-red-700 rounded">{{ session('error') }}</div>
            @endif

            <!-- Add New Leave Type -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-gray-700 font-semibold mb-4">Add New Leave Type</h3>
                <form method="POST" action="{{ route('admin.leave-types.store') }}" class="flex gap-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <x-input-label for="name" value="Leave Type Name" />
                        <x-text-input id="name" name="name" type="text"
                            class="mt-1 block w-full" :value="old('name')"
                            placeholder="e.g. Compassionate Leave" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="w-32">
                        <x-input-label for="max_days" value="Max Days" />
                        <x-text-input id="max_days" name="max_days" type="number"
                            class="mt-1 block w-full" :value="old('max_days', 14)"
                            min="1" max="365" />
                        <x-input-error :messages="$errors->get('max_days')" class="mt-1" />
                    </div>
                    <x-primary-button>Add</x-primary-button>
                </form>
            </div>

            <!-- Existing Leave Types -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leaveTypes as $type)
                            <tr x-data="{ editing: false }">
                                <td class="px-6 py-4">
                                    <span x-show="!editing">{{ $type->name }}</span>
                                    <form x-show="editing" method="POST"
                                        action="{{ route('admin.leave-types.update', $type) }}"
                                        class="flex gap-2 items-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $type->name }}"
                                            class="border-gray-300 rounded text-sm w-48" />
                                        <input type="number" name="max_days" value="{{ $type->max_days }}"
                                            class="border-gray-300 rounded text-sm w-20" min="1" max="365" />
                                        <button type="submit"
                                            class="text-green-600 hover:underline text-xs">Save</button>
                                        <button type="button" @click="editing = false"
                                            class="text-gray-400 hover:underline text-xs">Cancel</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4" x-show="!editing">{{ $type->max_days }} days</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-3" x-show="!editing">
                                        <button @click="editing = true"
                                            class="text-indigo-600 hover:underline text-xs">Edit</button>
                                        <form method="POST"
                                            action="{{ route('admin.leave-types.destroy', $type) }}"
                                            onsubmit="return confirm('Delete this leave type?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:underline text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-400">No leave types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
