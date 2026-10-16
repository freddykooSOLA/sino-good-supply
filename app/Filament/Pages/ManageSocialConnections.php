<?php

namespace App\Filament\Pages;

use App\Services\SocialFeedService;
use App\Support\SocialTokenVault;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSocialConnections extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static string $view = 'filament.pages.manage-social-connections';

    protected static ?string $navigationLabel = '社交平台连接';

    protected static ?string $title = '社交平台连接';

    protected static ?string $slug = 'social-connections';

    protected static ?int $navigationSort = 11;

    public ?array $data = [];

    public function mount(): void
    {
        $feed = app(SocialFeedService::class);
        $facebook = $feed->platformConnection('facebook');
        if (empty($facebook['page_url'])) {
            $legacy = \App\Models\Setting::getValue('facebook_connection', []) ?: [];
            $facebook = array_merge($facebook, [
                'enabled' => (bool) ($legacy['enabled'] ?? false),
                'page_url' => $legacy['page_url'] ?? '',
            ]);
        }

        $this->form->fill([
            'facebook' => $this->connectionFormState($facebook),
            'instagram' => $this->connectionFormState($feed->platformConnection('instagram')),
            'youtube' => $this->connectionFormState($feed->platformConnection('youtube')),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->platformSection('Facebook', 'facebook', true),
                $this->platformSection('Instagram', 'instagram', true),
                $this->platformSection('YouTube', 'youtube', false),
            ])
            ->statePath('data');
    }

    protected function platformSection(string $label, string $key, bool $withToken): Forms\Components\Section
    {
        return Forms\Components\Section::make($label)
            ->schema([
                Forms\Components\Toggle::make("{$key}.enabled")->label('启用')->inline(false),
                Forms\Components\TextInput::make("{$key}.page_url")
                    ->label($key === 'youtube' ? '频道 URL 或 @handle' : '主页链接')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make("{$key}.channel_url")
                    ->label('YouTube 频道（备用字段）')
                    ->visible($key === 'youtube')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make("{$key}.ig_user_id")
                    ->label('Instagram User ID（可选）')
                    ->visible($key === 'instagram'),
                Forms\Components\TextInput::make("{$key}.access_token")
                    ->label('Access Token')
                    ->password()
                    ->revealable()
                    ->visible($withToken)
                    ->helperText('留空则保留已保存的 Token'),
                Forms\Components\Placeholder::make("{$key}.last_synced_at")
                    ->label('上次同步')
                    ->content(fn () => (string) (app(SocialFeedService::class)->platformConnection($key)['last_synced_at'] ?? '—')),
                Forms\Components\Placeholder::make("{$key}.last_error")
                    ->label('最近错误')
                    ->content(fn () => (string) (app(SocialFeedService::class)->platformConnection($key)['last_error'] ?? '—')),
            ])
            ->columns(2);
    }

    /**
     * @param  array<string, mixed>  $connection
     * @return array<string, mixed>
     */
    protected function connectionFormState(array $connection): array
    {
        $connection['access_token'] = '';

        return $connection;
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $feed = app(SocialFeedService::class);
        foreach (['facebook', 'instagram', 'youtube'] as $platform) {
            $feed->persistConnection($platform, $state[$platform] ?? []);
        }

        Notification::make()->title('社交平台设置已保存')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncNow')
                ->label('立即同步')
                ->action(function (): void {
                    try {
                        $posts = app(SocialFeedService::class)->syncAll();
                        Notification::make()
                            ->title('已同步 '.count($posts).' 则动态')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('同步失败')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('保存')
                ->submit('save'),
        ];
    }
}
