<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ImageProcessingService
{
    public const MAX_DIMENSION = 12000;

    public const MAX_PIXELS = 80000000;

    public const DEFAULT_QUALITY = 88;

    /**
     * @var array<int, string>
     */
    public const OUTPUT_FORMATS = [
        'jpg',
        'png',
        'webp',
    ];

    public function ensureAvailable(): void
    {
        $requiredFunctions = [
            'imagecreatefromstring',
            'imagecreatetruecolor',
            'imagecopy',
            'imagecopyresampled',
            'imagesx',
            'imagesy',
            'imagejpeg',
            'imagepng',
            'imagedestroy',
        ];

        foreach ($requiredFunctions as $function) {
            if (! function_exists($function)) {
                throw ValidationException::withMessages([
                    'image' => 'De GD-extensie is niet beschikbaar of onvolledig op deze server.',
                ]);
            }
        }
    }

    public function load(string $absolutePath): mixed
    {
        $this->ensureAvailable();

        if (
            $absolutePath === '' ||
            ! is_file($absolutePath)
        ) {
            throw ValidationException::withMessages([
                'image' => 'Het afbeeldingsbestand bestaat niet meer.',
            ]);
        }

        $contents = @file_get_contents($absolutePath);

        if (
            $contents === false ||
            $contents === ''
        ) {
            throw ValidationException::withMessages([
                'image' => 'Het afbeeldingsbestand kon niet worden gelezen.',
            ]);
        }

        try {
            $image = @imagecreatefromstring($contents);
        } finally {
            unset($contents);
        }

        if ($image === false) {
            throw ValidationException::withMessages([
                'image' => 'Dit afbeeldingsbestand kan niet door GD worden geopend.',
            ]);
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $this->assertDimensions(
            imagesx($image),
            imagesy($image)
        );

        return $image;
    }

    /**
     * @return array{width:int,height:int}
     */
    public function dimensions(mixed $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $this->assertDimensions(
            $width,
            $height
        );

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    public function copy(
        mixed $source,
        ?int $sourceWidth = null,
        ?int $sourceHeight = null
    ): mixed {
        $sourceWidth ??= imagesx($source);
        $sourceHeight ??= imagesy($source);

        $this->assertDimensions(
            $sourceWidth,
            $sourceHeight
        );

        $canvas = $this->createTransparentCanvas(
            $sourceWidth,
            $sourceHeight
        );

        $copied = imagecopy(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $sourceWidth,
            $sourceHeight
        );

        if (! $copied) {
            $this->destroy($canvas);

            throw new RuntimeException(
                'De afbeelding kon niet worden gekopieerd.'
            );
        }

        return $canvas;
    }

    public function resize(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        ?int $requestedWidth,
        ?int $requestedHeight,
        bool $keepAspect = true
    ): mixed {
        if (
            $requestedWidth === null &&
            $requestedHeight === null
        ) {
            throw ValidationException::withMessages([
                'width' => 'Vul een nieuwe breedte en/of hoogte in.',
            ]);
        }

        if ($keepAspect) {
            [
                $requestedWidth,
                $requestedHeight,
            ] = $this->resolveAspectDimensions(
                $sourceWidth,
                $sourceHeight,
                $requestedWidth,
                $requestedHeight
            );
        } else {
            $requestedWidth ??= $sourceWidth;
            $requestedHeight ??= $sourceHeight;
        }

        $this->assertDimensions(
            $requestedWidth,
            $requestedHeight
        );

        $canvas = $this->createTransparentCanvas(
            $requestedWidth,
            $requestedHeight
        );

        $resampled = imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $requestedWidth,
            $requestedHeight,
            $sourceWidth,
            $sourceHeight
        );

        if (! $resampled) {
            $this->destroy($canvas);

            throw new RuntimeException(
                'Resize kon niet worden uitgevoerd.'
            );
        }

        return $canvas;
    }

    public function resizeExact(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        int $targetWidth,
        int $targetHeight
    ): mixed {
        return $this->resize(
            $source,
            $sourceWidth,
            $sourceHeight,
            $targetWidth,
            $targetHeight,
            false
        );
    }

    public function crop(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        int $x,
        int $y,
        int $width,
        int $height
    ): mixed {
        $this->assertCropRectangle(
            $sourceWidth,
            $sourceHeight,
            $x,
            $y,
            $width,
            $height
        );

        $canvas = $this->createTransparentCanvas(
            $width,
            $height
        );

        $copied = imagecopy(
            $canvas,
            $source,
            0,
            0,
            $x,
            $y,
            $width,
            $height
        );

        if (! $copied) {
            $this->destroy($canvas);

            throw new RuntimeException(
                'Crop kon niet worden uitgevoerd.'
            );
        }

        return $canvas;
    }

    public function rotate(
        mixed $source,
        int $angle
    ): mixed {
        if (
            ! in_array(
                $angle,
                [-270, -180, -90, 90, 180, 270],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'angle' => 'Kies een geldige rotatiehoek.',
            ]);
        }

        /*
         * GD draait tegen de klok in bij positieve waarden.
         * Voor de editor gebruiken we een intuïtieve klokwijzer-richting.
         */
        $gdAngle = -$angle;

        $transparent = imagecolorallocatealpha(
            $source,
            0,
            0,
            0,
            127
        );

        $rotated = imagerotate(
            $source,
            $gdAngle,
            $transparent
        );

        if ($rotated === false) {
            throw new RuntimeException(
                'Roteren kon niet worden uitgevoerd.'
            );
        }

        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);

        $this->assertDimensions(
            imagesx($rotated),
            imagesy($rotated)
        );

        return $rotated;
    }

    public function flip(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        string $direction
    ): mixed {
        if (
            ! in_array(
                $direction,
                ['horizontal', 'vertical'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'flip_direction' => 'Kies horizontaal of verticaal spiegelen.',
            ]);
        }

        $output = $this->copy(
            $source,
            $sourceWidth,
            $sourceHeight
        );

        if (function_exists('imageflip')) {
            $mode = $direction === 'horizontal'
                ? IMG_FLIP_HORIZONTAL
                : IMG_FLIP_VERTICAL;

            if (! imageflip($output, $mode)) {
                $this->destroy($output);

                throw new RuntimeException(
                    'Spiegelen kon niet worden uitgevoerd.'
                );
            }

            return $output;
        }

        $fallback = $this->createTransparentCanvas(
            $sourceWidth,
            $sourceHeight
        );

        try {
            if ($direction === 'horizontal') {
                for ($x = 0; $x < $sourceWidth; $x++) {
                    imagecopy(
                        $fallback,
                        $source,
                        $sourceWidth - $x - 1,
                        0,
                        $x,
                        0,
                        1,
                        $sourceHeight
                    );
                }
            } else {
                for ($y = 0; $y < $sourceHeight; $y++) {
                    imagecopy(
                        $fallback,
                        $source,
                        0,
                        $sourceHeight - $y - 1,
                        0,
                        $y,
                        $sourceWidth,
                        1
                    );
                }
            }
        } catch (Throwable $exception) {
            $this->destroy($fallback);
            $this->destroy($output);

            throw $exception;
        }

        $this->destroy($output);

        return $fallback;
    }

    /**
     * Ondersteunde opties:
     * - brightness: -100..100
     * - contrast: -100..100
     * - grayscale: bool
     * - sepia: bool
     * - blur: 0..6
     *
     * @param array<string, mixed> $options
     */
    public function enhance(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        array $options
    ): mixed {
        if (! function_exists('imagefilter')) {
            throw ValidationException::withMessages([
                'image' => 'Deze PHP/GD-installatie ondersteunt geen afbeeldingsfilters.',
            ]);
        }

        $output = $this->copy(
            $source,
            $sourceWidth,
            $sourceHeight
        );

        try {
            $brightness = max(
                -100,
                min(
                    100,
                    (int) ($options['brightness'] ?? 0)
                )
            );

            $contrast = max(
                -100,
                min(
                    100,
                    (int) ($options['contrast'] ?? 0)
                )
            );

            $blur = max(
                0,
                min(
                    6,
                    (int) ($options['blur'] ?? 0)
                )
            );

            $grayscale = filter_var(
                $options['grayscale'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            $sepia = filter_var(
                $options['sepia'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            if ($brightness !== 0) {
                $this->filter(
                    $output,
                    IMG_FILTER_BRIGHTNESS,
                    (int) round(
                        ($brightness / 100) * 255
                    )
                );
            }

            if ($contrast !== 0) {
                /*
                 * IMG_FILTER_CONTRAST gebruikt de omgekeerde richting:
                 * negatieve waarden versterken contrast.
                 */
                $this->filter(
                    $output,
                    IMG_FILTER_CONTRAST,
                    -$contrast
                );
            }

            if ($grayscale || $sepia) {
                $this->filter(
                    $output,
                    IMG_FILTER_GRAYSCALE
                );
            }

            if ($sepia) {
                $this->filter(
                    $output,
                    IMG_FILTER_COLORIZE,
                    90,
                    55,
                    30,
                    0
                );
            }

            for ($index = 0; $index < $blur; $index++) {
                $this->filter(
                    $output,
                    IMG_FILTER_GAUSSIAN_BLUR
                );
            }

            return $output;
        } catch (Throwable $exception) {
            $this->destroy($output);

            throw $exception;
        }
    }

    public function createTransparentCanvas(
        int $width,
        int $height
    ): mixed {
        $this->assertDimensions(
            $width,
            $height
        );

        $canvas = imagecreatetruecolor(
            $width,
            $height
        );

        if ($canvas === false) {
            throw new RuntimeException(
                'Er kon geen nieuw afbeeldingscanvas worden aangemaakt.'
            );
        }

        imagealphablending(
            $canvas,
            false
        );

        imagesavealpha(
            $canvas,
            true
        );

        $transparent = imagecolorallocatealpha(
            $canvas,
            0,
            0,
            0,
            127
        );

        imagefilledrectangle(
            $canvas,
            0,
            0,
            max(0, $width - 1),
            max(0, $height - 1),
            $transparent
        );

        return $canvas;
    }

    public function createSolidCanvas(
        int $width,
        int $height,
        string $hexColor = '#ffffff'
    ): mixed {
        $this->assertDimensions(
            $width,
            $height
        );

        [$red, $green, $blue] =
            $this->hexToRgb($hexColor);

        $canvas = imagecreatetruecolor(
            $width,
            $height
        );

        if ($canvas === false) {
            throw new RuntimeException(
                'Er kon geen afbeeldingscanvas worden aangemaakt.'
            );
        }

        $color = imagecolorallocate(
            $canvas,
            $red,
            $green,
            $blue
        );

        imagefilledrectangle(
            $canvas,
            0,
            0,
            max(0, $width - 1),
            max(0, $height - 1),
            $color
        );

        return $canvas;
    }

    public function compositeCentered(
        mixed $background,
        mixed $foreground
    ): mixed {
        $backgroundWidth = imagesx($background);
        $backgroundHeight = imagesy($background);
        $foregroundWidth = imagesx($foreground);
        $foregroundHeight = imagesy($foreground);

        $this->assertDimensions(
            $backgroundWidth,
            $backgroundHeight
        );

        $output = $this->copy(
            $background,
            $backgroundWidth,
            $backgroundHeight
        );

        imagealphablending($output, true);
        imagesavealpha($output, true);

        $x = (int) floor(
            ($backgroundWidth - $foregroundWidth) / 2
        );

        $y = (int) floor(
            ($backgroundHeight - $foregroundHeight) / 2
        );

        if (! imagecopy(
            $output,
            $foreground,
            $x,
            $y,
            0,
            0,
            $foregroundWidth,
            $foregroundHeight
        )) {
            $this->destroy($output);

            throw new RuntimeException(
                'De voorgrond kon niet op de achtergrond worden geplaatst.'
            );
        }

        return $output;
    }

    public function save(
        mixed $image,
        string $absolutePath,
        string $format,
        int $quality = self::DEFAULT_QUALITY
    ): void {
        $format = $this->normalizeFormat(
            $format
        );

        $quality = $this->normalizeQuality(
            $quality
        );

        $this->ensureFormatSupport(
            $format
        );

        $directory = dirname(
            $absolutePath
        );

        if (
            ! is_dir($directory) &&
            ! @mkdir(
                $directory,
                0775,
                true
            ) &&
            ! is_dir($directory)
        ) {
            throw new RuntimeException(
                'De uitvoermap kon niet worden aangemaakt.'
            );
        }

        $encoded = match ($format) {
            'jpg' => $this->encodeJpeg(
                $image,
                $absolutePath,
                $quality
            ),

            'png' => imagepng(
                $image,
                $absolutePath,
                $this->pngCompressionLevel(
                    $quality
                )
            ),

            'webp' => imagewebp(
                $image,
                $absolutePath,
                $quality
            ),

            default => false,
        };

        if (! $encoded) {
            throw new RuntimeException(
                'De afbeelding kon niet worden opgeslagen.'
            );
        }
    }

    public function normalizeFormat(string $format): string
    {
        $format = strtolower(
            trim($format)
        );

        if ($format === 'jpeg') {
            $format = 'jpg';
        }

        if (
            ! in_array(
                $format,
                self::OUTPUT_FORMATS,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'format' => 'Dit afbeeldingsformaat wordt niet ondersteund.',
            ]);
        }

        return $format;
    }

    public function formatFromMime(?string $mime): string
    {
        return match (
            strtolower(
                trim(
                    (string) $mime
                )
            )
        ) {
            'image/jpeg',
            'image/jpg' => 'jpg',

            'image/png' => 'png',

            'image/webp' => 'webp',

            default => throw ValidationException::withMessages([
                'format' => 'Het bronformaat van deze afbeelding wordt niet ondersteund.',
            ]),
        };
    }

    public function mimeForFormat(string $format): string
    {
        return match (
            $this->normalizeFormat(
                $format
            )
        ) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        };
    }

    public function extensionForFormat(string $format): string
    {
        return $this->normalizeFormat(
            $format
        );
    }

    public function normalizeQuality(
        mixed $quality
    ): int {
        if (
            $quality === null ||
            $quality === ''
        ) {
            return self::DEFAULT_QUALITY;
        }

        return max(
            1,
            min(
                100,
                (int) $quality
            )
        );
    }

    public function assertDimensions(
        int $width,
        int $height
    ): void {
        if (
            $width < 1 ||
            $height < 1
        ) {
            throw ValidationException::withMessages([
                'image' => 'De afbeelding heeft ongeldige afmetingen.',
            ]);
        }

        if (
            $width > self::MAX_DIMENSION ||
            $height > self::MAX_DIMENSION
        ) {
            throw ValidationException::withMessages([
                'image' => sprintf(
                    'De maximale afbeeldingsafmeting is %d pixels per zijde.',
                    self::MAX_DIMENSION
                ),
            ]);
        }

        if (
            $width >
            intdiv(
                self::MAX_PIXELS,
                max(1, $height)
            )
        ) {
            throw ValidationException::withMessages([
                'image' => 'De afbeelding bevat te veel pixels om veilig te verwerken.',
            ]);
        }
    }

    public function assertCropRectangle(
        int $sourceWidth,
        int $sourceHeight,
        int $x,
        int $y,
        int $width,
        int $height
    ): void {
        $this->assertDimensions(
            $width,
            $height
        );

        if (
            $x < 0 ||
            $y < 0 ||
            $x >= $sourceWidth ||
            $y >= $sourceHeight ||
            ($x + $width) > $sourceWidth ||
            ($y + $height) > $sourceHeight
        ) {
            throw ValidationException::withMessages([
                'crop_width' => 'Het gekozen cropgebied valt buiten de afbeelding.',
            ]);
        }
    }

    public function destroy(mixed $image): void
    {
        if (
            $image !== null &&
            (
                is_resource($image) ||
                $image instanceof \GdImage
            )
        ) {
            @imagedestroy($image);
        }
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveAspectDimensions(
        int $sourceWidth,
        int $sourceHeight,
        ?int $requestedWidth,
        ?int $requestedHeight
    ): array {
        if (
            $requestedWidth !== null &&
            $requestedHeight === null
        ) {
            $requestedHeight = max(
                1,
                (int) round(
                    $sourceHeight *
                    ($requestedWidth / $sourceWidth)
                )
            );
        } elseif (
            $requestedHeight !== null &&
            $requestedWidth === null
        ) {
            $requestedWidth = max(
                1,
                (int) round(
                    $sourceWidth *
                    ($requestedHeight / $sourceHeight)
                )
            );
        } else {
            $requestedWidth ??= $sourceWidth;
            $requestedHeight ??= $sourceHeight;

            $scale = min(
                $requestedWidth / $sourceWidth,
                $requestedHeight / $sourceHeight
            );

            $requestedWidth = max(
                1,
                (int) round(
                    $sourceWidth * $scale
                )
            );

            $requestedHeight = max(
                1,
                (int) round(
                    $sourceHeight * $scale
                )
            );
        }

        return [
            $requestedWidth,
            $requestedHeight,
        ];
    }

    private function filter(
        mixed $image,
        int $filter,
        mixed ...$arguments
    ): void {
        $applied = imagefilter(
            $image,
            $filter,
            ...$arguments
        );

        if (! $applied) {
            throw new RuntimeException(
                'Een afbeeldingsfilter kon niet worden toegepast.'
            );
        }
    }

    private function encodeJpeg(
        mixed $image,
        string $absolutePath,
        int $quality
    ): bool {
        $width = imagesx($image);
        $height = imagesy($image);

        $flattened = imagecreatetruecolor(
            $width,
            $height
        );

        if ($flattened === false) {
            return false;
        }

        try {
            $white = imagecolorallocate(
                $flattened,
                255,
                255,
                255
            );

            imagefilledrectangle(
                $flattened,
                0,
                0,
                max(0, $width - 1),
                max(0, $height - 1),
                $white
            );

            imagealphablending(
                $flattened,
                true
            );

            if (! imagecopy(
                $flattened,
                $image,
                0,
                0,
                0,
                0,
                $width,
                $height
            )) {
                return false;
            }

            imageinterlace(
                $flattened,
                true
            );

            return imagejpeg(
                $flattened,
                $absolutePath,
                $quality
            );
        } finally {
            $this->destroy(
                $flattened
            );
        }
    }

    private function pngCompressionLevel(
        int $quality
    ): int {
        return (int) round(
            9 -
            (
                (
                    $this->normalizeQuality(
                        $quality
                    ) / 100
                ) * 9
            )
        );
    }

    private function ensureFormatSupport(
        string $format
    ): void {
        $supported = match ($format) {
            'jpg' => function_exists('imagejpeg'),
            'png' => function_exists('imagepng'),
            'webp' => function_exists('imagewebp'),
            default => false,
        };

        if (! $supported) {
            throw ValidationException::withMessages([
                'format' => sprintf(
                    'Deze server ondersteunt geen %s-export via GD.',
                    strtoupper($format)
                ),
            ]);
        }
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function hexToRgb(
        string $hex
    ): array {
        $hex = ltrim(
            trim($hex),
            '#'
        );

        if (
            strlen($hex) === 3
        ) {
            $hex =
                $hex[0] . $hex[0] .
                $hex[1] . $hex[1] .
                $hex[2] . $hex[2];
        }

        if (
            strlen($hex) !== 6 ||
            ! ctype_xdigit($hex)
        ) {
            throw ValidationException::withMessages([
                'background_color' => 'Kies een geldige achtergrondkleur.',
            ]);
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
