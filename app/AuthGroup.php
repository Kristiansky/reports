<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuthGroup extends Model
{
    protected $table = 'auth_groups';
    
    protected $guarded = [];
    
    protected $primaryKey = 'id';
    
    public function users()
    {
        return $this->hasMany('App\User', 'group_id', 'id');
    }
    
    public function product_categories()
    {
        return $this->hasMany('App\ProductCategory', 'idg', 'id');
    }
    
    public function client()
    {
        return $this->hasOne('App\Client', 'Id', 'client_id');
    }

}
