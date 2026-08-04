<?php

namespace App\Services;

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

        $chunks = $this->chunk($text, 450);
        $translated = [];

        foreach ($chunks as $chunk) {
            $translated[] = $this->translateChunk($chunk, $target);
            usleep(150000);
        }

        return trim(implode("\n", $translated));
    }

    protected function translateChunk(string $text, string $target): string
    {
        $langPair = match ($target) {
            'zh', 'zh-CN', 'zh_CN' => 'en|zh-CN',
            'zh-TW', 'zh_hant', 'zh-Hant' => 'en|zh-TW',
            default => 'en|'.$target,
        };

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->get('https://api.mymemory.translated.net/get', array_filter([
                    'q' => $text,
                    'langpair' => $langPair,
                    'de' => config('services.translation.email'),
                ]));

            if (! $response->successful()) {
                Log::warning('Translation API HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $text;
            }

            $translated = data_get($response->json(), 'responseData.translatedText');

            if (! is_string($translated) || $translated === '') {
                return $text;
            }

            // MyMemory sometimes returns the QUERY itself on quota errors.
            if (str_contains($translated, 'MYMEMORY WARNING')) {
                Log::warning('Translation API quota warning', ['text' => $translated]);

                return $text;
            }

            return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } catch (Throwable $e) {
            Log::error('Translation failed: '.$e->getMessage());

            return $text;
        }
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
