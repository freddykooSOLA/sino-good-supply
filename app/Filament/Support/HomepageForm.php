<?php

namespace App\Filament\Support;

use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Setting;
use App\Support\ImageCompressor;
use Filament\Forms;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HomepageForm
{
    public static function tabs(): array
    {
        return [
            Forms\Components\Tabs\Tab::make('主要业务')
                ->schema([
                    static::headingFields('core_business'),
                    Forms\Components\Repeater::make('core_business')
                        ->label('业务卡片')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('图标 / 图片')
                                ->image()
                                ->directory('homepage/business')
                                ->disk('public')
                                ->visibility('public'),
                            ...static::textInputs('title', '标题'),
                            ...static::textareas('description', '描述'),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['title_zh'] ?? '新业务')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('首页案例')
                ->schema([
                    static::headingFields('featured_cases'),
                    Forms\Components\Repeater::make('featured_cases')
                        ->label('案例')
                        ->schema([
                            Forms\Components\Select::make('case_id')
                                ->label('选择已有案例（可选）')
                                ->options(fn () => CaseStudy::query()->orderBy('sort_order')->pluck('title_zh', 'id'))
                                ->searchable()
                                ->nullable()
                                ->helperText('选择后可用下方字段覆盖标题 / 图片 / 链接'),
                            Forms\Components\FileUpload::make('image')
                                ->label('封面图')
                                ->image()
                                ->directory('homepage/cases')
                                ->disk('public')
                                ->visibility('public'),
                            ...static::textInputs('title', '标题'),
                            Forms\Components\TextInput::make('link')
                                ->label('链接')
                                ->helperText('可留空；填写完整网址或站内路径，例如 /en/cases'),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['title_zh'] ?? '新案例')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('落单流程')
                ->schema([
                    static::headingFields('order_process'),
                    Forms\Components\Section::make('流程内容')
                        ->schema([
                            Forms\Components\FileUpload::make('flowchart_image')
                                ->label('流程图图片')
                                ->image()
                                ->directory('homepage/order-process')
                                ->disk('public')
                                ->visibility('public')
                                ->helperText('建议上传完整 SOP 流程图；若上传图片，前台优先展示图片。')
                                ->columnSpanFull(),
                            Forms\Components\Repeater::make('steps')
                                ->label('步骤列表（无图片时展示）')
                                ->schema([
                                    Forms\Components\TextInput::make('step')
                                        ->label('步骤编号')
                                        ->numeric()
                                        ->default(1),
                                    ...static::textInputs('title', '标题'),
                                    ...static::textareas('description', '说明'),
                                ])
                                ->defaultItems(0)
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['title_zh'] ?? '新步骤')
                                ->columnSpanFull(),
                        ])
                        ->statePath('order_process'),
                ]),
            Forms\Components\Tabs\Tab::make('公司优势')
                ->schema([
                    static::headingFields('advantages'),
                    Forms\Components\Repeater::make('advantages')
                        ->label('优势卡片')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('图标 / 图片')
                                ->image()
                                ->directory('homepage/advantages')
                                ->disk('public')
                                ->visibility('public'),
                            ...static::textInputs('title', '标题', required: true),
                            ...static::textareas('description', '描述'),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['title_zh'] ?? '新优势')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('产品类别')
                ->schema([
                    static::headingFields('product_categories'),
                    Forms\Components\CheckboxList::make('visible_category_ids')
                        ->label('首页显示的分类')
                        ->options(fn () => Category::query()->orderBy('sort_order')->pluck('name_zh', 'id'))
                        ->helperText('不勾选则显示全部启用分类。')
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('Facebook')
                ->schema([
                    static::headingFields('facebook_posts'),
                    Forms\Components\Section::make('自动读取主页帖子')
                        ->description('填写 Facebook 专页 ID 或专页链接，保存后首页会横列显示最新 3 则帖子。Facebook 规定读取帖子需要一次 Page Access Token（之后可留空）。')
                        ->schema([
                            Forms\Components\Toggle::make('enabled')
                                ->label('启用 Facebook 区块')
                                ->default(true)
                                ->inline(false),
                            Forms\Components\TextInput::make('page_url')
                                ->label('Facebook 专页 ID 或链接')
                                ->placeholder('例如 123456789 或 https://www.facebook.com/yourpage')
                                ->helperText('可只填专页数字 ID、专页用户名或完整链接。')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('access_token')
                                ->label('Page Access Token（首次需要）')
                                ->password()
                                ->revealable()
                                ->helperText(function (): string {
                                    $saved = filled(Setting::getValue('facebook_connection', [])['access_token'] ?? null)
                                        || filled(config('services.facebook.access_token'));

                                    $hint = '打开 Graph API Explorer，选该专页，勾选 pages_read_engagement / pages_show_list，点 Generate Access Token，把 Token 贴到这里。保存后首页会自动横列最新 3 则帖子。';

                                    return $saved
                                        ? '已保存读取密钥，可留空。'.$hint
                                        : $hint;
                                })
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->columnSpanFull(),
                            Forms\Components\Placeholder::make('page_id_display')
                                ->label('已识别主页 ID')
                                ->content(fn (): string => (string) (Setting::getValue('facebook_connection', [])['page_id'] ?? '尚未同步')),
                            Forms\Components\Placeholder::make('last_synced_display')
                                ->label('上次同步')
                                ->content(function (): string {
                                    $connection = Setting::getValue('facebook_connection', []) ?: [];
                                    $synced = $connection['last_synced_at'] ?? null;
                                    $error = $connection['last_error'] ?? null;
                                    $time = $synced ? \Illuminate\Support\Carbon::parse($synced)->timezone(config('app.timezone'))->toDateTimeString() : '尚未同步';

                                    return $error ? $time.'（最近错误：'.$error.'）' : $time;
                                }),
                        ])
                        ->columns(2)
                        ->statePath('facebook_connection'),
                    Forms\Components\Repeater::make('facebook_posts')
                        ->label('手动备用帖子（仅在自动同步失败或未配置时使用）')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('图片')
                                ->image()
                                ->directory('homepage/facebook')
                                ->disk('public')
                                ->visibility('public'),
                            ...static::textareas('text', '文字'),
                            Forms\Components\TextInput::make('link')
                                ->label('帖子链接')
                                ->url(),
                        ])
                        ->maxItems(3)
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => \Illuminate\Support\Str::limit($state['text_en'] ?? $state['text_zh'] ?? '备用帖子', 24))
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('最新动向')
                ->schema([
                    static::headingFields('latest_news'),
                    Forms\Components\Repeater::make('latest_news')
                        ->label('新闻 / 动向')
                        ->schema([
                            TranslationForm::toolbar([
                                'title_en' => ['zh' => 'title_zh', 'zh_hant' => 'title_zh_hant'],
                                'excerpt_en' => ['zh' => 'excerpt_zh', 'zh_hant' => 'excerpt_zh_hant'],
                            ]),
                            Forms\Components\DatePicker::make('date')
                                ->label('日期'),
                            ...static::textInputs('title', '标题', required: true),
                            ...static::textareas('excerpt', '摘要'),
                            Forms\Components\FileUpload::make('images')
                                ->label('图片（0–5 张，前台轮播）')
                                ->helperText('可不上传。上传后首页以幻灯片展示。')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->maxFiles(5)
                                ->maxSize(10240)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                ->directory('homepage/news')
                                ->disk('public')
                                ->visibility('public')
                                ->imageEditor()
                                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                                    return ImageCompressor::storeUnderLimit(
                                        file: $file,
                                        directory: 'homepage/news',
                                        maxKilobytes: 200,
                                        disk: 'public',
                                        maxDimension: 1600,
                                    );
                                }),
                            Forms\Components\TextInput::make('link')
                                ->label('链接'),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? $state['title_zh'] ?? '新动态')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('关于我们文案')
                ->schema([
                    ...static::textareas('about_intro', '关于我们引言', rows: 4),
                    Forms\Components\RichEditor::make('about_body_en')
                        ->label('正文（英）')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('about_body_zh')
                        ->label('正文（简）')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('about_body_zh_hant')
                        ->label('正文（繁）')
                        ->columnSpanFull(),
                ])
                ->statePath('about_content'),
        ];
    }

    public static function headingFields(string $key): Forms\Components\Section
    {
        return Forms\Components\Section::make('区块标题')
            ->schema([
                ...static::textInputs('title', '标题'),
                ...static::textareas('subtitle', '副标题'),
            ])
            ->columns(3)
            ->collapsed()
            ->statePath("homepage_headings.{$key}");
    }

    public static function heroSlideSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('image')
                ->label('图片')
                ->image()
                ->directory('banners')
                ->disk('public')
                ->visibility('public'),
            ...static::textInputs('title', '标题'),
            ...static::textareas('subtitle', '副标题'),
            ...static::textInputs('button_text', '按钮文字'),
            Forms\Components\TextInput::make('link')
                ->label('跳转链接')
                ->helperText('可留空；填写时请用完整网址，例如 https://...')
                ->rule('nullable')
                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function textInputs(string $field, string $label, bool $required = false): array
    {
        return [
            Forms\Components\TextInput::make("{$field}_en")
                ->label("{$label}（英）")
                ->required($required),
            Forms\Components\TextInput::make("{$field}_zh")
                ->label("{$label}（简）"),
            Forms\Components\TextInput::make("{$field}_zh_hant")
                ->label("{$label}（繁）"),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function textareas(string $field, string $label, int $rows = 2): array
    {
        return [
            Forms\Components\Textarea::make("{$field}_en")
                ->label("{$label}（英）")
                ->rows($rows),
            Forms\Components\Textarea::make("{$field}_zh")
                ->label("{$label}（简）")
                ->rows($rows),
            Forms\Components\Textarea::make("{$field}_zh_hant")
                ->label("{$label}（繁）")
                ->rows($rows),
        ];
    }
}
