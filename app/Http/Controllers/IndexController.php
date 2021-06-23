<?php
    
    namespace App\Http\Controllers;
    
    use App\Client;
    use App\Order;
    use App\User;
    use DateInterval;
    use DatePeriod;
    use DateTime;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Session;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
    class IndexController extends Controller
    {
        public function index()
        {
            $data = [];
            
            if(request('filter') && request('filter') == '1'){
                $dashboard_filter = array(
                    'range_start' => request('range_start'),
                    'range_end' => request('range_end'),
                );
                session()->put('dashboard_filter', $dashboard_filter);
                return redirect(route('home'));
            }
            
            if(!session('dashboard_filter')){
                $range_start = date('Y-m-d', strtotime('-30 days'));
                $range_end = date('Y-m-d', strtotime('now'));
                $dashboard_filter = array(
                    'range_start' => $range_start,
                    'range_end' => $range_end
                );
                session()->put('dashboard_filter', $dashboard_filter);
            }
            
            $client = session('client');
    
            $begin = new DateTime(session('dashboard_filter')['range_start']);
            $end = new DateTime(session('dashboard_filter')['range_end']);
            $end->setTime(0,0,1);
    
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);
    
            $days=array();
            foreach ($period as $dt) {
                $days[$dt->format("Y-m-d")] = array(
                    'sent_count' => 0,
                    'returned_count' => 0,
                    'sent_product_count' => 0,
                    'returned_product_count' => 0,
                );
            }
            
            $ido = User::where('group_id','=',$client->group->id)->where('name','=',$client->group->name)->firstOrFail()->id;
            
            $orders_sent_count = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(data_procesare_comanda)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(data_procesare_comanda)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->orderBy('idie', 'desc')
                ->select(
                    'idcomanda',
                    DB::raw('DATE(data_procesare_comanda) as date_processed')
                )
                ->groupBy('idcomanda')
                ->where('status', '=', 'expediat')
                ->get()
                ->count();
            
            $data['orders_sent_count'] = $orders_sent_count;
            
            $orders_sent = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(data_procesare_comanda)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(data_procesare_comanda)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    DB::raw('DATE(data_procesare_comanda) as date_processed'),
                    'idcomanda'
                )
                ->groupBy('idcomanda', 'date_processed')
                ->where('status', '=', 'expediat')
                ->get();
            
            foreach ($orders_sent as $order){
                $days[$order->date_processed]['sent_count'] = (int)$days[$order->date_processed]['sent_count'] + 1;
            }
            
            $orders_returned_count = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(ceretur)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(ceretur)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    'idcomanda'
                )
                ->groupBy('idcomanda')
                ->where('ceretur', '!=', '0000-00-00')
                ->get()
                ->count();
            
            $data['orders_returned_count'] = $orders_returned_count;
            
            $orders_returned = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(ceretur)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(ceretur)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    'ceretur',
                    'idcomanda'
                )
                ->where('ceretur', '!=', '0000-00-00')
                ->groupBy('idcomanda', 'ceretur')
                ->get();
            
            foreach ($orders_returned as $order){
                $days[$order->ceretur]['returned_count'] = (int)$days[$order->ceretur]['returned_count'] + 1;
            }
            
            $sent_products = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(data_procesare_comanda)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(data_procesare_comanda)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    DB::raw('DATE(data_procesare_comanda) as date_processed'),
                    'stor_iesiri.idp'
                )
                ->leftJoin('stor_produse', 'stor_iesiri.idp', '=', 'stor_produse.idp')
                ->groupBy('stor_iesiri.idp', 'date_processed')
                ->where('status', '=', 'expediat')
                ->get();
            ;
    
            foreach ($sent_products as $order){
                $days[$order->date_processed]['sent_product_count'] = (int)$days[$order->date_processed]['sent_product_count'] + 1;
            }
            
            $sent_products = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(ceretur)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(ceretur)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    'ceretur',
                    'stor_iesiri.idp'
                )
                ->leftJoin('stor_produse', 'stor_iesiri.idp', '=', 'stor_produse.idp')
                ->where('ceretur', '!=', '0000-00-00')
                ->groupBy('stor_produse.idp', 'ceretur')
                ->get();
            ;
    
            foreach ($sent_products as $order){
                $days[$order->ceretur]['returned_product_count'] = (int)$days[$order->ceretur]['returned_product_count'] + 1;
            }
            
            $data['dates'] = $days;
            
            $sub = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(data_procesare_comanda)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(data_procesare_comanda)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
