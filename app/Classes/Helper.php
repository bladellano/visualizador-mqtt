<?php

namespace App\Classes;

abstract class Helper
{
    public static function removeUnwantedCharacters($string)
    {
        return utf8_encode($string);
    }
}
