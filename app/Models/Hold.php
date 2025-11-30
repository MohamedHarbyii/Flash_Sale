<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    /** @use HasFactory<\Database\Factories\HoldFactory> */
    use HasFactory,HasUlids;
    protected $fillable=['id','product_id','quantity','expires_at'];
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
