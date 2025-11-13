<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Tareas';

    protected static ?string $modelLabel = 'Tarea';

    protected static ?string $pluralModelLabel = 'Tareas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Forms\Components\RichEditor::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('game_link')
                    ->label('Enlace del Juego')
                    ->url()
                    ->nullable(),
                Forms\Components\FileUpload::make('preview_image_url')
                    ->label('Imagen de Previsualización')
                    ->image()
                    ->directory('task-previews') // Almacenar en storage/app/public/task-previews
                    ->nullable(),
                Forms\Components\Select::make('category')
                    ->label('Categoría')
                    ->options([
                        'compresion_lectora' => 'Comprensión Lectora',
                        'matematicas' => 'Matemáticas',
                        'juegos_de_recreacion' => 'Juegos de Recreación',
                    ])
                    ->required(),
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
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicada')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'compresion_lectora' => 'Comprensión Lectora',
                        'matematicas' => 'Matemáticas',
                        'juegos_de_recreacion' => 'Juegos de Recreación',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicada')
                    ->boolean(),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
