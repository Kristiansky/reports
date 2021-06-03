<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'stor_categorii';
    
    protected $guarded = [];
    
    protected $primaryKey = 'idc';
    
    public function group()
    {
        return $this->hasOne('App\AuthGroup', 'id', 'idg');
    }
    
    public function products()
    {
        return $this->hasMany('App\Product', 'idc', 'idc');
    }
}
