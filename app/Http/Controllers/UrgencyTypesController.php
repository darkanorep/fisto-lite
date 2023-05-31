<?php

namespace App\Http\Controllers;

use App\Http\Requests\UrgencyTypeRequest;
use App\Http\Response\Response;
use App\Models\UrgencyTypes;
use Illuminate\Http\Request;

class UrgencyTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', true);
        $paginate = $request->input('paginate', true);
        $rows = $request->input('rows', 10);
        $search = $request->input('search', '');

        $query = UrgencyTypes::withTrashed()
        ->where(function ($query) use ($search) {
            $query->where('type', 'like', '%'.$search.'%')
            ->orWhere('transaction_days', 'like', '%'.$search.'%');
        });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $urgencyTypes = $query->latest('updated_at')->paginate($rows);
        } else {
            $urgencyTypes = $query->get(['type', 'transaction_days']);
        }

        return count($urgencyTypes) ? Response::fetch('Urgency Types', $urgencyTypes) : Response::not_found();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UrgencyTypeRequest $request)
    {
        $urgencyTypes = UrgencyTypes::create([
            'type' => $request->type,
            'transaction_days' => $request->transaction_days,
        ]);

        return Response::created('Urgency Type', UrgencyTypes::find($urgencyTypes->id));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $urgencyTypes =  UrgencyTypes::find($id);

        if ($urgencyTypes) {

            return Response::single_fetch('Urgency Type', $urgencyTypes);
        }

        return Response::not_found();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UrgencyTypeRequest $request, $id)
    {
        $urgencyTypes = UrgencyTypes::find($id);

        if ($urgencyTypes) {

            $urgencyTypes->update([
                'type' => $request->type,
                'transaction_days' => $request->transaction_days,
            ]);

            $urgencyTypes->save();

            return Response::updated('Urgency Type', $urgencyTypes);
        }

        return Response::not_found();
    }

    public function change_status($id) {
        return GenericController::change_status('urgency type', UrgencyTypes::class, $id);
    }
}
