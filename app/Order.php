<?php

namespace App;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $table = 'stor_iesiri';
    
    protected $guarded = [];
    
    protected $primaryKey = 'idie';
    
    public function getStatusAttribute($value)
    {
        if($value == 'Comanda'){
            return '<span style="color:blue">Order</span>';
        }elseif($value == 'InProcesare'){
            return '<span style="color:orange">InProcess</span>';
        }elseif($value == 'Blocata'){
            return '<span style="color:purple">Blocked</span>';
        }else{
            $color = 'green';
            if($this->ceretur != '0000-00-00'){
                $return_date = new Carbon($this->ceretur);
                $current_date = new Carbon();
                if($return_date->diff($current_date)->days > 25){
                    $color = 'red';
                }
            }
            return '<span style="color:'.$color.'">Sent' . ($this->ceretur != '0000-00-00' ? ' - <strong>Return</strong> on ' . date('d M Y', strtotime($this->ceretur)) : '') . '</span>';
        }
    }
    
    protected $appends = array('returned', 'products');
    
    public function getReturnedAttribute()
    {
        $result = DB::table('stor_intrari')->where('aviz', '=', $this->idie)->first();
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    public function getProductsAttribute()
    {
        $result = DB::table($this->table)
            ->select('stor_produse.idp','stor_produse.descriere','stor_produse.codprodusclient','stor_produse.codbare','stor_iesiri.volum')
            ->join('stor_produse','stor_iesiri.idp','=','stor_produse.idp')
            ->where('idcomanda', '=', $this->idie)->get();
        if($result){
            $return = array();
            foreach($result as $key=>$item){
                $return[$key]['descriere'] = $item->descriere;
                $return[$key]['volum'] = $item->volum;
                $return[$key]['idp'] = $item->idp;
                $return[$key]['codprodusclient'] = $item->codprodusclient;
                $return[$key]['codbare'] = $item->codbare;
                $returned_result = DB::table('stor_intrari')->where('aviz', '=', $this->idie)->first();
                if($returned_result){
                    $return[$key]['is_returned'] = true;
                }else{
                    $return[$key]['is_returned'] = false;
                }
            }
            return $return;
        }else{
            return false;
        }
    }
}
