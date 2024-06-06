
function createStateChart(_sElement, _aData, _sTitle, _aCategories) {

    const chartData = [];

    _aData.forEach((entry, index, arr) => {

        const dateISO8601 = convertToISO8601(entry.data_maquina);
        const currDate = new Date(dateISO8601);

        chartData.push([dateISO8601, 1]);

        const nextEntry = arr[index + 1];

        if (nextEntry) {
            const nextDate = new Date(nextEntry.data_maquina);
            const diffMinutes = (nextDate - currDate) / 1000 / 60;
            if (diffMinutes > 2) {
                const offDate = new Date(currDate);

                offDate.setMinutes(offDate.getMinutes() + 1);

                chartData.push([offDate.toISOString(), 0]);
            }
        }
    });

    Highcharts.chart(_sElement, {
        title: {
            text: _sTitle,
            align: 'center'
        },
        yAxis: {
            title: {
                text: 'Status'
            },
            categories: _aCategories,
        },
        xAxis: {
            type: 'datetime',
        },
        series: [{
            name: 'Tempo',
            data: chartData.map(point => [Date.parse(point[0]), point[1]]),
            step: 'left',
            marker: {
                enabled: false
            }
        }]
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

        return hasContent(data);

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
