<p>Beste {{ $user->name }},</p>
<p>Je kunt je wachtwoord herstellen via onderstaande link:</p>
<p><a href="{{ url('/reset-password/' . $token) }}">Wachtwoord herstellen</a></p>
<p>Deze link verloopt na 60 minuten.</p>
