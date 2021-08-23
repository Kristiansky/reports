
@extends('adminlte::page')

@section('title', __('main.upload_invoice'))

@section('content_header')
    <h1>{{ __('main.upload_invoice') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 col-lg-5">
            <form action="{{route('order.upload_invoice', $order->idcomanda)}}" method="post" enctype="multipart/form-data" >
                @csrf
                @method('POST')
                <div class="form-group">
                    <label for="pdf_file">{{__('main.choose_file')}}</label>
                    <input type="file" class="form-control-file form-control-sm @error('pdf_file') is-invalid @enderror" id="pdf_file" name="pdf_file" required>
                    @error('pdf_file')
                    <div class="invalid-feedback">{{$message}}</div>
                    @enderror
                </div>
                <button type="submit" name="addPdf" value="1" class="btn btn-primary">{{__('main.submit')}}</button>
            </form>
        </div>
    </div>
@endsection

@section('css')
    <link href="{{asset('css/custom.css')}}" rel="stylesheet">
@stop
