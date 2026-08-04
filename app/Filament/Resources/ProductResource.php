<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\ImageCompressor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = '产品管理';

    protected static ?string $modelLabel = '产品';

    protected static ?string $pluralModelLabel = '产品';

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
                                Forms\Components\Textarea::make('short_desc_en')
                                    ->label('简介')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('full_desc_en')
                                    ->label('详细描述')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('简体中文')
                            ->schema([
                                Forms\Components\TextInput::make('name_zh')
                                    ->label('名称')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug_zh')
                                    ->label('URL别名')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('short_desc_zh')
                                    ->label('简介')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('full_desc_zh')
                                    ->label('详细描述')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('繁体中文')
                            ->schema([
                                Forms\Components\TextInput::make('name_zh_hant')
                                    ->label('名称')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug_zh_hant')
                                    ->label('URL别名')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('short_desc_zh_hant')
                                    ->label('简介')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('full_desc_zh_hant')
                                    ->label('详细描述')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('specs')
                    ->label('规格参数')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('参数名')
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->label('参数值')
                            ->required(),
                    ])
                    ->columns(2)
                    ->default([])
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('images')
                    ->label('产品图片')
                    ->helperText('请上传 1–5 张图片。系统会自动压缩到每张 200KB 以下。')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->minFiles(1)
                    ->maxFiles(5)
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->directory('products')
                    ->disk('public')
                    ->visibility('public')
                    ->imageEditor()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        return ImageCompressor::storeUnderLimit(
                            file: $file,
                            directory: 'products',
                            maxKilobytes: 200,
                            disk: 'public',
                            maxDimension: 1600,
                        );
                    })
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('pdf_path')
                    ->label('产品PDF目录')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('pdfs')
                    ->disk('public')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\Toggle::make('is_featured')
                    ->label('首页推荐')
                    ->default(false),
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
                    ->label('缩略图')
                    ->disk('public')
                    ->getStateUsing(fn (Product $record) => $record->images[0] ?? null)
                    ->square(),
                Tables\Columns\TextColumn::make('name_zh')
                    ->label('名称')
                    ->searchable()
                    ->description(fn (Product $record) => $record->name_en),
                Tables\Columns\TextColumn::make('category.name_zh')
                    ->label('分类')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('推荐')
                    ->boolean(),
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
                Tables\Filters\TernaryFilter::make('is_featured')->label('首页推荐'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
