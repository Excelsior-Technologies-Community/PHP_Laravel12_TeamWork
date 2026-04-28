<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Team Management
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- SEARCH --}}
            <div class="mb-6">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search Teams..."
                        class="w-full border px-4 py-2 rounded dark:bg-gray-700 dark:text-white">
                </form>
            </div>

            <!-- CREATE TEAM -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Create New Team
                </h3>

                <form action="{{ route('teams.store') }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text" name="name" placeholder="Team Name"
                        class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white" required>

                    <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Create
                    </button>
                </form>
            </div>

            <!-- TEAM LIST -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">

                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Your Teams
                </h3>

                @forelse($teams as $team)
                    <div class="border-b py-4 dark:border-gray-700">

                        <!-- TEAM HEADER -->
                        <div class="flex justify-between items-center">

                            <div>
                                <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">
                                    {{ $team->name }}
                                </h4>

                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Members: {{ $team->users->count() }}
                                </p>

                                <!-- ROLE -->
                                <p class="text-sm text-blue-500">
                                    Role:
                                    {{ $team->users->where('id', auth()->id())->first()->pivot->role ?? 'member' }}
                                </p>

                                <!-- ACTIVE TEAM -->
                                @if(auth()->user()->current_team_id == $team->id)
                                    <span class="text-green-600 font-semibold">Active Team</span>
                                @endif
                            </div>

                            <!-- SWITCH BUTTON -->
                            <form method="POST" action="{{ route('teams.switch', $team->id) }}">
                                @csrf
                                <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                    Switch
                                </button>
                            </form>

                        </div>

                        <!-- MEMBERS LIST -->
                        <div class="mt-3">
                            <h5 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Members:
                            </h5>

                            @foreach($team->users as $user)
                                <div class="flex justify-between items-center border p-2 rounded mb-2">

                                    <span class="text-gray-800 dark:text-gray-200">
                                        {{ $user->name }}
                                    </span>

                                    <!-- REMOVE BUTTON (ONLY OWNER) -->
                                    @if($team->owner_id == auth()->id() && $user->id != auth()->id())
                                        <form method="POST" action="{{ route('teams.remove', [$team->id, $user->id]) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button class="text-red-500 hover:underline">
                                                Remove
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            @endforeach
                        </div>

                        <!-- INVITE FORM -->
                        <form action="{{ route('teams.invite', $team->id) }}" method="POST" class="mt-4 flex gap-3">
                            @csrf
                            <input type="email" name="email" placeholder="Invite Email"
                                class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white" required>

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