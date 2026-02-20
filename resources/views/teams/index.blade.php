<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Team Management
        </h2>
    </x-slot>

    <br>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Create Team -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Create New Team
                </h3>

                <form action="{{ route('teams.store') }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text"
                        name="name"
                        placeholder="Team Name"
                        class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white"
                        required>

                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Create
                    </button>
                </form>
            </div>

            <!-- Team List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Your Teams
                </h3>

                @forelse($teams as $team)
                <div class="border-b py-4 dark:border-gray-700">

                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">
                                {{ $team->name }}
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Members: {{ $team->users->count() }}
                            </p>
                        </div>
                    </div>

                    <!-- Invite Form -->
                    <form action="{{ route('teams.invite', $team->id) }}"
                        method="POST"
                        class="mt-3 flex gap-3">
                        @csrf
                        <input type="email"
                            name="email"
                            placeholder="Invite Email"
                            class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white"
                            required>

                        <button type="submit"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                            Invite
                        </button>
                    </form>

                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400">
                    You don’t have any teams yet.
                </p>
                @endforelse

            </div>

        </div>
    </div>

</x-app-layout>