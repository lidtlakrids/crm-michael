<?php

namespace App\Http\Middleware;

use Closure;

class Whitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
        if(!isWhitelisted()){
            return redirect('http://gcmcrmaccess.azurewebsites.net/');
        }else{
            return $next($request);
        }
    }
}
