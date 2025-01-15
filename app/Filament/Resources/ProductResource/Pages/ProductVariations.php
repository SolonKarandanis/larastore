<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductVariationTypesEnum;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $title = 'Variation';

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variations);
        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingData):array
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;
        $cartesianProduct= $this->cartesianProduct($variationTypes,$defaultQuantity, $defaultPrice);
        $mergedResult =[];

        foreach ($cartesianProduct as $product){
            // Extract option IDs from the current product combination as an array
            $optionIds= collect($product)
                ->filter(fn($value,$key)=>str_starts_with($key,'variation_type_'))
                ->map(fn($option) =>$option['id'])
                ->values()
                ->toArray();

            //Find matching entry in existing data
            $match = array_filter($existingData, function($exisitingOption) use ($optionIds){
                return $exisitingOption['variation_type_option_ids'];
            });

            //If match is found, override quantity and price
            if(!empty($match)){
                $existingEntry = reset($match);
                $product['quantity']= $existingEntry['quantity'];
            }
            else{
                // Set default quantity and price if no match
                $product['quantity']= $defaultQuantity;
                $product['price']= $defaultPrice;
            }
            $mergedResult[] = $product;
        }
        return $mergedResult;
    }

    private function cartesianProduct($variationTypes, $defaultQuantity,$defaultPrice):array
    {
        $result=[[]];

        return $result;
    }


}
