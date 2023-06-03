<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Response\Response;
use App\Models\POBatches;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
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
    public function store(TransactionRequest $request)
    {
        $user = Auth::user();

        if ($user->role == 'Requestor') {

            return GenericController::storeTransaction($request, $request->document_id);
        }

        return Response::unauthorized('You are not authorized to perform this action.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, $id)
    {
        $pad = Transaction::with('poBatches')->find($id);

        if ($pad) {

            $context = $request->all();

            $po_group = count($context['po_group']);
            $po_total_amount = 0;

            for ($i = 0; $i < $po_group; $i++) {
                $po_total_amount += $context['po_group'][$i]['po_amount'];
            }

            if (!(((abs($request->document_amount - $po_total_amount)) >= 0.00) && ((abs($request->document_amount - $po_total_amount)) < 1.00))) {
                return Response::conflict('PO Amount does not match with Document Amount.', ["document_amount" => $request->document_amount, "po_total_amount" => $po_total_amount, "variance" => $request->document_amount - $po_total_amount]);
            }


            $transaction = Transaction::updated([
                'user_id' => Auth::user()->id,
                'document_id' => $context['document_id'],
                'category_id' => $context['category_id'],
                'document_no' => $context['document_no'],
                'request_date' => now(),
                'document_amount' => $context['document_amount'],
                'document_date' => $context['document_date'],
                'company_id' => $context['company_id'],
                'location_id' => $context['location_id'],
                'supplier_id' => $context['supplier_id'],
                'remarks' => $context['remarks'],
                'po_group' => $context['po_group']
            ]);

            $pad->poBatches()->where('transaction_id', $pad->id)->delete();

            for ($i = 0; $i < $po_group; $i++) {
                $pad->poBatches()->create([
                    'po_number' => $request->po_group[$i]['po_number'],
                    'po_amount' => $request->po_group[$i]['po_amount'],
                    'po_total_amount' => $po_total_amount,
                    'rr_number' => $request->po_group[$i]['rr_number'],
                ]);
            }

            $transaction = Transaction::find($pad->id);

            return Response::updated('Transaction', new TransactionResource($transaction));

        }

        return Response::transaction_not_found();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
