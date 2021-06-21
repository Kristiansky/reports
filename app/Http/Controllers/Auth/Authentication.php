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
            $find1 = strpos($credentials['username'], '@');
            $find2 = strpos($credentials['username'], '.');
            if ($find1 !== false && $find2 !== false && $find2 > $find1){
                $user = User::where('email', '=', $credentials['username'])->where('password', '=', md5($credentials['password']))->first();
            }else{
                $user = User::where('username', '=', $credentials['username'])->where('password', '=', md5($credentials['password']))->first();
            }
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
