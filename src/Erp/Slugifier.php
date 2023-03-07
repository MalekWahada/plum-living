<?php

declare(strict_types=1);

namespace App\Erp;

class Slugifier
{
    public static function slugifyOptionCode(string $optionName, string $code): string
    {
        // remove numbers
        $code = preg_replace('/[0-9]+/', '', $code);
        $code = (string) \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($code);
        $code = trim($code);
        $code = strtolower($code);
        $code = str_replace([' ', '/', '-', '&'], '_', $code);
        return $optionName . '_' . $code;
    }

    public static function slugifyCode(string $code): string
    {
        $code = str_replace(' ', '_', trim($code));
        // suppression des accents (https://www.matthecat.com/supprimer-les-accents-d-une-chaine-avec-php.html)
        return (string) \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($code);
    }

    // slugify a status to a lower string without accents, spaces (' '), slashes ('/') and back-slashes ('\')
    public static function slugifyOrderERPStatus(string $erpStatus) :string
    {
        $status = str_replace([' ', '/', '\\', '\''], '_', trim($erpStatus));
        $status = (string) \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($status);
        // replace multiple underscores by a single underscore ('_')
        $status = preg_replace('/_+/', '_', $status);
        return strtolower($status);
    }
}
