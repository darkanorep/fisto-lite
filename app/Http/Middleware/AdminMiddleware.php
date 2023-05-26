<?php

namespace App\Http\Middleware;

use App\Http\Response\Response as ResponseResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Response\Response as Status;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        
        if (Auth::user()->role == 'Admin') {
            return $next($request);
        } else {
            return Status::unauthorized('Unauthorized Access');
        }
        
    }
}
