<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        Welcome back, {{ auth()->user()->name }}!
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ now()->format('l, d M Y') }}
                    </p>
                </div>
            </div>

            {{-- Pending Invitations --}}
            @php
                $pendingInvites = \Mpociot\Teamwork\TeamInvite::where('email', auth()->user()->email)->get();
            @endphp

            @if($pendingInvites->count() > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-yellow-800 dark:text-yellow-300 mb-4">
                        Team Invitations ({{ $pendingInvites->count() }})
                    </h3>

                    @foreach($pendingInvites as $invite)
                        <div class="flex items-center justify-between bg-white dark:bg-gray-800 border border-yellow-200 dark:border-gray-700 rounded-lg p-4 mb-3 last:mb-0">
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $invite->team->name ?? 'Unknown Team' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Invited {{ $invite->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ url('/invite/accept/' . $invite->accept_token) }}"
                                    class="bg-green-600 text-white px-4 py-1.5 rounded hover:bg-green-700 transition text-sm font-semibold">
                                    Accept
                                </a>
                                <a href="{{ url('/invite/deny/' . $invite->deny_token) }}"
                                    class="bg-red-500 text-white px-4 py-1.5 rounded hover:bg-red-600 transition text-sm font-semibold">
                                    Decline
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Active Team Info --}}
            @php
                $activeTeam = auth()->user()->current_team_id
                    ? \App\Models\Team::with('users')->find(auth()->user()->current_team_id)
                    : null;
            @endphp

            @if($activeTeam)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                Active Team: {{ $activeTeam->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $activeTeam->users->count() }} members
                            </p>
                        </div>
                        <span class="text-green-600 dark:text-green-400 font-bold text-sm">✓ Active</span>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('tasks.index') }}"
                            class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition text-sm font-semibold shadow">
                            Task Kanban Board
                        </a>
                        <a href="{{ route('chat.index') }}"
                            class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 transition text-sm font-semibold shadow">
                            Team Chat
                        </a>
                        <a href="{{ route('teams.index') }}"
                            class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition text-sm font-semibold shadow">
                            Manage Teams
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">You are not in any team yet.</p>
                    <a href="{{ route('teams.index') }}"
                        class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition font-semibold">
                        Create or Join a Team
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>