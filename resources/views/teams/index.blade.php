<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Team Management
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="bg-red-500 text-white p-3 rounded mb-4">{{ session('error') }}</div>
            @endif

            <div class="mb-6">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search Teams..."
                        value="{{ request('search') }}"
                        class="w-full border px-4 py-2 rounded dark:bg-gray-700 dark:text-white">
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Create New Team</h3>
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

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Your Teams</h3>

                @forelse($teams as $team)
                    <div class="border-b py-4 dark:border-gray-700">

                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $team->name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Members: {{ $team->users->count() }}</p>
                                <p class="text-sm text-blue-500">
                                    Role: {{ $team->users->where('id', auth()->id())->first()->pivot->role ?? 'member' }}
                                </p>

                                @if(auth()->user()->current_team_id == $team->id)
                                    <div class="mt-3 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600 inline-block">
                                        <span class="text-green-600 dark:text-green-400 font-bold block mb-2">✓ Active Team</span>
                                        <div class="flex gap-3">
                                            <a href="{{ route('tasks.index') }}"
                                                class="bg-purple-600 text-white px-4 py-1.5 rounded hover:bg-purple-700 transition text-sm font-semibold shadow">
                                                Task Kanban Board
                                            </a>
                                            <a href="{{ route('chat.index') }}"
                                                class="bg-teal-600 text-white px-4 py-1.5 rounded hover:bg-teal-700 transition text-sm font-semibold shadow">
                                                Team Chat
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('teams.switch', $team->id) }}">
                                @csrf
                                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 font-semibold transition">
                                    Switch Team
                                </button>
                            </form>
                        </div>

                        <div class="mt-4">
                            <h5 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Members:</h5>
                            @foreach($team->users as $user)
                                <div class="flex justify-between items-center border p-2 rounded mb-2 dark:border-gray-600">
                                    <div>
                                        <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->name }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $user->email }}</span>
                                        @if($user->id == $team->owner_id)
                                            <span class="text-xs bg-yellow-200 text-yellow-800 px-1.5 py-0.5 rounded-full ml-2">Owner</span>
                                        @endif
                                    </div>
                                    @if($team->owner_id == auth()->id() && $user->id != auth()->id())
                                        <form method="POST" action="{{ route('teams.remove', [$team->id, $user->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-500 hover:text-red-700 font-semibold text-sm transition">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($team->owner_id == auth()->id())
                            <div class="mt-4" x-data="userSearch({{ $team->id }})">
                                <div class="flex gap-3">
                                    <div class="relative flex-1">
                                        <input
                                            type="text"
                                            x-model="query"
                                            @input.debounce.300ms="search()"
                                            @focus="if(query.length >= 2) showDropdown = true"
                                            @click.away="showDropdown = false"
                                            placeholder="Search user by name or email to invite..."
                                            class="border rounded px-3 py-2 w-full dark:bg-gray-700 dark:text-white"
                                            autocomplete="off"
                                        >

                                        <div x-show="showDropdown && results.length > 0"
                                            class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto">
                                            <template x-for="user in results" :key="user.id">
                                                <div
                                                    @click="selectUser(user)"
                                                    class="flex items-center gap-3 px-4 py-3 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer border-b dark:border-gray-700 last:border-0">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold shrink-0"
                                                        x-text="user.name.charAt(0).toUpperCase()">
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200" x-text="user.name"></p>
                                                        <p class="text-xs text-gray-400" x-text="user.email"></p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div x-show="showDropdown && query.length >= 2 && results.length === 0"
                                            class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg mt-1 px-4 py-3">
                                            <p class="text-sm text-gray-400">No users found.</p>
                                        </div>
                                    </div>

                                    <form action="{{ route('teams.invite', $team->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="email" x-model="selectedEmail">
                                        <button type="submit"
                                            :disabled="!selectedEmail"
                                            :class="selectedEmail ? 'bg-green-600 hover:bg-green-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed'"
                                            class="text-white px-4 py-2 rounded transition font-semibold">
                                            Invite
                                        </button>
                                    </form>
                                </div>

                                <p x-show="selectedEmail" class="text-xs text-green-500 mt-1">
                                    Selected: <span x-text="selectedEmail" class="font-semibold"></span>
                                </p>
                            </div>
                        @endif

                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">You don't have any teams yet.</p>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        function userSearch(teamId) {
            return {
                query: '',
                results: [],
                showDropdown: false,
                selectedEmail: '',

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.showDropdown = false;
                        return;
                    }

                    const res = await fetch(`/users/search?q=${encodeURIComponent(this.query)}&team_id=${teamId}`);
                    this.results = await res.json();
                    this.showDropdown = true;
                },

                selectUser(user) {
                    this.query = user.name + ' (' + user.email + ')';
                    this.selectedEmail = user.email;
                    this.showDropdown = false;
                }
            }
        }
    </script>

</x-app-layout>