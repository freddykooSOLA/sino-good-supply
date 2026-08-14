<?php

namespace App\Filament\Support;

use App\Models\CaseStudy;
use App\Models\Category;
use Filament\Forms;

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
                    Forms\Components\Repeater::make('facebook_posts')
                        ->label('最新帖子')
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
                                ->url()
                                ->required(),
                        ])
                        ->maxItems(3)
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => \Illuminate\Support\Str::limit($state['text_en'] ?? $state['text_zh'] ?? '新帖子', 24))
                        ->columnSpanFull(),
                ]),
            Forms\Components\Tabs\Tab::make('最新动向')
                ->schema([
                    static::headingFields('latest_news'),
                    Forms\Components\Repeater::make('latest_news')
                        ->label('新闻 / 动向')
                        ->schema([
                            Forms\Components\DatePicker::make('date')
                                ->label('日期'),
                            ...static::textInputs('title', '标题', required: true),
                            ...static::textareas('excerpt', '摘要'),
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
