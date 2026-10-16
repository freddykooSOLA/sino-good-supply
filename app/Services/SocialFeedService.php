<?php

namespace App\Services;

use App\Models\Setting;
use App\Services\Social\YouTubeChannelResolver;
use App\Support\SocialTokenVault;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SocialFeedService
{
    public const CACHE_KEY = 'social.feed.merged.v1';

    public const CACHE_SECONDS = 1800;

    /**
     * @return list<array{platform: string, text: string, image: ?string, link: string, created_at: ?string}>
     */
    public function latest(int $limit = 6): array
    {
        try {
            $posts = Cache::get(self::CACHE_KEY);
            if (is_array($posts) && $posts !== []) {
                return array_slice($posts, 0, $limit);
            }
        } catch (Throwable $e) {
            report($e);
        }

        $cached = Setting::getValue('social_feed', []) ?: [];
        $stored = is_array($cached['posts'] ?? null) ? $cached['posts'] : [];
        if (! empty($stored)) {
            return array_slice($stored, 0, $limit);
        }

        return $this->manualFallback($limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function syncAll(): array
    {
        $merged = collect()
            ->merge($this->syncFacebook())
            ->merge($this->syncInstagram())
            ->merge($this->syncYouTube())
            ->sortByDesc(fn (array $post) => strtotime((string) ($post['created_at'] ?? '1970-01-01')))
            ->values()
            ->take(12)
            ->all();

        Setting::setValue('social_feed', [
            'synced_at' => now()->toIso8601String(),
            'posts' => $merged,
        ]);
        Cache::put(self::CACHE_KEY, $merged, self::CACHE_SECONDS);

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    public function platformConnection(string $platform): array
    {
        $all = Setting::getValue('social_connections', []) ?: [];
        $defaults = [
            'enabled' => false,
            'page_url' => '',
            'access_token' => '',
            'channel_url' => '',
            'ig_user_id' => '',
            'last_synced_at' => null,
            'last_error' => null,
        ];

        return array_merge($defaults, is_array($all[$platform] ?? null) ? $all[$platform] : []);
    }

    /**
     * @param  array<string, mixed>  $incoming
     */
    public function persistConnection(string $platform, array $incoming): void
    {
        $all = Setting::getValue('social_connections', []) ?: [];
        $existing = is_array($all[$platform] ?? null) ? $all[$platform] : [];
        $token = trim((string) ($incoming['access_token'] ?? ''));
        if ($token === '') {
            $token = SocialTokenVault::decrypt($existing['access_token'] ?? '');
        } else {
            $token = SocialTokenVault::encrypt($token);
        }

        $all[$platform] = array_merge($existing, [
            'enabled' => (bool) ($incoming['enabled'] ?? false),
            'page_url' => trim((string) ($incoming['page_url'] ?? $incoming['channel_url'] ?? '')),
            'channel_url' => trim((string) ($incoming['channel_url'] ?? $incoming['page_url'] ?? '')),
            'access_token' => $token,
            'ig_user_id' => trim((string) ($incoming['ig_user_id'] ?? $existing['ig_user_id'] ?? '')),
            'last_synced_at' => $existing['last_synced_at'] ?? null,
            'last_error' => $existing['last_error'] ?? null,
        ]);

        Setting::setValue('social_connections', $all);

        if ($platform === 'facebook') {
            $plainToken = SocialTokenVault::decrypt($all[$platform]['access_token'] ?? '');
            Setting::setValue('facebook_connection', array_merge(Setting::getValue('facebook_connection', []) ?: [], [
                'enabled' => (bool) ($all[$platform]['enabled'] ?? false),
                'page_url' => $all[$platform]['page_url'] ?? '',
                'access_token' => $plainToken,
            ]));
            app(FacebookFeedService::class)->forgetCache();
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function recordError(string $platform, string $message): void
    {
        $all = Setting::getValue('social_connections', []) ?: [];
        $conn = $this->platformConnection($platform);
        $conn['last_error'] = $message;
        $all[$platform] = array_merge($this->platformConnection($platform), $conn);
        Setting::setValue('social_connections', $all);
    }

    public function markSynced(string $platform): void
    {
        $all = Setting::getValue('social_connections', []) ?: [];
        $conn = $this->platformConnection($platform);
        $conn['last_synced_at'] = now()->toIso8601String();
        $conn['last_error'] = null;
        $all[$platform] = $conn;
        Setting::setValue('social_connections', $all);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function syncFacebook(): array
    {
        $facebook = app(FacebookFeedService::class);
        $connection = $facebook->connection();
        if (empty($connection['enabled']) || ! filled($connection['access_token'])) {
            return [];
        }

        try {
            $posts = $facebook->sync();

            return collect($posts)->map(fn (array $post) => [
                'platform' => 'facebook',
                'text' => $post['text'] ?? '',
                'image' => $post['image'] ?? null,
                'link' => $post['link'] ?? '',
                'created_at' => $post['created_at'] ?? null,
            ])->all();
        } catch (Throwable $e) {
            $this->recordError('facebook', $e->getMessage());

            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function syncInstagram(): array
    {
        $conn = $this->platformConnection('instagram');
        if (empty($conn['enabled'])) {
            return [];
        }

        $token = SocialTokenVault::decrypt($conn['access_token'] ?? '');
        if ($token === '') {
            $token = SocialTokenVault::decrypt($this->platformConnection('facebook')['access_token'] ?? '');
        }

        if ($token === '') {
            return [];
        }

        try {
            $igUserId = $conn['ig_user_id'] ?? '';
            if ($igUserId === '') {
                $pageId = app(FacebookFeedService::class)->connection()['page_id'] ?? null;
                if (! $pageId) {
                    throw new \RuntimeException('Instagram 需要 Facebook 主页 ID 或填写 IG User ID。');
                }
                $page = Http::timeout(10)->get($this->graphUrl($pageId), [
                    'fields' => 'instagram_business_account',
                    'access_token' => $token,
                ])->json();
                $igUserId = (string) ($page['instagram_business_account']['id'] ?? '');
            }

            if ($igUserId === '') {
                throw new \RuntimeException('未找到 Instagram Business 帐号。');
            }

            $response = Http::timeout(12)->get($this->graphUrl($igUserId.'/media'), [
                'fields' => 'caption,media_url,thumbnail_url,permalink,timestamp,media_type',
                'limit' => 8,
                'access_token' => $token,
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Instagram API 错误。');
            }

            $this->markSynced('instagram');

            return collect($response->json('data') ?? [])
                ->map(function (array $item) {
                    $image = $item['thumbnail_url'] ?? $item['media_url'] ?? null;

                    return [
                        'platform' => 'instagram',
                        'text' => Str::limit((string) ($item['caption'] ?? ''), 280),
                        'image' => $image,
                        'link' => (string) ($item['permalink'] ?? ''),
                        'created_at' => $item['timestamp'] ?? null,
                    ];
                })
                ->filter(fn (array $post) => filled($post['link']))
                ->values()
                ->all();
        } catch (Throwable $e) {
            $this->recordError('instagram', $e->getMessage());

            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function syncYouTube(): array
    {
        $conn = $this->platformConnection('youtube');
        if (empty($conn['enabled'])) {
            return [];
        }

        $channelInput = trim((string) ($conn['channel_url'] ?? $conn['page_url'] ?? ''));
        if ($channelInput === '') {
            return [];
        }

        try {
            $channelId = app(YouTubeChannelResolver::class)->resolveChannelId($channelInput);
            if (! $channelId) {
                throw new \RuntimeException('无法解析 YouTube 频道 ID。');
            }

            $xml = Http::timeout(12)->get('https://www.youtube.com/feeds/videos.xml', [
                'channel_id' => $channelId,
            ])->body();

            $feed = simplexml_load_string($xml);
            if ($feed === false) {
                throw new \RuntimeException('YouTube RSS 解析失败。');
            }

            $this->markSynced('youtube');
            $posts = [];
            foreach ($feed->entry as $entry) {
                $media = $entry->children('http://search.yahoo.com/mrss/');
                $thumbnail = (string) ($media->group->thumbnail->attributes()->url ?? '');
                $link = '';
                foreach ($entry->link as $linkNode) {
                    $rel = (string) ($linkNode->attributes()->rel ?? 'alternate');
                    if ($rel === 'alternate' || $link === '') {
                        $link = (string) ($linkNode->attributes()->href ?? '');
                    }
                }
                $posts[] = [
                    'platform' => 'youtube',
                    'text' => Str::limit((string) $entry->title, 280),
                    'image' => $thumbnail ?: null,
                    'link' => $link,
                    'created_at' => (string) $entry->published,
                ];
            }

            return array_slice($posts, 0, 8);
        } catch (Throwable $e) {
            $this->recordError('youtube', $e->getMessage());

            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function manualFallback(int $limit): array
    {
        $manual = Setting::getValue('facebook_posts', []) ?: [];

        return collect($manual)->map(fn (array $post) => [
            'platform' => 'manual',
            'text' => localized_setting($post, 'text') ?: ($post['text'] ?? ''),
            'image' => $post['image'] ?? null,
            'link' => $post['link'] ?? '#',
            'created_at' => $post['created_at'] ?? null,
        ])->take($limit)->values()->all();
    }

    protected function graphUrl(string $path): string
    {
        $version = config('services.facebook.graph_version', 'v21.0');

        return 'https://graph.facebook.com/'.$version.'/'.ltrim($path, '/');
    }
}
