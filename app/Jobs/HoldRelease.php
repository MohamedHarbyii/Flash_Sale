<?php

namespace App\Jobs;

use App\Classes\ProductDB;
use App\Http\Controllers\ProductCache;
use App\Models\Hold;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HoldRelease implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */


    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /*
         * الجوب دي وظيفتها الاساسيه انها هتشوف الهولدز المنتهي وهتاخد الكميه اللي فيها ترجعها للبروداكت
         * */
        /*
         * select holds where now-expire>2 min
         * upate stock to proudcts
         * */
            $holds=Hold::with('product')->where('expires_at','<',now())->get();
            foreach ($holds as $hold) {
                $product=$hold->product;
                $updated = ProductDB::IncrementStock($product,$hold->quantity);
                if(!$updated){Log::error("Error with updating the stock");}
                ProductCache::UpdateStock($product->id,$product->stock);
                $hold->delete();
            };

    }
}
