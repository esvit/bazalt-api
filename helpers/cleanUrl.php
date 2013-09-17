<?php

function cleanUrl($url, $replace = array(), $delimiter = '-')
{
    //$url = self::decodeCharReferences($url);

    if (!empty($replace)) {
        $url = str_replace((array)$replace, ' ', $url);
    }

    $replaceSymbols = array(
        '«', '»', '”', '“', '№', '—', '–', "\xC2\xA0" /* no break space */
    );

    // remove symbols
    $url = preg_replace('/[\\x00-\\x19\\x21-\\x2F\\x3A-\\x40\\x5B-\\x60\\x7B-\\x7F]/', ' ', $url);

    $url = str_replace($replaceSymbols, ' ', $url);

    $url = preg_replace("/\s+/", ' ', $url);

    $url = str_replace('?', '', $url);

    $url = preg_replace("/[\/_|+ -]+/", $delimiter, $url);

    $url = mb_strToLower(trim($url, $delimiter));

    return $url;
}