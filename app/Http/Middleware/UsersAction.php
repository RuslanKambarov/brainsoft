<?php

namespace App\Http\Middleware;

use DB;
use Auth;
use Closure;

class usersAction
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
        $username = Auth::user()->name ?? "Guest";
        $req = json_encode($request->all());
        DB::table("actions")->insert(["user" => $username, "route" => url()->current(), "request" => $req]);
        return $next($request);
    }
}
