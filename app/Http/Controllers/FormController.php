<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use Illuminate\Validation\Rule;
use App\Http\Requests\AddFormRequest;
use App\Http\Requests\FormImportRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\GenericController;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rows = $request->input('rows', 5);
        $search = $request->input('search');
        $paginate =  $request->input('paginate', true);

        $forms = Form::where(function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%')
            ->orWhere('form_type', 'like', '%' . $search . '%');
        })
        ->when($paginate, function ($query) use ($rows) {
            return $query->paginate($rows);
        }, function ($query) {
            return $query->get(['id', 'name']);
        });

        return count($forms) ?  Response::success('Data fetched.', $forms) : Response::not_found();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddFormRequest $request)
    {
        return Response::created('Form created.', Form::create([
            'form_type' => $request->form_type,
            'name' => $request->name
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Change status the specified resource from storage.
     */
    public function change_status($id)
    {
        return GenericController::change_status(Document::class, $id);
    }


    public function import(FormImportRequest $request)
    {
        $date = date('Y-m-d H:i:s');
        $forms = [];
    
        foreach ($request->all() as $data) {
            $forms[] = [
                'form_type' => $data['form_type'],
                'name' => $data['name'],
                'created_at' => $date,
                'updated_at' => $date
            ];
        }
    
        Form::upsert($forms, ['name'], ['name', 'form_type']);
        return Response::success('Data imported successfully.', $forms);
    }

}

