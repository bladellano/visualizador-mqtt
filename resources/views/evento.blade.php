@extends('adminlte::page')

@section('title', env('APP_NAME', '--') . ' | Evento | ' . $evento['tipo_evento'])

@section('content_header')
    <h1>Evento do MQTT</h1>
@stop

@section('content')

    <button onclick="window.history.back()" class="btn btn-secondary btn-sm text-uppercase mb-2">Voltar</button>

    <div class="card text-center">
        <div class="card-header font-weight-bold">
            {{ $evento['nome_maquina'] }}
        </div>
        <div class="card-body">
            <span class="badge bg-primary text-uppercase">{{ $evento['tipo_evento'] }}</span>
            <p class="card-text">Aqui você encontrará os detalhes de cada evento.</p>

            <div class="row">
                <div class="col-md-12">

                    <ul class="list-attritutes">

                        @foreach ($evento['eventos'] as $k => $v)
                            <li>
                                
                                <h6 class="text-left font-weight-light text-uppercase">{{ $k }}</h6>
                                <div class="progress">
                                    <div 
                                        class="progress-bar {{ $v > 50 ? 'bg-danger' : '' }}" 
                                        role="progressbar" 
                                        style="width: {{ $v }}%;"
                                        aria-valuenow="{{ $v }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100"
                                    >
                                        {{ $v }}
                                    </div>
                                </div>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>

        </div>
        <div class="card-footer text-muted">
            {{ $evento['ts'] }}
        </div>
    </div>
@stop

@section('css')
    <style>
        ul.list-attritutes {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        ul.list-attritutes>li {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        ul.list-attritutes>li:nth-child(6n) {
            margin-right: 0;
        }

    </style>
@stop

@section('js')

@stop
