<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_name',
        'original_path',
        'mime_type',
        'width',
        'height',
        'file_size',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Eigenaar van de afbeelding.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alle opgeslagen bewerkte versies.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ImageVersion::class);
    }

    /**
     * Laatst opgeslagen versie.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ImageVersion::class)
            ->latestOfMany();
    }

    /**
     * Leesbaar formaatlabel.
     */
    public function getFormatLabelAttribute(): string
    {
        return match (
            strtolower(
                trim(
                    (string) $this->mime_type
                )
            )
        ) {
            'image/jpeg',
            'image/jpg' => 'JPG',

            'image/png' => 'PNG',

            'image/webp' => 'WEBP',

            default => 'IMAGE',
        };
    }

    /**
     * Bestandsgrootte leesbaar maken.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = max(
            0,
            (int) $this->file_size
        );

        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format(
                $bytes / 1024 / 1024 / 1024,
                2
            ) . ' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format(
                $bytes / 1024 / 1024,
                2
            ) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format(
                $bytes / 1024,
                1
            ) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Leesbare dimensies.
     */
    public function getDimensionsLabelAttribute(): string
    {
        if (
            ! $this->width ||
            ! $this->height
        ) {
            return 'Onbekende afmetingen';
        }

        return sprintf(
            '%d × %d',
            $this->width,
            $this->height
        );
    }

    /**
     * Veilige weergavenaam.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = trim(
            (string) $this->original_name
        );

        return $name !== ''
            ? $name
            : 'Afbeelding #' . $this->id;
    }

    /**
     * Controleren of er al bewerkte versies zijn.
     */
    public function getHasVersionsAttribute(): bool
    {
        if (
            array_key_exists(
                'versions_count',
                $this->attributes
            )
        ) {
            return (int) $this->attributes['versions_count'] > 0;
        }

        if ($this->relationLoaded('versions')) {
            return $this->versions->isNotEmpty();
        }

        return $this->versions()
            ->exists();
    }
}

