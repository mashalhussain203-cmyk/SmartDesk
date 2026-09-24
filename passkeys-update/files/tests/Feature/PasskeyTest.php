<?php

namespace Tests\Feature;

use App\Models\Passkey;
use App\Models\User;
use App\Services\PasskeyService;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasskeyTest extends TestCase
{
    private mixed $privateKey;

    private string $credentialId;

    protected function setUp(): void
    {
        parent::setUp();
        /** The provided project does not autoload the Database\\Factories namespace. */
        require_once base_path('database/factories/UserFactory.php');
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('p', 32)),
            'app.url' => 'https://localhost',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'session.driver' => 'array',
            'cache.default' => 'array',
            'passkeys.enabled' => true,
            'passkeys.origin' => 'https://localhost',
            'login-security.enabled' => false,
        ]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--path' => [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/2026_09_15_232155_add_is_admin_to_users_tablee.php',
            'database/migrations/2026_09_17_132836_add_login_provider_to_users_table.php',
            'database/migrations/2026_09_24_210000_create_passkeys_table.php',
        ], '--force' => true])->assertExitCode(0);
        $this->privateKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $this->assertNotFalse($this->privateKey);
        $this->credentialId = random_bytes(32);
    }

    private function recent(User $user): array
    {
        return ['passkeys.recent_login' => ['user_id' => (string) $user->id, 'at' => time()]];
    }

    private function registration(string $challenge, string $origin = 'https://localhost', int $flags = 69): array
    {
        $key = openssl_pkey_get_details($this->privateKey);
        $cose = hex2bin('a5010203262001215820').$key['ec']['x'].hex2bin('225820').$key['ec']['y'];
        $authData = hash('sha256', 'localhost', true).chr($flags).pack('N', 0)
            .str_repeat("\0", 16).pack('n', strlen($this->credentialId)).$this->credentialId.$cose;
        /** Minimal CBOR none-attestation with a real P-256 public key. */
        $attestation = hex2bin('a363666d74646e6f6e656761747453746d74a068617574684461746159')
            .pack('n', strlen($authData)).$authData;

        return [
            'id' => PasskeyService::encode($this->credentialId), 'type' => 'public-key', 'name' => 'Test iPhone',
            'response' => [
                'clientDataJSON' => PasskeyService::encode(json_encode([
                    'type' => 'webauthn.create', 'challenge' => $challenge, 'origin' => $origin, 'crossOrigin' => false,
                ], JSON_THROW_ON_ERROR)),
                'attestationObject' => PasskeyService::encode($attestation),
            ],
        ];
    }

    private function enrolledUser(): User
    {
        $user = UserFactory::new()->create();
        $options = $this->actingAs($user)->withSession($this->recent($user))
            ->postJson('/account/passkeys/options')->assertOk()->json('publicKey');
        $this->postJson('/account/passkeys/verify', $this->registration($options['challenge']))->assertOk();
        Auth::logout();

        return $user;
    }

    private function assertion(User $user, string $challenge, int $flags = 5, string $rpId = 'localhost', int $counter = 1): array
    {
        $client = json_encode(['type' => 'webauthn.get', 'challenge' => $challenge,
            'origin' => 'https://localhost', 'crossOrigin' => false], JSON_THROW_ON_ERROR);
        $authData = hash('sha256', $rpId, true).chr($flags).pack('N', $counter);
        openssl_sign($authData.hash('sha256', $client, true), $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return [
            'id' => PasskeyService::encode($this->credentialId), 'type' => 'public-key',
            'response' => [
                'clientDataJSON' => PasskeyService::encode($client),
                'authenticatorData' => PasskeyService::encode($authData),
                'signature' => PasskeyService::encode($signature),
                'userHandle' => PasskeyService::encode(app(PasskeyService::class)->userHandle($user)),
            ],
        ];
    }

    public function test_registration_and_real_signed_login_preserve_pending_upload(): void
    {
        $user = $this->enrolledUser();
        $challenge = $this->withSession(['pending_image' => ['path' => 'pending/test.png']])
            ->postJson('/passkeys/login/options')->assertOk()->json('publicKey.challenge');
        $this->postJson('/passkeys/login/verify', $this->assertion($user, $challenge))
            ->assertOk()->assertJson(['redirect' => route('images.claim')]);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('passkeys', ['user_id' => $user->id, 'sign_count' => 1]);
        $this->assertSame('passkey', $user->fresh()->login_provider);
    }

    public function test_management_requires_authentication_and_recent_login(): void
    {
        $this->postJson('/account/passkeys/options')->assertUnauthorized();
        $user = UserFactory::new()->create();
        $this->actingAs($user)->postJson('/account/passkeys/options')->assertForbidden();
        $this->withSession(['passkeys.recent_login' => ['user_id' => (string) $user->id, 'at' => time() - 601]])
            ->postJson('/account/passkeys/options')->assertForbidden();
        $this->assertDatabaseCount('passkeys', 0);
    }

    public function test_registration_rejects_a_different_origin(): void
    {
        $user = UserFactory::new()->create();
        $challenge = $this->actingAs($user)->withSession($this->recent($user))
            ->postJson('/account/passkeys/options')->assertOk()->json('publicKey.challenge');
        $this->postJson('/account/passkeys/verify', $this->registration($challenge, 'https://other.localhost'))->assertUnprocessable();
        $this->assertDatabaseCount('passkeys', 0);
    }

    public function test_registration_requires_user_verification(): void
    {
        $user = UserFactory::new()->create();
        $challenge = $this->actingAs($user)->withSession($this->recent($user))
            ->postJson('/account/passkeys/options')->assertOk()->json('publicKey.challenge');
        $this->postJson('/account/passkeys/verify', $this->registration($challenge, 'https://localhost', 65))->assertUnprocessable();
        $this->assertDatabaseCount('passkeys', 0);
    }

    public function test_invalid_signature_is_rejected_and_challenge_cannot_be_reused(): void
    {
        $user = $this->enrolledUser();
        $challenge = $this->postJson('/passkeys/login/options')->json('publicKey.challenge');
        $valid = $this->assertion($user, $challenge);
        $invalid = $valid;
        $invalid['response']['signature'] = PasskeyService::encode(random_bytes(70));
        $this->postJson('/passkeys/login/verify', $invalid)->assertUnprocessable();
        $this->postJson('/passkeys/login/verify', $valid)->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_assertion_requires_verification_presence_correct_rp_and_user_handle(): void
    {
        $user = $this->enrolledUser();
        foreach (['verification', 'presence', 'rp', 'handle', 'challenge'] as $case) {
            $challenge = $this->postJson('/passkeys/login/options')->assertOk()->json('publicKey.challenge');
            $input = $this->assertion($user,
                $case === 'challenge' ? PasskeyService::encode(random_bytes(32)) : $challenge,
                $case === 'verification' ? 1 : ($case === 'presence' ? 4 : 5),
                $case === 'rp' ? 'wrong.example' : 'localhost');
            if ($case === 'handle') {
                $input['response']['userHandle'] = PasskeyService::encode(random_bytes(32));
            }
            $this->postJson('/passkeys/login/verify', $input)->assertUnprocessable();
            $this->assertGuest();
        }
    }

    public function test_expired_and_missing_challenges_are_rejected(): void
    {
        $this->postJson('/passkeys/login/verify', [])->assertUnprocessable();
        $this->withSession(['passkeys.challenge.login' => [
            'value' => PasskeyService::encode(random_bytes(32)), 'expires_at' => time() - 1, 'user_id' => null,
        ]])->postJson('/passkeys/login/verify', [])->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_users_cannot_list_or_delete_another_users_passkey(): void
    {
        $owner = $this->enrolledUser();
        $key = Passkey::query()->where('user_id', $owner->id)->firstOrFail();
        $other = UserFactory::new()->create();
        $this->actingAs($other)->withSession($this->recent($other))
            ->getJson('/account/passkeys')->assertOk()->assertJsonCount(0, 'passkeys');
        $this->deleteJson('/account/passkeys/'.$key->id)->assertNotFound();
        $this->actingAs($owner)->withSession($this->recent($owner))
            ->deleteJson('/account/passkeys/'.$key->id)->assertOk();
        $this->assertDatabaseCount('passkeys', 0);
    }

    public function test_replayed_signature_counter_is_rejected(): void
    {
        $user = $this->enrolledUser();
        Passkey::query()->where('user_id', $user->id)->update(['sign_count' => 5]);
        $challenge = $this->postJson('/passkeys/login/options')->json('publicKey.challenge');
        $this->postJson('/passkeys/login/verify', $this->assertion($user, $challenge, counter: 5))->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_disabled_feature_returns_not_found(): void
    {
        config(['passkeys.enabled' => false]);
        $this->postJson('/passkeys/login/options')->assertNotFound();
    }

    public function test_challenge_is_bound_to_the_session(): void
    {
        $user = $this->enrolledUser();
        $challenge = $this->postJson('/passkeys/login/options')->json('publicKey.challenge');
        session()->invalidate();
        $this->postJson('/passkeys/login/verify', $this->assertion($user, $challenge))->assertUnprocessable();
        $this->assertGuest();
    }
}
