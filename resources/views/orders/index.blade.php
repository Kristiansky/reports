@extends('adminlte::page')

@section('title', __('main.orders'))

@section('content_header')
    <h1>{{ __('main.orders') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">{{__('main.filters')}}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- form start -->
                    <form class="" action="{{route('order.index')}}" method="post">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-6 col-lg-1">
                                <div class="form-group">
                                    <label for="search">{{__('main.search')}}</label>
                                    <input type="text" class="form-control form-control-sm" id="search" name="search" autocomplete="off" placeholder="{{__('main.search')}}" value="{{isset(session('order_filter')['search']) ? session('order_filter')['search'] : ''}}">
                                </div>
                            </div>
                            <div class="col-6 col-lg-1">
                                <div class="form-group">
                                    <label for="status">{{__('main.status')}}</label>
                                    <select class="form-control form-control-sm" id="status" name="status">
                                        <option value="">{{ __('main.choose') }}</option>
                                        @foreach($status_options as $key => $status_option)
                                            <option value="{{ $key }}" @if(session('order_filter')['status'] == $key) selected @endif>{{$status_option}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="row">
                                    <div class="col-6 col-lg-6">
                                        <div class="form-group">
                                            <label for="entered_date_from">
                                                {{__('main.entered_date_from')}}
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm" name="entered_date_from" id="entered_date_from" autocomplete="off" placeholder="{{__('main.entered_date_from')}}" value="{{isset(session('order_filter')['entered_date_from']) ? session('order_filter')['entered_date_from'] : ''}}">
                                                <div class="input-group-append">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-6">
                                        <div class="form-group">
                                            <label for="entered_date_to">
                                                {{__('main.entered_date_to')}}
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm" name="entered_date_to" id="entered_date_to" autocomplete="off" placeholder="{{__('main.entered_date_to')}}" value="{{isset(session('order_filter')['entered_date_to']) ? session('order_filter')['entered_date_to'] : ''}}">
                                                <div class="input-group-append">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="row">
                                    <div class="col-6 col-lg-6">
                                        <div class="form-group">
                                            <label for="sent_date_from">
                                                {{__('main.sent_date_from')}}
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm" name="sent_date_from" id="sent_date_from" autocomplete="off" placeholder="{{__('main.sent_date_from')}}" value="{{isset(session('order_filter')['sent_date_from']) ? session('order_filter')['sent_date_from'] : ''}}">
                                                <div class="input-group-append">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-lg-6">
                                        <div class="form-group">
                                            <label for="sent_date_to">
                                                {{__('main.sent_date_to')}}
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm" name="sent_date_to" id="sent_date_to" autocomplete="off" placeholder="{{__('main.sent_date_to')}}" value="{{isset(session('order_filter')['sent_date_to']) ? session('order_filter')['sent_date_to'] : ''}}">
                                                <div class="input-group-append">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-1">
                                <div class="form-group">
                                    <label for="country">{{__('main.country')}}</label>
                                    <select class="form-control form-control-sm" id="country" name="country">
                                        <option value="">{{ __('main.choose') }}</option>
                                        @foreach($country_options as $country_option)
                                            <option value="{{ $country_option }}" @if(session('order_filter')['country'] == $country_option) selected @endif>{{$country_option}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 pt-4 mt-2">
                                <button type="submit" name="filter" value="1" class="btn btn-primary btn-sm mr-2 float-left">{{__('main.filter')}}</button>
                                <button type="submit" name="reset" value="1" class="btn btn-default btn-sm mr-2 float-left">{{__('main.reset')}}</button>
                                <button type="submit" name="export" value="1" class="btn btn-warning btn-sm mr-2 float-left">{{__('main.export_xlsx')}}</button>
                                <div class="custom-control custom-checkbox float-lg-left float-right">
                                    <input class="custom-control-input" type="checkbox" id="include_products" name="include_products" value="1">
                                    <label for="include_products" class="custom-control-label"><small>{{__('main.include_products')}}</small></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 col-lg-1">
                                <div class="form-group">
                                    <label for="courier">{{__('main.courier')}}</label>
                                    <select class="form-control form-control-sm" id="courier" name="courier">
                                        <option value="">{{ __('main.choose') }}</option>
                                        @foreach($courier_options as $courier_option)
                                            <option value="{{ $courier_option->curier }}" @if(session('order_filter')['courier'] == $courier_option->curier) selected @endif>{{$courier_option->curier}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-lg-1">
                                <div class="form-group">
                                    <label for="status_courier">{{__('main.status_courier')}}</label>
                                    <select class="form-control form-control-sm" id="status_courier" name="status_courier">
                                        <option value="">{{ __('main.choose') }}</option>
                                        @foreach($status_courier_options as $status_courier_option)
                                            <option value="{{ $status_courier_option->statuscurier }}" @if(session('order_filter')['status_courier'] == $status_courier_option->statuscurier) selected @endif>{{$status_courier_option->statuscurier}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-4 pl-4 pt-4">
                            {{--{{__('main.showing_records', ['first_index' => $paginator->items()['from'], 'last_index' =>  $paginator->items()['to'], 'total_count' =>  $paginator->items()['total'] ])}}--}}
                            {{__('main.total_records')}}: {{ $orders->total() }}
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex mt-3">
                                <div class="mx-auto">
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 pt-3 pr-3">
                            <form method="post" action="{{route('change_per_page')}}" class="form-horizontal">
                                @csrf
                                @method('POST')
                                <div class="form-group row">
                                    <label for="per_page" class="col-sm-8 col-form-label text-right">{{__('main.per_page')}}</label>
                                    <div class="col-sm-4">
                                        <select class="form-control form-control-sm" id="per_page" name="per_page" onchange="this.form.submit()">
                                            @foreach (Config::get('app.per_page_options') as $option)
                                                <option value="{{$option}}" @if(session('per_page') && session('per_page') == $option) selected @endif>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-hover mb-3 text-sm">
                            <thead>
                            <tr>

                                @php
                                    if(session('orders_sort_direction') && session('orders_sort_direction') == 'desc'){
                                        $direction = 'asc';
                                        $icon = '<i class="fas fa-arrow-alt-circle-down"></i>';
                                    }elseif(session('orders_sort_direction') && session('orders_sort_direction') == 'asc'){
                                        $direction = 'desc';
                                        $icon = '<i class="fas fa-arrow-alt-circle-up"></i>';
                                    }else{
                                        $direction = 'desc';
                                        $icon = '<i class="fas fa-arrow-alt-circle-up"></i>';
                                    }
                                @endphp

                                <th width="5%">
                                    <a href="?sort=idcomanda&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'idcomanda'){!!$icon!!}@endif {{__('main.order_id')}}
                                    </a>
                                </th>
                                <th width="5%">
                                    <a href="?sort=idextern&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'idextern'){!!$icon!!}@endif {{__('main.external_id')}}
                                    </a>
                                </th>
                                <th width="7%">
                                    <a href="?sort=datai&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'datai'){!!$icon!!}@endif {{__('main.order_in_date')}}
                                    </a>
                                </th>
                                <th width="8%">
                                    <a href="?sort=data_procesare_comanda&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'data_procesare_comanda'){!!$icon!!}@endif {{__('main.order_sent_date')}}
                                    </a>
                                </th>
                                <th width="13%">
                                    <a href="?sort=perscontact&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'perscontact'){!!$icon!!}@endif {{__('main.to')}}
                                    </a>
                                </th>
                                <th width="12%">
                                    <a href="?sort=status&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'status'){!!$icon!!}@endif {{__('main.status')}}
                                    </a>
                                </th>
                                <th width="5%">
                                    <a href="?sort=qty&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'qty'){!!$icon!!}@endif {{__('main.qty')}}
                                    </a>
                                </th>
                                <th width="2%"></th>
                                <th width="5%">
                                    <a href="?sort=curier&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'curier'){!!$icon!!}@endif {{__('main.courier')}}
                                    </a>
                                </th>
                                <th width="6%">
                                    <a href="?sort=awb&direction={{$direction}}">
                                        @if(session('orders_sort') && session('orders_sort') == 'awb'){!!$icon!!}@endif {{__('main.awb')}}
                                    </a>
                                </th>
                                <th width="20%">{{__('main.status_courier')}}</th>
                                <th width="12%">{{__('main.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{$order->idcomanda}}</td>
                                    <td>{{$order->idextern}}</td>
                                    <td>{{$order->datai}}</td>
                                    <td>{{$order->data_procesare_comanda == '0000-00-00 00:00:00' ? '' : $order->data_procesare_comanda}}</td>
                                    <td>{{$order->perscontact}}</td>
                                    <td>{!! $order->status !!}</td>
                                    <td>{{$order->qty}}</td>
                                    <td>@if($order->getRawOriginal('status') != 'expediat' && $order->parcurs == 1) @if($order->deadline == '0000-00-00 00:00:00' || empty($order->deadline)) <button class="btn btn-xs btn-danger px-2">&nbsp;</button> @else <button class="btn btn-xs btn-success px-2">&nbsp;</button> @endif @endif</td>
                                    <td>{{$order->curier}}</td>
                                    <td>{{$order->awb}}</td>
                                    <td>@if($order->ceretur != '0000-00-00' && $order->curier == 'econt'){{__('main.returned_order')}}@else{{\Illuminate\Support\Str::limit($order->statuscurier, 40, '...')}}@endif</td>
                                    <td class="pr-1">
                                        <button data-idcomanda="{{$order->idcomanda}}" type="button" class="btn btn-xs btn-success openOrder" data-toggle="modal" data-target="#orderModal"><i class="fa fa-eye"></i> {{__('main.view')}}</button>
                                        @if($order->getRawOriginal('status') == 'Comanda' || $order->getRawOriginal('status') == 'Blocata')
                                            <button data-idcomanda="{{$order->idcomanda}}" type="button" class="btn btn-xs btn-primary editOrder" data-toggle="modal" data-target="#editOrderModal"><i class="fa fa-pen"></i> {{__('main.edit')}}</button>
                                        @endif
                                        @if($order->getRawOriginal('status') == 'Blocata')
                                            <form method="post" action="{{route('order.destroy')}}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="idcomanda" value="{{$order->idcomanda}}">
                                                <button class="btn btn-danger btn-xs" onclick="return confirm('{{__('main.are_you_sure')}}')"><i class="fa fa-trash-alt"></i> {{__('main.delete')}}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 pl-4 pt-4">
                            {{--{{__('main.showing_records', ['first_index' => $paginator->items()['from'], 'last_index' =>  $paginator->items()['to'], 'total_count' =>  $paginator->items()['total'] ])}}--}}
                            {{__('main.total_records')}}: {{ $orders->total() }}
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex mt-3">
                                <div class="mx-auto">
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="editOrderModal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="overlay light d-none" id="editOverlay">
                    <i class="fas fa-3x fa-spinner fa-pulse"></i>
                </div>
                <div class="modal-header">
                    <h4 class="modal-title">{{__('main.order')}}: <span id="edit_order_number"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="lead">{!!__('main.order_edit_text')!!}</p>
                    <form action="" method="post">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="current_page" value="{{$orders->currentPage()}}"/>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <tr>
                                    <th width="25%">{{__('main.order_date')}}</th>
                                    <td width="75%"><span id="edit_order_in_date"></span></td>
                                </tr>
                                <tr>
                                    <th>{{__('main.payment_method')}}</th>
                                    <td><span id="edit_order_payment_method"></span></td>
                                </tr>
                                <tr>
                                    <th>{{__('main.total')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="number" step=".01" class="form-control form-control-sm" name="ramburs" id="ramburs" autocomplete="off" placeholder="{{__('main.total')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.address')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="adresa" id="adresa" autocomplete="off" placeholder="{{__('main.address')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.post_code')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="codpostal" id="codpostal" autocomplete="off" placeholder="{{__('main.post_code')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.city')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="localitate" id="localitate" autocomplete="off" placeholder="{{__('main.city')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.district')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="judet" id="judet" autocomplete="off" placeholder="{{__('main.district')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.recipient')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="perscontact" id="perscontact" autocomplete="off" placeholder="{{__('main.person_name')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.phone')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="telpers" id="telpers" autocomplete="off" placeholder="{{__('main.phone')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.email')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="email" class="form-control form-control-sm" name="emailpers" id="emailpers" autocomplete="off" placeholder="{{__('main.email')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('main.saturday_delivery') }}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" type="checkbox" id="sambata" value="da" name="sambata">
                                                <label for="sambata" class="custom-control-label">{{ __('main.yes') }}</label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.office_code')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="altele" id="altele" autocomplete="off" placeholder="{{__('main.office_code')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{__('main.ship_instructions')}}</th>
                                    <td>
                                        <div class="form-group m-0">
                                            <input type="text" class="form-control form-control-sm" name="ship_instructions" id="ship_instructions" autocomplete="off" placeholder="{{__('main.ship_instructions')}}" value="">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">{{__('main.save')}}</button>
                                </div>
                            </div>
                        </div>
                        <h5>{{__('main.order_products')}}</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th width="25%">{{__('main.product_name')}}</th>
                                    <th width="10%">{{__('main.qty')}}</th>
                                    <th width="15%">{{__('main.internal_id')}}</th>
                                    <th width="10%">{{__('main.current_stock')}}</th>
                                    <th width="15%">{{__('main.sku')}}</th>
                                    <th width="15%">{{__('main.barcode')}}</th>
                                    <th width="10%">{{__('main.action')}}</th>
                                </tr>
                                </thead>
                                <tbody id="edit_order_products">
                                </tbody>
                                <tbody id="add_order_products">
                                <tr>
                                    <td id="choose_product_holder">
                                        <div class="form-group">
                                            <select class="form-control form-control-sm select2" id="adding_product" name="adding_product" data-width="100%">
                                            </select>
                                        </div>
                                    </td>
                                    <td id="quantity_product_holder">
                                        <div class="form-group">
                                            <input id="adding_qty" type="number" name="adding_qty" class="form-control form-control-sm" placeholder="{{__('main.qty')}}" min="1" max="1" autocomplete="off">
                                        </div>
                                    </td>
                                    <td id="internal_id_product_holder"></td>
                                    <td id="current_stock_product_holder"></td>
                                    <td id="sku_product_holder"></td>
                                    <td id="barcode_product_holder"></td>
                                    <td>
                                        <button class="btn btn btn-success btn-xs" type='submit' name='addingProduct'><i class="fa fa-plus-circle"></i> {{__('main.add')}}</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{__('main.close')}}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="orderModal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="overlay light d-none" id="viewOverlay">
                    <i class="fas fa-3x fa-spinner fa-pulse"></i>
                </div>
                <div class="modal-header">
                    <h4 class="modal-title">{{__('main.order')}}: <span id="order_number"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <tbody>
                            <tr>
                                <th width="25%">{{__('main.order_date')}}</th>
                                <td width="75%"><span id="order_in_date"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.payment_method')}}</th>
                                <td><span id="order_payment_method"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.total')}}</th>
                                <td><span id="order_total"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.address')}}</th>
                                <td><span id="order_address"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.city')}}</th>
                                <td><span id="order_city"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.post_code')}}</th>
                                <td><span id="order_post_code"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.country')}}</th>
                                <td><span id="order_country"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.recipient')}}</th>
                                <td><span id="order_to"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.phone')}}</th>
                                <td><span id="order_phone"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.email')}}</th>
                                <td><span id="order_email"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.office_code')}}</th>
                                <td><span id="order_other_info"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.ship_instructions')}}</th>
                                <td><span id="order_ship_instructions"></span></td>
                            </tr>
                            <tr>
                                <th>{{__('main.shipping_method')}}</th>
                                <td><span id="order_shipping_method"></span></td>
                            </tr>
                            <tr class="d-none" id="row_returned">
                                <th>{{__('main.is_returned')}}</th>
                                <td><span id="order_returned"></span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <h5>{{__('main.order_products')}}</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>{{__('main.product_name')}}</th>
                                <th>{{__('main.qty')}}</th>
                                <th>{{__('main.internal_id')}}</th>
                                <th>{{__('main.current_stock')}}</th>
                                <th>{{__('main.sku')}}</th>
                                <th>{{__('main.barcode')}}</th>
                                <th class="d-none returned">{{__('main.is_returned')}}</th>
                                <th class="d-none returned">{{__('main.return_reason')}}</th>
                            </tr>
                            </thead>
                            <tbody id="order_products">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{__('main.close')}}</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">
@stop

@section('plugins.Moment', true)
@section('plugins.Datetimepicker', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('js')
    <script>
		$(document).ready(function () {
			$('.select2').select2();
			var chosen_product = []
			$('#adding_product').select2({
				ajax: {
					url: '{{route("product_ajax_search")}}',
					dataType: 'json',
					processResults: function (data) {
						return {
							results:  $.map(data, function (item) {
								chosen_product = item;
								return {
									text: item.name,
									id: item.id
								}
							})
						};
					},
				},
				placeholder: 'Търсене на продукт...',
				minimumInputLength: 3,
			});
			$('#entered_date_from, #entered_date_to, #sent_date_from, #sent_date_to').datetimepicker({
				format: 'YYYY-MM-DD',
				icons:
					{
						previous: 'fas fa-angle-left',
						next: 'fas fa-angle-right',
						up: 'fas fa-angle-up',
						down: 'fas fa-angle-down'
					}
			})

			var Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000
			});
            @if(session('message'))
                Toast.fire({
                    icon: "{{session('message_type')}}",
                    title: "{{session('message')}}"
                })
            @endif

			function editOrderModal(idcomanda){
				var url = '{{ route("order.update", ":id") }}';
				url = url.replace(':id', idcomanda);
				$('#editOrderModal form').attr('action', url);
				$.ajax({
					url: 'order/block',
					type:"POST",
					data:{
						'idcomanda': idcomanda,
						'_token': '{{ csrf_token() }}'
					},
					success:function(response){
						if (response == 'in_process'){
							Toast.fire({
								icon: "danger",
								title: "{{__('main.order_in_process')}}"
							});
							setTimeout(function() {
								location.reload();
							}, 2000);
							throw new Error("{{__('main.order_in_process')}}");
                        }else{
							Toast.fire({
								icon: "warning",
								title: "{{__('main.order_blocked')}}"
							})
                        }
					}
				});
				$.ajax({
					url: 'order/'+idcomanda+'/edit',
					type:"GET",
					success:function(response){
						$('#editOverlay').addClass('d-none');
						/*var adding_products_html = "<option value=''>{{ __('main.choose') }}</option>";
						var products_details=[];
						response.adding_products.forEach(function(adding_product){
							products_details[adding_product.idp] = {
								'stock': adding_product.stock,
								'codprodusclient': adding_product.codprodusclient,
								'codbare': adding_product.codbare,
							};
							adding_products_html +=
                                '<option value="'+adding_product.idp+'">'+adding_product.codprodusclient+': '+adding_product.descriere+'</option>'
                                ;
                        });
						$('#adding_product').html(adding_products_html);*/

						$('#adding_product').on('change', function () {
							// var chosen_idp = $(this).val();
							$('input#adding_qty').attr('max', chosen_product.stock).attr('required',true);
							$('#internal_id_product_holder').html(chosen_product.idp);
							$('#current_stock_product_holder').html(chosen_product.stock);
							$('#sku_product_holder').html(chosen_product.codprodusclient);
							$('#barcode_product_holder').html(chosen_product.codbare);
						});

						$('#edit_order_number').html(response.order.idextern);
						$('#edit_order_in_date').html(response.order.data1);
						$('#edit_order_payment_method').html(response.order.modplata);
						$('#ramburs').val(response.order.ramburs);
						$('#adresa').val(response.order.adresa);
						$('#codpostal').val(response.order.codpostal);
						$('#localitate').val(response.order.localitate);
						$('#judet').val(response.order.judet);
						$('#perscontact').val(response.order.perscontact);
						$('#telpers').val(response.order.telpers);
						$('#emailpers').val(response.order.emailpers);
						if(response.order.sambata == '1' || response.order.sambata == 'da'){
							$('#sambata').prop("checked", true);
						}
						$('#ship_instructions').val(response.order.ship_instructions);
						$('#altele').val(response.order.altele);
						var table_html = '';
						response.order.products.forEach(function(element){
							table_html +=
								"<tr>" +
								"<td>" + element.descriere + "</td>" +
								"<td><input type='number' class='form-control form-control-sm' name='qty["+ element.idp + "]' value='" + element.volum + "' /></td>" +
								"<td>" + element.idp + "</td>" +
								"<td>" + element.stock + "</td>" +
								"<td>" + element.codprodusclient + "</td>" +
								"<td>" + element.codbare + "</td>" +
								"<td><button class='btn btn-danger btn-xs' type='submit' name='removeProduct' value='" + element.idp + "'><i class='fa fa-trash'></i> {{__('main.remove')}} </button></td>" +
								"</tr>";
						});
						$('#edit_order_products').html(table_html);
					}
				});
			}

            @if(session('edited_order_idcomanda'))
			    $('.editOrder[data-idcomanda="{{session('edited_order_idcomanda')}}"]').trigger( "click" );
			    editOrderModal({{session('edited_order_idcomanda')}});
            @endif

			$(document).on('click', '.editOrder' , function () {
				var idcomanda = $(this).attr('data-idcomanda');
				$('#editOverlay').removeClass('d-none');
				editOrderModal(idcomanda);
			});
            $('.openOrder').on('click', function () {
				$('#viewOverlay').removeClass('d-none');
            	var idcomanda = $(this).attr('data-idcomanda');
				$.ajax({
					url: 'order/'+idcomanda,
					type:"GET",
					success:function(response){
						$('#viewOverlay').addClass('d-none');
						$('#order_number').html(response.idextern);
						$('#order_in_date').html(response.data1);
						$('#order_payment_method').html(response.modplata);
						$('#order_total').html(response.ramburs);
						$('#order_address').html(response.adresa);
						$('#order_city').html(response.localitate);
						$('#order_post_code').html(response.codpostal);
						$('#order_country').html(response.tara);
						$('#order_to').html(response.perscontact);
						$('#order_phone').html(response.telpers);
						$('#order_email').html(response.emailpers);
						$('#order_other_info').html(response.altele);
						$('#order_ship_instructions').html(response.ship_instructions);
						$('#order_shipping_method').html(response.shipping_method);
						if(response.returned === true){
                            $('#row_returned, .returned').removeClass('d-none');
                            $('#order_returned').html('{{__('main.yes')}}')
                        }else{
							$('#row_returned, .returned').addClass('d-none');
                        }
						var table_html = '';
						response.products.forEach(function(element){
							var returned = "";
							var return_reason = "";
							var return_part = '';
							if(element.is_returned === true){
								returned = "{{__('main.yes')}}";
								return_reason = element.return_reason == null ? '' : element.return_reason;
								return_part =
								"<td>" + returned + "</td>" +
								"<td>" + return_reason + "</td>";
                            }
							table_html +=
                                "<tr>" +
                                    "<td>" + element.descriere + "</td>" +
                                    "<td>" + element.volum + "</td>" +
                                    "<td>" + element.idp + "</td>" +
								    "<td>" + element.stock + "</td>" +
                                    "<td>" + element.codprodusclient + "</td>" +
                                    "<td>" + element.codbare + "</td>" +
								    return_part +
                                "</tr>";
                        });
						$('#order_products').html(table_html);
					}
				});
			})
		});
    </script>
@stop
