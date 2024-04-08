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
        "Registro Producao Sem Reset" => ["tipo" => 3, "classe" => "badge-success"],
        "Dados Motor Diesel" => ["tipo" => 10, "classe" => "badge-info"],
        "Pressões Bombas" => ["tipo" => 15, "classe" => "badge-danger"],
        "Movimentação Maquina" => ["tipo" => 15, "classe" => "badge-warning"]
    ];

    public static function obterInformacoes($subtopico)
    {
        if (array_key_exists($subtopico, static::$subtopicos))
            return static::$subtopicos[$subtopico];
        else
            return null;
    }

    public static function gerarStatus($tipo, $item)
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
                $link = "<a href='#' class='text-uppercase' data-toggle='tooltip' title='valor: " . rtrim($status, ';') . "'>VALOR: " . rtrim($status, ';') . "</a>";
                break;
            case 3:
            case 10:
            case 15:
                $link = "<a class='text-uppercase badge badge-light' href='/reports/evento/$item->id/$tipo'><span class=\"fas fa-plus\"></span> detalhes</a>";
                break;
            default:
                // Tratar outros tipos, se necessário
                break;
        }

        return $link;
    }
}
