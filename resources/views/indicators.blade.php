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
            <div class="row">
                <div class="col-md-6">Listagem de SubTópicos.</div>
                <div class="col-md-6 text-right"><button type="button" class="btn btn-primary btn-sm" id="openUrl">CARREGAR MAIS DE UM INDICADOR</button></div>
            </div>
        </div>
        <div class="card-body">

           <div class="row">
            <div class="col-md-6">
                <a href="#" id="selectAll" class="link-primary">Marcar todos</a> | 
                <a href="#" id="unselectAll" class="link-secondary">Desmarcar todos</a>
                <div class="list-group">

                    @foreach ($menu as $key => $item)
                        <button class="list-group-item list-group-item-action">
                            <input {{ $item['view'] == 'chart-up-to-1-value' ? '' : 'disabled' }} type="checkbox" value="{{ $item['base64'] }}" name="selected-indicators" id="selected-indicators">
                            <a href="chart/{{ $item['view'] }}?type_event[]={{ $item['base64'] }}" class="text-uppercase">{{ $key }}</a>
                        </button>
                    @endforeach
    
                </div>
            </div>
           </div>

        </div>        
    </div>

@stop

@section('css')
    <style>
      
    </style>
@stop

@section('js')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const selectedIndicators = new Set();

            document.querySelectorAll('input[name="selected-indicators"]').forEach(checkbox => {
                checkbox.addEventListener('change', event => {
                    if (event.target.checked) 
                        selectedIndicators.add(event.target.value);
                    else 
                        selectedIndicators.delete(event.target.value);
                });
            });

            document.getElementById('openUrl').addEventListener('click', () => {

                const baseUrl = 'chart/chart-up-to-1-value';

                var hashs = Array.from(selectedIndicators); 

                if(!hashs.length)
                    return Swal.fire('Atenção', 'Por favor, selecione pelo menos um indicador.', 'warning');


                const urlOpen = `${baseUrl}?type_event[]=${hashs.join('&type_event[]=')}`;

                window.location.href = urlOpen;
            });

            document.getElementById('selectAll').addEventListener('click', () => {
                document.querySelectorAll('[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = true;
                    selectedIndicators.add(checkbox.value);
                });
            });

            document.getElementById('unselectAll').addEventListener('click', () => {
                document.querySelectorAll('[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                    selectedIndicators.delete(checkbox.value);
                });
            });

        });

    </script>

@stop
