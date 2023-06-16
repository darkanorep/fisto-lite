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

                if ($transaction->state == 'AP-Returned' || $transaction->status == 'Pending') {

                    $transaction->update([
                        $transaction->status()->update(['is_received' => 1]),
                        $transaction->status = 'Received',
                        $transaction->state = Auth::user()->role . '-Received',
                        $transaction->phase = Auth::user()->role
                    ]);
    
                    return Response::transaction_received('Transaction', new TransactionResource($transaction));
                }

            } elseif (Auth::user()->role == 'AP Associate') {

                // $validation = $transaction->whereHas('status', function ($query) {
                //     $query->where('is_ap_tag_approved', 1);
                // })->where('state', 'AP-Tagged')->first();

                // if (!$validation) {
                //     return Response::transaction_not_found();
                // }

                // if ($validation) {

                //     $transaction->update([
                //         $transaction->status()->update(['is_received' => 1]),
                //         $transaction->status = 'Received',
                //         $transaction->state = Auth::user()->role . '-Received',
                //         $transaction->phase = Auth::user()->role
                //     ]);
    
                //     return Response::transaction_received('Transaction', new TransactionResource($transaction));

                // }

                $transaction->update([
                    $transaction->status()->update(['is_received' => 1]),
                    $transaction->status = 'Received',
                    $transaction->state = Auth::user()->role . '-Received',
                    $transaction->phase = Auth::user()->role
                ]);

                return Response::transaction_received('Transaction', new TransactionResource($transaction));

            }
        }

        return Response::transaction_not_found();
    }

    public function updateTransaction(Request $request, $id)
    {

        return GenericController::updateTransaction($request, $id);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
