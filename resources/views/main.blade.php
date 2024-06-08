@extends('adminlte::page')

@section('title', config('app.name') . ' | Eventos TITAN')

@section('content_header')
    <x-loading />
    <h5 class="font-weight-bold text-uppercase text-light">Eventos TITAN</h5>
@stop

@section('preloader')
    {{-- @TODO criar componente --}}
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde enquanto consultamos o banco de dados MQTT.</h4>
@stop

@section('content')

    <div class="card">

        <div class="card-header">
            <div class="row">
                <div class="col-md-6 text-light">Dashboard</div>
            </div>
        </div>

        <div class="card-body">

            <div class="row">
                <div class="col-md-12">
                    <x-filter/>  
                </div>
            </div>

            {{-- Adicionar charts --}}
            <hr>
            <div class="row">
                <div class="col-md-6"> <div id="chart-status" class="chart-card"></div> </div>
                <div class="col-md-6"> <div id="chart-movimentacao-maquina" class="chart-card"></div> </div>
            </div>

            <hr><div class="row"><div class="col-md-12"> <div id="chart-alarme" class="chart-card"></div></div></div>

            <hr><div class="row"><div class="col-md-12"> <div id="chart-situacao-producao" class="chart-card"></div></div></div>

            <hr><div class="row"><div class="col-md-12"> <div id="chart-situacao-alimentacao-maquina" class="chart-card"></div></div></div>

            <hr><div class="row"><div class="col-md-12"> <div id="chart-horimetro-motor-diesel" class="chart-card"></div></div></div>
            <hr><div class="row"><div class="col-md-12"> <div id="chart-horimetro-esteiras-locomocao" class="chart-card"></div></div></div>
            {{-- Fim charts --}}
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://code.highcharts.com/dashboards/css/dashboards.css">
    <link rel="stylesheet" href="{{ asset('assets/css/loading.css') }}">
    <style>
        .chart-card {
            width:100%; 
            height:250px; 
            text-align:center
        }
        .content-header, .content {
            background-color:#000; 
        }
        .card {
            background-color:#181b1f;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>

    <script src="{{ asset('scripts/dellano-dark-theme.js') }}"></script>

    <script src="{{ asset('scripts/main.js') }}"></script>

    <script src="{{ asset('scripts/chart-status.js') }}"></script>
    <script src="{{ asset('scripts/chart-alarme.js') }}"></script>
    <script src="{{ asset('scripts/chart-horimetro-motor-diesel.js') }}"></script>
    <script src="{{ asset('scripts/chart-horimetro-esteiras-locomocao.js') }}"></script>
    <script src="{{ asset('scripts/chart-movimentacao-maquina.js') }}"></script>
    <script src="{{ asset('scripts/chart-situacao-producao.js') }}"></script>
    <script src="{{ asset('scripts/chart-situacao-alimentacao-maquina.js') }}"></script> 

@stop
