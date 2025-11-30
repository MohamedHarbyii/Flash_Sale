<?php

namespace App\Classes;

use App\Http\Controllers\ProductCache;
use App\Jobs\HoldRelease;
use App\Models\Product;

use Illuminate\Support\Facades\Log ;

class CreateHold
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function Create($product_id,$qty) {
        $product=Product::where('id',$product_id)->first();
     $updated=ProductDB::DecrementStock($product,$qty);
     if(!$updated){abort(409,'out of stock');}
     ProductCache::UpdateStock($product_id,$product->stock-$qty);
     $hold=HoldDB::create(['product_id'=>$product_id,'quantity'=>$qty]);
     HoldRelease::dispatch()->delay(now()->addMinutes(2));
     return $hold;
    }
}
