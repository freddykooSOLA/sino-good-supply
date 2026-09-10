<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('hero_slides', [
            [
                'image' => null,
                'title_en' => 'SINO GOOD',
                'subtitle_en' => 'High-quality customized service solutions for Europe\'s finest hotels — we don\'t just sell products.',
                'subtitle_zh' => '为欧洲精品酒店提供高质定制服务方案，而非单纯卖产品。',
                'subtitle_zh_hant' => '為歐洲精品酒店提供高質定制服務方案，而非單純賣產品。',
                'button_text_en' => 'Explore Series',
                'button_text_zh' => '浏览系列',
                'button_text_zh_hant' => '瀏覽系列',
                'link' => '/en/category/hotel-supplies',
            ],
        ]);

        Setting::setValue('hero', [
            'autoplay' => true,
            'speed' => 5000,
        ]);

        Setting::setValue('page_heroes', [
            'products' => [
                'image' => null,
                'title_en' => 'Series',
                'title_zh' => '系列',
                'title_zh_hant' => '系列',
                'subtitle_en' => 'High-quality customized service solutions for Europe\'s finest hotels — we don\'t just sell products.',
                'subtitle_zh' => '为欧洲精品酒店提供高质定制服务方案，而非单纯卖产品。',
                'subtitle_zh_hant' => '為歐洲精品酒店提供高質定制服務方案，而非單純賣產品。',
            ],
            'cases' => [
                'image' => null,
                'title_en' => 'Case Studies',
                'title_zh' => '案例',
                'title_zh_hant' => '案例',
                'subtitle_en' => 'Selected hotel and hospitality projects delivered with SINO GOOD customized supply solutions.',
                'subtitle_zh' => '精选酒店项目案例，展示 SINO GOOD 高质定制服务方案。',
                'subtitle_zh_hant' => '精選酒店項目案例，展示 SINO GOOD 高質定制服務方案。',
            ],
            'about' => [
                'image' => null,
                'title_en' => 'About SINO GOOD',
                'title_zh' => '关于我们',
                'title_zh_hant' => '關於我們',
                'subtitle_en' => 'SINO GOOD provides high-quality customized service solutions for European hotel projects.',
                'subtitle_zh' => 'SINO GOOD 为欧洲酒店项目提供高质定制服务方案。',
                'subtitle_zh_hant' => 'SINO GOOD 為歐洲酒店項目提供高質定制服務方案。',
            ],
            'contact' => [
                'image' => null,
                'title_en' => 'Get in Touch',
                'title_zh' => '联系我们',
                'title_zh_hant' => '聯絡我們',
                'subtitle_en' => 'Tell us about your hotel project — we will propose a customized service scheme.',
                'subtitle_zh' => '告诉我们您的酒店项目，我们将提出定制服务方案。',
                'subtitle_zh_hant' => '告訴我們您的酒店項目，我們將提出定制服務方案。',
            ],
            'order_process' => [
                'image' => null,
                'title_en' => 'Order Process',
                'title_zh' => '落单流程',
                'title_zh_hant' => '落單流程',
                'subtitle_en' => 'From briefing to delivery — a clear, project-led workflow.',
                'subtitle_zh' => '从需求沟通到交付，清晰的项目化工作流程。',
                'subtitle_zh_hant' => '從需求溝通到交付，清晰的項目化工作流程。',
            ],
        ]);

        Setting::setValue('company_stats', [
            ['label_en' => 'Years of Experience', 'label_zh' => '年行业经验', 'label_zh_hant' => '年行業經驗', 'value' => '28'],
            ['label_en' => 'Hotels Served', 'label_zh' => '服务酒店', 'label_zh_hant' => '服務酒店', 'value' => '500+'],
            ['label_en' => 'Product Categories', 'label_zh' => '产品品类', 'label_zh_hant' => '產品品類', 'value' => '6'],
            ['label_en' => 'European Markets', 'label_zh' => '欧洲市场', 'label_zh_hant' => '歐洲市場', 'value' => '20+'],
        ]);

        Setting::setValue('contact', [
            'company_en' => 'SINO GOOD QY SUPPLY CHAIN CO., LTD',
            'company_zh' => '佛山兰悦君华贸易有限公司',
            'company_zh_hant' => '佛山蘭悅君華貿易有限公司',
            'address_en' => 'RM311, TOWER 4, HUACUI ROAD NO.26, H-T ECH CENTER',
            'address_zh' => '佛山市南海区桂城街道华翠南路26号汇泰创投中心4座3楼311',
            'address_zh_hant' => '佛山市南海區桂城街道華翠南路26號匯泰創投中心4座3樓311',
            'phone' => '+86 138 8993 1011',
            'email' => 'info@sinogood.com',
            'whatsapp' => '+1 370 244 6468',
            'wechat' => '',
            'facebook_url' => 'https://www.facebook.com/search/top?q=SINO+GOOD+QY+SUPPLY+CHAIN',
            'instagram_url' => 'https://www.instagram.com/sino.good/',
            'youtube_url' => 'https://www.youtube.com/@Fionlam1105',
            'media_type' => 'none',
            'media_image' => null,
            'google_maps_embed' => '',
        ]);

        Setting::setValue('brand_logos', []);
    }
}
