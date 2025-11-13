<x-filament-panels::page>
    {{-- Botón Volver --}}
    <div class="fi-ta-header-actions flex items-center gap-x-3 px-4 py-4 sm:px-6">
        @if ($this->selectedTask)
            <x-filament::button
                icon="heroicon-o-arrow-left"
                wire:click="deselectTask"
                outlined
                disabled
            >
                Volver a la lista
            </x-filament::button>
        @elseif ($this->category)
            {{-- No mostrar nada --}}
        @else
            <x-filament::button
                icon="heroicon-o-arrow-left"
                tag="a"
                href="{{ url()->previous() }}"
                outlined
            >
                Volver
            </x-filament::button>
        @endif
    </div>

    <div class="fi-page-content">
        <div class="fi-page-content-main">
            @if ($this->selectedTask)
                {{-- Mostrar el examen de la tarea seleccionada --}}
                @include('filament.padre.pages.partials.task-exam', ['task' => $this->selectedTask])
            @else
                {{-- Mostrar la lista de categorías y tareas --}}
                @if ($this->groupedTasks->isEmpty())
                    <x-filament::section>
                        <x-slot name="heading">
                            No hay tareas disponibles
                        </x-slot>
                        <p>No se encontraron tareas asignadas a tus hijos en este momento.</p>
                    </x-filament::section>
                @else
                    @foreach ($this->groupedTasks as $category => $tasks)
                        <x-filament::section id="{{ Str::slug($category) }}" class="mb-6">
                            <x-slot name="heading">
                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                            </x-slot>

                            @php
                                $pendingTasks = $tasks->filter(fn($task) => !$task->completions_exists);
                            @endphp

                            @if ($pendingTasks->isEmpty())
                                <p class="text-gray-500 dark:text-gray-400">No hay tareas pendientes en esta categoría.</p>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach ($pendingTasks as $task)
                                        @if ($category === 'juegos_de_recreacion')
                                            <x-filament::section
                                                class="transition"
                                            >
                                                <x-slot name="heading">
                                                    {{ str_replace('*', '', $task->name) }}
                                                </x-slot>
                                                @if ($task->preview_image_url)
                                                    <img src="{{ Storage::url($task->preview_image_url) }}" alt="Previsualización del juego" class="mb-4 rounded-lg object-cover w-full h-32">
                                                @endif
                                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{!! $task->description !!}</p>
                                                <div class="mt-6 flex justify-center">
                                                    <x-filament::button
                                                        x-data="{ open: false }"
                                                        x-on:click="open = true"
                                                        size="sm"
                                                        icon="heroicon-o-play"
                                                    >
                                                        Jugar
                                                    </x-filament::button>

                                                    {{-- Modal para el juego --}}
                                                    <div
                                                        x-show="open"
                                                        x-transition:enter="ease-out duration-300"
                                                        x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100"
                                                        x-transition:leave="ease-in duration-200"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/50 p-4"
                                                        style="display: none;"
                                                    >
                                                        <div
                                                            x-show="open"
                                                            x-transition:enter="ease-out duration-300"
                                                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                            x-transition:leave="ease-in duration-200"
                                                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                            class="relative w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
                                                            x-on:click.away="open = false"
                                                        >
                                                            <button
                                                                type="button"
                                                                x-on:click="open = false"
                                                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                                                            >
                                                                <span class="sr-only">Cerrar</span>
                                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>

                                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ str_replace('*', '', $task->name) }}</h3>

                                                            <div class="aspect-w-16 aspect-h-9 w-full">
                                                                <iframe src="{{ $task->game_link }}" frameborder="0" allowfullscreen class="h-full w-full rounded-lg"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </x-filament::section>
                                        @else
                                            <x-filament::section
                                                class="transition"
                                            >
                                                <x-slot name="heading">
                                                    {{ str_replace('*', '', $task->name) }}
                                                </x-slot>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{!! $task->description !!}</p>
                                                <div class="mt-6 flex justify-center">
                                                    <x-filament::button
                                                        wire:click="selectTask({{ $task->id }})"
                                                        size="sm"
                                                    >
                                                        Realizar Examen
                                                    </x-filament::button>
                                                </div>
                                            </x-filament::section>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </x-filament::section>
                    @endforeach
                @endif
            @endif
        </div>
    </div>
</x-filament-panels::page>
