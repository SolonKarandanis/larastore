<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    protected $table = 'product_variations';
    public $timestamps = false;

    protected $casts=[
        'variation_type_option_ids'=>'json',
    ];
}
