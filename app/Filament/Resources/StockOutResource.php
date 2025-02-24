<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOutResource\Pages;
use App\Models\StockOut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class StockOutResource extends Resource
{
    protected static ?string $model = StockOut::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?int $navigationSort = 30;

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

                Forms\Components\Select::make('stock_out_status_id')
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
                            <li><strong>Draft</strong>: Stok belum keluar gudang</li>
                            <li><strong>Completed</strong>: Stok sudah keluar gudang, final dan tidak dapat dirubah</li>
                            <li><strong>Cancelled</strong>: Stok dibatalkan</li>
                        </ul>
                    ')),

                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Repeater::make('stockMovements')
                            ->relationship()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Forms\Get $get): array {
                                return [
                                    ...$data,
                                    'warehouse_id' => $get('warehouse_id'),
                                    'type' => 'out',
                                    'stock_movement_status_id' => $get('stock_out_status_id'),
                                ];
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Forms\Get $get): array {
                                return [
                                    ...$data,
                                    'warehouse_id' => $get('warehouse_id'),
                                    'type' => 'out',
                                    'stock_movement_status_id' => $get('stock_out_status_id'),
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
                    ->placeholder('Write any additional information here')
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
                Tables\Filters\SelectFilter::make('stock_out_status_id')
                    ->label('Status')
                    ->options([
                        2 => 'Draft / Pending',
                        1 => 'Completed',
                        3 => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Stock Out Details'),
                Tables\Actions\EditAction::make()
                    ->visible(fn(StockOut $record): bool => $record->stock_out_status_id === 2),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(StockOut $record): bool => $record->stock_out_status_id === 2),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOuts::route('/'),
            'create' => Pages\CreateStockOut::route('/create'),
            'view' => Pages\ViewStockOut::route('/{record}'),
            'edit' => Pages\EditStockOut::route('/{record}/edit'),
        ];
    }
}
