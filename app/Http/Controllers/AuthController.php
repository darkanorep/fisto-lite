<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AddUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Response\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function index(Request $request)
    {

        $rows = $request->input('rows', 5);
        $paginate = $request->input('paginate', 1);
        $search = $request->search;

        $users = User::where(function ($query) use ($search) {
            $query->where('first_name', 'like', '%' . $search . '%')
                ->orWhere('last_name', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->orWhere('role', 'like', '%' . $search . '%');
        })->when($paginate, function ($query) use ($rows) {
            return $query->paginate($rows);
        }, function ($query) {
            return $query->get(['username', 'role']);
        });

        return count($users) ? Response::fetch('User', $users) : Response::not_found();
    }

    public function register(AddUserRequest $request)
    {

        return Response::created('User', new UserResource(User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ])));
    }

    public function login(Request $request)
    {
        if (Auth::attempt($request->only('username', 'password'))) {

            $data = [
                'username' => Auth::user()->username,
                'token' => Auth::user()->createToken('authToken of ' . Auth::user()->username)->plainTextToken
            ];

            return Response::success('Login Success.', $data);
        } else {
            return Response::forbidden('Invalid Credentials.');
        }
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return  Response::success('Logout Success.', Auth::user()->username);
    }
}
