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

        if ($transaction) {

            if (Auth::user()->role == 'AP') {

                if ($transaction->state === 'Pending') {

                    $transaction->update([
                        $transaction->is_received = 1,
                        $transaction->status = 'Received',
                        $transaction->state = Auth::user()->role . '-Received'
                    ]);
    
                    return Response::updated('Transaction', new TransactionResource($transaction));
                }

            } elseif (Auth::user()->role == 'AP Associate') {

                if ($transaction->is_ap_tag_approved == 1) {

                    $transaction->update([
                        $transaction->is_received = 1,
                        $transaction->status = 'Received',
                        $transaction->state = Auth::user()->role . '-Received'
                    ]);
    
                    return Response::updated('Transaction', new TransactionResource($transaction));

                }

            }
        }

        return Response::transaction_not_found();
    }

    public function updateTransaction(Request $request, $id)
    {

        $transaction = new Transaction();

        return GenericController::updateTransaction($transaction, $request, $id);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
