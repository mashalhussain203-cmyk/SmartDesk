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
        return Cache::remember('mashal:video-analyzer:v6:'.$videoId, now()->addMinutes(3), function () use ($videoUrl, $videoId, $counter) {
            // Use the public page first. Third-party metadata is always labeled
            // and used only when the official public page lacks fields.
            $state = $this->publicPage($videoUrl);
            $item = null;
            if (is_array($state)) {
                $scope = $state['__DEFAULT_SCOPE__'] ?? [];
                $item = $scope['webapp.video-detail']['itemInfo']['itemStruct']
                    ?? $scope['webapp.video-detail']['item_info']['item_struct']
                    ?? $state['ItemModule'][$videoId] ?? null;
                if (!is_array($item) || (string) ($item['id'] ?? '') !== $videoId) {
                    $item = $this->findVideo($state, $videoId);
                }
            }

            $mirror = $this->mirrorVideoDetails($videoUrl, $videoId);

            if (!is_array($item) && $mirror === []) {
                $browser = $this->browserData('video', $videoUrl, $videoId);
                if (is_array($browser['item'] ?? null)) {
                    $item = $browser['item'];
                }
            }

            $embed = $this->oembed($videoUrl);
            $stats = is_array($item) ? ($item['stats'] ?? $item['statsV2'] ?? []) : [];
            $stats = is_array($stats) ? $stats : [];
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
                    // An absent public response is not zero views.
                }
            }

            $technical = is_array($item['video'] ?? null) ? $item['video'] : [];
            $bitrateInfo = $technical['bitrateInfo'][0] ?? [];
            $bitrateInfo = is_array($bitrateInfo) ? $bitrateInfo : [];
            $width = $this->number($technical['width'] ?? $mirror['width'] ?? null);
            $height = $this->number($technical['height'] ?? $mirror['height'] ?? null);
            $fpsOfficial = $this->frameRate($technical['fps'] ?? $technical['frameRate'] ?? null);
            $fps = $fpsOfficial ?? $this->frameRate($mirror['fps'] ?? null);
            $bitrate = $this->number($bitrateInfo['Bitrate'] ?? $bitrateInfo['bitrate'] ?? $mirror['bitrate'] ?? null);
            $size = $this->number($technical['size'] ?? $mirror['file_size'] ?? null);
            $codec = $this->normalizeCodec($technical['codecType'] ?? $technical['codec'] ?? $mirror['codec'] ?? null);

            // FPS is often absent from TikTok's JSON. When a public CDN
            // video is available, ffprobe can read the actual video-stream
            // header without downloading or transcoding the full MP4.
            $publicMedia = $this->publicVideoAddress($technical['playAddr'] ?? null)
                ?? $this->publicVideoAddress($technical['playAddrH264'] ?? null)
                ?? $this->safeMediaUrl($mirror['hdplay'] ?? null)
                ?? $this->safeMediaUrl($mirror['play'] ?? null);
            $alternateMedia = $this->publicVideoAddress($technical['downloadAddr'] ?? null)
                ?? $this->safeMediaUrl($mirror['hdplay'] ?? null)
                ?? $this->safeMediaUrl($mirror['play'] ?? null);
            $fpsSource = $fpsOfficial !== null
                ? 'TikTok openbare videometadata'
                : ($fps !== null ? 'TikWM onafhankelijke metadata' : null);
            $probe = [];
            if ($width === null || $height === null || $fps === null || $codec === null || $size === null) {
                $probe = $this->metadataProbe($videoUrl, $videoId, $publicMedia, $alternateMedia);
                $width ??= $this->number($probe['width'] ?? null);
                $height ??= $this->number($probe['height'] ?? null);
                if ($fps === null && isset($probe['fps'])) {
                    $fps = $this->frameRate($probe['fps']);
                    if ($fps !== null) {
                        $fpsSource = $probe['source'] ?? 'Video-streammetadata';
                    }
                }
                $bitrate ??= $this->number($probe['bitrate'] ?? null);
                $size ??= $this->number($probe['file_size'] ?? null);
                $codec ??= $this->normalizeCodec($probe['codec'] ?? null);
            }

            if (!is_array($item) && $embed === null && $mirror === [] && ($probe['success'] ?? false) !== true && $this->numbers($stats) === 0) {
                throw new RuntimeException('TikTok kon deze openbare video niet laden. Controleer de link en probeer het opnieuw.');
            }

            $postRegion = $this->postRegion($item);
            $region = $postRegion ?? ($mirror['region'] ?? null);
            $regionSource = $postRegion !== null
                ? 'Officiële openbare TikTok-postmetadata'
                : ($region !== null ? 'Onafhankelijke publieke videobron' : null);
            $regionName = $this->regionName($region);
            $regionFlag = $this->regionFlag($region);

            $author = $item['author'] ?? null;
            $mirrorAuthor = $mirror['author'] ?? [];
            $username = (is_array($author) ? ($author['uniqueId'] ?? $author['unique_id'] ?? null) : $author)
                ?? (is_array($mirrorAuthor) ? ($mirrorAuthor['unique_id'] ?? $mirrorAuthor['uniqueId'] ?? null) : null)
                ?? ($probe['author'] ?? null);
            $nickname = (is_array($author) ? ($author['nickname'] ?? null) : null)
                ?? (is_array($mirrorAuthor) ? ($mirrorAuthor['nickname'] ?? null) : null)
                ?? ($embed['author_name'] ?? null) ?? $username;

            $caption = $item['desc'] ?? ($mirror['title'] ?? null)
                ?? ($probe['description'] ?? null) ?? ($embed['title'] ?? null);
            $caption = is_string($caption) ? trim($caption) : '';
            preg_match_all('/#[\p{L}\p{N}_.]+/u', $caption, $matches);
            $hashtags = array_values(array_unique(array_slice($matches[0] ?? [], 0, 16)));
            $publishedAt = $this->number($item['createTime'] ?? $item['create_time'] ?? $mirror['published_at'] ?? $probe['published_at'] ?? null);
            $estimatedDate = false;
            if ($publishedAt === null && preg_match('/^[0-9]{15,22}$/', $videoId)) {
                $publishedAt = intdiv((int) $videoId, 4294967296);
                $estimatedDate = true;
            }
            $published = $publishedAt !== null && $publishedAt > 1577836800 && $publishedAt <= time() + 86400
                ? CarbonImmutable::createFromTimestampUTC($publishedAt)->setTimezone('Europe/Amsterdam')->format('d-m-Y H:i')
                : null;

            $views = $this->number($stats['playCount'] ?? $stats['viewCount'] ?? $mirror['views'] ?? null);
            $likes = $this->number($stats['diggCount'] ?? $stats['likeCount'] ?? $mirror['likes'] ?? null);
            $comments = $this->number($stats['commentCount'] ?? $mirror['comments'] ?? null);
            $shares = $this->number($stats['shareCount'] ?? $mirror['shares'] ?? null);
            $duration = $this->number($technical['duration'] ?? $item['videoDuration'] ?? $mirror['duration'] ?? $probe['duration'] ?? null);

            $videoAvailable = is_array($item) || is_array($embed) || $mirror !== [] || ($probe['success'] ?? false) === true;
            $quality = $width && $height ? min($width, $height) : null;
            $download = $this->safeMediaUrl($mirror['hdplay'] ?? null)
                ?? $this->safeMediaUrl($probe['direct_url'] ?? null)
                ?? $this->safeMediaUrl($mirror['play'] ?? null);

            return [
                'video_id' => $videoId,
                'video_url' => $videoUrl,
                'author' => is_string($username) ? ltrim($username, '@') : null,
                'nickname' => is_string($nickname) ? $nickname : null,
                'title' => $caption,
                'hashtags' => $hashtags,
                'published_at' => $published,
                'published_estimated' => $estimatedDate,
                'thumbnail' => $technical['cover'] ?? $technical['originCover']
                    ?? ($mirror['cover'] ?? null) ?? ($embed['thumbnail_url'] ?? null),
                'views' => $views,
                'likes' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'engagement' => $views > 0 && $likes !== null && $comments !== null && $shares !== null
                    ? round(100 * ($likes + $comments + $shares) / $views, 2) : null,
                'resolution' => $width && $height ? $width.' × '.$height : null,
                'quality' => $quality !== null ? $quality.'P' : null,
                'fps' => $fps,
                'fps_source' => $fpsSource,
                'fps_status' => $fps === null ? 'niet uit openbare mediabron te bepalen'
                    : (str_contains((string) $fpsSource, 'ffprobe') ? 'gemeten' : 'uit metadata'),
                'duration' => $duration,
                'bitrate' => $bitrate,
                'codec' => $codec,
                'engine' => match ($codec) {
                    'H.264' => 'Standard (H.264)',
                    'H.265 / HEVC' => 'HEVC',
                    'AV1' => 'AV1',
                    default => null,
                },
                'file_size' => $size,
                'region' => $region,
                'region_name' => $regionName,
                'region_flag' => $regionFlag,
                'region_source' => $regionSource,
                'shadowban' => $videoAvailable ? 'Geen publieke blokkade aangetoond' : 'Niet te controleren',
                'shadowban_detail' => $videoAvailable
                    ? 'Openbaar vindbaar; de Voor jou-feedgeschiktheid is alleen zichtbaar in TikTok Studio.'
                    : 'Geen bevestigde publieke status; kijk in TikTok Studio voor aanbevelingsbeperkingen.',
                'public_visibility' => $videoAvailable ? 'Public' : 'Onbekend',
                'download_url' => $download,
                'download_quality' => $quality,
                'availability' => $videoAvailable ? 'Openbare video gevonden' : 'Beperkte openbare gegevens',
                'checked_at' => now()->format('d-m-Y H:i'),
            ];
        });
    }

    public function postingTimes(string $username, string $timezone = 'UTC'): array
    {
        if (!in_array($timezone, timezone_identifiers_list(), true) && $timezone !== 'UTC') {
            $timezone = 'UTC';
        }

        $posts = Cache::remember('mashal:posting-times:v4:'.strtolower($username), now()->addMinutes(10), function () use ($username) {
            $state = $this->publicPage('https://www.tiktok.com/@'.rawurlencode($username).'?lang=en') ?? [];
            $scope = $state['__DEFAULT_SCOPE__'] ?? [];
            $sources = [
                $scope['webapp.user-post'] ?? [],
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
                        $dateEstimated = $published === null;
                        if ($published === null) {
                            // TikTok's video snowflake id stores creation time in its high 32 bits.
                            $published = intdiv((int) $id, 4294967296);
                        }
                        if ($views !== null && $published > 1577836800 && $published < time() + 86400) {
                            $posts[$id] = ['id' => $id, 'views' => $views, 'published_at' => $published,
                                'source' => 'TikTok', 'date_estimated' => $dateEstimated];
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

            // When TikTok blocks its own endpoint on Railway, try a read-only
            // public third-party mirror; never send user credentials to it.
            if (count($posts) < 10) {
                foreach ($this->mirrorUserPosts($username) as $post) {
                    // An official TikTok observation takes precedence over a mirror.
                    $posts[$post['id']] ??= $post;
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
                        $posts[$id] ??= ['id' => $id, 'views' => $views, 'published_at' => $date,
                            'source' => 'TikTok browser', 'date_estimated' => true];
                    }
                }
            }

            // Independent public profile extractor as a final fallback.
            if (count($posts) < 10) {
                foreach ($this->ytDlpUserPosts($username) as $entry) {
                    $id = (string) ($entry['id'] ?? '');
                    $views = $this->number($entry['views'] ?? null);
                    $date = $this->number($entry['published_at'] ?? null);
                    if (preg_match('/^[0-9]{15,22}$/', $id) &&
                        $views !== null && $date !== null &&
                        $date > 1577836800 && $date <= time() + 86400) {
                        $posts[$id] ??= ['id' => $id, 'views' => $views, 'published_at' => $date,
                            'source' => 'yt-dlp', 'date_estimated' => true];
                    }
                }
            }

            if ($posts === []) {
                throw new RuntimeException(
                    'Er zijn geen verifieerbare openbare videostatistieken ontvangen voor @'.$username.'. '.
                    'Het profiel, de videofeed en aanvullende bronnen geven geen bruikbare weergavecijfers terug. '.
                    'Dit kan gebeuren bij een privéaccount, regiobeperking of TikTok-blokkade.'
                );
            }

            $posts = array_values($posts);
            usort($posts, fn ($a, $b) => $b['published_at'] <=> $a['published_at']);

            return array_slice($posts, 0, 60);
        });

        // Audit the origin of the observations. This is historical post
        // performance, NOT TikTok's private follower-active-hours analytics.
        $sources = [];
        $estimatedDates = 0;
        foreach ($posts as $post) {
            $origin = (string) ($post['source'] ?? 'Onbekende openbare bron');
            $sources[$origin] = ($sources[$origin] ?? 0) + 1;
            if ($post['date_estimated'] ?? true) {
                $estimatedDates++;
            }
        }
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
        $repeatSlots = count(array_filter($slots, fn ($slot) => $slot['count'] >= 2));
        $sampleReliable = count($posts) >= 20 && $repeatSlots >= 3 && $estimatedDates <= count($posts) / 4;
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
            'source_counts' => $sources,
            'estimated_dates' => $estimatedDates,
            'repeated_slots' => $repeatSlots,
            'sample_reliable' => $sampleReliable,
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


    /**
     * Optional fallback for publicly posted videos. TikWM is an independent
     * public mirror, not TikTok's official API. Never pass tokens or cookies.
     */
    private function mirrorUserPosts(string $username): array
    {
        // The independent public service documents unique_id in both
        // @username and username forms. Retry only if the first is empty.
        foreach ([$username, '@'.$username] as $identifier) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (compatible; MashalStudio/1.0)',
                ])->connectTimeout(4)->timeout(8)->get('https://www.tikwm.com/api/user/posts', [
                    'unique_id' => $identifier,
                    'count' => 30,
                    'cursor' => 0,
                ]);

                $payload = $response->successful() ? $response->json() : null;
                if (!is_array($payload) || (int) ($payload['code'] ?? -1) !== 0) {
                    continue;
                }
                $items = $payload['data']['videos'] ?? [];
                if (!is_array($items)) {
                    continue;
                }

                $posts = [];
                foreach (array_slice($items, 0, 60) as $video) {
                    if (!is_array($video)) {
                        continue;
                    }
                    $author = $video['author']['unique_id'] ?? $video['author']['uniqueId'] ?? null;
                    if (is_string($author) && strcasecmp(ltrim($author, '@'), $username) !== 0) {
                        continue;
                    }

                    $id = (string) ($video['video_id'] ?? $video['id'] ?? '');
                    if (!preg_match('/^[0-9]{15,22}$/', $id)) {
                        continue;
                    }
                    $views = $this->number($video['play_count'] ?? $video['playCount'] ?? $video['stats']['playCount'] ?? null);
                    $published = $this->number($video['create_time'] ?? $video['createTime'] ?? null);
                    $dateEstimated = $published === null;
                    if ($published === null) {
                        $published = intdiv((int) $id, 4294967296);
                    }
                    if ($views !== null && $published > 1577836800 && $published <= time() + 86400) {
                        $posts[$id] = ['id' => $id, 'views' => $views, 'published_at' => $published,
                            'source' => 'TikWM', 'date_estimated' => $dateEstimated];
                    }
                }

                if ($posts !== []) {
                    return array_values($posts);
                }
            } catch (Throwable $e) {
                // Do not invent posts when TikWM is rate-limited or unavailable.
            }
        }
        return [];
    }

    private function ytDlpUserPosts(string $username): array
    {
        if (!preg_match('/^[A-Za-z0-9._]{1,24}$/', $username)) {
            return [];
        }
        $script = base_path('scripts/tiktok_profile_fetch.py');
        if (!is_file($script)) {
            return [];
        }
        $python = base_path('.venv/bin/python');
        if (!is_file($python)) {
            $python = is_file('/opt/tiktok-venv/bin/python') ? '/opt/tiktok-venv/bin/python' : 'python3';
        }
        try {
            $proc = new Process([$python, $script, $username]);
            $proc->setTimeout(23);
            $proc->run();
            if (!$proc->isSuccessful()) {
                return [];
            }
            $result = json_decode($proc->getOutput(), true);
            return is_array($result) && is_array($result['posts'] ?? null)
                ? $result['posts'] : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function mirrorVideoDetails(string $url, string $videoId): array
    {
        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->connectTimeout(4)->timeout(9)->get('https://www.tikwm.com/api/', ['url' => $url, 'hd' => 1]);

            $payload = $response->successful() ? $response->json() : null;
            if (!is_array($payload) || (int) ($payload['code'] ?? -1) !== 0) {
                return [];
            }

            $data = $payload['data'] ?? null;
            if (!is_array($data) || (string) ($data['id'] ?? $data['video_id'] ?? '') !== $videoId) {
                return [];
            }

            $region = $data['region'] ?? $data['locationCreated'] ?? null;
            return [
                'region' => is_string($region) && preg_match('/^[A-Z]{2}$/i', $region) ? strtoupper($region) : null,
                'author' => $data['author'] ?? [],
                'title' => $data['title'] ?? null,
                'cover' => $data['cover'] ?? $data['origin_cover'] ?? null,
                'width' => $this->number($data['width'] ?? null),
                'height' => $this->number($data['height'] ?? null),
                'fps' => $this->number($data['fps'] ?? null),
                'codec' => $data['codec'] ?? null,
                'bitrate' => $this->number($data['bitrate'] ?? null),
                'file_size' => $this->number($data['hd_size'] ?? $data['size'] ?? null),
                'duration' => $this->number($data['duration'] ?? null),
                'published_at' => $this->number($data['create_time'] ?? null),
                'views' => $this->number($data['play_count'] ?? null),
                'likes' => $this->number($data['digg_count'] ?? null),
                'comments' => $this->number($data['comment_count'] ?? null),
                'shares' => $this->number($data['share_count'] ?? null),
                'hdplay' => $data['hdplay'] ?? null,
                'play' => $data['play'] ?? null,
            ];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function metadataProbe(string $url, string $videoId, ?string $publicMediaUrl = null, ?string $alternateMediaUrl = null): array
    {
        $script = base_path('scripts/tiktok_video_metadata.py');
        if (!is_file($script)) {
            return [];
        }
        $python = base_path('.venv/bin/python');
        if (!is_file($python)) {
            $python = is_file('/opt/tiktok-venv/bin/python') ? '/opt/tiktok-venv/bin/python' : 'python3';
        }
        try {
            $arguments = [$python, $script, $url, $videoId];
            if ($publicMediaUrl !== null) {
                $arguments[] = $publicMediaUrl;
            }
            if ($alternateMediaUrl !== null && $alternateMediaUrl !== $publicMediaUrl) {
                $arguments[] = $alternateMediaUrl;
            }
            $process = new Process($arguments);
            $process->setTimeout(42);
            $process->run();
            $payload = $process->isSuccessful() ? json_decode($process->getOutput(), true) : null;
            return is_array($payload) && ($payload['success'] ?? false) === true ? $payload : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function frameRate(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }
        $fps = round((float) $value, 2);
        return $fps >= 1 && $fps <= 240 ? $fps : null;
    }

    private function normalizeCodec(mixed $codec): ?string
    {
        if (!is_string($codec) || $codec === '') {
            return null;
        }
        $value = strtolower($codec);
        if (str_contains($value, 'avc') || str_contains($value, 'h264') || str_contains($value, 'h.264')) {
            return 'H.264';
        }
        if (str_contains($value, 'hev') || str_contains($value, 'h265') || str_contains($value, 'h.265')) {
            return 'H.265 / HEVC';
        }
        if (str_contains($value, 'av01') || str_contains($value, 'av1')) {
            return 'AV1';
        }
        if (str_contains($value, 'vp9')) {
            return 'VP9';
        }
        return strlen($codec) <= 25 ? $codec : null;
    }

    private function publicVideoAddress(mixed $value): ?string
    {
        if (is_string($value)) {
            return $this->safeMediaUrl($value);
        }
        if (!is_array($value)) {
            return null;
        }
        foreach ([
            $value['urlList'][0] ?? null,
            $value['UrlList'][0] ?? null,
            $value['url_list'][0] ?? null,
            $value[0] ?? null,
        ] as $candidate) {
            $safe = $this->safeMediaUrl($candidate);
            if ($safe !== null) {
                return $safe;
            }
        }
        return null;
    }

    private function safeMediaUrl(mixed $url): ?string
    {
        if (!is_string($url) || strlen($url) > 8000 || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            return null;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = ['tiktokcdn.com', 'tiktokcdn-us.com', 'tiktokcdn-eu.com', 'tiktokcdn-in.com', 'tiktokv.com', 'tiktokv.us', 'tikwm.com', 'akamaized.net', 'byteoversea.com', 'ibytedtos.com', 'tiktok.com', 'muscdn.com'];
        foreach ($allowed as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.'.$suffix)) {
                return $url;
            }
        }
        return null;
    }

    private function regionName(?string $region): ?string
    {
        if (!is_string($region) || !preg_match('/^[A-Z]{2}$/', $region)) {
            return null;
        }

        $names = [
            'NL' => 'Nederland', 'BE' => 'België', 'DE' => 'Duitsland',
            'US' => 'Verenigde Staten', 'GB' => 'Verenigd Koninkrijk',
            'PK' => 'Pakistan', 'FR' => 'Frankrijk', 'IT' => 'Italië',
            'ES' => 'Spanje', 'CA' => 'Canada', 'AU' => 'Australië',
            'IN' => 'India', 'AE' => 'Verenigde Arabische Emiraten',
            'SA' => 'Saoedi-Arabië', 'TR' => 'Turkije', 'MA' => 'Marokko',
            'BR' => 'Brazilië', 'JP' => 'Japan', 'KR' => 'Zuid-Korea',
            'ID' => 'Indonesië', 'PH' => 'Filipijnen', 'MX' => 'Mexico',
            'ZA' => 'Zuid-Afrika', 'EG' => 'Egypte', 'PT' => 'Portugal',
        ];
        if (isset($names[$region])) {
            return $names[$region];
        }
        if (class_exists(\Locale::class)) {
            $name = \Locale::getDisplayRegion('und-'.$region, 'nl');
            if (is_string($name) && $name !== '' && $name !== $region) {
                return ucfirst($name);
            }
        }
        return $region;
    }

    private function regionFlag(?string $region): ?string
    {
        if (!is_string($region) || !preg_match('/^[A-Z]{2}$/', $region)) {
            return null;
        }
        // Unicode regional indicators without depending on PHP mb_chr.
        $flagPoint = static function (int $point): string {
            return chr(0xF0 | ($point >> 18))
                .chr(0x80 | (($point >> 12) & 0x3F))
                .chr(0x80 | (($point >> 6) & 0x3F))
                .chr(0x80 | ($point & 0x3F));
        };
        return $flagPoint(0x1F1E6 + ord($region[0]) - 65)
            .$flagPoint(0x1F1E6 + ord($region[1]) - 65);
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
