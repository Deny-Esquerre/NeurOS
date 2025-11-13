<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'questions',
        'game_link', // New field
        'preview_image_url',
        'is_published',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_published' => 'boolean', // Cast new field
    ];

    // Relationship to track individual child completions
    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    // Relationship to get all children associated with this task
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_completions', 'task_id', 'user_id')
                    ->withPivot('completed_at')
                    ->withTimestamps();
    }

    // Accessor to calculate completion percentage
    public function getCompletionPercentageAttribute(): float
    {
        // Assuming 'hijo' role users are the target audience for tasks
        $totalChildren = User::role('hijo')->count();

        if ($totalChildren === 0) {
            return 0.0;
        }

        $completedChildren = $this->completions()->whereNotNull('completed_at')->count();

        return ($completedChildren / $totalChildren) * 100;
    }
}
