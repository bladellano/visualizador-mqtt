@extends('adminlte::page')

@section('title', 'O Visualizador MQTT')

@section('content_header')
    <h1>Seja bem-vindo.</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            Solução para visualizar os Eventos do MQTT Broker
        </div>
        <div class="card-body">
            <p class="card-text">O Visualizador MQTT irá mostrar todos os eventos publicados e recebidos pelo Serviço MQTT Local.</p>
            <p class="card-text">Visualizador MQTT pode pesquisar e filtrar os eventos por vários campos a serem escolhidos.</p>
            <a href="/reports" class="btn btn-primary btn-sm text-uppercase">Eventos</a>
            <a href="/dashboard" class="btn btn-primary btn-sm text-uppercase">Dashboard</a>
        </div>
    </div>

@stop

@section('css')
    <style>
    </style>
@stop

@section('js')

@stop
