<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferenceRequest;
use App\Http\Response\Response;
use App\Models\Reference;
use Illuminate\Http\Request;

class ReferenceController extends Controller
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

        $query = Reference::withTrashed()
        ->where(function ($query) use ($search) {
            $query->where('type', 'like', "%$search%")
            ->orWhere('description', 'like', "%$search%");
        });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $reference = $query->latest('updated_at')->paginate($rows);
        } else {

            $reference = $query->get(['id', 'type', 'description']);
        }

        return count($reference) ? Response::fetch('Reference', $reference) : Response::not_found();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ReferenceRequest $request)
    {
        $reference = Reference::create([
            'type' => $request->type,
            'description' => $request->description
        ]);

        return Response::created('Reference', Reference::find($reference->id));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $reference = Reference::find($id);

        if ($reference) {
            
            return Response::single_fetch('Reference', $reference);
        }

        return Response::not_found();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ReferenceRequest $request, $id)
    {
        $reference = Reference::find($id);

        if ($reference) {
            $reference->update([
                'type' => $request->type,
                'description' => $request->description
            ]);

            $reference->save();

            return Response::updated('Reference', $reference);
        }
        
        return Response::not_found();
    }

    public function change_status($id) {
        return GenericController::change_status('reference', Reference::class, $id);
    }

}
