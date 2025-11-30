<?php

namespace App\Classes;

use App\Models\order;
use App\Models\payment;
use App\Models\Product;
use App\PaymentStatus;
use Faker\Provider\Payment as ProviderPayment;
use Illuminate\Support\Facades\DB;

class PaymentActions
{
    public static function Success($data)
    {
        /**
         * I'll take the TxID - Order ID
         * store the data in the payment table {TxID - Order ID}
         * change the order status from pending to paid
         * all of this should be done in a transaction
         */
        // if (PaymentDB::HasTransaction($data->transaction_id)) {
        //     return null;
        // }

        return
            DB::transaction(function () use ($data) {
                $order = order::where('id', $data['order_id'])->lockForUpdate()->first();
                if($order['status']==PaymentStatus::Cancelled) {return;}
                $payment=PaymentDB::store(['transaction_id'=>$data['transaction_id'],'order_id'=>$data['order_id']]);
                OrderDB::UpdateStatus($order, PaymentStatus::Payed);

                return $payment;

            });

    }

    public static function Cancel($data)
    {

        // if (PaymentDB::HasTransaction($data->transaction_id)) {
        //     return null;
        // }

        return
            DB::transaction(function () use ($data) {
                $order = order::where('id', $data['order_id'])->lockForUpdate()->first();
                if($order['status']==PaymentStatus::Cancelled) {return;}
                $payment=PaymentDB::store(['transaction_id'=>$data['transaction_id'],'order_id'=>$data['order_id']]);
                OrderDB::UpdateStatus($order, PaymentStatus::Cancelled);
                $product=$order->product;
                ProductDB::IncrementStock($product,$order->quantity);
                return false;


            });
    }
}
