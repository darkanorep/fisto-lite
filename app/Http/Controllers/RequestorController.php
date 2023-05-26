<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use App\Http\Requests\TagRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequestorController extends Controller
{
    public function index(Request $request)
    {

        $row = $request->input('row', 10);
        $page = $request->input('page', 1);
        $paginate = $request->input('paginate', true);
        $search = $request->input('search');

        $data = Transaction::with(['requestor' => function ($query) {
            $query->select('id', 'username', 'role');
        }])
            ->with(['form' => function ($query) {
                $query->select('id','form_type', 'name as form_name');
            }])
            ->with('voucher')
            ->where('requestor_id', Auth::user()->id)->paginate($row, ['*'], 'page', $page);

        return Response::success('Successfully fetched.', $data);
    }

    public function store(TagRequest $request)
    {

        return Response::created(
            'Request successfully created.',
            Transaction::create([
                'form_id' => $request->form_id,
                'requestor_id' => Auth::user()->id
            ])
        );
    }

    public function show($id)
    {
        $docs = Transaction::find($id);

        if ($docs) {
            if ($docs->requestor_id === Auth::user()->id) {
                return Response::success('Successfully fetched.', Transaction::with('voucher')->find($id));
            } else {
                return Response::not_found();
            }
        } else {
            return Response::not_found();
        }
    }
}
