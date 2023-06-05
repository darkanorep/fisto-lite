<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\POBatches;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use PhpParser\Node\Stmt\Return_;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'Pending');
        $search = $request->input('search', '');
        $rows = $request->input('rows', 10);
        $date_from = $request->input('date_from', date('Y-m-d'));
        $date_to = $request->input('date_to', date('Y-m-d'));

        $transaction = Transaction::when($status, function ($query, $status) {
            return $query->where('status', $status);
        })
            ->with('users')
            ->with('documents')
            ->with('categories')
            ->with('companies')
            ->with('locations')
            ->with('suppliers')
            ->with('poBatches')
            ->where(function ($query) use ($search) {
                $query->orWhereHas('users', function ($query) use ($search) {
                    $query->where('first_name', 'like', "%$search%");
                })
                    ->orWhereHas('documents', function ($query) use ($search) {
                        $query->where('type', 'like', "%$search%");
                    })
                    ->orWhereHas('categories', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('companies', function ($query) use ($search) {
                        $query->where('company', 'like', "%$search%");
                    })
                    ->orWhereHas('locations', function ($query) use ($search) {
                        $query->where('location', 'like', "%$search%");
                    })
                    ->orWhereHas('suppliers', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    })->orWhereHas('poBatches', function ($query) use ($search) {
                        $query->where('po_number', 'like', "%$search%");
                    });
            })
            ->where('user_id', Auth::user()->id)
            ->whereBetween('request_date', [$date_from, $date_to]);

        $transactions = $transaction->latest('updated_at')->paginate($rows);

        return count($transactions) ? Response::fetch('Transaction', TransactionResource::collection($transactions)) : Response::not_found();
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
        $transaction = Transaction::find($id);

        if ($transaction->status === 'Pending' || $transaction->status === 'Returned') {
            $context = $request->all();

            //PAD
            switch ($request->document_id) {

                case $request->document_id == 1:
                    $po_group = count($context['po_group']);
                    $po_total_amount = 0;

                    for ($i = 0; $i < $po_group; $i++) {
                        $po_total_amount += $context['po_group'][$i]['po_amount'];
                    }

                    if (!(((abs($request->document_amount - $po_total_amount)) >= 0.00) && ((abs($request->document_amount - $po_total_amount)) < 1.00))) {
                        return Response::conflict('PO Amount does not match with Document Amount.', ["document_amount" => $request->document_amount, "po_total_amount" => $po_total_amount, "variance" => $request->document_amount - $po_total_amount]);
                    }

                    $transaction->user_id = Auth::user()->id;
                    $transaction->document_id = $context['document_id'];
                    $transaction->category_id = $context['category_id'];
                    $transaction->document_no = $context['document_no'];
                    $transaction->request_date = now();
                    $transaction->document_amount = $context['document_amount'];
                    $transaction->document_date = $context['document_date'];
                    $transaction->company_id = $context['company_id'];
                    $transaction->location_id = $context['location_id'];
                    $transaction->supplier_id = $context['supplier_id'];
                    $transaction->remarks = $context['remarks'];
                    $transaction->save();

                    $transaction->poBatches()->where('transaction_id', $transaction->id)->delete();

                    for ($i = 0; $i < $po_group; $i++) {
                        $transaction->poBatches()->create([
                            'po_number' => $request->po_group[$i]['po_number'],
                            'po_amount' => $request->po_group[$i]['po_amount'],
                            'po_total_amount' => $po_total_amount,
                            'rr_number' => $request->po_group[$i]['rr_number'],
                        ]);
                    }

                    $updatedTransaction = Transaction::find($transaction->id);
                    $updatedTransaction->status = 'Pending';
                    $updatedTransaction->state = 'Pending';
                    $updatedTransaction->save();

                    return Response::updated('Transaction', $updatedTransaction);
                    break;

                
                //PRM Common
                case $request->document_id == 2:

                    $transaction->user_id = Auth::user()->id;
                    $transaction->document_id = $context['document_id'];
                    $transaction->category_id = $context['category_id'];
                    $transaction->document_no = $context['document_no'];
                    $transaction->request_date = now();
                    $transaction->document_amount = $context['document_amount'];
                    $transaction->document_date = $context['document_date'];
                    $transaction->company_id = $context['company_id'];
                    $transaction->location_id = $context['location_id'];
                    $transaction->supplier_id = $context['supplier_id'];
                    $transaction->remarks = $context['remarks'];
                    $transaction->save();

                    $updatedTransaction = Transaction::find($transaction->id);
                    $updatedTransaction->status = 'Pending';
                    $updatedTransaction->state = 'Pending';
                    $updatedTransaction->save();

                    return Response::updated('Transaction', $updatedTransaction);
                    break;
            }
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
