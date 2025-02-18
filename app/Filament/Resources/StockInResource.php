<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockInResource\Pages;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockInResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $modelLabel = 'Stock In';
    protected static ?string $pluralModelLabel = 'Stock In';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationLabel = 'Stock In';
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'in')
            ->where('quantity', '>', 0)
            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('items')
                    ->schema([
                        Forms\Components\Select::make('product_variant_id')
                            ->relationship('productVariant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add Product')
                    ->collapsible()
                    ->cloneable()
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('type')
                    ->default('in'),

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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productVariant.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockIns::route('/'),
            'create' => Pages\CreateStockIn::route('/create'),
        ];
    }
}
