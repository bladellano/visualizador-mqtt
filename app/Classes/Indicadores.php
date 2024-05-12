<?php

namespace App\Classes;

abstract class Indicadores
{
    const MENU = [
        'Alarme Ativo' => 'chart-up-to-1-value', // view
        'Dados Motor Diesel' => 'chart-more-than-10-values',
        'Horimetro Esteiras Locomoção' => 'chart-up-to-1-value',
        'Horimetro Motor Diesel' => 'chart-up-to-1-value',
        'Maquina On Line' => 'chart-up-to-1-value',
        'Movimentação Maquina' => 'chart-up-to-1-value', // Maquina Parou de se Movimentar | Maquina se Movimentou
        'Pressões Bombas' => 'chart-more-than-10-values',
        'Registro Producao Sem Reset' => 'chart-up-to-1-value',
        'Situação Alimentação Maquina' => 'chart-up-to-1-value',
        'Situação Produção' => 'chart-up-to-1-value',
        'Situação Transportador de Saida' => 'chart-up-to-1-value' // Ligado | Desligado
    ];

    public static function convertArrayToBase64($array)
    {
        $result = [];

        foreach ($array as $key => $item) 
            $result[$key] = base64_encode($key);

        return $result;
    }
}
