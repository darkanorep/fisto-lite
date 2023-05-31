<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Response\Response;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
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

        $query = Category::withTrashed()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });

        if ($paginate) {
            $query->when($status, function ($query) {
                $query->whereNull('deleted_at');
            }, function ($query) {
                $query->whereNotNull('deleted_at');
            });

            $categories = $query->latest('updated_at')->paginate($rows);
        } else {
            $categories = $query->get(['id', 'name']);
        }

        return $categories->isNotEmpty() ? Response::fetch('Category', $categories) : Response::not_found();
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create([
            'name' => $request->name,
        ]);

        $category = Category::find($category->id);

        return Response::created('Category', $category);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);

        return $category ? Response::single_fetch('Category', $category) : Response::not_found();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if ($category) {
            $category->update([
                'name' => $request->name,
            ]);

            $category->save();

            return Response::updated('Category', $category);
        } else {
            return Response::not_found();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function change_status($id)
    {
        return GenericController::change_status('Category', Category::class, $id);
    }

}
