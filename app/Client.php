<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $primaryKey = 'Id';
    
    protected $table = 'clienti';
    
    protected $guarded = [];
    
    public function group()
    {
        return $this->hasOne('App\AuthGroup', 'client_id', 'Id');
    }
    
}
