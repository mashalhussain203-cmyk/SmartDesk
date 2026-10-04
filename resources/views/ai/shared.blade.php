<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>{{ $conversation->title }} · Mashal AI</title>
    <style>
        :root{color-scheme:dark}
        *{box-sizing:border-box}
        body{
            margin:0;
            background:
                radial-gradient(circle at 15% 8%, rgba(122,108,255,.10), transparent 28rem),
                radial-gradient(circle at 88% 18%, rgba(77,136,255,.07), transparent 30rem),
                #212121;
            color:#ececec;
            font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif
        }
        .shell{width:min(900px,100%);margin:0 auto;padding:32px 18px 80px}
        .head{display:flex;align-items:center;gap:12px;padding:0 0 28px;border-bottom:1px solid rgba(255,255,255,.1)}
        .logo{
            width:38px;
            height:38px;
            border-radius:12px;
            display:grid;
            place-items:center;
            background:linear-gradient(135deg,#8f82ff,#4d88ff);
            color:#f8f8ff;
            font-weight:950;
            box-shadow:0 10px 28px rgba(122,108,255,.22)
        }
        h1{font-size:20px;margin:0}
        .meta{font-size:12px;color:#9f95ff;margin-top:4px}
        article{padding:26px 0;border-bottom:1px solid rgba(255,255,255,.08)}
        .role{
            font-size:12px;
            font-weight:800;
            color:#a99fff;
            margin-bottom:9px;
            text-transform:uppercase;
            letter-spacing:.04em
        }
        .content{font-size:15px;line-height:1.75;white-space:pre-wrap;overflow-wrap:anywhere}
        .footer{padding-top:30px;color:#7f7f8a;font-size:12px}
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
