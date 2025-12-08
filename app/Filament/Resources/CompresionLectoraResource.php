<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompresionLectoraResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Services\OllamaService;

class CompresionLectoraResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Comprensión Lectora';
    protected static ?string $modelLabel = 'Tarea de Comprensión Lectora';
    protected static ?string $pluralModelLabel = 'Tareas de Comprensión Lectora';
    protected static ?string $navigationGroup = 'Gestion de tareas';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'compresion_lectora');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Generación de Tarea con IA')
                    ->description('Utiliza la IA para generar temas y tareas de comprensión lectora.')
                    ->columns(2)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->schema([
                        Forms\Components\Select::make('age')
                            ->label('Edad del Niño')
                            ->options([
                                7 => '7 años',
                                8 => '8 años',
                                9 => '9 años',
                                10 => '10 años',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('topics', null); // Clear topics when age changes
                                $set('selected_topic', null); // Clear selected topic
                                $set('custom_topic', null); // Clear custom topic
                            }),

                        Forms\Components\TextInput::make('custom_topic')
                            ->label('Opcional: Especificar un Tema')
                            ->placeholder('Ej. "Los planetas del sistema solar"')
                            ->live()
                            ->visible(fn (Forms\Get $get) => filled($get('age'))),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_topics')
                                ->label('Generar Temas')
                                ->icon('heroicon-o-sparkles')
                                ->color('primary')
                                ->action(function (Forms\Get $get, Forms\Set $set, OllamaService $ollamaService) {
                                    $age = $get('age');
                                    if (!$age) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('Por favor, selecciona una edad primero.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $topics = $ollamaService->generateTopics($age);

                                    if ($topics) {
                                        $set('topics', array_combine($topics, $topics)); // Use topic as key and value
                                        \Filament\Notifications\Notification::make()
                                            ->title('Temas Generados')
                                            ->body('Se han generado 3 temas.')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('No se pudieron generar los temas. Inténtalo de nuevo.')
                                            ->danger()
                                            ->send();
                                    }
                                })
                                ->visible(fn (Forms\Get $get) => filled($get('age')) && blank($get('custom_topic'))),
                        ])->columnSpanFull(),

                        Forms\Components\Select::make('selected_topic')
                            ->label('Selecciona un Tema')
                            ->options(fn (Forms\Get $get): array => $get('topics') ?? [])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Forms\Get $get) => filled($get('topics')) && blank($get('custom_topic'))),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_task')
                                ->label('Generar Tarea')
                                ->icon('heroicon-o-document-text')
                                ->color('success')
                                ->action(function (Forms\Get $get, Forms\Set $set, OllamaService $ollamaService) {
                                    set_time_limit(300); // Increase execution time for AI task generation
                                    $age = $get('age');
                                    $topic = $get('custom_topic') ?: $get('selected_topic');

                                    if (!$age || !$topic) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('Por favor, selecciona o especifica un tema y una edad.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $taskData = $ollamaService->generateTask($age, $topic);

                                    if ($taskData) {
                                        $set('name', "Tarea de Comprensión Lectora: " . $topic);
                                        $set('description', $taskData['text']);
                                        // Map questions to the repeater structure
                                        $formattedQuestions = [];
                                        foreach ($taskData['questions'] as $q) {
                                            $formattedQuestions[] = [
                                                'question' => $q['question'],
                                                'alternatives' => array_map(fn($alt) => ['alternative' => $alt], $q['alternatives']),
                                                'correct_answer' => $q['correct_answer'],
                                            ];
                                        }
                                        $set('questions', $formattedQuestions);
                                        \Filament\Notifications\Notification::make()
                                            ->title('Tarea Generada')
                                            ->body('El texto y las preguntas se han generado y rellenado en el formulario.')
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('No se pudo generar la tarea. Inténtalo de nuevo.')
                                            ->danger()
                                            ->send();
                                    }
                                })
                                ->visible(fn (Forms\Get $get) => filled($get('selected_topic')) || filled($get('custom_topic'))),
                        ])->columnSpanFull(),
                    ]),

                Forms\Components\TextInput::make('name')
                    ->label('Título de la Tarea')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('description') // Using RichEditor for better text formatting
                    ->label('Texto de Lectura')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('questions')
                    ->label('Preguntas de Comprensión')
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->label('Pregunta')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('alternatives')
                            ->label('Alternativas')
                            ->schema([
                                Forms\Components\TextInput::make('alternative')
                                    ->label('Alternativa')
                                    ->required(),
                            ])
                            ->defaultItems(4)
                            ->minItems(2)
                            ->maxItems(4)
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['alternative'] ?? null),
                        Forms\Components\Select::make('correct_answer')
                            ->label('Respuesta Correcta')
                            ->options(function (Forms\Get $get) {
                                $alternatives = $get('alternatives');
                                if (empty($alternatives)) {
                                    return [];
                                }
                                // Filter out any empty alternatives before plucking
                                return collect($alternatives)
                                    ->filter(fn($alt) => filled($alt['alternative'] ?? null))
                                    ->pluck('alternative', 'alternative')
                                    ->toArray();
                            })
                            ->required()
                            ->live() // Make it live to update options when alternatives change
                            ->columnSpanFull(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                    ->defaultItems(3)
                    ->minItems(1)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_published')
                    ->label('Publicada')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicada')
                    ->boolean(),
                Tables\Columns\TextColumn::make('completion_percentage') // Accessor from Task model
                    ->label('Completada (%)')
                    ->getStateUsing(fn (Task $record): string => number_format($record->completion_percentage, 0) . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompresionLectoras::route('/'),
            'create' => Pages\CreateCompresionLectora::route('/create'),
            'edit' => Pages\EditCompresionLectora::route('/{record}/edit'),
        ];
    }
}