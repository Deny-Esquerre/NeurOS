<?php

namespace App\Filament\Padre\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskCompletion;
use Filament\Notifications\Notification;

class Tareas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.padre.pages.tareas';

    protected static bool $shouldRegisterNavigation = false; // No registrar en la navegación principal

    protected static ?string $slug = 'tareas/{category?}'; // Permitir parámetro de categoría en la URL

    protected static ?string $title = 'Tareas'; // Título genérico

    public array $answers = [];
    public ?string $category = null; // Propiedad para almacenar la categoría de la URL
    public ?Task $selectedTask = null; // Propiedad para la tarea seleccionada

    public function mount(): void
    {
        // Si hay una categoría y solo una tarea en ella, seleccionarla automáticamente
        if ($this->category) {
            $tasksInCategory = $this->getGroupedTasksProperty()->get($this->category);
            if ($tasksInCategory && $tasksInCategory->count() === 1) {
                $this->selectedTask = $tasksInCategory->first();
            }
        }
    }

    public function getGroupedTasksProperty(): \Illuminate\Support\Collection
    {
        $child = Auth::user()->children->first(); // Asumo un solo hijo por padre

        $allTasks = Task::where('is_published', true);

        if ($child) { // Solo añadir withExists si hay un hijo
            $allTasks->withExists(['completions' => function ($query) use ($child) {
                $query->where('user_id', $child->id);
            }]);
        }

        // Filtrar por categoría si está presente en la URL
        if ($this->category) {
            $searchCategory = strtolower($this->category); // Convertir a minúsculas
            $allTasks = $allTasks->whereRaw('LOWER(category) = ?', [$searchCategory]); // Comparación case-insensitive
        }

        return $allTasks->get()->groupBy('category');
    }

    public function selectTask(int $taskId): void
    {
        $this->selectedTask = Task::find($taskId);
    }

    public function deselectTask(): void
    {
        $this->selectedTask = null;
    }

    public function getTitle(): string
    {
        return $this->category ? ucfirst(str_replace('_', ' ', $this->category)) : 'Tareas';
    }

    public function submitTask(int $taskId): void
    {
        $task = Task::find($taskId);

        if (!$task) {
            Notification::make()
                ->title('Tarea no encontrada')
                ->danger()
                ->send();
            return;
        }

        $rules = [];
        foreach ($task->questions as $questionIndex => $questionData) {
            // Asegurarse de que 'alternatives' existe antes de iterar
            if (isset($questionData['alternatives']) && is_array($questionData['alternatives'])) {
                $rules["answers.{$taskId}.{$questionIndex}"] = ['required', 'in:' . implode(',', $questionData['alternatives'])];
            } else {
                // Si no hay alternativas, la pregunta no puede ser respondida, o hay un error en los datos.
                // Por ahora, no añadimos la regla si no hay alternativas válidas.
            }
        }
        $this->validate($rules);

        $correctAnswersCount = 0;
        $totalQuestions = count($task->questions);
        $userAnswers = $this->answers[$taskId] ?? [];

        foreach ($task->questions as $questionIndex => $questionData) {
            $correctAnswer = $questionData['correct_answer'] ?? null;
            $submittedAnswer = $userAnswers[$questionIndex] ?? null;

            if ($submittedAnswer && $submittedAnswer === $correctAnswer) {
                $correctAnswersCount++;
            }
        }

        $isCompleted = ($correctAnswersCount === $totalQuestions);

        $child = Auth::user()->children->first();

        if (!$child) {
            Notification::make()
                ->title('No se encontró un hijo asociado para completar la tarea.')
                ->danger()
                ->send();
            return;
        }

        TaskCompletion::updateOrCreate(
            [
                'user_id' => $child->id,
                'task_id' => $taskId,
            ],
            [
                'completed_at' => $isCompleted ? now() : null,
                'answers' => json_encode($userAnswers),
                'score' => ($correctAnswersCount / $totalQuestions) * 100,
            ]
        );

        if ($isCompleted) {
            Notification::make()
                ->title('¡Tarea completada con éxito!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Tarea enviada. Algunas respuestas son incorrectas.')
                ->warning()
                ->send();
        }

        // Después de enviar la tarea, deseleccionar la tarea y actualizar la lista
        $this->selectedTask = null;
        $this->answers = []; // Limpiar respuestas
        // No es necesario llamar a mount() directamente, Livewire se encargará de re-renderizar
    }
}
