<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Http\Response\Response;
use App\Http\Controllers\GenericController;

class DocumentController extends Controller
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

        // $documents = Document::where(function ($query) use ($status) {
        //     //checking for status if active or not
        //     return $query->when($status, function ($query) {
        //         $query->whereNull('deleted_at');
        //     }, function ($query) {
        //         $query->whereNotNull('deleted_at');
        //     });
        //     //use for searching
        // })->where(function ($query) use ($search) {
        //     $query->where('type', 'like', "%$search%")
        //         ->orWhere('description', 'like', "%$search%");
        // })
        //     //use for paginate
        //     ->withTrashed()->when($paginate, function ($query) use ($rows) {
        //         return $query->latest('updated_at')->paginate($rows);
        //     }, function ($query) {
        //         return $query->get(['id', 'type', 'description']);
        //     });

        $query = Document::withTrashed()
        ->with('categories')
        ->where(function ($query) use ($search) {
            $query->where('type', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhereHas('categories', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
        });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $documents = $query->latest('updated_at')->paginate($rows);
        } else {
            $documents = $query->get(['id', 'type', 'description']);
        }

        return count($documents) ? Response::fetch('Document', $documents) : Response::not_found();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentRequest $request)
    {
        $document = Document::create([
            'type' => $request->type,
            'description' => $request->description,
        ]);

        $document->categories()->attach($request->categories);
        $document = Document::find($document->id);

        return Response::created('Document', $document);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $document = Document::find($id);

        if ($document) {
            return Response::single_fetch('Document', Document::with('categories')->find($id));
        }

        return Response::not_found();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentRequest $request, $id)
    {
        $document = Document::find($id);

        if ($document) {
            $document->update([
                'type' => $request->type,
                'description' => $request->description,
            ]);
            $document->categories()->sync($request->categories);
            $document->save();

            return Response::updated('Document', $document);

        } else {
            return Response::not_found();
        }
    }

    /**
     * Change status the specified resource from storage.
     */
    public function change_status($id)
    {
        return GenericController::change_status(Document::class, $id);
    }
}
