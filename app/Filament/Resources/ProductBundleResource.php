<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductBundleResource\Pages;
use App\Filament\Resources\ProductBundleResource\RelationManagers;
use App\Models\ProductBundle;
use App\Models\ProductMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductBundleResource extends Resource
{
    protected static ?string $model = ProductMaster::class;

    protected static ?string $modelLabel = 'Product Bundle';
    protected static ?string $pluralModelLabel = 'Product Bundles';

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 120;
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Product Bundles';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_bundling', 1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('is_bundling')
                    ->default(1),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bundle_variants_count')
                    ->counts('bundleVariants')
                    ->label('Variants'),
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
            'index' => Pages\ListProductBundles::route('/'),
            'create' => Pages\CreateProductBundle::route('/create'),
            'edit' => Pages\EditProductBundle::route('/{record}/edit'),
        ];
    }
}
