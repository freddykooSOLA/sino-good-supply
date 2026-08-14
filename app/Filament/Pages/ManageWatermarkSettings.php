<?php

namespace App\Filament\Pages;

use App\Models\WatermarkSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageWatermarkSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.manage-watermark-settings';

    protected static ?string $navigationLabel = 'PDF 水印';

    protected static ?string $title = 'PDF 水印设置';

    protected static ?string $slug = 'watermark-settings';

    protected static ?int $navigationSort = 11;

    public ?array $data = [];

    public function mount(): void
    {
        $setting = WatermarkSetting::current();

        $this->form->fill([
            'image_path' => $setting->image_path,
            'size' => $setting->size,
            'opacity' => $setting->opacity,
            'pattern' => $setting->pattern,
            'spacing' => $setting->spacing,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('水印图片')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('水印图片')
                            ->image()
                            ->directory('watermarks')
                            ->disk('public')
                            ->visibility('public')
                            ->helperText('建议使用透明 PNG。未上传时将使用文字水印 “SINO GOOD”。')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('显示方式')
                    ->schema([
                        Forms\Components\Select::make('pattern')
                            ->label('水印模式')
                            ->options([
                                WatermarkSetting::PATTERN_CENTERED => '居中大浮水印（45°）',
                                WatermarkSetting::PATTERN_TILED => '平铺小浮水印（45°）',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('size')
                            ->label('水印大小（px）')
                            ->numeric()
                            ->minValue(24)
                            ->maxValue(800)
                            ->required()
                            ->helperText('居中模式建议 180–360；平铺模式建议 60–160。'),
                        Forms\Components\TextInput::make('opacity')
                            ->label('透明度（0–100）')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->helperText('数值越小越透明，建议 10–30。'),
                        Forms\Components\TextInput::make('spacing')
                            ->label('平铺间距（px）')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(400)
                            ->required()
                            ->visible(fn (Get $get): bool => $get('pattern') === WatermarkSetting::PATTERN_TILED)
                            ->helperText('仅平铺模式生效。'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $state = $this->form->getState();
            $setting = WatermarkSetting::current();

            $setting->fill([
                'image_path' => $state['image_path'] ?? null,
                'size' => max(24, (int) ($state['size'] ?? 140)),
                'opacity' => max(0, min(100, (int) ($state['opacity'] ?? 18))),
                'pattern' => ($state['pattern'] ?? WatermarkSetting::PATTERN_TILED) === WatermarkSetting::PATTERN_CENTERED
                    ? WatermarkSetting::PATTERN_CENTERED
                    : WatermarkSetting::PATTERN_TILED,
                'spacing' => max(0, (int) ($state['spacing'] ?? 90)),
            ])->save();

            Notification::make()
                ->title('水印设置已保存')
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
                ->label('保存水印设置')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
