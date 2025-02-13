<?php

namespace App\Filament\Resources\ProductBundleResource\RelationManagers;

use App\Models\ProductBundleVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $variant = \App\Models\ProductVariant::find($state);
                                        if (!$variant) return;

                                        $bundleSku = $this->ownerRecord->sku;
                                        $bundleName = $this->ownerRecord->name;

                                        $variantInfoSku = "{$variant->color->code} - {$variant->size}";
                                        $variantInfoName = "{$variant->color->name} - {$variant->size}";

                                        $set('../../sku', "{$bundleSku} - {$variantInfoSku}");
                                        $set('../../name', "{$bundleName} - {$variantInfoName}");
                                    }),
                            ])
                            ->columns(1)
                            ->defaultItems(2)
                            ->minItems(2)
                            ->required()
                            ->reorderable(false)
                    ]),
                Forms\Components\Section::make('Basic Information')
                    ->description('SKU & Name is automatically generated from bundle items. While you can edit them, it is recommended to leave them as is.')
                    // ->extraAttributes(['class' => 'flex items-center justify-between'])
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->unique(ProductBundleVariant::class, 'sku', ignoreRecord: true)
                            ->maxLength(255)
                            ->default(fn() => $this->ownerRecord->sku)
                            ->readonly(fn(Forms\Get $get): bool => ! $get('enable_editing')),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn() => $this->ownerRecord->name)
                            ->readonly(fn(Forms\Get $get): bool => ! $get('enable_editing')),

                        Forms\Components\Toggle::make('enable_editing')
                            ->label('Enable SKU & Name editing')
                            ->live()
                            ->default(false)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
