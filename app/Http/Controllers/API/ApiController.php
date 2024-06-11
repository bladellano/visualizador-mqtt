<?php

namespace App\Http\Controllers\API;

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

use stdClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{

    const ATTRIBUTES_DADOS_MOTOR_DIESEL = [
        "Pressão Óleo Motor (Bar)" => 1,
        "Pressão Turbina (Bar)" => 2,
        "Temperatura Motor (ºC)" => 3,
        "Temperatura Turbina (ºC)" => 4,
        "Percentual Torque (%)" => 5,
        "Rotação (RPM)" => 6,
        "Tensão Bateria (V)" => 7,
        "Media Consumo Geral (L/H)" => 8,
        "Media Consumo Picando (L/H)" => 9,
        "Media Consumo Desde a última Partida (L/H)" => 10
    ];

    public function getEventsTenAttributes(Request $request)
    {
        $where = " WHERE TRUE ";

        if (!empty($request->closed_period)) 
            $where .= " AND mhv.ts >= CURDATE() - INTERVAL {$request->closed_period} DAY";
        else 
            $where .= " AND mhv.ts >= CURDATE() - INTERVAL 1 DAY";

        $SQL = "
            SELECT 
                mhv.id, mhv.value,
                SUBSTR(mhv.topic, LOCATE('/', mhv.topic) + 1) AS evento,
                STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i') AS data_maquina
                FROM mqtt_history_view mhv 
                    $where  
                    AND SUBSTR(topic, LOCATE('/', topic) + 1) IN ('Dados Motor Diesel')";

        $record = DB::connection('meraki_mqtt')->select($SQL);

        $new = [];

        foreach(self::ATTRIBUTES_DADOS_MOTOR_DIESEL as $name => $number) {

            foreach($record as $r) {

                $attributes = explode(";", $r->value);

                $new[$name][] = [
                    'id' => $r->id,
                    'value' => $r->value,
                    'evento' => $r->evento,
                    'data_maquina' => $r->data_maquina,
                    'name_attribute' => $name,
                    'v_attribute' => $attributes[$number],
                ];

            }

        }

        return response()->json($new);

    }

    private static function dateMachine()
    {
        return " STR_TO_DATE(SUBSTRING_INDEX(value, ';', 1), '%d/%m/%Y - %H:%i') ";
    }

    public function getEvents(Request $request)
    {

        $where = " WHERE TRUE ";

        if (isset($request->type_event) && !empty($request->type_event))
            $where .= " AND SUBSTR(topic, LOCATE('/', topic) + 1) IN ('" . base64_decode($request->type_event) . "') ";

        if (!empty($request->closed_period)) {
            $where .= " AND mhv.ts >= CURDATE() - INTERVAL {$request->closed_period} DAY";
        } else {
            $where .= " AND mhv.ts >= CURDATE() - INTERVAL 1 DAY";
        }

        $SQL = "
            SELECT
                mhv.id, mhv.ts,
                " . self::dateMachine() . " AS data_maquina,
                SUBSTRING_INDEX(value, ';', -1) AS mensagem
                FROM
                    mqtt_history_view mhv  
                        $where
                        ORDER BY " . self::dateMachine();

        $record = DB::connection('meraki_mqtt')->select($SQL);

        $record = array_map(function ($item) {
            $item->mensagem = \App\Classes\Helper::removeUnwantedCharacters($item->mensagem); //? Remove caracteres indesejaveis.
            return $item;
        }, $record);

         // Converte a data no formato ISO 8601
         $convertToISO8601 = function ($dateString) {
            $date = new \DateTime($dateString);
            return $date->format(\DateTime::ATOM);
        };

        $chartData = [];
        
        foreach ($record as $index => $entry) {
            $dateISO8601 = $convertToISO8601($entry->data_maquina);
            $currDate = new \DateTime($dateISO8601);
            
            $chartData[] = [
                'x' => strtotime($dateISO8601) * 1000,
                'y' => 1,
                'message' => $entry->mensagem
            ];

            $nextEntry = $record[$index + 1] ?? NULL;

            if ($nextEntry) {
                $nextDate = new \DateTime($nextEntry->data_maquina);
                $diffMinutes = ($nextDate->getTimestamp() - $currDate->getTimestamp()) / 60;
                
                if ($diffMinutes > 2) {
                    $offDate = clone $currDate;
                    $offDate->modify('+1 minute');
                    
                    $chartData[] = [
                        'x' => $offDate->getTimestamp() * 1000,
                        'y' => 0,
                        'message' => ''
                    ];
                }
            }
        }

        return response()->json($chartData);
    }

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
            $where .= " AND SUBSTR(topic, LOCATE('/', topic) + 1) IN (" . \App\Classes\Helper::_implode($request->type_event, true) . ")";

        if (isset($request->id) && !empty($request->id))
            $where .= " AND id = {$request->id}";

        if ((isset($request->start_date) && !empty($request->start_date)) && isset($request->end_date) && !empty($request->end_date)) {

            $where .= " AND mhv.ts BETWEEN '$request->start_date' AND '$request->end_date'";
        } else if (isset($request->closed_period) && !empty($request->closed_period)) {

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
            $where .= " AND SUBSTR(topic, LOCATE('/', topic) + 1) IN (" . \App\Classes\Helper::_implode($request->type_event, true) . ")";

        if ((isset($request->start_date) && !empty($request->start_date)) && isset($request->end_date) && !empty($request->end_date)) {

            $where .= " AND mhv.ts BETWEEN '$request->start_date' AND '$request->end_date'";
        } else if (isset($request->closed_period) && !empty($request->closed_period)) {

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
                WHEN finally.tipo_evento IN ('Maquina On Line','Horimetro Esteiras Locomoção','Horimetro Motor Diesel') 
                    THEN CONCAT(finally.tipo_evento, ': ', finally.mensagem)
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

        $groupBy = ' GROUP BY finally.tipo_evento, DATE(finally.ts) ';

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
            $WHERE .= " AND DATE(history.data_maquina) BETWEEN '" . $_REQUEST['filter']['start_date'] . "' AND '" . $_REQUEST['filter']['end_date'] . "' ";
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
