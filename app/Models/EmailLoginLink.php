<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailLoginLink extends Model
{
    /**
     * Velden die via mass assignment mogen worden opgeslagen.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    /**
     * Type casting voor databasevelden.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Controleer of deze magic link al gebruikt is.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Controleer of deze magic link verlopen is.
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return true;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Controleer of deze magic link nog geldig is.
     */
    public function isActive(): bool
    {
        return ! $this->isUsed()
            && ! $this->isExpired();
    }

    /**
     * Markeer deze magic link als gebruikt.
     */
    public function markAsUsed(): void
    {
        $this->forceFill([
            'used_at' => now(),
        ])->save();
    }

    /**
     * Filter op e-mailadres.
     */
    public function scopeForEmail(
        Builder $query,
        string $email
    ): Builder {
        return $query->where(
            'email',
            strtolower(trim($email))
        );
    }

    /**
     * Alleen ongebruikte links.
     */
    public function scopeUnused(
        Builder $query
    ): Builder {
        return $query->whereNull('used_at');
    }

    /**
     * Alleen niet-verlopen links.
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

    /**
     * Alleen actieve links.
     */
    public function scopeActive(
        Builder $query
    ): Builder {
        return $query
            ->unused()
            ->notExpired();
    }

    /**
     * Haal de laatste actieve magic link
     * voor een e-mailadres op.
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

    /**
     * Verwijder alle ongebruikte magic links
     * voor een e-mailadres.
     */
    public static function deleteUnusedForEmail(
        string $email
    ): int {
        return self::query()
            ->forEmail($email)
            ->unused()
            ->delete();
    }

    /**
     * Zoek een actieve magic link op basis
     * van de hash van de ontvangen token.
     */
    public static function findActiveByTokenHash(
        string $tokenHash
    ): ?self {
        return self::query()
            ->where(
                'token_hash',
                $tokenHash
            )
            ->active()
            ->first();
    }
}