@extends('adminlte::page')

@section('title', __('main.stock'))

@section('content_header')
    <h1>{{ __('main.stock') }}</h1>
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
                    <form class="" action="{{route('product.index')}}" method="post">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="search">{{__('main.search')}}</label>
                                    <input type="text" class="form-control form-control-sm" id="search" name="search" autocomplete="off" placeholder="{{__('main.search')}}" value="{{isset(session('product_filter')['search']) ? session('product_filter')['search'] : ''}}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="entry_from_date">
                                        {{__('main.entry_from_date')}}:
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="entry_from_date" id="entry_from_date" autocomplete="off" placeholder="{{__('main.entry_from_date')}}" value="{{isset(session('product_filter')['entry_from_date']) ? session('product_filter')['entry_from_date'] : ''}}">
                                        <div class="input-group-append">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="entry_to_date">
                                        {{__('main.entry_to_date')}}:
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="entry_to_date" id="entry_to_date" autocomplete="off" placeholder="{{__('main.entry_to_date')}}" value="{{isset(session('product_filter')['entry_to_date']) ? session('product_filter')['entry_to_date'] : ''}}">
                                        <div class="input-group-append">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="expiration_date">
                                        {{__('main.expiration_date')}}:
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="expiration_date" id="expiration_date" autocomplete="off" placeholder="{{__('main.expiration_date')}}" value="{{isset(session('product_filter')['expiration_date']) ? session('product_filter')['expiration_date'] : ''}}">
                                        <div class="input-group-append">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 pt-4 mt-2">
                                <button type="submit" name="filter" value="1" class="btn btn-primary btn-sm mr-2">{{__('main.filter')}}</button>
                                <button type="submit" name="reset" value="1" class="btn btn-default btn-sm mr-2">{{__('main.reset')}}</button>
                                <button type="submit" name="export" value="1" class="btn btn-warning btn-sm">{{__('main.export_xlsx')}}</button>
                            </div>
                        </div>
                        {{--<div class="row">
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="without_stock" name="without_stock" value="1" {{isset(session('product_filter')['without_stock']) && session('product_filter')['without_stock'] == 1 ? 'checked' : ''}}>
                                    <label class="form-check-label" for="without_stock">{{__('main.all_products_without_stock_also')}}</label>
                                </div>
                            </div>
                        </div>--}}
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
                            {{__('main.total_records')}}: {{ $products->total() }}
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex mt-3">
                                <div class="mx-auto">
                                    {{ $products->links() }}
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
                    <table class="table table-sm table-bordered table-striped table-hover mb-3 text-sm">
                        <thead>
                        <tr>
                            <th width="7%">{{__('main.product_id')}}</th>
                            <th width="10%">{{__('main.sku')}}</th>
                            <th width="30%">{{__('main.name')}}</th>
                            <th width="5%">{{__('main.stock')}}</th>
                            <th width="5%">{{__('main.incl_new')}}</th>
                            <th width="13%">{{__('main.lots')}}</th>
                            <th width="10%">{{__('main.lot_expiration')}}</th>
                            <th width="5%">{{__('main.damaged')}}</th>
                            <th width="15%">{{__('main.actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{$product->idp}}</td>
                                <td>{{$product->codprodusclient}}</td>
                                <td>{{$product->descriere}}</td>
{{--                                <td>{{$product->current_total_expediat}}</td>--}}
                                <td>{{$product->stock()}}</td>
{{--                                <td>{{$product->current_total}}</td>--}}
                                <td>{{$product->stockInclNew()}}</td>
                                <td>
                                    @if($product->lots())
                                        @foreach($product->lots() as $lot)
                                            @if(session('product_filter')['expiration_date'] && $lot['dataexp'] <= session('product_filter')['expiration_date'])
                                                {{$lot['number_of_items']}} {{__('main.items_in')}} {{$lot['lotul']}}<br/>
                                            @elseif(!session('product_filter')['expiration_date'])
                                                {{$lot['number_of_items']}} {{__('main.items_in')}} {{$lot['lotul']}}<br/>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if($product->lots())
                                        @foreach($product->lots() as $lot)
                                            @if(session('product_filter')['expiration_date'] && $lot['dataexp'] <= session('product_filter')['expiration_date'])
                                                {{$lot['dataexp']}}<br/>
                                            @elseif(!session('product_filter')['expiration_date'])
                                                {{$lot['dataexp']}}<br/>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>@if($product->damaged()){{$product->damaged()->total}}@endif</td>
                                <td>
                                    <button data-idp="{{$product->idp}}" type="button" class="btn btn-xs btn-success openHistory" data-toggle="modal" data-target="#historyModal"><i class="fa fa-history"></i> {{__('main.history')}}</button>
                                    @if(auth()->user()->group->id == '2')
                                        <button data-idp="{{$product->idp}}" type="button" class="btn btn-xs btn-primary editProduct" data-toggle="modal" data-target="#editProductModal"><i class="fa fa-pen"></i> {{__('main.edit')}}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-lg-4 pl-4 pt-4">
                            {{--{{__('main.showing_records', ['first_index' => $paginator->items()['from'], 'last_index' =>  $paginator->items()['to'], 'total_count' =>  $paginator->items()['total'] ])}}--}}
                            {{__('main.total_records')}}: {{ $products->total() }}
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex mt-3">
                                <div class="mx-auto">
                                    {{ $products->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="historyModal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('main.history')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered table-hover">
                        <tbody>
                        <tr>
                            <th width="25%">{{__('main.product_id')}}</th>
                            <td width="75%"><span id="product_id"></span></td>
                        </tr>
                        <tr>
                            <th>{{__('main.sku')}}</th>
                            <td><span id="product_sku"></span></td>
                        </tr>
                        <tr>
                            <th>{{__('main.name')}}</th>
                            <td><span id="product_name"></span></td>
                        </tr>
                        <tr>
                            <th>{{__('main.create_date')}}</th>
                            <td><span id="product_create_date"></span></td>
                        </tr>
                        <tr>
                            <th>{{__('main.stock')}}</th>
                            <td><span id="product_stock"></span></td>
                        </tr>
                        <tr>
                            <th>{{__('main.lots')}}</th>
                            <td><span id="product_lots"></span></td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered table-hover" id="product_entries">
                                <thead>
                                <tr>
                                    <th colspan="6"><h4 class="text-center">{{__('main.entries')}}</h4></th>
                                </tr>
                                <tr>
                                    <th>{{__("main.entry_id")}}</th>
                                    <th>{{__("main.qty")}}</th>
                                    <th>{{__("main.date_entered")}}</th>
                                    <th>{{__("main.document")}}</th>
                                    <th>{{__("main.expiration_date")}}</th>
                                    <th>{{__("main.batch_number")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>{{__('main.total')}}</th>
                                    <th id="product_entries_total"></th>
                                    <th colspan="4"></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered table-hover" id="product_sales">
                                <thead>
                                <tr>
                                    <th colspan="8"><h4 class="text-center">{{__('main.sales')}}</h4></th>
                                </tr>
                                <tr>
                                    <th>{{__("main.order_id")}}</th>
                                    <th>{{__("main.external_id")}}</th>
                                    <th>{{__("main.to")}}</th>
                                    <th>{{__("main.qty")}}</th>
                                    <th>{{__("main.date")}}</th>
                                    <th>{{__("main.status")}}</th>
                                    <th>{{__("main.batch_number")}}</th>
                                    <th>{{__("main.expiration_date")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>{{__('main.total')}}</th>
                                    <th id="product_sales_total"></th>
                                    <th colspan="6"></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{__('main.close')}}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <div class="modal fade" id="editProductModal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('main.edit')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('PATCH')
                        <table class="table table-sm table-bordered table-hover">
                            <tr>
                                <th width="25%">{{__('main.name')}}</th>
                                <td width="75%">
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control form-control-sm" name="descriere" id="descriere" autocomplete="off" placeholder="{{__('main.name')}}" value="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>{{__('main.sku')}}</th>
                                <td>
                                    <div class="form-group m-0">
                                        <input type="text" class="form-control form-control-sm" name="codprodusclient" id="codprodusclient" autocomplete="off" placeholder="{{__('main.sku')}}" value="">
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">{{__('main.submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{__('main.close')}}</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
@stop

@section('css')
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">
@stop

@section('plugins.Moment', true)
@section('plugins.Datetimepicker', true)
@section('plugins.Sweetalert2', true)

@section('js')
    <script>
		$(document).ready(function () {
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
			$('#entry_from_date, #entry_to_date, #expiration_date').datetimepicker({
				format: 'YYYY-MM-DD',
				icons:
					{
						previous: 'fas fa-angle-left',
						next: 'fas fa-angle-right',
						up: 'fas fa-angle-up',
						down: 'fas fa-angle-down'
					}
			});

			$('.openHistory').on('click', function () {
				var idp = $(this).attr('data-idp');
				$.ajax({
					url: 'product/'+idp,
					type:"GET",
					success:function(response){
						$('#product_id').html(response.product.idp);
						$('#product_sku').html(response.product.codprodusclient);
						$('#product_name').html(response.product.descriere);
						$('#product_create_date').html(response.product.data);
						$('#product_stock').html(response.stock + ' / ' + response.stock_incl_new);
						if(response.lots != false){
							var html = '';
							response.lots.forEach(function(element){
								html += element.number_of_items + " {{__('main.items_in')}} " + element.lotul + " {{__('main.expire_on')}} " + element.dataexp + "<br/>";
							});
							$('#product_lots').html(html);
                        }
						var html = '';
						var product_entries_total = 0;
						response.entries.forEach(function (product) {
							if (product.idreceptie == null || product.idreceptie == undefined || product.idreceptie == '') {
								product.idreceptie = '';
                            }
							product_entries_total = parseInt(product_entries_total) + parseInt(product.bucati);
							html += "<tr>" +
                                "<td>" + product.idin + "</td>" +
								"<td>" + product.bucati + "</td>" +
                                "<td>" + product.dataintrare + "</td>" +
                                "<td>" + product.aviz + "</td>" +
                                "<td>" + product.data_expirare + "</td>" +
                                "<td>" + product.idreceptie + "</td>" +
                                "</tr>";
						});
						$('#product_entries_total').html(product_entries_total);
						$('#product_entries tbody').html(html);

						var html = '';
						var product_sales_total = 0;
						response.orders.forEach(function (order) {
							product_sales_total = parseInt(product_sales_total) + parseInt(order.volum);
							html += "<tr>" +
								"<td>" + order.idie + "</td>" +
								"<td>" + order.idextern + "</td>" +
								"<td>" + order.perscontact + "</td>" +
								"<td>" + order.volum + "</td>" +
								"<td>" + order.datai + "</td>" +
								"<td>" + order.status + "</td>" +
								"<td>" + order.expiration_batch + "</td>" +
								"<td>" + order.expiration_date + "</td>" +
								"</tr>";
						});
						$('#product_sales_total').html(product_sales_total);
						$('#product_sales tbody').html(html);
					}
				});
			});

			$(document).on('click', '.editProduct' , function () {
				var idp = $(this).attr('data-idp');
				var url = '{{ route("product.update", ":id") }}';
				url = url.replace(':id', idp);
				$('#editProductModal form').attr('action', url);

				$.ajax({
					url: 'product/'+idp,
					type:"GET",
					success:function(response){
						$('#descriere').val(response.product.descriere);
						$('#codprodusclient').val(response.product.codprodusclient);
					}
				});
			});
		});
    </script>
@stop
