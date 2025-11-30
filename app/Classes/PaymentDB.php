<?php
namespace App\Classes;

use App\Models\payment;

class PaymentDB 
{
    public static function store($data) {
       return  payment::create($data);
    }
    public static function HasTransaction($TxID) {
       return payment::where('transaction_id','=',$TxID)->exists();
    }
}