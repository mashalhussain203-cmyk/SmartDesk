<?php

namespace App\Services;

use App\Services\ImageProcessingService;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PassportPhotoService
{
    public function __construct(
        private readonly ImageProcessingService $images
    ) {
    }

    /**
     * Geef alle geconfigureerde editor-presets terug.
     *
     * Let op: deze presets zijn hulpmiddelen voor de editor en vormen
     * geen automatische garantie dat een foto door een instantie wordt
     * geaccepteerd. Documenteisen kunnen veranderen.
     *
     * @return array<string, array<string, mixed>>
     */
    public function presets(): array
    {
        $presets = config(
            'mashal-image.passport_presets',
            []
        );

        return is_array($presets)
            ? $presets
            : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function preset(
        string $presetKey
    ): array {
        $presetKey = trim(
            $presetKey
        );

        $presets = $this->presets();

        if (
            $presetKey === '' ||
            ! isset($presets[$presetKey]) ||
            ! is_array($presets[$presetKey])
        ) {
            throw ValidationException::withMessages([
                'passport_preset' => 'Kies een geldige pasfoto- of ID-fotopreset.',
            ]);
        }

        $preset = $presets[$presetKey];

        $width = (int) (
            $preset['width'] ?? 0
        );

        $height = (int) (
            $preset['height'] ?? 0
        );

        $this->images->assertDimensions(
            $width,
            $height
        );

        $preset['width'] = $width;
        $preset['height'] = $height;
        $preset['ratio'] =
            $width / $height;

        return $preset;
    }

    public function ratio(
        string $presetKey
    ): float {
        $preset =
            $this->preset(
                $presetKey
            );

        return (float) $preset['ratio'];
    }

    /**
     * Maak één pasfoto/ID-foto op basis van een visueel gekozen cropgebied.
     *
     * @param array<string, mixed> $data
     */
    public function create(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        string $presetKey,
        array $data = []
    ): mixed {
        $preset =
            $this->preset(
                $presetKey
            );

        $targetWidth =
            (int) $preset['width'];

        $targetHeight =
            (int) $preset['height'];

        $crop =
            $this->cropRectangle(
                $sourceWidth,
                $sourceHeight,
                $targetWidth,
                $targetHeight,
                $data
            );

        $cropped =
            $this->images->crop(
                $source,
                $sourceWidth,
                $sourceHeight,
                $crop['x'],
                $crop['y'],
                $crop['width'],
                $crop['height']
            );

        try {
            return $this->images->resizeExact(
                $cropped,
                imagesx($cropped),
                imagesy($cropped),
                $targetWidth,
                $targetHeight
            );
        } finally {
            $this->images->destroy(
                $cropped
            );
        }
    }

    /**
     * Maak een automatisch gecentreerd cropgebied met de verhouding
     * van de gekozen preset.
     *
     * Handig als startpunt voordat de gebruiker het kader zelf sleept.
     *
     * @return array{x:int,y:int,width:int,height:int}
     */
    public function defaultCropRectangle(
        int $sourceWidth,
        int $sourceHeight,
        string $presetKey,
        float $coverage = 0.84
    ): array {
        $preset =
            $this->preset(
                $presetKey
            );

        $coverage = max(
            0.2,
            min(
                1.0,
                $coverage
            )
        );

        $ratio =
            (float) $preset['ratio'];

        $maxWidth =
            $sourceWidth * $coverage;

        $maxHeight =
            $sourceHeight * $coverage;

        $width =
            $maxWidth;

        $height =
            $width / $ratio;

        if ($height > $maxHeight) {
            $height =
                $maxHeight;

            $width =
                $height * $ratio;
        }

        $width = max(
            1,
            min(
                $sourceWidth,
                (int) round($width)
            )
        );

        $height = max(
            1,
            min(
                $sourceHeight,
                (int) round($height)
            )
        );

        return [
            'x' => max(
                0,
                (int) floor(
                    ($sourceWidth - $width) / 2
                )
            ),

            'y' => max(
                0,
                (int) floor(
                    ($sourceHeight - $height) / 2
                )
            ),

            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Corrigeer een bestaand cropgebied naar de verhouding van de export.
     * Het midden van het door de gebruiker gekozen gebied blijft zoveel
     * mogelijk behouden.
     *
     * @param array<string, mixed> $data
     *
     * @return array{x:int,y:int,width:int,height:int}
     */
    public function cropRectangle(
        int $sourceWidth,
        int $sourceHeight,
        int $targetWidth,
        int $targetHeight,
        array $data = []
    ): array {
        $this->images->assertDimensions(
            $sourceWidth,
            $sourceHeight
        );

        $this->images->assertDimensions(
            $targetWidth,
            $targetHeight
        );

        $x = max(
            0,
            (int) (
                $data['crop_x'] ?? 0
            )
        );

        $y = max(
            0,
            (int) (
                $data['crop_y'] ?? 0
            )
        );

        $x = min(
            $x,
            max(
                0,
                $sourceWidth - 1
            )
        );

        $y = min(
            $y,
            max(
                0,
                $sourceHeight - 1
            )
        );

        $availableWidth =
            $sourceWidth - $x;

        $availableHeight =
            $sourceHeight - $y;

        $width = max(
            1,
            min(
                $availableWidth,
                (int) (
                    $data['crop_width'] ??
                    $availableWidth
                )
            )
        );

        $height = max(
            1,
            min(
                $availableHeight,
                (int) (
                    $data['crop_height'] ??
                    $availableHeight
                )
            )
        );

        $targetRatio =
            $targetWidth /
            $targetHeight;

        $cropRatio =
            $width /
            $height;

        if ($cropRatio > $targetRatio) {
            $newWidth = max(
                1,
                (int) round(
                    $height *
                    $targetRatio
                )
            );

            $x += max(
                0,
                (int) floor(
                    ($width - $newWidth) / 2
                )
            );

            $width =
                $newWidth;
        } elseif ($cropRatio < $targetRatio) {
            $newHeight = max(
                1,
                (int) round(
                    $width /
                    $targetRatio
                )
            );

            $y += max(
                0,
                (int) floor(
                    ($height - $newHeight) / 2
                )
            );

            $height =
                $newHeight;
        }

        $x = max(
            0,
            min(
                $x,
                $sourceWidth - $width
            )
        );

        $y = max(
            0,
            min(
                $y,
                $sourceHeight - $height
            )
        );

        $this->images->assertCropRectangle(
            $sourceWidth,
            $sourceHeight,
            $x,
            $y,
            $width,
            $height
        );

        return [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Bouw een printvel met meerdere identieke pasfoto's.
     *
     * Dit maakt alleen de technische lay-out. De caller bepaalt zelf
     * de daadwerkelijke printmaat/DPI en kan deze instellingen via
     * config of de UI aanbieden.
     *
     * @param array{
     *     sheet_width?:int,
     *     sheet_height?:int,
     *     margin?:int,
     *     gap?:int,
     *     background_color?:string
     * } $options
     */
    public function createPrintSheet(
        mixed $passportPhoto,
        int $copies = 6,
        array $options = []
    ): mixed {
        $copies = max(
            1,
            min(
                100,
                $copies
            )
        );

        $photoWidth =
            imagesx($passportPhoto);

        $photoHeight =
            imagesy($passportPhoto);

        $sheetWidth = max(
            $photoWidth,
            (int) (
                $options['sheet_width'] ??
                1200
            )
        );

        $sheetHeight = max(
            $photoHeight,
            (int) (
                $options['sheet_height'] ??
                1800
            )
        );

        $margin = max(
            0,
            (int) (
                $options['margin'] ??
                48
            )
        );

        $gap = max(
            0,
            (int) (
                $options['gap'] ??
                24
            )
        );

        $backgroundColor =
            (string) (
                $options['background_color'] ??
                '#ffffff'
            );

        $this->images->assertDimensions(
            $sheetWidth,
            $sheetHeight
        );

        $availableWidth =
            $sheetWidth -
            ($margin * 2);

        $availableHeight =
            $sheetHeight -
            ($margin * 2);

        if (
            $availableWidth < $photoWidth ||
            $availableHeight < $photoHeight
        ) {
            throw ValidationException::withMessages([
                'print_sheet' => 'Het gekozen printvel is te klein voor deze pasfoto.',
            ]);
        }

        $columns = max(
            1,
            (int) floor(
                (
                    $availableWidth +
                    $gap
                ) /
                (
                    $photoWidth +
                    $gap
                )
            )
        );

        $rows = max(
            1,
            (int) floor(
                (
                    $availableHeight +
                    $gap
                ) /
                (
                    $photoHeight +
                    $gap
                )
            )
        );

        $capacity =
            $columns *
            $rows;

        if ($capacity < 1) {
            throw ValidationException::withMessages([
                'print_sheet' => 'Er past geen pasfoto op het gekozen printvel.',
            ]);
        }

        $copiesToPlace = min(
            $copies,
            $capacity
        );

        $sheet =
            $this->images->createSolidCanvas(
                $sheetWidth,
                $sheetHeight,
                $backgroundColor
            );

        try {
            imagealphablending(
                $sheet,
                true
            );

            $usedColumns = min(
                $columns,
                $copiesToPlace
            );

            $usedRows = (int) ceil(
                $copiesToPlace /
                $columns
            );

            $contentWidth =
                ($usedColumns * $photoWidth) +
                (
                    max(
                        0,
                        $usedColumns - 1
                    ) * $gap
                );

            $contentHeight =
                ($usedRows * $photoHeight) +
                (
                    max(
                        0,
                        $usedRows - 1
                    ) * $gap
                );

            $startX = max(
                $margin,
                (int) floor(
                    ($sheetWidth - $contentWidth) / 2
                )
            );

            $startY = max(
                $margin,
                (int) floor(
                    ($sheetHeight - $contentHeight) / 2
                )
            );

            for (
                $index = 0;
                $index < $copiesToPlace;
                $index++
            ) {
                $column =
                    $index % $columns;

                $row =
                    intdiv(
                        $index,
                        $columns
                    );

                $x =
                    $startX +
                    (
                        $column *
                        (
                            $photoWidth +
                            $gap
                        )
                    );

                $y =
                    $startY +
                    (
                        $row *
                        (
                            $photoHeight +
                            $gap
                        )
                    );

                $copied =
                    imagecopy(
                        $sheet,
                        $passportPhoto,
                        $x,
                        $y,
                        0,
                        0,
                        $photoWidth,
                        $photoHeight
                    );

                if (! $copied) {
                    throw new RuntimeException(
                        'Een pasfoto kon niet op het printvel worden geplaatst.'
                    );
                }
            }

            return $sheet;
        } catch (Throwable $exception) {
            $this->images->destroy(
                $sheet
            );

            throw $exception;
        }
    }

    /**
     * Handige metadata voor Blade/JS zonder dat Blade de configstructuur
     * zelf hoeft te interpreteren.
     *
     * @return array{
     *     key:string,
     *     label:string,
     *     short_label:string,
     *     width:int,
     *     height:int,
     *     ratio:float,
     *     description:string
     * }
     */
    public function metadata(
        string $presetKey
    ): array {
        $preset =
            $this->preset(
                $presetKey
            );

        return [
            'key' => $presetKey,
            'label' => (string) (
                $preset['label'] ??
                $presetKey
            ),
            'short_label' => (string) (
                $preset['short_label'] ??
                $preset['label'] ??
                $presetKey
            ),
            'width' => (int) $preset['width'],
            'height' => (int) $preset['height'],
            'ratio' => (float) $preset['ratio'],
            'description' => (string) (
                $preset['description'] ??
                ''
            ),
        ];
    }
}
