<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageVersion extends Model
{
    use HasFactory;

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

    protected $casts = [
        'image_id' => 'integer',
        'user_id' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'quality' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Originele afbeelding/project.
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Eigenaar van deze versie.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Genormaliseerd formaat.
     */
    public function getNormalizedFormatAttribute(): string
    {
        $format = strtolower(
            trim(
                (string) $this->format
            )
        );

        if ($format === 'jpeg') {
            return 'jpg';
        }

        return $format;
    }

    /**
     * Leesbaar formaatlabel.
     */
    public function getFormatLabelAttribute(): string
    {
        return match ($this->normalized_format) {
            'jpg' => 'JPG',
            'png' => 'PNG',
            'webp' => 'WEBP',
            default => strtoupper(
                $this->normalized_format ?: 'IMAGE'
            ),
        };
    }

    /**
     * Leesbare bestandsgrootte.
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
     * Leesbare afmetingen.
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
     * Leesbare operation-naam.
     */
    public function getOperationLabelAttribute(): string
    {
        return match (
            strtolower(
                trim(
                    (string) $this->operation
                )
            )
        ) {
            'resize' => 'Resize',
            'crop' => 'Crop',
            'rotate' => 'Rotatie',
            'flip' => 'Spiegelen',
            'compress' => 'Compressie',
            'convert' => 'Conversie',

            default => $this->operation
                ? ucfirst((string) $this->operation)
                : 'Bewerking',
        };
    }

    /**
     * Leesbare kwaliteitswaarde.
     */
    public function getQualityLabelAttribute(): string
    {
        if ($this->quality === null) {
            return 'Standaard';
        }

        return $this->quality . '%';
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
            : 'Versie #' . $this->id;
    }
}

