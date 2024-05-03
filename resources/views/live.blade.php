@extends('adminlte::page')

@section('title', config('app.name') . ' | Indicadores')

@section('content_header')

    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">← Indicadores</a>
    <hr />
    Manter leitura ao vivo: <input onchange="blockFilter()" type="checkbox" id="keep-reading" data-off="OFF" data-on="ON"
        data-toggle="toggle" data-onstyle="success" data-size="xs">
    <hr />
    <h1 id="event_name">
        {{-- @TODO loading que pode virar componente --}}
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </h1>
@stop

@section('preloader')
    {{-- @TODO loading que pode virar componente --}}
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde enquanto consultamos o banco de dados MQTT.</h4>
@stop

@section('content')

    <div>
        <div class="row">
            <div class="col-md-6">
                {{-- @TODO form/filter que pode virar componente --}}
                <form action="#" id="formFilter">
                    <label for="">Período:</label>
                    <input type="datetime-local" name="start_date" id="start_date">
                    <input type="datetime-local" name="end_date" id="end_date">
                    <button id="btn-filter" type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                </form>
            </div>
        </div>
    </div>
    <hr />

    <figure class="highcharts-figure">
    </figure>

@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">
    <style>
        .highcharts-figure {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
            grid-auto-rows: 12rem;
            grid-gap: 2px;
            grid-gap: 6px;
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

    <script>
        CHARTS = [];
        TIME = 3000;

        document.addEventListener('DOMContentLoaded', function() {

            blockFilter();

            $('#formFilter').submit(function(e) {

                e.preventDefault();

                const params = $(e.target).serialize();
                const qs = Object.fromEntries(new URLSearchParams(params));

                if (!Object.values(qs).every(v => v !== ''))
                    return alert('Por favor, preencha o filtro corretamente.')

                console.time("tempo-de-execucao");

                (async () => {

                    try {
                        const details = await eventDetails(params);

                        CHARTS.forEach(e => {
                            e.series[0].points[0].update(+details[e.userOptions.title])
                        });

                    } catch (err) {
                        alert(err.message);

                        const details = await eventDetails();

                        CHARTS.forEach(e => {
                            e.series[0].points[0].update(0)
                        });
                    }

                })();

                console.timeEnd("tempo-de-execucao");
            });

        });

        (async () => {

            const details = await eventDetails();

            const highchartsFigure = document.querySelector(".highcharts-figure");

            for (key in details) {

                const div = document.createElement("div");

                div.id = key.replace(/\s+/g, '-').toLowerCase();
                div.className = "chart-container";
                div.textContent = key;

                highchartsFigure.appendChild(div);
            }

            document.querySelectorAll('.chart-container').forEach((e) => {
                var created = createChart(e.innerHTML, e.id);
                CHARTS.push(created);
            });

        })();

        // Atualiza Chart(s) de tempo em tempo.
        setInterval(function() {

            (async () => {

                if (document.querySelector('#keep-reading').checked)
                    console.log('> Destravado')
                else
                    return console.log('> Travado')

                const details = await eventDetails();

                CHARTS.forEach(e => {
                    e.series[0].points[0].update(+details[e.userOptions.title])
                });

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

        function createChart(_title, _id) {

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

        async function eventDetails(params = null) {

            const _URL = new URL(window.location.href);
            const pathSegments = _URL.pathname.split("/").filter(segment => segment !== "");
            const _params = params ? `&${params}` : '';

            const hash = pathSegments.pop();

            if (!hash)
                return;

            const eventByName = await fetchData(`/api/registers-events?type_event=${hash}` + _params);

            if (!eventByName.length)
                throw new Error("Nenhum resultado foi encontrado.");

            const event = await fetchData(`/api/registers-events/${eventByName[0].id}/${eventByName[0].event.tipo}`);

            document.querySelector('#event_name').innerHTML = `Indicador <b>${event.tipo_evento}</b>`;

            return event.details;
        }
    </script>

@stop
