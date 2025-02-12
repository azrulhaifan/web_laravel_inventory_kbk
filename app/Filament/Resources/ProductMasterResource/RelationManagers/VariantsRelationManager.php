<?php

namespace App\Filament\Resources\ProductMasterResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
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
            ]);
    }

    protected function generateName($masterId, $colorId, $size, Forms\Set $set): void
    {
        if (!$masterId || !$colorId || !$size) {
            return;
        }

        $masterName = \App\Models\ProductMaster::find($masterId)?->name;
        $colorName = \App\Models\Color::find($colorId)?->name;
        
        $name = "{$masterName} - {$colorName} - {$size}";
        $set('name', $name);
    }

    protected function generateSku($masterId, $colorId, $size, Forms\Set $set): void
    {
        if (!$masterId || !$colorId || !$size) {
            return;
        }

        $masterSku = \App\Models\ProductMaster::find($masterId)?->sku;
        $colorCode = \App\Models\Color::find($colorId)?->code;
        
        $sku = "{$masterSku} - {$colorCode} - {$size}";
        $set('sku', $sku);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productMaster.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('color.name')
                    ->searchable()
                    ->sortable(),
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
