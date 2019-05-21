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

function DescriptDeletePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $ID = clGetIncome('ID', $AParams, 0);
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    // Удаление
    $d = new TDescript();
    $r=$d->Read($ID, $IDSite);
    if ($r==rcOk) $r=$d->Delete();
    unset($d);
    // Завершение
    $AResult -> SetCode($r);
    clEnd($r);
    return true;
}
