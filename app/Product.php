<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table = 'stor_produse';
    
    protected $guarded = [];
    
    protected $primaryKey = 'idp';
    
    public function category()
    {
        return $this->belongsTo('App\ProductCategory', 'idc', 'idc');
    }
    
    public function entries()
    {
        return $this->hasMany('App\Entry', 'idp', 'idp')->orderBy('idin', 'DESC');
    }
    
    public function orders()
    {
        return $this->hasMany('App\Order', 'idp', 'idp')->orderBy('idie', 'DESC');
    }
    
    /**
     * Returns the stock (shipped orders only)
     * @return int
     */
    public function stock(){
        $entries = 0;
        if(count($this->entries) > 0){
            foreach ($this->entries as $entry){
                $entries = $entries + $entry->bucati;
            }
        }
        $orders = 0;
        if(count($this->orders) > 0){
            foreach ($this->orders as $order){
                if($order->getRawOriginal('status') == 'expediat'){
                    $orders = $orders + $order->volum;
                }
            }
        }
        return $entries - $orders;
    }
    
    /**
     * Returns the stock including new (unshipped) orders
     * @return int
     */
    public function stockInclNew(){
        $entries = 0;
        if(count($this->entries) > 0){
            foreach ($this->entries as $entry){
                $entries = $entries + $entry->bucati;
            }
        }
        $orders = 0;
        if(count($this->orders) > 0){
            foreach ($this->orders as $order){
                $orders = $orders + $order->volum;
            }
        }
        return $entries - $orders;
    }
    
    public function lots(){
        $current_total = $this->stock();
        if($current_total > 0){
            $expiration_lots = DB::table('stor_receptii_detalii')
                ->select('*', DB::raw('SUM(`cantitate`) as `qty_sum`'))
                ->where('stor_receptii_detalii.idp', '=', $this->idp)
                ->where('dataexp', '!=', '0000-00-00')
                ->groupBy('stor_receptii_detalii.idp', 'stor_receptii_detalii.lotul')
                ->orderBy('stor_receptii_detalii.dataexp', 'desc')
                ->orderBy('stor_receptii_detalii.id', 'desc')
                ->leftJoin('stor_receptii', 'stor_receptii_detalii.idreceptie', '=', 'stor_receptii.id')
                ->get();
            if(!$expiration_lots->isEmpty()){
                $return = array();
                foreach($expiration_lots as $k=>$expiration_lot){
                    if($current_total < $expiration_lot->qty_sum){
                        if($current_total > 0) {
                            $tmp_arr = array(
                                'number_of_items' => $current_total,
                                'lotul' => $expiration_lot->lotul,
                                'dataexp' => $expiration_lot->dataexp,
                                'nraviz' => $expiration_lot->nraviz,
                                'numereceptie' => $expiration_lot->numereceptie,
                            );
                            $return[] = $tmp_arr;
                        }
                        break;
                    }else{
                        $tmp_arr = array(
                            'number_of_items' => $current_total,
                            'lotul' => $expiration_lot->lotul,
                            'dataexp' => $expiration_lot->dataexp,
                            'nraviz' => $expiration_lot->nraviz,
                            'numereceptie' => $expiration_lot->numereceptie,
                        );
                        $return[] = $tmp_arr;
                        $current_total = intval($expiration_lot->qty_sum - $current_total);
                    }
                }
                return $return;
            }else{
                return false;
            }
        }
        return false;
    }
    
    public function damaged(){
        $result = DB::table('stor_produse_damaged')
            ->select('idp', DB::raw('SUM(`volum`) as `total`'))
            ->where('idp', '=', $this->idp)
            ->groupBy('idp', 'idp')
            ->first();
        if($result){
            return $result;
        }else{
            return false;
        }
    }
    
}
