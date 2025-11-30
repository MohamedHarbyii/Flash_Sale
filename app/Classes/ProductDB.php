<?php

namespace App\Classes;

use App\Models\Product;

class ProductDB
{
    /**
     * Create a new class instance.
     */

    public function __construct()
    {

    }
    public static function Find($id)
    {

      return  Product::findOrFail($id);
    }
    public static function UpdateStock(Product $product,$new_stock) {
          $product->stock=$new_stock;
          $product->save();
    }
    public static function IncrementStock(Product $product,$qty) {
        return $product->increment('stock',$qty);
    }
    public static function DecrementStock(Product $product,$qty) {
        return $product->where('stock','>=',$qty)->decrement('stock',$qty);

    }
}
