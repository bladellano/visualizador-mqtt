<?php

namespace App\Classes;

abstract class Helper
{
    public static function removeUnwantedCharacters($str)
    {
        return mb_check_encoding($str, 'UTF-8')
            ? html_entity_decode($str)
            : utf8_encode(html_entity_decode($str));
    }

    public static function _implode($attribute, $base64Decode = false)
    {
        if (is_array($attribute)) {
            foreach ($attribute as &$a) {
                $a = $base64Decode ? "'" . base64_decode($a) . "'" : $a;
            }
        }

        return implode(',', $attribute);
    }
}
