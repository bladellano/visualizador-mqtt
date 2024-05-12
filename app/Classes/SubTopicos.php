<?php

namespace App\Classes;

abstract class SubTopicos
{
    protected static $subtopicos = [
        "Maquina On Line" => ["tipo" => 1, "classe" => "badge-success"],
        "Horimetro Motor Diesel" => ["tipo" => 1, "classe" => "badge-primary"],
        "Horimetro Esteiras Locomoção" => ["tipo" => 1, "classe" => "badge-primary"],
        "Alarme Ativo" => ["tipo" => 1, "classe" => "badge-primary"],
        "Produção Corrida" => ["tipo" => 3, "classe" => "badge-success"],
        "Produção Total Período" => ["tipo" => 3, "classe" => "badge-success"],
        "Produção Torque 0 a 59%" => ["tipo" => 3, "classe" => "badge-success"],
        "Produção Torque 60 a 69%" => ["tipo" => 3, "classe" => "badge-success"],
        "Produção Torque 70 a 79%" => ["tipo" => 3, "classe" => "badge-success"],
        "Produção Torque 80 a 89%" => ["tipo" => 3, "classe" => "badge-success"],
        "Produção Torque 90 a 100%" => ["tipo" => 3, "classe" => "badge-success"],
        "Ultimo Registro Antes Reset" => ["tipo" => 3, "classe" => "badge-success"],
        "Registro Produção Sem Reset" => ["tipo" => 3, "classe" => "badge-success"],
        "Dados Motor Diesel" => ["tipo" => 10, "classe" => "badge-info"],
        "Pressões Bombas" => ["tipo" => 15, "classe" => "badge-danger"],
        "Movimentação Maquina" => ["tipo" => 1, "classe" => "badge-warning"]
    ];

    protected static $atributos = [
        "15" => [
            "Avanço Rolo Superior (Bar)",
            "Recua Rolo Superior (Bar)",
            "Avanço Rolo Inferior (Bar)",
            "Recua Rolo Inferior (Bar)",
            "Avança Esteira Esquerda (Bar)",
            "Recua Esteira Esquerda (Bar)",
            "Avança Esteira Direita (Bar)",
            "Recua Esteira Direita (Bar)",
            "Bomba Carga I (Bar)",
            "Bomba Carga II (Bar)",
            "Trocador Calor I (Bar)",
            "Trocador Calor II (Bar)",
            "Transportador de Saida (Bar)",
            "Tensionador (Bar)",
            "Embreagem (Bar)"
        ],
        "10" => [
            "Pressão Óleo Motor (Bar)",
            "Pressão Turbina (Bar)",
            "Temperatura Motor (ºC)",
            "Temperatura Turbina (ºC)",
            "Percentual Torque (%)",
            "Rotação (RPM)",
            "Tensão Bateria (V)",
            "Media Consumo Geral (L/H)",
            "Media Consumo Picando (L/H)",
            "Media Consumo Desde a última Partida (L/H)"
        ],
        "3" => [
            "Tempo (ms)",
            "Media Consumo (L/H)",
            "Media Torque (%)",
        ]
    ];

    public static function combineKeysValues($evento, $tipo): array
    {
        $eventoAtributos = explode(";", $evento['value']);
        unset($eventoAtributos[0]); // Remove informacao do tipo data.

        if($tipo == 3) //! @TODO POG
            array_pop($eventoAtributos);

        if (!isset(static::$atributos[$tipo]))
            return [];

        $combine = array_combine(static::$atributos[$tipo], $eventoAtributos);

        arsort($combine);

        return $combine;
    }

    public static function obterInformacoes($subtopico)
    {
        if (array_key_exists($subtopico, static::$subtopicos))
            return static::$subtopicos[$subtopico];
        else
            return ["tipo" => 0, "classe" => "badge-secondary"];
    }

    public static function generateStatus($tipo, $item)
    {

        $mret = @explode(";", $item->value);
        $totalMatrix = @count($mret);

        $status = "";
        $link = "";

        for ($i = 1; $i <= $totalMatrix; $i++)
            $status = $status . @$mret[$i] . ";";

        switch ($tipo) {
            case 0:
                $link = rtrim($status, ';');
                break;
            case 1:
                $string = rtrim($status, ';');
                $string = htmlspecialchars($string);
                $link = "<span href='#' class='text-uppercase badge badge-info' data-toggle='tooltip' title='valor: " . $string . "'>" . $string . "</span>";
                break;
            case 3:
            case 10:
            case 15:
                $link = "<a class='text-uppercase badge badge-light' href='/reports/evento/$item->id/$tipo'><span class=\"fas fa-eye\"></span> VER STATUS</a>";
                break;
            default:
                // Tratar outros tipos, se necessário
                break;
        }

        return $link;
    }
}
