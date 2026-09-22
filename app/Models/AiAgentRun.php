<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'conversation_id', 'mode', 'status', 'used_web',
        'used_code', 'tools', 'sources', 'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'used_web' => 'boolean',
            'used_code' => 'boolean',
            'tools' => 'array',
            'sources' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
