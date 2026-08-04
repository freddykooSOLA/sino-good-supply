<?php

namespace App\Filament\Support;

use App\Services\TranslationService;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Throwable;

class TranslationForm
{
    /**
     * @param  array<string, array{zh: string, zh_hant: string, html?: bool, slug?: bool}>  $fieldMap
     *         Example: ['name_en' => ['zh' => 'name_zh', 'zh_hant' => 'name_zh_hant', 'slug' => true]]
     */
    public static function toolbar(array $fieldMap): Forms\Components\Actions
    {
        return Forms\Components\Actions::make([
            Forms\Components\Actions\Action::make('autoTranslate')
                ->label('从英文自动翻译（简/繁）')
                ->icon('heroicon-o-language')
                ->color('warning')
                ->action(function (Get $get, Set $set) use ($fieldMap) {
                    try {
                        /** @var TranslationService $translator */
                        $translator = app(TranslationService::class);
                        $translatedCount = 0;

                        foreach ($fieldMap as $englishField => $targets) {
                            $source = $get($englishField);

                            if (! filled($source)) {
                                continue;
                            }

                            $isHtml = (bool) ($targets['html'] ?? false);
                            $pair = $isHtml
                                ? $translator->translateHtmlPair($source)
                                : $translator->translatePair($source);

                            if (($targets['zh'] ?? null) && filled($pair['zh'])) {
                                $set($targets['zh'], $pair['zh']);
                                $translatedCount++;
                            }

                            if (($targets['zh_hant'] ?? null) && filled($pair['zh_hant'])) {
                                $set($targets['zh_hant'], $pair['zh_hant']);
                                $translatedCount++;
                            }

                            if (! empty($targets['slug'])) {
                                $slug = $get('slug_en') ?: Str::slug(strip_tags((string) $source));
                                if ($slug) {
                                    $set('slug_zh', $slug);
                                    $set('slug_zh_hant', $slug);
                                }
                            }
                        }

                        if ($translatedCount === 0) {
                            Notification::make()
                                ->title('没有可翻译的英文内容')
                                ->warning()
                                ->send();

                            return;
                        }

                        $set('edit_zh', false);
                        $set('edit_zh_hant', false);

                        Notification::make()
                            ->title('已自动翻译成简体 / 繁体中文')
                            ->body('如需修改，请打开对应语言页签并点击「编辑」开关。')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('翻译失败')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ])->columnSpanFull();
    }

    public static function editZhToggle(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('edit_zh')
            ->label('编辑简体中文')
            ->helperText('关闭时为只读（自动翻译结果）；打开后可手动修改。')
            ->default(false)
            ->live()
            ->dehydrated(false);
    }

    public static function editZhHantToggle(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('edit_zh_hant')
            ->label('编辑繁体中文')
            ->helperText('关闭时为只读（自动翻译结果）；打开后可手动修改。')
            ->default(false)
            ->live()
            ->dehydrated(false);
    }

    public static function zhLocked(): \Closure
    {
        return fn (Get $get): bool => ! (bool) $get('edit_zh');
    }

    public static function zhHantLocked(): \Closure
    {
        return fn (Get $get): bool => ! (bool) $get('edit_zh_hant');
    }
}
