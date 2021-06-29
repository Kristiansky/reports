@extends('adminlte::page')

@section('title', __('main.entries'))

@section('content_header')
    <h1>{{ __('main.entries') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">{{__('main.filters')}}</h3>
                </div>
                <div class="card-body">
                    <form class="" action="{{route('entries.index')}}" method="post">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-4 col-lg-2">
                                <div class="form-group">
                                    <label for="search">{{__('main.search')}}</label>
                                    <input type="text" class="form-control form-control-sm" id="search" name="search" autocomplete="off" placeholder="{{__('main.search')}}" value="{{isset(session('entries_filter')['search']) ? session('entries_filter')['search'] : ''}}">
                                </div>
                            </div>
                            <div class="col-4 col-lg-2">
                                <div class="form-group">
                                    <label for="entry_from_date">
                                        {{__('main.entry_from_date')}}:
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="entry_from_date" id="entry_from_date" autocomplete="off" placeholder="{{__('main.entry_from_date')}}" value="{{isset(session('entries_filter')['entry_from_date']) ? session('entries_filter')['entry_from_date'] : ''}}">
                                        <div class="input-group-append">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 col-lg-2">
                                <div class="form-group">
                                    <label for="entry_to_date">
                                        {{__('main.entry_to_date')}}:
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="entry_to_date" id="entry_to_date" autocomplete="off" placeholder="{{__('main.entry_to_date')}}" value="{{isset(session('entries_filter')['entry_to_date']) ? session('entries_filter')['entry_to_date'] : ''}}">
                                        <div class="input-group-append">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3 pt-0 pt-lg-4 mt-lg-2">
                                <button type="submit" name="filter" value="1" class="btn btn-primary btn-sm mr-2">{{__('main.filter')}}</button>
                                <button type="submit" name="reset" value="1" class="btn btn-default btn-sm mr-2">{{__('main.reset')}}</button>
                                <button type="submit" name="export" value="1" class="btn btn-warning btn-sm">{{__('main.export_xlsx')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
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
                            {{__('main.total_records')}}: {{ $entries->total() }}
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex mt-3">
                                <div class="mx-auto">
                                    {{ $entries->links() }}
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
                    <table class="table table-sm table-bordered table-striped table-hover mb-3 text-sm table-responsive">
                        <thead>
                        <tr>
                            @php
                                if(session('entries_sort_direction') && session('entries_sort_direction') == 'desc'){
                                    $direction = 'asc';
                                    $icon = '<i class="fas fa-arrow-alt-circle-down"></i>';
                                }elseif(session('entries_sort_direction') && session('entries_sort_direction') == 'asc'){
                                    $direction = 'desc';
                                    $icon = '<i class="fas fa-arrow-alt-circle-up"></i>';
                                }else{
                                    $direction = 'desc';
                                    $icon = '<i class="fas fa-arrow-alt-circle-up"></i>';
                                }
                            @endphp

                            <th width="15%">
                                <a href="?sort=idp&direction={{$direction}}">
                                    @if(session('entries_sort') && session('entries_sort') == 'idp'){!!$icon!!}@endif {{__('main.product_id')}}
                                </a>
                            </th>
                            <th width="10%">
                                <a href="?sort=codprodusclient&direction={{$direction}}">
                                    @if(session('entries_sort') && session('entries_sort') == 'codprodusclient'){!!$icon!!}@endif {{__('main.sku')}}
                                </a>
                            </th>
                            <th width="50%">
                                <a href="?sort=descriere&direction={{$direction}}">
                                    @if(session('entries_sort') && session('entries_sort') == 'descriere'){!!$icon!!}@endif {{__('main.name')}}
                                </a>
                            </th>
                            <th width="10%">
                                <a href="?sort=bucati&direction={{$direction}}">
                                    @if(session('entries_sort') && session('entries_sort') == 'bucati'){!!$icon!!}@endif {{__('main.qty')}}
                                </a>
                            </th>
                            <th width="15%">
                                <a href="?sort=dataintrare&direction={{$direction}}">
                                    @if(session('entries_sort') && session('entries_sort') == 'dataintrare'){!!$icon!!}@endif {{__('main.entry_date')}}
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td>{{$entry->idp}}</td>
                                <td>{{$entry->codprodusclient}}</td>
                                <td>{{$entry->descriere}}</td>
                                <td>{{$entry->bucati}}</td>
                                <td>{{$entry->dataintrare}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-lg-4 pl-4 pt-4">
                            {{--{{__('main.showing_records', ['first_index' => $paginator->items()['from'], 'last_index' =>  $paginator->items()['to'], 'total_count' =>  $paginator->items()['total'] ])}}--}}
                            {{__('main.total_records')}}: {{ $entries->total() }}
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex mt-3">
                                <div class="mx-auto">
                                    {{ $entries->links() }}
                                </div>
                            </div>
                        </div>
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
@section('plugins.Datetimepicker', true)
@section('plugins.Sweetalert2', true)

@section('js')
    <script>
		$(document).ready(function () {
			$('#entry_from_date, #entry_to_date').datetimepicker({
				format: 'YYYY-MM-DD',
				icons:
					{
						previous: 'fas fa-angle-left',
						next: 'fas fa-angle-right',
						up: 'fas fa-angle-up',
						down: 'fas fa-angle-down'
					}
			});
		});
    </script>
@stop
