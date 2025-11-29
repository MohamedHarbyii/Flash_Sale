<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductCache
{
    public static function get($product_id) {
    $product=Cache::rememberForever("static.$product_id",function()use($product_id){
        return Product::query()->
         where('id','=',$product_id)->
         firstOrFail(['name','price','id'])->toArray();
    });
   $product['stock']= Cache::rememberForever("stock.$product_id",function()use($product_id){
        return Product::query()->
        where('id','=',$product_id)->value('stock');

    });
   return $product;
    }
    public static function UpdateStock($product_id,$new_stock):void
    {

        Cache::put("stock.$product_id",$new_stock);
    }
}
