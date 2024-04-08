@extends('adminlte::page')

@section('title', 'Evento | ' . $evento['tipo_evento'])

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
                <div class="col-md-6">
                    <ul class="list-group">

                        @foreach ($evento['eventos'] as $e)
                            
                            <li class="list-group-item">
                                <h6 class="text-left">{{ $e }}:</h6>
                                <div class="progress">
                                    <div 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: {{ $e }}%;" 
                                        aria-valuenow="{{ $e }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">{{ $e }}%</div>
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

@stop

@section('js')

@stop
