<?php

namespace App\Http\Controllers;

use App\Models\POBatches;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TransactionResource;

class GenericController extends Controller
{
    public static function change_status($object, $model, $id)
    {
        $data = $model::withTrashed()->find($id);

        if ($data) {
            if ($data->trashed()) {
                $data->restore();

                return Response::restored($object, $data);
            } else {
                $data->delete();

                return Response::archived($object, $data);
            }
        } else {

            return Response::not_found();
        }
    }

    public static function storeTransaction($request, $document_id)
    {

        $context = $request->all();

        switch ($document_id) {

                //PAD
            case $document_id == 1:

                $po_group = count($request->po_group);
                $po_total_amount = 0;

                for ($i = 0; $i < $po_group; $i++) {
                    $po_total_amount += $request->po_group[$i]['po_amount'];
                }

                if (!(((abs($request->document_amount - $po_total_amount)) >= 0.00) && ((abs($request->document_amount - $po_total_amount)) < 1.00))) {
                    return Response::conflict('PO Amount does not match with Document Amount.', ["document_amount" => $request->document_amount, "po_total_amount" => $po_total_amount, "variance" => $request->document_amount - $po_total_amount]);
                }

                $transaction = Transaction::create([
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

                for ($i = 0; $i < $po_group; $i++) {
                    POBatches::create([
                        'transaction_id' => $transaction->id,
                        'po_number' => $request->po_group[$i]['po_number'],
                        'po_amount' => $request->po_group[$i]['po_amount'],
                        'po_total_amount' => $po_total_amount,
                        'rr_number' => $request->po_group[$i]['rr_number'],
                    ]);
                }

                $transaction = Transaction::find($transaction->id);

                return Response::created('Transaction', new TransactionResource($transaction));
                break;
        }
    }

    public static function updateTransaction($model, $request, $id)
    {

        $transaction = $model::find($id);
        $status = ucfirst($request->status);

        if ($transaction) {

            if ($status === 'Tag') {

                $transaction->status = $status;
                $transaction->state = $status;
                $transaction->remarks = $request->remarks;
                
                $transaction->save();

            } elseif ($status === 'Hold') {

                $transaction->status = $status;
                $transaction->state = $status;
                $transaction->remarks = $request->remarks;

                $transaction->save();

            } elseif ($status === 'Void') {

                $transaction->status = $status;
                $transaction->state = $status;
                $transaction->remarks = $request->remarks;

                $transaction->save();
                
            } elseif ($status === 'Returned') {
                $transaction->is_received = 0;
                $transaction->status = 'Returned';
                $transaction->state = 'Returned';
                $transaction->remarks = $request->remarks;

                $transaction->save();
            }

            return Response::updated('Transaction', new TransactionResource($transaction));
        }

        return Response::transaction_not_found();
    }
}
