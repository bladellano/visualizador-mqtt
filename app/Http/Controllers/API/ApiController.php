<?php

namespace App\Http\Controllers\API;

ini_set('memory_limit', '-1');

use stdClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function eventDetails($id, $type)
    {

        $sql = "
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
                    ) history WHERE history.id = {$id}";

        $event = DB::connection('meraki_mqtt')->select($sql);

        $event = array_map(function ($item) {
            $item->value = utf8_encode($item->value); //? Remove caracteres indesejaveis.
            return $item;
        }, $event);

        $event = (array)$event[0];

        $event['details'] = \App\Classes\SubTopicos::combineKeysValues($event, $type);
        $event['event'] = \App\Classes\SubTopicos::obterInformacoes($event['tipo_evento']);

        return response()->json($event);
    }

    public function getRegistersByEvents(Request $request)
    {
        $where = " WHERE TRUE ";
        $where_ = $where;

        #dd($request);

        if (isset($request->type_event) && !empty($request->type_event)) 
            $where .= " AND finally.tipo_evento =  '" . base64_decode($request->type_event) . "'";

        if (isset($request->id) && !empty($request->id))
            $where .= " AND last_history.id = {$request->id}";

        if ((isset($request->start_date) && !empty($request->start_date)) && isset($request->end_date) && !empty($request->end_date))
            $where_ .= " AND history.data_maquina BETWEEN '$request->start_date' AND '$request->end_date'";

        $sql = "
            SELECT last_history.id, finally.tipo_evento, last_history.value, last_history.ts, STR_TO_DATE(SUBSTRING_INDEX(last_history.value, ';', 1), '%d/%m/%Y - %H:%i') AS data_maquina 
                FROM (
                    SELECT
                        history.*,
                        IF(TIMESTAMPDIFF(MINUTE, history.data_maquina, NOW()) > 200000, '0', '1') AS on_line,
                        MAX(history.id) AS max_history_id
                    FROM (
                        SELECT
                            mhv.*,
                            SUBSTRING_INDEX(topic, '/', 1) AS nome_maquina,
                            STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i') AS data_maquina,
                            CONCAT(YEAR(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 
                            '-', LPAD(MONTH(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 2, '0')) AS ano_mes,
                            SUBSTRING_INDEX(topic, '/', -1) AS tipo_evento
                        FROM
                            mqtt_history_view mhv
                    ) history
                    $where_
                    GROUP BY history.tipo_evento
                ) finally 
                INNER JOIN mqtt_history_view last_history ON last_history.id = finally.max_history_id $where";

        #echo '<pre>utf8_encode($sql)<br />'; print_r(utf8_encode($sql)); echo '</pre>'; die;

        $records = DB::connection('meraki_mqtt')->select(utf8_encode($sql));

        $records = array_map(function ($item) {
            $item->value = utf8_encode($item->value); //? Remove caracteres indesejaveis.
            $item->event = \App\Classes\SubTopicos::obterInformacoes($item->tipo_evento);
            return $item;
        }, $records);

        return response()->json($records);
    }

    public function isMachineOn()
    {
        $sql = "
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

        $record = DB::connection('meraki_mqtt')->select($sql);
        $record = current($record);

        return response()->json($record);
    }

    public function reportDataTables()
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

        if (isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])) {
            $WHERE .= " AND DATE(history.data_maquina) BETWEEN '" . $_REQUEST['filter']['start-date'] . "' AND '" . $_REQUEST['filter']['end-date'] . "' ";
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
            $status = !empty($info['tipo']) ? \App\Classes\SubTopicos::generateStatus($info['tipo'], $item) : "";

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
