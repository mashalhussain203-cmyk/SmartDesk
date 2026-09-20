<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ImageVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Mashal Studio image editor.
 *
 * Verwerkt private originals en versies via Laravel's local disk en gebruikt
 * GD voor resize, crop, rotate, flip, compress en convert.
 */
class ImageEditorController extends Controller
{
    private const MAX_DIMENSION = 12000;

    private const MAX_PIXELS = 80000000;

    private const DEFAULT_QUALITY = 88;

    private const OPERATIONS = [
        'resize',
        'crop',
        'rotate',
        'flip',
        'compress',
        'convert',
    ];

    private const OUTPUT_FORMATS = [
        'jpg',
        'png',
        'webp',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $images = Image::query()
            ->where('user_id', $user->id)
            ->withCount('versions')
            ->latest('id')
            ->paginate(18);

        return view('images.index', [
            'images' => $images,
        ]);
    }

    public function edit(Request $request, Image $image): View
    {
        $this->authorizeOwner($request, $image);

        $image->load([
            'versions' => function ($query) {
                $query->latest('id');
            },
        ]);

        return view('images.editor', [
            'image' => $image,
        ]);
    }

    public function file(
        Request $request,
        Image $image
    ): StreamedResponse {
        $this->authorizeOwner($request, $image);

        $disk = $this->localDisk();

        abort_unless(
            ! empty($image->original_path) &&
            $disk->exists($image->original_path),
            404,
            'Het originele afbeeldingsbestand bestaat niet meer.'
        );

        return $disk->response(
            $image->original_path,
            $this->safeDownloadName($image->original_name),
            [
                'Content-Type' => $image->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function downloadOriginal(
        Request $request,
        Image $image
    ): StreamedResponse {
        $this->authorizeOwner($request, $image);

        $disk = $this->localDisk();

        abort_unless(
            ! empty($image->original_path) &&
            $disk->exists($image->original_path),
            404,
            'Het originele afbeeldingsbestand bestaat niet meer.'
        );

        return $disk->download(
            $image->original_path,
            $this->safeDownloadName($image->original_name),
            [
                'Content-Type' => $image->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function versionFile(
        Request $request,
        Image $image,
        ImageVersion $version
    ): StreamedResponse {
        $this->authorizeVersion($request, $image, $version);

        $disk = $this->localDisk();

        abort_unless(
            ! empty($version->path) &&
            $disk->exists($version->path),
            404,
            'Deze afbeeldingsversie bestaat niet meer.'
        );

        return $disk->response(
            $version->path,
            $this->safeDownloadName($version->file_name),
            [
                'Content-Type' => $this->mimeForFormat($version->format),
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function downloadVersion(
        Request $request,
        Image $image,
        ImageVersion $version
    ): StreamedResponse {
        $this->authorizeVersion($request, $image, $version);

        $disk = $this->localDisk();

        abort_unless(
            ! empty($version->path) &&
            $disk->exists($version->path),
            404,
            'Deze afbeeldingsversie bestaat niet meer.'
        );

        return $disk->download(
            $version->path,
            $this->safeDownloadName($version->file_name),
            [
                'Content-Type' => $this->mimeForFormat($version->format),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function process(
        Request $request,
        Image $image
    ): RedirectResponse {
        $this->authorizeOwner($request, $image);

        $this->ensureGdAvailable();

        $validated = $request->validate(
            [
                'operation' => [
                    'required',
                    'string',
                    'in:' . implode(',', self::OPERATIONS),
                ],

                'source_version_id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'width' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:' . self::MAX_DIMENSION,
                ],

                'height' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:' . self::MAX_DIMENSION,
                ],

                'keep_aspect' => [
                    'nullable',
                    'boolean',
                ],

                'crop_x' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'crop_y' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'crop_width' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:' . self::MAX_DIMENSION,
                ],

                'crop_height' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:' . self::MAX_DIMENSION,
                ],

                'angle' => [
                    'nullable',
                    'integer',
                    'in:-270,-180,-90,90,180,270',
                ],

                'flip_direction' => [
                    'nullable',
                    'string',
                    'in:horizontal,vertical',
                ],

                'quality' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],

                'format' => [
                    'nullable',
                    'string',
                    'in:jpg,jpeg,png,webp',
                ],
            ],
            [
                'operation.required' => 'Kies eerst een afbeeldingsbewerking.',
                'operation.in' => 'De gekozen afbeeldingsbewerking wordt niet ondersteund.',
                'width.max' => sprintf(
                    'De maximale breedte is %d pixels.',
                    self::MAX_DIMENSION
                ),
                'height.max' => sprintf(
                    'De maximale hoogte is %d pixels.',
                    self::MAX_DIMENSION
                ),
                'quality.min' => 'De kwaliteit moet minimaal 1 zijn.',
                'quality.max' => 'De kwaliteit mag maximaal 100 zijn.',
            ]
        );

        $user = $request->user();

        abort_unless($user !== null, 401);

        $sourceVersion = $this->resolveSourceVersion(
            $request,
            $image,
            $validated['source_version_id'] ?? null
        );

        if ($sourceVersion) {
            $sourcePath = $sourceVersion->path;
            $sourceFormat = $this->normalizeFormat($sourceVersion->format);
        } else {
            $sourcePath = $image->original_path;
            $sourceFormat = $this->formatFromMime($image->mime_type);
        }

        if (empty($sourcePath)) {
            throw ValidationException::withMessages([
                'image' => 'Het bronbestand van deze afbeelding ontbreekt.',
            ]);
        }

        $disk = $this->localDisk();

        if (! $disk->exists($sourcePath)) {
            throw ValidationException::withMessages([
                'image' => 'Het bronbestand van deze afbeelding bestaat niet meer.',
            ]);
        }

        $absoluteSourcePath = $disk->path($sourcePath);
        $source = $this->loadGdImage($absoluteSourcePath);

        $outputImage = null;
        $temporaryFile = null;
        $storedPath = null;

        try {
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);

            $this->assertValidDimensions(
                $sourceWidth,
                $sourceHeight
            );

            $operation = (string) $validated['operation'];

            $outputImage = $this->applyOperation(
                $source,
                $sourceWidth,
                $sourceHeight,
                $operation,
                $validated
            );

            $outputWidth = imagesx($outputImage);
            $outputHeight = imagesy($outputImage);

            $this->assertValidDimensions(
                $outputWidth,
                $outputHeight
            );

            $outputFormat = $this->resolveOutputFormat(
                $operation,
                $sourceFormat,
                $validated['format'] ?? null
            );

            $this->ensureFormatSupport($outputFormat);

            $quality = $this->resolveQuality(
                $validated['quality'] ?? null
            );

            $directory = sprintf(
                'users/%d/images/versions/%d',
                $user->id,
                $image->id
            );

            $fileName = $this->makeVersionFileName(
                $image,
                $operation,
                $outputFormat
            );

            $storedPath = $directory . '/' . $fileName;

            $temporaryFile = tempnam(
                sys_get_temp_dir(),
                'mashal-image-'
            );

            if ($temporaryFile === false) {
                throw new RuntimeException(
                    'Er kon geen tijdelijk afbeeldingsbestand worden aangemaakt.'
                );
            }

            $this->encodeImage(
                $outputImage,
                $temporaryFile,
                $outputFormat,
                $quality
            );

            $temporarySize = filesize($temporaryFile);

            if (
                $temporarySize === false ||
                $temporarySize < 1
            ) {
                throw new RuntimeException(
                    'De gegenereerde afbeelding is leeg.'
                );
            }

            if (! $disk->exists($directory)) {
                $created = $disk->makeDirectory($directory);

                if ($created === false) {
                    throw new RuntimeException(
                        'De opslagmap voor afbeeldingsversies kon niet worden aangemaakt.'
                    );
                }
            }

            $stream = fopen(
                $temporaryFile,
                'rb'
            );

            if ($stream === false) {
                throw new RuntimeException(
                    'Het tijdelijke afbeeldingsbestand kon niet worden geopend.'
                );
            }

            try {
                $stored = $disk->put(
                    $storedPath,
                    $stream
                );
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                throw new RuntimeException(
                    'De nieuwe afbeeldingsversie kon niet worden opgeslagen.'
                );
            }

            if (! $disk->exists($storedPath)) {
                throw new RuntimeException(
                    'Het gegenereerde afbeeldingsbestand kon na opslag niet worden gevonden.'
                );
            }

            $fileSize = $disk->size($storedPath);

            try {
                $version = DB::transaction(
                    function () use (
                        $user,
                        $image,
                        $fileName,
                        $storedPath,
                        $outputFormat,
                        $outputWidth,
                        $outputHeight,
                        $quality,
                        $fileSize,
                        $operation
                    ): ImageVersion {
                        return ImageVersion::create([
                            'image_id' => $image->id,
                            'user_id' => $user->id,
                            'file_name' => $fileName,
                            'path' => $storedPath,
                            'format' => $outputFormat,
                            'width' => $outputWidth,
                            'height' => $outputHeight,
                            'quality' => $quality,
                            'file_size' => $fileSize,
                            'operation' => $operation,
                        ]);
                    }
                );
            } catch (Throwable $exception) {
                if (
                    $storedPath &&
                    $disk->exists($storedPath)
                ) {
                    $disk->delete($storedPath);
                }

                throw $exception;
            }

            return redirect()
                ->route('images.editor', $image)
                ->with(
                    'status',
                    sprintf(
                        '%s voltooid. Versie #%d is opgeslagen als %d × %d %s.',
                        $this->operationLabel($operation),
                        $version->id,
                        $outputWidth,
                        $outputHeight,
                        strtoupper($outputFormat)
                    )
                );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'image' => 'De bewerking kon niet worden uitgevoerd. Probeer het opnieuw.',
            ]);
        } finally {
            if (
                is_string($temporaryFile) &&
                is_file($temporaryFile)
            ) {
                @unlink($temporaryFile);
            }

            if (
                $outputImage !== null &&
                $outputImage !== $source
            ) {
                $this->destroyGdImage($outputImage);
            }

            $this->destroyGdImage($source);
        }
    }

    public function destroyVersion(
        Request $request,
        Image $image,
        ImageVersion $version
    ): RedirectResponse {
        $this->authorizeVersion(
            $request,
            $image,
            $version
        );

        $path = $version->path;

        DB::transaction(
            function () use ($version): void {
                $version->delete();
            }
        );

        if ($path) {
            $disk = $this->localDisk();

            try {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('images.editor', $image)
            ->with(
                'status',
                'De afbeeldingsversie is verwijderd. Het originele bestand is behouden.'
            );
    }

    public function destroy(
        Request $request,
        Image $image
    ): RedirectResponse {
        $this->authorizeOwner(
            $request,
            $image
        );

        $image->load('versions');

        $paths = $image->versions
            ->pluck('path')
            ->filter()
            ->map(fn ($path) => (string) $path)
            ->values()
            ->all();

        if (! empty($image->original_path)) {
            $paths[] = (string) $image->original_path;
        }

        $imageId = (int) $image->id;
        $userId = (int) $image->user_id;

        DB::transaction(
            function () use ($image): void {
                $image->delete();
            }
        );

        $disk = $this->localDisk();

        foreach (array_unique($paths) as $path) {
            if (
                is_string($path) &&
                $path !== '' &&
                $disk->exists($path)
            ) {
                $disk->delete($path);
            }
        }

        $versionsDirectory = sprintf(
            'users/%d/images/versions/%d',
            $userId,
            $imageId
        );

        if ($disk->exists($versionsDirectory)) {
            $disk->deleteDirectory($versionsDirectory);
        }

        return redirect()
            ->route('images.index')
            ->with(
                'status',
                'De afbeelding en alle opgeslagen versies zijn verwijderd.'
            );
    }

    private function resolveSourceVersion(
        Request $request,
        Image $image,
        mixed $sourceVersionId
    ): ?ImageVersion {
        if (
            $sourceVersionId === null ||
            $sourceVersionId === ''
        ) {
            return null;
        }

        $user = $request->user();

        abort_unless($user !== null, 401);

        $version = ImageVersion::query()
            ->whereKey((int) $sourceVersionId)
            ->where('image_id', $image->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $version) {
            throw ValidationException::withMessages([
                'source_version_id' => 'De gekozen bronversie bestaat niet of hoort niet bij dit project.',
            ]);
        }

        return $version;
    }

    private function applyOperation(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        string $operation,
        array $data
    ): mixed {
        return match ($operation) {
            'resize' => $this->resizeImage(
                $source,
                $sourceWidth,
                $sourceHeight,
                $data
            ),

            'crop' => $this->cropImage(
                $source,
                $sourceWidth,
                $sourceHeight,
                $data
            ),

            'rotate' => $this->rotateImage(
                $source,
                $data
            ),

            'flip' => $this->flipImage(
                $source,
                $sourceWidth,
                $sourceHeight,
                $data
            ),

            'compress',
            'convert' => $this->copyImage(
                $source,
                $sourceWidth,
                $sourceHeight
            ),

            default => throw ValidationException::withMessages([
                'operation' => 'Onbekende afbeeldingsbewerking.',
            ]),
        };
    }

    private function resizeImage(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        array $data
    ): mixed {
        $requestedWidth = isset($data['width'])
            ? (int) $data['width']
            : null;

        $requestedHeight = isset($data['height'])
            ? (int) $data['height']
            : null;

        if (
            $requestedWidth === null &&
            $requestedHeight === null
        ) {
            throw ValidationException::withMessages([
                'width' => 'Vul een nieuwe breedte en/of hoogte in.',
            ]);
        }

        $keepAspect = filter_var(
            $data['keep_aspect'] ?? true,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($keepAspect) {
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
            } elseif (
                $requestedWidth !== null &&
                $requestedHeight !== null
            ) {
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
        } else {
            $requestedWidth ??= $sourceWidth;
            $requestedHeight ??= $sourceHeight;
        }

        $this->assertValidDimensions(
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
            $this->destroyGdImage($canvas);

            throw new RuntimeException(
                'Resize kon niet worden uitgevoerd.'
            );
        }

        return $canvas;
    }

    private function cropImage(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        array $data
    ): mixed {
        $x = isset($data['crop_x'])
            ? (int) $data['crop_x']
            : 0;

        $y = isset($data['crop_y'])
            ? (int) $data['crop_y']
            : 0;

        $width = isset($data['crop_width'])
            ? (int) $data['crop_width']
            : null;

        $height = isset($data['crop_height'])
            ? (int) $data['crop_height']
            : null;

        if (
            $width === null ||
            $height === null
        ) {
            throw ValidationException::withMessages([
                'crop_width' => 'Vul een crop-breedte en crop-hoogte in.',
            ]);
        }

        if (
            $x < 0 ||
            $y < 0 ||
            $x >= $sourceWidth ||
            $y >= $sourceHeight ||
            ($x + $width) > $sourceWidth ||
            ($y + $height) > $sourceHeight
        ) {
            throw ValidationException::withMessages([
                'crop_width' => 'De gekozen crop valt buiten de grenzen van de afbeelding.',
            ]);
        }

        $this->assertValidDimensions(
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
            $this->destroyGdImage($canvas);

            throw new RuntimeException(
                'Crop kon niet worden uitgevoerd.'
            );
        }

        return $canvas;
    }

    private function rotateImage(
        mixed $source,
        array $data
    ): mixed {
        if (! isset($data['angle'])) {
            throw ValidationException::withMessages([
                'angle' => 'Kies een rotatiehoek.',
            ]);
        }

        $angle = (int) $data['angle'];

        $transparent = imagecolorallocatealpha(
            $source,
            0,
            0,
            0,
            127
        );

        $rotated = imagerotate(
            $source,
            -$angle,
            $transparent
        );

        if ($rotated === false) {
            throw new RuntimeException(
                'Rotatie kon niet worden uitgevoerd.'
            );
        }

        imagealphablending(
            $rotated,
            false
        );

        imagesavealpha(
            $rotated,
            true
        );

        return $rotated;
    }

    private function flipImage(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        array $data
    ): mixed {
        $direction = $data['flip_direction'] ?? null;

        if (
            ! in_array(
                $direction,
                [
                    'horizontal',
                    'vertical',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'flip_direction' => 'Kies horizontaal of verticaal spiegelen.',
            ]);
        }

        $copy = $this->copyImage(
            $source,
            $sourceWidth,
            $sourceHeight
        );

        $mode = $direction === 'horizontal'
            ? IMG_FLIP_HORIZONTAL
            : IMG_FLIP_VERTICAL;

        if (! imageflip($copy, $mode)) {
            $this->destroyGdImage($copy);

            throw new RuntimeException(
                'Spiegelen kon niet worden uitgevoerd.'
            );
        }

        return $copy;
    }

    private function copyImage(
        mixed $source,
        int $width,
        int $height
    ): mixed {
        $canvas = $this->createTransparentCanvas(
            $width,
            $height
        );

        $copied = imagecopy(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height
        );

        if (! $copied) {
            $this->destroyGdImage($canvas);

            throw new RuntimeException(
                'De afbeelding kon niet worden gekopieerd.'
            );
        }

        return $canvas;
    }

    private function createTransparentCanvas(
        int $width,
        int $height
    ): mixed {
        $this->assertValidDimensions(
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

    private function loadGdImage(
        string $absolutePath
    ): mixed {
        if (
            $absolutePath === '' ||
            ! is_file($absolutePath)
        ) {
            throw ValidationException::withMessages([
                'image' => 'Het afbeeldingsbestand bestaat niet meer.',
            ]);
        }

        $contents = @file_get_contents(
            $absolutePath
        );

        if (
            $contents === false ||
            $contents === ''
        ) {
            throw ValidationException::withMessages([
                'image' => 'Het afbeeldingsbestand kon niet worden gelezen.',
            ]);
        }

        $gdImage = @imagecreatefromstring(
            $contents
        );

        unset($contents);

        if ($gdImage === false) {
            throw ValidationException::withMessages([
                'image' => 'Dit afbeeldingsbestand kan niet door GD worden geopend.',
            ]);
        }

        imagealphablending(
            $gdImage,
            true
        );

        imagesavealpha(
            $gdImage,
            true
        );

        return $gdImage;
    }

    private function encodeImage(
        mixed $image,
        string $path,
        string $format,
        int $quality
    ): void {
        $format = $this->normalizeFormat(
            $format
        );

        $this->ensureFormatSupport(
            $format
        );

        $encoded = match ($format) {
            'jpg' => $this->encodeJpeg(
                $image,
                $path,
                $quality
            ),

            'png' => imagepng(
                $image,
                $path,
                $this->pngCompressionLevel($quality)
            ),

            'webp' => imagewebp(
                $image,
                $path,
                $quality
            ),

            default => false,
        };

        if (! $encoded) {
            throw new RuntimeException(
                'De afbeelding kon niet naar het gekozen formaat worden geëxporteerd.'
            );
        }
    }

    private function encodeJpeg(
        mixed $image,
        string $path,
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

            $copied = imagecopy(
                $flattened,
                $image,
                0,
                0,
                0,
                0,
                $width,
                $height
            );

            if (! $copied) {
                return false;
            }

            imageinterlace(
                $flattened,
                true
            );

            return imagejpeg(
                $flattened,
                $path,
                $quality
            );
        } finally {
            $this->destroyGdImage(
                $flattened
            );
        }
    }

    private function pngCompressionLevel(
        int $quality
    ): int {
        $quality = max(
            1,
            min(
                100,
                $quality
            )
        );

        return (int) round(
            9 - (($quality / 100) * 9)
        );
    }

    private function resolveOutputFormat(
        string $operation,
        string $sourceFormat,
        mixed $requestedFormat
    ): string {
        if ($operation === 'convert') {
            if (
                $requestedFormat === null ||
                $requestedFormat === ''
            ) {
                throw ValidationException::withMessages([
                    'format' => 'Kies het formaat waarnaar je wilt converteren.',
                ]);
            }

            $format = $this->normalizeFormat(
                (string) $requestedFormat
            );
        } else {
            $format = $this->normalizeFormat(
                $sourceFormat
            );
        }

        if (
            ! in_array(
                $format,
                self::OUTPUT_FORMATS,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'format' => 'Dit uitvoerformaat wordt niet ondersteund.',
            ]);
        }

        return $format;
    }

    private function resolveQuality(
        mixed $requestedQuality
    ): int {
        if (
            $requestedQuality === null ||
            $requestedQuality === ''
        ) {
            return self::DEFAULT_QUALITY;
        }

        return max(
            1,
            min(
                100,
                (int) $requestedQuality
            )
        );
    }

    private function makeVersionFileName(
        Image $image,
        string $operation,
        string $format
    ): string {
        $base = pathinfo(
            (string) $image->original_name,
            PATHINFO_FILENAME
        );

        $base = Str::slug($base);

        if ($base === '') {
            $base = 'image';
        }

        return sprintf(
            '%s-%s-%s.%s',
            $base,
            Str::slug($operation),
            Str::uuid()->toString(),
            $this->normalizeFormat($format)
        );
    }

    private function normalizeFormat(
        ?string $format
    ): string {
        $format = strtolower(
            trim(
                (string) $format
            )
        );

        if ($format === 'jpeg') {
            return 'jpg';
        }

        return $format;
    }

    private function formatFromMime(
        ?string $mime
    ): string {
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
                'image' => 'Het formaat van het bronbestand wordt niet ondersteund.',
            ]),
        };
    }

    private function mimeForFormat(
        ?string $format
    ): string {
        return match (
            $this->normalizeFormat($format)
        ) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    private function assertValidDimensions(
        int $width,
        int $height
    ): void {
        if (
            $width < 1 ||
            $height < 1 ||
            $width > self::MAX_DIMENSION ||
            $height > self::MAX_DIMENSION
        ) {
            throw ValidationException::withMessages([
                'dimensions' => sprintf(
                    'Afmetingen moeten tussen 1 en %d pixels liggen.',
                    self::MAX_DIMENSION
                ),
            ]);
        }

        $this->assertValidPixelCount(
            $width,
            $height
        );
    }

    private function assertValidPixelCount(
        int $width,
        int $height
    ): void {
        if (
            $height > 0 &&
            $width > intdiv(PHP_INT_MAX, $height)
        ) {
            throw ValidationException::withMessages([
                'dimensions' => 'De berekende afmetingen zijn ongeldig.',
            ]);
        }

        $pixels = $width * $height;

        if ($pixels > self::MAX_PIXELS) {
            throw ValidationException::withMessages([
                'dimensions' => sprintf(
                    'De afbeelding bevat te veel pixels. Maximaal %s pixels zijn toegestaan.',
                    number_format(
                        self::MAX_PIXELS,
                        0,
                        ',',
                        '.'
                    )
                ),
            ]);
        }
    }

    private function ensureGdAvailable(): void
    {
        if (
            ! extension_loaded('gd') ||
            ! function_exists('imagecreatefromstring') ||
            ! function_exists('imagecreatetruecolor') ||
            ! function_exists('imagecopyresampled') ||
            ! function_exists('imagecopy') ||
            ! function_exists('imagesx') ||
            ! function_exists('imagesy')
        ) {
            throw ValidationException::withMessages([
                'image' => 'De GD-extensie is niet beschikbaar of onvolledig op deze PHP-server.',
            ]);
        }
    }

    private function ensureFormatSupport(
        string $format
    ): void {
        $format = $this->normalizeFormat(
            $format
        );

        $supported = match ($format) {
            'jpg' => function_exists('imagejpeg'),
            'png' => function_exists('imagepng'),
            'webp' => function_exists('imagewebp'),
            default => false,
        };

        if (! $supported) {
            throw ValidationException::withMessages([
                'format' => sprintf(
                    'Deze PHP/GD-installatie ondersteunt geen %s-export.',
                    strtoupper($format)
                ),
            ]);
        }
    }

    private function authorizeOwner(
        Request $request,
        Image $image
    ): void {
        $user = $request->user();

        abort_unless(
            $user !== null &&
            (int) $image->user_id ===
            (int) $user->id,
            403
        );
    }

    private function authorizeVersion(
        Request $request,
        Image $image,
        ImageVersion $version
    ): void {
        $this->authorizeOwner(
            $request,
            $image
        );

        $user = $request->user();

        abort_unless(
            $user !== null &&
            (int) $version->image_id ===
            (int) $image->id &&
            (int) $version->user_id ===
            (int) $user->id,
            404
        );
    }

    private function localDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk;
    }

    private function safeDownloadName(
        ?string $name
    ): string {
        $name = str_replace(
            '\\',
            '/',
            (string) $name
        );

        $name = basename($name);

        $name = preg_replace(
            '/[^\pL\pN._ -]+/u',
            '-',
            $name
        );

        $name = trim(
            (string) $name,
            " .-\t\n\r\0\x0B"
        );

        if ($name === '') {
            return 'mashal-image';
        }

        if (mb_strlen($name) > 220) {
            $extension = pathinfo(
                $name,
                PATHINFO_EXTENSION
            );

            $baseName = pathinfo(
                $name,
                PATHINFO_FILENAME
            );

            $baseName = mb_substr(
                $baseName,
                0,
                180
            );

            $name = $extension !== ''
                ? $baseName . '.' . $extension
                : $baseName;
        }

        return $name;
    }

    private function operationLabel(
        string $operation
    ): string {
        return match ($operation) {
            'resize' => 'Resize',
            'crop' => 'Crop',
            'rotate' => 'Rotatie',
            'flip' => 'Spiegelen',
            'compress' => 'Compressie',
            'convert' => 'Conversie',
            default => ucfirst($operation),
        };
    }

    private function destroyGdImage(
        mixed $image
    ): void {
        if (
            $image !== null &&
            function_exists('imagedestroy')
        ) {
            @imagedestroy($image);
        }
    }
}
