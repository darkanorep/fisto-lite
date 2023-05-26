<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Response\Response;

class GenericController extends Controller
{
    public static function change_status($model, $id)
    {
        $data = $model::withTrashed()->find($id);

        if ($data) {
            if ($data->trashed()) {
                $data->restore();

                return Response::success('Document successfully restored', $data);
            } else {
                $data->delete();
                
                return Response::success('Document successfully archived', $data);
            }

        } else {

            return Response::not_found();
        }
    }
}
