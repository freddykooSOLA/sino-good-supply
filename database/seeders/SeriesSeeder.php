<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Series;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class SeriesSeeder extends Seeder
{
    public function run(): void
    {
        $images = $this->ensurePlaceholderImages();
        $pdfPath = $this->ensurePlaceholderPdf();

        $inserted = [];

        foreach ($this->catalogue() as $categorySlug => $items) {
            $category = Category::query()->where('slug_en', $categorySlug)->first();

            if (! $category) {
                throw new \RuntimeException("Category not found: {$categorySlug}");
            }

            $inserted[$categorySlug] = 0;

            foreach ($items as $item) {
                $existing = Series::query()->where('slug_en', $item['slug_en'])->first();

                if ($existing) {
                    $this->command?->warn("Skip existing series: {$item['slug_en']}");

                    continue;
                }

                Series::query()->create([
                    'category_id' => $category->id,
                    'name_en' => $item['name_en'],
                    'name_zh' => $item['name_zh'],
                    'name_zh_hant' => $item['name_zh_hant'],
                    'slug_en' => $item['slug_en'],
                    'slug_zh' => $item['slug_zh'],
                    'slug_zh_hant' => $item['slug_zh_hant'],
                    'intro_en' => $item['intro_en'],
                    'intro_zh' => $item['intro_zh'],
                    'intro_zh_hant' => $item['intro_zh_hant'],
                    'images' => $images,
                    'pdf_path' => $pdfPath,
                    'is_active' => true,
                    'sort_order' => 0,
                ]);

                $inserted[$categorySlug]++;
            }
        }

        foreach ($inserted as $slug => $count) {
            $this->command?->info("{$slug}: inserted {$count} series");
        }

        $this->command?->info('Total inserted: '.array_sum($inserted));
    }

    /**
     * @return array<int, string>
     */
    protected function ensurePlaceholderImages(): array
    {
        $paths = [];
        $labels = ['Guestroom', 'Lobby', 'Spa', 'Terrace', 'Detail'];

        foreach ($labels as $index => $label) {
            $relative = 'series/demo/series-'.($index + 1).'.jpg';

            if (! Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->put($relative, $this->makePlaceholderJpeg($index + 1, $label));
            }

            $paths[] = $relative;
        }

        return $paths;
    }

    protected function ensurePlaceholderPdf(): string
    {
        $relative = 'series-pdfs/catalog-sample.pdf';

        if (! Storage::disk('local')->exists($relative)) {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(20, 24, 20);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 22);
            $pdf->Cell(0, 12, 'SINO GOOD', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Ln(4);
            $pdf->MultiCell(0, 8, "Catalogue sample for hotel project solutions.\nHigh-quality customized service schemes — we don't just sell products.", 0, 'L');
            $pdf->Ln(6);
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 7, 'This watermarked preview is a placeholder catalogue. Replace it in Filament with the actual series PDF.', 0, 'L');
            Storage::disk('local')->put($relative, $pdf->Output('', 'S'));
        }

        return $relative;
    }

    protected function makePlaceholderJpeg(int $number, string $label): string
    {
        $width = 1600;
        $height = 1200;
        $image = imagecreatetruecolor($width, $height);
        $black = imagecolorallocate($image, 26, 26, 26);
        $charcoal = imagecolorallocate($image, 44, 44, 44);
        $gold = imagecolorallocate($image, 201, 168, 76);
        $white = imagecolorallocate($image, 255, 255, 255);

        imagefilledrectangle($image, 0, 0, $width, $height, $black);
        imagefilledrectangle($image, 80, 80, $width - 80, $height - 80, $charcoal);
        imagerectangle($image, 80, 80, $width - 80, $height - 80, $gold);

        $title = 'SINO GOOD';
        $subtitle = sprintf('Series %d  /  %s', $number, $label);
        imagestring($image, 5, 120, 540, $title, $gold);
        imagestring($image, 5, 120, 580, $subtitle, $white);

        ob_start();
        imagejpeg($image, null, 82);
        $binary = ob_get_clean();
        imagedestroy($image);

        return $binary ?: '';
    }

    /**
     * @return array<string, list<array<string, string>>>
     */
    protected function catalogue(): array
    {
        return [
            'hotel-supplies' => [
                [
                    'name_en' => 'Boutique Guestroom Programme',
                    'name_zh' => '精品客房定制方案',
                    'name_zh_hant' => '精品客房定制方案',
                    'slug_en' => 'boutique-guestroom-programme',
                    'slug_zh' => 'jing-pin-ke-fang-ding-zhi-fang-an',
                    'slug_zh_hant' => 'jing-pin-ke-fang-ding-zhi-fang-an',
                    'intro_en' => 'A coordinated guestroom scheme covering amenities, lighting accents and operating supplies. Built as a service programme for European boutique hotels, not a loose product list.',
                    'intro_zh' => '覆盖客房配套、灯光点缀与营运物资的协同方案。面向欧洲精品酒店以服务方案交付，而不是一份零散货号清单。',
                    'intro_zh_hant' => '覆蓋客房配套、燈光點綴與營運物資的協同方案。面向歐洲精品酒店以服務方案交付，而不是一份零散貨號清單。',
                ],
                [
                    'name_en' => 'Lobby & Public Area Collection',
                    'name_zh' => '大堂与公共区域系列',
                    'name_zh_hant' => '大堂與公共區域系列',
                    'slug_en' => 'lobby-public-area-collection',
                    'slug_zh' => 'da-tang-yu-gong-gong-qu-yu-xi-lie',
                    'slug_zh_hant' => 'da-tang-yu-gong-gong-qu-yu-xi-lie',
                    'intro_en' => 'Customized FF&E direction for lobbies, corridors and lounge zones, aligned with brand mood boards and installation timelines.',
                    'intro_zh' => '为大堂、走廊与休息区定制的软装方向，对标品牌氛围板与安装工期。',
                    'intro_zh_hant' => '為大堂、走廊與休息區定制的軟裝方向，對標品牌氛圍板與安裝工期。',
                ],
                [
                    'name_en' => 'Hotel Operating Supplies Series',
                    'name_zh' => '酒店营运物资系列',
                    'name_zh_hant' => '酒店營運物資系列',
                    'slug_en' => 'hotel-operating-supplies-series',
                    'slug_zh' => 'jiu-dian-ying-yun-wu-zi-xi-lie',
                    'slug_zh_hant' => 'jiu-dian-ying-yun-wu-zi-xi-lie',
                    'intro_en' => 'Back-of-house and front-of-house operating supplies packaged as a repeatable hotel programme with QC and export logistics under one roof.',
                    'intro_zh' => '将前后台营运物资打包为可复制的酒店项目方案，品质管控与出口物流一体完成。',
                    'intro_zh_hant' => '將前後台營運物資打包為可複製的酒店項目方案，品質管控與出口物流一體完成。',
                ],
            ],
            'swimming-pool' => [
                [
                    'name_en' => 'Resort Pool Surround Series',
                    'name_zh' => '度假村泳池周边系列',
                    'name_zh_hant' => '度假村泳池周邊系列',
                    'slug_en' => 'resort-pool-surround-series',
                    'slug_zh' => 'du-jia-cun-yong-chi-zhou-bian-xi-lie',
                    'slug_zh_hant' => 'du-jia-cun-yong-chi-zhou-bian-xi-lie',
                    'intro_en' => 'Pool decks, edges and outdoor wet-area finishes specified as one outdoor leisure scheme for resort hotels.',
                    'intro_zh' => '将泳池甲板、收边与户外湿区饰面作为度假酒店的一整套户外休闲方案来配置。',
                    'intro_zh_hant' => '將泳池甲板、收邊與戶外濕區飾面作為度假酒店的一整套戶外休閒方案來配置。',
                ],
                [
                    'name_en' => 'Indoor Leisure Pool Series',
                    'name_zh' => '室内休闲泳池系列',
                    'name_zh_hant' => '室內休閒泳池系列',
                    'slug_en' => 'indoor-leisure-pool-series',
                    'slug_zh' => 'shi-nei-xiu-xian-yong-chi-xi-lie',
                    'slug_zh_hant' => 'shi-nei-xiu-xian-yong-chi-xi-lie',
                    'intro_en' => 'Indoor pool and spa-adjacent finishes tailored to climate control, maintenance and European hotel wellness programmes.',
                    'intro_zh' => '针对温控、维护与欧洲酒店康体项目定制的室内泳池及水疗相邻饰面方案。',
                    'intro_zh_hant' => '針對溫控、維護與歐洲酒店康體項目定制的室內泳池及水療相鄰飾面方案。',
                ],
            ],
            'wall-panels' => [
                [
                    'name_en' => 'Hotel Corridor Panel Series',
                    'name_zh' => '酒店走廊墙板系列',
                    'name_zh_hant' => '酒店走廊牆板系列',
                    'slug_en' => 'hotel-corridor-panel-series',
                    'slug_zh' => 'jiu-dian-zou-lang-qiang-ban-xi-lie',
                    'slug_zh_hant' => 'jiu-dian-zou-lang-qiang-ban-xi-lie',
                    'intro_en' => 'Durable corridor and lift-lobby wall systems designed as a complete interior envelope, including junctions and service access.',
                    'intro_zh' => '走廊与电梯厅墙面系统按完整室内界面设计，含收口与检修需求，而非单块板材销售。',
                    'intro_zh_hant' => '走廊與電梯廳牆面系統按完整室內界面設計，含收口與檢修需求，而非單塊板材銷售。',
                ],
                [
                    'name_en' => 'Feature Wall Collection',
                    'name_zh' => '特色墙面系列',
                    'name_zh_hant' => '特色牆面系列',
                    'slug_en' => 'feature-wall-collection',
                    'slug_zh' => 'te-se-qiang-mian-xi-lie',
                    'slug_zh_hant' => 'te-se-qiang-mian-xi-lie',
                    'intro_en' => 'Statement walls for reception, restaurants and suites, customized to drawings rather than off-the-shelf panels.',
                    'intro_zh' => '接待处、餐厅与套房的特色墙，按图纸定制，而不是现货墙板推销。',
                    'intro_zh_hant' => '接待處、餐廳與套房的特色牆，按圖紙定制，而不是現貨牆板推銷。',
                ],
                [
                    'name_en' => 'Acoustic Interior Panel Series',
                    'name_zh' => '室内声学墙板系列',
                    'name_zh_hant' => '室內聲學牆板系列',
                    'slug_en' => 'acoustic-interior-panel-series',
                    'slug_zh' => 'shi-nei-sheng-xue-qiang-ban-xi-lie',
                    'slug_zh_hant' => 'shi-nei-sheng-xue-qiang-ban-xi-lie',
                    'intro_en' => 'Acoustic and decorative panels coordinated for meeting rooms and guest floors as part of a quieter hotel interior scheme.',
                    'intro_zh' => '将声学与装饰墙板统筹用于会议室与客房楼层，作为更安静酒店室内方案的一部分。',
                    'intro_zh_hant' => '將聲學與裝飾牆板統籌用於會議室與客房樓層，作為更安靜酒店室內方案的一部分。',
                ],
            ],
            'jw-custom-profiles' => [
                [
                    'name_en' => 'JW Custom Extrusion Series',
                    'name_zh' => 'JW 定制挤出型材系列',
                    'name_zh_hant' => 'JW 定制擠出型材系列',
                    'slug_en' => 'jw-custom-extrusion-series',
                    'slug_zh' => 'jw-ding-zhi-ji-chu-xing-cai-xi-lie',
                    'slug_zh_hant' => 'jw-ding-zhi-ji-chu-xing-cai-xi-lie',
                    'intro_en' => 'Project-specific JW profiles developed from details and samples, covering edges, transitions and concealed fixings.',
                    'intro_zh' => '依据节点与样品开发的 JW 项目型材，覆盖收边、转接与隐蔽固定，体现定制而非现货。',
                    'intro_zh_hant' => '依據節點與樣品開發的 JW 項目型材，覆蓋收邊、轉接與隱蔽固定，體現定制而非現貨。',
                ],
                [
                    'name_en' => 'Architectural Trim Collection',
                    'name_zh' => '建筑收边型材系列',
                    'name_zh_hant' => '建築收邊型材系列',
                    'slug_en' => 'architectural-trim-collection',
                    'slug_zh' => 'jian-zhu-shou-bian-xing-cai-xi-lie',
                    'slug_zh_hant' => 'jian-zhu-shou-bian-xing-cai-xi-lie',
                    'intro_en' => 'Skirting, shadow gaps and perimeter trims supplied as a matched set for hotel interiors and wet areas.',
                    'intro_zh' => '踢脚、阴影缝与周圈收边作为配套组合供应，服务酒店室内与湿区界面。',
                    'intro_zh_hant' => '踢腳、陰影縫與週圈收邊作為配套組合供應，服務酒店室內與濕區界面。',
                ],
            ],
            'bathroom-products' => [
                [
                    'name_en' => 'Luxury Bathroom Suite Series',
                    'name_zh' => '奢华卫浴套系',
                    'name_zh_hant' => '奢華衛浴套系',
                    'slug_en' => 'luxury-bathroom-suite-series',
                    'slug_zh' => 'she-hua-wei-yu-tao-xi',
                    'slug_zh_hant' => 'she-hua-wei-yu-tao-xi',
                    'intro_en' => 'A complete bathroom scheme for guest suites: fittings, surfaces and accessories specified together for a consistent hotel grade.',
                    'intro_zh' => '面向套房的完整卫浴方案：五金、饰面与配件一并配置，保证酒店级一致性。',
                    'intro_zh_hant' => '面向套房的完整衛浴方案：五金、飾面與配件一併配置，保證酒店級一致性。',
                ],
                [
                    'name_en' => 'Spa Bathroom Collection',
                    'name_zh' => '水疗卫浴系列',
                    'name_zh_hant' => '水療衛浴系列',
                    'slug_en' => 'spa-bathroom-collection',
                    'slug_zh' => 'shui-liao-wei-yu-xi-lie',
                    'slug_zh_hant' => 'shui-liao-wei-yu-xi-lie',
                    'intro_en' => 'Wellness-oriented bathroom solutions for spa hotels, with wet-area detailing and customized finishes rather than catalogue SKUs.',
                    'intro_zh' => '面向水疗酒店的康体卫浴方案，强调湿区节点与定制饰面，而不是目录货号。',
                    'intro_zh_hant' => '面向水療酒店的康體衛浴方案，強調濕區節點與定制飾面，而不是目錄貨號。',
                ],
                [
                    'name_en' => 'Compact Hotel Bathroom Series',
                    'name_zh' => '紧凑客房卫浴系列',
                    'name_zh_hant' => '緊湊客房衛浴系列',
                    'slug_en' => 'compact-hotel-bathroom-series',
                    'slug_zh' => 'jin-cou-ke-fang-wei-yu-xi-lie',
                    'slug_zh_hant' => 'jin-cou-ke-fang-wei-yu-xi-lie',
                    'intro_en' => 'Space-efficient bathroom packages for standard rooms, engineered around drawings, lead times and repeatable hotel roll-outs.',
                    'intro_zh' => '为标准客房设计的紧凑卫浴套餐，围绕图纸、工期与可复制开业交付来组织。',
                    'intro_zh_hant' => '為標準客房設計的緊湊衛浴套餐，圍繞圖紙、工期與可複製開業交付來組織。',
                ],
            ],
            'indoor-outdoor-furniture' => [
                [
                    'name_en' => 'Terrace Lounge Series',
                    'name_zh' => '露台休闲家具系列',
                    'name_zh_hant' => '露台休閒家具系列',
                    'slug_en' => 'terrace-lounge-series',
                    'slug_zh' => 'lu-tai-xiu-xian-jia-ju-xi-lie',
                    'slug_zh_hant' => 'lu-tai-xiu-xian-jia-ju-xi-lie',
                    'intro_en' => 'Outdoor lounge settings for terraces and pool bars, specified for climate, fabric and hotel service flow.',
                    'intro_zh' => '露台与泳池酒吧的户外休闲组合，按气候、面料与酒店服务动线来定制。',
                    'intro_zh_hant' => '露台與泳池酒吧的戶外休閒組合，按氣候、面料與酒店服務動線來定制。',
                ],
                [
                    'name_en' => 'Indoor Lobby Seating Series',
                    'name_zh' => '室内大堂座椅系列',
                    'name_zh_hant' => '室內大堂座椅系列',
                    'slug_en' => 'indoor-lobby-seating-series',
                    'slug_zh' => 'shi-nei-da-tang-zuo-yi-xi-lie',
                    'slug_zh_hant' => 'shi-nei-da-tang-zuo-yi-xi-lie',
                    'intro_en' => 'Lobby and lounge seating developed as a branded interior scheme, with finishes matched to the wider hotel material programme.',
                    'intro_zh' => '大堂与休息区座椅作为品牌室内方案开发，饰面与酒店整体材料体系对齐。',
                    'intro_zh_hant' => '大堂與休息區座椅作為品牌室內方案開發，飾面與酒店整體材料體系對齊。',
                ],
            ],
        ];
    }
}
