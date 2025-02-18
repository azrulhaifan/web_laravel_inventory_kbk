<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BundleVariantResource\Pages;
use App\Models\ProductBundleVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BundleVariantResource extends Resource
{
    protected static ?string $model = ProductBundleVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Bundle Variants';
    protected static ?string $navigationGroup = 'Products';
    protected static ?int $navigationSort = 140;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Available Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('buying_price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('min_stock')
                            ->label('Available Stock')
                            ->disabled(),
                        Forms\Components\Textarea::make('description')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Bundle Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_variant_id')
                                    ->relationship('productVariant', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(function (Forms\Get $get): array {
                                        $selectedVariants = collect($get('../../items'))
                                            ->pluck('product_variant_id')
                                            ->filter();

                                        return \App\Models\ProductVariant::query()
                                            ->whereNotIn('id', $selectedVariants)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                            ])
                            ->columns(1)
                            ->defaultItems(2)
                            ->minItems(2)
                            ->required()
                            ->reorderable(false)
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBundleVariants::route('/'),
        ];
    }
}
