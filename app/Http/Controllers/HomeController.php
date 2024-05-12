<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function ajuda()
    {
        return view('ajuda');
    }

    public function chart($slug)
    {
        return view($slug);
    }

    public function indicators()
    {
        $menu = \App\Classes\Indicadores::MENU;
        $hashMenu = \App\Classes\Indicadores::convertArrayToBase64($menu);

        $merged = [];

        foreach ($hashMenu as $key => $value) {

            $decodedValue = $value;

            if (array_key_exists($key, $menu)) {
                $merged[$key] = [
                    'base64' => $decodedValue,
                    'view' => $menu[$key]
                ];
            }
        }

        $menu = $merged;

        return view('indicators', compact('menu'));
    }

    public function reports()
    {
        return view('reports');
    }

    public function evento($id, $tipo)
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
                    ) history WHERE history.id = {$id}
                ";

        $evento = DB::connection('meraki_mqtt')->select($sql);
        $evento = (array)$evento[0];
        $evento['ts'] = Carbon::parse($evento['ts'])->format('d/m/Y H:i:s');

        $evento['eventos'] = \App\Classes\SubTopicos::combineKeysValues($evento, $tipo);

        return view('evento', compact('evento'));
    }
}
