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
        // $document_type = json_decode($request->input('document_type', []));
        // $supplier = json_decode($request->input('supplier', []));

        $requestor = Auth::user()->role == 'Requestor';
        $ap = Auth::user()->role == 'AP';
        $apAssoc = Auth::user()->role == 'AP Associate';

        $transaction = Transaction::
            with('users')
                ->with('documents')
                ->with('categories')
                ->with('companies')
                ->with('locations')
                ->with('suppliers')
                ->with('poBatches')
            // when($requestor, function ($query) use ($status) {
            //     $query->whereIn('phase', ['Requestor', 'AP', 'AP Associate'])
            //     ->when($status, function ($query) use ($status) {
            //         $query->where('status', $status);
            //     });
            // })
            // ->when($ap, function ($query) use ($status) {
            //     $query->orWhere(function ($query) use ($status) {
            //         $query->whereIn('phase', ['AP', 'AP Associate'])
            //             ->when($status, function ($query) use ($status) {
            //                 $query->where('status', $status);
            //             });
            //     });
            // })
            // ->when($apAssoc, function ($query) use ($status) {
            //     $query->orWhere(function ($query) use ($status) {
            //         $query->whereIn('phase', ['AP Associate', 'Finance Supervisor', 'Finance Manager', 'Finance Director'])
            //             ->when($status, function ($query) use ($status) {
            //                 $query->where('status', $status);
            //             });
            //     });
            // })
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
            ->orWhere(function ($query) use ($search) {
                $query->where('document_no', 'like', "%$search%")
                    ->orWhere('tag_no', 'like', "%$search%");
            })
            // ->where('user_id', Auth::user()->id)
            ->whereBetween('request_date', [$date_from, $date_to]);

        $transactions = $transaction->latest('updated_at')->paginate($rows);

        return count($transactions) ? Response::fetch('Transaction', TransactionResource::collection($transactions)) : Response::transaction_not_found();
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


            switch ($request->document_id) {

                case $request->document_id == 1: //PAD

                    $validation = $transaction->where('state', 'AP-Returned')->first();

                    if (!$validation) {
                        return Response::transaction_not_found();
                    }

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



                case $request->document_id == 2: //PRM Common

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

                case $request->document_id == 5: //Contractor's Billing

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
                    $transaction->request_date = now();
                    $transaction->document_amount = $context['document_amount'];
                    $transaction->document_date = $context['document_date'];
                    $transaction->company_id = $context['company_id'];
                    $transaction->location_id = $context['location_id'];
                    $transaction->supplier_id = $context['supplier_id'];
                    $transaction->capex = $context['capex'];
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

                    return Response::updated('Transaction', new TransactionResource($updatedTransaction));
                    break;

                case $request->document_id == 7: //Payroll

                    $transaction->user_id = Auth::user()->id;
                    $transaction->document_id = $context['document_id'];
                    $transaction->from_date = $context['from_date'];
                    $transaction->to_date = $context['to_date'];
                    $transaction->document_amount = $context['document_amount'];
                    $transaction->company_id = $context['company_id'];
                    $transaction->department_id = $context['department_id'];
                    $transaction->location_id = $context['location_id'];
                    $transaction->supplier_id = $context['supplier_id'];
                    $transaction->remarks = $context['remarks'];

                    return Response::updated('Transaction', new TransactionResource($transaction));
                    break;
            }
        }

        return Response::transaction_not_found();
    }



    public function void(Request $request, $id)
    {

        $transaction = Transaction::whereIn('status', ['Pending', 'Returned'])
            ->where('user_id', Auth::user()->id)
            ->find($id);

        if ($transaction) {

            $transaction->status = 'Void';
            $transaction->state = 'Requestor-Void';
            $transaction->remarks = $request->remarks;
            $transaction->save();

            return Response::updated('Transaction', $transaction);
        }

        return Response::not_found();
    }
}
