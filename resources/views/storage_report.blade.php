@extends('adminlte::page')

@section('title', __('main.storage_report'))

@section('content_header')
    <h1>{{ __('main.storage_report') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-secondary">
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- form start -->
                    <form class="" action="{{route('storage_report')}}" method="post">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="month">{{__('main.month')}}</label>
                                    <select class="form-control form-control-sm" id="month" name="month">
                                        <option value="">{{ __('main.choose') }}</option>
                                        @foreach($months as $key=>$month)
                                            <option value="{{ $key }}" @if(session('storage_report_filter')['month'] == $key) selected @endif>{{$month}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="year">{{__('main.year')}}</label>
                                    <select class="form-control form-control-sm" id="year" name="year">
                                        @foreach($years as $key=>$year)
                                            <option value="{{ $year }}" @if(session('storage_report_filter')['year'] == $year) selected @endif>{{$year}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 pt-4 mt-2">
                                <button type="submit" name="filter" value="1" class="btn btn-primary btn-sm mr-2">{{__('main.filter')}}</button>
                                <button type="submit" name="reset" value="1" class="btn btn-default btn-sm mr-2">{{__('main.reset')}}</button>
                                <button type="submit" name="export" value="1" class="btn btn-warning btn-sm">{{__('main.export_xlsx')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    @if($days_data)
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered table-striped table-hover text-sm mb-0">
                            <thead>
                            <tr>
                                <th>{{__('main.date')}}</th>
                                <th>{{__('main.shelves')}}</th>
                                <th>{{__('main.shelf_price')}}</th>
                                <th>{{__('main.shelves')}}</th>
                                <th>{{__('main.pallet_price')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($days_data as $key => $days_datum)
                                <tr>
                                    <td>{{$key}}</td>
                                    <td>@if(isset($days_datum['shelves'])){{$days_datum['shelves']}}@endif</td>
                                    <td>@if(isset($days_datum['shelf_price'])){{$days_datum['shelf_price']}}@endif</td>
                                    <td>@if(isset($days_datum['pallets'])){{$days_datum['pallets']}}@endif</td>
                                    <td>@if(isset($days_datum['pallet_price'])){{$days_datum['pallet_price']}}@endif</td>
                                </tr>
                            @endforeach
                                <tr>
                                    <th>{{__('main.total')}}</th>
                                    <th></th>
                                    <th>{{$totals['shelf_price']}}</th>
                                    <th></th>
                                    <th>{{$totals['pallet_price']}}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('css')
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">
@stop

@section('plugins.Moment', true)
@section('plugins.Datetimepicker', true)
@section('plugins.Sweetalert2', true)

@section('js')
    <script>
		$(document).ready(function () {
		});
    </script>
@stop
