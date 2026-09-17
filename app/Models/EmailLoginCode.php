<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailLoginCode extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'email',
        'code_hash',
        'expires_at',
        'attempts',
        'used_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Controle: code gebruikt
    |--------------------------------------------------------------------------
    */

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | Controle: code verlopen
    |--------------------------------------------------------------------------
    */

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return true;
        }

        return $this->expires_at->isPast();
    }


    /*
    |--------------------------------------------------------------------------
    | Controle: code actief
    |--------------------------------------------------------------------------
    |
    | Een code is actief wanneer:
    |
    | - hij nog niet gebruikt is
    | - hij een geldige vervaldatum heeft
    | - de vervaldatum nog niet verstreken is
    |
    */

    public function isActive(): bool
    {
        return ! $this->isUsed()
            && ! $this->isExpired();
    }


    /*
    |--------------------------------------------------------------------------
    | Verkeerde poging registreren
    |--------------------------------------------------------------------------
    */

    public function registerFailedAttempt(): void
    {
        $this->increment('attempts');

        $this->refresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Code als gebruikt markeren
    |--------------------------------------------------------------------------
    */

    public function markAsUsed(): void
    {
        $this->forceFill([
            'used_at' => now(),
        ])->save();
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: voor e-mailadres
    |--------------------------------------------------------------------------
    */

    public function scopeForEmail(
        Builder $query,
        string $email
    ): Builder {
        return $query->where(
            'email',
            strtolower(
                trim($email)
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: ongebruikte codes
    |--------------------------------------------------------------------------
    */

    public function scopeUnused(
        Builder $query
    ): Builder {
        return $query->whereNull(
            'used_at'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: niet verlopen codes
    |--------------------------------------------------------------------------
    */

    public function scopeNotExpired(
        Builder $query
    ): Builder {
        return $query->where(
            'expires_at',
            '>',
            now()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scope: actieve codes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query
            ->unused()
            ->notExpired();
    }


    /*
    |--------------------------------------------------------------------------
    | Laatste actieve code ophalen
    |--------------------------------------------------------------------------
    */

    public static function latestActiveForEmail(
        string $email
    ): ?self {
        return self::query()
            ->forEmail($email)
            ->active()
            ->latest('id')
            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Alle ongebruikte codes verwijderen
    |--------------------------------------------------------------------------
    */

    public static function deleteUnusedForEmail(
        string $email
    ): int {
        return self::query()
            ->forEmail($email)
            ->unused()
            ->delete();
    }
}