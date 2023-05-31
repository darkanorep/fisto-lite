<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Response\Response;

class GenericController extends Controller
{
    public static function change_status($object, $model, $id)
    {
        $data = $model::withTrashed()->find($id);

        if ($data) {
            if ($data->trashed()) {
                $data->restore();

                return Response::restored($object, $data);
            } else {
                $data->delete();
                
                return Response::archived($object, $data);
            }

        } else {

            return Response::not_found();
        }
    }
}
