<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Http\Response\Response;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $suppliers = Supplier::get();

        // return Response::fetch('Supplier', SupplierResource::collection($suppliers));

        $status = $request->input('status', true);
        $paginate = $request->input('paginate', true);
        $rows = $request->input('rows', 10);
        $search = $request->input('search', '');

        $query = Supplier::withTrashed()
        ->where(function ($query) use ($search) {
            $query->where('code', 'LIKE', "%$search%")
            ->orWhere('name', 'LIKE', "%$search%")
            ->orWhere('terms', 'LIKE', "%$search%");
        });
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create([
            'code' =>  $request->code,
            'name' => $request->name,
            'terms' => $request->terms,
            'urgency_type_id' => $request->urgency_type_id,           
        ]);

        $supplier->references()->attach($request->references);
        $supplier = Supplier::find($supplier->id);

        return Response::created('Supplier', new SupplierResource($supplier));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::find($id);

        if ($supplier) {

            $supplier = new SupplierResource(Supplier::with('urgencyType')->with('references')->find($id));

            return Response::single_fetch('Supplier', $supplier);
        }

        return Response::not_found();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::find($id);

        if ($supplier) {

            $supplier->update([
                'code' =>  $request->code,
                'name' => $request->name,
                'terms' => $request->terms,
                'urgency_type_id' => $request->urgency_type_id,
            ]);

            $supplier->references()->sync($request->references);
            
            return Response::updated('Supplier', new SupplierResource($supplier));
        }

        return Response::not_found();
    }

    public function change_status($id) {
        return GenericController::change_status('supplier', Supplier::class, $id);
    }
}
