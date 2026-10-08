<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>YouTube Live Subscribers</title>
    <link rel="stylesheet" href="/vendor/odometer/odometer-theme-minimal.css?v=20261007-2">
    <style>
        * { box-sizing: border-box; }
        html, body { width: 100%; height: 100%; margin: 0; background: transparent; }
        body {
            display: grid;
            place-items: center;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #fff;
        }
        .embed {
            width: min(100%, 720px);
            min-height: 220px;
            padding: 22px;
            display: grid;
            grid-template-columns: auto minmax(0,1fr);
            align-items: center;
            gap: 20px;
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 24px;
            background:
                radial-gradient(circle at 100% 0%, rgba(255,83,99,.13), transparent 40%),
                rgba(8,10,14,.96);
            box-shadow: 0 24px 70px rgba(0,0,0,.28);
        }
        .avatar {
            width: 104px;
            height: 104px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 50%;
            object-fit: cover;
            background: #171b22;
        }
        .name {
            overflow: hidden;
            color: #e9edf2;
            font-size: 16px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .label {
            margin-top: 13px;
            color: #737c89;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .11em;
            text-transform: uppercase;
        }
        .count {
            margin-top: 5px;
            color: #fff;
            font-size: clamp(44px, 10vw, 72px);
            line-height: 1;
            font-weight: 790;
            letter-spacing: -.055em;
        }
        .odometer { font: inherit !important; line-height: inherit !important; }
        @media (max-width: 520px) {
            .embed { grid-template-columns: 1fr; text-align: center; }
            .avatar { margin: 0 auto; }
        }
    </style>
</head>
<body>
    <main class="embed">
        <img
            class="avatar"
            id="embed-avatar"
            src="/icons/follower-profile.svg?v=1"
            alt=""
            referrerpolicy="no-referrer"
        >
        <div>
            <div class="name" id="embed-name">YouTube channel</div>
            <div class="label">Subscribers</div>
            <div class="count" id="embed-count">0</div>
        </div>
    </main>

    <script src="/vendor/odometer/odometer.min.js?v=20261007-2"></script>
    <script>
    (function () {
        'use strict';

        var endpoint = @json(route('youtube-subscribers.stats', ['channelId' => $channelId]));
        var nameEl = document.getElementById('embed-name');
        var avatarEl = document.getElementById('embed-avatar');
        var countEl = document.getElementById('embed-count');
        var odometer = null;

        if (typeof window.Odometer === 'function') {
            odometer = new window.Odometer({
                el: countEl,
                value: 0,
                format: '(,ddd)',
                duration: 900
            });
        }

        function safeImage(url) {
            try {
                var parsed = new URL(String(url || ''));
                return parsed.protocol === 'https:' ? parsed.href : '';
            } catch (error) {
                return '';
            }
        }

        function apply(data) {
            if (!data || data.success === false) {
                return;
            }

            if (data.title) {
                nameEl.textContent = data.title;
            }

            var image = safeImage(data.avatar);
            if (image) {
                avatarEl.src = image;
            }

            var count = Number(data.subscribers);
            if (!Number.isFinite(count)) {
                return;
            }

            count = Math.max(0, Math.trunc(count));

            if (odometer) {
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(function () {
                        odometer.update(count);
                    });
                });
            } else {
                countEl.textContent = count.toLocaleString('en-US');
            }
        }

        function refresh() {
            fetch(
                endpoint + '?_=' + encodeURIComponent(String(Date.now())),
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-store',
                    credentials: 'same-origin'
                }
            )
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('request failed');
                    }
                    return response.json();
                })
                .then(apply)
                .catch(function () {});
        }

        refresh();
        window.setInterval(refresh, 5000);
    }());
    </script>
</body>
</html>
