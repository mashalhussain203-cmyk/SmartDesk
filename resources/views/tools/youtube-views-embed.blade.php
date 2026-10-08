<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>YouTube Live Views</title>
    <link rel="stylesheet" href="/vendor/odometer/odometer-theme-minimal.css?v=20261007-2">
    <style>
        :root { color-scheme:dark; }
        * { box-sizing:border-box; }
        html,body {
            width:100%; height:100%; margin:0; overflow:hidden;
            background:transparent; font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
        }
        .box {
            width:100%; height:100%; padding:18px 20px; display:flex;
            align-items:center; justify-content:space-between; gap:18px;
            border:1px solid rgba(255,255,255,.08); border-radius:18px;
            background:rgba(8,10,14,.94); color:#fff;
            box-shadow:0 20px 50px rgba(0,0,0,.25);
        }
        .copy { min-width:0; }
        .label {
            color:#7c8592; font-size:9px; font-weight:850;
            letter-spacing:.12em; text-transform:uppercase;
        }
        .value {
            margin-top:4px; color:#fff; font-size:46px; line-height:1;
            font-weight:800; letter-spacing:-.05em; white-space:nowrap;
        }
        .meta {
            min-width:0; max-width:45%; text-align:right;
        }
        .title {
            overflow:hidden; color:#cbd0d6; font-size:10px;
            font-weight:760; text-overflow:ellipsis; white-space:nowrap;
        }
        .stats { margin-top:7px; color:#697281; font-size:8px; white-space:nowrap; }
        .odometer { font:inherit !important; line-height:inherit !important; }
    </style>
</head>
<body>
<div class="box">
    <div class="copy">
        <div class="label">YouTube Live Views</div>
        <div class="value" id="value">0</div>
    </div>
    <div class="meta">
        <div class="title" id="title">YouTube video</div>
        <div class="stats" id="stats">0 likes · 0 comments</div>
    </div>
</div>
<script src="/vendor/odometer/odometer.min.js?v=20261007-2"></script>
<script>
(function () {
    'use strict';

    var endpoint = @json(route('youtube-views.stats', ['videoId' => $videoId]));
    var value = document.getElementById('value');
    var title = document.getElementById('title');
    var stats = document.getElementById('stats');
    var odometer = null;

    function fmt(number) {
        return Number(number || 0).toLocaleString('en-US');
    }

    function apply(data) {
        if (!data || data.success !== true) return;

        if (typeof window.Odometer === 'function') {
            if (!odometer) {
                odometer = new window.Odometer({
                    el: value,
                    value: 0,
                    format: '(,ddd)',
                    duration: 900
                });
            }
            window.requestAnimationFrame(function () {
                odometer.update(Number(data.views || 0));
            });
        } else {
            value.textContent = fmt(data.views);
        }

        title.textContent = data.title || 'YouTube video';
        stats.textContent =
            fmt(data.likes) + ' likes · '
            + fmt(data.comments) + ' comments';
    }

    function load() {
        fetch(endpoint + '?_=' + Date.now(), {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('failed');
                return response.json();
            })
            .then(apply)
            .catch(function () {});
    }

    load();
    window.setInterval(load, 3000);
}());
</script>
</body>
</html>
