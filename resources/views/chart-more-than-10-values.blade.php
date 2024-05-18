@extends('adminlte::page')

@section('title', config('app.name') . ' | Indicadores')

@section('content_header')

    <x-loading />

    <div class="row">
        <div class="col-md-6">
            <a href="{{ route('indicators') }}" class="btn btn-secondary btn-sm">VOLTAR</a>
        </div>
        <div class="col-md-6 text-right">
            Manter leitura ao vivo: <input onchange="blockFilter()" type="checkbox" id="keep-reading" data-off="OFF" data-on="ON" data-toggle="toggle" data-onstyle="success" data-size="xs">
        </div>
    </div>

    <h5 id="indicator-name" class="font-weight-bold text-uppercase text-center">
        {{-- @TODO loading que pode virar componente --}}
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="sr-only">Carregando...</span>
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

        <div class="col-md-12">

            <x-filter :with_time="true" />          

        </div>

    </div>
    <div class="row">
        <div class="col-md-12">
            <figure class="highcharts-figure">
            </figure>
        </div>
    </div>

@stop

@section('css')

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/loading.css') }}">

    <style>
        .highcharts-figure {
            display: grid;
            grid-template-columns: repeat(4, minmax(16rem, 1fr));
            grid-auto-rows: 165px;
            grid-gap: 2px;
        }

        .content-wrapper {
            background-color: #fff !important;
        }
     
    </style>
@stop

