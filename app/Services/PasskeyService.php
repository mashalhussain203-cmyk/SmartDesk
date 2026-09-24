<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use lbuchs\WebAuthn\WebAuthn;

class PasskeyService
{
    public function ensureAvailable(): void
    {
        abort_unless(config('passkeys.enabled'), 404);
        abort_unless(class_exists(WebAuthn::class), 503, 'Passkeys zijn nog niet geïnstalleerd.');
        $origin = (string) config('passkeys.origin');
        $parts = parse_url($origin);
        $host = $parts['host'] ?? '';
        $scheme = $parts['scheme'] ?? '';
        abort_unless(
            $host !== '' && ($scheme === 'https' || ($scheme === 'http' && $host === 'localhost'))
            && empty($parts['user']) && empty($parts['pass']) && empty($parts['query'])
            && empty($parts['fragment']) && empty($parts['path']),
            503,
            'Stel PASSKEYS_ORIGIN in op het exacte HTTPS-adres, zonder pad. Lokaal kan http://localhost:8000.'
        );
        abort_if(config('session.driver') === 'cookie', 503, 'Passkeys vereisen server-side sessies.');
        $cacheDriver = config('cache.stores.'.config('cache.default').'.driver');
        abort_if(! app()->runningUnitTests() && in_array($cacheDriver, ['array', 'null'], true),
            503, 'Passkeys vereisen een gedeelde cache, bijvoorbeeld database of Redis.');
    }

    public function rpId(): string
    {
        return (string) parse_url((string) config('passkeys.origin'), PHP_URL_HOST);
    }

    public function verifier(): WebAuthn
    {
        return new WebAuthn((string) config('passkeys.rp_name'), $this->rpId(), ['none']);
    }

    public function userHandle(User $user): string
    {
        return hash('sha256', 'mashal-passkeys-user:'.$user->getAuthIdentifier(), true);
    }

    public function requireRecentLogin(Request $request): void
    {
        $recent = $request->session()->get('passkeys.recent_login', []);
        abort_unless(
            ($recent['user_id'] ?? null) === (string) $request->user()->getAuthIdentifier()
            && ($recent['at'] ?? 0) >= time() - (int) config('passkeys.recent_login_seconds'),
            403,
            'Log voor deze wijziging opnieuw in en open daarna Accountbeveiliging.'
        );
    }

    public function issueChallenge(Request $request, string $purpose): string
    {
        $challenge = self::encode(random_bytes(32));
        $request->session()->put('passkeys.challenge.'.$purpose, [
            'value' => $challenge,
            'expires_at' => time() + (int) config('passkeys.challenge_seconds'),
            'user_id' => $request->user()?->getAuthIdentifier(),
        ]);

        return $challenge;
    }

    public function consumeChallenge(Request $request, string $purpose): string
    {
        $state = $request->session()->pull('passkeys.challenge.'.$purpose);
        if (! is_array($state) || ($state['expires_at'] ?? 0) < time()
            || ($state['user_id'] ?? null) !== $request->user()?->getAuthIdentifier()
            || ! is_string($state['value'] ?? null)) {
            $this->reject('De aanvraag is verlopen. Probeer opnieuw.');
        }

        /** Atomic reservation also rejects concurrent replays of the same session challenge. */
        if (! Cache::add('passkeys.used.'.hash('sha256', $state['value']), true, 300)) {
            $this->reject('Deze aanvraag is al gebruikt. Probeer opnieuw.');
        }

        return self::decode($state['value']);
    }

    public function validateClientData(string $encoded, string $type): string
    {
        $raw = self::decode($encoded);
        $data = json_decode($raw, true);
        if (! is_array($data) || ($data['type'] ?? null) !== $type
            || ($data['origin'] ?? null) !== config('passkeys.origin')
            || ($data['crossOrigin'] ?? false) !== false) {
            $this->reject();
        }

        return $raw;
    }

    public static function encode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function decode(string $value): string
    {
        if ($value === '' || preg_match('/[^A-Za-z0-9_-]/', $value)) {
            throw ValidationException::withMessages(['passkey' => 'Ongeldige passkeygegevens.']);
        }
        $bytes = base64_decode(strtr($value, '-_', '+/'), true);
        if ($bytes === false || self::encode($bytes) !== $value) {
            throw ValidationException::withMessages(['passkey' => 'Ongeldige passkeygegevens.']);
        }

        return $bytes;
    }

    public function reject(string $message = 'De passkey kon niet worden gecontroleerd. Probeer opnieuw.'): never
    {
        throw ValidationException::withMessages(['passkey' => $message]);
    }
}
