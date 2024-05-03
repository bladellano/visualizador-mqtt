@extends('adminlte::page')

@section('title', config('app.name') . ' | Indicadores')

@section('content_header')
    <h1>Indicadores</h1>
@stop

@section('preloader')
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde enquanto consultamos o banco de dados MQTT.</h4>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            Listagem de Eventos.
        </div>
        <div class="card-body">
            <div class="inner-indicators">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
@stop

@section('js')
    <script>
        (async () => {

            const _indicators = await indicators();

            const ul = document.createElement("ul");
            ul.className = 'list-group';
            
            _indicators.forEach(o => {
                const li = document.createElement("li");
                li.className = "list-group-item";

                const a = document.createElement("a");
                a.href = "live/" + btoa(o.tipo_evento)
                a.textContent = o.tipo_evento;
                a.className = 'text-uppercase';

                li.appendChild(a);

                ul.appendChild(li);

            });

            document.querySelector('.inner-indicators').innerHTML = null
            document.querySelector('.inner-indicators').appendChild(ul);

        })();

        async function indicators() {
            const data = await fetchData(`/api/registers-events`);
            console.log("data ", data);

            return data;
        }

        // FUNCTIONS
        async function fetchData(endpoint) {
            try {
                const response = await fetch(endpoint);
                const data = await response.json();
                return data;
            } catch (error) {
                throw error;
            }
        }
    </script>
@stop
