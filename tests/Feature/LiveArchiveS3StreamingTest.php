<?php

namespace Tests\Feature;

use App\Services\LiveArchiveS3;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use ReflectionProperty;
use Tests\TestCase;

class LiveArchiveS3StreamingTest extends TestCase
{
    private array $previousEnv = [];

    protected function tearDown(): void
    {
        foreach ($this->previousEnv as $name => $values) {
            if ($values['putenv'] === false) putenv($name);
            else putenv($name.'='.$values['putenv']);
            foreach (['_ENV', '_SERVER'] as $global) {
                if ($values[$global]['exists']) {
                    $GLOBALS[$global][$name] = $values[$global]['value'];
                } else {
                    unset($GLOBALS[$global][$name]);
                }
            }
        }
        parent::tearDown();
    }

    private function archiveWithResponses(array $responses): LiveArchiveS3
    {
        foreach ([
            'LIVE_S3_ENDPOINT' => 'https://t3.storageapi.dev',
            'LIVE_S3_BUCKET' => 'private-example',
            'LIVE_S3_REGION' => 'auto',
            'LIVE_S3_ACCESS_KEY_ID' => 'testing',
            'LIVE_S3_SECRET_ACCESS_KEY' => 'testing',
            'LIVE_S3_URL_STYLE' => 'virtual-host',
        ] as $name => $value) {
            if (!isset($this->previousEnv[$name])) {
                $this->previousEnv[$name] = [
                    'putenv' => getenv($name),
                    '_ENV' => ['exists' => array_key_exists($name, $_ENV), 'value' => $_ENV[$name] ?? null],
                    '_SERVER' => ['exists' => array_key_exists($name, $_SERVER), 'value' => $_SERVER[$name] ?? null],
                ];
            }
            putenv($name.'='.$value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        $archive = new LiveArchiveS3();
        $client = new Client(['handler' => HandlerStack::create(new MockHandler($responses))]);
        $property = new ReflectionProperty(LiveArchiveS3::class, 'client');
        $property->setValue($archive, $client);
        return $archive;
    }

    public function test_safari_byte_range_returns_206_and_content_range(): void
    {
        $archive = $this->archiveWithResponses([
            new Response(206, [
                'Content-Length' => '4',
                'Content-Range' => 'bytes 0-3/1000000',
                'Content-Type' => 'video/mp4',
            ], 'test'),
        ]);
        $request = Request::create('/live/lucycums/clip.mp4/watch', 'GET', [], [], [], [
            'HTTP_RANGE' => 'bytes=0-3',
        ]);
        $response = $archive->streamVideo($request, 'lucycums', 'clip.mp4', false);
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('bytes 0-3/1000000', $response->headers->get('Content-Range'));
        $this->assertSame('4', $response->headers->get('Content-Length'));
        $this->assertSame('bytes', $response->headers->get('Accept-Ranges'));
        ob_start();
        $response->sendContent();
        $this->assertSame('test', ob_get_clean());
    }

    public function test_mobile_download_supports_resume_ranges_and_attachment_filename(): void
    {
        $archive = $this->archiveWithResponses([
            new Response(206, [
                'Content-Length' => '4',
                'Content-Range' => 'bytes 100-103/1000000',
            ], 'data'),
        ]);
        $request = Request::create('/live/lucycums/clip.mp4/download', 'GET', [], [], [], [
            'HTTP_RANGE' => 'bytes=100-103',
        ]);
        $response = $archive->streamVideo($request, 'lucycums', 'clip.mp4', true);
        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('bytes 100-103/1000000', $response->headers->get('Content-Range'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('clip.mp4', $response->headers->get('Content-Disposition'));
        ob_start();
        $response->sendContent();
        $this->assertSame('data', ob_get_clean());
    }
}
