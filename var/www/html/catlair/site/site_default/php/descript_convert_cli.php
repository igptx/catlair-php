<?php
/******************************************************************************
 * Catlair PHP
 * CLI interface
 *
 ******************************************************************************
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
include "file.php";

$clLoger->Start(true);

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




/* Check param login */
if ($IDDescriptMask == '') clWar('ID descript mask empty');
else
{
    clBeg('Convert begin');

    $FileMask = '/var/www/html/catlair/site/'.$IDSite.'/descripts/'.$IDDescriptMask;
    clDeb('FileMask ['.$FileMask.']');
    foreach (glob($FileMask) as $File)
    {
        $Source = $File.'/file_language_default_'.basename($File);
        $Dest = $File.'/file_language_default';
        clDeb($Source.' ---- '.$Dest);
        if (file_exists($Source)) rename($Source, $Dest);
    }
    clEnd('');
}
clEnd('');

$clLoger->Stop();




