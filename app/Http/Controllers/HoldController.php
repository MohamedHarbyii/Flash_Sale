<?php

namespace App\Http\Controllers;

use App\CreateHold;
use App\Models\Hold;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHoldRequest;
use App\Http\Requests\UpdateHoldRequest;

class HoldController extends Controller
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
    public function store(StoreHoldRequest $request)
    {
        return   CreateHold::Create($request->product_id,$request->qty);
    }

    /**
     * Display the specified resource.
     */
    public function show(Hold $hold)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hold $hold)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHoldRequest $request, Hold $hold)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hold $hold)
    {
        //
    }
}
