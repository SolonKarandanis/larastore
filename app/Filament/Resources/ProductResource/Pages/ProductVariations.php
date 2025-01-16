<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductVariationTypesEnum;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $title = 'Variation';

    public function form(Form $form): Form
    {
        $types = $this->record->variationTypes;
        $fields = [];
        foreach ($types as $i => $type) {
            $fields[] = TextInput::make('variation_type_'.($type->id). '.id')
                ->hidden();
            $fields[] = TextInput::make('variation_type_'.($type->id). '.name')
                ->label($type->name);
        }
        return $form
                ->schema([
                    Repeater::make('variations')
                        ->label(false)
                        ->collapsible()
                        ->addable(false)
                        ->defaultItems(1)
                        ->columns(2)
                        ->columnSpan(2)
                        ->schema([
                            Section::make()
                                ->schema($fields)
                                ->columns(3),
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric(),
                            TextInput::make('price')
                                ->label('Price')
                                ->numeric(),
                        ])
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
        foreach ($variationTypes as $index => $variationType) {
            $temp=[];
            foreach ($variationType->options as $option) {
                //Add the current option to all existing combinations
                foreach ($result as $combination) {
                    $newCombination = $combination + [
                            'variation_type_' . ($variationType->id) => [
                                'id' => $option->id,
                                'name' => $option->name,
                                'label' => $variationType->name,
                            ]
                    ];
                    $temp[] = $newCombination;
                }
            }
            //Update results with the new combinations
            $result=$temp;
        }
        // Add quantity and price to completed combinations
        foreach ($result as $combination) {
            if(count($combination) == count($variationTypes)){
                $combination['quantity']= $defaultQuantity;
                $combination['price']= $defaultPrice;
            }
        }
        return $result;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        //Initialize an array to hold the formatted data
        $formattedData = [];
        //Loop through each variation to resttructure it
        foreach ($data['variations'] as $option){
            $variationTypeOptionIds= [];
            foreach ($this->record->variationTypes as $i => $variationType){
                $variationTypeOptionIds[]= $option['variation_type_'.($variationType->id)]['id'];
            }
            $quantity = $option['quantity'];
            $price = $option['price'];

            //Prepare the data structure for the database
            $formattedData[]=[
                'variation_type_option_ids'=>$variationTypeOptionIds,
                'quantity'=>$quantity,
                'price'=>$price,
            ];
        }
        $data['variations'] = $formattedData;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variations = $data['variations'];
        unset($data['variations']);

        $record->update($data);
        $record->variations()->delete();
        $record->variations()->createMany($variations);

        return $record;
    }

}
