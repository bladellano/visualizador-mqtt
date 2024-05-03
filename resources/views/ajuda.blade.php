@extends('adminlte::page')

@section('title', config('app.name') . ' | Ajuda')

@section('content_header')
<p></p>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            Sobre
        </div>
        <div class="card-body">
            <p class="card-text badge badge-success">VERSÃO: 1.0.0</p>
            <p class="card-text">
                Solução para visualização dos eventos do MQTT Broker local
                <br />Todos os direitos reservados </p>
            <p class="card-text">Para maiores informações acesso o website</p>
            <a href="https://www.meraki.eti.br/" target="_blank" class="card-text badge badge-secondary">https://www.meraki.eti.br/</a>
        </div>
    </div>

@stop

@section('css')
    <style>
    </style>
@stop

@section('js')

@stop
