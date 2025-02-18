<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 110;
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Product Variants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_master_id')
                    ->relationship('productMaster', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, $context, $get) {
                        static::generateSku($state, $get('color_id'), $get('size'), $set);
                        static::generateName($state, $get('color_id'), $get('size'), $set);
                        static::setPricesFromMaster($state, $set);
                    }),
                Forms\Components\Select::make('color_id')
                    ->relationship('color', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, $context, $get) {
                        static::generateSku($get('product_master_id'), $state, $get('size'), $set);
                        static::generateName($get('product_master_id'), $state, $get('size'), $set);
                    }),
                Forms\Components\Select::make('size')
                    ->options([
                        'S' => 'S',
                        'M' => 'M',
                        'L' => 'L',
                        'XL' => 'XL',
                        'NS' => 'NS',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, $context, $get) {
                        static::generateSku($get('product_master_id'), $get('color_id'), $state, $set);
                        static::generateName($get('product_master_id'), $get('color_id'), $state, $set);
                    }),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->readOnly(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->mask("9999999999")
                    ->stripCharacters('.,')
                    ->minValue(0)
                    ->suffix('g'),
                Forms\Components\Section::make('Pricing')
                    ->description('Default prices from product master, you can override each variant price. Price for product variant will not affect product master price.')
                    ->schema([
                        Forms\Components\TextInput::make('price_component_1')
                            ->label('Material Cost')
                            ->prefix("Rp")
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->step(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, $context, $get) {
                                static::calculateTotal($state, $set, $get);
                            }),
                        Forms\Components\TextInput::make('price_component_2')
                            ->label('Production Cost')
                            ->prefix("Rp")
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->step(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, $context, $get) {
                                static::calculateTotal($state, $set, $get);
                            }),
                        Forms\Components\TextInput::make('price_component_3')
                            ->label('Packaging Cost')
                            ->prefix("Rp")
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->step(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, $context, $get) {
                                static::calculateTotal($state, $set, $get);
                            }),
                        Forms\Components\TextInput::make('total_component_price')
                            ->label('Total Cost')
                            ->prefix("Rp")
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->disabled(),
                        Forms\Components\TextInput::make('selling_price')
                            ->label('Selling Price')
                            ->prefix("Rp")
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->step(1),
                    ])->columns(2),
            ]);
    }

    protected static function calculateTotal($state, Forms\Set $set, $get): void
    {
        $component1 = (float) str_replace(',', '', $get('price_component_1')) ?? 0;
        $component2 = (float) str_replace(',', '', $get('price_component_2')) ?? 0;
        $component3 = (float) str_replace(',', '', $get('price_component_3')) ?? 0;

        $total = $component1 + $component2 + $component3;
        $set('total_component_price', $total);
    }

    protected static function generateName($masterId, $colorId, $size, Forms\Set $set): void
    {
        if (!$masterId || !$colorId || !$size) {
            return;
        }

        $masterName = \App\Models\ProductMaster::find($masterId)?->name;
        $colorName = \App\Models\Color::find($colorId)?->name;

        $name = "{$masterName} - {$colorName} - {$size}";
        $set('name', $name);
    }

    protected static function generateSku($masterId, $colorId, $size, Forms\Set $set): void
    {
        if (!$masterId || !$colorId || !$size) {
            return;
        }

        $masterSku = \App\Models\ProductMaster::find($masterId)?->sku;
        $colorCode = \App\Models\Color::find($colorId)?->code;

        $sku = "{$masterSku} - {$colorCode} - {$size}";
        $set('sku', $sku);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productMaster.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('color.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('current_stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_component_price')
                    ->label('Buying Price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('size')
                    ->options([
                        'S' => 'S',
                        'M' => 'M',
                        'L' => 'L',
                        'XL' => 'XL',
                        'NS' => 'NS',
                    ]),
                Tables\Filters\SelectFilter::make('color')
                    ->relationship('color', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }

    protected static function setPricesFromMaster($masterId, Forms\Set $set): void
    {
        if (!$masterId) {
            return;
        }

        $master = \App\Models\ProductMaster::find($masterId);
        if (!$master) {
            return;
        }

        $set('price_component_1', $master->price_component_1);
        $set('price_component_2', $master->price_component_2);
        $set('price_component_3', $master->price_component_3);
        $set('total_component_price', $master->total_component_price);
        $set('selling_price', $master->selling_price);
    }
}
