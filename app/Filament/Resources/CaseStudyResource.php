<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseStudyResource\Pages;
use App\Filament\Support\TranslationForm;
use App\Models\CaseStudy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CaseStudyResource extends Resource
{
    protected static ?string $model = CaseStudy::class;

    protected static ?string $navigationIcon = 'heroicon-o-film';

    protected static ?string $navigationLabel = '案例管理';

    protected static ?string $modelLabel = '案例';

    protected static ?string $pluralModelLabel = '案例';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('video_type')
                    ->label('视频来源')
                    ->options([
                        CaseStudy::VIDEO_YOUTUBE => 'YouTube 链接',
                        CaseStudy::VIDEO_UPLOAD => '上传视频文件',
                    ])
                    ->default(CaseStudy::VIDEO_YOUTUBE)
                    ->inline()
                    ->live()
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('youtube_url')
                    ->label('YouTube 链接')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->url()
                    ->helperText('粘贴完整 YouTube 视频链接，系统会自动解析视频 ID')
                    ->required(fn (Get $get): bool => $get('video_type') === CaseStudy::VIDEO_YOUTUBE)
                    ->visible(fn (Get $get): bool => $get('video_type') === CaseStudy::VIDEO_YOUTUBE)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('video_path')
                    ->label('上传视频')
                    ->helperText('支持 MP4 / WebM / MOV，最大 40MB')
                    ->disk('public')
                    ->directory('case-videos')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'video/mp4',
                        'video/webm',
                        'video/quicktime',
                        'video/x-m4v',
                    ])
                    ->maxSize(40960)
                    ->downloadable()
                    ->openable()
                    ->required(fn (Get $get): bool => $get('video_type') === CaseStudy::VIDEO_UPLOAD)
                    ->visible(fn (Get $get): bool => $get('video_type') === CaseStudy::VIDEO_UPLOAD)
                    ->columnSpanFull(),
                TranslationForm::toolbar([
                    'title_en' => ['zh' => 'title_zh', 'zh_hant' => 'title_zh_hant'],
                    'description_en' => ['zh' => 'description_zh', 'zh_hant' => 'description_zh_hant'],
                ]),
                Forms\Components\Tabs::make('语言内容')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('英文')
                            ->schema([
                                Forms\Components\TextInput::make('title_en')
                                    ->label('标题')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_en')
                                    ->label('说明')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('简体中文')
                            ->schema([
                                TranslationForm::editZhToggle(),
                                Forms\Components\TextInput::make('title_zh')
                                    ->label('标题')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(TranslationForm::zhLocked())
                                    ->dehydrated(),
                                Forms\Components\Textarea::make('description_zh')
                                    ->label('说明')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->disabled(TranslationForm::zhLocked())
                                    ->dehydrated(),
                            ]),
                        Forms\Components\Tabs\Tab::make('繁体中文')
                            ->schema([
                                TranslationForm::editZhHantToggle(),
                                Forms\Components\TextInput::make('title_zh_hant')
                                    ->label('标题')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(TranslationForm::zhHantLocked())
                                    ->dehydrated(),
                                Forms\Components\Textarea::make('description_zh_hant')
                                    ->label('说明')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->disabled(TranslationForm::zhHantLocked())
                                    ->dehydrated(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('启用')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('预览')
                    ->getStateUsing(fn (CaseStudy $record) => $record->thumbnailUrl())
                    ->height(54)
                    ->width(96)
                    ->defaultImageUrl(url('/favicon.ico')),
                Tables\Columns\TextColumn::make('video_type')
                    ->label('来源')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === CaseStudy::VIDEO_UPLOAD ? '本地视频' : 'YouTube')
                    ->color(fn (?string $state): string => $state === CaseStudy::VIDEO_UPLOAD ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('title_zh')
                    ->label('标题')
                    ->searchable()
                    ->description(fn (CaseStudy $record) => $record->title_en),
                Tables\Columns\TextColumn::make('media')
                    ->label('视频')
                    ->getStateUsing(function (CaseStudy $record): string {
                        if ($record->isUploadedVideo()) {
                            return basename((string) $record->video_path);
                        }

                        return $record->youtube_url ?: '—';
                    })
                    ->limit(36)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('video_type')
                    ->label('视频来源')
                    ->options([
                        CaseStudy::VIDEO_YOUTUBE => 'YouTube',
                        CaseStudy::VIDEO_UPLOAD => '本地视频',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('启用状态'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('编辑'),
                Tables\Actions\DeleteAction::make()->label('删除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('批量删除'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaseStudies::route('/'),
            'create' => Pages\CreateCaseStudy::route('/create'),
            'edit' => Pages\EditCaseStudy::route('/{record}/edit'),
        ];
    }
}
