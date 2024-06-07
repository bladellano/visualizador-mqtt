
function createStateChart(_sElement, _aData, _sTitle, _aCategories) {

    const chartData = [];

    _aData.forEach((entry, index, arr) => {

        const dateISO8601 = convertToISO8601(entry.data_maquina);
        const currDate = new Date(dateISO8601);

        chartData.push({
            x:Date.parse(dateISO8601),
            y:1,
            message:entry.mensagem
        });

        const nextEntry = arr[index + 1];

        if (nextEntry) {
            const nextDate = new Date(nextEntry.data_maquina);
            const diffMinutes = (nextDate - currDate) / 1000 / 60;
            if (diffMinutes > 2) {
                const offDate = new Date(currDate);

                offDate.setMinutes(offDate.getMinutes() + 1);

                chartData.push({
                    x:Date.parse(offDate.toISOString()),
                    y:0,
                    message:''
                });
            }
        }
    });

    Highcharts.chart(_sElement, {
         //? chart: { type: 'line' }, // Tipo de graficos - bar/pie/line(default).
        title: {
            text: _sTitle,
            align: 'center'
        },
        yAxis: {
            title: {
                text: 'Status'
            },
            categories: _aCategories,
            //? reversed: true // Pode ser preciso inverter.
        },
        xAxis: {
            type: 'datetime',
        },
        series: [{
            name: 'Tempo',
            data: chartData,
            step: 'left',
            marker: {
                enabled: false
            }
            //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
        }],
        tooltip: {
            formatter: function () {
                return `<b>${Highcharts.dateFormat('%d/%m/%Y %H:%M:%S', this.x)}</b><br/> Status: ${this.point.message}`;
            }
        }
    });

    hideCopyright();

}

// FUNCTIONS
function convertToISO8601(dateString) {
    return dateString.replace(' ', 'T') + 'Z';
}

function hideCopyright() {
    document.querySelector('.highcharts-credits').remove();
}

async function fetchData(endpoint) {
    try {
        const response = await fetch(endpoint);
        const data = await response.json();

        //? return hasContent(data); Caso queira usar Exception.
        return data;

    } catch (error) {
        throw error;
    }
}

/** Verifica se é um objeto ou array e retorna o 'data' */
function hasContent(data) {
    if (Array.isArray(data)) {
        if (!data.length)
            throw new Error("Nenhum resultado foi encontrado.");
        return data;
    } else if (data instanceof Object) {
        return data;
    } else {
        throw new Error("Nenhum resultado foi encontrado.");
    }
}

function chartLoading(element) {
    $(`#${element}`).html('<i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>');
}
