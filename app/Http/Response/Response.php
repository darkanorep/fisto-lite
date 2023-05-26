<?php

namespace App\Http\Response;
use Illuminate\Support\Str;
use App\Http\Response\Status;

    class Response {

        public static function success($message, $data) {
            return response()->json([
                'code' => Status::OK,
                'message' => $message,
                'result' => $data
            ], Status::OK);
        }

        public static function updated($model, $data) {
            return response()->json([
                'code' => Status::OK,
                'message' => $model.' successfully updated.',
                'result' => $data
            ]);
        }

        public static function fetch($model, $data) {
            return response()->json([
                'code' => Status::OK,
                'message' => Str::plural($model).' successfully fetched.',
                'result' => $data
            ]);
        }

        public static function single_fetch($model, $data) {
            return response()->json([
                'code' => Status::OK,
                'message' => Str::singular($model).' successfully fetched.',
                'result' => $data
            ]);
        }

        public static function created($model, $data) {
            return response()->json([
                'code' => Status::CREATED,
                'message' => 'New '. $model.' created.',
                'result' => $data
            ], Status::CREATED);
        }

        public static function unauthorized($message) {
            return response()->json([
                'code' => Status::UNAUTHORIZED,
                'message' => $message
            ], Status::UNAUTHORIZED);
        }

        public static function forbidden($message) {
            return response()->json([
                'code' => Status::FORBIDDEN,
                'message' => $message
            ],  Status::FORBIDDEN);
        }

        public static function not_found() {
            return response()->json([
                'code' => Status::NOT_FOUND,
                'message' => 'No records found.'
            ],  Status::NOT_FOUND);
        }

        public static function conflict($message, $data) {
            return response()->json([
                'code' => Status::CONFLICT,
                'message' => $message,
                'result' => $data
            ], Status::CONFLICT);
        }
    }
