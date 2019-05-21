<?php

/*******************************************************************************
 * Descript Utils
 * CatlairPHP
 *
 * Инструментарий дескриптов для краткого вызова
 *
 * still@itserv.ru
 */

/*******************************************************************************
 * Функции для работы с файлам контента дескриптов
 */



function clDescriptsPath($AIDSite)
{
    return clSitePath($AIDSite).'/descripts';
}



function clDataPath($AIDSite, $AID)
{
    return clDescriptsPath($AIDSite).'/'.$AID;
}



/**
 * возвращает имя файла дескрипта
 */
function clDescriptFile($AIDSite, $AID)
{
    return clDataPath($AIDSite, $AID) . '/descript.xml';
}



/*
 * Проверка существования дескрипта для сайта
 */
function clDescriptExists($AIDSite, $AID)
{
    $FileName = clDescriptFile($AIDSite, $AID);
    return file_exists($FileName);
}



/**
 * Проверка существования дескрипта для сайта или для дефаулного сайта
 */
function clDescriptExistsAny($AIDSite, $AID)
{
    $FileName = clDescriptFile($AIDSite, $AID);
    $Result = file_exists($FileName);
    if (!$Result)
    {
        $FileName = clDescriptFile(SITE_DEFAULT, $AID);
        $Result = file_exists($FileName);
    }
    return $Result;
}



/**
 * возвращает имя файла контента
 */
function clDescriptContentFile($AIDSite, $AIDLang, $AID)
{
    return clDataPath($AIDSite, $AID) . '/content_' . $AIDLang;
}



/**
 * возвращает имя файла контента исходя из желательных языка и сайта
 * подбирается максимально возможный контент на основании данных
 * в случае отсутствия файла возвращается false.
 *
 * $AID - идентификатор дескрипта
 * $ALang - идентификатор языка
 * $ASite - идентификатор сайта
 */

function clDescriptContentFileAny($ASite, $ALang, $AID)
{
    $File = clDescriptContentFile($ASite, $ALang, $AID);
    if (!file_exists($File))
    {
        $File = clDescriptContentFile($ASite, LANG_DEFAULT, $AID);
        if (!file_exists($File))
        {
            $File = clDescriptContentFile(SITE_DEFAULT, $ALang, $AID);
            if (!file_exists($File))
            {
                $File = clDescriptContentFile(SITE_DEFAULT, LANG_DEFAULT, $AID);
                if (!file_exists($File)) $File=false;
            }
        }
    }
    return $File;
}



/*******************************************************************************
 * Функции для пуей PHP библиотек загружаемых при помощи cast
 */

/*
 * Возвращает путь .
 * $AIDSite - идентификтор сайта
 */
function clLibraryPath($AIDSite)
{
    return clSitePath($AIDSite) . '/php';
}



/*
 * Возвращает полное имя файла библиотеки по имени
 * $AID - идентификтор дескрипта
 * $APath - путь ранее полученный функцией clDescriptPath
 */
function clLibraryFile($AName, $APath)
{
 return $APath . '/' . $AName . '.php';
}



function clLibraryFileAny($AName, $AIDSite)
{
    // пытаемся найти файл библиотеки c указанного сайта
    $PathName = clLibraryPath($AIDSite);
    $FileName = clLibraryFile($AName, $PathName);
    if (!file_exists($FileName) && $AIDSite!=SITE_DEFAULT)
    {
        clDeb('Library ['.$FileName.'] not found');
        // пытаемся найти файл библиотеки с умолчального сайта
        $PathName = clLibraryPath(SITE_DEFAULT);
        $FileName = clLibraryFile($AName, $PathName);
        if (!file_exists($FileName))
        {
            clDeb('Library ['.$FileName.'] not found');
            $FileName = false;
        }
    }
    return $FileName;
}



/*******************************************************************************
 * Функции для пуей массивов данных для дескриптов
 */

