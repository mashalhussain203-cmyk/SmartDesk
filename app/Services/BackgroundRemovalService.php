<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class BackgroundRemovalService
{
    private const ALLOWED_INPUT_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    private const ALLOWED_FORMATS = [
        'png',
        'webp',
        'jpg',
    ];

    private const TRANSPARENT_FORMATS = [
        'png',
        'webp',
    ];

    public function isConfigured(): bool
    {
        return trim(
            (string) config(
                'background-removal.remove_bg.api_key',
                ''
            )
        ) !== '';
    }

    /**
     * Verwijder de achtergrond van een bestand op een Laravel filesystem-disk
     * en sla het resultaat opnieuw op.
     *
     * Standaard wordt PNG gebruikt zodat transparantie behouden blijft.
     *
     * @param array<string, mixed> $options
     *
     * @return array{
     *     disk:string,
     *     path:string,
     *     format:string,
     *     mime:string,
     *     size:int,
     *     width:int|null,
     *     height:int|null,
     *     foreground_type:string|null,
     *     credits_charged:float|null
     * }
     */
    public function removeAndStore(
        string $sourceDisk,
        string $sourcePath,
        string $destinationDisk,
        string $destinationPath,
        array $options = []
    ): array {
        $sourceDisk = trim($sourceDisk);
        $sourcePath = ltrim(
            trim($sourcePath),
            '/'
        );

        $destinationDisk = trim(
            $destinationDisk
        );

        $destinationPath = ltrim(
            trim($destinationPath),
            '/'
        );

        if (
            $sourceDisk === '' ||
            $sourcePath === '' ||
            $destinationDisk === '' ||
            $destinationPath === ''
        ) {
            throw ValidationException::withMessages([
                'image' => 'Bron- en doelpad zijn verplicht voor achtergrondverwijdering.',
            ]);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(
            $sourceDisk
        );

        if (! $disk->exists($sourcePath)) {
            throw ValidationException::withMessages([
                'image' => 'De bronafbeelding bestaat niet meer.',
            ]);
        }

        $size = (int) $disk->size(
            $sourcePath
        );

        $this->assertInputSize(
            $size
        );

        $stream = $disk->readStream(
            $sourcePath
        );

        if ($stream === false) {
            throw new RuntimeException(
                'De bronafbeelding kon niet als stream worden geopend.'
            );
        }

        try {
            $filename = basename(
                $sourcePath
            );

            $mime = $disk->mimeType(
                $sourcePath
            );

            $result = $this->removeStream(
                $stream,
                $filename,
                is_string($mime)
                    ? $mime
                    : null,
                $options
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $destination */
        $destination =
            Storage::disk(
                $destinationDisk
            );

        $written = $destination->put(
            $destinationPath,
            $result['contents']
        );

        if (! $written) {
            throw new RuntimeException(
                'De afbeelding zonder achtergrond kon niet worden opgeslagen.'
            );
        }

        $storedSize = (int) $destination->size(
            $destinationPath
        );

        return [
            'disk' => $destinationDisk,
            'path' => $destinationPath,
            'format' => $result['format'],
            'mime' => $result['mime'],
            'size' => $storedSize,
            'width' => $result['width'],
            'height' => $result['height'],
            'foreground_type' => $result['foreground_type'],
            'credits_charged' => $result['credits_charged'],
        ];
    }

    /**
     * Achtergrond verwijderen en het binaire resultaat teruggeven.
     *
     * @param resource $stream
     * @param array<string, mixed> $options
     *
     * @return array{
     *     contents:string,
     *     format:string,
     *     mime:string,
     *     width:int|null,
     *     height:int|null,
     *     foreground_type:string|null,
     *     credits_charged:float|null
     * }
     */
    public function removeStream(
        mixed $stream,
        string $filename,
        ?string $mime = null,
        array $options = []
    ): array {
        $this->assertConfigured();

        if (! is_resource($stream)) {
            throw ValidationException::withMessages([
                'image' => 'De bronafbeelding kon niet worden gelezen.',
            ]);
        }

        $filename = trim(
            $filename
        );

        if ($filename === '') {
            $filename = 'image.jpg';
        }

        if ($mime !== null) {
            $this->assertInputMime(
                $mime
            );
        }

        $requestOptions =
            $this->normalizeOptions(
                $options
            );

        $request = Http::withHeaders([
            'X-Api-Key' => $this->apiKey(),
            'Accept' => $this->acceptHeader(
                $requestOptions['format']
            ),
        ])
            ->connectTimeout(
                $this->connectTimeout()
            )
            ->timeout(
                $this->timeout()
            )
            ->attach(
                'image_file',
                $stream,
                $filename,
                $mime
                    ? ['Content-Type' => $mime]
                    : []
            );

        $backgroundStream = null;

        try {
            if (
                isset(
                    $requestOptions['bg_image_file_path']
                )
            ) {
                $backgroundPath =
                    (string) $requestOptions[
                        'bg_image_file_path'
                    ];

                $this->assertLocalBackgroundFile(
                    $backgroundPath
                );

                $backgroundStream = fopen(
                    $backgroundPath,
                    'rb'
                );

                if ($backgroundStream === false) {
                    throw ValidationException::withMessages([
                        'background_image' => 'De gekozen achtergrondafbeelding kon niet worden geopend.',
                    ]);
                }

                $request = $request->attach(
                    'bg_image_file',
                    $backgroundStream,
                    basename($backgroundPath)
                );

                unset(
                    $requestOptions[
                        'bg_image_file_path'
                    ]
                );
            }

            $response = $request->post(
                $this->endpoint(),
                $requestOptions
            );
        } catch (ConnectionException $exception) {
            throw ValidationException::withMessages([
                'image' => 'De achtergrondservice is tijdelijk niet bereikbaar. Probeer het zo opnieuw.',
            ]);
        } finally {
            if (is_resource($backgroundStream)) {
                fclose($backgroundStream);
            }
        }

        $this->throwForFailedResponse(
            $response
        );

        $contents =
            $response->body();

        if ($contents === '') {
            throw new RuntimeException(
                'De achtergrondservice gaf een leeg resultaat terug.'
            );
        }

        $format =
            $this->formatFromResponse(
                $response,
                $requestOptions['format']
            );

        $mime =
            $this->mimeForFormat(
                $format
            );

        [
            $width,
            $height,
        ] = $this->detectDimensions(
            $contents
        );

        return [
            'contents' => $contents,
            'format' => $format,
            'mime' => $mime,
            'width' => $width,
            'height' => $height,
            'foreground_type' => $this->headerString(
                $response,
                'X-Type'
            ),
            'credits_charged' => $this->headerFloat(
                $response,
                'X-Credits-Charged'
            ),
        ];
    }

    /**
     * Echte transparante uitsnede.
     *
     * @return array<string, mixed>
     */
    public function transparent(
        string $sourceDisk,
        string $sourcePath,
        string $destinationDisk,
        string $destinationPath,
        array $options = []
    ): array {
        $options['format'] =
            $options['format'] ?? 'png';

        $format =
            $this->normalizeFormat(
                (string) $options['format']
            );

        if (
            ! in_array(
                $format,
                self::TRANSPARENT_FORMATS,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'format' => 'Gebruik PNG of WebP wanneer je een transparante achtergrond wilt.',
            ]);
        }

        $options['format'] =
            $format;

        unset(
            $options['bg_color'],
            $options['bg_image_url'],
            $options['bg_image_file_path']
        );

        return $this->removeAndStore(
            $sourceDisk,
            $sourcePath,
            $destinationDisk,
            $destinationPath,
            $options
        );
    }

    /**
     * Verwijder de bestaande achtergrond en plaats direct een egale kleur.
     *
     * Voorbeelden:
     * - #ffffff
     * - fff
     * - 81d4fa
     * - white
     */
    public function withColor(
        string $sourceDisk,
        string $sourcePath,
        string $destinationDisk,
        string $destinationPath,
        string $color,
        array $options = []
    ): array {
        $color =
            $this->normalizeBackgroundColor(
                $color
            );

        $options['bg_color'] =
            $color;

        /*
         * JPG is efficiënt voor een volledig dekkende achtergrond.
         * De caller kan expliciet PNG/WebP kiezen.
         */
        $options['format'] =
            $options['format'] ?? 'jpg';

        unset(
            $options['bg_image_url'],
            $options['bg_image_file_path']
        );

        return $this->removeAndStore(
            $sourceDisk,
            $sourcePath,
            $destinationDisk,
            $destinationPath,
            $options
        );
    }

    /**
     * Verwijder de achtergrond en plaats een nieuwe achtergrond vanaf URL.
     */
    public function withBackgroundUrl(
        string $sourceDisk,
        string $sourcePath,
        string $destinationDisk,
        string $destinationPath,
        string $backgroundUrl,
        array $options = []
    ): array {
        $backgroundUrl = trim(
            $backgroundUrl
        );

        if (
            $backgroundUrl === '' ||
            filter_var(
                $backgroundUrl,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'Vul een geldige achtergrond-URL in.',
            ]);
        }

        if (
            ! in_array(
                strtolower(
                    (string) parse_url(
                        $backgroundUrl,
                        PHP_URL_SCHEME
                    )
                ),
                ['http', 'https'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'Alleen HTTP- en HTTPS-achtergrondafbeeldingen zijn toegestaan.',
            ]);
        }

        $options['bg_image_url'] =
            $backgroundUrl;

        $options['format'] =
            $options['format'] ?? 'jpg';

        unset(
            $options['bg_color'],
            $options['bg_image_file_path']
        );

        return $this->removeAndStore(
            $sourceDisk,
            $sourcePath,
            $destinationDisk,
            $destinationPath,
            $options
        );
    }

    /**
     * Verwijder de achtergrond en plaats een lokale afbeelding als nieuwe
     * achtergrond. $backgroundAbsolutePath moet een absoluut pad zijn.
     */
    public function withBackgroundFile(
        string $sourceDisk,
        string $sourcePath,
        string $destinationDisk,
        string $destinationPath,
        string $backgroundAbsolutePath,
        array $options = []
    ): array {
        $this->assertLocalBackgroundFile(
            $backgroundAbsolutePath
        );

        $options[
            'bg_image_file_path'
        ] = $backgroundAbsolutePath;

        $options['format'] =
            $options['format'] ?? 'jpg';

        unset(
            $options['bg_color'],
            $options['bg_image_url']
        );

        return $this->removeAndStore(
            $sourceDisk,
            $sourcePath,
            $destinationDisk,
            $destinationPath,
            $options
        );
    }

    /**
     * Alleen voor het ophalen van transparante bytes zonder opslag.
     *
     * @param array<string, mixed> $options
     */
    public function transparentBytesFromStorage(
        string $disk,
        string $path,
        array $options = []
    ): string {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage =
            Storage::disk(
                $disk
            );

        if (! $storage->exists($path)) {
            throw ValidationException::withMessages([
                'image' => 'De bronafbeelding bestaat niet meer.',
            ]);
        }

        $size = (int) $storage->size(
            $path
        );

        $this->assertInputSize(
            $size
        );

        $stream =
            $storage->readStream(
                $path
            );

        if ($stream === false) {
            throw new RuntimeException(
                'De bronafbeelding kon niet worden geopend.'
            );
        }

        try {
            $options['format'] =
                $options['format'] ?? 'png';

            unset(
                $options['bg_color'],
                $options['bg_image_url'],
                $options['bg_image_file_path']
            );

            $result =
                $this->removeStream(
                    $stream,
                    basename($path),
                    $storage->mimeType($path) ?: null,
                    $options
                );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $result['contents'];
    }

    /**
     * Controleer of de service correct is ingesteld.
     */
    public function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'image' => 'Background removal is nog niet geconfigureerd. Voeg REMOVE_BG_API_KEY toe aan de omgeving.',
            ]);
        }

        if ($this->endpoint() === '') {
            throw new RuntimeException(
                'De background-removal endpoint ontbreekt.'
            );
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function normalizeOptions(
        array $options
    ): array {
        $format =
            $this->normalizeFormat(
                (string) (
                    $options['format'] ??
                    config(
                        'background-removal.remove_bg.default_format',
                        'png'
                    )
                )
            );

        $size = strtolower(
            trim(
                (string) (
                    $options['size'] ??
                    config(
                        'background-removal.remove_bg.default_size',
                        'auto'
                    )
                )
            )
        );

        $allowedSizes = [
            'auto',
            'preview',
            'full',
            '50mp',
        ];

        if (
            ! in_array(
                $size,
                $allowedSizes,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'size' => 'Kies een geldige achtergrond-removal resolutie.',
            ]);
        }

        $normalized = [
            'size' => $size,
            'format' => $format,
        ];

        if (
            array_key_exists(
                'crop',
                $options
            )
        ) {
            $normalized['crop'] =
                $this->booleanString(
                    $options['crop']
                );
        }

        if (
            isset(
                $options['crop_margin']
            ) &&
            trim(
                (string) $options[
                    'crop_margin'
                ]
            ) !== ''
        ) {
            $normalized['crop_margin'] =
                trim(
                    (string) $options[
                        'crop_margin'
                    ]
                );
        }

        if (
            isset($options['scale']) &&
            trim(
                (string) $options['scale']
            ) !== ''
        ) {
            $normalized['scale'] =
                trim(
                    (string) $options['scale']
                );
        }

        if (
            isset(
                $options['position']
            ) &&
            trim(
                (string) $options[
                    'position'
                ]
            ) !== ''
        ) {
            $normalized['position'] =
                trim(
                    (string) $options[
                        'position'
                    ]
                );
        }

        if (
            isset($options['roi']) &&
            trim(
                (string) $options['roi']
            ) !== ''
        ) {
            $normalized['roi'] =
                trim(
                    (string) $options['roi']
                );
        }

        if (
            isset(
                $options['channels']
            ) &&
            trim(
                (string) $options[
                    'channels'
                ]
            ) !== ''
        ) {
            $channels = strtolower(
                trim(
                    (string) $options[
                        'channels'
                    ]
                )
            );

            if (
                ! in_array(
                    $channels,
                    ['rgba', 'alpha'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'channels' => 'Channels moet rgba of alpha zijn.',
                ]);
            }

            $normalized['channels'] =
                $channels;
        }

        if (
            array_key_exists(
                'semitransparency',
                $options
            )
        ) {
            $normalized[
                'semitransparency'
            ] = $this->booleanString(
                $options[
                    'semitransparency'
                ]
            );
        }

        $backgroundCount = 0;

        if (
            isset($options['bg_color']) &&
            trim(
                (string) $options[
                    'bg_color'
                ]
            ) !== ''
        ) {
            $normalized['bg_color'] =
                $this->normalizeBackgroundColor(
                    (string) $options[
                        'bg_color'
                    ]
                );

            $backgroundCount++;
        }

        if (
            isset(
                $options['bg_image_url']
            ) &&
            trim(
                (string) $options[
                    'bg_image_url'
                ]
            ) !== ''
        ) {
            $normalized['bg_image_url'] =
                trim(
                    (string) $options[
                        'bg_image_url'
                    ]
                );

            $backgroundCount++;
        }

        if (
            isset(
                $options[
                    'bg_image_file_path'
                ]
            ) &&
            trim(
                (string) $options[
                    'bg_image_file_path'
                ]
            ) !== ''
        ) {
            $normalized[
                'bg_image_file_path'
            ] = trim(
                (string) $options[
                    'bg_image_file_path'
                ]
            );

            $backgroundCount++;
        }

        if ($backgroundCount > 1) {
            throw ValidationException::withMessages([
                'background_image' => 'Kies één achtergrond: kleur, URL of geüploade achtergrond.',
            ]);
        }

        return $normalized;
    }

    private function apiKey(): string
    {
        return trim(
            (string) config(
                'background-removal.remove_bg.api_key',
                ''
            )
        );
    }

    private function endpoint(): string
    {
        return rtrim(
            trim(
                (string) config(
                    'background-removal.remove_bg.endpoint',
                    'https://api.remove.bg/v1.0/removebg'
                )
            ),
            '/'
        );
    }

    private function timeout(): int
    {
        return max(
            5,
            (int) config(
                'background-removal.remove_bg.timeout',
                90
            )
        );
    }

    private function connectTimeout(): int
    {
        return max(
            2,
            (int) config(
                'background-removal.remove_bg.connect_timeout',
                10
            )
        );
    }

    private function maxInputBytes(): int
    {
        return max(
            1024,
            (int) config(
                'background-removal.remove_bg.max_input_bytes',
                23068672
            )
        );
    }

    private function assertInputSize(
        int $bytes
    ): void {
        if ($bytes < 1) {
            throw ValidationException::withMessages([
                'image' => 'De afbeelding is leeg.',
            ]);
        }

        if ($bytes > $this->maxInputBytes()) {
            throw ValidationException::withMessages([
                'image' => sprintf(
                    'De afbeelding is te groot voor background removal. Maximaal %.1f MB.',
                    $this->maxInputBytes() /
                    1024 /
                    1024
                ),
            ]);
        }
    }

    private function assertInputMime(
        string $mime
    ): void {
        $mime = strtolower(
            trim($mime)
        );

        if (
            ! in_array(
                $mime,
                self::ALLOWED_INPUT_MIMES,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'image' => 'Background removal ondersteunt hier alleen JPG, PNG en WebP.',
            ]);
        }
    }

    private function assertLocalBackgroundFile(
        string $absolutePath
    ): void {
        $absolutePath = trim(
            $absolutePath
        );

        if (
            $absolutePath === '' ||
            ! is_file($absolutePath)
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'De nieuwe achtergrondafbeelding bestaat niet.',
            ]);
        }

        $size = @filesize(
            $absolutePath
        );

        if (
            $size === false ||
            $size < 1
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'De nieuwe achtergrondafbeelding is leeg of onleesbaar.',
            ]);
        }

        if (
            $size >
            $this->maxInputBytes()
        ) {
            throw ValidationException::withMessages([
                'background_image' => 'De nieuwe achtergrondafbeelding is te groot.',
            ]);
        }
    }

    private function normalizeFormat(
        string $format
    ): string {
        $format = strtolower(
            trim($format)
        );

        if ($format === 'jpeg') {
            $format = 'jpg';
        }

        if (
            ! in_array(
                $format,
                self::ALLOWED_FORMATS,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'format' => 'Gebruik PNG, WebP of JPG voor background removal.',
            ]);
        }

        return $format;
    }

    private function normalizeBackgroundColor(
        string $color
    ): string {
        $color = trim(
            $color
        );

        if ($color === '') {
            throw ValidationException::withMessages([
                'background_color' => 'Kies een achtergrondkleur.',
            ]);
        }

        if (
            preg_match(
                '/^#[0-9a-fA-F]{3,8}$/',
                $color
            )
        ) {
            return ltrim(
                $color,
                '#'
            );
        }

        if (
            preg_match(
                '/^[0-9a-fA-F]{3,8}$/',
                $color
            )
        ) {
            return $color;
        }

        if (
            preg_match(
                '/^[a-zA-Z]{3,32}$/',
                $color
            )
        ) {
            return strtolower(
                $color
            );
        }

        throw ValidationException::withMessages([
            'background_color' => 'Gebruik een geldige hexkleur of kleurnaam.',
        ]);
    }

    private function acceptHeader(
        string $format
    ): string {
        return match ($format) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'jpg' => 'image/jpeg',
            default => '*/*',
        };
    }

    private function mimeForFormat(
        string $format
    ): string {
        return match ($format) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'jpg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }

    private function formatFromResponse(
        Response $response,
        string $fallback
    ): string {
        $contentTypeHeader =
            $this->headerString(
                $response,
                'Content-Type'
            ) ?? '';

        $contentType = strtolower(
            trim(
                explode(
                    ';',
                    $contentTypeHeader
                )[0]
            )
        );

        return match ($contentType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/jpeg',
            'image/jpg' => 'jpg',
            default => $fallback,
        };
    }

    /**
     * @return array{0:int|null,1:int|null}
     */
    private function detectDimensions(
        string $contents
    ): array {
        if (! function_exists('getimagesizefromstring')) {
            return [
                null,
                null,
            ];
        }

        $size = @getimagesizefromstring(
            $contents
        );

        if (
            ! is_array($size) ||
            ! isset(
                $size[0],
                $size[1]
            )
        ) {
            return [
                null,
                null,
            ];
        }

        return [
            (int) $size[0],
            (int) $size[1],
        ];
    }

    private function booleanString(
        mixed $value
    ): string {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        )
            ? 'true'
            : 'false';
    }

    private function throwForFailedResponse(
        Response $response
    ): void {
        if ($response->successful()) {
            return;
        }

        $status =
            $response->status();

        $message =
            $this->extractProviderError(
                $response
            );

        if ($status === 401 || $status === 403) {
            throw ValidationException::withMessages([
                'image' => 'Background removal is niet geautoriseerd. Controleer de REMOVE_BG_API_KEY.',
            ]);
        }

        if ($status === 402) {
            throw ValidationException::withMessages([
                'image' => 'Het background-removal tegoed is op. Controleer je provider-account.',
            ]);
        }

        if ($status === 429) {
            throw ValidationException::withMessages([
                'image' => 'De background-removal service is momenteel te druk of de limiet is bereikt. Probeer later opnieuw.',
            ]);
        }

        if ($status === 400 || $status === 422) {
            throw ValidationException::withMessages([
                'image' => $message !== ''
                    ? $message
                    : 'De afbeelding kon niet door de background-removal service worden verwerkt.',
            ]);
        }

        throw new RuntimeException(
            $message !== ''
                ? sprintf(
                    'Background removal mislukt (%d): %s',
                    $status,
                    $message
                )
                : sprintf(
                    'Background removal mislukt met HTTP-status %d.',
                    $status
                )
        );
    }

    private function extractProviderError(
        Response $response
    ): string {
        try {
            $json =
                $response->json();

            if (
                is_array($json) &&
                isset($json['errors']) &&
                is_array(
                    $json['errors']
                )
            ) {
                $messages = [];

                foreach (
                    $json['errors']
                    as $error
                ) {
                    if (! is_array($error)) {
                        continue;
                    }

                    $title = trim(
                        (string) (
                            $error['title'] ??
                            ''
                        )
                    );

                    $detail = trim(
                        (string) (
                            $error['detail'] ??
                            ''
                        )
                    );

                    $text = trim(
                        $title .
                        (
                            $detail !== ''
                                ? ': ' . $detail
                                : ''
                        )
                    );

                    if ($text !== '') {
                        $messages[] =
                            $text;
                    }
                }

                if ($messages !== []) {
                    return implode(
                        ' · ',
                        array_slice(
                            $messages,
                            0,
                            3
                        )
                    );
                }
            }
        } catch (Throwable) {
            // Provider gaf geen bruikbare JSON terug.
        }

        $body = trim(
            $response->body()
        );

        $contentType =
            strtolower(
                $this->headerString(
                    $response,
                    'Content-Type'
                ) ?? ''
            );

        if (
            $body === '' ||
            str_starts_with(
                $contentType,
                'image/'
            )
        ) {
            return '';
        }

        return mb_substr(
            strip_tags($body),
            0,
            500
        );
    }

    private function headerString(
        Response $response,
        string $name
    ): ?string {
        $header = $response->header(
            $name
        );

        if (
            $header === null ||
            is_array($header)
        ) {
            return null;
        }

        $value = trim(
            (string) $header
        );

        return $value !== ''
            ? $value
            : null;
    }

    private function headerFloat(
        Response $response,
        string $name
    ): ?float {
        $value =
            $this->headerString(
                $response,
                $name
            );

        if (
            $value === null ||
            ! is_numeric($value)
        ) {
            return null;
        }

        return (float) $value;
    }
}
