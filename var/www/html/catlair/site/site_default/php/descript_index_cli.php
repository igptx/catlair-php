<?php
/******************************************************************************
 * Catlair PHP
 * CLI интерфейс
 * Инлексация дескрипта. Создание по идетификатору дескрипта поискового индекса.
 *
 * Параметры
 * - Маска идентификатора дескрипта (по умолчаию *)
 * - Идентификатор сайта (по умолчанию SITE_DEFAULT)
 * - Идентификатор языка (по умолчанию LANG_DEFAULT)
 *
 * still@itserv.ru
 */

include "utils.php";
include "debug.php";
include "result.php";
include "descript.php";
include "catlair.php";
include "search.php";

$clLoger->Start(true); // start logger

clBeg('Indexate descript.');

// Get descript IT
if (count($argv)>1) $IDDescriptMask = $argv[1];
else
{
        clInf('ID descript mask: [*]');
        $IDDescriptMask = readline();
        if ($IDDescriptMask=='') $IDDescriptMask = '*';
}

if (count($argv)>2) $IDSite = $argv[2];
else
{
        clInf('ID site ['.SITE_DEFAULT.']: ');
        $IDSite = readline();
        if ($IDSite=='') $IDSite = SITE_DEFAULT;
}

if (count($argv)>3) $IDLang = $argv[3];
else
{
        clInf('ID lang ['.LANG_DEFAULT.']: ');
        $IDLang = readline();
        if ($IDLang=='') $IDLang = LANG_DEFAULT;
}

if (count($argv)>4) $Clear = $argv[4];
else
{
        clInf('Clear all [notclear]: ');
        $Clear = readline();
        if ($Clear=='') $Clear = 'notclear';
}



// Check param login
if ($IDDescriptMask == '') clWar('ID descript mask empty');
else
{
    clBeg('Indexate begin');

    if ($Clear=='clear')
    {
        $Index = new TIndex();
        $Index->Clear($IDSite,$IDLang);
        unset($Index);
    }

    $FileMask = clDescriptsPath($IDSite).'/'.$IDDescriptMask;
    clDeb('FileMask ['.$FileMask.']');
    foreach (glob($FileMask) as $File)
    {
        $IDDescript = pathinfo($File, PATHINFO_BASENAME);
        $d = new TDescript();
        $d->Read( $IDDescript, $IDSite);
        $d->Index($IDLang, $Clear=='clear');
        unset($d);
    }

    clEnd('');
    clWar('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
    clWar('!!! Remember about rights on folders with index files !!!');
    clWar('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
}

clEnd('');
$clLoger->Stop();
