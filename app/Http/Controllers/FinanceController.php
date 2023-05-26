<?php

namespace App\Http\Controllers;

use App\Http\Response\Response;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FinanceController extends Controller
{

    public function voucher_approve(Request $request) {
        $voucher = Voucher::find($request->id);

        if (!$voucher) {
            return Response::not_found();
        } elseif($voucher->status == 'approved') {
            return Response::conflict('Voucher already approved.', $voucher);
        
        }elseif($voucher->status == 'rejected'){
            return Response::conflict('Voucher already rejected.', $voucher);
        } else {
            $authorized = null;

            if ($voucher->amount <= 500000) {
                $authorized = User::where('role', 'Finance Supervisor')->first();
            } elseif ($voucher->amount <= 1000000000) {
                $authorized = User::where('role', 'Finance Manager')->first();
            } else {
                $authorized = User::where('role', 'Finance Director')->first();
            }

            if ($authorized) {
                // Perform approval actions with the authorized user
                if ($authorized->id === Auth::user()->id) {
                    // Authorized user is the current authenticated user
                    if ($request->is_approved == true) {
                        $voucher->status = 'approved';
                        $voucher->is_approved = true;
                        $voucher->state = 'transmitted';
                        $voucher->approved_by = Auth::user()->only('id','first_name','last_name','role');
                    } else {
                        $voucher->status = 'rejected';
                        $voucher->is_approved = false;
                    }
        
                    $voucher->save();
                    return Response::success('Voucher status updated.', $voucher);
                } else {
                    // Unauthorized user trying to approve the voucher
                    return Response::conflict('Only '.$authorized->role.' can approve this voucher.', $voucher);
                }
            } else {
                // Handle the case where no authorized user is found
                return response("No authorized user found for voucher approval.");
            }
        }
        
    }
    
}
