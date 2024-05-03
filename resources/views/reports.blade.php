@extends('adminlte::page')

@section('title', config('app.name') . ' | Eventos')

@section('content_header')
    <h1>Eventos</h1>
@stop

@section('preloader')
    <i class="fas fa-4x fa-spin fa-spinner text-secondary"></i>
    <h4 class="mt-4 text-dark">Por favor, aguarde enquanto consultamos o banco de dados MQTT.</h4>
@stop

@section('content')

    <div>
        <div class="row">
            <div class="col-md-6">
                <form action="#" id="formFilterReports">
                    <label for="">Período:</label>
                    <input type="date" name="start-date" id="start-date">
                    <input type="date" name="end-date" id="end-date">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                </form>
            </div>
        </div>
    </div>
    <hr/>
    <table id="reports" class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Data e Hora</th>
                <th scope="col">Máquina</th>
                <th scope="col">Sub Tópico</th>
                <th scope="col">Data Máquina</th>
                <th scope="col">Mensagem/Status</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.min.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            $('#formFilterReports').submit(function(e) {

                e.preventDefault();

                const params = $(e.target).serialize();
                const qs = Object.fromEntries(new URLSearchParams(params));

                if(!Object.values(qs).every(v => v !== ''))
                    return alert('Por favor, preencha o filtro corretamente.')

                table.destroy();

                buildReport({
                    filter: qs
                });

            });

            buildReport();

            function buildReport(data = {}) {

                table = $('#reports').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": "api/reports-datatables",
                        "type": "GET",
                        "data": data
                    },
                    "order": [
                        [0, "desc"]
                    ],
                    "columns": [{
                            data: "id"
                        },
                        {
                            data: "ts"
                        },
                        {
                            data: "nome_maquina"
                        },
                        {
                            data: "tipo_evento"
                        },
                        {
                            data: "data_maquina"
                        },
                        {
                            data: "status"
                        }
                    ],
                    "language": {
                        "sEmptyTable": "Nenhum registro encontrado",
                        "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                        "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                        "sInfoPostFix": "",
                        "sInfoThousands": ".",
                        "sLengthMenu": "_MENU_ resultados por página",
                        "sLoadingRecords": "Carregando...",
                        "sProcessing": "Processando...",
                        "sZeroRecords": "Nenhum registro encontrado",
                        "sSearch": "Pesquisar",
                        "oPaginate": {
                            "sNext": "Próximo",
                            "sPrevious": "Anterior",
                            "sFirst": "Primeiro",
                            "sLast": "Último"
                        },
                        "oAria": {
                            "sSortAscending": ": Ordenar colunas de forma ascendente",
                            "sSortDescending": ": Ordenar colunas de forma descendente"
                        },
                        "select": {
                            "rows": {
                                "_": "Selecionado %d linhas",
                                "0": "Nenhuma linha selecionada",
                                "1": "Selecionado 1 linha"
                            }
                        }
                    }
                });

            }

            //End
        });
    </script>
@stop
