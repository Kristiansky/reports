@extends('adminlte::page')

@section('title', __('main.dashboard'))

@section('content_header')
    <h1>{{ __('main.dashboard') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-6">
            <form class="" action="{{route('home')}}" id="daterange_picker">
                @csrf
                @method('POST')
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="font-weight-light">
                                {{__('main.date_range')}}:
                                <span id="reportrange" class="font-weight-bold">{{session('dashboard_filter')['range_start']}} - {{session('dashboard_filter')['range_end']}}</span>
                            </label>
                            <div class="input-group">
                                <input class="form-control" type="hidden" name="range_start" id="range_start" value="{{session('dashboard_filter')['range_start']}}">
                                <input class="form-control" type="hidden" name="range_end" id="range_end" value="{{session('dashboard_filter')['range_end']}}">
                                <button type="button" class="btn btn-primary btn-block float-right" id="daterange-btn">
                                    <i class="far fa-calendar-alt"></i> {{__('main.choose_range')}}
                                    <i class="fas fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group d-none">
                            <div class="input-group">
                                <button type="submit" name="filter" value="1" class="btn btn-primary mr-2">{{__('main.filter')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> {{__('main.orders_data')}}
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="height: 350px">
                    <canvas id="sent_count"></canvas>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <div class="@if(isset($countries) || isset($couriers)) col-md-3 @else col-md-6 @endif">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-box-open"></i> {{__('main.top_selling_product')}}</h3>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" id="top_products_list">
                    @foreach($top_products as $product)
                        <p>{{$product->product_name}} <strong>{{$product->sold_products}}</strong></p>
                    @endforeach
                </div>
                <!-- /.card-body -->
                <div class="card-footer text-center">
                    <button class="btn btn-sm btn-success" id="load_more_top_selling">{{__('main.load_more')}} <i class="fas fa-arrow-circle-down"></i></button>
                </div>
            </div>
            <!-- /.card -->
        </div>
        @if(isset($countries) || isset($couriers))
            <div class="col-md-3">
                @if(isset($countries))
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i> {{__('main.country_data')}}
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body" style="height: 220px">
                            <canvas id="country_data"></canvas>
                        </div>
                        <!-- /.card-body -->
                    </div>
                @endif
                @if(isset($couriers))
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-dolly"></i> {{__('main.courier_data')}}
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body" style="height: 220px">
                            <canvas id="courier_data"></canvas>
                        </div>
                        <!-- /.card-body -->
                    </div>
                @endif
            </div>
        @endif
        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-6 col-6">
                    <!-- small card -->
                    <div class="small-box bg-success">
                        <div class="inner py-3">
                            <h3>{{$orders_sent_count}}</h3>
                            <p>{{__('main.sent_orders')}}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <a href="{{route('order.index')}}" class="small-box-footer">
                            {{__('main.more')}} <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 col-6">
                    <!-- small card -->
                    <div class="small-box bg-warning">
                        <div class="inner py-3">
                            <h3>{{$orders_returned_count}}</h3>
                            <p>{{__('main.returned_orders')}}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <a href="{{route('order.index')}}" class="small-box-footer">
                            {{__('main.more')}} <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building"></i> {{__('main.city_data')}}
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body" style="height: 330px">
                            <canvas id="city_data"></canvas>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">
@stop

@section('plugins.Moment', true)
@section('plugins.Daterangepicker', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Chartjs', true)

@section('js')
    <script>
		$(document).ready(function () {
			var ctx = document.getElementById("sent_count").getContext('2d');
			var sent_count = new Chart(ctx, {
				type: 'bar',
				options: {
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero:true
							}
						}]
					}
				}
			});
			var line_chart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: [
                        @foreach($dates as $key => $date)
							"{{$key}}",
                        @endforeach
					],
					datasets: [{
						label: '{{__('main.sent_orders')}}',
						data: [
                            @foreach($dates as $key => $date)
                            {{$date['sent_count']}},
                            @endforeach
						],
						backgroundColor: [
							'rgba(40, 167, 69, 0.2)',
						],
						borderColor: [
							'rgba(40, 167, 69, 1)',
						],
						borderWidth: 1
					},{
						label: '{{__('main.returned_orders')}}',
						data: [
                            @foreach($dates as $key => $date)
                            {{$date['returned_count']}},
                            @endforeach
						],
						backgroundColor: [
							'rgba(255, 193, 7, 0.2)',
						],
						borderColor: [
							'rgba(255, 193, 7,1)',
						],
						borderWidth: 1
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero:true
							}
						}]
					},
					tooltips: {
						mode: 'nearest',
						intersect: false,
					},
				}
			});
			var ctx = document.getElementById('city_data').getContext('2d');
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'bar',
				// The data for our dataset
				data: {
					labels:
						[
                            @foreach($city_data as $city_datum)
								"{{$city_datum->localitate}}",
                            @endforeach
						],
					datasets: [{
						label: '{{__('main.sent_by_city')}}',
						backgroundColor: 'rgba(40, 167, 69, 0.2)',
						borderColor:  'rgba(40, 167, 69,1)',
						borderWidth: 1,
						data: [
                            @foreach($city_data as $city_datum)
								"{{$city_datum->total_sent_orders}}",
                            @endforeach
						],
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero:true
							}
						}]
					},
					tooltips: {
						mode: 'nearest',
						intersect: false,
					},
				}
			});
            @if(isset($countries))
                var ctx = document.getElementById('country_data').getContext('2d');
                var country_pie_chart = new Chart(ctx,{
                    type: 'pie',
                    data: {
                        labels: [
                        	@foreach($countries as $country)
                            '{{$country->country}}',
                            @endforeach
                        ],
                        datasets: [
                            {
                                data: [
                                    @foreach($countries as $country)
										'{{$country->orders_count}}',
                                    @endforeach
                                ],
                                backgroundColor : ['#00a65a', '#f39c12', '#00c0ef', '#f56954', '#3c8dbc', '#d2d6de'],
                            }
                        ]
                    },
                    options: {
                        responsive: true,
						maintainAspectRatio: false,
                    }
                });
            @endif
            @if(isset($couriers))
                var ctx = document.getElementById('courier_data').getContext('2d');
                var courier_pie_chart = new Chart(ctx,{
                    type: 'pie',
                    data: {
                        labels: [
                        	@foreach($couriers as $courier)
                            '{{$courier->courier}}',
                            @endforeach
                        ],
                        datasets: [
                            {
                                data: [
                                    @foreach($couriers as $courier)
										'{{$courier->orders_count}}',
                                    @endforeach
                                ],
                                backgroundColor : ['#3c8dbc', '#00a65a', '#f56954', '#f39c12', '#00c0ef'],
                            }
                        ]
                    },
                    options: {
                        responsive: true,
						maintainAspectRatio: false,
                    }
                });
            @endif
			moment.locale('bg');
			$('#daterange-btn').daterangepicker(
				{
					locale: {
						"format": "YYYY-MM-DD",
						"separator": " - ",
						"applyLabel": "{{__('main.choose')}}",
						"cancelLabel": "{{__('main.cancel')}}",
						"fromLabel": "{{__('main.From')}}",
						"toLabel": "{{__('main.To')}}",
						"customRangeLabel": "{{__('main.choose_range')}}",
						"firstDay": 1
					},
					ranges   : {
						// 'Today'       : [moment(), moment()],
						// 'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'{{__('main.last_days', ['days' => 7])}}' : [moment().subtract(6, 'days'), moment()],
						'{{__('main.last_days', ['days' => 30])}}': [moment().subtract(30, 'days'), moment()],
						'{{__('main.this_month')}}'  : [moment().startOf('month'), moment().endOf('month')],
						'{{__('main.this_year')}}'  : [moment().startOf('year'), moment()]
					},
					startDate: '{{session('dashboard_filter')['range_start']}}',
					endDate  : '{{session('dashboard_filter')['range_end']}}',
				},
				function (start, end) {
					$('#range_start').val(start.format("YYYY-MM-DD"));
					$('#range_end').val(end.format("YYYY-MM-DD"));
					$('span#reportrange').html(start.format("YYYY-MM-DD") + ' - ' + end.format("YYYY-MM-DD"));
					$('#daterange_picker [type="submit"]').trigger('click');
				}
			);
			var offset = 10;
			$('#load_more_top_selling').on('click', function () {
				$.ajax({
					url: 'get_top_products',
                    type:"POST",
                    data:{
                        'offset': offset,
                        '_token': '{{ csrf_token() }}'
                    },
					success:function(response){
						var list_html = '';
						response.forEach(function(element){
							list_html +='' +
                                '<p>' +
                                    element.product_name +
                                    '<strong>' +
                                        element.sold_products +
                                    '</strong>' +
                                '</p>';
                        });
                        $('#top_products_list').append(
							list_html
                        );
						offset = offset + 10;
					}
				});
			});
		});
    </script>
@stop
@section('plugins.Datatables', true)
