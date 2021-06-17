@extends('adminlte::page')

@section('title', __('main.change_client'))

@section('content_header')
    <h1>{{ __('main.change_client') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
{{--            @if(!session('client'))--}}
                <form class="form-inline" method="post" action="{{route('change_client_update')}}">
                    @csrf
                    <div class="form-group">
                        <label for="client" class="mr-2">{{ __('main.choose_client') }}</label>
                        <select class="form-control mr-2" id="client" name="client">
                            <option value="0">{{ __('main.choose') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->idclient }}" @if(session('client') && session('client')->Id == $client->idclient) selected @endif>{{$client->cod_client}} - {{$client->NumeClient1}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('main.submit') }}</button>
                </form>
{{--            @endif--}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/custom.css">
@stop

@section('js')
{{--    <script> console.log('Hi!'); </script>--}}
@stop
{{--@section('plugins.Datatables', true)--}}
