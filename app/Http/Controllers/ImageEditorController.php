<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ImageVersion;
use App\Services\BackgroundRemovalService;
use App\Services\ImageProcessingService;
use App\Services\PassportPhotoService;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ImageEditorController extends Controller
{
    /**
     * Alle bewerkingen die via de centrale process-route mogen worden uitgevoerd.
     *
     * "background" is één editor-tool. Bij opslag wordt de database-operation
     * specifieker opgeslagen als remove_background, background_color of
     * background_image.
     *
     * @var array<int, string>
     */
    private const OPERATIONS = [
        'resize',
        'crop',
        'rotate',
        'flip',
        'enhance',
        'passport',
        'compress',
        'convert',
        'background',
    ];

    public function __construct(
        private readonly ImageProcessingService $images,
        private readonly PassportPhotoService $passportPhotos,
        private readonly BackgroundRemovalService $backgroundRemoval
    ) {
    }

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

    public function edit(
        Request $request,
        Image $image
    ): View {
        $this->authorizeOwner(
            $request,
            $image
        );

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
        $this->authorizeOwner(
            $request,
            $image
        );

        $disk = $this->localDisk();

        abort_unless(
            ! empty($image->original_path) &&
            $disk->exists($image->original_path),
            404,
            'Het originele afbeeldingsbestand bestaat niet meer.'
        );

        return $disk->response(
            $image->original_path,
            $this->safeDownloadName(
                $image->original_name
            ),
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
        $this->authorizeOwner(
            $request,
            $image
        );

        $disk = $this->localDisk();

        abort_unless(
            ! empty($image->original_path) &&
            $disk->exists($image->original_path),
            404,
            'Het originele afbeeldingsbestand bestaat niet meer.'
        );

        return $disk->download(
            $image->original_path,
            $this->safeDownloadName(
                $image->original_name
            ),
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
        $this->authorizeVersion(
            $request,
            $image,
            $version
        );

        $disk = $this->localDisk();

        abort_unless(
            ! empty($version->path) &&
            $disk->exists($version->path),
            404,
            'Deze afbeeldingsversie bestaat niet meer.'
        );

        return $disk->response(
            $version->path,
            $this->safeDownloadName(
                $version->file_name
            ),
            [
                'Content-Type' => $this->images->mimeForFormat(
                    (string) $version->format
                ),
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
        $this->authorizeVersion(
            $request,
            $image,
            $version
        );

        $disk = $this->localDisk();

        abort_unless(
            ! empty($version->path) &&
            $disk->exists($version->path),
            404,
            'Deze afbeeldingsversie bestaat niet meer.'
        );

        return $disk->download(
            $version->path,
            $this->safeDownloadName(
                $version->file_name
            ),
            [
                'Content-Type' => $this->images->mimeForFormat(
                    (string) $version->format
                ),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function process(
        Request $request,
        Image $image
    ): RedirectResponse {
        $this->authorizeOwner(
            $request,
            $image
        );

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $user = $request->user();

        abort_unless(
            $user !== null,
            401
        );

        $operation =
            (string) $validated['operation'];

        $sourceVersion =
            $this->resolveSourceVersion(
                $request,
                $image,
                $validated['source_version_id'] ?? null
            );

        if ($sourceVersion !== null) {
            $sourcePath =
                (string) $sourceVersion->path;

            $sourceFormat =
                $this->images->normalizeFormat(
                    (string) $sourceVersion->format
                );
        } else {
            $sourcePath =
                (string) $image->original_path;

            $sourceFormat =
                $this->images->formatFromMime(
                    $image->mime_type
                );
        }

        if ($sourcePath === '') {
            throw ValidationException::withMessages([
                'image' => 'Het bronbestand van deze afbeelding ontbreekt.',
            ]);
        }

        $disk =
            $this->localDisk();

        if (! $disk->exists($sourcePath)) {
            throw ValidationException::withMessages([
                'image' => 'Het bronbestand van deze afbeelding bestaat niet meer. Upload een nieuwe afbeelding of kies een bestaande versie.',
            ]);
        }

        if ($operation === 'background') {
            try {
                return $this->processBackground(
                    $request,
                    $image,
                    (int) $user->id,
                    $sourcePath,
                    $validated
                );
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'image' => 'De achtergrondbewerking kon niet worden uitgevoerd. Probeer het opnieuw.',
                ]);
            }
        }

        $source = null;
        $output = null;
        $storedPath = null;

        try {
            $this->images->ensureAvailable();

            $source =
                $this->images->load(
                    $disk->path(
                        $sourcePath
                    )
                );

            $dimensions =
                $this->images->dimensions(
                    $source
                );

            $sourceWidth =
                $dimensions['width'];

            $sourceHeight =
                $dimensions['height'];

            $output =
                $this->applyOperation(
                    $source,
                    $sourceWidth,
                    $sourceHeight,
                    $operation,
                    $validated
                );

            $outputDimensions =
                $this->images->dimensions(
                    $output
                );

            $outputFormat =
                $this->resolveOutputFormat(
                    $operation,
                    $sourceFormat,
                    $validated['format'] ?? null
                );

            $quality =
                $this->images->normalizeQuality(
                    isset($validated['quality'])
                        ? (int) $validated['quality']
                        : ImageProcessingService::DEFAULT_QUALITY
                );

            $directory =
                $this->versionDirectory(
                    (int) $user->id,
                    (int) $image->id
                );

            $fileName =
                $this->makeVersionFileName(
                    $image,
                    $operation,
                    $outputFormat
                );

            $storedPath =
                $directory . '/' . $fileName;

            /*
             * De local disk wijst in productie naar /app/storage/app/private.
             * ImageProcessingService schrijft dus rechtstreeks op het Railway
             * volume wanneer dat op die map gemount is.
             */
            $absoluteOutputPath =
                $disk->path(
                    $storedPath
                );

            $this->images->save(
                $output,
                $absoluteOutputPath,
                $outputFormat,
                $quality
            );

            if (! $disk->exists($storedPath)) {
                throw new RuntimeException(
                    'Het gegenereerde afbeeldingsbestand kon na opslag niet worden gevonden.'
                );
            }

            $fileSize =
                (int) $disk->size(
                    $storedPath
                );

            $version =
                $this->createVersionRecord(
                    image: $image,
                    userId: (int) $user->id,
                    fileName: $fileName,
                    path: $storedPath,
                    format: $outputFormat,
                    width: $outputDimensions['width'],
                    height: $outputDimensions['height'],
                    quality: $quality,
                    fileSize: $fileSize,
                    operation: $operation
                );

            return $this->successfulProcessRedirect(
                $image,
                $version
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            if (
                $storedPath !== null &&
                $disk->exists($storedPath)
            ) {
                try {
                    $disk->delete($storedPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            report($exception);

            throw ValidationException::withMessages([
                'image' => 'De bewerking kon niet worden uitgevoerd. Probeer het opnieuw.',
            ]);
        } finally {
            if (
                $output !== null &&
                $output !== $source
            ) {
                $this->images->destroy(
                    $output
                );
            }

            if ($source !== null) {
                $this->images->destroy(
                    $source
                );
            }
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

        $path =
            (string) $version->path;

        DB::transaction(
            function () use ($version): void {
                $version->delete();
            }
        );

        if ($path !== '') {
            $disk =
                $this->localDisk();

            try {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route(
                'images.editor',
                $image
            )
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

        $image->load(
            'versions'
        );

        $paths =
            $image->versions
                ->pluck('path')
                ->filter()
                ->map(
                    fn ($path) => (string) $path
                )
                ->values()
                ->all();

        if (! empty($image->original_path)) {
            $paths[] =
                (string) $image->original_path;
        }

        $imageId =
            (int) $image->id;

        $userId =
            (int) $image->user_id;

        DB::transaction(
            function () use ($image): void {
                $image->delete();
            }
        );

        $disk =
            $this->localDisk();

        foreach (
            array_unique($paths)
            as $path
        ) {
            if (
                is_string($path) &&
                $path !== '' &&
                $disk->exists($path)
            ) {
                try {
                    $disk->delete($path);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        $versionsDirectory =
            $this->versionDirectory(
                $userId,
                $imageId
            );

        if ($disk->exists($versionsDirectory)) {
            try {
                $disk->deleteDirectory(
                    $versionsDirectory
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('images.index')
            ->with(
                'status',
                'De afbeelding en alle opgeslagen versies zijn verwijderd.'
            );
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validationRules(): array
    {
        return [
            'operation' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::OPERATIONS
                ),
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
                'max:' . ImageProcessingService::MAX_DIMENSION,
            ],

            'height' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . ImageProcessingService::MAX_DIMENSION,
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
                'max:' . ImageProcessingService::MAX_DIMENSION,
            ],

            'crop_height' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . ImageProcessingService::MAX_DIMENSION,
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

            'brightness' => [
                'nullable',
                'integer',
                'min:-100',
                'max:100',
            ],

            'contrast' => [
                'nullable',
                'integer',
                'min:-100',
                'max:100',
            ],

            'grayscale' => [
                'nullable',
                'boolean',
            ],

            'sepia' => [
                'nullable',
                'boolean',
            ],

            'blur' => [
                'nullable',
                'integer',
                'min:0',
                'max:6',
            ],

            'passport_preset' => [
                'nullable',
                'string',
                'max:80',
            ],

            'format' => [
                'nullable',
                'string',
                'in:jpg,jpeg,png,webp',
            ],

            /*
             * Background Removal.
             */
            'background_mode' => [
                'nullable',
                'string',
                'in:transparent,white,color,url,upload',
            ],

            'background_color' => [
                'nullable',
                'string',
                'max:32',
            ],

            'background_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'background_image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:20480',
            ],

            'background_format' => [
                'nullable',
                'string',
                'in:jpg,jpeg,png,webp',
            ],

            'background_size' => [
                'nullable',
                'string',
                'in:auto,preview,full,50mp',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'operation.required' => 'Kies eerst een afbeeldingsbewerking.',
            'operation.in' => 'De gekozen afbeeldingsbewerking wordt niet ondersteund.',
            'width.max' => sprintf(
                'De maximale breedte is %d pixels.',
                ImageProcessingService::MAX_DIMENSION
            ),
            'height.max' => sprintf(
                'De maximale hoogte is %d pixels.',
                ImageProcessingService::MAX_DIMENSION
            ),
            'quality.min' => 'De kwaliteit moet minimaal 1 zijn.',
            'quality.max' => 'De kwaliteit mag maximaal 100 zijn.',
            'background_url.url' => 'Vul een geldige URL voor de achtergrondafbeelding in.',
            'background_image.image' => 'Het achtergrondbestand moet een afbeelding zijn.',
            'background_image.mimes' => 'Gebruik JPG, PNG of WebP als achtergrondafbeelding.',
            'background_image.max' => 'De achtergrondafbeelding mag maximaal 20 MB zijn.',
        ];
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

        $user =
            $request->user();

        abort_unless(
            $user !== null,
            401
        );

        $version =
            ImageVersion::query()
                ->whereKey(
                    (int) $sourceVersionId
                )
                ->where(
                    'image_id',
                    $image->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->first();

        if ($version === null) {
            throw ValidationException::withMessages([
                'source_version_id' => 'De gekozen bronversie bestaat niet of hoort niet bij dit project.',
            ]);
        }

        return $version;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyOperation(
        mixed $source,
        int $sourceWidth,
        int $sourceHeight,
        string $operation,
        array $data
    ): mixed {
        return match ($operation) {
            'resize' =>
                $this->images->resize(
                    $source,
                    $sourceWidth,
                    $sourceHeight,
                    $this->nullableInteger(
                        $data['width'] ?? null
                    ),
                    $this->nullableInteger(
                        $data['height'] ?? null
                    ),
                    filter_var(
                        $data['keep_aspect'] ?? true,
                        FILTER_VALIDATE_BOOLEAN
                    )
                ),

            'crop' =>
                $this->images->crop(
                    $source,
                    $sourceWidth,
                    $sourceHeight,
                    $this->requiredInteger(
                        $data,
                        'crop_x',
                        'Kies een geldig cropgebied.'
                    ),
                    $this->requiredInteger(
                        $data,
                        'crop_y',
                        'Kies een geldig cropgebied.'
                    ),
                    $this->requiredInteger(
                        $data,
                        'crop_width',
                        'Kies een geldige crop-breedte.'
                    ),
                    $this->requiredInteger(
                        $data,
                        'crop_height',
                        'Kies een geldige crop-hoogte.'
                    )
                ),

            'rotate' =>
                $this->images->rotate(
                    $source,
                    $this->requiredInteger(
                        $data,
                        'angle',
                        'Kies een rotatiehoek.'
                    )
                ),

            'flip' =>
                $this->images->flip(
                    $source,
                    $sourceWidth,
                    $sourceHeight,
                    $this->requiredString(
                        $data,
                        'flip_direction',
                        'Kies horizontaal of verticaal spiegelen.'
                    )
                ),

            'enhance' =>
                $this->images->enhance(
                    $source,
                    $sourceWidth,
                    $sourceHeight,
                    $data
                ),

            'passport' =>
                $this->passportPhotos->create(
                    $source,
                    $sourceWidth,
                    $sourceHeight,
                    $this->requiredString(
                        $data,
                        'passport_preset',
                        'Kies een pasfoto- of ID-fotopreset.'
                    ),
                    $data
                ),

            'compress',
            'convert' =>
                $this->images->copy(
                    $source,
                    $sourceWidth,
                    $sourceHeight
                ),

            default =>
                throw ValidationException::withMessages([
                    'operation' => 'Onbekende afbeeldingsbewerking.',
                ]),
        };
    }

    /**
     * Verwerk echte achtergrondverwijdering via BackgroundRemovalService.
     *
     * @param array<string, mixed> $data
     */
    private function processBackground(
        Request $request,
        Image $image,
        int $userId,
        string $sourcePath,
        array $data
    ): RedirectResponse {
        $this->backgroundRemoval->assertConfigured();

        $mode =
            strtolower(
                trim(
                    (string) (
                        $data['background_mode'] ??
                        'transparent'
                    )
                )
            );

        $size =
            strtolower(
                trim(
                    (string) (
                        $data['background_size'] ??
                        'auto'
                    )
                )
            );

        $requestedFormat =
            $this->images->normalizeFormat(
                (string) (
                    $data['background_format'] ??
                    (
                        $mode === 'transparent'
                            ? 'png'
                            : 'jpg'
                    )
                )
            );

        if (
            $mode === 'transparent' &&
            ! in_array(
                $requestedFormat,
                ['png', 'webp'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'background_format' => 'Een transparante achtergrond moet als PNG of WebP worden opgeslagen.',
            ]);
        }

        $storedOperation =
            match ($mode) {
                'transparent' => 'remove_background',
                'white',
                'color' => 'background_color',
                'url',
                'upload' => 'background_image',

                default => throw ValidationException::withMessages([
                    'background_mode' => 'Kies een geldige achtergrondbewerking.',
                ]),
            };

        $directory =
            $this->versionDirectory(
                $userId,
                (int) $image->id
            );

        $fileName =
            $this->makeVersionFileName(
                $image,
                $storedOperation,
                $requestedFormat
            );

        $storedPath =
            $directory . '/' . $fileName;

        $options = [
            'size' => $size,
            'format' => $requestedFormat,
        ];

        $result = null;

        try {
            $result =
                match ($mode) {
                    'transparent' =>
                        $this->backgroundRemoval->transparent(
                            'local',
                            $sourcePath,
                            'local',
                            $storedPath,
                            $options
                        ),

                    'white' =>
                        $this->backgroundRemoval->withColor(
                            'local',
                            $sourcePath,
                            'local',
                            $storedPath,
                            '#ffffff',
                            $options
                        ),

                    'color' =>
                        $this->backgroundRemoval->withColor(
                            'local',
                            $sourcePath,
                            'local',
                            $storedPath,
                            $this->requiredString(
                                $data,
                                'background_color',
                                'Kies een achtergrondkleur.'
                            ),
                            $options
                        ),

                    'url' =>
                        $this->backgroundRemoval->withBackgroundUrl(
                            'local',
                            $sourcePath,
                            'local',
                            $storedPath,
                            $this->requiredString(
                                $data,
                                'background_url',
                                'Vul een URL van een achtergrondafbeelding in.'
                            ),
                            $options
                        ),

                    'upload' =>
                        $this->backgroundRemoval->withBackgroundFile(
                            'local',
                            $sourcePath,
                            'local',
                            $storedPath,
                            $this->uploadedBackgroundPath(
                                $request
                            ),
                            $options
                        ),

                    default =>
                        throw ValidationException::withMessages([
                            'background_mode' => 'Kies een geldige achtergrondbewerking.',
                        ]),
                };

            $disk =
                $this->localDisk();

            if (! $disk->exists($storedPath)) {
                throw new RuntimeException(
                    'De achtergrondbewerking is voltooid, maar het resultaat kon niet in storage worden gevonden.'
                );
            }

            [
                $width,
                $height,
            ] = $this->backgroundResultDimensions(
                $result,
                $storedPath
            );

            $format =
                $this->images->normalizeFormat(
                    (string) (
                        $result['format'] ??
                        $requestedFormat
                    )
                );

            $fileSize =
                (int) (
                    $result['size'] ??
                    $disk->size($storedPath)
                );

            $version =
                $this->createVersionRecord(
                    image: $image,
                    userId: $userId,
                    fileName: $fileName,
                    path: $storedPath,
                    format: $format,
                    width: $width,
                    height: $height,
                    quality: 100,
                    fileSize: $fileSize,
                    operation: $storedOperation
                );

            return $this->successfulProcessRedirect(
                $image,
                $version
            );
        } catch (Throwable $exception) {
            $disk =
                $this->localDisk();

            if ($disk->exists($storedPath)) {
                try {
                    $disk->delete(
                        $storedPath
                    );
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }
    }

    private function uploadedBackgroundPath(
        Request $request
    ): string {
        $file =
            $request->file(
                'background_image'
            );

        if (
            ! $file instanceof UploadedFile ||
            ! $file->isValid()
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'Kies een geldige achtergrondafbeelding om te uploaden.',
            ]);
        }

        $path =
            $file->getRealPath();

        if (
            ! is_string($path) ||
            $path === '' ||
            ! is_file($path)
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'De geüploade achtergrondafbeelding kon niet worden gelezen.',
            ]);
        }

        return $path;
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array{0:int,1:int}
     */
    private function backgroundResultDimensions(
        array $result,
        string $storedPath
    ): array {
        $width =
            isset($result['width'])
                ? (int) $result['width']
                : 0;

        $height =
            isset($result['height'])
                ? (int) $result['height']
                : 0;

        if (
            $width > 0 &&
            $height > 0
        ) {
            $this->images->assertDimensions(
                $width,
                $height
            );

            return [
                $width,
                $height,
            ];
        }

        $absolutePath =
            $this->localDisk()->path(
                $storedPath
            );

        $size =
            @getimagesize(
                $absolutePath
            );

        if (
            ! is_array($size) ||
            ! isset(
                $size[0],
                $size[1]
            )
        ) {
            throw new RuntimeException(
                'De afmetingen van het background-removal resultaat konden niet worden bepaald.'
            );
        }

        $width =
            (int) $size[0];

        $height =
            (int) $size[1];

        $this->images->assertDimensions(
            $width,
            $height
        );

        return [
            $width,
            $height,
        ];
    }

    private function resolveOutputFormat(
        string $operation,
        string $sourceFormat,
        mixed $requestedFormat
    ): string {
        if ($operation === 'convert') {
            if (
                $requestedFormat === null ||
                trim((string) $requestedFormat) === ''
            ) {
                throw ValidationException::withMessages([
                    'format' => 'Kies het formaat waarnaar je wilt converteren.',
                ]);
            }

            return $this->images->normalizeFormat(
                (string) $requestedFormat
            );
        }

        return $this->images->normalizeFormat(
            $sourceFormat
        );
    }

    private function createVersionRecord(
        Image $image,
        int $userId,
        string $fileName,
        string $path,
        string $format,
        int $width,
        int $height,
        int $quality,
        int $fileSize,
        string $operation
    ): ImageVersion {
        try {
            return DB::transaction(
                function () use (
                    $image,
                    $userId,
                    $fileName,
                    $path,
                    $format,
                    $width,
                    $height,
                    $quality,
                    $fileSize,
                    $operation
                ): ImageVersion {
                    return ImageVersion::create([
                        'image_id' => $image->id,
                        'user_id' => $userId,
                        'file_name' => $fileName,
                        'path' => $path,
                        'format' => $format,
                        'width' => $width,
                        'height' => $height,
                        'quality' => $quality,
                        'file_size' => $fileSize,
                        'operation' => $operation,
                    ]);
                }
            );
        } catch (Throwable $exception) {
            $disk =
                $this->localDisk();

            if ($disk->exists($path)) {
                try {
                    $disk->delete($path);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }
    }

    private function successfulProcessRedirect(
        Image $image,
        ImageVersion $version
    ): RedirectResponse {
        return redirect()
            ->route(
                'images.editor',
                $image
            )
            ->with(
                'status',
                sprintf(
                    '%s voltooid. Versie #%d is opgeslagen als %d × %d %s.',
                    $this->operationLabel(
                        (string) $version->operation
                    ),
                    $version->id,
                    (int) $version->width,
                    (int) $version->height,
                    strtoupper(
                        (string) $version->format
                    )
                )
            );
    }

    private function versionDirectory(
        int $userId,
        int $imageId
    ): string {
        return sprintf(
            'users/%d/images/versions/%d',
            $userId,
            $imageId
        );
    }

    private function makeVersionFileName(
        Image $image,
        string $operation,
        string $format
    ): string {
        $base =
            pathinfo(
                (string) $image->original_name,
                PATHINFO_FILENAME
            );

        $base =
            Str::slug(
                $base
            );

        if ($base === '') {
            $base =
                'image';
        }

        return sprintf(
            '%s-%s-%s.%s',
            $base,
            Str::slug(
                $operation
            ),
            Str::uuid()->toString(),
            $this->images->extensionForFormat(
                $format
            )
        );
    }

    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredInteger(
        array $data,
        string $key,
        string $message
    ): int {
        if (
            ! array_key_exists(
                $key,
                $data
            ) ||
            $data[$key] === null ||
            $data[$key] === ''
        ) {
            throw ValidationException::withMessages([
                $key => $message,
            ]);
        }

        return (int) $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredString(
        array $data,
        string $key,
        string $message
    ): string {
        $value =
            trim(
                (string) (
                    $data[$key] ??
                    ''
                )
            );

        if ($value === '') {
            throw ValidationException::withMessages([
                $key => $message,
            ]);
        }

        return $value;
    }

    private function authorizeOwner(
        Request $request,
        Image $image
    ): void {
        $user =
            $request->user();

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

        $user =
            $request->user();

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
        $disk =
            Storage::disk(
                'local'
            );

        return $disk;
    }

    private function safeDownloadName(
        ?string $name
    ): string {
        $name =
            str_replace(
                '\\',
                '/',
                (string) $name
            );

        $name =
            basename(
                $name
            );

        $name =
            preg_replace(
                '/[^\pL\pN._ -]+/u',
                '-',
                $name
            );

        $name =
            trim(
                (string) $name,
                " .-\t\n\r\0\x0B"
            );

        if ($name === '') {
            return 'mashal-image';
        }

        if (mb_strlen($name) > 220) {
            $extension =
                pathinfo(
                    $name,
                    PATHINFO_EXTENSION
                );

            $baseName =
                mb_substr(
                    pathinfo(
                        $name,
                        PATHINFO_FILENAME
                    ),
                    0,
                    180
                );

            return $extension !== ''
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
            'enhance' => 'Fotoverbetering',
            'passport' => 'Pasfoto / ID-foto',
            'compress' => 'Compressie',
            'convert' => 'Conversie',
            'remove_background' => 'Achtergrond verwijderd',
            'background_color' => 'Nieuwe achtergrondkleur',
            'background_image' => 'Nieuwe achtergrondafbeelding',
            default => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $operation
                )
            ),
        };
    }
}
