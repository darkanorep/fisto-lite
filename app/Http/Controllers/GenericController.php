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
use App\Models\Status;

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
                    'po_group' => $context['po_group'],
                    'phase' => Auth::user()->role
                ]);

                Status::create([
                    'transaction_id' => $transaction->id,
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
                    'remarks' => $context['remarks'],
                    'phase' => Auth::user()->role
                ]);

                Status::create([
                    'transaction_id' => $transaction->id,
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
                    'phase' => Auth::user()->role
                ]);

                Status::create([
                    'transaction_id' => $transaction->id,
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
                    'phase' => Auth::user()->role
                ]);

                Status::create([
                    'transaction_id' => $transaction->id,
                ]);

                return Response::created('Transaction', new TransactionResource($transaction));
                break;
        }
    }

    public static function updateTransaction($request, $id)
    {

        $transaction = Transaction::find($id);
        $status = ucfirst($request->status);

        if ($transaction) {

            if (Auth::user()->role === 'AP') {

                switch ($status) {

                    case 'Return':

                        // $validation = $transaction->whereHas('status', function ($query) {
                        //     $query->where('status.is_received', 0);
                        // })->find($id);

                        // if ($validation) {
                        //     return Response::transaction_not_found();
                        // }

                        $transaction->status = 'Returned';
                        $transaction->state = Auth::user()->role . '-Returned';
                        $transaction->status()->update([
                            'is_received' => 0,
                            'is_ap_tag_approved' => 0,
                            'is_returned' => 1
                        ]);
                        $transaction->remarks = $request->remarks;
                        $transaction->phase = Auth::user()->role;
                        $transaction->save();

                        return Response::returned('Transaction', new TransactionResource($transaction));

                        break;

                    case 'Tag':

                        // $validation = $transaction->whereHas('status', function ($query) {
                        //     $query->where('status.is_received', 0);
                        // })->find($id);

                        // if ($validation) {
                        //     return Response::transaction_not_found();
                        // }

                        $transaction->tag_no
                            ? $tagNo = $transaction->tag_no
                            : $tagNo = rand(1000, 9999);

                        $transaction->status = 'Pending';
                        $transaction->state = Auth::user()->role . '-Tagged';
                        $transaction->tag_no = $tagNo;
                        $transaction->status()->update([
                            'is_received' => 0,
                            'is_ap_tag_approved' => 1,
                            'is_returned' => 0
                        ]);
                        $transaction->remarks = $request->remarks;
                        $transaction->phase = Auth::user()->role;
                        $transaction->save();

                        break;
                }
            } elseif (Auth::user()->role === 'AP Associate') {

                switch ($status) {

                    case 'Voucher':

                        // $validation = $transaction->whereHas('status', function ($query) {
                        //     $query->where('status.is_ap_tag_approved', 0)
                        //     ->orWhere('is_received', 0)
                        //     ->orWhere('state', 'AP Associate-Received');
                        // })->find($id);

                        // if (!$validation) {
                        //     return Response::transaction_not_found();
                        // }

                        $transaction->status = 'Pending';
                        $transaction->state = Auth::user()->role . '-Voucher';
                        $transaction->voucher_no = $request->voucher_no;
                        $transaction->status()->update([
                            'is_received' => 0,
                            'is_ap_assoc_approved' => 1,
                            'is_returned' => 0
                        ]);
                        $transaction->phase = Auth::user()->role;
                        $transaction->save();

                        break;

                    case 'Return':

                        // $validation = $transaction->whereHas('status', function ($query) {
                        //     $query->where('status.is_ap_tag_approved', 0)
                        //     ->orWhere('is_received', 0);
                        // })->find($id);

                        // if ($validation) {
                        //     return Response::transaction_not_found();
                        // }

                        $transaction->status = 'Returned';
                        $transaction->state = Auth::user()->role . '-Returned';
                        $transaction->status()->update([
                            'is_received' => 0,
                            'is_ap_tag_approved' => 1,
                            'is_ap_assoc_approved' => 0,
                            'is_returned' => 1
                        ]);
                        $transaction->remarks = $request->remarks;
                        $transaction->phase = Auth::user()->role;
                        $transaction->save();

                        return Response::returned('Transaction', new TransactionResource($transaction));
                        break;
                }
            } elseif (Auth::user()->role == 'Finance Supervisor' || Auth::user()->role == 'Finance Manager' || Auth::user()->role == 'Finance Director') {

                $authorizedApprover = null;

                if ($transaction->document_amount <= 500000) {
                    $authorizedApprover = 'Finance Supervisor';
                } elseif ($transaction->document_amount <= 1000000000) {
                    $authorizedApprover = 'Finance Manager';
                } elseif ($transaction->document_amount < 1000000001) {
                    $authorizedApprover = 'Finance Director';
                }

                if ($authorizedApprover) {
                    
                    $transaction->status = 'Approved';
                    $transaction->state = $authorizedApprover . '-Approved';
                    $transaction->phase = $authorizedApprover;
                    $transaction->status()->update([
                        'is_finance_approved' => 1,
                    ]);
                    $transaction->save();

                    return Response::updated('Transaction', new TransactionResource($transaction));
                }

                return Response::unauthorized('Not authorized to approve this transaction, only ' . $authorizedApprover . ' can approve this transaction.');
            }

            return Response::updated('Transaction', new TransactionResource($transaction));
        }

        return Response::transaction_not_found();
    }
}
