<?php

namespace App\Http\Controllers;

use App\User;
use App\Order;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxRead;

class OrderController extends Controller
{
    public $str_options = array("Алея","Булевард","Път","Магистрала","Улица","Квартал","Ж.К.","Площад");
    
    public $status_options = array('comanda'=>'Order', 'inprocesare'=>'InProcess', 'expediat'=>'Sent', 'blocata'=>'Blocked', 'incompleta'=>'Incomplete', 'procesabila'=>'Processable', 'neprocesabila'=>'UnProcessable', 'retur'=>'Return');
    
    public $country_options = array('BG','GR','RO','IT','ES','GB','DE','NL');
    
    public $packets_to_clients = array(
        153 => 'smart_packets',
        155 => 'escreo_packets',
    );
    
    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index()
    {
        $client = session('client');
        $ido = User::where('group_id','=',$client->group->id)->where('name','=',$client->group->name)->firstOrFail()->id;
        if(request('filter') && request('filter') == '1'){
            $order_filter = array(
                'search' => request('search'),
                'status' => request('status'),
                'date_from' => request('date_from'),
                'date_to' => request('date_to'),
                'country' => request('country'),
                'other' => request('other'),
            );
            session()->put('order_filter', $order_filter);
            return redirect(route('order.index'));
        }elseif (request('reset') && request('reset') == '1'){
            session()->forget('order_filter');
            session()->forget('orders_sort');
            session()->forget('orders_sort_direction');
            return redirect(route('order.index'));
        }
        
        if(request('sort')){
            session()->put('orders_sort', request('sort'));
            session()->put('orders_sort_direction', request('direction'));
            return redirect(route('order.index'));
        }
        
        if(!session('orders_sort')){
            session()->put('orders_sort', 'idcomanda');
            session()->put('orders_sort_direction', 'desc');
        }
        
        $orders = Order::where('ido', '=', $ido)
            ->where(function ($query){
                if(session('order_filter')['search'] && session('order_filter')['search'] != ''){
                    $query->where('idcomanda', '=', session('order_filter')['search'])
                        ->orWhere('idextern', '=', session('order_filter')['search'])
                        ->orWhere('perscontact', 'like', '%' . session('order_filter')['search'] . '%');
                }
                
                if(session('order_filter')['date_from'] && session('order_filter')['date_from'] != ''){
                    $query->where('datai', '>', session('order_filter')['date_from'].' 00:00:00');
                }
                if(session('order_filter')['date_to'] && session('order_filter')['date_to'] != ''){
                    $query->where('datai', '<', session('order_filter')['date_to'].' 00:00:00');
                }
                if(session('order_filter')['country'] && session('order_filter')['country'] != ''){
                    $query->where('tara', '=', session('order_filter')['country']);
                }
                if(session('order_filter')['status'] && session('order_filter')['status'] != ''){
                    if(session('order_filter')['status'] == 'procesabila'){
                        $query->where('deadline', '!=', '0000-00-00 00:00:00')
                        ->where('parcurs', '=', '1')
                        ->where('status', '!=', 'expediat');
                    }elseif(session('order_filter')['status'] == 'neprocesabila'){
                        $query->where('deadline', '=', '0000-00-00 00:00:00')
                            ->where('parcurs', '=', '1')
                            ->where('status', '!=', 'expediat');
                    }elseif(session('order_filter')['status'] == 'retur'){
                        $query->where('ceretur', '!=', '0000-00-00');
                    }else{
                        $query->where('status', '=', session('order_filter')['status']);
                    }
                }
            })
            ->select(
                'idie',
                'idextern',
                'datai',
                'data_procesare_comanda',
                'perscontact',
                'status',
                'curier',
                'awb',
                'statuscurier',
                'ceretur',
                'parcurs',
                'deadline',
                'idcomanda',
                DB::raw('SUM(volum) as qty')
            )
            ->groupBy('idcomanda')
            ->orderBy(
                session('orders_sort') ? session('orders_sort') : 'idcomanda',
                session('orders_sort_direction') ? session('orders_sort_direction') : 'desc'
            );
    
        if(request('export') && request('export') == '1'){
            $orders = $orders->get();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', __('main.order_id'));
            $sheet->setCellValue('B1', __('main.external_id'));
            $sheet->setCellValue('C1', __('main.order_in_date'));
            $sheet->setCellValue('D1', __('main.order_sent_date'));
            $sheet->setCellValue('E1', __('main.to'));
            $sheet->setCellValue('F1', __('main.status'));
            $sheet->setCellValue('G1', __('main.qty'));
            $sheet->setCellValue('H1', __('main.courier'));
            $sheet->setCellValue('I1', __('main.awb'));
            $sheet->setCellValue('J1', __('main.status_courier'));
            if (request('include_products') && request('include_products') == '1'){
                $sheet->setCellValue('K1', __('main.order_products'));
            }
            
            $row = 1;
            foreach ($orders as $order) {
                $row++;
                $sheet->setCellValue('A' . $row, $order->idie);
                $sheet->setCellValue('B' . $row, $order->idextern);
                $sheet->setCellValue('C' . $row, $order->datai);
                $sheet->setCellValue('D' . $row, $order->data_procesare_comanda == '0000-00-00 00:00:00' ? '' : $order->data_procesare_comanda);
                $sheet->setCellValue('E' . $row, $order->perscontact);
                $sheet->setCellValue('F' . $row, strip_tags($order->status));
                $sheet->setCellValue('G' . $row, $order->qty);
                $sheet->setCellValue('H' . $row, $order->curier);
                $sheet->setCellValue('I' . $row, $order->awb);
                $sheet->setCellValue('J' . $row, $order->statuscurier);
    
                if (request('include_products') && request('include_products') == '1'){
                    if ($order->ceretur != '0000-00-00'){
                        $products_text = '';
                        foreach ($order->products as $product){
                            $products_text .= $product['codprodusclient'] . ' - ' . $product['volum'] . ' - ' . ($product['is_returned'] ? __('main.returned') : __('main.not_returned')) . ";\r\n";
                        }
                    }else{
                        $products_text = '';
                        foreach ($order->products as $product){
                            $products_text .= $product['codprodusclient'] . ' - ' . $product['volum'] . ";\r\n";
                        }
                    }
                    $sheet->setCellValue('K' . $row, $products_text);
                }
            }
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'. urlencode('orders-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
            $writer->save('php://output');
            
        }
    
        $orders = $orders->paginate(session('per_page'));
//        $paginator = new Paginator($orders, session('per_page'), request('page') ? request('page') : 1, ['path' => route('order.index')]);
    
    
        $status_options = $this->status_options;
        $country_options = $this->country_options;
        return view('orders.index', compact('orders'/*, 'paginator'*/, 'status_options', 'country_options'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $client = session('client');
        $productCategories = $client->group->product_categories;
        $data['products'] = array();
        $exclude = array();
        if(session('cart_products')){
            foreach (session('cart_products') as $item) {
                $exclude[]=$item[0]['idp'];
            }
        }
        $idcs = array();
        foreach ($productCategories as $productCategory){
            $idcs[] = $productCategory->idc;
            foreach ($productCategory->products->sortBy('idp') as $product){
                if(/*$product->stock() > 0 &&*/ !in_array($product->idp, $exclude)){
                    $data['products'][] = $product;
                }
            }
        }
        
        if(request('removeCartProduct')){
            
            $cart_products = session('cart_products');
            session()->forget('cart_products');
            foreach($cart_products as $cart_product){
                if($cart_product[0]['idp'] == request('removeCartProduct')){
                    continue;
                }else{
                    $product_arr = array('idp' => $cart_product[0]['idp'], 'qty' => $cart_product[0]['qty']);
                    session()->push('cart_products', collect([$product_arr]));
                }
            }
            session()->flash('message', __('main.order_cart_success_remove'));
            session()->flash('message_type', 'success');
            return redirect(route('order.create'));
        }
        $validation = array();
        if(request('addProduct')){
            $validation = array(
                'product' => 'required|integer',
                'qty' => 'required|integer',
            );
            $request->validate($validation);
            
            $product_arr = array('idp' => request('product'), 'qty' => request('qty'));
            session()->push('cart_products', collect([$product_arr]));
            session()->flash('message', __('main.order_cart_success_add'));
            session()->flash('message_type', 'success');
        }
        
        if(!empty(session('cart_products'))){
            foreach(session('cart_products') as $product){
                $data['cart_products'][]=array('product' => Product::where('idp','=',$product[0]['idp'])->firstOrFail(), 'qty' => $product[0]['qty']);
            }
        }
        
        if(request('addProduct')){
            return redirect(route('order.create'));
        }
        
        if(request('addXlsx')){
            $validation = array(
                'xlsx_file' => 'required|file',
            );
            $request->validate($validation);
    
            $filename = date('Ymdhis') . "_" . $request->file('xlsx_file')->getClientOriginalName();
            
            $request->file('xlsx_file')->storeAs('/clients/' . $client->cod_client . '/orders', $filename);
    
//            $stored_file = storage_path('/clients/' . $client->cod_client . '/orders/' . $filename);
            
            $reader = new XlsxRead();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->file('xlsx_file'));
            $xlsx_arr = $spreadsheet->getActiveSheet()->toArray();
            $headings = array_shift($xlsx_arr);
            $fixed_headings = array();
            foreach ($headings as $heading){
                $heading = strtolower($heading);
                $heading = preg_replace('/\s+/', '_', $heading);
                $fixed_headings[] = $heading;
            }
            array_walk(
                $xlsx_arr,
                function (&$row) use ($fixed_headings) {
                    $row = array_combine($fixed_headings, $row);
                }
            );

            $orders = array();
            foreach($xlsx_arr as $key => $row){
                $orders[$row['order_id']][]=$row;
            }
            
            foreach ($orders as $order){
                $i=0;
                foreach ($order as $key => $row){
                    $codcomanda = $row['order_id'];
                    if (empty($codcomanda)) {
                        continue;
                    }
                    
                    $product = Product::where('codprodusclient','=',$row['item_id'])->whereIn('idc',$idcs)->first();
                    if($product == null){
                        $product = Product::where('descriere','=','Unknown product')->whereIn('idc', $idcs)->first();
                    }
        
                    $address = $row['address'] . " " . $row['comments'];
                    if (isset($row['office_code'])){
                        if (
                            empty($row['office_code'])
                            && (strpos($address, 'econt') !== false
                                || strpos($address, 'еконт') !== false
                                || strpos($address, 'Еконт') !== false
                            )
                        ){
                            $office_code = 1;
                        } elseif (empty($row['office_code'])){
                            $office_code = 'Automat';
                        }else{
                            $office_code = $row['office_code'];
                        }
                    } else {
                        $office_code = 'Automat';
                    }
                    
                    if (isset($this->packets_to_clients[$product->idc])){
                        $packet_products = DB::table($this->packets_to_clients[$product->idc])
                            ->select('quantity','SKU2')
                            ->where('SKU1', '=', $product->codprodusclient)
                            ->orderBy('id', 'asc')
                            ->get();
                        if ($packet_products->isNotEmpty()){
                            foreach ($packet_products as $packet_product){
                                $qty = (int)$packet_product->quantity * (int)$row['quantity'];
                                $current_product = Product::where('codprodusclient','=',$packet_product->SKU2)->where('idc','=',$product->idc)->firstOrFail();
                                $ido = User::where('group_id','=',$current_product->category->group->id)->where('name','=',$current_product->category->group->name)->firstOrFail()->id;
                                $order_array = [
                                    'ido' => $ido,
                                    'idso' => Auth::user()->id,
                                    'idp' => $current_product->idp,
                                    'volum' => $qty,
                                    'data1' => date("Y-m-d"),
                                    'data2' => date("Y-m-d"),
                                    'datai' => date("Y-m-d H:i:s"),
                                    'idcomanda' => (isset($idcomanda) ? $idcomanda : '0'),
                                    'adresa' => $address,
                                    'localitate' => $row['town'],
                                    'judet' => $row['town'],
                                    'tara' => isset($row['country']) ? $row['country']: (isset($row['courier']) && strtolower($row['courier']) == 'acs' ? 'GR' : "BG"),
                                    'perscontact' => $row['contact_person'],
                                    'codpostal' => isset($row['postcode']) ? $row['postcode'] : '',
                                    'telpers' => $row['phone'],
                                    'ramburs' => $row['order_value'] == '' ? 0 : $row['order_value'],
                                    'sambata' => 0,
                                    'altele' => $office_code,
                                    'status' => 'Comanda',
                                    'pret' => 0,
                                    'modplata' => $row['order_value'] != null ? 'cashondelivery' : '',
                                    'curier' => isset($row['courier']) ? strtolower(trim($row['courier'])) : "n/a",
                                    'ship_instructions' => $row['comments'] != null ? $row['comments'] : '',
                                    'idextern' => $codcomanda,
                                    'shipping_method' => isset($row['shipping_method']) ? $row['shipping_method'] : '',
                                    'url_factura' => isset($row['invoice_url']) ? $row['invoice_url'] : "",
                                ];
        
                                if($i==0){
                                    $idcomanda = DB::table('stor_iesiri')->insertGetId(
                                        $order_array
                                    );
                                    DB::table('stor_iesiri')
                                        ->where('idie', $idcomanda)->update(['idcomanda' => $idcomanda]);
                                }else{
                                    DB::table('stor_iesiri')->insert(
                                        $order_array
                                    );
                                }
                                $i++;
                            }
                        }else{
                            $qty = (int)$row['quantity'];
                            $ido = User::where('group_id','=',$product->category->group->id)->where('name','=',$product->category->group->name)->firstOrFail()->id;
                            $order_array = [
                                'ido' => $ido,
                                'idso' => Auth::user()->id,
                                'idp' => $product->idp,
                                'volum' => $qty,
                                'data1' => date("Y-m-d"),
                                'data2' => date("Y-m-d"),
                                'datai' => date("Y-m-d H:i:s"),
                                'idcomanda' => (isset($idcomanda) ? $idcomanda : '0'),
                                'adresa' => $address,
                                'localitate' => $row['town'],
                                'judet' => $row['town'],
                                'tara' => isset($row['country']) ? $row['country']: (isset($row['courier']) && strtolower($row['courier']) == 'acs' ? 'GR' : "BG"),
                                'perscontact' => $row['contact_person'],
                                'codpostal' => isset($row['postcode']) ? $row['postcode'] : '',
                                'telpers' => $row['phone'],
                                'ramburs' => $row['order_value'] != null ? $row['order_value'] : 0,
                                'sambata' => 0,
                                'altele' => $office_code,
                                'status' => 'Comanda',
                                'pret' => 0,
                                'modplata' => $row['order_value'] != null ? 'cashondelivery' : '',
                                'curier' => isset($row['courier']) ? strtolower(trim($row['courier'])) : "n/a",
                                'ship_instructions' => $row['comments'] != null ? $row['comments'] : '',
                                'idextern' => $codcomanda,
                                'shipping_method' => isset($row['shipping_method']) ? $row['shipping_method'] : '',
                                'url_factura' => isset($row['invoice_url']) ? $row['invoice_url'] : "",
                            ];
    
                            if($i==0){
                                $idcomanda = DB::table('stor_iesiri')->insertGetId(
                                    $order_array
                                );
                                DB::table('stor_iesiri')
                                    ->where('idie', $idcomanda)->update(['idcomanda' => $idcomanda]);
                            }else{
                                DB::table('stor_iesiri')->insert(
                                    $order_array
                                );
                            }
                            $i++;
                        }
                    }else{
                        $qty = (int)$row['quantity'];
                        $ido = User::where('group_id','=',$product->category->group->id)->where('name','=',$product->category->group->name)->firstOrFail()->id;
                        $order_array = [
                            'ido' => $ido,
                            'idso' => Auth::user()->id,
                            'idp' => $product->idp,
                            'volum' => $qty,
                            'data1' => date("Y-m-d"),
                            'data2' => date("Y-m-d"),
                            'datai' => date("Y-m-d H:i:s"),
                            'idcomanda' => (isset($idcomanda) ? $idcomanda : '0'),
                            'adresa' => $address,
                            'localitate' => $row['town'],
                            'judet' => $row['town'],
                            'tara' => isset($row['country']) ? $row['country']: (isset($row['courier']) && strtolower($row['courier']) == 'acs' ? 'GR' : "BG"),
                            'perscontact' => $row['contact_person'],
                            'codpostal' => isset($row['postcode']) ? $row['postcode'] : '',
                            'telpers' => $row['phone'],
                            'ramburs' => $row['order_value'] != null ? $row['order_value'] : 0,
                            'sambata' => 0,
                            'altele' => $office_code,
                            'status' => 'Comanda',
                            'pret' => 0,
                            'modplata' => $row['order_value'] != null ? 'cashondelivery' : '',
                            'curier' => isset($row['courier']) ? strtolower(trim($row['courier'])) : "n/a",
                            'ship_instructions' => $row['comments'] != null ? $row['comments'] : '',
                            'idextern' => $codcomanda,
                            'shipping_method' => isset($row['shipping_method']) ? $row['shipping_method'] : '',
                            'url_factura' => isset($row['invoice_url']) ? $row['invoice_url'] : "",
                        ];
    
                        if($i==0){
                            $idcomanda = DB::table('stor_iesiri')->insertGetId(
                                $order_array
                            );
                            DB::table('stor_iesiri')
                                ->where('idie', $idcomanda)->update(['idcomanda' => $idcomanda]);
                        }else{
                            DB::table('stor_iesiri')->insert(
                                $order_array
                            );
                        }
                        $i++;
                    }
                }
            }
            session()->flash('message', __('main.order_success_add'));
            session()->flash('message_type', 'success');
            
            return redirect(route('order.index'));
        }
        
        $data['stroptions'] = $this->str_options;
        return view('orders.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validation = array(
            'ramburs' => 'nullable|numeric',
        );
        $request->validate($validation);
    
        $this->addProductsToOrder($request);
    
        session()->forget('cart_products');
        
        session()->flash('message', __('main.order_success_add'));
        session()->flash('message_type', 'success');
        
        return redirect(route('order.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @return Response
     */
    public function show(Order $order)
    {
        return response()->json($order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Order $order
     * @return Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Order $order
     * @return Response
     */
    public function update(Request $request, Order $order)
    {
        if(isset($request->removeProduct)){
            DB::table('stor_iesiri')
                ->where('idcomanda', '=', $order->idie)
                ->where('idp', '=', (int)$request->removeProduct)
                ->delete();
            session()->flash('edited_order_idcomanda', $order->idcomanda);
            session()->flash('message', __('main.order_cart_success_remove'));
            session()->flash('message_type', 'success');
            return redirect(route('order.index') . "?page=" . $request->current_page);
        }
        DB::table('stor_iesiri')
            ->where('idcomanda','=', $order->idcomanda)
            ->update([
                'ramburs' => $request->ramburs,
                'adresa' => $request->adresa,
                'codpostal' => $request->codpostal,
                'localitate' => $request->localitate,
                'judet' => $request->judet,
                'perscontact' => $request->perscontact,
                'telpers' => $request->telpers,
                'emailpers' => $request->emailpers,
                'sambata' => $request->sambata,
                'ship_instructions' => $request->ship_instructions,
                'altele' => $request->altele,
            ]);
        foreach($request->qty as $key=>$qty){
            DB::table('stor_iesiri')
                ->where('idcomanda','=', $order->idie)
                ->where('idp','=', $key)
                ->update([
                    'volum' => $qty,
                ]);
        }
        DB::table('stor_iesiri')
            ->where('idcomanda','=', $order->idie)
            ->update([
                'status' => 'Comanda',
            ]);
        DB::table('stor_log_comenzi')->insert(
            [
                'ido' => Auth::user()->id,
                'idcomanda' => $order->idie,
                'actiune' => "Deblocata",
                'data' => date("Y-m-d H:i:s"),
            ]
        );
        session()->flash('message', __('main.order_success_edit'));
        session()->flash('message_type', 'success');
        return redirect(route('order.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Order $order
     * @return Response
     */
    public function destroy(Request $request)
    {
        DB::table('stor_iesiri')
            ->where('idcomanda','=', $request->idcomanda)
            ->delete();
        session()->flash('message', __('main.order_success_delete'));
        session()->flash('message_type', 'success');
        return redirect(route('order.index'));
    }
    
    public function block(Request $request){
        DB::table('stor_iesiri')
            ->where('idcomanda','=', $request->idcomanda)
            ->update([
                'status' => 'Blocata',
                'deadline' => '0000-00-00 00:00:00',
            ]);
        DB::table('stor_log_comenzi')->insert(
            [
                'ido' => Auth::user()->id,
                'idcomanda' => $request->idcomanda,
                'actiune' => "Blocata",
                'data' => date("Y-m-d H:i:s"),
            ]
        );
    
        return true;
    }
    
    public function addProductsToOrder(Request $request){
        $i=0;
        $idcomanda = 0;
        foreach(session('cart_products') as $cart_product){
            $product = Product::where('idp','=',$cart_product[0]['idp'])->first();
            if (isset($this->packets_to_clients[$product->idc])){
                $packet_products = DB::table($this->packets_to_clients[$product->idc])
                    ->select('quantity','SKU2')
                    ->where('SKU1', '=', $product->codprodusclient)
                    ->orderBy('id', 'asc')
                    ->get();
                if ($packet_products->isNotEmpty()){
                    foreach ($packet_products as $packet_product) {
                        $qty = (int)$packet_product->quantity * (int)$cart_product[0]['qty'];
                        $current_product = Product::where('codprodusclient','=',$packet_product->SKU2)->where('idc','=',$product->idc)->first();
                        $idcomanda = $this->insertProductToOrder($current_product, $qty, $request, $i, $idcomanda);
                        $i++;
                    }
                }else{
                    $idcomanda = $this->insertProductToOrder($product, $cart_product[0]['qty'], $request, $i, $idcomanda);
                    $i++;
                }
            }else{
                $idcomanda = $this->insertProductToOrder($product, $cart_product[0]['qty'], $request, $i, $idcomanda);
                $i++;
            }
        }
    }
    
    public function insertProductToOrder(Product $product, $qty, Request $request, $i, $idcomanda){
        $ido = User::where('group_id','=',$product->category->group->id)->where('name','=',$product->category->group->name)->first()->id;
        $address = $request->get('tstr') . " " . $request->get('str');
        if($nr = $request->get('nr') != ''){
            $address .= ' Nr. ' . $nr;
        }
        if($bl = $request->get('bl') != ''){
            $address .= ' Bl. ' . $bl;
        }
        if($sc = $request->get('sc') != ''){
            $address .= ' Sc. ' . $sc;
        }
        if($ap = $request->get('ap') != ''){
            $address .= ' Apt. ' . $ap;
        }
        if($et = $request->get('et') != ''){
            $address .= ' Et. ' . $et;
        }
        $order_array = [
            'ido' => $ido,
            'idso' => Auth::user()->id,
            'idp' => $product->idp,
            'volum' => $qty,
            'data1' => $request->get('data1'),
            'data2' => $request->get('data2'),
            'datai' => date("Y-m-d H:i:s"),
            'locatie' => $request->get('locatie'),
            'idcomanda' => $idcomanda,
            'adresa' => $address,
            'tstr' => $request->get('tstr'),
            'str' => $request->get('str'),
            'nr' => $nr,
            'bl' => $bl,
            'sc' => $sc,
            'ap' => $ap,
            'et' => $et,
            'localitate' => $request->get('localitate'),
            'judet' => $request->get('judet'),
            'perscontact' => $request->get('perscontact'),
            'codpostal' => $request->get('codpostal'),
            'telpers' => $request->get('telpers'),
            'ramburs' => $request->get('ramburs'),
            'rambursalttip' => $request->get('rambursalttip'),
            'sambata' => $request->get('sambata') ? $request->get('sambata') : 'nu',
            'altele' => $request->get('altele'),
            'status' => 'Comanda',
            'pret' => 0,
            'modplata' => $request->get('ramburs') != 0 ? 'cashondelivery' : '',
            'curier' => $request->get('curier'),
            'ship_instructions' => $request->get('locatie') . ' ' . $product->descriere,
        ];
    
        if($i==0){
            $idcomanda = DB::table('stor_iesiri')->insertGetId(
                $order_array
            );
            DB::table('stor_iesiri')
                ->where('idie', $idcomanda)->update(['idcomanda' => $idcomanda]);
            return $idcomanda;
        }else{
            DB::table('stor_iesiri')->insert(
                $order_array
            );
            return true;
        }
    }
}
