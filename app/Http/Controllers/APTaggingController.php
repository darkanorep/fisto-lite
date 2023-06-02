<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Http\Response\Response;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class APTaggingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function received($id)
    {
        $transaction = Transaction::find($id);

        if ($transaction->is_received === 0) {

            $transaction->update([
                $transaction->is_received = 1,
                $transaction->status = 'Received',
                $transaction->state = 'Received'
            ]);

            return Response::updated('Transaction', new TransactionResource($transaction));
        }
    }

    public function returned(Request $request, $id) {
        
        $transaction = Transaction::find($id); 

        if ($transaction->is_received === 1) {

            $transaction->update([
                $transaction->is_received = 1,
                $transaction->status = 'Returned',
                $transaction->state = 'Returned',
                $transaction->remarks = $request->remarks
            ]);

            return Response::updated('Transaction', new TransactionResource($transaction));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
