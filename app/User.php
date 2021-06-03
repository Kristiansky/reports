<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'auth_users';
    
    protected $guarded = ['password'];
    
    protected $hidden = ['password'];
    
    public function group()
    {
        return $this->hasOne('App\AuthGroup', 'id', 'group_id');
    }
}
