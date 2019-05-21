<?php
/******************************************************************************
 * Catlair PHP
 * CLI интерфейс
 * выпуск сайта и его сборка
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
include "file.php";



$clLoger->Start(true); // start logger

clBeg('Production begin');

// Get descript IT
if (count($argv)>1) $IDSiteSource = $argv[1];
else
{
        clInf('Site source: [*]');
        $IDSiteSource = readline();
        if ($IDSiteSource=='') $IDSiteSource = SITE_DEFAULT;
}

if (count($argv)>2) $IDSiteDestination = $argv[2];
else
{
        clInf('Site destination:');
        $IDSiteDestination = readline();
        if ($IDSiteDestination=='') $IDSiteDestination = null;
}

// Check param login
if ($IDSiteDestination == null) clWar('Site destination not found');
else
{
    $SourcePath = clSitePath($IDSiteSource);
    $DestinationPath = clSitePath($IDSiteDestination);
    clDeb('['.$SourcePath.'] Source site path');
    clDeb('['.$DestinationPath.'] Destination site path');

    // Удаляем сайт направление
    if (file_exists($DestinationPath))
    {
        clDeleteFolder($DestinationPath);
        clDeb('Удаление каталога ['.$DestinationPath.']');
    }

    $Header = '/'.str_repeat('*',79).EOL;
    $Header.= ' * Catlair PHP engine'.EOL;
    $Header.= ' * Property of itserv.ru'.EOL;
    $Header.= ' *'.EOL;
    $Header.= ' * phone: +7(863)207-207-3'.EOL;
    $Header.= ' * email: a@itserv.ru'.EOL;
    $Header.= ' * site: https://itserv.ru'.EOL;
    $Header.= ' *'.EOL;
    $Header.= ' * ' . date('Y-m-d h:i:s') . EOL;
    $Header.= ' */'.EOL;

    // Создание словаря
    $Vocab =
    [
        ['Key'=>'<?php', 'Value'=>'<?php'.EOL.$Header],

        ['Key'=>'SITE_DEFAULT', 'Value'=>'f'.MD5('SITE_DEFAULT')],
        ['Key'=>'LANG_DEFAULT', 'Value'=>'f'.MD5('LANG_DEFAULT')],
        ['Key'=>'TYPE_DEFAULT', 'Value'=>'f'.MD5('TYPE_DEFAULT')],
        ['Key'=>'BIND_DEFAULT', 'Value'=>'f'.MD5('BIND_DEFAULT')],
        ['Key'=>'rcOk', 'Value'=>'f'.MD5('rcOk')],
        ['Key'=>'rcUnknown', 'Value'=>'f'.MD5('rcUnknown')],

        ['Key'=>'clBeg', 'Value'=>'f'.MD5('clBeg')],
        ['Key'=>'clEnd', 'Value'=>'f'.MD5('clEnd')],
        ['Key'=>'clDeb', 'Value'=>'f'.MD5('clDeb')],
        ['Key'=>'clInf', 'Value'=>'f'.MD5('clInf')],
        ['Key'=>'clWar', 'Value'=>'f'.MD5('clWar')],
        ['Key'=>'clErr', 'Value'=>'f'.MD5('clErr')],
        ['Key'=>'clGetIncome', 'Value'=>'f'.MD5('clGetIncome')],
        ['Key'=>'HexToString', 'Value'=>'f'.MD5('HexToString')],
        ['Key'=>'StringToHex', 'Value'=>'f'.MD5('StringToHex')]
    ];


    /**
     * Создаем PHP
     */

    clBeg('PHP');
    $LibrarySourcePath = clLibraryPath($IDSiteSource);
    $LibraryDestinationPath = clLibraryPath($IDSiteDestination);
    clDeb('['.$LibrarySourcePath.'] Source site path');
    clDeb('['.$LibraryDestinationPath.'] Destination site path');
    // Создаем папку скиптов
    if (!file_exists($LibraryDestinationPath)) mkdir($LibraryDestinationPath, FILE_RIGHT, true);
    // Обход файлов скриптов
    $Files = array_diff(scandir($LibrarySourcePath), array('.', '..'));
    foreach ($Files as $File)
    {
        clBeg($File);
        $PHPContent = file_get_contents($LibrarySourcePath.'/'.$File);
        $PHPContent = Obfus($PHPContent, $Vocab);
        file_put_contents($LibraryDestinationPath.'/'.$File, $PHPContent);
        clEnd('');
    }
    clEnd('PHP');


    /**
     * Создаем дескрипты
     */

    clBeg('Descript');
    $Source = clDescriptsPath($IDSiteSource);
    $Dest = clDescriptsPath($IDSiteDestination);
    clDeb('['.$Source.'] source descript path');
    clDeb('['.$Dest.'] destination descript path');
    FileCopy($Source, $Dest);
    clEnd('Descript');
}
clEnd('End');
$clLoger->Stop();




// Обфурскация имен переменных
function ConvertVar($AValue)
{
    if
    (
        $AValue[0]=='$clLoger' ||  // это потому что не фурсится cl.php надо принять меры.
        $AValue[0]=='$this' ||
        $AValue[0]=='$GLOBALS' ||
        $AValue[0]=='$_SERVER' ||
        $AValue[0]=='$_GET' ||
        $AValue[0]=='$_POST' ||
        $AValue[0]=='$_FILES' ||
        $AValue[0]=='$_COOKIE' ||
        $AValue[0]=='$_SESSION' ||
        $AValue[0]=='$_REQUEST' ||
        $AValue[0]=='$_ENV'
    ) $Result=$AValue[0];
    else $Result='$f'.MD5((string)$AValue[0]);
    clDeb($AValue[0].' -> '.$Result);
    return $Result;
}



// Обфурскация имен переменных
function ConvertString($AValue)
{
    if ($AValue!='\'\'')
    {
        $s=StringToHex( substr($AValue[0], 1, -1));
        return 'HexToString(\'' . $s . '\')';
    }
    else return $AValue[0];
}



/*Обфурскатор*/
function Obfus($ASource, &$AVac)
{
    // выкусывание однострочных коментариев
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?:\/\/.*?(\n|$))/', '$1', $ASource);
    // выкусывание однострочных коментариев
    $ASource = preg_replace ('/(\'.*?\')|(?:\/\*(?:.|\n)*?\*\/)/', '$1', $ASource);
    // все пробелы до конца строки
    $ASource = preg_replace ('/\s+?(\n)/', '$1', $ASource);
    // уьбрать все пробелы вокруг =
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(=) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг >
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(>) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг <
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(<) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг .
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\.) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг .
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(,) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг (
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\() ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг )
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\)) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг -
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\-) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг +
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\+) ?)/', '$1$2', $ASource);
    // Косим все энтеры
    $ASource = preg_replace ('/\n/', ' ', $ASource);
    // Косим двойные пробелы
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?:( ) +)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг {
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\{) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг }
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\}) ?)/', '$1$2', $ASource);
    // уьбрать все пробелы вокруг ;
    $ASource = preg_replace ('/(\'(?:.|\n)*?\')|(?: ?(\;) ?)/', '$1$2', $ASource);
    // конвертим строки в хекс
    $ASource = preg_replace_callback ('/\'(?:.|\n)*?\'/', 'ConvertString', $ASource);
    // превращаем переменнные в мусор
//    $ASource = preg_replace_callback ('/\$\w+/', 'ConvertVar', $ASource);

    // Подмены словаря
    foreach ($AVac as $Line) $ASource = str_replace ($Line['Key'], $Line['Value'], $ASource);
    return $ASource;
}
