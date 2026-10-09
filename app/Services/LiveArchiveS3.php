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
        foreach (['LIVE_S3_ENDPOINT','LIVE_S3_BUCKET','LIVE_S3_REGION','LIVE_S3_ACCESS_KEY_ID','LIVE_S3_SECRET_ACCESS_KEY'] as $name) {
            if (!is_string(env($name)) || trim(env($name)) === '') return false;
        }
        return true;
    }

    private function config(): array
    {
        if (!$this->configured()) throw new RuntimeException('Private live archive bucket is not configured.');
        $endpoint = rtrim((string) env('LIVE_S3_ENDPOINT'), '/');
        if (!str_starts_with($endpoint, 'https://')) throw new RuntimeException('HTTPS S3 endpoint required.');
        return [
            'endpoint' => $endpoint,
            'bucket' => (string) env('LIVE_S3_BUCKET'),
            'region' => (string) env('LIVE_S3_REGION'),
            'key' => (string) env('LIVE_S3_ACCESS_KEY_ID'),
            'secret' => (string) env('LIVE_S3_SECRET_ACCESS_KEY'),
        ];
    }

    /**
     * Sign Railway S3-compatible bucket requests via AWS Signature Version 4.
     * Only server-side code calls this method; keys and object URLs are never
     * sent to browsers or included in HTML.
     */
    private function request(string $method, ?string $key = null, array $query = [], array $extraHeaders = []): \Psr\Http\Message\ResponseInterface
    {
        $cfg = $this->config();
        $endpoint = parse_url($cfg['endpoint']);
        $host = (string) ($endpoint['host'] ?? '');
        if ($host === '') throw new RuntimeException('S3 endpoint has no host.');
        if (isset($endpoint['port'])) $host .= ':'.$endpoint['port'];
        $prefix = rtrim((string) ($endpoint['path'] ?? ''), '/');
        $segments = array_map('rawurlencode', explode('/', $cfg['bucket'].($key === null ? '' : '/'.$key)));
        $uri = $prefix.'/'.implode('/', $segments);
        if ($key === null) $uri .= '/';
        ksort($query, SORT_STRING);
        $qs = implode('&', array_map(
            static fn ($k) => rawurlencode((string) $k).'='.rawurlencode((string) $query[$k]),
            array_keys($query)
        ));
        $now = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);
        $payloadHash = hash('sha256', '');
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
        return $this->client->request($method, $cfg['endpoint'].$uri.($qs ? '?'.$qs : ''), [
            'headers' => $requestHeaders,
            'stream' => true,
            'allow_redirects' => false,
        ]);
    }

    public function listVideos(string $account): array
    {
        $response = $this->request('GET', null, ['list-type' => '2', 'prefix' => "recordings/{$account}/", 'max-keys' => '1000']);
        if ($response->getStatusCode() !== 200) throw new RuntimeException('Unable to list archived recordings ('.$response->getStatusCode().').');
        $xml = @simplexml_load_string((string) $response->getBody());
        if ($xml === false) throw new RuntimeException('Invalid bucket list response.');
        $result = [];
        foreach ($xml->Contents as $entry) {
            $key = (string) $entry->Key;
            $filename = basename($key);
            if (!preg_match('/^[A-Za-z0-9_.-]+\.mp4$/D', $filename) || !str_starts_with($key, "recordings/{$account}/")) continue;
            $result[] = [
                'account' => $account,
                'filename' => $filename,
                'bytes' => (int) $entry->Size,
                'created_at' => strtotime((string) $entry->LastModified) ?: time(),
            ];
        }
        return $result;
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
        if (!in_array($account, ['knock1knock','emyii'], true) ||
            !preg_match('/^[A-Za-z0-9_.-]+\.mp4$/D', $filename)) {
            abort(404);
        }
        return "recordings/{$account}/{$filename}";
    }

    public function streamVideo(Request $request, string $account, string $filename, bool $download): StreamedResponse
    {
        $key = $this->objectKey($account, $filename);
        $headers = [];
        if (!$download && $request->hasHeader('Range')) {
            $range = (string) $request->header('Range');
            if (!preg_match('/^bytes=\d*-\d*$/D', $range)) abort(416);
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
            $body = $res->getBody();
            try {
                while (!$body->eof()) {
                    echo $body->read(65536);
                    if (connection_aborted()) break;
                    flush();
                }
            } finally {
                $body->close();
            }
        }, $code, $responseHeaders);
    }

    public function removeVideo(string $account, string $filename): void
    {
        $key = $this->objectKey($account, $filename);
        $res = $this->request('DELETE', $key);
        if (!in_array($res->getStatusCode(), [200, 202, 204], true)) {
            throw new RuntimeException('Could not remove archived recording.');
        }
    }
}
