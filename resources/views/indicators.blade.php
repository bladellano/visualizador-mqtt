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
                        <div class="title">
                            <img src="{{ asset('images/engine.png') }}" alt=""><br/>
                            <a href="chart/{{ $item['view'] }}?type_event[]={{ $item['base64'] }}">{{ $key }}</a>
                        </div>
                    </div>
                @endforeach
                <div class="board bg-important">
                    <div class="title">
                        <a href="chart/{{ $item['view'] }}?type_event[]=TWFxdWluYSBPbiBMaW5l&type_event[]=SG9yaW1ldHJvIE1vdG9yIERpZXNlbA==&type_event[]=SG9yaW1ldHJvIEVzdGVpcmFzIExvY29tb8Onw6Nv&type_event[]=QWxhcm1lIEF0aXZv">•
                            Maquina On Line <br> • Horimetro Motor Diesel <br> • Horimetro Esteiras Locomoção <br> • Alarme Ativo
                        </a>
                    </div>
                </div>
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
            width: 250px;
            height: 120px;
            margin: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            border: 1px solid #999;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 2px;

        }

        .content-body .board:hover {
            background-color: #ccc;
        }

        .content-body .title {
            text-align: center;
            text-transform: uppercase;
        }

        .content-body .title a {
            color: #000;
            font-size: 14px;
        }

        .content-body .board.bg-important {
            display: inline-block;
            background-color: #000;
            border: 1px solid #333;!important;

        }

        .content-body .board.bg-important .title a {
            color: white !important;
        }
    </style>
@stop

@section('js')
@stop
