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
        $this->get('/zh/order-process')->assertOk()->assertSee('SINO 怎么做', false);
        $this->get('/zh-hant/about')->assertOk();
    }

    public function test_contact_info_displays_formal_details(): void
    {
        foreach (['en', 'zh', 'zh-hant'] as $locale) {
            $this->get("/{$locale}/contact")
                ->assertOk()
                ->assertSee('+86 138 8993 1011', false)
                ->assertSee('wa.me/13702446468', false)
                ->assertSee('instagram.com/sino.good', false)
                ->assertDontSee('+86 0000 0000', false);
        }

        $this->get('/en/contact')
            ->assertSee('SINO GOOD QY SUPPLY CHAIN CO., LTD', false)
            ->assertSee('RM311, TOWER 4', false);

        $this->get('/zh/contact')
            ->assertSee('佛山兰悦君华贸易有限公司', false)
            ->assertSee('佛山市南海区桂城街道', false);

        $this->get('/en/cases')->assertOk()->assertDontSee('+86 0000 0000', false);
        $this->get('/en')->assertOk()->assertSee('wa.me/13702446468', false);
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

    public function test_series_pdf_is_served_when_file_exists(): void
    {
        $category = Category::query()->firstOrFail();
        $path = 'series-pdfs/preview-test.pdf';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, $this->minimalPdf());

        Series::query()->create([
            'category_id' => $category->id,
            'name_en' => 'With Pdf',
            'name_zh' => '有PDF',
            'name_zh_hant' => '有PDF',
            'slug_en' => 'with-pdf',
            'slug_zh' => 'with-pdf',
            'slug_zh_hant' => 'with-pdf',
            'pdf_path' => $path,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->get('/en/series/with-pdf')
            ->assertOk()
            ->assertSee('data-pdf-preview', false);

        $this->get('/en/series/with-pdf/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_series_pdf_path_stored_as_json_array_still_resolves(): void
    {
        $category = Category::query()->firstOrFail();
        $path = 'series-pdfs/json-path.pdf';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, $this->minimalPdf());

        $series = Series::query()->create([
            'category_id' => $category->id,
            'name_en' => 'Json Pdf',
            'name_zh' => 'JSON PDF',
            'name_zh_hant' => 'JSON PDF',
            'slug_en' => 'json-pdf',
            'slug_zh' => 'json-pdf',
            'slug_zh_hant' => 'json-pdf',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        \Illuminate\Support\Facades\DB::table('series')->where('id', $series->id)->update([
            'pdf_path' => json_encode([$path]),
        ]);

        $this->assertTrue($series->fresh()->hasPdf());
        $this->get('/en/series/json-pdf/pdf')->assertOk();
    }

    public function test_homepage_news_slider_renders_images(): void
    {
        \App\Models\Setting::setValue('latest_news', [
            [
                'date' => '2026-08-15',
                'title_en' => 'Factory visit',
                'title_zh' => '工厂参观',
                'title_zh_hant' => '工廠參觀',
                'excerpt_en' => 'New showroom photos.',
                'images' => ['homepage/news/demo.jpg'],
            ],
        ]);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Factory visit')
            ->assertSee('homepage/news/demo.jpg', false);
    }

    public function test_series_images_can_skip_watermark(): void
    {
        $category = Category::query()->firstOrFail();
        \Illuminate\Support\Facades\Storage::disk('public')->put('series/plain.jpg', $this->tinyJpeg());

        $series = Series::query()->create([
            'category_id' => $category->id,
            'name_en' => 'No Watermark Photos',
            'name_zh' => '无水印照片',
            'name_zh_hant' => '無水印照片',
            'slug_en' => 'no-watermark-photos',
            'slug_zh' => 'no-watermark-photos',
            'slug_zh_hant' => 'no-watermark-photos',
            'images' => ['series/plain.jpg'],
            'watermark_images' => false,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $url = $series->imageUrls()[0] ?? '';
        $this->assertStringContainsString('/storage/series/plain.jpg', $url);
        $this->assertStringNotContainsString('/media/wm/', $url);
    }

    public function test_watermark_settings_exist(): void
    {
        $this->assertNotNull(WatermarkSetting::query()->first());
    }

    protected function minimalPdf(): string
    {
        return "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\nxref\n0 4\n0000000000 65535 f \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n196\n%%EOF";
    }

    protected function tinyJpeg(): string
    {
        return 'fake-image';
    }
}
