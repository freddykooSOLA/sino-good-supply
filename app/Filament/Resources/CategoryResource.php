<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = '产品分类';

    protected static ?string $modelLabel = '分类';

    protected static ?string $pluralModelLabel = '产品分类';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_zh')
                    ->label('名称')
                    ->searchable()
                    ->description(fn (Category $record) => $record->name_en),
                Tables\Columns\TextColumn::make('slug_en')
                    ->label('英文别名')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('产品数')
                    ->counts('products'),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
