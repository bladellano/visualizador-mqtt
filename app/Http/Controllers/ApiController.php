<?php

namespace App\Http\Controllers;

ini_set('memory_limit', '-1');

use stdClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function maquinaOnline()
    {
        $query = "
                SELECT
                history.*,
                IF(TIMESTAMPDIFF(MINUTE, history.data_maquina, NOW()) > 2, '0', '1') AS on_line
            FROM
                (
                SELECT
                    mhv.*,
                    SUBSTRING_INDEX(topic, '/', 1) AS nome_maquina,
                    STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1),
                    '%d/%m/%Y - %H:%i') AS data_maquina,
                    CONCAT(YEAR(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 
                        '-', LPAD(MONTH(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 2, '0')) AS ano_mes,
                    SUBSTRING_INDEX(topic, '/', -1) AS tipo_evento
                FROM
                    mqtt_history_view mhv 	
                    ) history
            WHERE
                TRUE
                AND tipo_evento = 'Maquina On Line'
            ORDER BY
                history.id desc
            LIMIT 1";

        $retorno = DB::connection('meraki_mqtt')->select($query);
        $retorno = current($retorno);

        return response()->json($retorno);
    }

    public function reports()
    {
        $length =  $_REQUEST['length'];
        $start =  $_REQUEST["start"];
        $searchValue =  $_REQUEST['search']['value'];
        $draw =  $_REQUEST["draw"];
        $order =  $_REQUEST['order'][0];
    
        $WHERE = " WHERE TRUE ";

        if (!empty($searchValue)) :
            $WHERE .= " AND history.ano_mes LIKE '" . $searchValue . "%' ";
            $WHERE .= " OR history.nome_maquina LIKE '" . $searchValue . "%' ";
            $WHERE .= " OR history.tipo_evento LIKE '" . $searchValue . "%' ";
        endif;

        if(isset( $_REQUEST['filter']) && !empty($_REQUEST['filter'])) {
            $WHERE .= " AND DATE(history.data_maquina) BETWEEN '". $_REQUEST['filter']['start-date']."' AND '". $_REQUEST['filter']['end-date']."' ";
        }

        $SQL = "
            SELECT
                history.*,
                IF(TIMESTAMPDIFF(MINUTE, history.data_maquina, NOW()) > 2, '0', '1') AS on_line
            FROM
                (
                SELECT
                    mhv.*,
                    SUBSTRING_INDEX(topic, '/', 1) AS nome_maquina,
                    STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1),
                    '%d/%m/%Y - %H:%i') AS data_maquina,
                    CONCAT(YEAR(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 
                        '-', LPAD(MONTH(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 2, '0')) AS ano_mes,
                    SUBSTRING_INDEX(topic, '/', -1) AS tipo_evento
                FROM
                    mqtt_history_view mhv 	
                    ) history
         ";

        $SQL .= $WHERE;

        $SQL_TOTAL = $SQL;

        $LIMIT = " LIMIT " . $start . "," . $length;

        $SQL .= " ORDER BY " . ($order['column'] + 1) . " " . strtoupper($order['dir']);

        $SQL .= $LIMIT;

        $records = DB::connection('meraki_mqtt')->select($SQL);

        $records = array_map(function ($item) {

            $info = \App\Classes\SubTopicos::obterInformacoes($item->tipo_evento);
            $status = !empty($info['tipo']) ? \App\Classes\SubTopicos::gerarStatus($info['tipo'], $item) : "";

            $newItem = new stdClass();
            $newItem->id = $item->id;
            $newItem->ts = Carbon::parse($item->ts)->format('d/m/Y H:i:s');
            $newItem->topic = $item->topic;
            $newItem->nome_maquina = $item->nome_maquina;
            $newItem->data_maquina = Carbon::parse($item->data_maquina)->format('d/m/Y H:i:s');
            $newItem->tipo_evento = "<span class='text-uppercase badge " . $info['classe'] . "'>" . $item->tipo_evento . "</span>";
            $newItem->status = $status;

            return $newItem;

        }, $records);

        $totalRecords  = count(DB::connection('meraki_mqtt')->select($SQL_TOTAL));

        $response = [];
        $response["draw"] = intval($draw);
        $response["recordsTotal"] = $totalRecords;
        $response["recordsFiltered"] = $totalRecords;
        $response["data"] = $records;

        return $response;
    }
}
