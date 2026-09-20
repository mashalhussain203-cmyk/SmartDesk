<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImageUploadController extends Controller
{
    /**
     * Maximale uploadgrootte in kilobytes.
     *
     * 20480 KB = 20 MB.
     */
    private const MAX_UPLOAD_KB = 20480;

    /**
     * Maximale breedte of hoogte.
     */
    private const MAX_DIMENSION = 20000;

    /**
     * Maximale hoeveelheid pixels.
     */
    private const MAX_PIXELS = 100000000;

    /**
     * Hoelang een tijdelijke upload geldig blijft.
     *
     * 2 uur is ruim genoeg voor:
     * upload -> login/registratie -> claim.
     */
    private const PENDING_TTL_SECONDS = 7200;

    /**
     * Sessiekey voor een nog niet geclaimde upload.
     */
    private const PENDING_SESSION_KEY = 'pending_image';

    /**
     * Tijdelijke uploadmap op de local disk.
     */
    private const TEMP_DIRECTORY = 'temp-images';

    /**
     * Ondersteunde MIME-types.
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Upload een afbeelding voordat of nadat de gebruiker is ingelogd.
     *
     * Flow:
     *
     * guest:
     * upload
     * -> tijdelijk bestand
     * -> pending_image in sessie
     * -> login
     * -> images.claim
     * -> editor
     *
     * authenticated:
     * upload
     * -> tijdelijk bestand
     * -> pending_image in sessie
     * -> images.claim
     * -> editor
     */
    public function storeTemporary(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'image' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:' . self::MAX_UPLOAD_KB,
                ],
            ],
            [
                'image.required' => 'Kies eerst een afbeelding.',
                'image.file' => 'Het gekozen bestand is ongeldig.',
                'image.image' => 'Het bestand moet een geldige afbeelding zijn.',
                'image.mimes' => 'Alleen JPG, JPEG, PNG en WEBP zijn toegestaan.',
                'image.max' => 'De afbeelding mag maximaal 20 MB groot zijn.',
            ]
        );

        /** @var UploadedFile $file */
        $file = $validated['image'];

        try {
            $metadata = $this->inspectUploadedImage($file);
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | Oude pending upload opruimen
        |--------------------------------------------------------------------------
        |
        | Per browser/sessie bewaren we bewust maar één nog niet geclaimde
        | afbeelding. Uploadt iemand opnieuw, dan vervangt die nieuwe upload
        | de vorige pending upload.
        |
        */

        $this->deletePreviousPendingUpload($request);

        /*
        |--------------------------------------------------------------------------
        | Tijdelijk bestand opslaan
        |--------------------------------------------------------------------------
        */

        try {
            $temporaryPath = $file->store(
                self::TEMP_DIRECTORY,
                'local'
            );
        } catch (Throwable $exception) {
            Log::warning(
                'Temporary image upload failed.',
                [
                    'exception' => $exception->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'De afbeelding kon niet tijdelijk worden opgeslagen.'
                );
        }

        if (! is_string($temporaryPath) || $temporaryPath === '') {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'De afbeelding kon niet tijdelijk worden opgeslagen.'
                );
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($temporaryPath)) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'De tijdelijke upload kon niet worden teruggevonden.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Pending upload in sessie bewaren
        |--------------------------------------------------------------------------
        */

        $pending = [
            'path' => $temporaryPath,
            'original_name' => $this->sanitizeOriginalName(
                $file->getClientOriginalName()
            ),
            'mime_type' => $metadata['mime_type'],
            'file_size' => $metadata['file_size'],
            'width' => $metadata['width'],
            'height' => $metadata['height'],
            'uploaded_at' => now()->timestamp,
            'expires_at' => now()
                ->addSeconds(self::PENDING_TTL_SECONDS)
                ->timestamp,
        ];

        $request->session()->put(
            self::PENDING_SESSION_KEY,
            $pending
        );

        /*
        |--------------------------------------------------------------------------
        | Belangrijk: intended URL altijd naar claim laten wijzen
        |--------------------------------------------------------------------------
        |
        | Dit is de kern van de gewenste gebruikersflow.
        |
        | Ook als een logincontroller Laravel's redirect()->intended() gebruikt,
        | komt de bezoeker na succesvolle login automatisch bij images.claim.
        |
        */

        $request->session()->put(
            'url.intended',
            route('images.claim')
        );

        /*
        |--------------------------------------------------------------------------
        | Gast -> login
        |--------------------------------------------------------------------------
        */

        if (! Auth::check()) {
            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Je afbeelding staat klaar. Log in of maak een account aan om direct verder te gaan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Ingelogd -> direct claimen
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('images.claim');
    }

    /**
     * Koppel een tijdelijke upload aan de ingelogde gebruiker.
     */
    public function claim(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            /*
             * De route hoort achter auth middleware te staan.
             * Deze fallback houdt de flow veilig wanneer routes ooit wijzigen.
             */
            $request->session()->put(
                'url.intended',
                route('images.claim')
            );

            return redirect()
                ->route('login');
        }

        $pending = $request->session()->get(
            self::PENDING_SESSION_KEY
        );

        if (! is_array($pending)) {
            return $this->redirectWithoutPendingImage($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Pending upload controleren
        |--------------------------------------------------------------------------
        */

        $temporaryPath = isset($pending['path'])
            ? trim((string) $pending['path'])
            : '';

        if ($temporaryPath === '') {
            $this->forgetPendingUpload($request);

            return $this->redirectWithoutPendingImage($request);
        }

        if (! $this->isAllowedTemporaryPath($temporaryPath)) {
            $this->forgetPendingUpload($request);

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De tijdelijke afbeelding is ongeldig. Upload de afbeelding opnieuw.'
                );
        }

        if ($this->pendingUploadHasExpired($pending)) {
            $this->deletePendingFile($temporaryPath);
            $this->forgetPendingUpload($request);

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De tijdelijke upload is verlopen. Upload de afbeelding opnieuw.'
                );
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($temporaryPath)) {
            $this->forgetPendingUpload($request);

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De tijdelijke afbeelding bestaat niet meer. Upload de afbeelding opnieuw.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Bestand opnieuw valideren
        |--------------------------------------------------------------------------
        |
        | We vertrouwen niet uitsluitend op de sessiemetadata.
        |
        */

        try {
            $absolutePath = $disk->path($temporaryPath);

            $verified = $this->inspectStoredImage($absolutePath);

            $extension = $this->extensionForMime(
                $verified['mime_type']
            );
        } catch (Throwable $exception) {
            $this->deletePendingFile($temporaryPath);
            $this->forgetPendingUpload($request);

            Log::warning(
                'Pending image validation failed during claim.',
                [
                    'user_id' => $user->id,
                    'path' => $temporaryPath,
                    'exception' => $exception->getMessage(),
                ]
            );

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De tijdelijke afbeelding is ongeldig of beschadigd. Upload een nieuwe afbeelding.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Definitieve opslaglocatie
        |--------------------------------------------------------------------------
        */

        $fileName = Str::uuid()->toString()
            . '.'
            . $extension;

        $finalDirectory = sprintf(
            'users/%d/images/originals',
            $user->id
        );

        $finalPath = $finalDirectory
            . '/'
            . $fileName;

        try {
            if (! $disk->exists($finalDirectory)) {
                $created = $disk->makeDirectory(
                    $finalDirectory
                );

                if ($created === false) {
                    throw new RuntimeException(
                        'De persoonlijke opslagmap kon niet worden aangemaakt.'
                    );
                }
            }

            $moved = $disk->move(
                $temporaryPath,
                $finalPath
            );

            if (
                $moved === false ||
                ! $disk->exists($finalPath)
            ) {
                throw new RuntimeException(
                    'Het bestand kon niet definitief worden opgeslagen.'
                );
            }
        } catch (Throwable $exception) {
            Log::error(
                'Claimed image could not be moved to permanent storage.',
                [
                    'user_id' => $user->id,
                    'temporary_path' => $temporaryPath,
                    'final_path' => $finalPath,
                    'exception' => $exception->getMessage(),
                ]
            );

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De afbeelding kon niet naar je persoonlijke opslag worden verplaatst.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Database-record aanmaken
        |--------------------------------------------------------------------------
        */

        $originalName = $this->sanitizeOriginalName(
            $pending['original_name']
                ?? 'afbeelding.' . $extension
        );

        try {
            $image = Image::create([
                'user_id' => $user->id,
                'original_name' => $originalName,
                'original_path' => $finalPath,
                'mime_type' => $verified['mime_type'],
                'width' => $verified['width'],
                'height' => $verified['height'],
                'file_size' => $verified['file_size'],
            ]);
        } catch (Throwable $exception) {
            /*
             * Geen database-record betekent ook geen permanent bestand.
             */
            if ($disk->exists($finalPath)) {
                $disk->delete($finalPath);
            }

            Log::error(
                'Image database record could not be created after claim.',
                [
                    'user_id' => $user->id,
                    'final_path' => $finalPath,
                    'exception' => $exception->getMessage(),
                ]
            );

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De afbeelding kon niet aan je account worden gekoppeld. Probeer opnieuw.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Pending state volledig opruimen
        |--------------------------------------------------------------------------
        */

        $this->forgetPendingUpload($request);

        /*
        |--------------------------------------------------------------------------
        | Direct naar editor
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'images.editor',
                $image
            )
            ->with(
                'status',
                'Je upload is aan je account gekoppeld en staat klaar om te bewerken.'
            );
    }

    /**
     * Inspecteer een Laravel UploadedFile.
     *
     * @return array{
     *     mime_type: string,
     *     width: int,
     *     height: int,
     *     file_size: int
     * }
     */
    private function inspectUploadedImage(
        UploadedFile $file
    ): array {
        if (! $file->isValid()) {
            throw new RuntimeException(
                'De upload is niet geldig.'
            );
        }

        $realPath = $file->getRealPath();

        if (
            ! is_string($realPath) ||
            $realPath === ''
        ) {
            throw new RuntimeException(
                'Het uploadbestand kon niet worden gelezen.'
            );
        }

        return $this->inspectStoredImage(
            $realPath
        );
    }

    /**
     * Inspecteer een afbeeldingsbestand inhoudelijk.
     *
     * @return array{
     *     mime_type: string,
     *     width: int,
     *     height: int,
     *     file_size: int
     * }
     */
    private function inspectStoredImage(
        string $absolutePath
    ): array {
        if (
            $absolutePath === '' ||
            ! is_file($absolutePath)
        ) {
            throw new RuntimeException(
                'Het afbeeldingsbestand bestaat niet.'
            );
        }

        $dimensions = @getimagesize(
            $absolutePath
        );

        if ($dimensions === false) {
            throw new RuntimeException(
                'Het bestand is geen geldige afbeelding.'
            );
        }

        $width = isset($dimensions[0])
            ? (int) $dimensions[0]
            : 0;

        $height = isset($dimensions[1])
            ? (int) $dimensions[1]
            : 0;

        if (
            $width < 1 ||
            $height < 1
        ) {
            throw new RuntimeException(
                'De afbeelding heeft ongeldige afmetingen.'
            );
        }

        if (
            $width > self::MAX_DIMENSION ||
            $height > self::MAX_DIMENSION
        ) {
            throw new RuntimeException(
                'De afbeelding heeft te grote afmetingen.'
            );
        }

        /*
         * Integer overflow vermijden op ongebruikelijke platforms.
         */
        if (
            $height > 0 &&
            $width > intdiv(
                PHP_INT_MAX,
                $height
            )
        ) {
            throw new RuntimeException(
                'De afbeelding heeft ongeldige afmetingen.'
            );
        }

        $totalPixels = $width * $height;

        if ($totalPixels > self::MAX_PIXELS) {
            throw new RuntimeException(
                'De afbeelding bevat te veel pixels.'
            );
        }

        $mimeType = isset($dimensions['mime'])
            ? strtolower(
                trim(
                    (string) $dimensions['mime']
                )
            )
            : '';

        if (
            ! in_array(
                $mimeType,
                self::ALLOWED_MIME_TYPES,
                true
            )
        ) {
            throw new RuntimeException(
                'Dit afbeeldingstype wordt niet ondersteund.'
            );
        }

        $fileSize = filesize(
            $absolutePath
        );

        if ($fileSize === false) {
            throw new RuntimeException(
                'De bestandsgrootte kon niet worden bepaald.'
            );
        }

        $fileSize = (int) $fileSize;

        if ($fileSize < 1) {
            throw new RuntimeException(
                'Het afbeeldingsbestand is leeg.'
            );
        }

        if (
            $fileSize >
            (self::MAX_UPLOAD_KB * 1024)
        ) {
            throw new RuntimeException(
                'De afbeelding is groter dan 20 MB.'
            );
        }

        return [
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
            'file_size' => $fileSize,
        ];
    }

    /**
     * Bepaal de veilige bestandsextensie vanuit het gevalideerde MIME-type.
     */
    private function extensionForMime(
        string $mimeType
    ): string {
        return match (
            strtolower(
                trim($mimeType)
            )
        ) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',

            default => throw new RuntimeException(
                'Niet ondersteund afbeeldingstype.'
            ),
        };
    }

    /**
     * Maak een oorspronkelijke bestandsnaam veilig voor metadata.
     */
    private function sanitizeOriginalName(
        mixed $originalName
    ): string {
        $originalName = trim(
            (string) $originalName
        );

        if ($originalName === '') {
            return 'afbeelding';
        }

        /*
         * Windows separators normaliseren zodat basename altijd correct werkt.
         */
        $originalName = str_replace(
            '\\',
            '/',
            $originalName
        );

        $originalName = basename(
            $originalName
        );

        $cleaned = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $originalName
        );

        if (is_string($cleaned)) {
            $originalName = trim(
                $cleaned
            );
        }

        if ($originalName === '') {
            return 'afbeelding';
        }

        /*
         * Extreem lange namen inkorten zonder de extensie kwijt te raken.
         */
        if (mb_strlen($originalName) > 220) {
            $extension = pathinfo(
                $originalName,
                PATHINFO_EXTENSION
            );

            $baseName = pathinfo(
                $originalName,
                PATHINFO_FILENAME
            );

            $baseName = mb_substr(
                $baseName,
                0,
                180
            );

            $originalName = $extension !== ''
                ? $baseName . '.' . $extension
                : $baseName;
        }

        return $originalName !== ''
            ? $originalName
            : 'afbeelding';
    }

    /**
     * Controleer dat een pending pad echt in onze tijdelijke uploadmap zit.
     */
    private function isAllowedTemporaryPath(
        string $path
    ): bool {
        return Str::startsWith(
            $path,
            self::TEMP_DIRECTORY . '/'
        );
    }

    /**
     * Controleer of de tijdelijke upload te oud is.
     *
     * Oude sessies zonder expires_at blijven compatibel via uploaded_at.
     */
    private function pendingUploadHasExpired(
        array $pending
    ): bool {
        $expiresAt = isset($pending['expires_at'])
            ? (int) $pending['expires_at']
            : 0;

        if ($expiresAt > 0) {
            return now()->timestamp > $expiresAt;
        }

        $uploadedAt = isset($pending['uploaded_at'])
            ? (int) $pending['uploaded_at']
            : 0;

        if ($uploadedAt < 1) {
            /*
             * Oude sessies zonder timestamp niet onverwacht blokkeren.
             */
            return false;
        }

        return (
            now()->timestamp - $uploadedAt
        ) > self::PENDING_TTL_SECONDS;
    }

    /**
     * Verwijder een oud pending bestand en zijn sessiestatus.
     */
    private function deletePreviousPendingUpload(
        Request $request
    ): void {
        $previous = $request->session()->get(
            self::PENDING_SESSION_KEY
        );

        if (! is_array($previous)) {
            return;
        }

        $path = isset($previous['path'])
            ? trim((string) $previous['path'])
            : '';

        if (
            $path !== '' &&
            $this->isAllowedTemporaryPath($path)
        ) {
            $this->deletePendingFile($path);
        }

        $this->forgetPendingUpload($request);
    }

    /**
     * Verwijder uitsluitend een bestand binnen temp-images.
     */
    private function deletePendingFile(
        string $path
    ): void {
        if (! $this->isAllowedTemporaryPath($path)) {
            return;
        }

        try {
            $disk = Storage::disk('local');

            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        } catch (Throwable $exception) {
            Log::warning(
                'Pending image cleanup failed.',
                [
                    'path' => $path,
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * Ruim pending upload en intended URL op.
     */
    private function forgetPendingUpload(
        Request $request
    ): void {
        $request->session()->forget(
            self::PENDING_SESSION_KEY
        );

        /*
         * Na claim hoeft een latere login niet opnieuw naar images.claim.
         */
        $request->session()->forget(
            'url.intended'
        );
    }

    /**
     * Redirect wanneer er geen pending upload beschikbaar is.
     */
    private function redirectWithoutPendingImage(
        Request $request
    ): RedirectResponse {
        /*
         * Geen pending upload betekent dat een oude intended claim-route
         * ook niet meer nodig is.
         */
        $request->session()->forget(
            'url.intended'
        );

        return redirect()
            ->route('images.index')
            ->with(
                'status',
                'Er stond geen tijdelijke upload meer klaar. Je bent naar je afbeeldingen gestuurd.'
            );
    }
}
