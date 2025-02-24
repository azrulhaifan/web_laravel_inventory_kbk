<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockInResource\Pages;
use App\Models\StockIn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class StockInResource extends Resource
{
    protected static ?string $model = StockIn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),

                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier / Vendor')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Radio::make('reference_type')
                    ->label('Reference ID Type')
                    ->options([
                        'auto' => 'Auto Generated',
                        'manual' => 'Manual Input',
                    ])
                    ->default('auto')
                    ->inline()
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('reference_id')
                    ->disabled(fn(callable $get) => $get('reference_type') === 'auto')
                    ->dehydrated(fn(callable $get) => $get('reference_type') === 'manual')
                    ->required(fn(callable $get) => $get('reference_type') === 'manual')
                    ->placeholder(fn(callable $get) => $get('reference_type') === 'auto' ? 'Auto Generated by System' : 'Enter Reference ID')
                    ->maxLength(25)
                    ->columnSpanFull(),

                Forms\Components\Select::make('stock_in_status_id')
                    ->label('Status')
                    ->options([
                        2 => 'Draft / Pending',
                        1 => 'Completed',
                        3 => 'Cancelled',
                    ])
                    ->default(2)
                    ->required()
                    ->helperText(new HtmlString('
                        <strong>Keterangan</strong>:
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Draft</strong>: Stok belum masuk gudang</li>
                            <li><strong>Completed</strong>: Stok sudah masuk gudang, final dan tidak dapat dirubah</li>
                            <li><strong>Cancelled</strong>: Stok dibatalkan</li>
                        </ul>
                    '))
                    ->required(),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('stockMovements')
                            ->relationship()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Forms\Get $get): array {
                                return [
                                    ...$data,
                                    'warehouse_id' => $get('warehouse_id'),
                                    'type' => 'in',
                                    'stock_movement_status_id' => $get('stock_in_status_id'),
                                ];
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Forms\Get $get): array {
                                return [
                                    ...$data,
                                    'warehouse_id' => $get('warehouse_id'),
                                    'type' => 'in',
                                    'stock_movement_status_id' => $get('stock_in_status_id'),
                                ];
                            })
                            ->schema([
                                Forms\Components\Select::make('product_variant_id')
                                    ->relationship('productVariant', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(function (Forms\Get $get): array {
                                        $selectedVariants = collect($get('../../stockMovements'))
                                            ->pluck('product_variant_id')
                                            ->filter();

                                        return \App\Models\ProductVariant::query()
                                            ->whereNotIn('id', $selectedVariants)
                                            ->get()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Textarea::make('notes')
                    ->placeholder('Write any addional information here')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_movements_count')
                    ->counts('stockMovements')
                    ->label('Item')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('stock_in_status_id')
                    ->label('Status')
                    ->options([
                        2 => 'Draft / Pending',
                        1 => 'Completed',
                        3 => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Stock In Details'),
                Tables\Actions\EditAction::make()
                    ->visible(fn(StockIn $record): bool => $record->stock_in_status_id === 2), // Only for Draft/Pending
                // Tables\Actions\DeleteAction::make()
                //     ->visible(fn(StockIn $record): bool => in_array($record->stock_in_status_id, [2])), // Only for Draft/Pending
            ])
            ->bulkActions([
                //
            ])
            ->recordUrl(fn(StockIn $record): string => Pages\ViewStockIn::getUrl(['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockIns::route('/'),
            'create' => Pages\CreateStockIn::route('/create'),
            'view' => Pages\ViewStockIn::route('/{record}'),
            'edit' => Pages\EditStockIn::route('/{record}/edit'),
        ];
    }
}
