<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Task Kanban Board
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6">
                <form action="{{ route('tasks.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <input type="text" name="title" placeholder="Task Title..." class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm" required>
                    <select name="status" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">Add Task</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $status => $title)
                    <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg min-h-[400px]">
                        <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-300 uppercase">{{ $title }}</h3>
                        
                        @foreach($tasks->where('status', $status) as $task)
                            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow mb-3 border border-gray-200 dark:border-gray-700">
                                <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $task->title }}</h4>
                                
                                <div class="mt-4 flex gap-2 justify-between items-center">
                                    <form action="{{ route('tasks.updateStatus', $task) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-xs p-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded">
                                            <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>To Do</option>
                                            <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Done</option>
                                        </select>
                                    </form>
                                    
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>