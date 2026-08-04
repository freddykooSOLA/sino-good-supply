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
                'subtitle_en' => 'One-stop Building Materials Sourcing Partner for Europe\'s Finest Hotels',
                'button_text_en' => 'Explore Products',
                'link' => '/en/category/hotel-supplies',
            ],
        ]);

        Setting::setValue('company_stats', [
            ['label_en' => 'Years of Experience', 'label_zh' => '年行业经验', 'label_zh_hant' => '年行業經驗', 'value' => '28'],
            ['label_en' => 'Hotels Served', 'label_zh' => '服务酒店', 'label_zh_hant' => '服務酒店', 'value' => '500+'],
            ['label_en' => 'Product Categories', 'label_zh' => '产品品类', 'label_zh_hant' => '產品品類', 'value' => '6'],
            ['label_en' => 'European Markets', 'label_zh' => '欧洲市场', 'label_zh_hant' => '歐洲市場', 'value' => '20+'],
        ]);

        Setting::setValue('contact', [
            'company_en' => 'SINO GOOD QY Supply Chain CO LTD',
            'company_zh' => 'SINO GOOD 启扬供应链有限公司',
            'company_zh_hant' => 'SINO GOOD 啟揚供應鏈有限公司',
            'address_en' => 'China',
            'address_zh' => '中国',
            'address_zh_hant' => '中國',
            'phone' => '+86 0000 0000',
            'email' => 'info@sinogood.com',
            'whatsapp' => '',
            'wechat' => '',
        ]);

        Setting::setValue('brand_logos', []);
    }
}
