@extends('adminlte::page')

@section('title', config('app.name') . ' | Eventos TITAN')

@section('content_header')
    <x-loading />
    <h5 class="font-weight-bold text-uppercase">Eventos TITAN</h5>
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
                <div class="col-md-6">Dashboard</div>
            </div>
        </div>

        <div class="card-body">

            <div class="row">
                <div class="col-md-12">
                    <x-filter/>  
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">

                    <div id="chart-status" style="width:100%; height:400px;"></div>

                </div>
            </div>

        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://code.highcharts.com/dashboards/css/dashboards.css">
    <link rel="stylesheet" href="{{ asset('assets/css/loading.css') }}">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="{{ asset('scripts/chart-status.js') }}"></script>
@stop
