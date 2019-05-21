<?php
/******************************************************************************
 * Catlair PHP
 * CLI интерфейс
 * Создание дескриптов из контента.
 *
 * Параметры
 * - Маска файла контента (по умолчанию *)
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

clBeg('Import descript from content.');

// Get descript IT
if (count($argv)>1) $FileMask = $argv[1];
else
{
        clInf('File mask: [*]');
        $FileMask = readline();
        if ($FileMask=='') $FileMask = '*';
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

if (count($argv)>4) $IDParent = $argv[4];
else
{
        clInf('ID parent ['.FOLDER_IMPORT.']: ');
        $IDParent = readline();
        if ($IDParent=='') $IDParent = FOLDER_IMPORT;
}


// Check param login
if ($FileMask == '') clWar('File mask empty');
else
{
    // Сборка пути для анализа конетна
    $ContentMask = clDescriptContentPath($IDLang, $IDSite) . '/' . $FileMask;
    // Перебор всех файлов контента
    clBeg('Begin import from ['.$ContentMask.']');
    foreach ( glob($ContentMask) as $File)
    {
        clDeb('Found the file ['.$File.']');
        // Определение имени дескрипта
        $IDDescript = pathinfo($File, PATHINFO_FILENAME);
        $FileExtention =  pathinfo($File, PATHINFO_EXTENSION);
        if ($FileExtention !=='') $IDDescript .= '.'.$FileExtention;
        // Проверка дескрипта
        // Загрузка или создание дескрипта
        if (!clDescriptExists($IDDescript, $IDSite))
        {
            $d = new TDescript();
            $d->Create($IDDescript, 'Content', $IDSite);
            $d->SetLang($IDLang, 'Caption', $IDDescript);
            $d->Set('Indexate', 'on');
            $Parent = new TDescript();
            if ($Parent->Read($IDParent, $d->IDSite) != rcOk) clWar('Parent not found ['.$IDParent.']');
            {
                $d->BindBegin($Parent);
                $d->Bind($Parent, BIND_DEFAULT, false);
                $d->BindEnd($Parent);
            }
            $d->Flush();
            unset($Parent);
        }
        else clInf('Descript exists ['.$IDDescript.']');
        unset($d);
    }
}

clWar('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
clWar('!!! Remind about rights on folders with index files !!!');
clWar('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
clEnd('End');
$clLoger->Stop();
