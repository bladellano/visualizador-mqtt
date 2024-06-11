document.addEventListener('DOMContentLoaded', function () {

    var selector = 'chart-dados-motor-diesel';
    var titleChart = 'Dados Motor Diesel';

    //? Start
    (async () => {

        try {

            const params = $('#form-filter').serialize();

            const data = await fetchData('/api/get-events-ten-attributes' + `?${params}`);

            createStateChartManyMessages(selector, data, titleChart);

        } catch (err) {

            Swal.fire('Erro', err.message, 'error');
        }

    })();

    $('#form-filter').submit(function (e) {

        e.preventDefault();

        const params = $(e.target).serialize();
        const qs = Object.fromEntries(new URLSearchParams(params));

        if (!qs.closed_period && (!qs.start_date || !qs.end_date))
            return Swal.fire('Erro', 'Por favor, preencha o filtro corretamente.', 'error');

        (async () => {

            chartLoading(selector);

            try {
                const data = await fetchData('/api/get-events-ten-attributes' + `?${params}`);
                createStateChartManyMessages(selector, data, titleChart);
            } catch (err) {
                Swal.fire('Erro', err.message, 'error');
            }

        })();

    });

});

function convertChartData(_data) {

    const chartData = [];

    _data.forEach((entry, index, arr) => {

        const dateISO8601 = convertToISO8601(entry.data_maquina);
        const currDate = new Date(dateISO8601);

        chartData.push({
            x: Date.parse(dateISO8601),
            y: +entry.v_attribute,
        });

        const nextEntry = arr[index + 1];

        if (nextEntry) {
            const nextDate = new Date(nextEntry.data_maquina);
            const diffMinutes = (nextDate - currDate) / 1000 / 60;
            if (diffMinutes > 2) {
                const offDate = new Date(currDate);

                offDate.setMinutes(offDate.getMinutes() + 1);

                chartData.push({
                    x: Date.parse(offDate.toISOString()),
                    y: 0,
                });
            }
        }
    });

    return chartData;
}

function createStateChartManyMessages(_sSelector, _aData, _sTitle) {

    const seriePressaoOleMotor = convertChartData(_aData['Pressão Óleo Motor (Bar)']);
    const serieTemperaturaMotor = convertChartData(_aData['Temperatura Motor (ºC)']);
    const seriePercentualTorque = convertChartData(_aData['Percentual Torque (%)']);
    const seriePressaoTurbina = convertChartData(_aData['Pressão Turbina (Bar)']);
    const serieTensaoBateria = convertChartData(_aData['Tensão Bateria (V)']);
    const serieTemperaturaTurbina = convertChartData(_aData['Temperatura Turbina (ºC)']);
    const serieRotacao = convertChartData(_aData['Rotação (RPM)']);
    const serieMediaConsumoGeral = convertChartData(_aData['Media Consumo Geral (L/H)']);
    const serieMediaConsumoPicando = convertChartData(_aData['Media Consumo Picando (L/H)']);
    const serieMediaConsumoDesdeUltimaPartida = convertChartData(_aData['Media Consumo Desde a última Partida (L/H)']);

    Highcharts.chart(_sSelector, {
        chart: {
            zooming: {
                type: 'x'
            }
        },
        boost: {
            useGPUTranslations: true,
            usePreAllocated: true
        },
        title: {
            text: _sTitle,
            align: 'center'
        },
        yAxis: {
            title: {
                text: 'Status'
            },
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
        xAxis: {
            type: 'datetime',
            endOnTick: true,
            startOnTick: true,
        },
        series: [
            {
                name: 'Pressão Óleo Motor (Bar)',
                data: seriePressaoOleMotor.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Temperatura Motor (ºC)',
                data: serieTemperaturaMotor.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Percentual Torque (%)',
                data: seriePercentualTorque.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Pressão Turbina (Bar)',
                data: seriePressaoTurbina.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Tensão Bateria (V)',
                data: serieTensaoBateria.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Temperatura Turbina (ºC)',
                data: serieTemperaturaTurbina.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Rotação (RPM)',
                data: serieRotacao.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Media Consumo Geral (L/H)',
                data: serieMediaConsumoGeral.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Media Consumo Picando (L/H)',
                data: serieMediaConsumoPicando.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            },
            {
                name: 'Media Consumo Desde a última Partida (L/H)',
                data: serieMediaConsumoDesdeUltimaPartida.map(e => [e.x, e.y]),
                step: 'left',
                boostThreshold: 1000,
                marker: {
                    enabled: false
                },
                //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
                boostThreshold: 115000 // Ativar boost para mais de 15000 pontos
            }
        ]
    });

    hideCopyright();

}
