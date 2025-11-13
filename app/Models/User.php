<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens; // Add HasApiTokens here

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole($panel->getId());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parent_id',
        'psicologo_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación Padre-Hijo (un hijo tiene un padre)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Relación Padre-Hijo (un padre tiene muchos hijos)
    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // Alias en español para la relación children
    public function hijos(): HasMany
    {
        return $this->children();
    }

    // Tareas asignadas a un niño
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_completions', 'user_id', 'task_id')
                    ->withPivot('completed_at', 'answers')
                    ->withTimestamps();
    }

    // Relación Padre-Psicólogo (un padre/usuario tiene un psicólogo)
    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psicologo_id');
    }

    // Relación Psicólogo-Padre (un psicólogo tiene muchos padres/usuarios)
    public function padres(): HasMany
    {
        return $this->hasMany(User::class, 'psicologo_id');
    }
}
