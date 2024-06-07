document.addEventListener('DOMContentLoaded', function () {

    var categories = ['PAROU', 'PRODUZINDO'];
    var selector = 'chart-situacao-producao';
    var typeEvent = 'U2l0dWHDp8OjbyBQcm9kdcOnw6Nv';
    var titleChart = 'Situação Produção';

    //? Start
    (async () => {

        try {

            const params = $('#form-filter').serialize();

            const data = await fetchData('/api/get-events' + `?${params}&type_event=${typeEvent}`);

            createStateChart(selector, data, titleChart, categories);

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
                const data = await fetchData('/api/get-events' + `?${params}&type_event=${typeEvent}`);
                createStateChart(selector, data, titleChart, categories);
            } catch (err) {
                Swal.fire('Erro', err.message, 'error');
            }

        })();

    });

});