/*
 * Возвращает файл массива дескиптов
 * $AIDSite - идентификтор сайта
 * $AID - идентификатор дескрипа
 * $AТфьу - наименование массива данных
 */
function clArrayFile($AIDSite, $AID, $AName)
{
    return clDataPath($AIDSite, $AID) . '/array_' . $AName . '.xml';
}



function clArrayFileAny( $AIDSite, $AID, $AName)
{
    // пытаемся найти файл
    $FileName = clArrayFile($AIDSite, $AID, $AName);
    if (!file_exists($FileName) && $AIDSite!=SITE_DEFAULT)
    {
        // пытаемся найти файл библиотеки с умолчального сайта
        $FileName = clArrayFile(SITE_DEFAULT, $AID, $AName);
        if (!file_exists($FileName)) $FileName = false;
    }
    return $FileName;
}



/*******************************************************************************
 * Получение строк для индексайии и поиска при использовании index.php
 */
function clIDString($AID)
{
    return preg_replace('/[^a-zA-Zа-яА-Я0-9_]/ui', '', $AID);
}



/*
 * Возвращает строку связи с родителями для полнотекстовго поиска
 */
function clIndexParentString($AIDParent, $AIDBind)
{
    if ($AIDParent!='' && $AIDBind!='') $Result = 'Parent_' . clIDString($AIDParent) . '_' . clIDString($AIDBind);
    else $Result = '';
    return $Result;
}




/**
 * Возвращает строку типа для полнотекстовго поиска и индексации
 */
function clIndexTypeString($AIDType)
{
    return clIDString($AIDType).'_type';
}



/**
 * Возвращает строку поиска по идентификатору ID
 */
function clIndexIDString($AID)
{
    return clIDString($AID).'_id';
}



function clIndexArrayString(&$AKey, $AValue)
{
    return 'array_' . $AKey . '_' . $AValue;
}




/*******************************************************************************
 * Сервисные функции быстрого получения контента из идескриптов
 */

/**
 * Получение описания дескрипта по идентификатору
 */
function clDescriptCaptionByID($AID, $AIDSite, $AIDLang)
{
    clBeg('');
    $d = new TDescript();
    $Result = $d->ReadExisting($AID, $AIDSite);
    if ($Result == rcOk) $Result = $d->GetLangAny($AIDLang, 'Caption', '{'.$AID.'}');
    else $Result='{'. $AID . ':' . $Result . '}';
    unset($d);
    clEnd('');
    return $Result;
}



/**
 * Получение контента дескрипта по идентификатору
 */
function clDescriptContentByID($AID, $IDSite, $AIDLang)
{
    clBeg('');
    $d = new TDescript();
    $Result = $d->ReadExisting($AID, $IDSite);
    if ($Result == rcOk) $Result = $d->ContentRead($AIDLang);
    else $Result='{ID:"' . $AID . '", Site:"'.$IDSite.'", Lang:"' . $AIDLang. '", Code:"' .$Result . '"}';
    unset($d);
    clEnd('');
    return $Result;
}



function clDescriptIDValidate($AID)
{
    if (strlen($AID) > 250) $Result = 'IDVeryLong';
    else if (strpos($AID,'/') !== false) $Result = 'IDContainSlash';
        else if (strpos($AID,'..')!==false) $Result = 'IDContainDots';
            else if (strpos($AID,'\\')!==false) $Result = 'IDContainBackSlash';
                else if (strpos($AID,'?')!==false) $Result = 'IDContainQuestion';
                    else if (strpos($AID,'&')!==false) $Result = 'IDContainAmpersand';
//                        else if (strpos($AID,"'")!==false) $Result = 'IDContainSingleQuote';
                            else if (strpos($AID,'"')!==false) $Result = 'IDContainDoubleQuote';
                                else $Result = rcOk;
    return $Result;
}



/**
 * Normalize caption
 */
function clDescriptCaptionNormalize($ACaption)
{
    $Result = $ACaption;
    $ASource = preg_replace ('/\s+/', ' ', $ASource);
    return $Result;
}




