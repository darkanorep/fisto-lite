<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationRequest;
use App\Http\Response\Response;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Location::with('departments')->get();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(LocationRequest $request)
    {
        $location = Location::create([
            'code' => $request->code,
            'location' => $request->location
        ]);

        $location->departments()->attach($request->departments);

        return Response::created('Location', Location::find($location->id));
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(LocationRequest $request, $id)
    {
        $location = Location::find($id);

        if ($location) {
            $location->update([
                'code' => $request->code,
                'location' => $request->location
            ]);

            $location->departments()->sync($request->departments);
            $location->save();

            return Response::updated('Location', Location::find($location->id));
        }

        return Response::not_found();
    }

    public function change_status($id) {
        return GenericController::change_status(Location::class, $id);
    }

}
