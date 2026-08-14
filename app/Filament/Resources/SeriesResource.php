<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeriesResource\Pages;
use App\Filament\Support\TranslationForm;
use App\Models\Series;
use App\Support\ImageCompressor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SeriesResource extends Resource
{
    protected static ?string $model = Series::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = '系列管理';

    protected static ?string $modelLabel = '系列';

    protected static ?string $pluralModelLabel = '系列';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('所属分类')
                    ->relationship('category', 'name_zh')
                    ->searchable()
                    ->preload()
                    ->required(),
                TranslationForm::toolbar([
                    'name_en' => ['zh' => 'name_zh', 'zh_hant' => 'name_zh_hant', 'slug' => true],
                    'intro_en' => ['zh' => 'intro_zh', 'zh_hant' => 'intro_zh_hant'],
                ]),
                Forms\Components\Tabs::make('语言内容')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('英文')
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label('名称')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug_en', Str::slug($state ?? ''))),
                                Forms\Components\TextInput::make('slug_en')
                                    ->label('URL别名')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('intro_en')
                                    ->label('简介')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('简体中文')
                            ->schema([
                                TranslationForm::editZhToggle(),
                                Forms\Components\TextInput::make('name_zh')
                                    ->label('名称')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(TranslationForm::zhLocked())
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('slug_zh')
                                    ->label('URL别名')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->disabled(TranslationForm::zhLocked())
                                    ->dehydrated(),
                                Forms\Components\Textarea::make('intro_zh')
                                    ->label('简介')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->disabled(TranslationForm::zhLocked())
                                    ->dehydrated(),
                            ]),
                        Forms\Components\Tabs\Tab::make('繁体中文')
                            ->schema([
                                TranslationForm::editZhHantToggle(),
                                Forms\Components\TextInput::make('name_zh_hant')
                                    ->label('名称')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(TranslationForm::zhHantLocked())
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('slug_zh_hant')
                                    ->label('URL别名')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->disabled(TranslationForm::zhHantLocked())
                                    ->dehydrated(),
                                Forms\Components\Textarea::make('intro_zh_hant')
                                    ->label('简介')
                                    ->rows(4)
                                    ->columnSpanFull()
                                    ->disabled(TranslationForm::zhHantLocked())
                                    ->dehydrated(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('images')
                    ->label('系列图片')
                    ->helperText('请上传 1–10 张图片。系统会自动压缩到每张 200KB 以下。')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->minFiles(1)
                    ->maxFiles(10)
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->directory('series')
                    ->disk('public')
                    ->visibility('public')
                    ->imageEditor()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        return ImageCompressor::storeUnderLimit(
                            file: $file,
                            directory: 'series',
                            maxKilobytes: 200,
                            disk: 'public',
                            maxDimension: 1600,
                        );
                    })
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('pdf_path')
                    ->label('系列 PDF（仅预览，不可下载）')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('series-pdfs')
                    ->disk('local')
                    ->maxSize(51200)
                    ->helperText('PDF 会存储为私有文件，前台仅提供带水印预览。')
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
                Tables\Columns\ImageColumn::make('images')
                    ->label('封面')
                    ->disk('public')
                    ->getStateUsing(fn (Series $record) => $record->images[0] ?? null)
                    ->square(),
                Tables\Columns\TextColumn::make('name_zh')
                    ->label('名称')
                    ->searchable()
                    ->description(fn (Series $record) => $record->name_en),
                Tables\Columns\TextColumn::make('category.name_zh')
                    ->label('分类')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn (Series $record) => filled($record->pdf_path)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('分类')
                    ->relationship('category', 'name_zh'),
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
            'index' => Pages\ListSeries::route('/'),
            'create' => Pages\CreateSeries::route('/create'),
            'edit' => Pages\EditSeries::route('/{record}/edit'),
        ];
    }
}
