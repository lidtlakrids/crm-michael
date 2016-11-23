<?php

namespace App\Http\Middleware;

use Closure;

class Acl
{
    protected $acl;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param $controller
     * @param $method
     * @return mixed
     */
    public function handle($request, Closure $next,$controller,$method)
    {
        if(isAllowed($controller,$method)){
            return $next($request);
        }else{
            return view('errors.denied');
        }
    }
}
