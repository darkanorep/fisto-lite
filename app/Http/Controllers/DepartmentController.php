<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Http\Response\Response;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Department::with('company')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request)
    {
        $department = Department::create([
            'code' => $request->code,
            'department' => $request->department,
            'company_id' => $request->company_id
        ]);

        return Response::created('Department', $department);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $department = Department::find($id);

        if ($department) {

            $department = Department::with('company')->find($id);

            return Response::single_fetch('Department', $department);
        }

        return Response::not_found();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, $id)
    {
        $department = Department::find($id);

        if ($department) {
            $department->update([
                'code' => $request->code,
                'department' => $request->department,
                'company_id' => $request->company_id
            ]);

            $department->save();

            return Response::updated('Department', $department);
        } 

        return Response::not_found();
    }

    public function change_status($id)
    {
        return GenericController::change_status(Department::class, $id);
    }
}
