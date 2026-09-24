<?php

namespace App\Http\Controllers;

use App\Models\Passkey;
use App\Models\User;
use App\Services\PasskeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PasskeyController extends Controller
{
    public function __construct(private readonly PasskeyService $passkeys) {}

    public function index(Request $request): JsonResponse
    {
        $this->passkeys->ensureAvailable();

        return response()->json([
            'passkeys' => Passkey::query()->where('user_id', $request->user()->id)
                ->orderByDesc('id')->get(['id', 'name', 'created_at', 'last_used_at']),
        ])->header('Cache-Control', 'no-store');
    }

    public function registerOptions(Request $request): JsonResponse
    {
        $this->passkeys->ensureAvailable();
        $this->passkeys->requireRecentLogin($request);
        $user = $request->user();
        $existing = Passkey::query()->where('user_id', $user->id)->get();
        abort_if($existing->count() >= 10, 422, 'Je kunt maximaal tien passkeys bewaren.');

        return response()->json(['publicKey' => [
            'challenge' => $this->passkeys->issueChallenge($request, 'register'),
            'rp' => ['id' => $this->passkeys->rpId(), 'name' => config('passkeys.rp_name')],
            'user' => [
                'id' => PasskeyService::encode($this->passkeys->userHandle($user)),
                'name' => $user->email,
                'displayName' => $user->name ?: $user->email,
            ],
            'pubKeyCredParams' => [['type' => 'public-key', 'alg' => -7], ['type' => 'public-key', 'alg' => -257]],
            'timeout' => (int) config('passkeys.challenge_seconds') * 1000,
            'attestation' => 'none',
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'residentKey' => 'required',
                'requireResidentKey' => true,
                'userVerification' => 'required',
            ],
            'excludeCredentials' => $existing->where('rp_id', $this->passkeys->rpId())
                ->map(fn (Passkey $key): array => ['type' => 'public-key', 'id' => $key->credential_id])->values(),
        ]])->header('Cache-Control', 'no-store');
    }

    public function registerVerify(Request $request): JsonResponse
    {
        $this->passkeys->ensureAvailable();
        $this->passkeys->requireRecentLogin($request);
        $challenge = $this->passkeys->consumeChallenge($request, 'register');
        $input = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'id' => ['required', 'string', 'max:2048'],
            'type' => ['required', 'in:public-key'],
            'response.clientDataJSON' => ['required', 'string', 'max:8192'],
            'response.attestationObject' => ['required', 'string', 'max:65536'],
        ]);
        $clientData = $this->passkeys->validateClientData($input['response']['clientDataJSON'], 'webauthn.create');
        $attestation = PasskeyService::decode($input['response']['attestationObject']);
        try {
            $data = $this->passkeys->verifier()->processCreate($clientData, $attestation, $challenge, true, true);
        } catch (Throwable) {
            $this->passkeys->reject();
        }
        if (! hash_equals(PasskeyService::encode($data->credentialId), $input['id'])) {
            $this->passkeys->reject();
        }

        DB::transaction(function () use ($request, $input, $data): void {
            User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_if(Passkey::query()->where('user_id', $request->user()->id)->count() >= 10, 422,
                'Je kunt maximaal tien passkeys bewaren.');
            $hash = hash('sha256', $data->credentialId);
            abort_if(Passkey::query()->where('credential_hash', $hash)->exists(), 422,
                'Deze passkey is al geregistreerd.');
            Passkey::query()->create([
                'user_id' => $request->user()->id,
                'credential_hash' => $hash,
                'credential_id' => PasskeyService::encode($data->credentialId),
                'public_key' => $data->credentialPublicKey,
                'rp_id' => $this->passkeys->rpId(),
                'name' => trim($input['name']) ?: 'Mijn iPhone',
                'sign_count' => $data->signatureCounter ?? 0,
            ]);
        });

        return response()->json(['message' => 'Je passkey is ingesteld. Je kunt hiermee inloggen.']);
    }

    public function loginOptions(Request $request): JsonResponse
    {
        $this->passkeys->ensureAvailable();

        return response()->json(['publicKey' => [
            'challenge' => $this->passkeys->issueChallenge($request, 'login'),
            'rpId' => $this->passkeys->rpId(),
            'userVerification' => 'required',
            'timeout' => (int) config('passkeys.challenge_seconds') * 1000,
        ]])->header('Cache-Control', 'no-store');
    }

    public function loginVerify(Request $request): JsonResponse
    {
        $this->passkeys->ensureAvailable();
        $challenge = $this->passkeys->consumeChallenge($request, 'login');
        $input = $request->validate([
            'id' => ['required', 'string', 'max:2048'],
            'type' => ['required', 'in:public-key'],
            'response.clientDataJSON' => ['required', 'string', 'max:8192'],
            'response.authenticatorData' => ['required', 'string', 'max:8192'],
            'response.signature' => ['required', 'string', 'max:2048'],
            'response.userHandle' => ['required', 'string', 'max:128'],
        ]);
        $clientData = $this->passkeys->validateClientData($input['response']['clientDataJSON'], 'webauthn.get');
        $credentialId = PasskeyService::decode($input['id']);
        $authenticatorData = PasskeyService::decode($input['response']['authenticatorData']);
        $signature = PasskeyService::decode($input['response']['signature']);
        $userHandle = PasskeyService::decode($input['response']['userHandle']);

        $user = DB::transaction(function () use ($credentialId, $authenticatorData, $signature, $userHandle, $clientData, $challenge): User {
            $key = Passkey::query()->where('credential_hash', hash('sha256', $credentialId))
                ->where('rp_id', $this->passkeys->rpId())->lockForUpdate()->first();
            if (! $key || ! hash_equals($key->credential_id, PasskeyService::encode($credentialId))) {
                $this->passkeys->reject();
            }
            $user = $key->user;
            if (! $user || ! hash_equals($this->passkeys->userHandle($user), $userHandle)) {
                $this->passkeys->reject();
            }
            $verifier = $this->passkeys->verifier();
            try {
                $verifier->processGet($clientData, $authenticatorData, $signature,
                    $key->public_key, $challenge, $key->sign_count, true, true);
            } catch (Throwable) {
                $this->passkeys->reject();
            }
            $key->forceFill(['sign_count' => $verifier->getSignatureCounter() ?? 0, 'last_used_at' => now()])->save();

            return $user;
        });

        $user->forceFill(['login_provider' => 'passkey'])->save();
        $request->merge(['login_provider' => 'passkey']);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->flash('success', 'Je bent ingelogd met je passkey.');

        $fallback = route($user->is_admin ? 'admin.dashboard' : 'home');
        $intended = $request->session()->pull('url.intended', $fallback);
        $origin = (string) config('passkeys.origin');
        $destination = is_string($intended) && str_starts_with($intended, $origin.'/') ? $intended : $fallback;
        $pending = $request->session()->get('pending_image');
        if (is_array($pending) && ! empty($pending['path'])) {
            $destination = route('images.claim');
        }

        return response()->json(['redirect' => $destination])->header('Cache-Control', 'no-store');
    }

    public function destroy(Request $request, int $passkey): JsonResponse
    {
        $this->passkeys->ensureAvailable();
        $this->passkeys->requireRecentLogin($request);
        Passkey::query()->where('user_id', $request->user()->id)->whereKey($passkey)->firstOrFail()->delete();

        return response()->json(['message' => 'De passkey is verwijderd van je account.']);
    }
}
