<?php

namespace App\Http\Controllers;

use App\Classes\PaymentActions;
use App\Classes\PaymentDB;
use App\Http\Requests\StorepaymentRequest;
use App\Http\Requests\UpdatepaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\payment;
use Illuminate\Support\Facades\Log;

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
        $data = $request->validated();
        if (PaymentDB::HasTransaction($request->transaction_id)) {
            Log::channel('flash_sales')->info("♻️ Duplicate Webhook ignored: {$request->transaction_id}");

            return response()->json(['message' => 'proccessed before']);
        }
        if ($request->status == 'success') {
            Log::channel('flash_sales')->
            info("✅ Payment Success: {$request->transaction_id} for Order: {$request->order_id}");

            $paid = PaymentActions::Success($data);

            return response()->json(['data' => new PaymentResource($paid), 'message' => 'done successfully']);
        } elseif ($request->status == 'failed') {
            Log::channel('flash_sales')->
            warning("❌ Payment Failed: {$request->transaction_id} for Order: {$request->order_id}");

            PaymentActions::Cancel($data);

            return response()->json(['data' => null, 'message' => 'something went wrong successfully']);
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
