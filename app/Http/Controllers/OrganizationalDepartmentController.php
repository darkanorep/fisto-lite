<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationalDepartmentRequest;
use App\Models\OrganizationalDepartment;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use PhpParser\Node\Stmt\Return_;

class OrganizationalDepartmentController extends Controller
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

        $query = OrganizationalDepartment::withTrashed()
            ->where(function ($query) use ($search) {
                $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
            });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $org = $query->latest('updated_at')->paginate($rows);
        } else {
            $org = $query->get(['id', 'code', 'name']);
        }

        return count($org) ? Response::fetch('Organizational Department', $org) : Response::not_found();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrganizationalDepartmentRequest $request)
    {
        return Response::created('Organizational Department',  OrganizationalDepartment::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $org = OrganizationalDepartment::withTrashed()->find($id);

        return $org ? Response::single_fetch('Organizational Department', $org) : Response::not_found();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrganizationalDepartmentRequest $request, $id)
    {
        $org = OrganizationalDepartment::find($id);

        if ($org) {
            $org->update($request->all());
        } else {
            return Response::not_found();
        }

        return Response::success('Organizational Department successfully updated.', $org);
    }

    public function import(OrganizationalDepartmentRequest $request)
    {

        $organization = [];

        foreach ($request->all() as $org) {
            $deleted_at = $org['status'] == 'inactive' ? now() : null;

            $organization[] = [
                'code' => $org['code'],
                'name' => $org['name'],
                'deleted_at' => $deleted_at
            ];
        }

        OrganizationalDepartment::upsert($organization, ['code'], ['code'], ['name', 'deleted_at']);

        return Response::success('Organizational Department successfully sync.', $organization);
    }
}
