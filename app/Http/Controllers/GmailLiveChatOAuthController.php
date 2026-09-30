<?php

namespace App\Http\Controllers;

use App\Services\GmailApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class GmailLiveChatOAuthController extends Controller
{
    public function connect(
        Request $request,
        GmailApiClient $gmail
    ): RedirectResponse {
        $this->authorizeAdmin($request);

        if (! $gmail->oauthClientConfigured()) {
            throw new RuntimeException(
                'Zet LIVE_CHAT_GMAIL_CLIENT_ID en LIVE_CHAT_GMAIL_CLIENT_SECRET eerst in Railway.'
            );
        }

        $state = Str::random(64);

        $request->session()->put(
            'live_chat.gmail_oauth_state',
            $state
        );

        return redirect()->away(
            $gmail->authorizationUrl($state)
        );
    }

    public function callback(
        Request $request,
        GmailApiClient $gmail
    ): RedirectResponse {
        $this->authorizeAdmin($request);

        $expectedState = (string) $request->session()->pull(
            'live_chat.gmail_oauth_state',
            ''
        );

        $receivedState = (string) $request->query('state', '');

        abort_unless(
            $expectedState !== ''
            && $receivedState !== ''
            && hash_equals($expectedState, $receivedState),
            419,
            'De Google OAuth-sessie is verlopen. Start de Gmail-koppeling opnieuw.'
        );

        if ($request->filled('error')) {
            return redirect()
                ->route('admin.live-chat.index')
                ->with(
                    'error',
                    'Google Gmail-koppeling geannuleerd: '
                    .(string) $request->query('error')
                );
        }

        $code = trim((string) $request->query('code', ''));

        abort_if(
            $code === '',
            422,
            'Google gaf geen autorisatiecode terug.'
        );

        $tokens = $gmail->exchangeAuthorizationCode($code);
        $refreshToken = trim(
            (string) ($tokens['refresh_token'] ?? '')
        );

        if ($refreshToken === '') {
            throw new RuntimeException(
                'Google gaf geen refresh token terug. Verwijder eventueel eerdere toestemming voor de app in uw Google-account en probeer opnieuw.'
            );
        }

        $gmail->storeRefreshToken($refreshToken);

        $profile = $gmail->profile();
        $connectedEmail = strtolower(
            trim((string) ($profile['emailAddress'] ?? ''))
        );

        $expectedEmail = $gmail->username();

        if (
            $expectedEmail !== ''
            && $connectedEmail !== ''
            && ! hash_equals($expectedEmail, $connectedEmail)
        ) {
            $gmail->clearStoredRefreshToken();

            throw new RuntimeException(
                'U koppelde '.$connectedEmail
                .', maar LIVE_CHAT_GMAIL_USERNAME staat op '
                .$expectedEmail.'. Koppel het juiste Google-account.'
            );
        }

        return redirect()
            ->route('admin.live-chat.index')
            ->with(
                'status',
                'Gmail is gekoppeld. Live-chat e-mails blijven nu in dezelfde Gmail-thread.'
            );
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            (bool) $request->user()?->is_admin,
            403
        );
    }
}
