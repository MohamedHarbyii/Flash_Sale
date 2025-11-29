<?php

namespace App;

use App\Models\Hold;

class HoldDB
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function create($data) {
       $hold= Hold::create($data);
       $hold->expires_at=now()->addMinutes(2);
       return $hold->get(['id','expires_at']);
    }
}
