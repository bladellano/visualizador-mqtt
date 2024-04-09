<?php

namespace App\Http\Controllers;

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
    

}
