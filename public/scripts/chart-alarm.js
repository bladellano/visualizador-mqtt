document.addEventListener('DOMContentLoaded', function () {

      //? Start
      (async () => {

        try {

            const params = $('#form-filter').serialize();
            
            const data = await fetchData('/api/get-events' + `?${params}&type_event=QWxhcm1lIEF0aXZv`);

            createStateChart('chart-alarm', data, 'Alarme', ['OFFLINE', 'ATIVO']);

        } catch (err) {

            Swal.fire('Erro', err.message, 'error');
        }

    })();

    $('#form-filter').submit(function(e) {

        e.preventDefault(); 

        const params = $(e.target).serialize();
        const qs = Object.fromEntries(new URLSearchParams(params));

        if (!qs.closed_period && (!qs.start_date || !qs.end_date))
            return Swal.fire('Erro', 'Por favor, preencha o filtro corretamente.', 'error');

        $('#loading-screen').fadeIn();

        (async () => {
            
            try {
                const data = await fetchData('/api/get-events' + `?${params}&type_event=QWxhcm1lIEF0aXZv`);
                createStateChart('chart-status', data, 'Status da Máquina', ['OFFLINE', 'ONLINE']);
            } catch (err) {
                Swal.fire('Erro', err.message, 'error');
            }

        })();

        $('#loading-screen').fadeOut();

    });

});
