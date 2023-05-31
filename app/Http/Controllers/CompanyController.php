<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Response\Response;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // return Response::fetch('Company', Company::with('associates')->get());
        $status = $request->input('status', true);
        $paginate = $request->input('paginate', true);
        $rows = $request->input('rows', 10);
        $search = $request->input('search', '');

        $query = Company::withTrashed()
            ->with('associates')
            ->where(function ($query) use ($search) {
                $query->where('code', 'like', "%$search%")
                    ->orWhere('company', 'like', "%$search%")
                    ->orWhereHas('associates', function ($query) use ($search) {
                        $query->where('username', 'like', "%$search%");
                    });
            });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $company = $query->latest('updated_at')->paginate($rows);
        } else {
            $company = $query->get(['id', 'code', 'company']);
        }

        return count($company) ?  Response::fetch('Company', $company) : Response::not_found();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyRequest $request)
    {
        $newCompany = Company::create([
            'code' => $request->code,
            'company' => $request->company,
        ]);

        $newCompany->associates()->attach($request->associates);

        return Response::created('Company', Company::find($newCompany->id));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $company = Company::find($id);

        if ($company) {
            
            return Response::single_fetch('Company', Company::with('associates')->find($id));
        }

        return Response::not_found();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyRequest $request, $id)
    {
        $company = Company::find($id);

        if ($company) {
            $company->update([
                'code' => $request->code,
                'company' => $request->company,
            ]);

            $company->associates()->sync($request->associates);
            $company->save();

            return Response::updated('Company', $company);
        } else {

            return Response::not_found();
        }
    }

    public function change_status($id)
    {
        return GenericController::change_status('Company', Company::class, $id);
    }
}
