<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 * Индексация дескрипта
 *
 * still@itserv.ru
 */

/**
 * Выполняет перенос дескрипта между дескриптами
 * ID - переносимый дескрипт
 * IDSource - откуда переносится дескрипт
 * IDDest - куда переносится дескрипт
 */

function DescriptMovePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // получение параметров
    $IDLang = clGetLang(clGetIncome('IDLang', $AParams, null));
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $ID=clGetIncome('ID', $AParams, null);
    $IDFrom=clGetIncome('IDFrom', $AParams, null);
    $IDTo=clGetIncome('IDTo', $AParams, null);
    $IDBind=clGetIncome('IDBind', $AParams, BIND_DEFAULT);

    $Result=rcUnknown;

    // Если дескрипт передан то обрабатываем его
    if ($ID==null) $Result='EmptyIDDescript';
    else
    {
        $Descript = new TDescript();
        $Result = $Descript->Read($ID, $IDSite);
        if ($Result == rcOk) $Result=$Descript->Move($IDFrom, $IDTo, $IDBind);
        unset($Descript);
    }

    // возвращение результата
    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}
