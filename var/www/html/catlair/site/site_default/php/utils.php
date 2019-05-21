<?php

/*******************************************************************************
 * Утилиты на разные случаи жизни
 */



/**
 * Конвертация объема информации из байтов (int) в строку mb кб и прочее
 */
function clSizeToStr($ADelta, $AZero)
{
     if ($ADelta >= 1024*1024*1024*1024) $r=round($ADelta/(1024*1024*1024*1024),2).' Тб';
     else if ($ADelta >= 1024*1024*1024) $r=round($ADelta/(1024*1024*1024),2).' Гб';
         else if ($ADelta >= 1024*1024) $r=round($ADelta/(1024*1024),2).' Мб';
            else if ($ADelta >= 1024) $r=round($ADelta/1024,2).' Кб';
                else if ($ADelta > 0.1 && !AZero) $r=$ADelta.' Б';
                    else $r=$AZero;
     return $r;
}



/**
 * Аналог JS encodeURIComponent
 */
function encodeURIComponent($str)
{
    $revert = array('%21'=>'!', '%2A'=>'*', '%28'=>'(', '%29'=>')');
    $r = strtr(rawurlencode($str), $revert);
    $r = strtr($r, chr(39), '%27');
    return $r;
}



/*
 * Создание GUIDоподобной строки.
 */
function clGUID()
{
    /* !!! Надо будет переписать на нормальный алгоритм. */
     $A=str_pad(dechex(rand(0,hexdec('EFFFFFFF'))),8,'0');
     $B=str_pad(dechex(rand(0,hexdec('FFFF'))),4,'0');
     $C=str_pad(dechex(rand(0,hexdec('FFFF'))),4,'0');
     $D=str_pad(dechex(rand(0,hexdec('FFFF'))),4,'0');
     $E=str_pad(dechex(rand(0,hexdec('FFFF'))),4,'0');
     $F=str_pad(dechex(rand(0,hexdec('EFFFFFFF'))),8,'0');
     return $A.'-'.$B.'-'.$C.'-'.$D.'-'.$E.$F;
}


/*
 * Создание случайного инденификатора формата N-000.
 */
function clRndID($ALength)
{
    $Letter = 'ABDEFGHKLMNPRSTUXYZ';
    return substr($Letter, rand(0, strlen($Letter)-1), 1) . (string)rand(1, pow(10,$ALength));
}



/*
 * Получение параметров в порядке приоритета
 * $AXML - строка параметров полученная из процедуры
 * $_POST
 * $_GET
 */
function clGetIncome($AName, $AXML, $ADefault)
{
 if ($AXML!=null && $AXML[$AName]) $r=$AXML[$AName];
 else if (array_key_exists($AName, $_POST)) $r=$_POST[$AName];
    else if (array_key_exists($AName, $_GET)) $r=$_GET[$AName];
        else $r=$ADefault;
 return $r;
}



/**
 * Используется для проверки корректности пути $APath дабы избежать ./../.
 */
function clPathControl($APath)
{
    $APath = str_replace ('/../', '', $APath);
    $APath = str_replace ('/./', '', $APath);
    $APath = str_replace ('/'.'/', '', $APath);
    return $APath;
}



/**
 * Удаление папки рекурсивное
 */
function clDeleteFolder($APath)
{
    if (is_dir($APath) === true)
    {
        $files = array_diff(scandir($APath), array('.', '..'));
        foreach ($files as $file) clDeleteFolder(realpath($APath) . '/' . $file);
        return rmdir($APath);
    }
    else
    {
        if (is_file($APath) === true) return unlink($APath);
    }
    return !file_exists($APath);
}



function StringToHex($AString)
{
    $hex="";
    for ($i=0; $i < strlen($AString); $i++) $hex .= dechex(ord($AString[$i]));
    return $hex;
}



function HexToString($hex)
{
    $string="";
    for ($i=0; $i < strlen($hex)-1; $i+=2) $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    return $string;
}



function FileCopy($src,$dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ( $file = readdir($dir)) )
    {
        if (( $file != '.' ) && ( $file != '..' ))
        {
            if ( is_dir($src . '/' . $file) ) FileCopy($src . '/' . $file, $dst . '/' . $file);
            else copy($src . '/' . $file, $dst . '/' . $file);
        }
    }
    closedir($dir);
}
