<?php

namespace App\Filament\Resources\CompresionLectoraResource\Pages;

use App\Filament\Resources\CompresionLectoraResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateCompresionLectora extends CreateRecord
{
    protected static string $resource = CompresionLectoraResource::class;

    public $isGenerating = false;

    protected function getListeners()
    {
        return [
            "echo-private:user.".Auth::id().",ReadingTaskGenerated" => 'onTaskGenerated',
            "echo-private:user.".Auth::id().",ReadingTaskFailed" => 'onTaskFailed',
        ];
    }

    public function onTaskGenerated($event)
    {
        $this->isGenerating = false;

        $taskData = $event['taskData'];
        $this->form->fill([
            'name' => "Tarea de Comprensión Lectora: " . $taskData['topic'],
            'description' => $taskData['text'],
            'questions' => array_map(function ($q) {
                return [
                    'question' => $q['question'],
                    'alternatives' => array_map(fn($alt) => ['alternative' => $alt], $q['alternatives']),
                    'correct_answer' => $q['correct_answer'],
                ];
            }, $taskData['questions']),
        ]);

        Notification::make()
            ->title('Tarea Generada')
            ->body('El texto y las preguntas se han generado y rellenado en el formulario.')
            ->success()
            ->send();
    }

    public function onTaskFailed($event)
    {
        $this->isGenerating = false;

        Notification::make()
            ->title('Error al generar la tarea')
            ->body('Hubo un problema al generar la tarea: ' . $event['message'])
            ->danger()
            ->send();
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category'] = 'compresion_lectora';

        return $data;
    }
}
