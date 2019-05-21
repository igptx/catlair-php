<?php
/******************************************************************************
 * Catlair PHP
 * CLI интерфейс
 * Поиск по фразе дескриптов
 *
 * Параметры
 * - Поисковая строка слова разделенные пробелом (обязательно)
 * - Идентификатор сайта (по умолчанию SITE_DEFAULT)
 * - Идентификатор языка (по умолчанию LANG_DEFAULT)
 *
 * still@itserv.ru
 */

include "debug.php";
include "utils.php";
include "result.php";
include "descript.php";
include "catlair.php";
include "search.php";

$clLoger->Start(true); // start logger

clBeg('Import descript from content.');

// Get descript IT
if (count($argv)>1) $Find = $argv[1];
else
{
        clInf('Find:');
        $FileMask = readline();
}

if (count($argv)>2) $IDSite = $argv[2];
else
{
        clInf('ID site [SITE_DEFAULT]: ');
        $IDSite = readline();
        if ($IDSite=='') $IDSite = SITE_DEFAULT;
}

if (count($argv)>3) $IDLang = $argv[3];
else
{
        clInf('ID lang [LANG_DEFAULT]: ');
        $IDLang = readline();
        if ($IDLang=='') $IDLang = LANG_DEFAULT;
}


// Check param login
if ($Find == '') clWar('Empty find string');
else
{
            $Index = new TIndex();
            $Result = $Index->Find($Find, $IDLang, $IDSite);

//     $r=new TResult();
//
//     $Descripts = new TDescripts();
//     $Descripts->LoadFromArray($Result, 'ID');
//     $Descripts->SelectCaption($IDLang, 'ID', 'Caption');
//     $Descripts->BuildResult($r, 'Records');
//     unset($Descript);
//
//     print_r($r);
//     print_r($r->End());


            unset($Index);
            // Перебор всех файлов контента
            foreach ($Result as $IDDescript)
            {
                clInf($IDDescript);
            }
}
clEnd('End');
$clLoger->Stop();
