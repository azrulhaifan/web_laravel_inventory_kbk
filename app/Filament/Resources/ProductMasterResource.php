<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductMasterResource\Pages;
use App\Filament\Resources\ProductMasterResource\RelationManagers;
use App\Models\ProductMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class ProductMasterResource extends Resource
{
    protected static ?string $model = ProductMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Master Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->mask("999999999999")
                    ->stripCharacters('.,')
                    ->minValue(0)
                    ->suffix('g'),
                Forms\Components\Section::make('Pricing')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),
                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->suffix('g'),
                Tables\Columns\TextColumn::make('total_component_price')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductMasters::route('/'),
            'create' => Pages\CreateProductMaster::route('/create'),
            'edit' => Pages\EditProductMaster::route('/{record}/edit'),
        ];
    }
}
