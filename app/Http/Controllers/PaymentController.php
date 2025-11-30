<?php

namespace App\Http\Controllers;

use App\Classes\PaymentActions;
use App\Classes\PaymentDB;
use App\Models\payment;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorepaymentRequest;
use App\Http\Requests\UpdatepaymentRequest;
use App\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorepaymentRequest $request)
    {
        $data=$request->validated();
        if(PaymentDB::HasTransaction($request->transaction_id))
        {
            return response()->json(['message'=>'proccessed before']);
        }
        if($request->status=='success') {

        
        $paid=PaymentActions::Success($data) ;
        
        return response()->json(['data'=>new PaymentResource($paid),'message'=>'done successfully']);
    }
    elseif($request->status=='failed') {
         PaymentActions::Cancel($data) ;
        
        return response()->json(['data'=>null,'message'=>'something went wrong successfully']);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatepaymentRequest $request, payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(payment $payment)
    {
        //
    }
}
