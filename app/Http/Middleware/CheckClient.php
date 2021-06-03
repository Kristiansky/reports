<?php

namespace App\Http\Middleware;

use App\Client;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckClient
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
        if(!session('per_page')){
            session()->put('per_page', 25);
        }
        if(session('client') == null){
            $user = Auth::user();
            if($user->group->id != 1 && $user->group->id != 2 && !empty($user->group->client)){
                Session::put('client',
                    Client::findOrFail($user->group->client->Id)
                );
                return $next($request);
            }else{
                return redirect()->route('change_client_view');
            }
        }else{
            return $next($request);
        }
    }
}
