<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Select;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Gestion de usuarios';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('roles')
                    ->multiple()
                    ->relationship(titleAttribute: 'name')
                    ->options(
                        \Spatie\Permission\Models\Role::all()->mapWithKeys(fn ($role) => [$role->id => ucfirst($role->name)])
                    )
                    ->preload()
                    ->live(),
                Select::make('parent_id')
                    ->relationship('parent', 'name', fn (Builder $query) => $query->role('padre'))
                    ->label('Padre')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->visible(function (\Filament\Forms\Get $get) {
                        $roleIds = $get('roles');
                        if (empty($roleIds)) return false;
                        if (!is_array($roleIds)) $roleIds = [$roleIds];
                        $hijoRoleId = Role::where('name', 'Hijo')->value('id'); // Usar 'Hijo' capitalizado
                        return in_array($hijoRoleId, $roleIds);
                    }),
                Select::make('psicologo_id')
                    ->relationship('psicologo', 'name', fn (Builder $query) => $query->role('psicologo'))
                    ->label('Psicólogo')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->visible(function (\Filament\Forms\Get $get) {
                        $roleIds = $get('roles');
                        if (empty($roleIds)) return false;
                        if (!is_array($roleIds)) $roleIds = [$roleIds];
                        $hijoRoleId = Role::where('name', 'Hijo')->value('id'); // Usar 'Hijo' capitalizado
                        return in_array($hijoRoleId, $roleIds);
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Padre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'psicologo' => 'info',
                        'padre' => 'warning',
                        'hijo' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
