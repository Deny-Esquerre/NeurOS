<x-filament::section class="mb-4">
    <x-slot name="heading">
        {{ str_replace('*', '', $task->name) }}
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Columna Izquierda: Título y Descripción --}}
        <div>
            <p class="text-gray-600 dark:text-gray-400">{!! $task->description !!}</p>
            @if ($task->game_link)
                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        href="{{ $task->game_link }}"
                        target="_blank"
                        icon="heroicon-o-play"
                    >
                        Jugar
                    </x-filament::button>
                </div>
            @endif
        </div>

        {{-- Columna Derecha: Preguntas y Opciones --}}
        <div>
            @if (!empty($task->questions))
                <form wire:submit.prevent="submitTask({{ $task->id }})">
                    @foreach ($task->questions as $questionIndex => $questionData)
                        <div class="mb-4">
                            <p class="font-medium mb-2">{{ $questionData['question'] }}</p>
                            @foreach ($questionData['alternatives'] as $optionIndex => $option)
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        name="answers[{{ $task->id }}][{{ $questionIndex }}]"
                                        wire:model="answers.{{ $task->id }}.{{ $questionIndex }}"
                                        value="{{ $option['alternative'] }}"
                                        class="text-primary-600 focus:ring-primary-600"
                                    >
                                    <span>{{ $option['alternative'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                    <x-filament::button type="submit" class="mt-4">
                        Enviar Respuestas
                    </x-filament::button>
                </form>
            @else
                <p class="text-gray-500 dark:text-gray-400">No hay preguntas para esta tarea.</p>
            @endif
        </div>
    </div>
</x-filament::section>
