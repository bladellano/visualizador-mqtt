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
                    mqtt_history_view mhv WHERE mhv.id = {$id}
                    ) history";

        $event = DB::connection('meraki_mqtt')->select($sql);

        $event = array_map(function ($item) {
            $item->value = \App\Classes\Helper::removeUnwantedCharacters($item->value); //? Remove caracteres indesejaveis.
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

        if (isset($request->type_event) && !empty($request->type_event))
            $where .= " AND SUBSTR(topic, LOCATE('/', topic) + 1) = '" . base64_decode($request->type_event) . "'";

        if (isset($request->id) && !empty($request->id))
            $where .= " AND id = {$request->id}";

        if ((isset($request->start_date) && !empty($request->start_date)) && isset($request->end_date) && !empty($request->end_date)) {

            $where .= " AND mhv.ts BETWEEN '$request->start_date' AND '$request->end_date'";

        } else if(isset($request->closed_period) && !empty($request->closed_period)) {

            $where .= " AND mhv.ts >= CURDATE() - INTERVAL {$request->closed_period} DAY";

        } else {
            
            $where .= " AND mhv.ts >= CURDATE() - INTERVAL 90 DAY";
        }

        $sql = "
            SELECT 
            finally.id, 
            finally.tipo_evento, 
            finally.value, 
            finally.mensagem, 
            finally.ts, 
            DATE_FORMAT(finally.ts, '%d/%m/%Y %H:%i:%s') AS ts_formatada,
            finally.data_maquina 
                FROM (
                    SELECT
                        history.*,
                        IF(TIMESTAMPDIFF(MINUTE, history.data_maquina, NOW()) > 200000, '0', '1') AS on_line
                    FROM (
                        SELECT
                            mhv.*,
                            SUBSTRING_INDEX(topic, '/', 1) AS nome_maquina,
                            STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i') AS data_maquina,
                            SUBSTRING_INDEX(value, ';', -1) AS mensagem,
                            CONCAT(YEAR(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 
                            '-', LPAD(MONTH(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 2, '0')) AS ano_mes,
                            SUBSTRING_INDEX(topic, '/', -1) AS tipo_evento
                        FROM
                            mqtt_history_view mhv $where
                    ) history
                ) finally ";

        $orderBy = ' ORDER BY finally.id DESC ';

        $query = $sql . $orderBy;

        $records = DB::connection('meraki_mqtt')->select($query);

        $records = array_map(function ($item) {
            $item->value = \App\Classes\Helper::removeUnwantedCharacters($item->value);
            $item->event = \App\Classes\SubTopicos::obterInformacoes($item->tipo_evento);
            return $item;
        }, $records);

        if (isset($request->group_by_message) && $request->group_by_message == 1) {
            $collection = collect($records);
            $grouped = $collection->groupBy('mensagem');
            $records = $grouped->toArray();
        }

        return response()->json($records);
    }

    public function getMessageOneValue(Request $request)
    {

        $where = " WHERE TRUE ";

        if (isset($request->type_event) && !empty($request->type_event))
            $where .= " AND SUBSTR(topic, LOCATE('/', topic) + 1) = '" . base64_decode($request->type_event) . "'";

        if ((isset($request->start_date) && !empty($request->start_date)) && isset($request->end_date) && !empty($request->end_date)) {

            $where .= " AND mhv.ts BETWEEN '$request->start_date' AND '$request->end_date'";

        } else if(isset($request->closed_period) && !empty($request->closed_period)) {

            $where .= " AND mhv.ts >= CURDATE() - INTERVAL {$request->closed_period} DAY";

        } else {
            
            $where .= " AND mhv.ts >= CURDATE() - INTERVAL 90 DAY";
        }

        $sql = "
            SELECT 
            finally.id, 
            finally.tipo_evento, 
            finally.value, 
            CASE
                WHEN finally.tipo_evento IN ('Horimetro Esteiras Locomoção','Horimetro Motor Diesel') THEN CONCAT('Valor: ', finally.mensagem)
                ELSE finally.mensagem
            END mensagem, 
            finally.ts, 
            finally.data_maquina,
            finally.on_line,
            finally.ano_mes,     
            finally._timestamp,
            finally.ts_formated,
            count(*) as _count
                FROM (
                    SELECT
                        history.*,
                        IF(TIMESTAMPDIFF(MINUTE, history.data_maquina, NOW()) > 200000, '0', '1') AS on_line
                    FROM (
                        SELECT
                            mhv.*,
                            SUBSTRING_INDEX(topic, '/', 1) AS nome_maquina,
                            STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i') AS data_maquina,
                            SUBSTRING_INDEX(value, ';', -1) AS mensagem,
                            CONCAT(YEAR(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 
                            '-', LPAD(MONTH(STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i')), 2, '0')) AS ano_mes,
                            SUBSTRING_INDEX(topic, '/', -1) AS tipo_evento,
                            UNIX_TIMESTAMP(mhv.ts) as _timestamp,
                            DATE_FORMAT(mhv.ts, '%d/%m/%Y') AS ts_formated
                        FROM
                            mqtt_history_view mhv $where
                    ) history
                ) finally ";

        $orderBy = ' ORDER BY finally.id DESC ';

        $groupBy = ' GROUP BY DATE(finally.ts) ';

        $query = $sql .  $groupBy . $orderBy;

        $records = DB::connection('meraki_mqtt')->select($query);

        $records = array_map(function ($item) {
            $item->value = \App\Classes\Helper::removeUnwantedCharacters($item->value);
            $item->mensagem = \App\Classes\Helper::removeUnwantedCharacters($item->mensagem);
            return $item;
        }, $records);

        if (isset($request->group_by_message) && $request->group_by_message == 1) {
            $collection = collect($records);
            $grouped = $collection->groupBy('mensagem');
            $records = $grouped->toArray();
        }
        
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

        if (isset($_REQUEST['filter']) && !empty($_REQUEST['filter']))
            $WHERE .= " AND DATE(history.data_maquina) BETWEEN '" . $_REQUEST['filter']['start-date'] . "' AND '" . $_REQUEST['filter']['end-date'] . "' ";
        else
            $WHERE .= " AND history.data_maquina >= CURDATE() - INTERVAL 30 DAY ";

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
