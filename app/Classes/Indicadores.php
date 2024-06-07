<?php

namespace App\Classes;

abstract class Indicadores
{
    const MENU = [
        'Maquina On Line' => 'chart-up-to-1-value', // TWFxdWluYSBPbiBMaW5l //?[X]
        'Horimetro Motor Diesel' => 'chart-up-to-1-value', // SG9yaW1ldHJvIE1vdG9yIERpZXNlbA== //?[X]
        'Horimetro Esteiras Locomoção' => 'chart-up-to-1-value', // SG9yaW1ldHJvIEVzdGVpcmFzIExvY29tb8Onw6Nv //?[X]
        'Alarme Ativo' => 'chart-up-to-1-value', // QWxhcm1lIEF0aXZv //?[X]

        'Movimentação Maquina' => 'chart-up-to-1-value', // TW92aW1lbnRhw6fDo28gTWFxdWluYQ== //?[X]
        'Situação Alimentação Maquina' => 'chart-up-to-1-value', // U2l0dWHDp8OjbyBBbGltZW50YcOnw6NvIE1hcXVpbmE= //?[X]
        'Situação Produção' => 'chart-up-to-1-value', // U2l0dWHDp8OjbyBQcm9kdcOnw6Nv //?[X]
        'Dados Motor Diesel' => 'chart-more-than-10-values', // RGFkb3MgTW90b3IgRGllc2Vs
        'Pressões Bombas' => 'chart-more-than-10-values', // UHJlc3PDtWVzIEJvbWJhcw==
        'Registro Producao Sem Reset' => 'chart-up-to-1-value', // UmVnaXN0cm8gUHJvZHVjYW8gU2VtIFJlc2V0
        'Situação Transportador de Saida' => 'chart-up-to-1-value' // U2l0dWHDp8OjbyBUcmFuc3BvcnRhZG9yIGRlIFNhaWRh
    ];

    public static function convertArrayToBase64($array)
    {
        $result = [];

        foreach ($array as $key => $item) 
            $result[$key] = base64_encode($key);

        return $result;
    }
}
