<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    /**
     * Velden die via create() / firstOrCreate()
     * ingevuld mogen worden.
     */
    protected $fillable = [
        'user_id',
        'car_id',
    ];

    /**
     * Databasewaarden als integers behandelen.
     */
    protected $casts = [
        'user_id' => 'integer',
        'car_id' => 'integer',
    ];

    /**
     * De gebruiker bij deze favoriet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
