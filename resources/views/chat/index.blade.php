<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $receiverId ? 'Private Chat' : $team->name . ' - Group Chat' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm flex h-[600px] overflow-hidden">

                <div class="w-1/4 border-r border-gray-200 dark:border-gray-700 flex flex-col bg-gray-50 dark:bg-gray-900">

                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <a href="{{ route('chat.index') }}"
                            class="flex items-center gap-3 p-2 rounded-lg transition
                                {{ !$receiverId ? 'bg-indigo-100 dark:bg-indigo-900' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                #
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Group Chat</p>
                                <p class="text-xs text-gray-500">{{ $team->name }}</p>
                            </div>
                        </a>
                    </div>

                    <div class="p-4 overflow-y-auto flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-wider">Members</p>

                        @foreach($members as $member)
                            <div class="mb-1">
                                <a href="{{ route('chat.index', ['receiver_id' => $member->id]) }}"
                                    class="flex items-center gap-3 p-2 rounded-lg transition
                                        {{ $member->id == $receiverId ? 'bg-indigo-100 dark:bg-indigo-900' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">

                                    <div class="w-9 h-9 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                                            {{ $member->name }}
                                        </p>
                                        @if($member->id == $team->owner_id)
                                            <span class="text-[10px] bg-yellow-200 text-yellow-800 px-1.5 py-0.5 rounded-full">Owner</span>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex-1 flex flex-col">

                    <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        @if($receiverId)
                            {{-- allMembers વાપરો જેથી receiver ની details મળે --}}
                            @php $receiver = $allMembers->firstWhere('id', $receiverId); @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($receiver->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                                        {{ $receiver->name ?? 'Unknown' }}
                                    </p>
                                    <p class="text-xs text-gray-400">Private Message</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                                    #
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                                        {{ $team->name }}
                                    </p>
                                    {{-- allMembers વાપરો સાચો count માટે --}}
                                    <p class="text-xs text-gray-400">{{ $allMembers->count() }} members</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 p-6 overflow-y-auto bg-gray-50 dark:bg-gray-900" id="chat-box">

                        @forelse($messages as $message)
                            <div class="flex {{ $message->user_id == Auth::id() ? 'justify-end' : 'justify-start' }} mb-4">
                                <div class="max-w-[70%]">

                                    @if($message->user_id != Auth::id())
                                        <p class="text-[11px] text-gray-500 mb-1 ml-1">{{ $message->user->name }}</p>
                                    @endif

                                    <div class="{{ $message->user_id == Auth::id()
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600' }}
                                        px-4 py-2.5 rounded-2xl shadow-sm">
                                        <p class="text-sm leading-relaxed">{{ $message->content }}</p>
                                    </div>

                                    <span class="text-[10px] text-gray-400 block {{ $message->user_id == Auth::id() ? 'text-right' : 'text-left' }} mt-1 mx-1">
                                        {{ $message->created_at->format('H:i') }}
                                    </span>

                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-full">
                                <p class="text-gray-400 text-sm">
                                    {{ $receiverId ? 'Start a private conversation...' : 'No messages yet. Say hello!' }}
                                </p>
                            </div>
                        @endforelse

                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <form action="{{ route('chat.store') }}" method="POST" class="flex gap-2 items-center">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $receiverId }}">
                            <input
                                type="text"
                                name="content"
                                id="message-input"
                                placeholder="{{ $receiverId ? 'Send a private message...' : 'Message ' . $team->name . '...' }}"
                                class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full px-4 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autofocus
                                autocomplete="off"
                            >
                            <button type="submit"
                                class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-700 transition shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                    <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');

        chatBox.scrollTop = chatBox.scrollHeight;

        document.getElementById('message-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.closest('form').submit();
            }
        });

        let lastContent = chatBox.innerHTML;

        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newChatBox = doc.getElementById('chat-box');

                    if (newChatBox && newChatBox.innerHTML !== lastContent) {
                        lastContent = newChatBox.innerHTML;
                        chatBox.innerHTML = lastContent;
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                })
                .catch(() => {});
        }, 5000);
    </script>

</x-app-layout>