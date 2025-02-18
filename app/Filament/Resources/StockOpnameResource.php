<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $modelLabel = 'Stock Opname';
    protected static ?string $pluralModelLabel = 'Stock Opname';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Stock Opname';
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $productVariantId = $get('product_variant_id');
                        if (!$state || !$productVariantId) {
                            $set('current_stock', 0);
                            $set('quantity', 0);
                            return;
                        }

                        $stock = \App\Models\Stock::where('warehouse_id', $state)
                            ->where('product_variant_id', $productVariantId)
                            ->first();

                        $currentStock = $stock ? $stock->quantity : 0;
                        $realStock = (int) $get('real_stock');

                        $set('current_stock', $currentStock);
                        $set('quantity', $realStock - $currentStock);
                    }),
                Forms\Components\Select::make('product_variant_id')
                    ->relationship('productVariant', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $warehouseId = $get('warehouse_id');
                        if (!$state || !$warehouseId) {
                            $set('current_stock', 0);
                            return;
                        }

                        $stock = \App\Models\Stock::where('warehouse_id', $warehouseId)
                            ->where('product_variant_id', $state)
                            ->first();

                        $set('current_stock', $stock ? $stock->quantity : 0);
                    }),
                Forms\Components\TextInput::make('current_stock')
                    ->label('Current Stock')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(0)
                    ->afterStateUpdated(function (Forms\Get $get, $state) {
                        $warehouseId = $get('warehouse_id');
                        $productVariantId = $get('product_variant_id');

                        if (!$warehouseId || !$productVariantId) return 0;

                        $stock = \App\Models\Stock::where('warehouse_id', $warehouseId)
                            ->where('product_variant_id', $productVariantId)
                            ->first();

                        return $stock ? $stock->quantity : 0;
                    })
                    ->live(),
                Forms\Components\TextInput::make('real_stock')
                    ->label('Real Stock Count')
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->default(0)
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $currentStock = (int) $get('current_stock');
                        $realStock = (int) $state;
                        $difference = $realStock - $currentStock;
                        $set('quantity', $difference);
                        $set('notes', "adjust stock qty from {$currentStock} to {$realStock} with different qty : {$difference}");
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->label('Stock Difference')
                    ->readOnly()
                    ->default(function (Forms\Get $get) {
                        $realStock = (int) $get('real_stock');
                        $currentStock = (int) $get('current_stock');
                        return $realStock - $currentStock;
                    }),
                Forms\Components\Hidden::make('type')
                    ->default('opname'),
                Forms\Components\Hidden::make('stock_movement_status_id')
                    ->default(1),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productVariant.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse')
                    ->relationship('warehouse', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'opname')
            ->where('stock_movement_status_id', 1);
    }
}
