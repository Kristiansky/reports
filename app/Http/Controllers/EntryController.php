<?php

namespace App\Http\Controllers;

use App\AuthGroup;
use App\Entry;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
        if(request('filter') && request('filter') == '1'){
            $entries_filter = array(
                'search' => request('search'),
                'without_stock' => request('without_stock'),
            );
            session()->put('entries_filter', $entries_filter);
            return redirect(route('entries.index'));
        }elseif (request('reset') && request('reset') == '1'){
            session()->forget('entries_filter');
            return redirect(route('entries.index'));
        }
        
        $client = session('client');
        if(!$client){
            return redirect(route('home'));
        }
        $entries = DB::table('stor_produse')
            ->select('stor_produse.idp','stor_intrari.dataintrare','stor_produse.codprodusclient','stor_produse.descriere', DB::raw('SUM(stor_intrari.bucati) AS bucati'))
            ->leftJoin('stor_categorii', 'stor_produse.idc', '=', 'stor_categorii.idc')
            ->leftJoin('auth_groups', 'auth_groups.id', '=', 'stor_categorii.idg')
            ->leftJoin('stor_intrari', 'stor_intrari.idp', '=', 'stor_produse.idp')
            ->where('auth_groups.id', '=', $client->group->id)
            ->where(function ($query){
                $query->where('stor_produse.idp', session('entries_filter')['search'] ? '=' : '<>', session('entries_filter')['search'])
                    ->orWhere('stor_produse.codprodusclient', session('entries_filter')['search'] ? 'like' : 'not like', '%' . session('entries_filter')['search'] . '%')
                    ->orWhere('stor_produse.descriere', session('entries_filter')['search'] ? 'like' : 'not like', '%' . session('entries_filter')['search'] . '%');
            })
            ->groupBy('stor_produse.idp', 'stor_intrari.dataintrare')
            ->orderBy('stor_intrari.dataintrare', 'desc');
    
        if(request('export') && request('export') == '1'){
            $entries = $entries->get();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', __('main.product_id'));
            $sheet->setCellValue('B1', __('main.sku'));
            $sheet->setCellValue('C1', __('main.name'));
            $sheet->setCellValue('D1', __('main.qty'));
            $sheet->setCellValue('E1', __('main.entry_date'));
            $row = 1;
            foreach ($entries as $entry) {
                $row++;
                $sheet->setCellValue('A' . $row, $entry->idp);
                $sheet->setCellValue('B' . $row, $entry->codprodusclient);
                $sheet->setCellValue('C' . $row, $entry->descriere);
                $sheet->setCellValue('D' . $row, $entry->bucati);
                $sheet->setCellValue('E' . $row, $entry->dataintrare);
            }
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'. urlencode('entries-' . date("H-i-s-d-m-Y") . '.xlsx').'"');
            $writer->save('php://output');
        }
        $entries = $entries->paginate(session('per_page'));
//        $paginator = new Paginator($entries, session('per_page'), request('page') ? request('page') : 1, ['path' => route('entries.index')]);
        return view('entries.index', compact('entries'/*, 'paginator'*/));
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
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function show(Entry $entry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function edit(Entry $entry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Entry $entry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Entry $entry)
    {
        //
    }
}
