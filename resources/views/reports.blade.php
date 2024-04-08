@extends('adminlte::page')

@section('title', 'Eventos')

@section('content_header')
    <h1>Eventos</h1>
@stop

@section('content')

    <table id="reports" class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Data e Hora</th>
                <th scope="col">Máquina</th>
                <th scope="col">Sub Tópico</th>
                <th scope="col">Hora Máquina</th>
                <th scope="col">Mensagem/Status</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($items as $item)
                <tr>
                    <th scope="row">{{ $item['id'] }}</th>
                    <td>{{ $item['ts'] }}</td>
                    <td>{{ $item['maquina'] }}</td>
                    <td>{!! $item['subtopic'] !!}</td>
                    <td>{{ $item['horamaquina'] }}</td>
                    <td>{!! $item['status'] !!}</td>
                </tr>
            @endforeach

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
        $(document).ready(function() {
            $('#reports').DataTable({
                "order": [[ 0, "desc" ]],
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
        });
    </script>
@stop
