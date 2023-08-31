<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, Closure $next, ...$guards)
    {
      /*  if ($jwt = $request->cookie('jwt')) {
            $request->headers->set('Authorization', 'Bearer ' . $jwt);
        }*/

        $header = $request->header('token');
        if ($header == "") {
            return response()->json(["message" => "you can't send the token empty"]);
        }
        $request->headers->set('Authorization', 'Bearer ' . $header);
        $this->authenticate($request, $guards);

        return $next($request);
    }
}
