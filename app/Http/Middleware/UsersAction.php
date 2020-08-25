<?php

namespace App\Http\Middleware;

use DB;
use Auth;
use Session;
use Closure;

class UsersAction
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
        //dd(Auth::user());
        $req = json_encode($request->all());
        DB::table("actions")->insert(["user" => $username, "route" => url()->current(), "request" => $req]);
        return $next($request);
    }
}
