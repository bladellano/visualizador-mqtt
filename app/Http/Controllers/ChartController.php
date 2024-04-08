<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{

    public function quantidadeEventos(Request $request)
    {

        $where = " WHERE TRUE ";

        if ($request->all())
            $where .= " AND DATE(ts) BETWEEN '" . $request->get('start-date') . "' AND '" . $request->get('end-date') . "' ";

        $items = DB::connection('meraki_mqtt')->select('
            SELECT 
                id,
                ts,
                ts_last,
                topic,
                SUBSTRING_INDEX(topic, \'/\', 1) AS nome_maquina,
                SUBSTRING_INDEX(topic, \'/\', -1) AS tipo_evento,
                value,
                COUNT(*) AS quantidade
            FROM 
                mqtt_history_view
           ' . $where . '
        GROUP BY tipo_evento');

        $items = array_map(function ($item) {

            return [
                'quantidade' => $item->quantidade,
                'tipo_evento' => $item->tipo_evento,
                'nome_maquina' => $item->nome_maquina,
            ];
        }, $items);

        return response()->json($items);
    }

    public function todosEventos(Request $request)
    {
        $where = " AND ts >= SUBDATE(CURDATE(), 30)";

        if ($request->all())
            $where = " AND DATE(ts) BETWEEN '" . $request->get('start-date') . "' AND '" . $request->get('end-date') . "' ";

        $items = DB::connection('meraki_mqtt')->select("
            SELECT  
            SUBSTRING_INDEX(topic, '/', 1) AS nome_maquina,
            SUBSTRING_INDEX(topic, '/', -1) AS tipo_evento,
            COUNT(*) AS quantidade
            FROM 
                mqtt_history_view
            WHERE TRUE $where
            GROUP BY tipo_evento");

        $items = array_map(function ($item) {
            $item = (array)$item;
            return $item;
        }, $items);

        return response()->json($items);
    }
}
