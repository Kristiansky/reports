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
            180 => 'bguchebnik_packets',
            182 => 'titantrade_packets',
        );
        
        public $stacks_to_clients = array(
            262 => true, //KOLIB
        );
        
        public $upload_invoice_to_clients = array(
            268 => true, //BGUCH
            254 => true, //PAOLITA
        );
        
        public $from_to_idextern = array(
            268 => true, //BGUCH
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
                    'entered_date_from' => request('entered_date_from'),
                    'entered_date_to' => request('entered_date_to'),
                    'sent_date_from' => request('sent_date_from'),
                    'sent_date_to' => request('sent_date_to'),
                    'country' => request('country'),
                    'other' => request('other'),
                    'courier' => request('courier'),
                    'status_courier' => request('status_courier'),
                    'from_id' => request('from_id'),
                    'to_id' => request('to_id'),
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
            
            if (isset($this->stacks_to_clients[$client->group->id]) && $this->stacks_to_clients[$client->group->id] == true){
                $show_stacks = true;
            }else{
                $show_stacks = false;
            }
            
            if (isset($this->upload_invoice_to_clients[$client->group->id]) && $this->upload_invoice_to_clients[$client->group->id] == true){
                $show_upload_ivnoice = true;
            }else{
                $show_upload_ivnoice = false;
            }
            
            if (isset($this->from_to_idextern[$client->group->id]) && $this->from_to_idextern[$client->group->id] == true){
                $show_from_to_idextern = true;
            }else{
                $show_from_to_idextern = false;
            }
            
            $orders = Order::where('ido', '=', $ido)
                ->where(function ($query){
                    if(session('order_filter')['search'] && session('order_filter')['search'] != ''){
                        $query->where('idcomanda', '=', session('order_filter')['search'])
                            ->orWhere('idextern', '=', session('order_filter')['search'])
                            ->orWhere('awb', '=', session('order_filter')['search'])
                            ->orWhere('perscontact', 'like', '%' . session('order_filter')['search'] . '%');
                    }
                    if(session('order_filter')['from_id'] && session('order_filter')['from_id'] != ''){
                        $query->where(DB::raw('CAST(idextern AS INT)'), '>=', session('order_filter')['from_id']);
//                            ->where('status', '!=', 'expediat');
                    }
                    if(session('order_filter')['to_id'] && session('order_filter')['to_id'] != ''){
                        $query->where(DB::raw('CAST(idextern AS INT)'), '<=', session('order_filter')['to_id']);
//                            ->where('status', '!=', 'expediat');
                    }
                    if(session('order_filter')['entered_date_from'] && session('order_filter')['entered_date_from'] != ''){
                        $query->where('datai', '>', session('order_filter')['entered_date_from'].' 00:00:00');
                    }
                    if(session('order_filter')['entered_date_to'] && session('order_filter')['entered_date_to'] != ''){
                        $query->where('datai', '<', session('order_filter')['entered_date_to'].' 23:59:59');
                    }
                    if(session('order_filter')['sent_date_from'] && session('order_filter')['sent_date_from'] != ''){
                        $query->where('data_procesare_comanda', '>', session('order_filter')['sent_date_from'].' 00:00:00');
                    }
                    if(session('order_filter')['sent_date_to'] && session('order_filter')['sent_date_to'] != ''){
                        $query->where('data_procesare_comanda', '<', session('order_filter')['sent_date_to'].' 23:59:59');
                    }
                    if(session('order_filter')['country'] && session('order_filter')['country'] != ''){
                        $query->where('tara', '=', session('order_filter')['country']);
                    }
                    if(session('order_filter')['courier'] && session('order_filter')['courier'] != ''){
                        $query->where('curier', '=', session('order_filter')['courier']);
                    }
                    if(session('order_filter')['status_courier'] && session('order_filter')['status_courier'] != ''){
                        $query->where('statuscurier', '=', session('order_filter')['status_courier']);
                    }
                    if(session('order_filter')['status'] && session('order_filter')['status'] != ''){
                        if(session('order_filter')['status'] == 'procesabila'){
                            $query->where('deadline', '!=', '0000-00-00 00:00:00')
                                ->where('parcurs', '=', '1');
//                                ->where('status', '!=', 'expediat')
                        }elseif(session('order_filter')['status'] == 'neprocesabila'){
                            $query->where('deadline', '=', '0000-00-00 00:00:00')
                                ->where('parcurs', '=', '1');
//                                ->where('status', '!=', 'expediat')
                        }elseif(session('order_filter')['status'] == 'retur'){
                            $query->where('ceretur', '!=', '0000-00-00');
                        }elseif(session('order_filter')['status'] == 'incompleta'){
                            $query->where('incompleta', '=', 'da');
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
                    'incompleta',
                    DB::raw('SUM(volum) as qty')
                )
                ->groupBy('idcomanda')
                ->orderBy(
                    session('orders_sort') ? session('orders_sort') : 'idcomanda',
                    session('orders_sort_direction') ? session('orders_sort_direction') : 'desc'
                );
            
            if(request('export_orders_products') && request('export_orders_products') == '1'){
    
                $orders->where('status', '!=', 'expediat');
                $orders = $orders->get();
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $products_feed = [];
                if($orders){
                    
                    $sheet->setCellValue('A1', 'sku');
                    $sheet->setCellValue('B1', 'name');
                    $sheet->setCellValue('C1', 'barcode');
                    $sheet->setCellValue('D1', 'current_stock');
                    $sheet->setCellValue('E1', 'qty_ordered');
                    $sheet->setCellValue('F1', 'total_for_warehouse');
                    
                    foreach ($orders as $order) {
                        foreach ($order->products as $product){
                            if(isset($products_feed[$product['codprodusclient']])){
                                $products_feed[$product['codprodusclient']]['qty_ordered'] = (int) $products_feed[$product['codprodusclient']]['qty_ordered'] + (int) $product['volum'];
                            }else{
                                $total_for_warehouse = 0;
//                                if ($product['volum'] > $product['stock']){
//                                    $total_for_warehouse = $product['volum'] + abs($product['stock']);
//                                }
                                $products_feed[$product['codprodusclient']] = array(
                                    'sku' => $product['codprodusclient'],
                                    'name' => $product['descriere'],
                                    'barcode' => $product['codbare'],
                                    'qty_ordered' => $product['volum'],
                                    'current_stock' => $product['stock'],
                                    'total_for_warehouse' => $total_for_warehouse,
                                );
                            }
                        }
                    }
                    foreach($products_feed as $product_key => $product){
                        $total_for_warehouse = 0;
                        if (($product['qty_ordered'] > $product['current_stock']) && ($product['current_stock'] >= 0)){
                            $total_for_warehouse = $product['qty_ordered'] - $product['current_stock'];
                        }elseif (($product['qty_ordered'] > $product['current_stock']) && ($product['current_stock'] < 0)){
                            $total_for_warehouse = $product['qty_ordered'] + abs($product['current_stock']);
                        }
                        $products_feed[$product_key]['total_for_warehouse'] = $total_for_warehouse;
                    }
                    
                    $row = 1;
                    foreach($products_feed as $product_feed){
                        $row++;
                        $sheet->setCellValue('A' . $row, $product_feed['sku']);
                        $sheet->setCellValue('B' . $row, $product_feed['name']);
                        $sheet->setCellValue('C' . $row, $product_feed['barcode']);
                        $sheet->setCellValue('D' . $row, $product_feed['current_stock']);
                        $sheet->setCellValue('E' . $row, $product_feed['qty_ordered']);
                        $sheet->setCellValue('F' . $row, $product_feed['total_for_warehouse']);
                    }
                    $writer = new Xlsx($spreadsheet);
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="'. urlencode('products-ordered-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
                    $writer->save('php://output');
                    exit;
                }
            }
            
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
                if($show_stacks){
                    $sheet->setCellValue('H1', __('main.stacks'));
                    $sheet->setCellValue('I1', __('main.courier'));
                    $sheet->setCellValue('J1', __('main.awb'));
                    $sheet->setCellValue('K1', __('main.status_courier'));
                    if (request('include_products') && request('include_products') == '1'){
                        $sheet->setCellValue('L1', __('main.order_products'));
                    }
                }else{
                    $sheet->setCellValue('H1', __('main.courier'));
                    $sheet->setCellValue('I1', __('main.awb'));
                    $sheet->setCellValue('J1', __('main.status_courier'));
                    if (request('include_products') && request('include_products') == '1'){
                        $sheet->setCellValue('K1', __('main.order_products'));
                    }
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
                    if($show_stacks){
                        $sheet->setCellValue('H' . $row, $order->getTotalStacks());
                        $sheet->setCellValue('I' . $row, $order->curier);
                        $sheet->setCellValue('J' . $row, $order->awb);
                        if ($order->ceretur != '0000-00-00' && $order->curier == 'econt'){
                            $sheet->setCellValue('K' . $row, __('main.returned_order'));
                        }else{
                            $sheet->setCellValue('K' . $row, $order->statuscurier);
                        }
                    }else{
                        $sheet->setCellValue('H' . $row, $order->curier);
                        $sheet->setCellValue('I' . $row, $order->awb);
                        if ($order->ceretur != '0000-00-00' && $order->curier == 'econt'){
                            $sheet->setCellValue('J' . $row, __('main.returned_order'));
                        }else{
                            $sheet->setCellValue('J' . $row, $order->statuscurier);
                        }
                    }
                    
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
                        if($show_stacks){
                            $sheet->setCellValue('L' . $row, $products_text);
                        }else{
                            $sheet->setCellValue('K' . $row, $products_text);
                        }
                    }
                }
                $writer = new Xlsx($spreadsheet);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'. urlencode('orders-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
                $writer->save('php://output');
                exit;
            }
            
            $orders = $orders->paginate(session('per_page'));
//        $paginator = new Paginator($orders, session('per_page'), request('page') ? request('page') : 1, ['path' => route('order.index')]);
            
            
            $status_options = $this->status_options;
            $country_options = $this->country_options;
            $courier_options = Order::select('curier')
                ->where('ido', '=', $ido)
                ->distinct()
                ->get()
            ;
            $status_courier_options = Order::select('statuscurier')
                ->where('ido', '=', $ido)
                ->where('statuscurier', '!=', '')
                ->distinct()
                ->get()
            ;
            return view('orders.index', compact('orders'/*, 'paginator'*/, 'status_options', 'country_options', 'courier_options', 'status_courier_options', 'show_stacks', 'show_upload_ivnoice', 'show_from_to_idextern'));
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
//        $data['products'] = array();
//        $exclude = array();
//        if(session('cart_products')){
//            foreach (session('cart_products') as $item) {
//                $exclude[]=$item[0]['idp'];
//            }
//        }
            $idcs = array();
            foreach ($productCategories as $productCategory){
                $idcs[] = $productCategory->idc;
//            foreach ($productCategory->products->sortBy('idp') as $product){
//                if(/*$product->stock() > 0 &&*/ !in_array($product->idp, $exclude)){
//                    $data['products'][] = $product;
//                }
//            }
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
                $item_count = 0;
                foreach ($fixed_headings as $key => $heading){
                    if (strpos($heading, 'item_id') !== false) {
                        $item_count++;
                        $fixed_headings[$key] = 'item_id_' . $item_count;
                    }
                    if (strpos($heading, 'name') !== false) {
                        $fixed_headings[$key] = 'name_' . $item_count;
                    }
                    if (strpos($heading, 'quantity') !== false) {
                        $fixed_headings[$key] = 'quantity_' . $item_count;
                    }
                }
                
                array_walk(
                    $xlsx_arr,
                    function (&$row) use ($fixed_headings) {
                        $row = array_combine($fixed_headings, $row);
                    }
                );
                
                foreach($xlsx_arr as $key => $row){
                    foreach ($row as $sub_key => $sub_row){
                        if ((strpos($sub_key, 'item_id') !== false || strpos($sub_key, 'name') !== false || strpos($sub_key, 'quantity') !== false) && $sub_row == null){
                            unset($xlsx_arr[$key][$sub_key]);
                        }
                    }
                }
                
                foreach($xlsx_arr as $key => $row){
                    $item_recount = 0;
                    if (isset($row['order_id'])){
                        foreach ($row as $sub_key => $sub_row){
                            if (strpos($sub_key, 'item_id') !== false) {
                                $item_recount++;
                            }
                        }
                        $xlsx_arr[$key]['item_count'] = $item_recount;
                    }
                }
                
                $orders = array();
                foreach($xlsx_arr as $key => $row){
                    if (isset($row['order_id'])){
                        if ($row['item_count'] > 1){
                            for ($i=1;$i<=$row['item_count'];$i++){
                                if (isset($row['item_id_'.$i])){
                                    $row['item_id'] = $row['item_id_'.$i];
                                }
                                if (isset($row['name_'.$i])){
                                    $row['name'] = $row['name_'.$i];
                                }
                                if (isset($row['quantity_'.$i])){
                                    $row['quantity'] = $row['quantity_'.$i];
                                }
                                unset($row['item_id_'.$i]);
                                unset($row['name_'.$i]);
                                unset($row['quantity_'.$i]);
                                $orders[$row['order_id']][]=$row;
                            }
                        }else{
                            $row['item_id'] = $row['item_id_1'];
                            $row['name'] = $row['name_1'];
                            $row['quantity'] = $row['quantity_1'];
                            unset($row['item_id_1']);
                            unset($row['name_1']);
                            unset($row['quantity_1']);
                            $orders[$row['order_id']][]=$row;
                        }
                    }
                }
                
                $orderTotalFields = array('order_total', 'order total', 'order_value', 'order value', 'price');
                
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
                        
                        $comments = isset($row['comments']) ? $row['comments'] : '';
                        $address_only = isset($row['address']) ? $row['address'] : '';
                        $address = $address_only . " " . $comments;
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
                        
                        $phone = $row['phone'];
                        $phoneMatch = array();
                        if (($client->cod_client == 'BDC' || $client->cod_client == 'WOW') && preg_match('/(\+?359)?0?(.*)/', $phone, $phoneMatch)) {
                            $phone = '0' . $phoneMatch[2];
                        }
                        $phone = empty($phone) ? "" : $phone;
                        
                        $valoareramburs = '';
                        foreach ($orderTotalFields as $f){
                            $valoareramburs = isset($row[$f]) && !empty($row[$f]) ? trim($row[$f]) : $valoareramburs;
                        }
                        @reset($orderTotalFields);
                        
                        $metoda = strtoupper(trim($row['payment_method']));
                        
                        if (empty($metoda) || $metoda == 'COD') {
                            $metoda = 'cashondelivery';
                        } else {
                            $metoda = 'NOCOD';
                            //$valoareramburs='';
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
                                        'adresa' => $address_only,
                                        'localitate' => $row['town'],
                                        'judet' => $row['town'],
                                        'tara' => isset($row['country']) ? $row['country']: (isset($row['courier']) && strtolower($row['courier']) == 'acs' ? 'GR' : "BG"),
                                        'perscontact' => $row['contact_person'],
                                        'codpostal' => isset($row['postcode']) ? $row['postcode'] : '',
                                        'telpers' => $phone,
                                        'emailpers' => isset($row['email']) ? $row['email'] : '',
                                        'ramburs' => $valoareramburs,
                                        'sambata' => 0,
                                        'altele' => $office_code,
                                        'status' => 'Comanda',
                                        'pret' => 0,
                                        'modplata' => $metoda,
                                        'curier' => isset($row['courier']) ? strtolower(trim($row['courier'])) : "n/a",
                                        'ship_instructions' => $comments,
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
                                    'adresa' => $address_only,
                                    'localitate' => $row['town'],
                                    'judet' => $row['town'],
                                    'tara' => isset($row['country']) ? $row['country']: (isset($row['courier']) && strtolower($row['courier']) == 'acs' ? 'GR' : "BG"),
                                    'perscontact' => $row['contact_person'],
                                    'codpostal' => isset($row['postcode']) ? $row['postcode'] : '',
                                    'telpers' => $phone,
                                    'emailpers' => isset($row['email']) ? $row['email'] : '',
                                    'ramburs' => $valoareramburs,
                                    'sambata' => 0,
                                    'altele' => $office_code,
                                    'status' => 'Comanda',
                                    'pret' => 0,
                                    'modplata' => $metoda,
                                    'curier' => isset($row['courier']) ? strtolower(trim($row['courier'])) : "n/a",
                                    'ship_instructions' => $comments,
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
                                'adresa' => $address_only,
                                'localitate' => $row['town'],
                                'judet' => $row['town'],
                                'tara' => isset($row['country']) ? $row['country']: (isset($row['courier']) && strtolower($row['courier']) == 'acs' ? 'GR' : "BG"),
                                'perscontact' => $row['contact_person'],
                                'codpostal' => isset($row['postcode']) ? $row['postcode'] : '',
                                'telpers' => $phone,
                                'emailpers' => isset($row['email']) ? $row['email'] : '',
                                'ramburs' => $valoareramburs,
                                'sambata' => 0,
                                'altele' => $office_code,
                                'status' => 'Comanda',
                                'pret' => 0,
                                'modplata' => $metoda,
                                'curier' => isset($row['courier']) ? strtolower(trim($row['courier'])) : "n/a",
                                'ship_instructions' => $comments,
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
//        $client = session('client');
//        $productCategories = $client->group->product_categories;
//        $adding_products = array();
//        $exclude = array();
//        if($order->products){
//            foreach ($order->products as $item) {
//                $exclude[]=$item['idp'];
//            }
//        }
//        $idcs = array();
//        foreach ($productCategories as $productCategory){
//            $idcs[] = $productCategory->idc;
//            foreach ($productCategory->products->sortBy('idp') as $product){
//                if(/*$product->stock() > 0 &&*/ !in_array($product->idp, $exclude)){
//                    $product['stock'] = (int)$product->stock();
//                    $adding_products[] = $product;
//                }
//            }
//        }

//        return response()->json(['order'=>$order,'adding_products'=>$adding_products]);
            return response()->json(['order'=>$order]);
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
                $old_order_id = $order->idie;
                DB::table('stor_iesiri')
                    ->where('idcomanda', '=', $order->idie)
                    ->where('idp', '=', (int)$request->removeProduct)
                    ->delete();
                $remaining_rows = DB::table('stor_iesiri')
                    ->where('idcomanda', '=', $order->idie)->get();
                if (!empty($remaining_rows)){
                    $new_order_id = $remaining_rows->first()->idie;
                    DB::table('stor_iesiri')
                        ->where('idcomanda', '=', $old_order_id)
                        ->update(
                            ['idcomanda' => $new_order_id]
                        );
                }
                session()->flash('edited_order_idcomanda', $order->idcomanda);
                session()->flash('message', __('main.order_cart_success_remove'));
                session()->flash('message_type', 'success');
                return redirect(route('order.index') . "?page=" . $request->current_page);
            }
            if(isset($request->addingProduct)){
                $last_row = Order::where('idcomanda', '=', $order->idie)
                    ->get()->last();
                
                $order_array = [
                    'ido' => $last_row->ido,
                    'idso' => Auth::user()->id,
                    'idp' => $request->adding_product,
                    'volum' => $request->adding_qty,
                    'data1' => $last_row->data1,
                    'data2' => $last_row->data2,
                    'datai' => $last_row->datai,
                    'locatie' => $last_row->locatie,
                    'idextern' => $last_row->idextern,
                    'idcomanda' => $last_row->idcomanda,
                    'adresa' => $last_row->adresa,
                    'tstr' => $last_row->tstr,
                    'str' => $last_row->str,
                    'nr' => $last_row->nr,
                    'bl' => $last_row->bl,
                    'sc' => $last_row->sc,
                    'ap' => $last_row->ap,
                    'et' => $last_row->et,
                    'localitate' => $last_row->localitate,
                    'tara' => $last_row->tara,
                    'judet' => $last_row->judet,
                    'perscontact' => $last_row->perscontact,
                    'codpostal' => $last_row->codpostal,
                    'telpers' => $last_row->telpers,
                    'emailpers' => $last_row->emailpers,
                    'ramburs' => $last_row->ramburs,
                    'url_factura' => $last_row->url_factura,
                    'sambata' => $last_row->sambata,
                    'altele' => $last_row->altele,
                    'status' => $last_row->getRawOriginal('status'),
                    'pret' => $last_row->pret,
                    'modplata' => $last_row->modplata,
                    'curier' => $last_row->curier,
                    'shipping_method' => $last_row->shipping_method,
                    'ship_instructions' => $last_row->ship_instructions,
                ];
                
                DB::table('stor_iesiri')->insert(
                    $order_array
                );
                
                session()->flash('edited_order_idcomanda', $order->idcomanda);
                session()->flash('message', __('main.order_cart_success_add'));
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
        
        public function exclude(Request $request)
        {
            $selection = DB::table('stor_iesiri')
                ->select(
                    'idie',
                    'ido',
                    'idcomanda',
                    'idextern',
                    'group_id'
                )
                ->where('idcomanda','=', $request->idcomanda)
                ->join('auth_users','stor_iesiri.ido','=','auth_users.id')
                ->first()
            ;
            
            DB::table('stor_comenzi_excluse')->insert([
                'idcomandaclient' => $selection->idextern,
                'idg' => $selection->group_id,
                'iduser' => Auth::user()->id,
                'data' => date('Y-m-d H:i:s')
            ]);
            
//            $this->destroy($request);
            DB::table('stor_iesiri')
                ->where('idcomanda','=', $request->idcomanda)
                ->delete();
            session()->flash('message', __('main.order_success_delete'));
            session()->flash('message_type', 'success');
            return redirect(route('order.index'));
        }
        
        public function block(Request $request){
            $order = Order::where('idcomanda','=', $request->idcomanda)->first();
            if ($order->getRawOriginal('status') == 'InProcesare'){
                return 'in_process';
            }else{
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
                
                return 'blocked';
            }
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
                            $idcomanda = $this->insertProductToOrder($current_product, $qty, $request, $i, $idcomanda, $product->descriere);
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
        
        
        public function insertProductToOrder(Product $product, $qty, Request $request, $i, $idcomanda, $packet_title = ''){
            $ido = User::where('group_id','=',$product->category->group->id)->where('name','=',$product->category->group->name)->first()->id;
            $address = $request->get('tstr') . " " . $request->get('str');
            $nr = $request->get('nr');
            $bl = $request->get('bl');
            $sc = $request->get('sc');
            $ap = $request->get('ap');
            $et = $request->get('et');
            if($nr != ''){
                $address .= ' Nr. ' . $nr;
            }
            if($bl != ''){
                $address .= ' Bl. ' . $bl;
            }
            if($sc != ''){
                $address .= ' Vh. ' . $sc;
            }
            if($ap != ''){
                $address .= ' Ap. ' . $ap;
            }
            if($et != ''){
                $address .= ' Et. ' . $et;
            }
            $locatie = "";
            if ($request->get('locatie') == null){
                $locatie = "";
            }else{
                $locatie = $request->get('locatie');
            }
            $tstr = "";
            if ($request->get('tstr') == null){
                $tstr = "";
            }else{
                $tstr = $request->get('tstr');
            }
            $str = "";
            if ($request->get('str') == null){
                $str = "";
            }else{
                $str = $request->get('str');
            }
            $order_array = [
                'ido' => $ido,
                'idso' => Auth::user()->id,
                'idp' => $product->idp,
                'volum' => $qty,
                'data1' => $request->get('data1'),
                'data2' => $request->get('data2'),
                'datai' => date("Y-m-d H:i:s"),
                'locatie' => $locatie,
                'idcomanda' => $idcomanda,
                'adresa' => $address,
                'tstr' => $tstr,
                'str' => $str,
                'nr' => $nr == null ? '' : $nr,
                'bl' => $bl == null ? '' : $bl,
                'sc' => $sc == null ? '' : $sc,
                'ap' => $ap == null ? '' : $ap,
                'et' => $et == null ? '' : $et,
                'idextern' => $request->get('idextern'),
                'localitate' => $request->get('localitate'),
                'tara' => $request->get('tara') == '' ? 'BG' : $request->get('tara'),
                'judet' => $request->get('judet'),
                'perscontact' => $request->get('perscontact'),
                'codpostal' => $request->get('codpostal'),
                'telpers' => $request->get('telpers'),
                'emailpers' => $request->get('emailpers'),
                'ramburs' => $request->get('ramburs'),
                'url_factura' => $request->get('url_factura'),
                'sambata' => $request->get('sambata') ? $request->get('sambata') : 'nu',
                'altele' => $request->get('altele'),
                'status' => 'Comanda',
                'pret' => 0,
                'modplata' => $request->get('ramburs') != 0 ? 'cashondelivery' : '',
                'curier' => $request->get('curier'),
                'shipping_method' => $request->get('shipping_method') ? $request->get('shipping_method') : "",
                'ship_instructions' => $request->get('ship_instructions') . ($i == 0 ? $packet_title : ''),
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
                return $idcomanda;
            }
        }
        
        public function uploadInvoice(Request $request, Order $order){
            $client = session('client');
            
            if(request('addPdf')) {
                $validation = array(
                    'pdf_file' => 'required|file',
                );
                $request->validate($validation);
                
                $filename = date('Ymdhis') . "_" . $request->file('pdf_file')->getClientOriginalName();
                
                $request->file('pdf_file')->storeAs('/clients/' . $client->cod_client . '/pdf', $filename, 'public_uploads');
                
                DB::table('stor_iesiri')->where('idcomanda', $order->idcomanda)->update(['url_factura' => url('/') . '/uploads/clients/' . $client->cod_client . '/pdf/' . $filename]);
                
                session()->flash('message', __('main.order_success_edit'));
                session()->flash('message_type', 'success');
                return redirect(route('order.index'));
            }
            
            return view('orders.upload_invoice', compact('order'));
        }
        
        public function lookInvoice(){
            $storagePath  = Storage::disk('public_uploads')->getDriver()->getAdapter()->getPathPrefix();
            return $storagePath;
        }
    }
