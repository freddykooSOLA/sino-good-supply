<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\FacebookFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacebookFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\SettingSeeder::class);
        $this->seed(\Database\Seeders\HomepageContentSeeder::class);
    }

    public function test_it_parses_facebook_page_urls(): void
    {
        $service = app(FacebookFeedService::class);

        $this->assertSame('SinoGood', $service->parsePageIdentifier('https://www.facebook.com/SinoGood'));
        $this->assertSame('123456', $service->parsePageIdentifier('https://www.facebook.com/pages/Hotel-Supplies/123456'));
        $this->assertSame('999', $service->parsePageIdentifier('https://www.facebook.com/profile.php?id=999'));
        $this->assertSame('111222', $service->parsePageIdentifier('111222'));
        $this->assertSame('sinogood', $service->parsePageIdentifier('sinogood'));
        $this->assertSame('123456789', $service->parsePageIdentifier('123456789'));
    }

    public function test_homepage_hides_facebook_section_without_token_or_posts(): void
    {
        Setting::setValue('facebook_connection', [
            'enabled' => true,
            'page_url' => '123456789',
            'access_token' => '',
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertDontSee('facebook.com/plugins/page.php', false);
    }

    public function test_homepage_uses_synced_facebook_posts(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::sequence()
                ->push([
                    'id' => '555',
                    'name' => 'Sino Good',
                    'link' => 'https://www.facebook.com/sinogood',
                ])
                ->push([
                    'data' => [
                        [
                            'id' => '555_1',
                            'message' => 'Auto Facebook post from connected page',
                            'full_picture' => 'https://cdn.example.com/fb.jpg',
                            'permalink_url' => 'https://www.facebook.com/sinogood/posts/1',
                            'created_time' => '2026-08-01T00:00:00+0000',
                        ],
                    ],
                ]),
        ]);

        Setting::setValue('facebook_connection', [
            'enabled' => true,
            'page_url' => 'https://www.facebook.com/sinogood',
            'access_token' => 'page-token',
        ]);
        Setting::setValue('facebook_posts', [
            [
                'text_en' => 'Manual fallback post',
                'link' => 'https://www.facebook.com/manual',
            ],
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Auto Facebook post from connected page')
            ->assertSee('md:grid-cols-3', false)
            ->assertDontSee('Manual fallback post');
    }

    public function test_homepage_falls_back_to_manual_posts_when_not_connected(): void
    {
        Setting::setValue('facebook_posts', [
            [
                'text_en' => 'Manual fallback post',
                'link' => 'https://www.facebook.com/manual',
                'image' => null,
            ],
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Manual fallback post');
    }

    public function test_facebook_sync_command_stores_posts(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::sequence()
                ->push([
                    'id' => '555',
                    'name' => 'Sino Good',
                ])
                ->push([
                    'data' => [
                        [
                            'id' => '555_2',
                            'message' => 'Synced by artisan',
                            'permalink_url' => 'https://www.facebook.com/sinogood/posts/2',
                            'created_time' => '2026-08-02T00:00:00+0000',
                        ],
                    ],
                ]),
        ]);

        Setting::setValue('facebook_connection', [
            'enabled' => true,
            'page_url' => 'https://www.facebook.com/sinogood',
            'access_token' => 'page-token',
        ]);

        $this->artisan('facebook:sync')->assertSuccessful();

        $feed = Setting::getValue('facebook_feed', []);
        $this->assertSame('Synced by artisan', $feed['posts'][0]['text'] ?? null);
    }
}
