<?php

namespace App\Http\Controllers;

use App\AuthGroup;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductController extends Controller
{
    public $stacks_to_clients = array(
        262 => true, //KOLIB
    );
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client = session('client');
        $idc = array();
        foreach(AuthGroup::where('id', '=', $client->group->id)->firstOrFail()->product_categories as $product_category){
            $idc[] = $product_category->idc;
        }
        if(request('filter') && request('filter') == '1'){
            $product_filter = array(
                'search' => request('search'),
                'without_stock' => request('without_stock'),
                'entry_from_date' => request('entry_from_date'),
                'entry_to_date' => request('entry_to_date'),
                'expiration_date' => request('expiration_date'),
            );
            session()->put('product_filter', $product_filter);
            return redirect(route('product.index'));
        }elseif (request('reset') && request('reset') == '1'){
            session()->forget('product_filter');
            session()->forget('products_sort');
            session()->forget('products_sort_direction');
            return redirect(route('product.index'));
        }
    
        if(request('sort')){
            session()->put('products_sort', request('sort'));
            session()->put('products_sort_direction', request('direction'));
            return redirect(route('product.index'));
        }
        
        if(!session('products_sort')){
            session()->put('products_sort', 'idp');
            session()->put('products_sort_direction', 'asc');
        }
        
        //Todo: Enable No stock filter
        $entries = DB::table('stor_intrari')
            ->select('stor_intrari.idp', 'dataintrare', 'data_expirare', DB::raw('SUM(bucati) AS suma'))
            ->groupBy('stor_intrari.idp');
//
//        $sales_expediat = DB::table('stor_iesiri')
//            ->select('stor_iesiri.idp', DB::raw('SUM(volum) AS suma'))
//            ->where('status', '=', 'expediat')
//            ->groupBy('stor_iesiri.idp');
        
        $products = Product::whereIn('idc', $idc)
            ->select(
                'stor_produse.idp as idp',
                'stor_produse.codprodusclient as codprodusclient',
                'stor_produse.descriere as descriere',
                'stor_produse.codbare as codbare',
                'stor_produse.pieces_in_package as pieces_in_package'
//                DB::raw('(COALESCE(entries.suma, 0) - COALESCE(sales_expediat.suma, 0)) as current_total_expediat')
            )
            ->where(function ($query){
                if(session('product_filter')['search'] && session('product_filter')['search'] != ''){
                    $query->where('stor_produse.idp', '=', session('product_filter')['search'])
                        ->orWhere('codprodusclient', 'like', '%' . session('product_filter')['search'] . '%')
                        ->orWhere('descriere', 'like', '%' . session('product_filter')['search'] . '%');
                }
                if((session('product_filter')['entry_from_date'] && session('product_filter')['entry_from_date']!="") && !session('product_filter')['entry_to_date']){
                    $query->where('entries.dataintrare', '!=', '0000-00-00')
                        ->where('entries.dataintrare', '>=', session('product_filter')['entry_from_date']);
                }elseif((session('product_filter')['entry_from_date'] && session('product_filter')['entry_from_date']!="") && (session('product_filter')['entry_to_date'] && session('product_filter')['entry_to_date']!="")){
                    $query->where('entries.dataintrare', '!=', '0000-00-00')
                        ->where('entries.dataintrare', '>=', session('product_filter')['entry_from_date'])
                        ->whereBetween('entries.dataintrare', [session('product_filter')['entry_from_date'], session('product_filter')['entry_to_date']]);
                }
            })
            ->leftJoinSub($entries, 'entries', function ($join) {
                $join->on('entries.idp', '=', 'stor_produse.idp');
            })
//            ->leftJoinSub($sales_expediat, 'sales_expediat', function ($join) {
//                $join->on('sales_expediat.idp', '=', 'stor_produse.idp');
//            })
//            ->having('current_total_expediat', '>', !session('product_filter')['without_stock'] && session('product_filter')['without_stock'] == 0 ? 0 : -1)
            ->orderBy(
                session('products_sort') ? session('products_sort') : 'stor_produse.idp',
                session('products_sort_direction') ? session('products_sort_direction') : 'asc'
            )
        ;
    
        if(request('export') && request('export') == '1'){
            $products = $products->get();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', __('main.product_id'));
            $sheet->setCellValue('B1', __('main.sku'));
            $sheet->setCellValue('C1', __('main.barcode'));
            $sheet->setCellValue('D1', __('main.name'));
            $sheet->setCellValue('E1', __('main.stock'));
            $sheet->setCellValue('F1', __('main.incl_new'));
            $sheet->setCellValue('G1', __('main.lots'));
            $sheet->setCellValue('H1', __('main.lot_expiration'));
            $sheet->setCellValue('I1', __('main.damaged'));
            $row = 1;
            foreach ($products as $product) {
                $row++;
                $sheet->setCellValue('A' . $row, $product->idp);
                $sheet->setCellValue('B' . $row, $product->codprodusclient);
                $sheet->setCellValue('C' . $row, $product->codbare);
                $sheet->setCellValue('D' . $row, $product->descriere);
                if (isset($this->stacks_to_clients[$client->group->id]) && $this->stacks_to_clients[$client->group->id] == true && $product->pieces_in_package != NULL){
                    $sheet->setCellValue('E' . $row, $product->stock() . ' / ' . __('main.stacks') . ': ' . $product->stacks());
                }else{
                    $sheet->setCellValue('E' . $row, $product->stock());
                }
                $sheet->setCellValue('F' . $row, $product->stockInclNew());
                $lots = '';
                $expiration_dates = '';
                if($product->lots()){
                    foreach($product->lots() as $lot){
                        if(session('product_filter')['expiration_date'] && $lot['dataexp'] <= session('product_filter')['expiration_date']){
                            $lots .= $lot['number_of_items'] . ' ' . __('main.items_in') . ' ' . $lot['lotul'] . "\n";
                            $expiration_dates .= $lot['dataexp'] . "\n";
                        }elseif(!session('product_filter')['expiration_date']){
                            $lots .= $lot['number_of_items'] . ' ' . __('main.items_in') . ' ' . $lot['lotul'] . "\n";
                            $expiration_dates .= $lot['dataexp'] . "\n";
                        }
                    }
                }
                $sheet->setCellValue('G' . $row, $lots);
                $sheet->setCellValue('H' . $row, $expiration_dates);
                $damaged = '';
                if($product->damaged()){
                    $damaged = $product->damaged()->total;
                }
                $sheet->setCellValue('I' . $row, $damaged);
            }
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'. urlencode('stock-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
            $writer->save('php://output');
            exit;
        }
    
        $products = $products->paginate(session('per_page'));
//        $paginator = new Paginator($products, session('per_page'), request('page') ? request('page') : 1, ['path' => route('product.index')]);
        if (isset($this->stacks_to_clients[$client->group->id]) && $this->stacks_to_clients[$client->group->id] == true){
            $show_stacks = true;
        }else{
            $show_stacks = false;
        }
        return view('products.index', compact('products', 'show_stacks'/*, 'paginator'*/));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $entries_result = DB::table('stor_intrari')
            ->select('stor_intrari.idin', 'stor_intrari.bucati', 'stor_intrari.dataintrare', 'stor_intrari.aviz', 'stor_intrari.data_expirare')
            ->where('stor_intrari.idp', '=', $product->idp)
            ->distinct()->get()
        ;
        $entries = array();
        foreach($entries_result as $key=>$row){
            $entry = DB::table('stor_receptii_detalii')
                ->select('idreceptie')
                ->where('idp', '=', $product->idp)
                ->where('dataexp', '=', $row->data_expirare)
                ->first()
            ;
            $entries[] = array(
                'idin' => $row->idin,
                'bucati' => $row->bucati,
                'dataintrare' => $row->dataintrare,
                'aviz' => $row->aviz,
                'data_expirare' => $row->data_expirare,
                'idreceptie' => $entry ? $entry->idreceptie : ''
            );
        }
        
        $orders = DB::table('stor_iesiri')
            ->select('stor_iesiri.idie', 'stor_iesiri.idextern', 'stor_iesiri.perscontact', 'stor_iesiri.volum', 'stor_iesiri.datai', 'stor_iesiri.status', 'stor_iesiri.expiration_batch', 'stor_iesiri.expiration_date')
            ->where('stor_iesiri.idp', '=', $product->idp)
            ->orderBy('idie', 'DESC')
            ->get()
        ;
        return response()->json(['product' => $product, 'stock' => $product->stock(), 'stock_incl_new' => $product->stockInclNew(), 'lots' => $product->lots(), 'entries' => $entries, 'orders' => $orders]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        DB::table('stor_produse')
            ->where('idp','=', $product->idp)
            ->update([
                'descriere' => $request->descriere,
                'codprodusclient' => $request->codprodusclient,
            ]);
    
        session()->flash('message', __('main.product_success_edit'));
        session()->flash('message_type', 'success');
        return redirect(route('product.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
    
    public function getStocks()
    {
        $products = Product::where('idc', '=', 180)->get();
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', __('main.product_id'));
        $sheet->setCellValue('B1', __('main.sku'));
        $sheet->setCellValue('C1', __('main.barcode'));
        $sheet->setCellValue('D1', __('main.name'));
        $sheet->setCellValue('E1', __('main.stock'));
        $sheet->setCellValue('F1', __('main.incl_new'));
        $sheet->setCellValue('G1', __('main.lots'));
        $sheet->setCellValue('H1', __('main.lot_expiration'));
        $sheet->setCellValue('I1', __('main.damaged'));
        $row = 1;
        
        foreach ($products as $product){
            $stock = $product->stockInclNew();
            if ($stock < 0){
                $row++;
                $sheet->setCellValue('A' . $row, $product->idp);
                $sheet->setCellValue('B' . $row, $product->codprodusclient);
                $sheet->setCellValue('C' . $row, $product->codbare);
                $sheet->setCellValue('D' . $row, $product->descriere);
                if (isset($this->stacks_to_clients[$client->group->id]) && $this->stacks_to_clients[$client->group->id] == true && $product->pieces_in_package != NULL){
                    $sheet->setCellValue('E' . $row, $product->stock() . ' / ' . __('main.stacks') . ': ' . $product->stacks());
                }else{
                    $sheet->setCellValue('E' . $row, $product->stock());
                }
                $sheet->setCellValue('F' . $row, $product->stockInclNew());
                $lots = '';
                $expiration_dates = '';
                if($product->lots()){
                    foreach($product->lots() as $lot){
                        if(session('product_filter')['expiration_date'] && $lot['dataexp'] <= session('product_filter')['expiration_date']){
                            $lots .= $lot['number_of_items'] . ' ' . __('main.items_in') . ' ' . $lot['lotul'] . "\n";
                            $expiration_dates .= $lot['dataexp'] . "\n";
                        }elseif(!session('product_filter')['expiration_date']){
                            $lots .= $lot['number_of_items'] . ' ' . __('main.items_in') . ' ' . $lot['lotul'] . "\n";
                            $expiration_dates .= $lot['dataexp'] . "\n";
                        }
                    }
                }
                $sheet->setCellValue('G' . $row, $lots);
                $sheet->setCellValue('H' . $row, $expiration_dates);
                $damaged = '';
                if($product->damaged()){
                    $damaged = $product->damaged()->total;
                }
                $sheet->setCellValue('I' . $row, $damaged);
            }
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode('negative-stock-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
        $writer->save('php://output');
        exit;
    }
}
