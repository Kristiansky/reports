
@extends('adminlte::page')

@section('title', __('main.place_order'))

@section('content_header')
    <h1>{{ __('main.place_order') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 col-lg-5">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="order-tabs-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="order-tabs-normal-tab" data-toggle="pill" href="#order-tabs-normal" role="tab" aria-controls="order-tabs-normal" aria-selected="true">{{__('main.normal')}}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="order-tabs-xlsx-tab" data-toggle="pill" href="#order-tabs-xlsx" role="tab" aria-controls="order-tabs-xlsx" aria-selected="false">{{__('main.upload_file')}}</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="order-tabs-tabContent">
                        <div class="tab-pane fade active show" id="order-tabs-normal" role="tabpanel" aria-labelledby="order-tabs-normal">
                            <form method="post" action="{{route('order.create')}}">
                                @csrf
                                @method('POST')
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="product"><code>*</code>{{__('main.choose_product')}}</label>
                                            <select class="form-control form-control-sm select2 @error('product') is-invalid @enderror" id="product" name="product" required>
                                                <option value="">{{ __('main.choose') }}</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->idp }}">{{$product->codprodusclient}}: {{$product->descriere}}</option>
                                                @endforeach
                                            </select>
                                            @error('product')
                                            <div class="invalid-feedback">{{$message}}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="qty">
                                                <code>*</code>
                                                {{__('main.qty')}} |
                                                {{__('main.stock_of')}}
                                                <span id="text_stock_of_idp"></span>:
                                                <span id="text_stock_of_qty"></span>
                                            </label>
                                            <input id="qty" type="number" name="qty" class="form-control form-control-sm @error('qty') is-invalid @enderror" placeholder="{{__('main.qty')}}" min="1" max="1" required autocomplete="off">
                                            @error('qty')
                                            <div class="invalid-feedback">{{$message}}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="addProduct" value="1" class="btn btn-primary">{{__('main.add')}}</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="order-tabs-xlsx" role="tabpanel" aria-labelledby="order-tabs-xlsx">
                            <div class="alert alert-warning alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-exclamation-triangle"></i> {{__('main.alert')}}</h5>
                                {{__('main.alert_about_order_file')}}
                            </div>
                            <form action="{{route('order.create')}}" method="post" enctype="multipart/form-data" >
                                @csrf
                                @method('POST')
                                <div class="form-group">
                                    <label for="xlsx_file">{{__('main.choose_file')}}</label>
                                    <input type="file" class="form-control-file form-control-sm @error('xlsx_file') is-invalid @enderror" id="xlsx_file" name="xlsx_file" required>
                                    @error('xlsx_file')
                                    <div class="invalid-feedback">{{$message}}</div>
                                    @enderror
                                </div>
                                <button type="submit" name="addXlsx" value="1" class="btn btn-primary">{{__('main.submit')}}</button>
                                <a href="{{ asset('xlsx/example_order.xlsx') }}" class="btn btn-success float-right"><i class="fa fa-download"></i> {{__('main.download_template')}}</a>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
        <div class="col-12 col-lg-7">
            @if(!empty($cart_products))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-shopping-basket"></i> {{__('main.order_products')}}</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>{{__('main.product_name')}}</th>
                                <th>{{__('main.qty')}}</th>
                                <th>{{__('main.actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($cart_products as $cart_product)
                                    <tr>
                                        <td>{{$cart_product['product']->idp}}</td>
                                        <td>{{$cart_product['product']->codprodusclient}}</td>
                                        <td>{{$cart_product['product']->descriere}}</td>
                                        <td>{{$cart_product['qty']}}</td>
                                        <td>
                                            <form method="post" action="{{route('order.create')}}">
                                                @csrf
                                                <button type="submit" name="removeCartProduct" value="{{$cart_product['product']->idp}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> {{__('main.remove')}}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            @endif
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            @if(!empty($cart_products))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{__('main.order_info')}}</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post" action="{{route('order.store')}}">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-xl-3">
                                    <div class="form-group">
                                        <label for="data1">
                                            <code>*</code>
                                            {{__('main.order_date')}}:
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm @error('data1') is-invalid @enderror" name="data1" id="data1" required autocomplete="off" placeholder="{{__('main.order_date')}}">
                                            <div class="input-group-append">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                        @error('data1')
                                        <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <div class="form-group">
                                        <label for="data2">
                                            <code>*</code>
                                            {{__('main.order_process_date')}}:
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm @error('data2') is-invalid @enderror" name="data2" id="data2" required autocomplete="off" placeholder="{{__('main.order_process_date')}}">
                                            <div class="input-group-append">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                        @error('data2')
                                        <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-xl-3">
                                    <div class="form-group">
                                        <label for="locatie">
                                            {{__('main.order_delivery_extra_info')}}:
                                        </label>
                                        <input id="locatie" type="text" name="locatie" class="form-control form-control-sm" placeholder="{{__('main.order_extra_info')}}" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3 col-xl-1">
                                    <label>
                                        {{__('main.address')}}</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3 col-xl-1">
                                    <div class="form-group">
                                        <select name="tstr" id="tstr" class="form-control form-control-sm">
                                            <option value="">{{ __('main.choose') }}</option>
                                            @foreach($stroptions as $option)
                                                <option value="{{$option}}">{{$option}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <div class="form-group">
                                        <input id="str" type="text" name="str" class="form-control form-control-sm" placeholder="{{__('main.street')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-1">
                                    <div class="form-group">
                                        <input id="nr" type="text" name="nr" class="form-control form-control-sm" placeholder="{{__('main.number')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-1">
                                    <div class="form-group">
                                        <input id="bl" type="text" name="bl" class="form-control form-control-sm" placeholder="{{__('main.block')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-1">
                                    <div class="form-group">
                                        <input id="sc" type="text" name="sc" class="form-control form-control-sm" placeholder="{{__('main.entrance')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-1">
                                    <div class="form-group">
                                        <input id="et" type="text" name="et" class="form-control form-control-sm" placeholder="{{__('main.floor')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <div class="form-group">
                                        <input id="ap" type="text" name="ap" class="form-control form-control-sm" placeholder="{{__('main.apartment')}}" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label for="localitate">
                                        {{__('main.city')}}
                                    </label>
                                    <div class="form-group">
                                        <input id="localitate" type="text" name="localitate" class="form-control form-control-sm" placeholder="{{__('main.city')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <div class="form-group">
                                        <label for="judet">{{__('main.choose_district')}}</label>
                                        <select name="judet" id="judet" class="form-control form-control-sm">
                                            <option value="">{{ __('main.choose') }}</option>
                                            <option value="Sofia-grad">Sofia-grad</option>
                                            <option value="Sofia oblast">Sofia oblast</option>
                                            <option value="Blagoevgrad">Blagoevgrad</option>
                                            <option value="Burgas">Burgas</option>
                                            <option value="Varna">Varna</option>
                                            <option value="Veliko Turnovo">Veliko Turnovo</option>
                                            <option value="Vidin">Vidin</option>
                                            <option value="Vratsa">Vratsa</option>
                                            <option value="Gabrovo">Gabrovo</option>
                                            <option value="Dobrich">Dobrich</option>
                                            <option value="Kardzhali">Kardzhali</option>
                                            <option value="Kyustendil">Kyustendil</option>
                                            <option value="Lovech">Lovech</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Pazadzhik">Pazadzhik</option>
                                            <option value="Pernik">Pernik</option>
                                            <option value="Pleven">Pleven</option>
                                            <option value="Plovdiv">Plovdiv</option>
                                            <option value="Razgrad">Razgrad</option>
                                            <option value="Ruse">Ruse</option>
                                            <option value="Silistra">Silistra</option>
                                            <option value="Sliven">Sliven</option>
                                            <option value="Smolian">Smolian</option>
                                            <option value="Stara Zagora">Stara Zagora</option>
                                            <option value="Targovishte">Targovishte</option>
                                            <option value="Haskovo">Haskovo</option>
                                            <option value="Shumen">Shumen</option>
                                            <option value="Yambol">Yambol</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-1">
                                    <label for="codpostal">
                                        {{__('main.post_code')}}
                                    </label>
                                    <div class="form-group">
                                        <input id="codpostal" type="text" name="codpostal" class="form-control form-control-sm" placeholder="{{__('main.post_code')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label for="perscontact">
                                        {{__('main.person_name')}}
                                    </label>
                                    <div class="form-group">
                                        <input id="perscontact" type="text" name="perscontact" class="form-control form-control-sm" placeholder="{{__('main.person_name')}}" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label for="telpers">
                                        {{__('main.phone')}}
                                    </label>
                                    <div class="form-group">
                                        <input id="telpers" type="text" name="telpers" class="form-control form-control-sm" placeholder="{{__('main.phone')}}" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label for="ramburs">
                                        {{__('main.COD')}}
                                    </label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                  <i class="fas fa-dollar-sign"></i>
                                                </span>
                                            </div>
                                            <input id="ramburs" type="text" name="ramburs" class="form-control form-control-sm @error('ramburs') is-invalid @enderror" placeholder="{{__('main.COD')}}" autocomplete="off">
                                        </div>
                                        @error('ramburs')
                                        <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label for="rambursalttip">
                                        {{__('main.COD_Documents')}}
                                    </label>
                                    <div class="form-group">
                                        <input id="rambursalttip" type="text" name="rambursalttip" class="form-control form-control-sm" placeholder="{{__('main.COD_Documents')}}" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label for="curier">
                                        <code>*</code>
                                        {{__('main.courier')}}
                                    </label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                  <i class="fas fa-truck"></i>
                                                </span>
                                            </div>
                                            <select name="curier" id="curier" class="form-control form-control-sm @error('curier') is-invalid @enderror" required>
                                                <option value="">{{ __('main.choose') }}</option>
                                                <option value="office">Pick from office</option>
                                                <option value="speedy">Speedy</option>
                                                <option value="econt">Econt</option>
                                                <option value="transpress">Transpress</option>
                                                <option value="inout">Inout</option>
                                                <option value="postone">Postone</option>
                                                <option value="dhl">DHL</option>
                                            </select>
                                        </div>
                                        @error('curier')
                                        <div class="invalid-feedback">{{$message}}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2 pt-4 mt-2">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" id="sambata" value="1" name="sambata">
                                            <label for="sambata" class="custom-control-label">{{ __('main.saturday_delivery') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-xl-5">
                                    <div class="form-group">
                                        <label for="altele">
                                            {{__('main.other_info')}}
                                        </label>
                                        <div class="form-group">
                                            <input id="altele" type="text" name="altele" class="form-control form-control-sm" placeholder="{{__('main.other_info')}}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                            </div>
                            <button type="submit" name="completeOrder" value="1" class="btn btn-success">{{__('main.submit')}}</button>
                        </form>
                    </div>
                    <!-- /.card-body -->
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">
@stop

@section('plugins.Select2', true)
@section('plugins.Moment', true)
@section('plugins.Datetimepicker', true)
@section('plugins.Sweetalert2', true)

@section('js')
    <script>
		$(document).ready(function () {
			$('.select2').select2();
			var volumes = {
                @foreach($products as $product)
				    {{$product->idp}}: {{$product->stock()}},
                @endforeach
			};
            $('select#product').on('change', function () {
                var idp = $(this).val();
                $('#text_stock_of_idp').html(idp);
                $('#text_stock_of_qty').html(volumes[idp]);
                $('input#qty').attr('max', volumes[idp]);
			});
            $('#data1, #data2').datetimepicker({
                format: 'YYYY-MM-DD',
				icons:
					{
						previous: 'fas fa-angle-left',
						next: 'fas fa-angle-right',
						up: 'fas fa-angle-up',
						down: 'fas fa-angle-down'
					}
			})
		});
        @if(session('message'))
		    var Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000
			});

            Toast.fire({
                icon: "{{session('message_type')}}",
                title: "{{session('message')}}"
            })
        @endif
    </script>
@stop

