<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
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
        $this->form->fill([
            'hero_slides' => Setting::getValue('hero_slides', []) ?: [],
            'company_stats' => Setting::getValue('company_stats', []) ?: [],
            'contact' => Setting::getValue('contact', []) ?: [],
            'brand_logos' => Setting::getValue('brand_logos', []) ?: [],
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
                                Forms\Components\Repeater::make('hero_slides')
                                    ->label('轮播幻灯片')
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->label('图片')
                                            ->image()
                                            ->directory('banners')
                                            ->disk('public')
                                            ->visibility('public'),
                                        Forms\Components\TextInput::make('title_en')
                                            ->label('英文标题'),
                                        Forms\Components\TextInput::make('subtitle_en')
                                            ->label('英文副标题'),
                                        Forms\Components\TextInput::make('button_text_en')
                                            ->label('按钮文字'),
                                        Forms\Components\TextInput::make('link')
                                            ->label('跳转链接')
                                            ->helperText('可留空；填写时请用完整网址，例如 https://...')
                                            ->rule('nullable')
                                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null),
                                    ])
                                    ->defaultItems(0)
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title_en'] ?? '新幻灯片')
                                    ->columnSpanFull(),
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

    public function save(): void
    {
        try {
            $state = $this->form->getState();

            Setting::setValue('hero_slides', $state['hero_slides'] ?? []);
            Setting::setValue('company_stats', $state['company_stats'] ?? []);
            Setting::setValue('contact', $state['contact'] ?? []);
            Setting::setValue('brand_logos', $state['brand_logos'] ?? []);

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
