<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * 16.03.2019
 *
 * still@itserv.ru
 */


/*
 * Пользовательское удаления дескрипта
 */

function DescriptRenameIDPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $ID = clGetIncome('ID', $AParams, null);
    $IDNew = clGetIncome('IDNew', $AParams, null);
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    // Удаление
    $d = new TDescript();
    $Result = $d->Read($ID, $IDSite);
    if ($Result == rcOk) $Result = $d->RenameID($IDNew);
    if ($Result == rcOk) $AResult->Set('ID', $IDNew);
    unset($d);
    // Завершение
    $AResult -> SetCode($Result);
    clEnd($Result);
    return true;
}
