<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class TikTokAnalyticsService
{
    /**
     * Public TikTok data only. No credentials, signed API or private analytics.
     * Missing technical fields are deliberately returned as null.
     */
    public function video(string $videoUrl, string $videoId, TikTokVideoStatsService $counter): array
    {
        return Cache::remember('mashal:video-analyzer:v2:'.$videoId, now()->addMinutes(2), function () use ($videoUrl, $videoId, $counter) {
            $item = null;
            $state = $this->publicPage($videoUrl);

            if ($state !== null) {
                $scope = $state['__DEFAULT_SCOPE__'] ?? [];
                $item = $scope['webapp.video-detail']['itemInfo']['itemStruct']
                    ?? $scope['webapp.video-detail']['item_info']['item_struct']
                    ?? $state['ItemModule'][$videoId]
                    ?? null;
                if (!is_array($item) || (string) ($item['id'] ?? '') !== $videoId) {
                    $item = $this->findVideo($state, $videoId);
                }
            }

            // The rendered public TikTok page can contain data not in the
            // initial HTTP HTML (including a video's posted region).
            if (!is_array($item) || empty($item['region'])) {
                $browser = $this->browserData('video', $videoUrl, $videoId);
                if (is_array($browser['item'] ?? null)) {
                    $browserItem = $browser['item'];
                    if (!is_array($item)) {
                        $item = $browserItem;
                    } else {
                        $item = array_replace_recursive($browserItem, $item);
                    }
                }
            }

            $embed = $this->oembed($videoUrl);
            $stats = is_array($item) ? ($item['stats'] ?? $item['statsV2'] ?? []) : [];
            if (!is_array($stats)) {
                $stats = [];
            }

            // Reuse the same public TikTok source already used by Live Counts.
            if ($stats === []) {
                try {
                    $extra = $counter->getSupplementalTikTokStats($videoUrl, $videoId);
                    $stats = [
                        'playCount' => $extra['views'] ?? null,
                        'diggCount' => $extra['likes'] ?? null,
                        'commentCount' => $extra['comments'] ?? null,
                        'shareCount' => $extra['shares'] ?? null,
                    ];
                } catch (Throwable $e) {
                    // TikTok sometimes challenges server requests. Never invent values.
                }
            }

            if (!is_array($item) && $embed === null && $this->numbers($stats) === 0) {
                throw new RuntimeException('TikTok kon deze publieke video niet laden. Controleer de link of probeer het later.');
            }

            $technical = is_array($item['video'] ?? null) ? $item['video'] : [];
            $bitrateInfo = $technical['bitrateInfo'][0] ?? null;
            $bitrate = is_array($bitrateInfo) ? $this->number($bitrateInfo['Bitrate'] ?? $bitrateInfo['bitrate'] ?? null) : null;
            $width = $this->number($technical['width'] ?? null);
            $height = $this->number($technical['height'] ?? null);
            $fps = $this->number($technical['fps'] ?? $technical['frameRate'] ?? null);
            $views = $this->number($stats['playCount'] ?? $stats['viewCount'] ?? null);
            $likes = $this->number($stats['diggCount'] ?? $stats['likeCount'] ?? null);
            $comments = $this->number($stats['commentCount'] ?? null);
            $shares = $this->number($stats['shareCount'] ?? null);
            $author = $item['author'] ?? null;

            return [
                'video_id' => $videoId,
                'video_url' => $videoUrl,
                'author' => (is_array($author) ? ($author['uniqueId'] ?? $author['nickname'] ?? null) : $author)
                    ?? ($embed['author_name'] ?? null),
                'title' => $item['desc'] ?? ($embed['title'] ?? null),
                'thumbnail' => $technical['cover'] ?? ($embed['thumbnail_url'] ?? null),
                'views' => $views,
                'likes' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'engagement' => $views > 0 && $likes !== null && $comments !== null && $shares !== null
                    ? round(100 * ($likes + $comments + $shares) / $views, 2) : null,
                'resolution' => $width && $height ? $width.' × '.$height : null,
                'fps' => $fps,
                'duration' => $this->number($technical['duration'] ?? $item['videoDuration'] ?? null),
                'bitrate' => $bitrate,
                'codec' => $technical['codecType'] ?? $technical['codec'] ?? null,
                'file_size' => $this->number($technical['size'] ?? null),
                'region' => $this->postRegion($item),
                'region_source' => $this->postRegion($item) !== null
                    ? 'TikTok openbare postmetadata' : null,
                'shadowban' => 'Alleen in TikTok Studio te controleren',
                'public_visibility' => is_array($item)
                    ? 'Openbare video gevonden' : ($embed !== null ? 'Embed publiek beschikbaar' : 'Onbekend'),
                'availability' => is_array($item) ? 'Publieke videodata gevonden' : 'Beperkte openbare gegevens',
                'checked_at' => now()->format('d-m-Y H:i'),
            ];
        });
    }

    public function postingTimes(string $username, string $timezone = 'UTC'): array
    {
        if (!in_array($timezone, timezone_identifiers_list(), true) && $timezone !== 'UTC') {
            $timezone = 'UTC';
        }

        $posts = Cache::remember('mashal:posting-times:v2:'.strtolower($username), now()->addMinutes(10), function () use ($username) {
            $state = $this->publicPage('https://www.tiktok.com/@'.rawurlencode($username).'?lang=en') ?? [];
            $scope = $state['__DEFAULT_SCOPE__'] ?? [];
            $sources = [
                $scope['webapp.user-post'] ?? [],
                $state['ItemModule'] ?? [],
                $scope['webapp.user-detail'] ?? [],
            ];
            $posts = [];
            $visited = 0;
            $collect = function ($node, int $depth = 0) use (&$collect, &$posts, &$visited, $username) {
                if (!is_array($node) || $depth > 16 || ++$visited > 30000 || count($posts) >= 100) {
                    return;
                }

                $id = (string) ($node['id'] ?? '');
                $stats = $node['stats'] ?? $node['statsV2'] ?? $node['statistics'] ?? null;
                if (preg_match('/^[0-9]{15,22}$/', $id) && is_array($stats)) {
                    $author = $node['author'] ?? null;
                    $authorId = is_array($author) ? ($author['uniqueId'] ?? $author['unique_id'] ?? null) : $author;
                    if ($authorId === null || strcasecmp(ltrim((string) $authorId, '@'), $username) === 0) {
                        $views = $this->number($stats['playCount'] ?? $stats['viewCount'] ?? null);
                        $published = $this->number($node['createTime'] ?? $node['create_time'] ?? null);
                        if ($published === null) {
                            // TikTok's video snowflake id stores creation time in its high 32 bits.
                            $published = intdiv((int) $id, 4294967296);
                        }
                        if ($views !== null && $published > 1577836800 && $published < time() + 86400) {
                            $posts[$id] = ['id' => $id, 'views' => $views, 'published_at' => $published];
                        }
                    }
                }

                foreach ($node as $value) {
                    if (is_array($value)) {
                        $collect($value, $depth + 1);
                    }
                }
            };

            foreach ($sources as $source) {
                $collect($source);
            }

            // TikTok places the profile's post list behind /api/post/item_list.
            // The initial server-rendered HTML often contains *no* video cards.
            if (count($posts) < 15) {
                foreach ($this->publicPostPages($state) as $publicFeed) {
                    $collect($publicFeed);
                }
            }

            // Browser fallback observes publicly loaded TikTok data with the
            // same Chromium dependencies already installed on Railway.
            if (count($posts) < 10) {
                $browser = $this->browserData('posts', $username);
                foreach ($browser['posts'] ?? [] as $row) {
                    if (!is_array($row) || !preg_match('/^[0-9]{15,22}$/', (string) ($row['id'] ?? ''))) {
                        continue;
                    }
                    $id = (string) $row['id'];
                    $views = $this->number($row['views'] ?? null);
                    $date = $this->number($row['published_at'] ?? null);
                    if ($views !== null && $date !== null && $date > 1577836800 && $date <= time() + 86400) {
                        $posts[$id] = ['id' => $id, 'views' => $views, 'published_at' => $date];
                    }
                }
            }

            if ($posts === []) {
                throw new RuntimeException(
                    'TikTok heeft geen recente openbare videostatistieken vrijgegeven. '.
                    'We hebben zowel het profiel als de publieke videofeed gecontroleerd. '.
                    'Bij een TikTok-verificatie of afgeschermd account kan dit niet automatisch.'
                );
            }

            $posts = array_values($posts);
            usort($posts, fn ($a, $b) => $b['published_at'] <=> $a['published_at']);

            return array_slice($posts, 0, 60);
        });

        $slots = [];
        $daily = array_fill(0, 7, ['views' => 0, 'count' => 0]);
        foreach ($posts as $post) {
            $date = CarbonImmutable::createFromTimestampUTC($post['published_at'])->setTimezone($timezone);
            $day = (int) $date->isoWeekday() - 1;
            $hour = (int) $date->format('G');
            $key = $day.'-'.$hour;
            if (!isset($slots[$key])) {
                $slots[$key] = ['day' => $day, 'hour' => $hour, 'views' => 0, 'count' => 0];
            }
            $slots[$key]['views'] += $post['views'];
            $slots[$key]['count']++;
            $daily[$day]['views'] += $post['views'];
            $daily[$day]['count']++;
        }

        foreach ($slots as &$slot) {
            $slot['average'] = (int) round($slot['views'] / $slot['count']);
        }
        unset($slot);
        uasort($slots, fn ($a, $b) => $b['average'] <=> $a['average'] ?: $b['count'] <=> $a['count']);

        $best = array_slice(array_values($slots), 0, 5);
        $maximum = max(array_column($slots, 'average') ?: [1]);
        $matrix = [];
        for ($day = 0; $day < 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $slot = $slots[$day.'-'.$hour] ?? null;
                $matrix[$day][$hour] = [
                    'count' => $slot['count'] ?? 0,
                    'average' => $slot['average'] ?? null,
                    'intensity' => $slot ? max(12, round($slot['average'] / max(1, $maximum) * 100)) : 0,
                ];
            }
        }

        return [
            'username' => $username,
            'timezone' => $timezone,
            'post_count' => count($posts),
            'best' => $best,
            'matrix' => $matrix,
            'daily' => $daily,
            'checked_at' => now()->format('d-m-Y H:i'),
        ];
    }


    /**
     * Public web post feed. Some users have only a userInfo/secUid in
     * server-rendered HTML; itemList is loaded later through the web API.
     */
    private function publicPostPages(array $state): array
    {
        $scope = $state['__DEFAULT_SCOPE__'] ?? [];
        $user = $scope['webapp.user-detail']['userInfo']['user']
            ?? $scope['webapp.user-detail']['user_info']['user'] ?? [];
        $secUid = is_array($user) ? ($user['secUid'] ?? $user['sec_uid'] ?? null) : null;
        if (!is_string($secUid) || !preg_match('/^[\w-]{12,150}$/', $secUid)) {
            return [];
        }

        $pages = [];
        $cursor = 0;
        for ($batch = 0; $batch < 2; $batch++) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Referer' => 'https://www.tiktok.com/',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0.0.0 Safari/537.36',
                ])->connectTimeout(5)->timeout(11)->get('https://www.tiktok.com/api/post/item_list/', [
                    'aid' => 1988,
                    'app_name' => 'tiktok_web',
                    'device_platform' => 'web_pc',
                    'from_page' => 'user',
                    'count' => 30,
                    'cursor' => $cursor,
                    'secUid' => $secUid,
                ]);

                $data = $response->successful() ? $response->json() : null;
                if (!is_array($data) || !is_array($data['itemList'] ?? null)) {
                    break;
                }
                $pages[] = $data;
                if (empty($data['hasMore']) || !isset($data['cursor']) || (string) $data['cursor'] === (string) $cursor) {
                    break;
                }
                $cursor = (string) $data['cursor'];
            } catch (Throwable $e) {
                break;
            }
        }

        return $pages;
    }

    private function browserData(string $mode, string $target, ?string $videoId = null): array
    {
        $script = base_path('scripts/tiktok_analytics_browser.py');
        if (!is_file($script)) {
            return [];
        }

        $python = base_path('.venv/bin/python');
        if (!is_file($python)) {
            $python = is_file('/opt/tiktok-venv/bin/python')
                ? '/opt/tiktok-venv/bin/python' : 'python3';
        }

        $arguments = [$python, $script, $mode, $target];
        if ($mode === 'video') {
            $arguments[] = (string) $videoId;
        }

        try {
            $process = new Process($arguments);
            $process->setTimeout(30);
            $process->run();
            if (!$process->isSuccessful()) {
                return [];
            }

            $result = json_decode($process->getOutput(), true);
            return is_array($result) ? $result : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function postRegion(mixed $item): ?string
    {
        if (!is_array($item)) {
            return null;
        }

        // This is the region field of the *post*, not the creator's IP,
        // phone country code, account country or their audience's country.
        foreach ([$item['region'] ?? null, $item['locationCreated'] ?? null] as $value) {
            if (is_string($value) && preg_match('/^[A-Z]{2}$/i', $value)) {
                return strtoupper($value);
            }
        }

        return null;
    }

    private function publicPage(string $url): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.tiktok.com/',
            ])->connectTimeout(6)->timeout(14)->get($url);
            if (!$response->successful()) {
                return null;
            }
            $html = $response->body();
            foreach (['__UNIVERSAL_DATA_FOR_REHYDRATION__', 'SIGI_STATE', '__NEXT_DATA__'] as $id) {
                if (preg_match('/<script\b(?=[^>]*\bid=["\x27]'.preg_quote($id, '/').'["\x27])[^>]*>(.*?)<\/script>/is', $html, $match)) {
                    $data = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
                    if (is_array($data)) {
                        return $data;
                    }
                }
            }
        } catch (Throwable $e) {
            // Only genuine public results can be displayed; no placeholder data.
        }

        return null;
    }

    private function oembed(string $url): ?array
    {
        try {
            $response = Http::connectTimeout(5)->timeout(10)
                ->get('https://www.tiktok.com/oembed', ['url' => $url]);
            return $response->successful() && is_array($response->json()) ? $response->json() : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function findVideo(array $state, string $videoId): ?array
    {
        $stack = [[$state, 0]];
        $visited = 0;
        while ($stack !== [] && ++$visited < 12000) {
            [$node, $depth] = array_pop($stack);
            if (!is_array($node) || $depth > 14) {
                continue;
            }
            if ((string) ($node['id'] ?? '') === $videoId && is_array($node['stats'] ?? $node['statsV2'] ?? null)) {
                return $node;
            }
            foreach ($node as $value) {
                if (is_array($value)) {
                    $stack[] = [$value, $depth + 1];
                }
            }
        }

        return null;
    }

    private function number(mixed $value): ?int
    {
        return (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value)))
            ? max(0, (int) $value) : null;
    }

    private function numbers(array $stats): int
    {
        return count(array_filter($stats, fn ($value) => $this->number($value) !== null));
    }
}
