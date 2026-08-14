<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedIfMissing('homepage_headings', [
            'core_business' => [
                'title_en' => 'What We Do',
                'title_zh' => '主要业务',
                'title_zh_hant' => '主要業務',
                'subtitle_en' => 'High-quality customized service solutions — not a simple product catalogue.',
                'subtitle_zh' => '提供高质定制服务方案，而非单纯卖产品。',
                'subtitle_zh_hant' => '提供高質定制服務方案，而非單純賣產品。',
            ],
            'featured_cases' => [
                'title_en' => 'Selected Projects',
                'title_zh' => '精选案例',
                'title_zh_hant' => '精選案例',
                'subtitle_en' => 'Hospitality projects delivered with SINO GOOD customized supply solutions.',
                'subtitle_zh' => '以定制服务方案交付的酒店项目。',
                'subtitle_zh_hant' => '以定制服務方案交付的酒店項目。',
            ],
            'order_process' => [
                'title_en' => 'Order Process',
                'title_zh' => '落单流程',
                'title_zh_hant' => '落單流程',
                'subtitle_en' => 'From briefing to delivery — a clear, project-led workflow.',
                'subtitle_zh' => '从需求沟通到交付，清晰的项目化工作流程。',
                'subtitle_zh_hant' => '從需求溝通到交付，清晰的項目化工作流程。',
            ],
            'advantages' => [
                'title_en' => 'Why SINO GOOD',
                'title_zh' => '为什么选择 SINO GOOD',
                'title_zh_hant' => '為什麼選擇 SINO GOOD',
            ],
            'product_categories' => [
                'title_en' => 'Product Categories',
                'title_zh' => '产品类别',
                'title_zh_hant' => '產品類別',
                'subtitle_en' => 'Explore series by category. Each series is a curated solution, not a single SKU.',
                'subtitle_zh' => '按类别浏览系列。每个系列都是一套方案，而非单一产品。',
                'subtitle_zh_hant' => '按類別瀏覽系列。每個系列都是一套方案，而非單一產品。',
            ],
            'facebook_posts' => [
                'title_en' => 'Latest on Facebook',
                'title_zh' => 'Facebook 最新发布',
                'title_zh_hant' => 'Facebook 最新發布',
            ],
            'latest_news' => [
                'title_en' => 'Latest Updates',
                'title_zh' => '最新动向',
                'title_zh_hant' => '最新動向',
            ],
        ]);

        $this->seedIfMissing('core_business', [
            [
                'title_en' => 'Customized Project Solutions',
                'title_zh' => '高质定制服务方案',
                'title_zh_hant' => '高質定制服務方案',
                'description_en' => 'We design complete material schemes for hotel projects — from briefing to installation-ready delivery.',
                'description_zh' => '为酒店项目量身设计完整材料方案，从需求沟通到可安装交付。',
                'description_zh_hant' => '為酒店項目量身設計完整材料方案，從需求溝通到可安裝交付。',
            ],
            [
                'title_en' => 'One-stop Sourcing',
                'title_zh' => '一站式采购',
                'title_zh_hant' => '一站式採購',
                'description_en' => 'Hotel supplies, swimming pools, wall panels, JW custom profiles, bathroom products, and furniture — coordinated as one programme.',
                'description_zh' => '酒店用品、游泳池、墙板、JW 定制型材、卫浴与家具，作为同一套方案统筹。',
                'description_zh_hant' => '酒店用品、游泳池、牆板、JW 定制型材、衛浴與家具，作為同一套方案統籌。',
            ],
            [
                'title_en' => 'Quality, Logistics & Care',
                'title_zh' => '品质、物流与跟进',
                'title_zh_hant' => '品質、物流與跟進',
                'description_en' => 'Factory QC, export logistics and timeline control built around European hospitality projects.',
                'description_zh' => '围绕欧洲酒店工期的工厂质检、出口物流与进度管控。',
                'description_zh_hant' => '圍繞歐洲酒店工期的工廠質檢、出口物流與進度管控。',
            ],
        ]);

        $this->seedIfMissing('advantages', [
            [
                'title_en' => 'Quality Control',
                'title_zh' => '品质管控',
                'title_zh_hant' => '品質管控',
                'description_en' => 'Rigorous inspection across the supply chain for hotel-grade standards.',
                'description_zh' => '全链路严格质检，符合酒店级标准。',
                'description_zh_hant' => '全鏈路嚴格質檢，符合酒店級標準。',
            ],
            [
                'title_en' => 'Logistics',
                'title_zh' => '物流配送',
                'title_zh_hant' => '物流配送',
                'description_en' => 'Reliable export logistics tailored for European hotel timelines.',
                'description_zh' => '面向欧洲酒店工期的可靠出口物流。',
                'description_zh_hant' => '面向歐洲酒店工期的可靠出口物流。',
            ],
            [
                'title_en' => 'Customized Solutions',
                'title_zh' => '定制服务方案',
                'title_zh_hant' => '定制服務方案',
                'description_en' => 'JW custom profiles and tailored service schemes — we do not simply sell products.',
                'description_zh' => 'JW 定制型材与项目专属服务方案，而非单纯卖产品。',
                'description_zh_hant' => 'JW 定制型材與項目專屬服務方案，而非單純賣產品。',
            ],
            [
                'title_en' => '28 Years',
                'title_zh' => '28 年经验',
                'title_zh_hant' => '28 年經驗',
                'description_en' => 'Decades of experience serving hospitality projects across Europe.',
                'description_zh' => '深耕欧洲酒店项目数十年。',
                'description_zh_hant' => '深耕歐洲酒店項目數十年。',
            ],
        ]);

        $this->seedIfMissing('order_process', [
            'flowchart_image' => null,
            'steps' => [
                [
                    'step' => 1,
                    'title_en' => 'Briefing',
                    'title_zh' => '需求沟通',
                    'title_zh_hant' => '需求溝通',
                    'description_en' => 'Share drawings, mood boards and project constraints.',
                    'description_zh' => '沟通图纸、氛围板与项目限制条件。',
                    'description_zh_hant' => '溝通圖紙、氛圍板與項目限制條件。',
                ],
                [
                    'step' => 2,
                    'title_en' => 'Solution Design',
                    'title_zh' => '方案设计',
                    'title_zh_hant' => '方案設計',
                    'description_en' => 'We propose a customized series mix rather than a generic SKU list.',
                    'description_zh' => '以定制系列组合提案，而不是一份通用货号清单。',
                    'description_zh_hant' => '以定制系列組合提案，而不是一份通用貨號清單。',
                ],
                [
                    'step' => 3,
                    'title_en' => 'Sampling',
                    'title_zh' => '样品确认',
                    'title_zh_hant' => '樣品確認',
                    'description_en' => 'Confirm materials, finishes and details before production.',
                    'description_zh' => '投产前确认材料、表面处理与细节。',
                    'description_zh_hant' => '投產前確認材料、表面處理與細節。',
                ],
                [
                    'step' => 4,
                    'title_en' => 'Production',
                    'title_zh' => '生产制造',
                    'title_zh_hant' => '生產製造',
                    'description_en' => 'Manufacture against the approved scheme and timeline.',
                    'description_zh' => '按确认方案与工期组织生产。',
                    'description_zh_hant' => '按確認方案與工期組織生產。',
                ],
                [
                    'step' => 5,
                    'title_en' => 'Quality Control',
                    'title_zh' => '品质检验',
                    'title_zh_hant' => '品質檢驗',
                    'description_en' => 'Factory inspection before packing and shipment.',
                    'description_zh' => '包装出货前进行工厂检验。',
                    'description_zh_hant' => '包裝出貨前進行工廠檢驗。',
                ],
                [
                    'step' => 6,
                    'title_en' => 'Delivery',
                    'title_zh' => '物流交付',
                    'title_zh_hant' => '物流交付',
                    'description_en' => 'Export logistics coordinated to the hotel programme.',
                    'description_zh' => '按酒店项目计划安排出口物流。',
                    'description_zh_hant' => '按酒店項目計劃安排出口物流。',
                ],
            ],
        ]);

        $this->seedIfMissing('featured_cases', []);
        $this->seedIfMissing('facebook_posts', []);
        $this->seedIfMissing('latest_news', []);
        $this->seedIfMissing('visible_category_ids', []);

        $this->seedIfMissing('about_content', [
            'about_intro_en' => 'SINO GOOD QY Supply Chain CO LTD provides high-quality customized service solutions for Europe\'s finest hotels. We are not merely a product vendor.',
            'about_intro_zh' => 'SINO GOOD 启扬供应链有限公司为欧洲精品酒店提供高质定制服务方案。我们不是单纯的产品销售商。',
            'about_intro_zh_hant' => 'SINO GOOD 啟揚供應鏈有限公司為歐洲精品酒店提供高質定制服務方案。我們不是單純的產品銷售商。',
            'about_body_en' => '<p>From briefing to installation-ready delivery, our team designs, sources and manages complete material programmes covering hotel supplies, swimming pools, wall panels, JW custom profiles, bathroom products, and indoor/outdoor furniture.</p><p>Quality control, logistics and project-specific customization sit under one roof — so owners and operators receive a coordinated service scheme, not a pile of unrelated products.</p>',
            'about_body_zh' => '<p>从需求沟通到可安装交付，团队一站式完成酒店用品、游泳池、墙板、JW 定制型材、卫浴产品及室内外家具的方案设计、采购与项目管理。</p><p>品质管控、物流与项目定制集于一体，业主与运营商获得的是一套协同服务方案，而不是互不相关的产品堆砌。</p>',
            'about_body_zh_hant' => '<p>從需求溝通到可安裝交付，團隊一站式完成酒店用品、游泳池、牆板、JW 定制型材、衛浴產品及室內外家具的方案設計、採購與項目管理。</p><p>品質管控、物流與項目定制集於一體，業主與運營商獲得的是一套協同服務方案，而不是互不相關的產品堆砌。</p>',
        ]);
    }

    protected function seedIfMissing(string $key, mixed $value): void
    {
        if (Setting::query()->where('key', $key)->exists()) {
            return;
        }

        Setting::setValue($key, $value);
    }
}
