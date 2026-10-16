<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoGeoSocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_and_llms_and_robots_are_available(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);

        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('#', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap:', false)
            ->assertSee('GPTBot', false)
            ->assertSee('ClaudeBot', false);
    }

    public function test_homepage_includes_canonical_hreflang_and_json_ld(): void
    {
        Setting::setValue('contact', [
            'email' => 'info@sinogood.com',
            'company_en' => 'SINO GOOD',
            'facebook_url' => 'https://www.facebook.com/example',
        ]);

        $response = $this->get('/en');
        $response->assertOk();
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('hreflang="zh"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Organization"', false);
    }

    public function test_faq_page_skips_faq_schema_while_draft(): void
    {
        Setting::setValue('faq_published', false);

        $this->get('/en/faq')
            ->assertOk()
            ->assertDontSee('"@type":"FAQPage"', false);
    }

    public function test_social_sync_command_uses_http_fake_for_youtube(): void
    {
        Cache::flush();
        Http::fake([
            'https://www.youtube.com/feeds/videos.xml*' => Http::response(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015" xmlns:media="http://search.yahoo.com/mrss/">
<entry>
<title>Test video</title>
<link rel="alternate" href="https://www.youtube.com/watch?v=abc"/>
<published>2026-10-01T00:00:00+00:00</published>
<media:group><media:thumbnail url="https://i.ytimg.com/vi/abc/hqdefault.jpg"/></media:group>
</entry>
</feed>
XML, 200),
        ]);

        Setting::setValue('social_connections', [
            'youtube' => [
                'enabled' => true,
                'channel_url' => 'UCxyz1234567890123456789012',
                'access_token' => '',
            ],
        ]);

        $this->artisan('social:sync')->assertSuccessful();

        $feed = Setting::getValue('social_feed', []);
        $this->assertNotEmpty($feed['posts'] ?? []);
    }
}
