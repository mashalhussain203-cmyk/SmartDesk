<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImageUploadController extends Controller
{
    /**
     * Maximale bestandsgrootte in KB.
     *
     * 20480 KB = 20 MB.
     */
    private const MAX_UPLOAD_KB = 20480;

    /**
     * Maximale breedte of hoogte van een afbeelding.
     */
    private const MAX_DIMENSION = 20000;

    /**
     * Maximale hoeveelheid pixels.
     *
     * Beschermt de server tegen extreem grote afbeeldingen.
     */
    private const MAX_PIXELS = 100000000;

    /**
     * Ondersteunde afbeeldingstypen.
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Tijdelijke afbeelding uploaden.
     *
     * Dit mag ook gebeuren voordat de gebruiker is ingelogd.
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

        /*
        |--------------------------------------------------------------------------
        | Bestand controleren
        |--------------------------------------------------------------------------
        */

        try {
            $metadata = $this->inspectUploadedImage($file);
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Vorige tijdelijke upload verwijderen
        |--------------------------------------------------------------------------
        |
        | Wanneer dezelfde gebruiker opnieuw uploadt voordat de vorige
        | afbeelding is geclaimd, verwijderen we het oude tijdelijke bestand.
        |
        */

        $this->deletePreviousPendingUpload($request);

        /*
        |--------------------------------------------------------------------------
        | Tijdelijk opslaan
        |--------------------------------------------------------------------------
        */

        $temporaryPath = $file->store(
            'temp-images',
            'local'
        );

        if (! is_string($temporaryPath) || $temporaryPath === '') {
            return back()
                ->with(
                    'error',
                    'De afbeelding kon niet tijdelijk worden opgeslagen.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Originele naam opschonen
        |--------------------------------------------------------------------------
        */

        $originalName = $this->sanitizeOriginalName(
            $file->getClientOriginalName()
        );

        /*
        |--------------------------------------------------------------------------
        | Upload in sessie bewaren
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'pending_image',
            [
                'path' => $temporaryPath,
                'original_name' => $originalName,
                'mime_type' => $metadata['mime_type'],
                'file_size' => $metadata['file_size'],
                'width' => $metadata['width'],
                'height' => $metadata['height'],
                'uploaded_at' => now()->timestamp,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Gast naar login sturen
        |--------------------------------------------------------------------------
        */

        if (! Auth::check()) {
            /*
             * Laravel gebruikt url.intended na succesvolle login.
             *
             * Daardoor wordt de tijdelijke afbeelding daarna automatisch
             * aan het ingelogde account gekoppeld.
             */
            $request->session()->put(
                'url.intended',
                route('images.claim')
            );

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Je afbeelding staat klaar. Log in of registreer om verder te gaan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Ingelogde gebruiker direct laten claimen
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('images.claim');
    }

    /**
     * Tijdelijke afbeelding aan de ingelogde gebruiker koppelen.
     */
    public function claim(Request $request): RedirectResponse
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Gebruiker controleren
        |--------------------------------------------------------------------------
        |
        | De route hoort al achter auth middleware te staan, maar deze controle
        | zorgt voor extra veiligheid wanneer de routeconfiguratie ooit wijzigt.
        |
        */

        if (! $user) {
            return redirect()
                ->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | Pending upload ophalen
        |--------------------------------------------------------------------------
        */

        $pendingImage = $request->session()->get(
            'pending_image'
        );

        if (
            ! is_array($pendingImage) ||
            empty($pendingImage['path'])
        ) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Er staat geen afbeelding klaar om te bewerken.'
                );
        }

        $disk = Storage::disk('local');

        $temporaryPath = (string) $pendingImage['path'];

        /*
        |--------------------------------------------------------------------------
        | Alleen onze tijdelijke uploadmap accepteren
        |--------------------------------------------------------------------------
        */

        if (! Str::startsWith($temporaryPath, 'temp-images/')) {
            $request->session()->forget(
                'pending_image'
            );

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De tijdelijke afbeelding is ongeldig.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Tijdelijk bestand controleren
        |--------------------------------------------------------------------------
        */

        if (! $disk->exists($temporaryPath)) {
            $request->session()->forget(
                'pending_image'
            );

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De tijdelijke afbeelding bestaat niet meer. Upload de afbeelding opnieuw.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Werkelijke afbeelding opnieuw inspecteren
        |--------------------------------------------------------------------------
        |
        | We vertrouwen niet alleen op gegevens uit de sessie.
        |
        */

        try {
            $absolutePath = $disk->path(
                $temporaryPath
            );

            $verified = $this->inspectStoredImage(
                $absolutePath
            );
        } catch (Throwable $exception) {
            $disk->delete(
                $temporaryPath
            );

            $request->session()->forget(
                'pending_image'
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
        | Extensie bepalen vanuit werkelijke MIME
        |--------------------------------------------------------------------------
        */

        try {
            $extension = $this->extensionForMime(
                $verified['mime_type']
            );
        } catch (Throwable $exception) {
            $disk->delete(
                $temporaryPath
            );

            $request->session()->forget(
                'pending_image'
            );

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Het afbeeldingstype wordt niet ondersteund.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Unieke definitieve bestandsnaam maken
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

        /*
        |--------------------------------------------------------------------------
        | Definitieve directory aanmaken
        |--------------------------------------------------------------------------
        */

        if (! $disk->exists($finalDirectory)) {
            $disk->makeDirectory(
                $finalDirectory
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Bestand verplaatsen
        |--------------------------------------------------------------------------
        */

        try {
            $moved = $disk->move(
                $temporaryPath,
                $finalPath
            );
        } catch (Throwable $exception) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De afbeelding kon niet naar je persoonlijke opslag worden verplaatst.'
                );
        }

        if (
            ! $moved ||
            ! $disk->exists($finalPath)
        ) {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'De afbeelding kon niet definitief worden opgeslagen.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Originele bestandsnaam
        |--------------------------------------------------------------------------
        */

        $originalName = $this->sanitizeOriginalName(
            $pendingImage['original_name']
                ?? 'afbeelding.' . $extension
        );

        /*
        |--------------------------------------------------------------------------
        | Database-record aanmaken
        |--------------------------------------------------------------------------
        */

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
             * Geen database-record betekent dat we het reeds verplaatste
             * bestand ook weer verwijderen.
             */
            if ($disk->exists($finalPath)) {
                $disk->delete(
                    $finalPath
                );
            }

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Pending sessie opruimen
        |--------------------------------------------------------------------------
        */

        $request->session()->forget(
            'pending_image'
        );

        /*
        |--------------------------------------------------------------------------
        | Editor openen
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'images.editor',
                $image
            )
            ->with(
                'status',
                'Je afbeelding is opgeslagen en klaar om te bewerken.'
            );
    }

    /**
     * Een Laravel UploadedFile controleren.
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
        /*
        |--------------------------------------------------------------------------
        | Uploadstatus controleren
        |--------------------------------------------------------------------------
        */

        if (! $file->isValid()) {
            throw new RuntimeException(
                'De upload is niet geldig.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tijdelijk bestandspad ophalen
        |--------------------------------------------------------------------------
        */

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
     * Een afbeeldingsbestand inhoudelijk inspecteren.
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
        /*
        |--------------------------------------------------------------------------
        | Bestand moet bestaan
        |--------------------------------------------------------------------------
        */

        if (
            $absolutePath === '' ||
            ! is_file($absolutePath)
        ) {
            throw new RuntimeException(
                'Het afbeeldingsbestand bestaat niet.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Afbeeldingsmetadata lezen
        |--------------------------------------------------------------------------
        */

        $dimensions = @getimagesize(
            $absolutePath
        );

        if ($dimensions === false) {
            throw new RuntimeException(
                'Het bestand is geen geldige afbeelding.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Breedte en hoogte
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Maximale dimensies
        |--------------------------------------------------------------------------
        */

        if (
            $width > self::MAX_DIMENSION ||
            $height > self::MAX_DIMENSION
        ) {
            throw new RuntimeException(
                'De afbeelding heeft te grote afmetingen.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pixel-limiet
        |--------------------------------------------------------------------------
        */

        $totalPixels = $width * $height;

        if ($totalPixels > self::MAX_PIXELS) {
            throw new RuntimeException(
                'De afbeelding bevat te veel pixels.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME-type bepalen
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Bestandsgrootte controleren
        |--------------------------------------------------------------------------
        */

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
     * Veilige bestandsextensie bepalen op basis van MIME-type.
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
     * Originele bestandsnaam veilig opschonen.
     *
     * Deze naam wordt alleen als metadata opgeslagen.
     * De daadwerkelijke bestandsnaam op de server is altijd een UUID.
     */
    private function sanitizeOriginalName(
        mixed $originalName
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Naar string converteren
        |--------------------------------------------------------------------------
        */

        $originalName = trim(
            (string) $originalName
        );

        if ($originalName === '') {
            return 'afbeelding';
        }

        /*
        |--------------------------------------------------------------------------
        | Windows directory separators normaliseren
        |--------------------------------------------------------------------------
        */

        $originalName = str_replace(
            '\\',
            '/',
            $originalName
        );

        /*
        |--------------------------------------------------------------------------
        | Alleen daadwerkelijke bestandsnaam behouden
        |--------------------------------------------------------------------------
        */

        $originalName = basename(
            $originalName
        );

        /*
        |--------------------------------------------------------------------------
        | Control characters verwijderen
        |--------------------------------------------------------------------------
        */

        $cleanedName = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $originalName
        );

        if (is_string($cleanedName)) {
            $originalName = trim(
                $cleanedName
            );
        }

        if ($originalName === '') {
            return 'afbeelding';
        }

        /*
        |--------------------------------------------------------------------------
        | Zeer lange namen inkorten
        |--------------------------------------------------------------------------
        */

        if (
            mb_strlen($originalName) > 220
        ) {
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
     * Vorige nog niet geclaimde tijdelijke upload verwijderen.
     */
    private function deletePreviousPendingUpload(
        Request $request
    ): void {
        $previous = $request->session()->get(
            'pending_image'
        );

        if (
            ! is_array($previous) ||
            empty($previous['path'])
        ) {
            return;
        }

        $path = (string) $previous['path'];

        /*
        |--------------------------------------------------------------------------
        | Alleen bestanden uit temp-images verwijderen
        |--------------------------------------------------------------------------
        */

        if (! Str::startsWith($path, 'temp-images/')) {
            $request->session()->forget(
                'pending_image'
            );

            return;
        }

        $disk = Storage::disk(
            'local'
        );

        if ($disk->exists($path)) {
            $disk->delete(
                $path
            );
        }

        $request->session()->forget(
            'pending_image'
        );
    }
}