<?php
namespace App\Classes;

use App\Models\order;
use App\PaymentStatus;
use Illuminate\Support\Facades\DB;

class OrderDB
{
    public static function create($data) {
      return  DB::transaction(function()use($data){
        $data['status']=PaymentStatus::Pending;
        $hold=HoldDB::getValid($data['hold_id']);
        $data['total_price'] = $hold->quantity*$hold->product->price;
        $data['quantity']=$hold->quantity;
        $data['product_id']=$hold->product_id;
        $hold->delete();
        return order::create($data);
    });
    } 
    public static function get($id) {
        return order::findOrFail($id);
    }
    public static function UpdateStatus(order $order , $status) {
        $order->status=$status;
        $order->save();
    }
}