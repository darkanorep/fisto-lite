<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use App\Http\Requests\DepartmentRequest;

class DepartmentController extends Controller
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

        $query = Department::withTrashed()
            ->with('company')
            ->where(function ($query) use ($search) {
                $query->where('code', 'like', "%$search%")
                    ->orWhere('department', 'like', "%$search%")
                    ->orWhereHas('company', function ($query) use ($search) {
                        $query->where('company', 'like', "%$search%");
                    });
            });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $department = $query->latest('updated_at')->paginate($rows);
        } else {
            $department = $query->get(['id', 'code', 'department']);
        }

        return count($department) ?  Response::fetch('Department', $department) : Response::not_found();
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
        return GenericController::change_status('Department', Department::class, $id);
    }

    public function import(DepartmentRequest $request)
    {
        $departments = [];
    
        foreach ($request->all() as $department) {
            $company = Company::where('company', $department['company'])->first();
            $deleted_at = $department['status'] == 'Active' ? null : now();
    
            if ($company) {
                $departments[] = [
                    'code' => $department['code'],
                    'department' => $department['department'],
                    'company_id' => $company->id,
                    'deleted_at' => $deleted_at
                ];
            }
        }

        Department::insert($departments, ['code'], ['department', 'company_id']);
        
        return Response::success('Departments successfully import.', $departments);
    }
    
}
