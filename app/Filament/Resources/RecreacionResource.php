<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecreacionResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User; // Added for completion check

class RecreacionResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube'; // Changed icon
    protected static ?string $navigationLabel = 'Juegos de Recreación'; // Changed label
    protected static ?string $modelLabel = 'Juego de Recreación'; // Changed label
    protected static ?string $pluralModelLabel = 'Juegos de Recreación'; // Changed label
    protected static ?string $navigationGroup = 'Gestion de tareas';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', 'recreacion');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Juego')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('game_link')
                    ->label('Enlace del Juego (URL)')
                    ->required()
                    ->url() // Validate as URL
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('description')
                    ->label('Instrucciones del Juego')
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
                    ->label('Nombre del Juego')
                    ->searchable(),
                Tables\Columns\TextColumn::make('game_link')
                    ->label('Enlace del Juego')
                    ->url(fn (Task $record): ?string => $record->game_link) // Pass the URL
                    ->openUrlInNewTab()
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
            'index' => Pages\ListRecreacions::route('/'),
            'create' => Pages\CreateRecreacion::route('/create'),
            'edit' => Pages\EditRecreacion::route('/{record}/edit'),
        ];
    }
}