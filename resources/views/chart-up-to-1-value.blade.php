@extends('adminlte::page')

@section('title', config('app.name') . ' | Indicadores')

@section('content_header')

    <x-loading/>

    <div class="row">
        <div class="col-md-6">
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">← VOLTAR</a>
        </div>
        <div class="col-md-6 text-right">
            Manter leitura ao vivo: <input onchange="blockFilter()" type="checkbox" id="keep-reading" data-off="OFF"
                data-on="ON" data-toggle="toggle" data-onstyle="success" data-size="xs">
        </div>
    </div>

    <h5 id="indicator-name" class="font-weight-bold text-uppercase text-center">
        {{-- @TODO loading que pode virar componente --}}
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </h5>
    <hr>
@stop

@section('preloader')
    {{-- @TODO loading que pode virar componente --}}
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde enquanto consultamos o banco de dados MQTT.</h4>
@stop

@section('content')

    <div class="row">
        <div class="col-md-2">

            <x-filter/>

            <hr />

            <div id="menu-id"></div>

        </div>
        <div class="col-md-10">

            <div id="container"></div>

        </div>
    </div>

@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/loading.css') }}">

    <style>
        .content-wrapper {
            background-color: #fff !important;
        }

        .highcharts-credits {
            display: none;
        }
    </style>
@stop

@section('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        TIME = 3000;
        TITLE = 'Quantidade de disparos por dia.'

        document.addEventListener('DOMContentLoaded', function() {

            blockFilter();

            $('#form-filter').submit(function(e) {

                e.preventDefault();

                const params = $(e.target).serialize();
                const qs = Object.fromEntries(new URLSearchParams(params));

                if ( !qs.closed_period && (!qs.start_date || !qs.end_date) )
                    return Swal.fire('Erro','Por favor, preencha o filtro corretamente.','error');

                $('#loading-screen').fadeIn();

                console.time("tempo-de-execucao");

                (async () => {

                    try {
                        const _URL = new URL(window.location.href);
                        const pathSegments = _URL.pathname.split("/").filter(segment => segment !== '');
                        const _params = params ? `&${params}` : '';

                        const dataChartLine = await fetchData(`/api/mensagens-um-valor?type_event=${pathSegments.pop()}` + _params);

                        chart = createChartLine(TITLE, 'container', dataChartLine, 'line');

                    } catch (err) {
                        Swal.fire('Erro',err.message,'error');
                    }

                    $('#loading-screen').fadeOut();

                })();

                console.timeEnd("tempo-de-execucao");
            });

        });

        //! Start
        (async () => {

            try {
                const dataChartLine = await getData();
                chart = createChartLine(TITLE, 'container', dataChartLine, 'line');

            } catch (error) {

                Swal.fire('Erro',err.message,'error');
            }

        })();

        // Atualiza Chart(s) de tempo em tempo.
        setInterval(function() {

            (async () => {

                if (document.querySelector('#keep-reading').checked)
                    console.log('> Unlocked')
                else
                    return console.log('> Locked')

                const dataChartLine = await getData();

                chart = createChartLine('Quantidade de disparos...', 'container', dataChartLine, 'line');

            })();

        }, TIME);

        function blockFilter() {

            const btnFilter = document.getElementById('btn-filter');

            if (document.querySelector('#keep-reading').checked)
                btnFilter.setAttribute('disabled', 'disabled');
            else
                btnFilter.removeAttribute('disabled');
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

        async function createChartLine(_title, _id, _data, _type = 'column') {

            const chart = Highcharts.chart(_id, {
                chart: {
                    type: _type
                },
                title: {
                    text: _title
                },
                xAxis: {
                    categories: _data.map(function(item) {
                        return item.ts_formated;
                    }),
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Quantidade de Registros'
                    }
                },
                series: [{
                    name: 'Vezes',
                    data: _data.map(function(item) {
                        return item._count;
                    })
                }]
            });

            return chart;
        }

        async function getData(params = null) {

            const _URL = new URL(window.location.href);
            const pathSegments = _URL.pathname.split("/").filter(segment => segment !== "");
            const _params = params ? `&${params}` : '';

            const hash = pathSegments.pop();

            if (!hash)
                return;

            const record = await fetchData(`/api/mensagens-um-valor?type_event=${hash}` + _params);
            
            if (!record.length)
                throw new Error("Nenhum resultado foi encontrado.");
            
            document.querySelector('#indicator-name').innerHTML = `${record[0].tipo_evento}`;

            return record;
        }
    </script>

@stop
