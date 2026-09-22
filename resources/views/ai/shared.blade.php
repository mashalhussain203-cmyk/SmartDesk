<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>{{ $conversation->title }} · Mashal AI</title>
    <style>
        :root{color-scheme:dark}
        *{box-sizing:border-box}
        body{margin:0;background:#212121;color:#ececec;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        .shell{width:min(900px,100%);margin:0 auto;padding:32px 18px 80px}
        .head{display:flex;align-items:center;gap:12px;padding:0 0 28px;border-bottom:1px solid rgba(255,255,255,.1)}
        .logo{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;background:#fff;color:#111;font-weight:950}
        h1{font-size:20px;margin:0}
        .meta{font-size:12px;color:#999;margin-top:4px}
        article{padding:26px 0;border-bottom:1px solid rgba(255,255,255,.08)}
        .role{font-size:12px;font-weight:800;color:#aaa;margin-bottom:9px;text-transform:uppercase;letter-spacing:.04em}
        .content{font-size:15px;line-height:1.75;white-space:pre-wrap;overflow-wrap:anywhere}
        .footer{padding-top:30px;color:#777;font-size:12px}
    </style>
</head>
<body>
<div class="shell">
    <div class="head">
        <div class="logo">M</div>
        <div>
            <h1>{{ $conversation->title }}</h1>
            <div class="meta">
                Gedeeld gesprek · alleen lezen
                @if($expiresAt)
                    · geldig tot {{ $expiresAt->format('d-m-Y H:i') }}
                @endif
            </div>
        </div>
    </div>

    @foreach($conversation->messages as $message)
        <article>
            <div class="role">{{ $message->role === 'assistant' ? 'Mashal AI' : 'Jij' }}</div>
            <div class="content">{{ $message->content }}</div>
        </article>
    @endforeach

    <div class="footer">Gedeeld via Mashal AI.</div>
</div>
</body>
</html>
