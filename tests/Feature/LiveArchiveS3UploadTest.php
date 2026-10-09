<?php

namespace Tests\Feature;

use App\Services\LiveArchiveS3;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use ReflectionProperty;
use Tests\TestCase;

class LiveArchiveS3UploadTest extends TestCase
{
    public function test_private_s3_upload_signs_file_bytes_and_verifies_the_stored_length(): void
    {
        $payload = "\0\0\0\x18ftypisom".str_repeat('x', 2048);
        $path = tempnam(sys_get_temp_dir(), 'smartdesk-mp4-');
        file_put_contents($path, $payload);

        $vars = [
            'LIVE_S3_ENDPOINT' => 'https://s3.example.test',
            'LIVE_S3_BUCKET' => 'test-private',
            'LIVE_S3_REGION' => 'auto',
            'LIVE_S3_ACCESS_KEY_ID' => 'test-access',
            'LIVE_S3_SECRET_ACCESS_KEY' => 'test-secret',
            'LIVE_S3_URL_STYLE' => 'path',
        ];
        $original = [];
        foreach ($vars as $name => $value) {
            $original[$name] = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null];
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        try {
            $history = [];
            $handler = new MockHandler([
                new Response(404),
                new Response(200),
                new Response(200, ['Content-Length' => (string) strlen($payload)]),
            ]);
            $stack = HandlerStack::create($handler);
            $stack->push(Middleware::history($history));
            $client = new Client(['handler' => $stack, 'http_errors' => false]);
            $archive = new LiveArchiveS3();
            (new ReflectionProperty(LiveArchiveS3::class, 'client'))->setValue($archive, $client);

            $stored = $archive->storeVideoFromPath('lucycums', 'test.mp4', $path);
            $this->assertSame(strlen($payload), $stored);
            $this->assertCount(3, $history);
            $this->assertSame(['HEAD', 'PUT', 'HEAD'], array_map(
                static fn ($call) => $call['request']->getMethod(), $history
            ));
            $put = $history[1]['request'];
            $this->assertSame('test-private', explode('/', trim($put->getUri()->getPath(), '/'))[0]);
            $this->assertSame(hash('sha256', $payload), $put->getHeaderLine('X-Amz-Content-Sha256'));
            $this->assertSame((string) strlen($payload), $put->getHeaderLine('Content-Length'));
            $this->assertSame('video/mp4', $put->getHeaderLine('Content-Type'));
            $this->assertStringContainsString('AWS4-HMAC-SHA256', $put->getHeaderLine('Authorization'));
        } finally {
            @unlink($path);
            foreach ($original as $name => [$env, $server]) {
                if ($env === null) unset($_ENV[$name]);
                else $_ENV[$name] = $env;
                if ($server === null) unset($_SERVER[$name]);
                else $_SERVER[$name] = $server;
            }
        }
    }
}
