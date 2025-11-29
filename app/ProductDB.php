<?php

namespace App;

use App\Models\Product;

class ProductDB
{
    /**
     * Create a new class instance.
     */

    public function __construct()
    {

    }
    public static function UpdateStock(Product $product,$new_stock) {
          $product->stock=$new_stock;
          $product->save();
    }
}
