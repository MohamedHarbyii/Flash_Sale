<?php

namespace App\Classes;

use App\Models\Hold;
use Exception;
use Illuminate\Database\QueryException;

class HoldDB
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public static function getValid($id) {
        
         $hold=Hold::where('id',$id)->where('expires_at','>=',now())->first();
        if(!$hold){
            throw new \Exception('expired hold');
        }
        return $hold;
    }
    public static function create($data) {
       $data['expires_at']=now()->addMinutes(2);
        $hold= Hold::create($data);
       
       return $hold;
    }
}
