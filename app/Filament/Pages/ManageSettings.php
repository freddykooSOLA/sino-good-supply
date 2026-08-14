<?php

namespace App\Filament\Pages;

use App\Filament\Support\HomepageForm;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.manage-settings';

    protected static ?string $navigationLabel = '系统设置';

    protected static ?string $title = '系统设置';

    protected static ?string $slug = 'system-settings';

    protected static ?int $navigationSort = 10;

    public ?array $data = [];

    public function mount(): void
    {
        $pageHeroDefaults = [
            'products' => [],
            'cases' => [],
            'about' => [],
            'contact' => [],
            'order_process' => [],
        ];

        $this->form->fill([
            'hero_slides' => Setting::getValue('hero_slides', []) ?: [],
            'hero' => array_merge([
                'autoplay' => true,
                'speed' => 5000,
            ], Setting::getValue('hero', []) ?: []),
            'page_heroes' => array_replace_recursive(
                $pageHeroDefaults,
                Setting::getValue('page_heroes', []) ?: []
            ),
            'company_stats' => Setting::getValue('company_stats', []) ?: [],
            'contact' => array_merge([
                'media_type' => 'none',
                'media_image' => null,
                'google_maps_embed' => '',
            ], Setting::getValue('contact', []) ?: []),
            'brand_logos' => Setting::getValue('brand_logos', []) ?: [],
            'homepage_headings' => Setting::getValue('homepage_headings', []) ?: [],
            'core_business' => Setting::getValue('core_business', []) ?: [],
            'featured_cases' => Setting::getValue('featured_cases', []) ?: [],
            'order_process' => array_merge([
                'flowchart_image' => null,
                'steps' => [],
            ], Setting::getValue('order_process', []) ?: []),
            'advantages' => Setting::getValue('advantages', []) ?: [],
            'visible_category_ids' => Setting::getValue('visible_category_ids', []) ?: [],
            'facebook_posts' => Setting::getValue('facebook_posts', []) ?: [],
            'latest_news' => Setting::getValue('latest_news', []) ?: [],
            'about_content' => Setting::getValue('about_content', []) ?: [],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('首页轮播')
                            ->schema([
                                Forms\Components\Section::make('轮播设置')
                                    ->schema([
                                        Forms\Components\Toggle::make('autoplay')
                                            ->label('启用自动轮播')
                                            ->helperText('关闭后仅可通过左右按钮手动切换')
                                            ->inline(false)
                                            ->default(true)
                                            ->live(),
                                        Forms\Components\TextInput::make('speed')
                                            ->label('轮播速度（毫秒）')
                                            ->numeric()
                                            ->minValue(2000)
                                            ->maxValue(60000)
                                            ->step(500)
                                            ->default(5000)
                                            ->helperText('建议 3000–8000，例如 5000 = 5 秒')
                                            ->required()
                                            ->visible(fn (Get $get): bool => (bool) $get('autoplay')),
                                    ])
                                    ->columns(2)
                                    ->statePath('hero'),
                                Forms\Components\Repeater::make('hero_slides')
                                    ->label('轮播幻灯片')
                                    ->schema(HomepageForm::heroSlideSchema())
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['title_zh'] ?? '新幻灯片')
                                    ->columnSpanFull(),
                            ]),
                        ...HomepageForm::tabs(),
                        Forms\Components\Tabs\Tab::make('页面头图')
                            ->schema([
                                $this->pageHeroSection('系列 / 分类页', 'products'),
                                $this->pageHeroSection('案例页', 'cases'),
                                $this->pageHeroSection('关于我们', 'about'),
                                $this->pageHeroSection('联系我们', 'contact'),
                                $this->pageHeroSection('落单流程', 'order_process'),
                            ]),
                        Forms\Components\Tabs\Tab::make('公司数据')
                            ->schema([
                                Forms\Components\Repeater::make('company_stats')
                                    ->label('数据统计')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')
                                            ->label('数值')
                                            ->required(),
                                        Forms\Components\TextInput::make('label_en')
                                            ->label('英文标签')
                                            ->required(),
                                        Forms\Components\TextInput::make('label_zh')
                                            ->label('简体标签'),
                                        Forms\Components\TextInput::make('label_zh_hant')
                                            ->label('繁体标签'),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('联系方式')
                            ->schema([
                                Forms\Components\Section::make('联系信息')
                                    ->schema([
                                        Forms\Components\TextInput::make('company_en')
                                            ->label('公司名称（英）'),
                                        Forms\Components\TextInput::make('company_zh')
                                            ->label('公司名称（简）'),
                                        Forms\Components\TextInput::make('company_zh_hant')
                                            ->label('公司名称（繁）'),
                                        Forms\Components\Textarea::make('address_en')
                                            ->label('地址（英）')
                                            ->rows(2),
                                        Forms\Components\Textarea::make('address_zh')
                                            ->label('地址（简）')
                                            ->rows(2),
                                        Forms\Components\Textarea::make('address_zh_hant')
                                            ->label('地址（繁）')
                                            ->rows(2),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('电话')
                                            ->tel(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('邮箱')
                                            ->email(),
                                        Forms\Components\TextInput::make('whatsapp')
                                            ->label('WhatsApp'),
                                        Forms\Components\TextInput::make('wechat')
                                            ->label('微信'),
                                    ])
                                    ->columns(2)
                                    ->statePath('contact'),
                                Forms\Components\Section::make('联系页媒体')
                                    ->description('在联系表单旁显示图片或 Google 地图')
                                    ->schema([
                                        Forms\Components\Select::make('media_type')
                                            ->label('显示方式')
                                            ->options([
                                                'none' => '不显示',
                                                'image' => '上传图片',
                                                'map' => 'Google 地图',
                                            ])
                                            ->default('none')
                                            ->live()
                                            ->required(),
                                        Forms\Components\FileUpload::make('media_image')
                                            ->label('联系页图片')
                                            ->image()
                                            ->directory('contact')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->visible(fn (Get $get): bool => $get('media_type') === 'image'),
                                        Forms\Components\Textarea::make('google_maps_embed')
                                            ->label('Google 地图嵌入')
                                            ->rows(4)
                                            ->helperText('粘贴 Google Maps「嵌入地图」的 iframe 代码，或直接粘贴 embed 网址')
                                            ->visible(fn (Get $get): bool => $get('media_type') === 'map'),
                                    ])
                                    ->columns(1)
                                    ->statePath('contact'),
                            ]),
                        Forms\Components\Tabs\Tab::make('品牌标识')
                            ->schema([
                                Forms\Components\Repeater::make('brand_logos')
                                    ->label('合作品牌 Logo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Logo')
                                            ->image()
                                            ->directory('brands')
                                            ->disk('public')
                                            ->visibility('public'),
                                        Forms\Components\TextInput::make('name')
                                            ->label('品牌名称'),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function pageHeroSection(string $label, string $key): Forms\Components\Section
    {
        return Forms\Components\Section::make($label)
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('头图')
                    ->image()
                    ->directory('page-heroes')
                    ->disk('public')
                    ->visibility('public')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title_en')
                    ->label('英文标题'),
                Forms\Components\TextInput::make('title_zh')
                    ->label('简体标题'),
                Forms\Components\TextInput::make('title_zh_hant')
                    ->label('繁体标题'),
                Forms\Components\Textarea::make('subtitle_en')
                    ->label('英文副标题')
                    ->rows(2),
                Forms\Components\Textarea::make('subtitle_zh')
                    ->label('简体副标题')
                    ->rows(2),
                Forms\Components\Textarea::make('subtitle_zh_hant')
                    ->label('繁体副标题')
                    ->rows(2),
            ])
            ->columns(3)
            ->collapsed()
            ->statePath("page_heroes.{$key}");
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();

            $hero = $state['hero'] ?? [];
            $hero['autoplay'] = (bool) ($hero['autoplay'] ?? false);
            $hero['speed'] = max(2000, (int) ($hero['speed'] ?? 5000));

            Setting::setValue('hero_slides', $state['hero_slides'] ?? []);
            Setting::setValue('hero', $hero);
            Setting::setValue('page_heroes', $state['page_heroes'] ?? []);
            Setting::setValue('company_stats', $state['company_stats'] ?? []);
            Setting::setValue('contact', $state['contact'] ?? []);
            Setting::setValue('brand_logos', $state['brand_logos'] ?? []);
            Setting::setValue('homepage_headings', $state['homepage_headings'] ?? []);
            Setting::setValue('core_business', $state['core_business'] ?? []);
            Setting::setValue('featured_cases', $state['featured_cases'] ?? []);
            Setting::setValue('order_process', $state['order_process'] ?? []);
            Setting::setValue('advantages', $state['advantages'] ?? []);
            Setting::setValue('visible_category_ids', array_values($state['visible_category_ids'] ?? []));
            Setting::setValue('facebook_posts', array_slice($state['facebook_posts'] ?? [], 0, 3));
            Setting::setValue('latest_news', $state['latest_news'] ?? []);
            Setting::setValue('about_content', $state['about_content'] ?? []);

            Notification::make()
                ->title('设置已保存')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('保存失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('保存设置')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
