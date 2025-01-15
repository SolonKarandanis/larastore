<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class ProductVariationTypes extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $navigationIcon = 'heroicon-c-photo';

    public function form(Form $form): Form
    {
        return $form
                ->schema([

                ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
