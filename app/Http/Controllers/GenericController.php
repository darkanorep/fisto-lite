<?php

namespace App\Http\Controllers;

use App\Models\POBatches;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TransactionRequest;
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


                //PRM Common
            case $document_id == 2:

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
                    'remarks' => $context['remarks']
                ]);

                $transaction = Transaction::find($transaction->id);

                return Response::created('Transaction', new TransactionResource($transaction));
                break;

                //Contractor's Billing
            case $document_id == 5:

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
                    'request_date' => now(),
                    'document_amount' => $context['document_amount'],
                    'document_date' => $context['document_date'],
                    'company_id' => $context['company_id'],
                    'location_id' => $context['location_id'],
                    'supplier_id' => $context['supplier_id'],
                    'po_group' => $context['po_group'],
                    'capex' => $context['capex'],
                    'remarks' => $context['remarks'],
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

            case $document_id == 7:

                $transaction = Transaction::create([
                    'user_id' => Auth::user()->id,
                    'document_id' => $context['document_id'],
                    'from_date' => $context['from_date'],
                    'to_date' => $context['to_date'],
                    'document_amount' => $context['document_amount'],
                    'company_id' => $context['company_id'],
                    'department_id' => $context['department_id'],
                    'location_id' => $context['location_id'],
                    'supplier_id' => $context['supplier_id'],
                    'remarks' => $context['remarks'],
                ]);

                return Response::created('Transaction', new TransactionResource($transaction));
                break;
        }
    }

    public static function updateTransaction($model, $request, $id)
    {

        $transaction = $model->whereNotIn('status', ['Void'])->find($id);
        $status = ucfirst($request->status);

        if ($transaction) {

            if (Auth::user()->role === 'AP') {

                switch ($status) {

                    case 'Return':
    
                        if ($model->whereIn('status', ['Returned'])->find($id)) {
                            $transaction->state = Auth::user()->role . '-Returned';
                            $transaction->save();
                            
                            return Response::conflict('Transaction is already ' . $status, new TransactionResource($transaction));
                        }
    
                        $modelInstance = $model->where(function ($query) {
                            $query->where('is_received', 0)
                                ->orWhere('is_ap_tag_approved', 1);
                        })->find($id);
                        
                        if ($modelInstance) {
                            return Response::transaction_not_found();
                        }
                        
                        $transaction->status = 'Returned';
                        $transaction->state = Auth::user()->role . '-Returned';
                        $transaction->is_received = 0;
                        $transaction->is_ap_tag_received = 0;
                        $transaction->is_ap_tag_approved = 0;
                        $transaction->remarks = $request->remarks;
                        $transaction->save();
    
                        return Response::transaction_received('Transaction', new TransactionResource($transaction));
    
                        break;
    
                    case 'Hold':
    
                        if ($model->whereIn('status', ['Hold'])->find($id)) {
                            return Response::conflict('Transaction is already ' . $status, new TransactionResource($transaction));
                        }
    
                        $transaction->status = 'Hold';
                        $transaction->state = Auth::user()->role . '-Hold';
                        $transaction->remarks = $request->remarks;
                        $transaction->save();
    
                        break;
    
                    case 'Tag':
    
                        if ($model->whereIn('status', ['Tagged'])->find($id)) {
                            return Response::conflict('Transaction is already Tagged', new TransactionResource($transaction));
                        }
    
                        if ($model->whereIn('status', ['Returned', 'Pending'])->find($id)) {
                            return Response::transaction_not_found();
                        }
    
                        $transaction->tag_no
                            ? $tagNo = $transaction->tag_no
                            : $tagNo = rand(1000, 9999);
    
                        $transaction->status = 'Tagged';
                        $transaction->state = Auth::user()->role . '-Tagged';
                        $transaction->tag_no = $tagNo;
                        $transaction->is_received = 1;
                        $transaction->is_ap_tag_approved = 1;
                        $transaction->remarks = $request->remarks;
                        $transaction->save();
    
                        break;
                }

            } elseif (Auth::user()->role === 'AP Associate') {
                
                switch ($status) {

                    case 'Return':
    
                        if ($model->whereIn('status', ['Returned'])->find($id)) {
                            $transaction->state = Auth::user()->role . '-Returned';
                            $transaction->save();

                            return Response::conflict('Transaction is already ' . $status, new TransactionResource($transaction));
                        }

                        $transaction->status = 'Returned';
                        $transaction->state = Auth::user()->role . '-Returned';
                        $transaction->is_received = 0;
                        $transaction->is_ap_tag_approved = 0;
                        $transaction->is_ap_assoc_approved = 0;
                        $transaction->remarks = $request->remarks;
                        $transaction->save();
    
                        return Response::transaction_received('Transaction', new TransactionResource($transaction));
    
                        break;
                    
                    case 'Voucher':

                        if ($model->whereIn('status', ['Returned', 'Pending'])->find($id)) {
                            return Response::transaction_not_found();
                        }

                        $transaction->voucher_no
                        ? $voucherNo = $transaction->voucher_no
                        : $voucherNo = rand(1000, 9999);

                        $transaction->status = 'Vouchered';
                        $transaction->state = Auth::user()->role . '-Vouchered';
                        $transaction->voucher_no = $voucherNo;
                        $transaction->is_received = 1;
                        $transaction->is_ap_assoc_approved = 1;
                        $transaction->voucher_date = $request->voucher_date;
                        $transaction->remarks = $request->remarks;
                        $transaction->save();


                        break;
                }
            }

            return Response::updated('Transaction', new TransactionResource($transaction));
        }

        return Response::transaction_not_found();
    }
}
