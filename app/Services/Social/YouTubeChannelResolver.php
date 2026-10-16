<?php

namespace App\Services\Social;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YouTubeChannelResolver
{
    public function resolveChannelId(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        if (preg_match('/channel_id=([A-Za-z0-9_-]+)/', $input, $m)) {
            return $m[1];
        }

        if (preg_match('#youtube\.com/channel/([A-Za-z0-9_-]+)#', $input, $m)) {
            return $m[1];
        }

        if (preg_match('/^UC[A-Za-z0-9_-]{20,}$/', $input)) {
            return $input;
        }

        $handle = null;
        if (preg_match('#youtube\.com/@([^/?#]+)#', $input, $m)) {
            $handle = $m[1];
        } elseif (str_starts_with($input, '@')) {
            $handle = ltrim($input, '@');
        }

        if ($handle) {
            return Cache::remember('youtube.channel.'.$handle, 86400, fn () => $this->scrapeChannelId('https://www.youtube.com/@'.$handle));
        }

        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return Cache::remember('youtube.channel.url.'.md5($input), 86400, fn () => $this->scrapeChannelId($input));
        }

        return null;
    }

    protected function scrapeChannelId(string $url): ?string
    {
        $response = Http::timeout(12)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; SinoGoodBot/1.0)',
        ])->get($url);

        if ($response->failed()) {
            return null;
        }

        $html = $response->body();
        if (preg_match('/"channelId":"(UC[^"]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/"externalId":"(UC[^"]+)"/', $html, $m)) {
            return $m[1];
        }

        return null;
    }
}
