@extends('adminlte::page')

@section('title', config('app.name') . ' | Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('preloader')
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde...</h4>
@stop

@section('content')

    <div class="row">
        <div class="col-md-12">
            <input type="checkbox" id="status-maquina" data-off="OFF" data-on="ON" data-toggle="toggle" data-onstyle="success" data-size="xs">
            <label id="nome-maquina">
                <div class="spinner-border spinner-border-sm" role="status">
                <span class="sr-only">Loading...</span>
              </div>
            </label>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <label for="">Período:</label>
                    <input type="date" name="start-date" id="start-date">
                    <input type="date" name="end-date" id="end-date">
                    <button id="filterData" class="btn btn-primary btn-sm">Filtrar</button>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <h5>Gráfico pieChart</h5>
            <div id="quantidade-eventos" class="chartDiv"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h5>Gráfico chartXY</h5>
            <div id="todos-eventos" class="chartDiv"></div>
        </div>
    </div>

@stop

@section('css')

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">

    <style>
        .chartDiv {
            width: 100%;
            height: 450px;
            border: 1px solid #b9b4b4;
            border-radius: 8px;
            margin: 10px 0;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/plugins/rangeSelector.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/lang/pt_BR.js"></script>

    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // START CHART's

            pieChart('quantidade-eventos', 'quantidade-eventos', 'quantidade', 'tipo_evento');

            chartXY('todos-eventos', 'todos-eventos', 'quantidade', 'tipo_evento');

            // END

            verificarStatus();

            setInterval(verificarStatus, 30000);

            $('#filterData').click(function() {

                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;

                pieChart('quantidade-eventos', 'quantidade-eventos?start-date=' + startDate + '&end-date=' +
                    endDate, 'quantidade', 'tipo_evento');

                chartXY('todos-eventos', 'todos-eventos?start-date=' + startDate + '&end-date=' + endDate,
                    'quantidade', 'tipo_evento');

            });

            async function chartXY(el, endpoint, value, category) {

                am4core.useTheme(am4themes_animated);

                var chart = am4core.create(el, am4charts.XYChart);

                hideCopyright();

                chart.marginRight = 400;

                var data = await fetchData('/chart-dashboard/' + endpoint);

                chart.data = data;

                // Create axes
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = category;
                categoryAxis.title.text = "Eventos";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.minGridDistance = 20;
                categoryAxis.renderer.labels.template.rotation = -90;

                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.title.text = "Quantidade";

                // Create series
                var series = chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.valueY = value;
                series.dataFields.categoryX = category;
                series.name = "Quantidade";
                series.tooltipText = "{name}: [bold]{valueY}[/]";

                // Add cursor
                chart.cursor = new am4charts.XYCursor();
            }

            async function pieChart(el, endpoint, value, category) {

                var chart = am4core.create(el, am4charts.PieChart);

                hideCopyright();

                dados = await fetchData('/chart-dashboard/' + endpoint);

                chart.data = dados;

                var pieSeries = chart.series.push(new am4charts.PieSeries());
                pieSeries.dataFields.value = value;
                pieSeries.dataFields.category = category;

                // Add a legend
                chart.legend = new am4charts.Legend();

            }

            async function fetchData(endpoint) {
                try {
                    const response = await fetch(endpoint);
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Erro ao buscar dados:', error);
                    throw error;
                }
            }

            function hideCopyright() {
                $('g:has(> g[stroke="#3cabff"])').hide();
            }

            function verificarStatus() {

                fetch('api/machine-on')
                    .then(response => response.json())
                    .then(data => {
                        $('#status-maquina').bootstrapToggle('enable');
                        $('#status-maquina').bootstrapToggle(data.on_line == 1 ? 'on' : 'off');
                        $('#nome-maquina').html(data.nome_maquina);
                        $('#status-maquina').bootstrapToggle('disable');
                    })
                    .catch(error => {
                        console.error('Erro ao consultar o endpoint:', error);
                    });
            }

        });
    </script>

@stop
