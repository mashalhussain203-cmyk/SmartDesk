<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LiveArchiveS3
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 20, 'connect_timeout' => 8, 'http_errors' => false]);
    }

    public function configured(): bool
    {
        $settings = config('services.live_archive', []);
        foreach (['endpoint', 'bucket', 'region', 'access_key_id', 'secret_access_key'] as $name) {
            if (!is_string($settings[$name] ?? null) || trim($settings[$name]) === '') return false;
        }
        return true;
    }

    private function config(): array
    {
        if (!$this->configured()) throw new RuntimeException('Private live archive bucket is not configured.');
        $settings = config('services.live_archive', []);
        $endpoint = rtrim((string) $settings['endpoint'], '/');
        if (!str_starts_with($endpoint, 'https://')) throw new RuntimeException('HTTPS S3 endpoint required.');
        return [
            'endpoint' => $endpoint,
            'bucket' => (string) $settings['bucket'],
            'region' => (string) $settings['region'],
            'key' => (string) $settings['access_key_id'],
            'secret' => (string) $settings['secret_access_key'],
        ];
    }

    /**
     * Sign Railway S3-compatible bucket requests via AWS Signature Version 4.
     * Only server-side code calls this method; keys and object URLs are never
     * sent to browsers or included in HTML.
     */
    private function request(string $method, ?string $key = null, array $query = [], array $extraHeaders = [], ?string $bodyFilePath = null): \Psr\Http\Message\ResponseInterface
    {
        $cfg = $this->config();
        $endpoint = parse_url($cfg['endpoint']);
        $virtual = (config('services.live_archive.url_style', 'path') === 'virtual-host');
        $host = (string) ($endpoint['host'] ?? '');
        if ($host === '') throw new RuntimeException('S3 endpoint has no host.');
        if ($virtual) $host = $cfg['bucket'].'.'.$host;
        if (isset($endpoint['port'])) $host .= ':'.$endpoint['port'];
        $prefix = rtrim((string) ($endpoint['path'] ?? ''), '/');
        $object = $key === null ? '' : implode('/', array_map('rawurlencode', explode('/', $key)));
        $uri = $virtual
            ? ($prefix.'/'.($object ?: ''))
            : ($prefix.'/'.rawurlencode($cfg['bucket']).'/'.$object);
        ksort($query, SORT_STRING);
        $qs = implode('&', array_map(
            static fn ($k) => rawurlencode((string) $k).'='.rawurlencode((string) $query[$k]),
            array_keys($query)
        ));
        $now = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);
        $payloadHash = $bodyFilePath === null ? hash('sha256', '') : hash_file('sha256', $bodyFilePath);
        if ($payloadHash === false) throw new RuntimeException('Unable to read private video upload.');
        $canonicalHeaders = "host:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$now}\n";
        $headersList = 'host;x-amz-content-sha256;x-amz-date';
        $canonical = implode("\n", [$method, $uri, $qs, $canonicalHeaders, $headersList, $payloadHash]);
        $scope = "{$date}/{$cfg['region']}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$now}\n{$scope}\n".hash('sha256', $canonical);
        $signingKey = hash_hmac('sha256', 'aws4_request',
            hash_hmac('sha256', 's3',
                hash_hmac('sha256', $cfg['region'],
                    hash_hmac('sha256', $date, 'AWS4'.$cfg['secret'], true),
                true),
            true),
        true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $authorization = "AWS4-HMAC-SHA256 Credential={$cfg['key']}/{$scope}, SignedHeaders={$headersList}, Signature={$signature}";
        $requestHeaders = array_merge([
            'Host' => $host,
            'X-Amz-Date' => $now,
            'X-Amz-Content-Sha256' => $payloadHash,
            'Authorization' => $authorization,
        ], $extraHeaders);
        $body = null;
        if ($bodyFilePath !== null) {
            $body = fopen($bodyFilePath, 'rb');
            if ($body === false) throw new RuntimeException('Unable to open private video upload.');
            $size = filesize($bodyFilePath);
            if ($size === false) throw new RuntimeException('Unable to determine private video size.');
            $requestHeaders['Content-Length'] = (string) $size;
        }
        try {
            return $this->client->request($method, 'https://'.$host.$uri.($qs ? '?'.$qs : ''), [
                'headers' => $requestHeaders,
                'body' => $body,
                'stream' => true,
                'allow_redirects' => false,
                // Streaming video uploads and downloads may exceed the default 20 seconds.
                'timeout' => ($method === 'GET' && str_starts_with((string) $key, 'recordings/'))
                    ? 0 : (($method === 'PUT' && str_starts_with((string) $key, 'recordings/')) ? 180 : 20),
            ]);
        } finally {
            if (is_resource($body)) fclose($body);
        }
    }

    /**
     * Scan the private bucket and recognize current and legacy recorder key layouts.
     * Supported examples: recordings/<account>/<file>.mp4,
     * live-recordings/<account>/<file>.mp4, <account>/<file>.mp4,
     * and timestamp_<account>.mp4.
     */
    public function listVideosForAccounts(array $accounts): array
    {
        // List the archive namespace once instead of guessing individual folder
        // prefixes. This also finds nested layouts such as
        // recordings/<account>/<date>/<file>.mp4.
        $allowed = array_values(array_unique(array_filter($accounts, static fn ($account) =>
            is_string($account) && preg_match('/^[A-Za-z0-9_.-]{1,64}$/D', $account)
        )));
        if (!$allowed) return [];

        $result = [];
        $seen = [];
        $token = null;
        $seenTokens = [];
        $pages = 0;

        do {
            $query = [
                'list-type' => '2',
                'prefix' => 'recordings/',
                'max-keys' => '1000',
            ];
            if (is_string($token) && $token !== '') {
                $query['continuation-token'] = $token;
            }

            $response = $this->request('GET', null, $query);
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('Unable to list private live recordings (HTTP '.$response->getStatusCode().').');
            }

            $xml = @simplexml_load_string((string) $response->getBody());
            if ($xml === false) {
                throw new RuntimeException('Invalid private archive list response.');
            }

            $entries = $xml->xpath('//*[local-name()="Contents"]') ?: [];
            foreach ($entries as $entry) {
                $read = static function (\SimpleXMLElement $node, string $name): string {
                    $value = $node->xpath('./*[local-name()="'.$name.'"]');
                    return isset($value[0]) ? (string) $value[0] : '';
                };

                $key = $read($entry, 'Key');
                $filename = basename($key);
                if ($key === '' || !preg_match('/^[A-Za-z0-9_.-]+\.mp4$/Di', $filename) || isset($seen[$key])) {
                    continue;
                }

                $account = $this->accountForRecordingKey($key, $filename, $allowed);
                if ($account === null || !str_starts_with($key, 'recordings/')) {
                    continue;
                }

                $seen[$key] = true;
                $result[] = [
                    'account' => $account,
                    'filename' => $filename,
                    'key' => $key,
                    'bytes' => (int) $read($entry, 'Size'),
                    'created_at' => strtotime($read($entry, 'LastModified')) ?: time(),
                ];
            }

            $truncatedNodes = $xml->xpath('//*[local-name()="IsTruncated"]') ?: [];
            $tokenNodes = $xml->xpath('//*[local-name()="NextContinuationToken"]') ?: [];
            $truncated = strtolower(isset($truncatedNodes[0]) ? (string) $truncatedNodes[0] : '') === 'true';
            $next = trim(isset($tokenNodes[0]) ? (string) $tokenNodes[0] : '');
            $token = ($truncated && $next !== '' && !isset($seenTokens[$next])) ? $next : null;
            if ($token !== null) $seenTokens[$token] = true;
            $pages++;
        } while ($token !== null && $pages < 1000);

        return $result;
    }

    public function listVideos(string $account): array
    {
        return array_values(array_filter(
            $this->listVideosForAccounts([$account]),
            static fn (array $recording): bool => $recording['account'] === $account
        ));
    }

    private function accountForRecordingKey(string $key, string $filename, array $allowed): ?string
    {
        $segments = array_values(array_filter(explode('/', trim($key, '/')), static fn ($part) => $part !== ''));
        if (!$segments || end($segments) !== $filename) return null;

        foreach ($segments as $segment) {
            if (in_array($segment, $allowed, true)) return $segment;
        }

        // Preserve a useful folder label for legacy layouts outside the current
        // monitored-account allowlist. Root-level MP4s are grouped as "archief".
        $parent = count($segments) > 1 ? $segments[count($segments) - 2] : 'archief';
        $reserved = ['recordings', 'live-recordings', 'archive', 'archives', 'videos'];
        if (in_array(strtolower($parent), $reserved, true)) {
            $parent = count($segments) > 2 ? $segments[count($segments) - 3] : 'archief';
        }

        if (!preg_match('/^[A-Za-z0-9_.-]{1,64}$/D', $parent) || in_array(strtolower($parent), $reserved, true)) {
            return 'archief';
        }

        return $parent;
    }

    public function getStatus(string $account): ?array
    {
        $res = $this->request('GET', "status/{$account}.json");
        if ($res->getStatusCode() === 404) return null;
        if ($res->getStatusCode() !== 200) throw new RuntimeException('Could not read recording status.');
        $json = json_decode((string) $res->getBody(), true);
        return is_array($json) ? $json : null;
    }

    private function objectKey(string $account, string $filename): string
    {
        if (!in_array($account, ['knock1knock', 'emyii', 'cutefacebigass', 'ricasashaa', 'julesxdann', 'leo_kitty', 'dellris'], true) ||
            !preg_match('/^[A-Za-z0-9_.-]+\.mp4$/D', $filename)) {
            abort(404);
        }
        return "recordings/{$account}/{$filename}";
    }

    /**
     * Store a privately staged MP4 in Railway S3 using a signed streaming PUT.
     * Only server-side code sees S3 credentials; the browser uploads small
     * chunks to the admin-only endpoint instead of accessing S3 directly.
     */
    public function storeVideoFromPath(string $account, string $filename, string $filePath): int
    {
        $key = $this->objectKey($account, $filename);
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('Private MP4 upload is unavailable.');
        }
        $size = filesize($filePath);
        if ($size === false || $size < 1024 || $size > 250 * 1024 * 1024) {
            throw new RuntimeException('Private MP4 upload size is invalid.');
        }

        $existing = $this->request('HEAD', $key);
        if ($existing->getStatusCode() === 200) {
            throw new RuntimeException('This recording already exists.');
        }
        if ($existing->getStatusCode() !== 404) {
            throw new RuntimeException('Unable to check private MP4 destination.');
        }

        $response = $this->request('PUT', $key, [], [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'private, no-store',
        ], $filePath);
        if (!in_array($response->getStatusCode(), [200, 201, 204], true)) {
            throw new RuntimeException('Private MP4 upload failed (HTTP '.$response->getStatusCode().').');
        }

        $head = $this->request('HEAD', $key);
        if ($head->getStatusCode() !== 200 || (int) $head->getHeaderLine('Content-Length') !== $size) {
            throw new RuntimeException('Uploaded private MP4 could not be verified.');
        }
        return $size;
    }

    private function resolveStoredObjectKey(string $account, string $filename, ?string $storedKey): string
    {
        if ($storedKey === null || $storedKey === '') return $this->objectKey($account, $filename);
        if (!preg_match('/^[A-Za-z0-9_.-]{1,64}$/D', $account)
            || !preg_match('/^[A-Za-z0-9_.-]+\.mp4$/D', $filename)
            || basename($storedKey) !== $filename
            || str_contains($storedKey, "..") 
            || str_starts_with($storedKey, '/')
            || $this->accountForRecordingKey($storedKey, $filename, [$account]) !== $account) {
            abort(404);
        }
        return $storedKey;
    }

    public function streamVideo(Request $request, string $account, string $filename, bool $download, ?string $storedKey = null): StreamedResponse
    {
        $key = $this->resolveStoredObjectKey($account, $filename, $storedKey);
        $headers = [];
        // iOS Safari and mobile download managers both use byte ranges.
        if ($request->hasHeader('Range')) {
            $range = (string) $request->header('Range');
            if (!preg_match('/^bytes=(?:\d+-\d*|\d*-\d+)$/D', $range)) abort(416);
            $headers['Range'] = $range;
        }
        $res = $this->request('GET', $key, [], $headers);
        $code = $res->getStatusCode();
        if ($code === 404) abort(404);
        if (!in_array($code, [200, 206], true)) {
            throw new RuntimeException("Video unavailable (HTTP {$code}).");
        }
        $responseHeaders = [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.addcslashes($filename, '"\\').'"',
        ];
        foreach (['Content-Length', 'Content-Range'] as $keyHeader) {
            if ($res->hasHeader($keyHeader)) $responseHeaders[$keyHeader] = $res->getHeaderLine($keyHeader);
        }
        return response()->stream(static function () use ($res): void {
            // Large private recordings can take minutes or hours to send over
            // mobile networks. PHP's default 30-second execution limit used to
            // terminate this response mid-video (production fatal error).
            if (function_exists('set_time_limit')) {
                @set_time_limit(120);
            }
            $body = $res->getBody();
            $chunks = 0;
            try {
                while (!$body->eof()) {
                    if (connection_aborted()) break;
                    $chunk = $body->read(65536);
                    if ($chunk === '') break;
                    echo $chunk;
                    if (function_exists('set_time_limit') && ++$chunks % 16 === 0) {
                        @set_time_limit(120);
                    }
                    flush();
                }
            } finally {
                $body->close();
            }
        }, $code, $responseHeaders);
    }

    public function removeVideo(string $account, string $filename, ?string $storedKey = null): void
    {
        $key = $this->resolveStoredObjectKey($account, $filename, $storedKey);
        $res = $this->request('DELETE', $key);
        if (!in_array($res->getStatusCode(), [200, 202, 204], true)) {
            throw new RuntimeException('Could not remove archived recording.');
        }
    }
}
