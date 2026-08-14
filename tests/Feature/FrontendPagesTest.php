<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Series;
use App\Models\WatermarkSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->seed(\Database\Seeders\SettingSeeder::class);
        $this->seed(\Database\Seeders\HomepageContentSeeder::class);
        $this->seed(\Database\Seeders\WatermarkSettingsSeeder::class);
    }

    public function test_localized_home_pages_are_ok(): void
    {
        foreach (['en', 'zh', 'zh-hant'] as $locale) {
            $this->get("/{$locale}")->assertOk();
        }
    }

    public function test_core_pages_are_ok(): void
    {
        $this->get('/en/about')->assertOk();
        $this->get('/en/contact')->assertOk();
        $this->get('/en/cases')->assertOk();
        $this->get('/en/order-process')->assertOk();
        $this->get('/en/products')->assertOk();
        $this->get('/zh/order-process')->assertOk()->assertSee('落单流程', false);
        $this->get('/zh-hant/about')->assertOk();
    }

    public function test_category_page_lists_series(): void
    {
        $category = Category::query()->where('slug_en', 'hotel-supplies')->firstOrFail();

        $series = Series::query()->create([
            'category_id' => $category->id,
            'name_en' => 'Marble Collection',
            'name_zh' => '大理石系列',
            'name_zh_hant' => '大理石系列',
            'slug_en' => 'marble-collection',
            'slug_zh' => 'marble-collection',
            'slug_zh_hant' => 'marble-collection',
            'intro_en' => 'Custom marble series for hotel lobbies.',
            'intro_zh' => '酒店大堂定制大理石系列。',
            'intro_zh_hant' => '酒店大堂定制大理石系列。',
            'images' => [],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->get('/en/category/hotel-supplies')
            ->assertOk()
            ->assertSee('Marble Collection')
            ->assertDontSee('featured-products-placeholder');

        $this->get('/en/series/marble-collection')
            ->assertOk()
            ->assertSee($series->name_en);

        $this->get('/zh/series/marble-collection')
            ->assertOk()
            ->assertSee('大理石系列');
    }

    public function test_series_pdf_returns_404_without_file(): void
    {
        $category = Category::query()->firstOrFail();

        Series::query()->create([
            'category_id' => $category->id,
            'name_en' => 'No Pdf',
            'name_zh' => '无PDF',
            'name_zh_hant' => '無PDF',
            'slug_en' => 'no-pdf',
            'slug_zh' => 'no-pdf',
            'slug_zh_hant' => 'no-pdf',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->get('/en/series/no-pdf/pdf')->assertNotFound();
    }

    public function test_watermark_settings_exist(): void
    {
        $this->assertNotNull(WatermarkSetting::query()->first());
    }
}
