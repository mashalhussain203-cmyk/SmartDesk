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

    /**
     * Velden die via mass assignment mogen worden opgeslagen.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'original_name',
        'original_path',
        'mime_type',
        'width',
        'height',
        'file_size',
    ];

    /**
     * Database casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Eigenaar van dit afbeeldingsproject.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Alle opgeslagen bewerkte versies.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(
            ImageVersion::class
        );
    }

    /**
     * Laatst opgeslagen versie.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(
            ImageVersion::class
        )->latestOfMany();
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
     * Leesbare bestandsgrootte.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        return $this->formatBytes(
            (int) $this->file_size
        );
    }

    /**
     * Leesbare afbeeldingsafmetingen.
     */
    public function getDimensionsLabelAttribute(): string
    {
        $width = (int) $this->width;
        $height = (int) $this->height;

        if (
            $width < 1 ||
            $height < 1
        ) {
            return 'Onbekende afmetingen';
        }

        return sprintf(
            '%d × %d',
            $width,
            $height
        );
    }

    /**
     * Veilige naam voor weergave in de interface.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = trim(
            (string) $this->original_name
        );

        return $name !== ''
            ? $name
            : 'Afbeelding #' . $this->getKey();
    }

    /**
     * Controleer of dit project bewerkte versies heeft.
     *
     * Werkt efficiënt met:
     * - withCount('versions')
     * - eager loaded versions
     * - losse modelinstanties
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

        if (
            $this->relationLoaded(
                'versions'
            )
        ) {
            return $this->versions->isNotEmpty();
        }

        return $this->versions()
            ->exists();
    }

    /**
     * Aantal versies, zonder onnodige extra query wanneer de informatie
     * al geladen is.
     */
    public function getVersionCountAttribute(): int
    {
        if (
            array_key_exists(
                'versions_count',
                $this->attributes
            )
        ) {
            return (int) $this->attributes['versions_count'];
        }

        if (
            $this->relationLoaded(
                'versions'
            )
        ) {
            return $this->versions->count();
        }

        return $this->versions()
            ->count();
    }

    /**
     * Bestandsgrootte formatteren.
     */
    private function formatBytes(
        int $bytes
    ): string {
        $bytes = max(
            0,
            $bytes
        );

        if ($bytes >= 1024 ** 3) {
            return number_format(
                $bytes / (1024 ** 3),
                2
            ) . ' GB';
        }

        if ($bytes >= 1024 ** 2) {
            return number_format(
                $bytes / (1024 ** 2),
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
}
