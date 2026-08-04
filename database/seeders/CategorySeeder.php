<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name_en' => 'Hotel Supplies',
                'name_zh' => '酒店用品',
                'name_zh_hant' => '酒店用品',
                'slug_en' => 'hotel-supplies',
                'slug_zh' => 'jiu-dian-yong-pin',
                'slug_zh_hant' => 'jiu-dian-yong-pin',
                'sort_order' => 1,
            ],
            [
                'name_en' => 'Swimming Pool',
                'name_zh' => '游泳池',
                'name_zh_hant' => '游泳池',
                'slug_en' => 'swimming-pool',
                'slug_zh' => 'you-yong-chi',
                'slug_zh_hant' => 'you-yong-chi',
                'sort_order' => 2,
            ],
            [
                'name_en' => 'Wall Panels',
                'name_zh' => '墙板',
                'name_zh_hant' => '牆板',
                'slug_en' => 'wall-panels',
                'slug_zh' => 'qiang-ban',
                'slug_zh_hant' => 'qiang-ban',
                'sort_order' => 3,
            ],
            [
                'name_en' => 'JW Custom Profiles',
                'name_zh' => 'JW定制型材',
                'name_zh_hant' => 'JW定制型材',
                'slug_en' => 'jw-custom-profiles',
                'slug_zh' => 'jw-ding-zhi-xing-cai',
                'slug_zh_hant' => 'jw-ding-zhi-xing-cai',
                'sort_order' => 4,
            ],
            [
                'name_en' => 'Bathroom Products',
                'name_zh' => '卫浴产品',
                'name_zh_hant' => '衛浴產品',
                'slug_en' => 'bathroom-products',
                'slug_zh' => 'wei-yu-chan-pin',
                'slug_zh_hant' => 'wei-yu-chan-pin',
                'sort_order' => 5,
            ],
            [
                'name_en' => 'Indoor & Outdoor Furniture',
                'name_zh' => '室内外家具',
                'name_zh_hant' => '室內外家具',
                'slug_en' => 'indoor-outdoor-furniture',
                'slug_zh' => 'shi-nei-wai-jia-ju',
                'slug_zh_hant' => 'shi-nei-wai-jia-ju',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug_en' => $category['slug_en']],
                array_merge($category, ['is_active' => true])
            );
        }
    }
}
