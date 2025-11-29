<?php

namespace App;

use App\Http\Controllers\ProductCache;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function()use($product_id,$qty){
           $product=Product::where('id',$product_id)->lockForUpdate()->first();

           $product_stock=$product->stock;

           if($qty>$product_stock){throw new \Exception("Out of stock"); }

           $new_stock=$product_stock-$qty;

           ProductDB::UpdateStock($product,$new_stock);

           $hold=HoldDB::create(['product_id'=>$product_id,'quantity'=>$qty]);

           DB::afterCommit(function()use($product_id,$new_stock){

               ProductCache::UpdateStock($product_id,$new_stock);

           });
           return $hold;
        });
    }
}
