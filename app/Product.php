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
        $entries_sub = Entry::where('idp', '=', $this->idp)
            ->select('idp', DB::raw('SUM(bucati) AS suma'))
            ->groupBy('idp')
        ;
        
        $entries_sum = DB::table( DB::raw("({$entries_sub->toSql()}) as entries") )
            ->mergeBindings($entries_sub->getQuery())
            ->select(
                DB::raw('SUM(suma) as total_entries'),
                'idp'
            )
            ->groupBy('idp')
            ->first();
        
        $orders_sub = Order::where('idp', '=', $this->idp)
            ->select('idp', DB::raw('SUM(volum) AS suma'))
            ->where('status', '=', 'expediat')
            ->groupBy('idp')
        ;
        
        $orders_sum = DB::table( DB::raw("({$orders_sub->toSql()}) as orders") )
            ->mergeBindings($orders_sub->getQuery())
            ->select(
                DB::raw('SUM(suma) as total_orders'),
                'idp'
            )
            ->groupBy('idp')
            ->first();
        
        $return = 0;
        if($entries_sum != null){
            $return = $entries_sum->total_entries;
        }
        if($orders_sum != null){
            $return = $return - $orders_sum->total_orders;
        }
        return $return;
    }
    /**
     * Returns the reserved stock (shipped + unshipped orders)
     * @return int
     */
    public function reserved_stock(){
        $entries_sub = Entry::where('idp', '=', $this->idp)
            ->select('idp', DB::raw('SUM(bucati) AS suma'))
            ->groupBy('idp')
        ;
        
        $entries_sum = DB::table( DB::raw("({$entries_sub->toSql()}) as entries") )
            ->mergeBindings($entries_sub->getQuery())
            ->select(
                DB::raw('SUM(suma) as total_entries'),
                'idp'
            )
            ->groupBy('idp')
            ->first();
        
        $orders_sub = Order::where('idp', '=', $this->idp)
            ->select('idp', DB::raw('SUM(volum) AS suma'))
            ->groupBy('idp')
        ;
        
        $orders_sum = DB::table( DB::raw("({$orders_sub->toSql()}) as orders") )
            ->mergeBindings($orders_sub->getQuery())
            ->select(
                DB::raw('SUM(suma) as total_orders'),
                'idp'
            )
            ->groupBy('idp')
            ->first();
        
        $return = 0;
        if($entries_sum != null){
            $return = $entries_sum->total_entries;
        }
        if($orders_sum != null){
            $return = $return - $orders_sum->total_orders;
        }
        return $return;
    }
    
    /**
     * Returns the stock including new (unshipped) orders
     * @return int
     */
    public function stockInclNew(){
        $entries_sub = Entry::where('idp', '=', $this->idp)
            ->select('idp', DB::raw('SUM(bucati) AS suma'))
            ->groupBy('idp')
        ;
    
        $entries_sum = DB::table( DB::raw("({$entries_sub->toSql()}) as entries") )
            ->mergeBindings($entries_sub->getQuery())
            ->select(
                DB::raw('SUM(suma) as total_entries'),
                'idp'
            )
            ->groupBy('idp')
            ->first();
    
        $orders_sub = Order::where('idp', '=', $this->idp)
            ->select('idp', DB::raw('SUM(volum) AS suma'))
            ->groupBy('idp')
        ;
    
        $orders_sum = DB::table( DB::raw("({$orders_sub->toSql()}) as orders") )
            ->mergeBindings($orders_sub->getQuery())
            ->select(
                DB::raw('SUM(suma) as total_orders'),
                'idp'
            )
            ->groupBy('idp')
            ->first();
    
        $return = 0;
        if($entries_sum != null){
            $return = $entries_sum->total_entries;
        }
        if($orders_sum != null){
            $return = $return - $orders_sum->total_orders;
        }
        return $return;
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
    
    public function stacks(){
        if ($this->pieces_in_package != NULL){
            $current_total = $this->stock();
            return (int)$current_total / (int)$this->pieces_in_package;
        }else{
            return false;
        }
    }
    
}
