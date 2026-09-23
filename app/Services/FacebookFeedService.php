<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class FacebookFeedService
{
    public const CACHE_KEY = 'facebook.feed.v1';

    public const CACHE_SECONDS = 3600;

    /**
     * @return list<array{id?: string, text: string, image: ?string, link: string, created_at?: ?string}>
     */
    public function latest(int $limit = 3): array
    {
        $connection = $this->connection();
        $auto = ! empty($connection['enabled']) && filled($connection['page_url'] ?? null);

        if ($auto) {
            if (filled($connection['access_token'] ?? null)) {
                try {
                    $posts = Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => $this->sync());

                    if (! empty($posts)) {
                        return array_slice($posts, 0, $limit);
                    }
                } catch (Throwable $e) {
                    report($e);
                    $this->recordError($e->getMessage());
                }
            }

            $cached = Setting::getValue('facebook_feed', []) ?: [];
            $feedPosts = is_array($cached['posts'] ?? null) ? $cached['posts'] : [];

            if (! empty($feedPosts)) {
                return array_slice($feedPosts, 0, $limit);
            }
        }

        return array_slice(Setting::getValue('facebook_posts', []) ?: [], 0, $limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function sync(): array
    {
        $connection = $this->connection();
        $token = trim((string) ($connection['access_token'] ?? ''));
        $pageUrl = trim((string) ($connection['page_url'] ?? ''));

        if ($token === '' || $pageUrl === '') {
            throw new \RuntimeException('请先填写 Facebook 主页链接和 Page Access Token。');
        }

        $pageId = $this->resolvePageId($pageUrl, $token);
        $posts = $this->fetchPosts($pageId, $token, 8);

        Setting::setValue('facebook_connection', array_merge($connection, [
            'page_id' => $pageId,
            'last_synced_at' => now()->toIso8601String(),
            'last_error' => null,
        ]));
        Setting::setValue('facebook_feed', [
            'synced_at' => now()->toIso8601String(),
            'posts' => $posts,
        ]);
        Cache::put(self::CACHE_KEY, $posts, self::CACHE_SECONDS);

        return $posts;
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function recordError(string $message): void
    {
        Setting::setValue('facebook_connection', array_merge($this->connection(), [
            'last_error' => $message,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function connection(): array
    {
        $stored = Setting::getValue('facebook_connection', []) ?: [];
        $merged = array_merge([
            'enabled' => true,
            'page_url' => '',
            'page_id' => '',
            'access_token' => '',
            'last_synced_at' => null,
            'last_error' => null,
        ], is_array($stored) ? $stored : []);

        if (! filled($merged['page_url'])) {
            $merged['page_url'] = config('services.facebook.page_url') ?: '';
        }

        if (! filled($merged['access_token'])) {
            $merged['access_token'] = config('services.facebook.access_token') ?: '';
        }

        if (! filled($merged['access_token'])) {
            $appId = config('services.facebook.app_id') ?: '';
            $appSecret = config('services.facebook.app_secret') ?: '';
            if ($appId !== '' && $appSecret !== '') {
                $merged['access_token'] = $appId.'|'.$appSecret;
            }
        }

        return $merged;
    }

    public function embedUrl(): ?string
    {
        $connection = $this->connection();

        if (empty($connection['enabled'])) {
            return null;
        }

        $url = trim((string) ($connection['page_url'] ?? ''));

        if ($url === '') {
            return null;
        }

        $identifier = $this->parsePageIdentifier($url);

        if (! $identifier) {
            return str_starts_with($url, 'http') ? $url : null;
        }

        return 'https://www.facebook.com/'.$identifier;
    }

    public function pagePluginSrc(): ?string
    {
        $href = $this->embedUrl();

        if (! $href) {
            return null;
        }

        $locale = match (app()->getLocale()) {
            'zh' => 'zh_CN',
            'zh-hant' => 'zh_TW',
            default => 'en_US',
        };

        return 'https://www.facebook.com/plugins/page.php?'.http_build_query([
            'href' => $href,
            'tabs' => 'timeline',
            'width' => 500,
            'height' => 720,
            'small_header' => 'false',
            'adapt_container_width' => 'true',
            'hide_cover' => 'false',
            'show_facepile' => 'false',
            'locale' => $locale,
        ]);
    }

    public function parsePageIdentifier(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $url)) {
            return $url;
        }

        if (! str_contains($url, 'http')) {
            $url = 'https://www.facebook.com/'.ltrim($url, '/');
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (! empty($query['id']) && preg_match('/^\d+$/', (string) $query['id'])) {
            return (string) $query['id'];
        }

        if (preg_match('#^pages/[^/]+/(\d+)$#', $path, $matches)) {
            return $matches[1];
        }

        if (preg_match('#^people/[^/]+/(\d+)$#', $path, $matches)) {
            return $matches[1];
        }

        $first = explode('/', $path)[0] ?? '';
        $reserved = ['pages', 'watch', 'reel', 'share', 'groups', 'events', 'profile.php', 'login', 'help'];

        if ($first === '' || in_array($first, $reserved, true)) {
            return null;
        }

        return $first;
    }

    protected function resolvePageId(string $pageUrl, string $token): string
    {
        $identifier = $this->parsePageIdentifier($pageUrl);

        if (! $identifier) {
            throw new \RuntimeException('无法从 Facebook 主页链接解析主页 ID。');
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->get($this->graphUrl($identifier), [
                'fields' => 'id,name,link',
                'access_token' => $token,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException($this->graphError($response->json(), '无法读取 Facebook 主页，请检查链接和 Token。'));
        }

        $id = (string) ($response->json('id') ?? '');

        if ($id === '') {
            throw new \RuntimeException('Facebook 未返回主页 ID。');
        }

        return $id;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function fetchPosts(string $pageId, string $token, int $limit): array
    {
        $response = Http::timeout(10)
            ->acceptJson()
            ->get($this->graphUrl($pageId.'/published_posts'), [
                'fields' => 'id,message,story,created_time,full_picture,permalink_url,attachments{media,type,url,subattachments}',
                'limit' => max(3, $limit),
                'access_token' => $token,
            ]);

        if ($response->failed() || empty($response->json('data'))) {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($this->graphUrl($pageId.'/posts'), [
                    'fields' => 'id,message,story,created_time,full_picture,permalink_url,attachments{media,type,url,subattachments}',
                    'limit' => max(3, $limit),
                    'access_token' => $token,
                ]);
        }

        if ($response->failed()) {
            throw new \RuntimeException($this->graphError($response->json(), '无法读取 Facebook 帖子。'));
        }

        $items = $response->json('data') ?? [];

        return collect(is_array($items) ? $items : [])
            ->map(fn (array $post) => $this->normalizePost($post))
            ->filter(fn (array $post) => filled($post['link']) && (filled($post['text']) || filled($post['image'])))
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array{id: string, text: string, image: ?string, link: string, created_at: ?string}
     */
    protected function normalizePost(array $post): array
    {
        $text = trim((string) ($post['message'] ?? $post['story'] ?? ''));
        $image = $post['full_picture'] ?? null;

        if (! $image) {
            $attachment = $post['attachments']['data'][0] ?? [];
            $image = $attachment['media']['image']['src']
                ?? $attachment['subattachments']['data'][0]['media']['image']['src']
                ?? null;
        }

        $result = [
            'id' => (string) ($post['id'] ?? ''),
            'text' => Str::limit($text, 280),
            'image' => $image ? (string) $image : null,
            'link' => (string) ($post['permalink_url'] ?? ''),
            'created_at' => $post['created_time'] ?? null,
        ];

        if ($result['link'] === '' && $result['id'] !== '') {
            $result['link'] = 'https://www.facebook.com/'.$result['id'];
        }

        return $result;
    }

    protected function graphUrl(string $path): string
    {
        $version = config('services.facebook.graph_version', 'v21.0');

        return 'https://graph.facebook.com/'.$version.'/'.ltrim($path, '/');
    }

    /**
     * @param  mixed  $payload
     */
    protected function graphError($payload, string $fallback): string
    {
        if (is_array($payload) && filled($payload['error']['message'] ?? null)) {
            return 'Facebook：'.$payload['error']['message'];
        }

        return $fallback;
    }
}
