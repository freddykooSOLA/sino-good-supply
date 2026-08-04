<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseStudyResource\Pages;
use App\Models\CaseStudy;
use Filament\Forms;
use Filament\Forms\Form;
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
                Forms\Components\TextInput::make('youtube_url')
                    ->label('YouTube 链接')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->required()
                    ->url()
                    ->helperText('粘贴完整 YouTube 视频链接，系统会自动解析视频 ID')
                    ->columnSpanFull(),
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
                                Forms\Components\TextInput::make('title_zh')
                                    ->label('标题')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_zh')
                                    ->label('说明')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('繁体中文')
                            ->schema([
                                Forms\Components\TextInput::make('title_zh_hant')
                                    ->label('标题')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description_zh_hant')
                                    ->label('说明')
                                    ->rows(4)
                                    ->columnSpanFull(),
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
                Tables\Columns\ImageColumn::make('youtube_id')
                    ->label('预览')
                    ->getStateUsing(fn (CaseStudy $record) => $record->thumbnailUrl())
                    ->height(54)
                    ->width(96),
                Tables\Columns\TextColumn::make('title_zh')
                    ->label('标题')
                    ->searchable()
                    ->description(fn (CaseStudy $record) => $record->title_en),
                Tables\Columns\TextColumn::make('youtube_url')
                    ->label('YouTube')
                    ->limit(40)
                    ->url(fn (CaseStudy $record) => $record->youtube_url, true)
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