@section('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script src="https://code.highcharts.com/highcharts-more.js"></script>

    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        CHARTS = [];
        TIME = 3000;

        document.addEventListener('DOMContentLoaded', function() {

            blockFilter();

            $('#form-filter').submit(function(e) {

                e.preventDefault();

                const params = $(e.target).serialize();
                const qs = Object.fromEntries(new URLSearchParams(params));

                if (!qs.closed_period && (!qs.start_date || !qs.end_date))
                    return Swal.fire('Erro', 'Por favor, preencha o filtro corretamente.', 'error');

                $('#loading-screen').fadeIn();

                console.time("tempo-de-execucao");

                (async () => {

                    try {
                        const queryString = window.location.search;
                        const _URL = new URL(window.location.href);
                        const pathSegments = _URL.pathname.split("/").filter(segment => segment !== "");
                        const _params = params ? `&${params}` : '';

                        const eventByName = await fetchData(`/api/registers-events${queryString}` + _params);

                        /* Se não existir os elementos em tela, ele constroi. */
                        if(!document.querySelectorAll('.chart-container').length) {
                            const params = $('#form-filter').serialize();
                            const details = await eventDetails(params);
                            createChartsOnScreen(details);
                        }

                        createMenu(eventByName);

                    } catch (err) {

                        Swal.fire('Erro', err.message, 'error');

                        const details = await eventDetails();

                        updateCHART(0);
                    }

                    $('#loading-screen').fadeOut();

                })();

                console.timeEnd("tempo-de-execucao");
            });

            // FOCO
            $("body").delegate(".target-change-time", "change", async function(e) {

                $('#loading-screen').fadeIn();

                try {

                    const details = await eventDetails(`id=${e.currentTarget.value}`);

                    updateCHART(details);

                } catch (err) {

                    Swal.fire('Erro', err.message, 'error');

                    const details = await eventDetails();

                    updateCHART(0);

                }

                $('#loading-screen').fadeOut();

            });

        });

        // Start
        (async () => {

            try {

                const params = $('#form-filter').serialize();

                const details = await eventDetails(params);

                createChartsOnScreen(details);

                updateCHART(details);

            } catch (err) {
                Swal.fire('Erro', err.message, 'error');
            }

        })();

        // Atualiza Chart(s) de tempo em tempo.
        setInterval(function() {

            (async () => {

                if (document.querySelector('#keep-reading').checked)
                    console.log('%cUnlocked', 'color: #5ac039');
                else
                    return console.log('%cLocked', 'color: #fff');

                const details = await eventDetails();

                updateCHART(details);

            })();

        }, TIME);

        // FUNCTIONS
        function blockFilter() {

            updateCHART(0);

            const btnFilter = document.getElementById('btn-filter');

            if (document.querySelector('#keep-reading').checked)
                btnFilter.setAttribute('disabled', 'disabled');
            else
                btnFilter.removeAttribute('disabled');
        }

        /** @argument _attributes é um objeto com chave e valor dos atributos do tipo de evento:
         * Ex: {"Media Consumo Geral (L/H)": "23", "Media Consumo Desde a última Partida (L/H)": "23", "Pressão Óleo Motor (Bar)": "0.0"}
         * */
        function createChartsOnScreen(_attributes) {

            const highchartsFigure = document.querySelector(".highcharts-figure");

            for (key in _attributes) {

                const div = document.createElement("div");

                div.id = key.replace(/\s+/g, '-').toLowerCase();
                div.className = "chart-container";
                div.textContent = key;

                highchartsFigure.appendChild(div);
            }

            document.querySelectorAll('.chart-container').forEach((e) => {
                var created = createChartSpeedometer(e.innerHTML, e.id);
                CHARTS.push(created);
            });

        }

        async function updateCHART(_details) {
            CHARTS.forEach(e => e.series[0].points[0].update(+_details[e.userOptions.title]));
        }

        async function fetchData(endpoint) {
            try {
                const response = await fetch(endpoint);
                const data = await response.json();
                return data;
            } catch (error) {
                throw error;
            }
        }

        function createChartSpeedometer(_title, _id) {

            const options = {
                chart: {
                    type: 'solidgauge'
                },
                title: _title,
                pane: {
                    center: ['50%', '85%'],
                    size: '140%',
                    startAngle: -90,
                    endAngle: 90,
                    background: {
                        backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || '#EEE',
                        innerRadius: '60%',
                        outerRadius: '100%',
                        shape: 'arc'
                    }
                },
                exporting: {
                    enabled: false
                },
                tooltip: {
                    enabled: false
                },
                yAxis: {
                    stops: [
                        [0.1, '#55BF3B'], // green
                        [0.5, '#DDDF0D'], // yellow
                        [0.9, '#DF5353'] // red
                    ],
                    lineWidth: 0,
                    tickWidth: 0,
                    minorTickInterval: null,
                    tickAmount: 2,
                    title: {
                        y: -70
                    },
                    labels: {
                        y: 16
                    }
                },
                plotOptions: {
                    solidgauge: {
                        dataLabels: {
                            y: 5,
                            borderWidth: 0,
                            useHTML: true
                        }
                    }
                }
            };

            const chart = Highcharts.chart(_id, Highcharts.merge(options, {
                yAxis: {
                    min: 0,
                    max: 200,
                    title: {
                        text: _title
                    }
                },
                credits: {
                    enabled: false
                },
                series: [{
                    name: _title,
                    data: [0],
                    dataLabels: {
                        format: '<div style="text-align:center">' +
                            '<span style="font-size:25px">{y}</span><br/>' +
                            '<span style="font-size:12px;opacity:0.4"></span>' +
                            '</div>'
                    },
                    tooltip: {
                        // valueSuffix: ' km/h'
                        valueSuffix: ''
                    }
                }]
            }));

            return chart;
        }

        function createMenu(data) {

            const select = document.createElement("select");

            const selectTime = document.querySelector('#menu-id');

            select.className = 'form-control form-control-sm target-change-time';

            var option = document.createElement("option");
            option.value = '';
            option.textContent = `--Selecione o Tempo--`;

            select.appendChild(option);

            data.forEach(o => {

                var option = document.createElement("option");
                option.value = o.id;
                option.textContent = `🕐 ${o.ts_formatada}`;

                select.appendChild(option);

            });

             if(document.querySelector('.target-change-time'))
                document.querySelector('.target-change-time').remove();

            selectTime.appendChild(select);            
        }

        async function eventDetails(params = null) {

            const queryString = window.location.search;
            const _URL = new URL(window.location.href);
            const pathSegments = _URL.pathname.split("/").filter(segment => segment !== "");
            const _params = params ? `&${params}` : '';

            const hash = pathSegments.pop();

            if (!hash)
                return;

            const eventByName = await fetchData(`/api/registers-events${queryString}` + _params);

            if (!eventByName.length)
                throw new Error("Nenhum resultado foi encontrado. Tente ajustar o filtro.");

            const event = await fetchData(`/api/registers-details/${eventByName[0].id}/${eventByName[0].event.tipo}`);

            document.querySelector('#indicator-name').innerHTML = `${event.tipo_evento}`;

            return event.details;
        }
    </script>

@stop
