<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat transcript #{{ $conversation->id }}</title>
    <style>
        body{font-family:Arial,sans-serif;max-width:900px;margin:32px auto;padding:0 20px;color:#161616;background:#fff}
        header{border-bottom:2px solid #111;padding-bottom:16px;margin-bottom:24px}.meta{color:#666;font-size:14px}.msg{padding:14px 16px;border:1px solid #ddd;border-radius:12px;margin:10px 0}.msg.admin{background:#f4f7ff}.msg.visitor{background:#fafafa}.who{font-weight:700}.time{float:right;color:#777;font-size:12px}.attachment{margin-top:8px;font-size:13px;color:#555}.toolbar{display:flex;gap:8px;margin-bottom:18px}.toolbar button{padding:10px 14px;cursor:pointer}@media print{.toolbar{display:none}body{margin:0;max-width:none}.msg{break-inside:avoid}}
    </style>
</head>
<body>
<div class="toolbar"><button onclick="window.print()">Afdrukken / opslaan als PDF</button></div>
<header>
    <h1>Chat transcript #{{ $conversation->id }}</h1>
    <div class="meta">{{ $conversation->name ?: 'Gast' }} @if($conversation->email) · {{ $conversation->email }} @endif</div>
</header>
@foreach($messages as $message)
    <div class="msg {{ $message->sender }}">
        <span class="who">{{ $message->sender === 'admin' ? 'Medewerker' : 'Klant' }}</span>
        <span class="time">{{ $message->created_at }}</span>
        <div style="clear:both;margin-top:8px;white-space:pre-wrap">{{ $message->body }}</div>
        @if($message->attachment_name)
            <div class="attachment">📎 {{ $message->attachment_name }} @if($message->attachment_size) ({{ number_format($message->attachment_size / 1048576, 1) }} MB) @endif</div>
        @endif
    </div>
@endforeach
</body>
</html>
