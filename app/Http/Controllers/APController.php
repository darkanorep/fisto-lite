<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagNumberRequest;
use App\Http\Response\Response;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Http\Request;

class APController extends Controller
{
    public function received(Request $request) {
        
        $docs = Transaction::find($request->id);

        if ($docs) {

            if ($docs->is_ap_approved == true) {
                return Response::success('Already approved this request.', $docs);

            } elseif ($docs->status == 'reject') {

                return Response::success('Already rejected this request.', $docs);

            } else {
                if ($request->status == false) {
                    $docs->update([
                        $docs->is_ap_received = true,
                        $docs->status = 'reject',
                        $docs->state = $request->state
                    ]);

                    return Response::success('Rejected', $docs);

                } else {
                    $docs->update([
                        $docs->is_ap_approved = true,
                        $docs->is_ap_received = true,
                        $docs->status = 'approved'
                    ]);

                    return Response::success('Approved', $docs);
                }

                $docs->save();

            }

        } else {
            return Response::not_found();
        }
    }



    public function issuing_tag_no(TagNumberRequest $request, $id) {
        $docs = Transaction::find($id);

        if (!$docs) {
            return Response::not_found();
        } elseif ($docs->is_ap_approved == false) {
            return Response::success('This request was not approved.', $docs);
        } elseif ($docs->tag_no !== null) {
            return Response::success('Tag number already issued.', $docs);
        } else {
            if ($docs->is_ap_approved == true) {
                $docs->is_ap_approved = true;
                $docs->tag_no = $request->tag_no;
                $docs->status = 'approved';

                $docs->save();

                return Response::success('Tag number issued.', $docs);
            } else {
                return Response::success('This request was not approved.', $docs);
            }
        }
    }


    public function voucher_creation(Request $request) {
        $docs = Transaction::find($request->transaction_id);

        if (!$docs) {
            return Response::not_found();
        } elseif ($docs->is_ap_approved == false) {
            return Response::conflict('This request was not approved.',  $docs);
        } elseif(Voucher::where('transaction_id', $request->transaction_id)->exists()) {
            return Response::conflict('Voucher already created.',  $docs);
        } else {
            Voucher::create([
                'transaction_id' => $request->transaction_id,
                'amount' => $request->amount
            ]);

            $voucher = Voucher::where('transaction_id', $request->transaction_id)->first();

            return Response::created('Voucher created.', $voucher);
        }
    }
}