//                    DB::raw('COUNT(idcomanda) as sent_orders'),
                    'idcomanda',
                    'localitate',
                    'tara',
                    'curier'
                )
                ->groupBy('idcomanda')
            ;
            
            $city_data = DB::table(DB::raw("({$sub->toSql()}) as sub"))
                ->mergeBindings($sub->getQuery())
                ->select(
                    DB::raw('COUNT(idcomanda) as total_sent_orders'),
                    'localitate'
                )
                ->groupBy('localitate')
                ->orderBy('total_sent_orders', 'DESC')
                ->take(10)
                ->get()
            ;
            $data['city_data'] = $city_data;
            
            $top_products = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(data_procesare_comanda)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(data_procesare_comanda)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    DB::raw('COUNT(stor_iesiri.idp) as sold_products'),
                    'stor_iesiri.idp',
                    'stor_produse.descriere as product_name'
                )
                ->leftJoin('stor_produse', 'stor_iesiri.idp', '=', 'stor_produse.idp')
                ->groupBy('idp')
                ->orderBy('sold_products', 'DESC')
                ->take(10)
                ->get()
            ;
            $data['top_products'] = $top_products;
    
            $countries = DB::table(DB::raw("({$sub->toSql()}) as sub"))
                ->mergeBindings($sub->getQuery())
                ->select(
                    DB::raw('COUNT(tara) as orders_count'),
                    DB::raw('tara as country')
                )
                ->groupBy('country')
                ->orderBy('orders_count', 'DESC')
                ->get()
            ;
            
            if($countries && $countries->count() > 1){
                $data['countries'] = $countries;
            }
    
            $couriers = DB::table(DB::raw("({$sub->toSql()}) as sub"))
                ->mergeBindings($sub->getQuery())
                ->select(
                    DB::raw('COUNT(curier) as orders_count'),
                    DB::raw('curier as courier')
                )
                ->groupBy('curier')
                ->orderBy('orders_count', 'DESC')
                ->get()
            ;
            
            if($couriers && $couriers->count() > 1){
                $data['couriers'] = $couriers;
            }
            
            return view('dashboard', $data);
        }
        
        public function changeClientView(){
            $data['clients'] = DB::select('
                SELECT
                    clienti.id as idclient,clienti.*
                FROM
                    `clienti`
                JOIN `auth_groups` ON `clienti`.`id`=`auth_groups`.`client_id`
                JOIN `stor_categorii` ON `auth_groups`.`id`=`stor_categorii`.`idg`
                
                GROUP BY `clienti`.`id`
                ORDER BY `cod_client`
            ');
            
            return view('change_client', $data);
        }
        
        public function changeClientUpdate(Request $request){
            if($request->client != 0){
                Session::put('client',
                    Client::findOrFail($request->client)
                );
            }
            return redirect(route('home'));
        }
        
        public function changePerPage(Request $request){
            Session::put('per_page', $request->get('per_page'));
            return redirect()->back();
        }
        
        public function storageReport(){
            $client = session('client');
            
            if(request('filter') && request('filter') == '1'){
                $storage_report = array(
                    'month' => request('month'),
                    'year' => request('year'),
                );
                session()->put('storage_report_filter', $storage_report);
                return redirect(route('storage_report'));
            }elseif (request('reset') && request('reset') == '1'){
                session()->forget('storage_report_filter');
                return redirect(route('storage_report'));
            }
            $data = null;
            $days_data = array();
            $totals['shelves'] = 0;
            $totals['shelf_price'] = 0;
            $totals['pallets'] = 0;
            $totals['pallet_price'] = 0;
            $prices = DB::table('stor_ecommerce_facturari')
                ->select('tarifdepozitareraft as shelf_price', 'tarifdepozitarepalet as pallet_price')
                ->where('id','=',$client->group->id)
                ->first();
            if((session('storage_report_filter')['month'] && session('storage_report_filter')['year']) && $prices){
                $daily_price['shelf_price'] = (float)number_format(doubleval($prices->shelf_price) / 30, 2, '.', '');
                $daily_price['pallet_price'] = (float)number_format(doubleval($prices->pallet_price) / 30, 2, '.', '');
                $month = session('storage_report_filter')['month'];
                $year = session('storage_report_filter')['year'];
                
                for($d=1; $d<=31; $d++)
                {
                    $time=mktime(12, 0, 0, $month, $d, $year);
                    if (date('m', $time)==$month){
                        $date = date('Y-m-d', $time);
                        if($date < date('Y-m-d')){
                            $days_data[$date] = array(
                                'shelves' => 0,
                                'shelf_price' => 0,
                                'pallets' => 0,
                                'pallet_price' => 0,
                            );
                        }
                    }
                }
                $data['shelves'] = DB::select('
                SELECT COUNT(shelve_table.datal) as shelve_count, shelve_table.datal AS store_date
                FROM (
                    SELECT stor_locatii_ecommerce_log.x,auth_groups.name AS client,stor_locatii_ecommerce_log.data AS datal
                    FROM stor_locatii_ecommerce_log
                    JOIN stor_produse ON stor_produse.idp=stor_locatii_ecommerce_log.idp
                    JOIN stor_categorii ON stor_categorii.idc=stor_produse.idc
                    JOIN auth_groups ON stor_categorii.idg=auth_groups.id
                    WHERE
                    MONTH(stor_locatii_ecommerce_log.data)='.$month.'
                    AND YEAR(stor_locatii_ecommerce_log.data)='.$year.'
                    AND ecommerce="da"
                    AND auth_groups.id='.$client->group->id.'
                    AND x>=20
                    GROUP BY x,stor_locatii_ecommerce_log.data
                    ORDER BY stor_locatii_ecommerce_log.data ASC, x ASC, y ASC, z ASC, t ASC, u ASC
                ) as shelve_table
                GROUP BY shelve_table.datal
            ');
                foreach ($data['shelves'] as $datum){
                    $days_data[$datum->store_date]['shelves'] = $datum->shelve_count;
                    $days_data[$datum->store_date]['shelf_price'] = $daily_price['shelf_price'] * (int)$datum->shelve_count;
                }
                $data['pallets'] = DB::select('
                SELECT COUNT(pallets_table.datal) as pallets_count, pallets_table.datal AS store_date
                FROM (
                    SELECT stor_locatii_ecommerce_log.x,auth_groups.name AS client,stor_locatii_ecommerce_log.data AS datal
                    FROM stor_locatii_ecommerce_log
                    JOIN stor_produse ON stor_produse.idp=stor_locatii_ecommerce_log.idp
                    JOIN stor_categorii ON stor_categorii.idc=stor_produse.idc
                    JOIN auth_groups ON stor_categorii.idg=auth_groups.id
                    WHERE
                    MONTH(stor_locatii_ecommerce_log.data)='.$month.'
                    AND YEAR(stor_locatii_ecommerce_log.data)='.$year.'
                    AND ecommerce="da"
                    AND auth_groups.id='.$client->group->id.'
                    AND x<=10
                    GROUP BY x,y,stor_locatii_ecommerce_log.data
                    ORDER BY stor_locatii_ecommerce_log.data ASC, x ASC, y ASC, z ASC, t ASC, u ASC
                ) as pallets_table
                GROUP BY pallets_table.datal
            ');
                foreach ($data['pallets'] as $datum){
                    $days_data[$datum->store_date]['pallets'] = $datum->pallets_count;
                    $days_data[$datum->store_date]['pallet_price'] = $daily_price['pallet_price'] * (int)$datum->pallets_count;
                }
                
                foreach ($days_data as $key => $days_datum) {
                    $totals['shelf_price'] = (double)$totals['shelf_price'] + (isset($days_datum['shelf_price']) ? (double)$days_datum['shelf_price'] : 0);
                    $totals['pallet_price'] = (double)$totals['pallet_price'] + (isset($days_datum['pallet_price']) ? (double)$days_datum['pallet_price'] : 0);
                }
            }
            
            if(request('export') && request('export') == '1'){
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', __('main.date'));
                $sheet->setCellValue('B1', __('main.shelves'));
                $sheet->setCellValue('C1', __('main.shelf_price'));
                $sheet->setCellValue('D1', __('main.shelves'));
                $sheet->setCellValue('E1', __('main.pallet_price'));
                $row = 1;
                foreach ($days_data as $key => $days_datum) {
                    $row++;
                    $sheet->setCellValue('A' . $row, $key);
                    $sheet->setCellValue('B' . $row, isset($days_datum['shelves']) ? $days_datum['shelves'] : '');
                    $sheet->setCellValue('C' . $row, isset($days_datum['shelf_price']) ? $days_datum['shelf_price'] : '');
                    $sheet->setCellValue('D' . $row, isset($days_datum['pallets']) ? $days_datum['pallets'] : '');
                    $sheet->setCellValue('E' . $row, isset($days_datum['pallet_price']) ? $days_datum['pallet_price'] : '');
                }
                $row++;
                $sheet->setCellValue('A' . $row, __('main.total'));
                $sheet->setCellValue('B' . $row, '');
                $sheet->setCellValue('C' . $row, $totals['shelf_price']);
                $sheet->setCellValue('D' . $row, '');
                $sheet->setCellValue('E' . $row, $totals['pallet_price']);
                
                $writer = new Xlsx($spreadsheet);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'. urlencode('storage_report-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
                $writer->save('php://output');
                exit;
            }
            
            $months = array_reduce(range(1,12),function($rslt,$m){ $rslt[$m] = date('F',mktime(0,0,0,$m,10)); return $rslt; });
            $years = range(date("Y"),2017);
            return view('storage_report', compact('months', 'years', 'days_data', 'totals'));
        }
        
        public function getAllTopProducts(Request $request){
            $client = session('client');
            $ido = User::where('group_id','=',$client->group->id)->where('name','=',$client->group->name)->firstOrFail()->id;
    
            $top_products = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('dashboard_filter')['range_start'] && session('dashboard_filter')['range_start'] != ''){
                        $query->where(DB::raw('DATE(data_procesare_comanda)'), '>=', session('dashboard_filter')['range_start']);
                    }
                })
                ->where(function ($query){
                    if(session('dashboard_filter')['range_end'] && session('dashboard_filter')['range_end'] != ''){
                        $query->where( DB::raw('DATE(data_procesare_comanda)'), '<=', session('dashboard_filter')['range_end']);
                    }
                })
                ->select(
                    DB::raw('COUNT(stor_iesiri.idp) as sold_products'),
                    'stor_iesiri.idp',
                    'stor_produse.descriere as product_name'
                )
                ->leftJoin('stor_produse', 'stor_iesiri.idp', '=', 'stor_produse.idp')
                ->groupBy('idp')
                ->orderBy('sold_products', 'DESC')
                ->offset($request->get('offset'))
                ->limit(10)
                ->get()
            ;
            return response()->json($top_products);
        }
    }
