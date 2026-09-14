<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartDesk | Wachtwoord herstellen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h1 class="h3 mb-3">Wachtwoord herstellen</h1>
                    <p class="text-muted">Voer je e-mailadres in om een resetlink te ontvangen.</p>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">E-mailadres</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Verstuur resetlink</button>
                    </form>

                    <div class="mt-3">
                        <a href="{{ route('login') }}">Terug naar inloggen</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
