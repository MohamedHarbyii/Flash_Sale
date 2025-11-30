<?php

namespace App\Models;

use App\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory , HasUlids;
    protected $guarded = [];
    protected $casts = ['status'=>PaymentStatus::class];
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
