<?php

namespace App\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;
use Symfony\Component\Process\Process;

class TikTokVideoStatsService
{
    /**
     * How long one snapshot is considered fresh.
     *
     * Your frontend polls every 4 seconds, so only the first request
     * after this window refreshes TikTok. Other visitors reuse that snapshot.
     */
    private const FRESH_MS = 4500;

    /**
     * Keep the last successful snapshot longer than the fresh window.
     * This lets us serve the latest known real value if TikTok temporarily fails.
     */
    private const SNAPSHOT_TTL_SECONDS = 45;

    /**
     * Prevent multiple visitors from refreshing the same video simultaneously.
     */
    private const LOCK_SECONDS = 30;

    private const ALLOWED_HOSTS = [
        'tiktok.com',
        'www.tiktok.com',
        'm.tiktok.com',
        'vm.tiktok.com',
        'vt.tiktok.com',
    ];

    public function resolveVideo(string $input): array
    {
        $url = $this->normalizeUrl($input);

        if (!$this->isAllowedTikTokUrl($url)) {
            throw new RuntimeException('Gebruik een geldige TikTok-video-URL.');
        }

        $videoId = $this->extractVideoId($url);

        if ($videoId !== null) {
            return [
                'video_id' => $videoId,
                'url' => $url,
            ];
        }

        $resolvedUrl = $this->followShortTikTokUrl($url);
        $videoId = $this->extractVideoId($resolvedUrl);

        if ($videoId === null) {
            throw new RuntimeException('Kon geen TikTok-video-ID uit deze link halen.');
        }

        return [
            'video_id' => $videoId,
            'url' => $resolvedUrl,
        ];
    }

    /**
     * Public method used by the controller.
     *
     * Result always contains real values returned by TikTok.
     * No artificial increments are generated.
     */
    public function getLiveStats(string $videoUrl, string $videoId): array
    {
        $resolved = $this->resolveVideo($videoUrl);

        if ((string) $resolved['video_id'] !== (string) $videoId) {
            throw new RuntimeException('De TikTok-link hoort niet bij deze video.');
        }

        $cacheKey = $this->snapshotKey($videoId);
        $lockKey = $this->lockKey($videoId);

        $snapshot = Cache::get($cacheKey);

        if ($this->isFreshSnapshot($snapshot)) {
            return $this->snapshotResult($snapshot, false);
        }

        /*
         * Only one request is allowed to refresh a video at a time.
         */
        $lock = Cache::lock($lockKey, self::LOCK_SECONDS);

        if ($lock->get()) {
            try {
                /*
                 * Double-check because another request may have refreshed
                 * between our first cache read and acquiring the lock.
                 */
                $snapshot = Cache::get($cacheKey);

                if ($this->isFreshSnapshot($snapshot)) {
                    return $this->snapshotResult($snapshot, false);
                }

                try {
                    $stats = $this->fetchFreshTikTokStats(
                        $resolved['url'],
                        $videoId
                    );

                    $snapshot = [
                        'stats' => $stats,
                        'fetched_at_ms' => $this->nowMs(),
                    ];

                    Cache::put(
                        $cacheKey,
                        $snapshot,
                        now()->addSeconds(self::SNAPSHOT_TTL_SECONDS)
                    );

                    return $this->snapshotResult($snapshot, true);
                } catch (Throwable $e) {
                    // DEBUG BUILD: expose the real refresh error instead of hiding it behind stale cache.
                    throw $e;
                }
            } finally {
                $lock->release();
            }
        }

        /*
         * Another request is currently refreshing this same video.
         * Return the latest real snapshot immediately when available.
         */
        $snapshot = Cache::get($cacheKey);

        if ($this->isUsableSnapshot($snapshot)) {
            return $this->snapshotResult($snapshot, false);
        }

        /*
         * First-ever request and another worker owns the refresh lock.
         * The Livecounts Chromium render can take 10-20 seconds on a cold
         * Railway container. Wait for that worker instead of failing after 2s.
         */
        for ($attempt = 0; $attempt < 96; $attempt++) {
            usleep(250000);

            $snapshot = Cache::get($cacheKey);

            if ($this->isUsableSnapshot($snapshot)) {
                return $this->snapshotResult($snapshot, false);
            }
        }

        /*
         * Recovery path: the previous worker may have crashed while holding
         * the lock. After ~24s, try once more to become the refresher.
         */
        $recoveryLock = Cache::lock($lockKey, self::LOCK_SECONDS);

        if ($recoveryLock->get()) {
            try {
                $snapshot = Cache::get($cacheKey);

                if ($this->isUsableSnapshot($snapshot)) {
                    return $this->snapshotResult($snapshot, false);
                }

                $stats = $this->fetchFreshTikTokStats(
                    $resolved['url'],
                    $videoId
                );

                $snapshot = [
                    'stats' => $stats,
                    'fetched_at_ms' => $this->nowMs(),
                ];

                Cache::put(
                    $cacheKey,
                    $snapshot,
                    now()->addSeconds(self::SNAPSHOT_TTL_SECONDS)
                );

                return $this->snapshotResult($snapshot, true);
            } finally {
                $recoveryLock->release();
            }
        }

        throw new RuntimeException(
            'Livecounts refresh is nog bezig en heeft na 24 seconden nog geen snapshot opgeleverd.'
        );
    }

