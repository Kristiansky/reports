<?php
    
    namespace App\Http\Controllers\Auth;
    use App\Client;
    use App\Http\Controllers\Controller;
    use App\User;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Session;

    class Authentication extends Controller{
        
        public static function attempt($credentials){
            $user = User::where('email', '=', $credentials['email'])->where('password', '=', md5($credentials['password']))->first();
            if($user != null){
                Auth::login($user, true);
                Session::put('per_page', 25);
                if($user->group->id != 1 && $user->group->id != 2 && !empty($user->group->client)){
                    Session::put('client',
                        Client::findOrFail($user->group->client->Id)
                    );
                }
                return true;
            }else{
                return false;
            }
            
        }
        
    }
