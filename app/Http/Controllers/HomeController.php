<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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

    public function reports()
    {

        $items = DB::connection('meraki_mqtt')->select('SELECT * FROM mqtt_history_view WHERE ts >= SUBDATE(CURDATE(), 5)');

        $items = array_map(function ($item) {

            $explodeTopic = explode('/', $item->topic);
            $explodeValue = explode(';', $item->value);

            $ts = Carbon::parse($item->ts)->format('d/m/Y H:i:s');

            $subtopico = trim($explodeTopic[1]);

            $info = \App\Classes\SubTopicos::obterInformacoes($subtopico);
            $status = \App\Classes\SubTopicos::gerarStatus(@$info['tipo'], $item);

            return [
                'id' => $item->id,
                'ts' => $ts,
                'ts_last' => $item->ts_last,
                'topic' => $item->topic,
                'value' => $item->value,
                'maquina' => $explodeTopic[0],
                'subtopic' => "<span class='text-uppercase badge " . @$info['classe'] . "'>" . $subtopico . "</span>",
                'subtopic_raw' => $subtopico,
                'horamaquina' => $explodeValue[0],
                'status' => $status
            ];
        }, $items);

        #dd($items);

        return view('reports', compact('items'));
    }

    public function evento($id, $tipo)
    {

        $evento = DB::connection('meraki_mqtt')->select('
            SELECT id,
            ts,
            ts_last,
            topic,
            STR_TO_DATE(SUBSTRING_INDEX(value, \';\', 1), \'%d/%m/%Y - %H:%i\') AS data_maquina,
            SUBSTRING_INDEX(topic, \'/\', 1) AS nome_maquina,
            SUBSTRING_INDEX(topic, \'/\', -1) AS tipo_evento,
            value FROM mqtt_history_view WHERE id = ' . $id);

        $ts = Carbon::parse($evento[0]->ts)->format('d/m/Y H:i:s');

        $evento = (array)$evento[0];
        $evento['ts'] =  $ts;

        $evento['eventos'] = explode(";", $evento['value']);
        unset($evento['eventos'][0]); //? Removendo a data.

        return view('evento', compact('evento'));
    }
}
