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
    public function index(Request $request)
    {
        $status = $request->input('status', true);
        $paginate = $request->input('paginate', true);
        $rows = $request->input('rows', 10);
        $search = $request->input('search', '');

        $query = Location::withTrashed()
            ->with('departments')
            ->where(function ($query) use ($search) {
                $query->where('code', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%")
                    ->orWhereHas('departments', function ($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                        ->orWhere('department', 'like', "%$search%");
                    });
            });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $location = $query->latest('updated_at')->paginate($rows);
        } else {
            $location = $query->get(['id', 'code', 'location']);
        }

        return count($location) ?  Response::fetch('Location', $location) : Response::not_found();
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
    public function show($id)
    {
        $location = Location::find($id);

        if ($location) {

            return Response::single_fetch('Location', Location::with('departments')->find($id));
        }

        return Response::not_found();
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
        return GenericController::change_status('Location', Location::class, $id);
    }

}
