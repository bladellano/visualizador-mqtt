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
        <div class="col-md-2">

            <x-filter :with_time="true"/>
       
            <hr/>

            <div id="menu-id"></div>

        </div>
        <div class="col-md-10">
            <figure class="highcharts-figure">
            </figure>
        </div>
    </div>

@stop

@section('css')

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
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

        /* 
        .highcharts-figure>* { height: 65px; }
        .hc-cat-title { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .highcharts-title { display: none; } 
        */
        #menu-id {
            overflow: auto;
            height: 550px;
        }

    </style>
@stop

@section('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script src="https://code.highcharts.com/modules/bullet.js"></script>
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

                if ( !qs.closed_period && (!qs.start_date || !qs.end_date) )
                    return Swal.fire('Erro','Por favor, preencha o filtro corretamente.','error');

                $('#loading-screen').fadeIn();

                console.time("tempo-de-execucao");

                (async () => {

                    try {
                        const _URL = new URL(window.location.href);
                        const pathSegments = _URL.pathname.split("/").filter(segment => segment !== "");
                        const _params = params ? `&${params}` : '';

                        const eventByName = await fetchData(`/api/registers-events?type_event=${pathSegments.pop()}` + _params);

                        createMenu(eventByName);

                    } catch (err) {

                        Swal.fire('Erro',err.message,'error');

                        const details = await eventDetails();

                        CHARTS.forEach(e => {
                            e.series[0].points[0].update(0)
                        });
                    }

                    $('#loading-screen').fadeOut();

                })();

                console.timeEnd("tempo-de-execucao");
            });

            $("body").delegate(".target-indicator-clicked", "click", async function(e){
                 
                $('#loading-screen').fadeIn();

                try {

                    const details = await eventDetails(`id=${e.target.dataset.id}`);

                    CHARTS.forEach(e => {
                        e.series[0].points[0].update(+details[e.userOptions.title])
                        // e.series[0].points[0].update(+details[e.userOptions.title.text])
                    });

                } catch (err) {

                    Swal.fire('Erro',err.message,'error');

                    const details = await eventDetails();

                    CHARTS.forEach(e => {
                        e.series[0].points[0].update(0)
                    });
                }

                $('#loading-screen').fadeOut();

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
                // var created = createChartBullet(e.innerHTML, e.id);
                var created = createChartSpeedometer(e.innerHTML, e.id);
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
                    // e.series[0].points[0].update(+details[e.userOptions.title.text])
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

        function createChartBullet(_title, _id) {

            const options = {
                chart: {
                    inverted: true,
                    marginLeft: 220,
                    type: 'bullet'
                },
                title: {
                    text: null
                },
                legend: {
                    enabled: false
                },
                yAxis: {
                    gridLineWidth: 0
                },
                plotOptions: {
                    series: {
                        pointPadding: 0.25,
                        borderWidth: 0,
                        color: '#000',
                        targetOptions: {
                            width: '280%'
                        }
                    }
                },
                credits: {
                    enabled: false
                },
                exporting: {
                    enabled: false
                }
            }

            Highcharts.setOptions(options);

            const chart = Highcharts.chart(_id, {
                chart: {
                    marginTop: 8
                },
                title: {
                    text: _title
                },
                xAxis: {
                    categories: ['<span class="hc-cat-title">' + _title + '</span>']
                },
                yAxis: {
                    plotBands: [{
                        from: 0,
                        to: 50,
                        color: '#eeeeee'
                    }, {
                        from: 50,
                        to: 150,
                        color: '#ffdb58'
                    }, {
                        from: 150,
                        to: 300,
                        color: '#ffc247'
                    }, {
                        from: 300,
                        to: 9e9,
                        color: '#ff6347'
                    }],
                    title: null
                },
                series: [{
                    data: [{
                        y: 0,
                        target: 1
                    }]
                }],
                tooltip: {
                    pointFormat: '<b>{point.y}</b> (with target at {point.target})'
                }
            });

            return chart;

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

                const ul = document.createElement("ul");

                ul.className = 'list-group';

                data.forEach(o => {

                    const a = document.createElement("a");
                    a.setAttribute('data-id', o.id);
                    a.href = `#${o.id}`;
                    a.textContent = `🕐 ${o.ts_formatada}`;
                    a.className = "text-uppercase list-group-item list-group-item-action list-group-item-secondary target-indicator-clicked";

                    ul.appendChild(a);

                });

                document.querySelector('#menu-id').innerHTML = null
                document.querySelector('#menu-id').appendChild(ul);
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

            document.querySelector('#indicator-name').innerHTML = `${event.tipo_evento}`;

            return event.details;
        }
    </script>

@stop
