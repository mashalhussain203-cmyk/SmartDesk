<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageVersion extends Model
{
    use HasFactory;

    /**
     * Velden die via mass assignment mogen worden opgeslagen.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_id',
        'user_id',
        'file_name',
        'path',
        'format',
        'width',
        'height',
        'quality',
        'file_size',
        'operation',
    ];

    /**
     * Database casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image_id' => 'integer',
            'user_id' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'quality' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Bijbehorend afbeeldingsproject.
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(
            Image::class
        );
    }

    /**
     * Eigenaar van deze versie.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Genormaliseerd uitvoerformaat.
     */
    public function getNormalizedFormatAttribute(): string
    {
        $format = strtolower(
            trim(
                (string) $this->format
            )
        );

        return $format === 'jpeg'
            ? 'jpg'
            : $format;
    }

    /**
     * Leesbaar formaatlabel.
     */
    public function getFormatLabelAttribute(): string
    {
        return match (
            $this->normalized_format
        ) {
            'jpg' => 'JPG',
            'png' => 'PNG',
            'webp' => 'WEBP',

            default => strtoupper(
                $this->normalized_format
                ?: 'IMAGE'
            ),
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
     * Leesbaar bewerkingslabel.
     */
    public function getOperationLabelAttribute(): string
    {
        $operation = strtolower(
            trim(
                (string) $this->operation
            )
        );

        return match ($operation) {
            'resize' => 'Resize',
            'crop' => 'Crop',
            'rotate' => 'Rotatie',
            'flip' => 'Spiegelen',
            'compress' => 'Compressie',
            'convert' => 'Conversie',

            default => $operation !== ''
                ? ucfirst($operation)
                : 'Bewerking',
        };
    }

    /**
     * Leesbare kwaliteitswaarde.
     */
    public function getQualityLabelAttribute(): string
    {
        $quality = $this->quality;

        if ($quality === null) {
            return 'Standaard';
        }

        return max(
            1,
            min(
                100,
                (int) $quality
            )
        ) . '%';
    }

    /**
     * Veilige naam voor weergave.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = trim(
            (string) $this->file_name
        );

        return $name !== ''
            ? $name
            : 'Versie #' . $this->getKey();
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
