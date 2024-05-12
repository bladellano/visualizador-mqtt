@extends('adminlte::page')

@section('title', config('app.name') . ' | Indicadores')

@section('content_header')
    <h5 class="font-weight-bold text-uppercase">Indicadores</h5>
@stop

@section('preloader')
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde enquanto consultamos o banco de dados MQTT.</h4>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            Listagem de SubTópicos.
        </div>
        <div class="card-body">

            <div class="content-body">
                @foreach ($menu as $key => $item)
                    <div class="board">
                        <div class="title"><a href="chart/{{$item['view']}}/{{$item['base64']}}">{{ $key }}</a></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        .content-body {
            display: flex;
            flex-wrap: wrap;
        }
        .content-body .board {
            width: 120px;
            height: 120px;
            margin: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #007bff;
            border: 1px solid #ccc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 2px
        }
        .content-body .title {
            text-align: center;
            text-transform: uppercase;
        }
        .content-body .title a{
            color: white!important;
            font-size: 14px
        }
    </style>
@stop

@section('js')
@stop
