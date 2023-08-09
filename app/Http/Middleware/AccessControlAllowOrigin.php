<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AccessControlAllowOrigin
{
    public function handle($request, Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Content-Type,Access-Token");
        header("Access-Control-Expose-Headers: *");
        // header("Content-Type:multipart/form-data");
 // headers: {
 //        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
 //    }
        if($request->getMethod() !== 'OPTIONS') {
            header("Access-Control-Expose-Headers: *");
        }
        return $next($request);
    }
}
