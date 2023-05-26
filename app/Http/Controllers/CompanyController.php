<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response('index to');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response('store to');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return response('show to');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        return response('update to');
    }

}
