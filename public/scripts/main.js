
function createStateChart(_sElement, _aData, _sTitle, _aCategories) {

    Highcharts.chart(_sElement, {
         //? chart: { type: 'line' }, // Tipo de graficos - bar/pie/spline/line(default).
        boost: {
            useGPUTranslations: true,
            usePreAllocated: true
        },
        chart: {
            zooming: {
                type: 'x'
            }
        },
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
            data: _aData.map(point => [point.x, point.y] ),
            step: 'left',
            marker: {
                enabled: false
            },
            //? dataLabels: { enabled: true } // Exibe os pontos nas linhas.
            boostThreshold: 15000 // Ativar boost para mais de 15000 pontos
        }],
        tooltip: {
            formatter: function () {
                return `<b>${Highcharts.dateFormat('%d/%m/%Y %H:%M:%S', this.x)}</b><br/> Status: ${this.point.y}`;
                //! return `<b>${Highcharts.dateFormat('%d/%m/%Y %H:%M:%S', this.x)}</b><br/> Status: ${this.point.message}`;
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
