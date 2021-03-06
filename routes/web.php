<?php
    
    use Illuminate\Support\Facades\Redirect;
    use Illuminate\Support\Facades\Route;
    
    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */
    Route::get('/get_stocks', 'ProductController@getStocks')->name('get_stocks');
    
    Route::get('/look_invoice', 'OrderController@lookInvoice');
    
    Route::get('storage/{filename}', function ($filename)
    {
        $path = storage_path('public/' . $filename);
        
        if (!File::exists($path)) {
            dd($filename);
            abort(404);
        }
        
        $file = File::get($path);
        $type = File::mimeType($path);
        
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        
        return $response;
    });
    
    Route::get('admin/pre', function(){
        return Redirect::to('', 301);
    });
    
    Route::get('lang/{locale}', 'LocalizationController@index');
    
    Route::get('login', 'Auth\LoginController@login')->name('login');
    Route::post('login', 'Auth\LoginController@authenticate');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');
    
    Route::middleware(['auth', 'check.admin'])->group(function () {
        Route::get('/change_client', 'IndexController@changeClientView')->name('change_client_view');
        Route::post('/change_client', 'IndexController@changeClientUpdate')->name('change_client_update');
    });
    Route::middleware(['auth', 'check.client'])->group(function () {
        Route::post('/storage_report', 'IndexController@storageReport')->name('storage_report');
        Route::get('/storage_report', 'IndexController@storageReport')->name('storage_report');
        Route::get('/', 'IndexController@index')->name('home');
        Route::post('/', 'IndexController@index')->name('home');
        Route::resource('/order', 'OrderController');
        Route::post('/order', 'OrderController@index')->name('order.index');
        Route::post('/order/destroy', 'OrderController@destroy')->name('order.destroy');
        Route::post('/order/exclude', 'OrderController@exclude')->name('order.exclude');
        Route::post('/order/block', 'OrderController@block')->name('order.block');
        Route::post('/order/create', 'OrderController@create')->name('order.create');
        Route::post('/order/store', 'OrderController@store')->name('order.store');
        Route::resource('/product', 'ProductController');
        Route::post('/product', 'ProductController@index')->name('product.index');
        Route::get('/product_ajax_search', 'IndexController@ajaxSearch')->name('product_ajax_search');
        Route::get('/entries', 'EntryController@index')->name('entries.index');
        Route::post('/entries', 'EntryController@index')->name('entries.index');
        Route::post('/get_top_products', 'IndexController@getAllTopProducts');
        Route::post('/per_page', 'IndexController@changePerPage')->name('change_per_page');
        Route::get('/order/{order}/upload_invoice', 'OrderController@uploadInvoice')->name('order.upload_invoice');
        Route::post('/order/{order}/upload_invoice', 'OrderController@uploadInvoice')->name('order.upload_invoice');
    });