    /**
     * Fetch stats without TikTok API keys/tokens and without the unofficial
     * /api/item/detail endpoint. We only download the public video webpage
     * and read the JSON TikTok embeds in that HTML.
     */
    private function fetchFreshTikTokStats(
        string $videoUrl,
        string $videoId
    ): array {
        $errors = [];

        /*
         * Provider 1: render Livecounts' official TikTok embed in Chromium,
         * wait for its JavaScript counters, then read the visible
         * Views/Likes/Comments/Shares values from the rendered DOM.
         */
        try {
            $livecountsRendered = $this->fetchViaLivecountsRenderedPage($videoId);

            if ($livecountsRendered !== null) {
                return $livecountsRendered;
            }
        } catch (Throwable $e) {
            $errors[] = 'livecounts-rendered-page: '.$e->getMessage();
        }

        /*
         * Provider 2: static HTML fallback for Livecounts. This may only contain
         * placeholders, but it is cheap and harmless to try.
         */
        try {
            $livecountsPage = $this->fetchViaLivecountsPage($videoId);

            if ($livecountsPage !== null) {
                return $livecountsPage;
            }
        } catch (Throwable $e) {
            $errors[] = 'livecounts-page: '.$e->getMessage();
        }

        /*
         * Provider 3: normal request to Livecounts' public stats endpoint.
         * No challenge/auth bypass is attempted here.
         */
        try {
            $livecounts = $this->fetchViaLivecounts($videoId);

            if ($livecounts !== null) {
                return $livecounts;
            }
        } catch (Throwable $e) {
            $errors[] = 'livecounts-endpoint: '.$e->getMessage();
        }

        /*
         * Livecounts is intentionally required for this dashboard.
         * Do not silently substitute rounded TikTok/yt-dlp data: if all
         * Livecounts paths fail, surface the provider errors to the frontend.
         */
        throw new RuntimeException(
            'TIKTOK_DEBUG:'.json_encode([
                'stage' => 'all_providers_failed',
                'message' => 'Livecounts kon geen bruikbare live data leveren.',
                'provider_errors' => $errors,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    private function fetchViaLivecountsRenderedPage(string $videoId): ?array
    {
        $script = base_path('scripts/livecounts_browser_fetch.py');

        if (!is_file($script)) {
            throw new RuntimeException(
                'scripts/livecounts_browser_fetch.py ontbreekt.'
            );
        }

        $projectPython = base_path('.venv/bin/python');
        $python = (string) env(
            'TIKTOK_PYTHON',
            is_file($projectPython)
                ? $projectPython
                : '/opt/tiktok-venv/bin/python'
        );

        if (!is_file($python) && $python !== 'python3') {
            $python = 'python3';
        }

        $process = new Process([
            $python,
            $script,
            $videoId,
        ]);
        $process->setTimeout(22);
        $process->setIdleTimeout(null);

        $process->run();

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());
        $payload = json_decode($stdout, true);

        if (!$process->isSuccessful()) {
            $message = is_array($payload)
                ? (string) ($payload['message'] ?? 'Livecounts browser helper faalde.')
                : 'Livecounts browser helper faalde.';

            throw new RuntimeException(
                $message
                .' exit='.(string) $process->getExitCode()
                .' stderr='.mb_substr($stderr, 0, 1200)
            );
        }

        if (!is_array($payload) || ($payload['success'] ?? false) !== true) {
            throw new RuntimeException(
                is_array($payload)
                    ? (string) ($payload['message'] ?? 'Livecounts browser helper gaf success=false.')
                    : 'Livecounts browser helper gaf geen geldige JSON terug.'
            );
        }

        $stats = $payload['stats'] ?? null;

        if (!is_array($stats)) {
            throw new RuntimeException(
                'Livecounts browser helper bevatte geen stats.'
            );
        }

        $views = $this->toInt($stats['views'] ?? null);
        $likes = $this->toInt($stats['likes'] ?? null);
        $comments = $this->toInt($stats['comments'] ?? null);
        $shares = $this->toInt($stats['shares'] ?? null);
        $favorites = $this->toInt(
            $stats['favorites']
            ?? $stats['favoriteCount']
            ?? $stats['collectCount']
            ?? null
        );

        if (
            $views === null
            && $likes === null
            && $comments === null
            && $shares === null
            && $favorites === null
        ) {
            throw new RuntimeException(
                'Livecounts gerenderde pagina bevatte geen counters.'
            );
        }

        return [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'favorites' => $favorites,
            'author_name' => $payload['author_name'] ?? null,
            'title' => $payload['title'] ?? null,
            'thumbnail_url' => $payload['thumbnail_url'] ?? null,
            'source' => $payload['source'] ?? 'livecounts-official-embed-rendered',
            'precision' => 'raw_integer',
            '_debug' => $payload['debug'] ?? [
                'provider' => 'livecounts-rendered-page',
            ],
        ];
    }

    private function fetchViaLivecountsPage(string $videoId): ?array
    {
        $url = 'https://livecounts.io/tiktok-live-view-counter/'
            .rawurlencode($videoId);

        $response = Http::withHeaders([
            'Accept' =>
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9,nl;q=0.7',
            'User-Agent' =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                .'AppleWebKit/537.36 (KHTML, like Gecko) '
                .'Chrome/141.0.0.0 Safari/537.36',
            'Cache-Control' => 'no-cache, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ])
            ->connectTimeout(6)
            ->timeout(12)
            ->retry(1, 300, throw: false)
            ->get($url, [
                '_mashal_live' => $this->nowMs(),
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Livecounts pagina HTTP '.$response->status()
            );
        }

        $html = (string) $response->body();

        if (trim($html) === '') {
            throw new RuntimeException('Livecounts pagina was leeg.');
        }

        /*
         * Next/React may serialize page data with normal quotes, escaped quotes
         * or HTML entities. Normalize those representations before scanning.
         */
        $searchable = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $searchable = str_replace(
            ['\\\"', '\\"', '\\u0022', '\u0022'],
            '"',
            $searchable
        );

        $views = $this->extractLivecountsCounter(
            $searchable,
            ['viewCount', 'views', 'view_count']
        );
        $likes = $this->extractLivecountsCounter(
            $searchable,
            ['likeCount', 'likes', 'like_count']
        );
        $comments = $this->extractLivecountsCounter(
            $searchable,
            ['commentCount', 'comments', 'comment_count']
        );
        $shares = $this->extractLivecountsCounter(
            $searchable,
            ['shareCount', 'shares', 'share_count']
        );
        $favorites = $this->extractLivecountsCounter(
            $searchable,
            [
                'favoriteCount',
                'favorites',
                'favouriteCount',
                'collectCount',
                'collect_count',
            ]
        );

        /*
         * Some Livecounts rendering code maps the four TikTok values into
         * followerCount + bottomOdos [likes, comments, shares].
         */
        if ($views === null) {
            $views = $this->extractLivecountsCounter(
                $searchable,
                ['followerCount']
            );
        }

        if (
            ($likes === null || $comments === null || $shares === null)
            && preg_match(
                '/["\']bottomOdos["\']\s*:\s*\[\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\]/i',
                $searchable,
                $bottom
            )
        ) {
            $likes ??= (int) $bottom[1];
            $comments ??= (int) $bottom[2];
            $shares ??= (int) $bottom[3];
        }

        if (
            $views === null
            && $likes === null
            && $comments === null
            && $shares === null
            && $favorites === null
        ) {
            throw new RuntimeException(
                'Livecounts pagina bevatte geen server-rendered counters.'
            );
        }

        if (
            (int) ($views ?? 0) === 0
            && (int) ($likes ?? 0) === 0
            && (int) ($comments ?? 0) === 0
            && (int) ($shares ?? 0) === 0
            && (int) ($favorites ?? 0) === 0
        ) {
            throw new RuntimeException(
                'Livecounts statische pagina bevat alleen 0-placeholders.'
            );
        }

        $title = null;
        $thumbnailUrl = null;

        if (preg_match(
            '/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']/i',
            $html,
            $match
        )) {
            $title = html_entity_decode(
                $match[1],
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        }

        if (preg_match(
            '/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i',
            $html,
            $match
        )) {
            $thumbnailUrl = html_entity_decode(
                $match[1],
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        }

        return [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'favorites' => $favorites,
            'author_name' => null,
            'title' => $title,
            'thumbnail_url' => $thumbnailUrl,
            'source' => 'livecounts-public-page',
            'precision' => 'raw_integer',
            '_debug' => [
                'provider' => 'livecounts-page',
                'http_status' => $response->status(),
                'endpoint' => 'livecounts.io/tiktok-live-view-counter/{videoId}',
                'html_length' => strlen($html),
                'found' => [
                    'views' => $views !== null,
                    'likes' => $likes !== null,
                    'comments' => $comments !== null,
                    'shares' => $shares !== null,
                    'favorites' => $favorites !== null,
                ],
            ],
        ];
    }

    private function extractLivecountsCounter(
        string $text,
        array $keys
    ): ?int {
        foreach ($keys as $key) {
            $quoted = preg_quote($key, '/');

            foreach ([
                '/["\']'.$quoted.'["\']\s*:\s*["\']?(\d{1,20})["\']?/i',
                '/\\?["\']'.$quoted.'\\?["\']\s*:\s*\\?["\']?(\d{1,20})/i',
            ] as $pattern) {
                if (preg_match($pattern, $text, $match)) {
                    return (int) $match[1];
                }
            }
        }

        return null;
    }

    private function fetchViaLivecounts(string $videoId): ?array
    {
        $url = 'https://tiktok.livecounts.io/video/stats/'.rawurlencode($videoId);

        $response = Http::withHeaders([
            'Accept' => 'application/json,text/plain,*/*',
            'Origin' => 'https://livecounts.io',
            'Referer' => 'https://livecounts.io/',
            'User-Agent' =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                .'AppleWebKit/537.36 (KHTML, like Gecko) '
                .'Chrome/141.0.0.0 Safari/537.36',
            'Cache-Control' => 'no-cache, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ])
            ->connectTimeout(5)
            ->timeout(10)
            ->retry(1, 300, throw: false)
            ->get($url, [
                '_mashal_live' => $this->nowMs(),
            ]);

        if ($response->status() === 429) {
            throw new RuntimeException('Livecounts rate limit (HTTP 429).');
        }

        if (!$response->successful()) {
            throw new RuntimeException(
                'Livecounts HTTP '.$response->status()
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException('Livecounts gaf geen geldige JSON terug.');
        }

        /*
         * Livecounts has used more than one public response shape over time.
         * Older public wrappers use viewCount/likeCount/commentCount/shareCount;
         * newer responses have also been observed as views/likes/comments/shares.
         * Accept both, plus a few harmless nesting variants.
         */
        $counterData = $data;
        foreach (['data', 'stats', 'video'] as $container) {
            if (isset($data[$container]) && is_array($data[$container])) {
                $counterData = array_merge($counterData, $data[$container]);
            }
        }

        $views = $this->toInt(
            $counterData['views']
            ?? $counterData['viewCount']
            ?? $counterData['view_count']
            ?? null
        );
        $likes = $this->toInt(
            $counterData['likes']
            ?? $counterData['likeCount']
            ?? $counterData['like_count']
            ?? null
        );
        $comments = $this->toInt(
            $counterData['comments']
            ?? $counterData['commentCount']
            ?? $counterData['comment_count']
            ?? null
        );
        $shares = $this->toInt(
            $counterData['shares']
            ?? $counterData['shareCount']
            ?? $counterData['share_count']
            ?? null
        );
        $favorites = $this->toInt(
            $counterData['favorites']
            ?? $counterData['favoriteCount']
            ?? $counterData['favouriteCount']
            ?? $counterData['collectCount']
            ?? $counterData['collect_count']
            ?? null
        );

        if (
            $views === null
            && $likes === null
            && $comments === null
            && $shares === null
            && $favorites === null
        ) {
            throw new RuntimeException(
                'Livecounts-response bevat geen bruikbare counters. Keys: '
                .implode(',', array_slice(array_keys($data), 0, 25))
            );
        }

        /*
         * The older public wrapper also uses /video/data/{id} for metadata.
         * Metadata is optional; counter delivery must still succeed if it fails.
         */
        $authorName = null;
        $title = null;
        $thumbnailUrl = null;

        try {
            $metaResponse = Http::withHeaders([
                'Accept' => 'application/json,text/plain,*/*',
                'Origin' => 'https://livecounts.io',
                'Referer' => 'https://livecounts.io/',
                'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                    .'AppleWebKit/537.36 (KHTML, like Gecko) '
                    .'Chrome/141.0.0.0 Safari/537.36',
                'Cache-Control' => 'no-cache, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ])
                ->connectTimeout(4)
                ->timeout(7)
                ->get(
                    'https://tiktok.livecounts.io/video/data/'
                    .rawurlencode($videoId)
                );

            if ($metaResponse->successful()) {
                $meta = $metaResponse->json();

                if (is_array($meta)) {
                    $title = $meta['title'] ?? $meta['desc'] ?? null;
                    $thumbnailUrl = $meta['cover'] ?? $meta['thumbnail'] ?? null;

                    $author = $meta['author'] ?? null;
                    if (is_array($author)) {
                        $authorName = $author['id']
                            ?? $author['username']
                            ?? $author['uniqueId']
                            ?? null;
                    } elseif (is_string($author)) {
                        $authorName = $author;
                    }
                }
            }
        } catch (Throwable $ignored) {
            // Metadata is optional; stats remain usable.
        }

        return [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'favorites' => $favorites,
            'author_name' => $authorName,
            'title' => $title,
            'thumbnail_url' => $thumbnailUrl,
            'source' => 'livecounts-public-endpoint',
            'precision' => 'raw_integer',
            '_debug' => [
                'provider' => 'livecounts',
                'http_status' => $response->status(),
                'endpoint' => 'tiktok.livecounts.io/video/stats/{videoId}',
                'response_keys' => array_slice(array_keys($data), 0, 25),
                'shape' => [
                    'new_style' => isset($counterData['views']) || isset($counterData['likes']),
                    'legacy_style' => isset($counterData['viewCount']) || isset($counterData['likeCount']),
                ],
            ],
        ];
    }

    /**
     * Fetch the public TikTok page through curl_cffi browser impersonation.
     * The helper prints one JSON object to stdout. It never needs a TikTok API
     * key/token and it does not use a third-party scraping API.
     */
    private function fetchViaBrowserImpersonation(
        string $videoUrl,
        string $videoId
    ): ?array {
        $script = base_path('scripts/tiktok_public_fetch.py');
        $debug = [
            'stage' => 'python_prepare',
            'script' => $script,
            'script_exists' => is_file($script),
            'video_id' => $videoId,
        ];

        if (!is_file($script)) {
            $debug['message'] = 'scripts/tiktok_public_fetch.py ontbreekt op Railway.';
            throw new RuntimeException('TIKTOK_DEBUG:'.json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $projectPython = base_path('.venv/bin/python');
        $python = (string) env(
            'TIKTOK_PYTHON',
            is_file($projectPython) ? $projectPython : '/opt/tiktok-venv/bin/python'
        );

        if (!is_file($python) && $python !== 'python3') {
            $python = 'python3';
        }

        $debug['python'] = $python;
        $debug['project_venv_exists'] = is_file($projectPython);

        $process = new Process([$python, $script, $videoUrl, $videoId]);
        $process->setTimeout(25);
        $process->setIdleTimeout(20);

        try {
            $process->run();
        } catch (Throwable $e) {
            $debug['stage'] = 'python_start';
            $debug['message'] = $e->getMessage();
            throw new RuntimeException('TIKTOK_DEBUG:'.json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());
        $payload = json_decode($stdout, true);

        $debug['stage'] = 'python_finished';
        $debug['exit_code'] = $process->getExitCode();
        $debug['successful_process'] = $process->isSuccessful();
        $debug['stderr'] = mb_substr($stderr, 0, 2000);
        $debug['stdout'] = mb_substr($stdout, 0, 6000);

        if (is_array($payload)) {
            $debug['python_payload'] = $payload;
            $debug['stage'] = (string) ($payload['stage'] ?? $debug['stage']);
        }

        if (!$process->isSuccessful()) {
            $debug['message'] = is_array($payload)
                ? (string) ($payload['message'] ?? 'Python-helper eindigde met een fout.')
                : 'Python-helper eindigde met een fout.';
            throw new RuntimeException('TIKTOK_DEBUG:'.json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        if (!is_array($payload)) {
            $debug['stage'] = 'decode_python_json';
            $debug['message'] = 'Python gaf geen geldige JSON terug.';
            throw new RuntimeException('TIKTOK_DEBUG:'.json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        if (($payload['success'] ?? false) !== true) {
            $debug['message'] = (string) ($payload['message'] ?? 'Python gaf success=false terug.');
            throw new RuntimeException('TIKTOK_DEBUG:'.json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $stats = $payload['stats'] ?? null;
        if (!is_array($stats)) {
            $debug['stage'] = 'missing_stats';
            $debug['message'] = 'Python-response bevat geen stats-object.';
            throw new RuntimeException('TIKTOK_DEBUG:'.json_encode($debug, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return [
            'views' => $this->toInt($stats['views'] ?? null),
            'likes' => $this->toInt($stats['likes'] ?? null),
            'comments' => $this->toInt($stats['comments'] ?? null),
            'shares' => $this->toInt($stats['shares'] ?? null),
            'favorites' => $this->toInt(
                $stats['favorites']
                ?? $stats['favoriteCount']
                ?? $stats['collectCount']
                ?? null
            ),
            'author_name' => $payload['author_name'] ?? null,
            'title' => $payload['title'] ?? null,
            'thumbnail_url' => $payload['thumbnail_url'] ?? null,
            'source' => $payload['source'] ?? 'tiktok-public-html',
            'precision' => $payload['precision'] ?? 'unknown',
            '_debug' => $payload['debug'] ?? $debug,
        ];
    }

    /**
     * Read a public TikTok HTML page. This method intentionally does not call
     * /api/item/detail, Research API, Display API or any signed TikTok endpoint.
     */
    private function fetchVideoObjectFromPublicPage(
        string $url,
        string $videoId
    ): ?array {
        $response = $this->requestTikTok($url, [
            'Accept' =>
                'text/html,application/xhtml+xml,application/xml;q=0.9,'
                .'image/avif,image/webp,image/apng,*/*;q=0.8',
            'Referer' => 'https://www.tiktok.com/',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'TikTok HTTP '.$response->status()
            );
        }

        $html = (string) $response->body();

        if (trim($html) === '') {
            throw new RuntimeException('TikTok stuurde een lege HTML-pagina terug');
        }

        if ($this->looksLikeTikTokChallenge($html)) {
            throw new RuntimeException(
                'TikTok gaf een browser/challenge-pagina terug aan de Railway-server'
            );
        }

        foreach ([
            '__UNIVERSAL_DATA_FOR_REHYDRATION__',
            'SIGI_STATE',
            '__NEXT_DATA__',
        ] as $scriptId) {
            $data = $this->extractJsonScript($html, $scriptId);

            if (!is_array($data)) {
                continue;
            }

            if ($scriptId === '__UNIVERSAL_DATA_FOR_REHYDRATION__') {
                $direct =
                    $data['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct']
                    ?? $data['__DEFAULT_SCOPE__']['webapp.video-detail']['item_info']['item_struct']
                    ?? null;

                if (
                    is_array($direct)
                    && $this->videoMatches($direct, $videoId)
                    && $this->containsStats($direct)
                ) {
                    return $direct;
                }
            }

            if ($scriptId === 'SIGI_STATE') {
                $direct = $data['ItemModule'][$videoId] ?? null;

                if (
                    is_array($direct)
                    && $this->containsStats($direct)
                ) {
                    return $direct;
                }
            }

            $found = $this->findVideoRecursively($data, $videoId);

            if ($found !== null) {
                return $found;
            }
        }

        /*
         * TikTok occasionally changes only the script element id. Search every
         * application/json script rather than depending on a single DOM id.
         */
        if (preg_match_all(
            '/<script\\b[^>]*type=["\\\']application\\/json["\\\'][^>]*>(.*?)<\\/script>/is',
            $html,
            $matches
        )) {
            foreach ($matches[1] as $rawJson) {
                $rawJson = html_entity_decode(
                    trim((string) $rawJson),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                $data = json_decode($rawJson, true);

                if (!is_array($data)) {
                    continue;
                }

                $found = $this->findVideoRecursively($data, $videoId);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return $this->extractVideoFromHtmlWindow($html, $videoId);
    }

    private function canonicalVideoPageUrl(string $videoUrl): string
    {
        $parts = parse_url($videoUrl);

        if (!is_array($parts)) {
            return $videoUrl;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) ($parts['host'] ?? 'www.tiktok.com'));
        $path = (string) ($parts['path'] ?? '/');

        return $scheme.'://'.$host.$path.'?lang=en';
    }

    private function looksLikeTikTokChallenge(string $html): bool
    {
        $sample = strtolower(substr($html, 0, 250000));

        foreach ([
            'captcha',
            'verify to continue',
            'verify you are human',
            'security verification',
            'secsdk-captcha',
        ] as $needle) {
            if (str_contains($sample, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractVideoFromHtmlWindow(
        string $html,
        string $videoId
    ): ?array {
        $decoded = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        /* JSON may be embedded with escaped quotes in some page variants. */
        $searchable = str_replace('\\"', '"', $decoded);
        $offset = 0;

        while (($position = strpos($searchable, $videoId, $offset)) !== false) {
            $start = max(0, $position - 30000);
            $window = substr($searchable, $start, 90000);

            $stats = [
                'playCount' => $this->extractCounterFromText(
                    $window,
                    ['playCount', 'play_count', 'viewCount', 'view_count']
                ),
                'diggCount' => $this->extractCounterFromText(
                    $window,
                    ['diggCount', 'digg_count', 'likeCount', 'like_count']
                ),
                'commentCount' => $this->extractCounterFromText(
                    $window,
                    ['commentCount', 'comment_count']
                ),
                'shareCount' => $this->extractCounterFromText(
                    $window,
                    ['shareCount', 'share_count']
                ),
            ];

            if (count(array_filter($stats, static fn ($v) => $v !== null)) >= 2) {
                return [
                    'id' => $videoId,
                    'stats' => $stats,
                ];
            }

            $offset = $position + strlen($videoId);
        }

        return null;
    }

    private function extractCounterFromText(
        string $text,
        array $keys
    ): ?int {
        foreach ($keys as $key) {
            $quotedKey = preg_quote($key, '/');

            foreach ([
                '/["\']'.$quotedKey.'["\']\\s*:\\s*["\']?(\\d{1,20})["\']?/i',
                '/\\b'.$quotedKey.'\\b\\s*[:=]\\s*["\']?(\\d{1,20})["\']?/i',
            ] as $pattern) {
                if (preg_match($pattern, $text, $match)) {
                    return (int) $match[1];
                }
            }
        }

        return null;
    }

    private function requestTikTok(
        string $url,
        array $extraHeaders = []
    ): Response {
        /*
         * Keep one stable guest cookie identity between polls. TikTok often sets
         * ttwid/guest cookies on the first public page request and expects those
         * cookies again on later requests. Without a cookie jar Railway looked
         * like a brand-new bot every four seconds.
         */
        $cookieValues = Cache::get('tiktok-live-count:guest-cookies:v1', []);
        $jar = new CookieJar();

        if (is_array($cookieValues) && $cookieValues !== []) {
            $jar = CookieJar::fromArray($cookieValues, '.tiktok.com');
        }

        $headers = array_merge([
            'User-Agent' =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                .'AppleWebKit/537.36 (KHTML, like Gecko) '
                .'Chrome/154.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9,nl;q=0.7',
            'Cache-Control' => 'no-cache, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'DNT' => '1',
            'Sec-CH-UA' => '"Chromium";v="154", "Google Chrome";v="154", "Not_A Brand";v="99"',
            'Sec-CH-UA-Mobile' => '?0',
            'Sec-CH-UA-Platform' => '"Windows"',
        ], $extraHeaders);

        $response = Http::withHeaders($headers)
            ->withOptions([
                'cookies' => $jar,
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => true,
                    'referer' => true,
                    'track_redirects' => true,
                ],
                'http_errors' => false,
            ])
            ->connectTimeout(8)
            ->timeout(18)
            ->retry(1, 450, throw: false)
            ->get($url);

        $persist = [];
        foreach ($jar->toArray() as $cookie) {
            $name = (string) ($cookie['Name'] ?? '');
            $value = (string) ($cookie['Value'] ?? '');
            if ($name !== '' && $value !== '') {
                $persist[$name] = $value;
            }
        }

        if ($persist !== []) {
            Cache::put(
                'tiktok-live-count:guest-cookies:v1',
                $persist,
                now()->addHours(12)
            );
        }

        return $response;
    }

    private function normalizeVideoObject(array $video): array
    {
        $stats = $video['stats']
            ?? $video['statsV2']
            ?? $video['statistics']
            ?? [];

        $views = $this->firstInt([
            $stats['playCount'] ?? null,
            $stats['play_count'] ?? null,
            $stats['viewCount'] ?? null,
            $stats['view_count'] ?? null,
        ]);

        $likes = $this->firstInt([
            $stats['diggCount'] ?? null,
            $stats['digg_count'] ?? null,
            $stats['likeCount'] ?? null,
            $stats['like_count'] ?? null,
        ]);

        $comments = $this->firstInt([
            $stats['commentCount'] ?? null,
            $stats['comment_count'] ?? null,
        ]);

        $shares = $this->firstInt([
            $stats['shareCount'] ?? null,
            $stats['share_count'] ?? null,
        ]);

        $favorites = $this->firstInt([
            $stats['collectCount'] ?? null,
            $stats['collect_count'] ?? null,
            $stats['favoriteCount'] ?? null,
            $stats['favorites'] ?? null,
        ]);

        if (
            $views === null
            && $likes === null
            && $comments === null
            && $shares === null
            && $favorites === null
        ) {
            throw new RuntimeException(
                'TikTok response bevatte geen bruikbare counters.'
            );
        }

        $author = $video['author'] ?? null;

        if (is_array($author)) {
            $authorName =
                $author['uniqueId']
                ?? $author['unique_id']
                ?? $author['username']
                ?? $author['nickname']
                ?? null;
        } elseif (is_string($author)) {
            $authorName = $author;
        } else {
            $authorName = null;
        }

        $videoData = is_array($video['video'] ?? null)
            ? $video['video']
            : [];

        return [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'favorites' => $favorites,

            'author_name' => $authorName,

            'title' =>
                $video['desc']
                ?? $video['description']
                ?? $video['title']
                ?? null,

            'thumbnail_url' => $this->extractUrlValue(
                $videoData['cover']
                    ?? $videoData['originCover']
                    ?? $videoData['origin_cover']
                    ?? $videoData['dynamicCover']
                    ?? $videoData['dynamic_cover']
                    ?? null
            ),
        ];
    }

    private function snapshotResult(
        array $snapshot,
        bool $fetchedFresh,
        bool $staleFallback = false
    ): array {
        $stats = $snapshot['stats'];

        return array_merge(
            $stats,
            [
                'fetched_fresh' => $fetchedFresh,
                'stale_fallback' => $staleFallback,
                'snapshot_age_ms' => max(
                    0,
                    $this->nowMs()
                    - (int) ($snapshot['fetched_at_ms'] ?? 0)
                ),
            ]
        );
    }

    private function isFreshSnapshot(mixed $snapshot): bool
    {
        if (!$this->isUsableSnapshot($snapshot)) {
            return false;
        }

        $age = $this->nowMs()
            - (int) $snapshot['fetched_at_ms'];

        return $age >= 0
            && $age < self::FRESH_MS;
    }

    private function isUsableSnapshot(mixed $snapshot): bool
    {
        return is_array($snapshot)
            && isset($snapshot['stats'])
            && is_array($snapshot['stats'])
            && isset($snapshot['fetched_at_ms'])
            && is_numeric($snapshot['fetched_at_ms']);
    }

    private function snapshotKey(string $videoId): string
    {
        return 'tiktok-live-count:snapshot:v2:'.$videoId;
    }

    private function lockKey(string $videoId): string
    {
        return 'tiktok-live-count:refresh-lock:v2:'.$videoId;
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    private function extractJsonScript(
        string $html,
        string $scriptId
    ): ?array {
        $pattern =
            '/<script\b[^>]*\bid=["\']'
            .preg_quote($scriptId, '/')
            .'["\'][^>]*>(.*?)<\/script>/is';

        if (!preg_match($pattern, $html, $match)) {
            return null;
        }

        $json = trim((string) ($match[1] ?? ''));

        if ($json === '') {
            return null;
        }

        // Parse the script text exactly as TikTok emitted it first.
        $decoded = json_decode($json, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        // Some alternate page variants HTML-encode the script body.
        $decoded = json_decode(
            html_entity_decode($json, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            true
        );

        return is_array($decoded) ? $decoded : null;
    }

    private function findVideoRecursively(
        mixed $value,
        string $videoId
    ): ?array {
        if (!is_array($value)) {
            return null;
        }

        if (
            $this->videoMatches($value, $videoId)
            && $this->containsStats($value)
        ) {
            return $value;
        }

        foreach ($value as $child) {
            if (!is_array($child)) {
                continue;
            }

            $found = $this->findVideoRecursively(
                $child,
                $videoId
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function videoMatches(
        array $video,
        string $videoId
    ): bool {
        $candidate =
            $video['id']
            ?? $video['aweme_id']
            ?? $video['awemeId']
            ?? $video['itemId']
            ?? $video['item_id']
            ?? null;

        return $candidate !== null
            && (string) $candidate === (string) $videoId;
    }

    private function containsStats(array $video): bool
    {
        return is_array($video['stats'] ?? null)
            || is_array($video['statsV2'] ?? null)
            || is_array($video['statistics'] ?? null);
    }

    private function extractUrlValue(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (!is_array($value)) {
            return null;
        }

        foreach ([
            $value['url_list'][0] ?? null,
            $value['urlList'][0] ?? null,
            $value['UrlList'][0] ?? null,
            $value[0] ?? null,
        ] as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function firstInt(array $values): ?int
    {
        foreach ($values as $value) {
            $number = $this->toInt($value);

            if ($number !== null) {
                return $number;
            }
        }

        return null;
    }

    private function toInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        if (is_array($value)) {
            return $this->firstInt([
                $value['value'] ?? null,
                $value['count'] ?? null,
                $value['number'] ?? null,
            ]);
        }

        return null;
    }

    private function withCacheBuster(string $url): string
    {
        $separator = str_contains($url, '?')
            ? '&'
            : '?';

        return $url
            .$separator
            .'_mashal_live='
            .rawurlencode(
                $this->nowMs()
                .'-'
                .random_int(100000, 999999)
            );
    }

    private function followShortTikTokUrl(string $url): string
    {
        $current = $url;

        for ($i = 0; $i < 6; $i++) {
            if (!$this->isAllowedTikTokUrl($current)) {
                throw new RuntimeException(
                    'TikTok redirectte naar een ongeldig domein.'
                );
            }

            $response = Http::withHeaders([
                'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                    .'AppleWebKit/537.36 (KHTML, like Gecko) '
                    .'Chrome/141.0.0.0 Safari/537.36',

                'Accept' =>
                    'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',

                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])
                ->withoutRedirecting()
                ->connectTimeout(6)
                ->timeout(10)
                ->get($current);

            if (!$response->redirect()) {
                break;
            }

            $location = $response->header('Location');

            if (!$location) {
                break;
            }

            $current = $this->absoluteUrl(
                $current,
                $location
            );
        }

        return $current;
    }

    private function normalizeUrl(string $input): string
    {
        $url = trim($input);

        if ($url === '') {
            throw new RuntimeException('TikTok URL ontbreekt.');
        }

        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://'.$url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('Gebruik een geldige TikTok URL.');
        }

        return $url;
    }

    private function isAllowedTikTokUrl(string $url): bool
    {
        $host = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_HOST
            )
        );

        if ($host === '') {
            return false;
        }

        foreach (self::ALLOWED_HOSTS as $allowedHost) {
            if ($host === $allowedHost) {
                return true;
            }
        }

        return str_ends_with($host, '.tiktok.com');
    }

    private function extractVideoId(string $url): ?string
    {
        foreach ([
            '~/(?:video|v)/(\d{10,30})(?:[/?#]|$)~i',
            '~[?&](?:item_id|video_id)=(\d{10,30})(?:&|$)~i',
        ] as $pattern) {
            if (preg_match($pattern, $url, $match)) {
                return $match[1];
            }
        }

        return null;
    }

    private function absoluteUrl(
        string $baseUrl,
        string $location
    ): string {
        if (preg_match('~^https?://~i', $location)) {
            return $location;
        }

        $parts = parse_url($baseUrl);

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? 'www.tiktok.com';

        if (str_starts_with($location, '//')) {
            return $scheme.':'.$location;
        }

        if (str_starts_with($location, '/')) {
            return $scheme.'://'.$host.$location;
        }

        $path = $parts['path'] ?? '/';

        $directory = rtrim(
            str_replace(
                '\\',
                '/',
                dirname($path)
            ),
            '/'
        );

        return $scheme
            .'://'
            .$host
            .$directory
            .'/'
            .$location;
    }
}
