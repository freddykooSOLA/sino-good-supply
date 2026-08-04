<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TranslationService
{
    public function toSimplified(string $text): string
    {
        return $this->translate($text, 'zh-CN');
    }

    public function toTraditional(string $text): string
    {
        return $this->translate($text, 'zh-TW');
    }

    public function translatePair(?string $english): array
    {
        $english = trim((string) $english);

        if ($english === '') {
            return ['zh' => '', 'zh_hant' => ''];
        }

        return [
            'zh' => $this->toSimplified($english),
            'zh_hant' => $this->toTraditional($english),
        ];
    }

    public function translateHtmlPair(?string $html): array
    {
        $plain = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($plain === '') {
            return ['zh' => '', 'zh_hant' => ''];
        }

        $pair = $this->translatePair($plain);

        return [
            'zh' => $this->wrapParagraphs($pair['zh']),
            'zh_hant' => $this->wrapParagraphs($pair['zh_hant']),
        ];
    }

    public function translate(string $text, string $target): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        $target = $this->normalizeTarget($target);
        $cacheKey = 'tr:gtx:'.md5($target.'|'.$text);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($text, $target) {
            $chunks = $this->chunk($text, 1500);
            $translated = [];

            foreach ($chunks as $chunk) {
                $translated[] = $this->translateViaGoogleGtx($chunk, $target);
                usleep(80000);
            }

            return trim(implode("\n", array_filter($translated, fn ($line) => $line !== '')));
        });
    }

    protected function translateViaGoogleGtx(string $text, string $target): string
    {
        try {
            $response = Http::timeout(25)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; SINOGOOD-Translator/1.0)',
                ])
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => 'en',
                    'tl' => $target,
                    'dt' => 't',
                    'q' => $text,
                ]);

            if (! $response->successful()) {
                Log::warning('Google GTX translation HTTP error', [
                    'status' => $response->status(),
                    'target' => $target,
                ]);

                return $text;
            }

            $json = $response->json();

            if (! is_array($json) || ! isset($json[0]) || ! is_array($json[0])) {
                return $text;
            }

            $out = '';
            foreach ($json[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $out .= $segment[0];
                }
            }

            $out = trim(html_entity_decode($out, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            return $out !== '' ? $out : $text;
        } catch (Throwable $e) {
            Log::error('Google GTX translation failed: '.$e->getMessage());

            return $text;
        }
    }

    protected function normalizeTarget(string $target): string
    {
        return match ($target) {
            'zh', 'zh-CN', 'zh_CN', 'zh_Hans', 'zh-Hans' => 'zh-CN',
            'zh-TW', 'zh_hant', 'zh-Hant', 'zh_Hant', 'zh-HK' => 'zh-TW',
            default => $target,
        };
    }

    /**
     * @return array<int, string>
     */
    protected function chunk(string $text, int $limit): array
    {
        if (mb_strlen($text) <= $limit) {
            return [$text];
        }

        $parts = preg_split('/(?<=[.!?。！？\n])\s+/u', $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($parts as $part) {
            if ($current !== '' && mb_strlen($current.' '.$part) > $limit) {
                $chunks[] = $current;
                $current = $part;
                continue;
            }

            $current = $current === '' ? $part : $current.' '.$part;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks ?: [$text];
    }

    protected function wrapParagraphs(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        $paragraphs = preg_split("/\n+/", $text) ?: [$text];

        return collect($paragraphs)
            ->map(fn (string $line) => '<p>'.e(trim($line)).'</p>')
            ->implode('');
    }
}
