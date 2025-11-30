<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductCache extends Controller
{
    public static function get($product_id) {
    $product=Cache::rememberForever("static.$product_id",function()use($product_id){
         Product::where('id',$product_id)->except('stock');
    });
   $product['stock']= Cache::rememberForever("stock.$product_id",function()use($product_id){
        return Product::findOrFail($product_id)->only('stock');
        
    });
    }
}
