<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'project_id', 'title', 'mode', 'pinned',
        'archived', 'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'pinned' => 'boolean',
            'archived' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AiProject::class, 'project_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AiDocument::class, 'conversation_id');
    }

    public function shareLinks(): HasMany
    {
        return $this->hasMany(AiShareLink::class, 'conversation_id');
    }
}
