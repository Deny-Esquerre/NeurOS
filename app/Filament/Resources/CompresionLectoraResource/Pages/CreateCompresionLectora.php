<?php

namespace App\Filament\Resources\CompresionLectoraResource\Pages;

use App\Filament\Resources\CompresionLectoraResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\Task; // Added
use App\Events\ReadingTaskGenerated; // Added
use App\Events\ReadingTaskFailed; // Added

class CreateCompresionLectora extends CreateRecord
{
    protected static string $resource = CompresionLectoraResource::class;

    public $isGenerating = false; // Keep this as it might be used by the form actions

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category'] = 'compresion_lectora';

        return $data;
    }

    protected function getListeners(): array
    {
        return [
            ReadingTaskGenerated::class => 'onTaskGenerated',
            ReadingTaskFailed::class => 'onTaskFailed',
            'loadGeneratedTask' => 'loadGeneratedTask',
            'refreshGeneratedTasks' => 'refreshFormContents', // Added
        ];
    }

    public function refreshFormContents()
    {
        // This will re-evaluate the default values of form components, including the Repeater
        $this->form->fill();
    }

    public function onTaskGenerated($taskId, $userId)
    {
        if (Auth::id() !== $userId) {
            return;
        }

        $task = Task::find($taskId);

        if (!$task) {
            Notification::make()
                ->title('Error')
                ->body('No se encontró la tarea generada.')
                ->danger()
                ->send();
            return;
        }

        // Fill the form with the generated task data
        $this->form->fill([
            'name' => $task->name,
            'description' => $task->description,
            'questions' => array_map(function ($q) {
                // Assuming questions are stored as JSON in DB and need re-mapping for Repeater
                return [
                    'question' => $q['question'],
                    'alternatives' => array_map(fn($alt) => ['alternative' => $alt], $q['alternatives']),
                    'correct_answer' => $q['correct_answer'],
                ];
            }, $task->questions),
        ]);

        $this->dispatch('refreshGeneratedTasks'); // Added
        Notification::make()
            ->title('Tarea Generada Correctamente')
            ->body('El texto y las preguntas se han generado y rellenado en el formulario.')
            ->success()
            ->send();
    }

    public function onTaskFailed($userId, $errorMessage)
    {
        if (Auth::id() !== $userId) {
            return;
        }

        Notification::make()
            ->title('Error al Generar Tarea')
            ->body('No se pudo generar la tarea. Detalles: ' . $errorMessage)
            ->danger()
            ->send();
    }
} // Added missing closing brace for the class
