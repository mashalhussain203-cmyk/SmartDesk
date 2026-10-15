<?php

namespace App\Http\Controllers;

use App\Services\TikTokAnalyticsService;
use App\Services\TikTokVideoStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TikTokAnalyticsController extends Controller
{
    public function video(Request $request, TikTokAnalyticsService $analytics, TikTokVideoStatsService $counter): View
    {
        $result = null;
        $error = null;
        $url = trim((string) $request->query('url', ''));

        if ($url !== '') {
            $request->validate(['url' => ['required', 'string', 'max:2048']]);
            try {
                $resolved = $counter->resolveVideo($url);
                $result = $analytics->video($resolved['url'], $resolved['video_id'], $counter);
            } catch (Throwable $e) {
                report($e);
                $error = $e->getMessage();
            }
        }

        return view('tools.tiktok-analytics', [
            'mode' => 'video',
            'input' => $url,
            'result' => $result,
            'error' => $error,
        ]);
    }

    public function postingTimes(Request $request, TikTokAnalyticsService $analytics, TikTokVideoStatsService $counter): View
    {
        $result = null;
        $error = null;
        $input = trim((string) $request->query('username', ''));
        $timezone = (string) $request->query('timezone', 'UTC');

        if ($input !== '') {
            $request->validate([
                'username' => ['required', 'string', 'max:255'],
                'timezone' => ['nullable', 'string', 'max:64'],
            ]);

            try {
                $profile = $counter->resolveTikTokUsername($input);
                $result = $analytics->postingTimes($profile['username'], $timezone);
            } catch (Throwable $e) {
                report($e);
                $error = $e->getMessage();
            }
        }

        return view('tools.tiktok-analytics', [
            'mode' => 'times',
            'input' => $input,
            'result' => $result,
            'error' => $error,
        ]);
    }
}
