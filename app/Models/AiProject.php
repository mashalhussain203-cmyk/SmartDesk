<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProject extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'name', 'description', 'instructions', 'color',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(AiConversation::class, 'project_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AiDocument::class, 'project_id');
    }
}
