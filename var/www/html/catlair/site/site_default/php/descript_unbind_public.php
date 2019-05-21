<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 * удаление связи между дескриптами
 *
 * still@itserv.ru
 */

/**
 * Удаление связи между между дескриптами
 * ID - переносимый дескрипт
 * IDSource - откуда переносится дескрипт
 */

function DescriptUnbindPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // получение параметров
    $IDLang = clGetLang(clGetIncome('IDLang', $AParams, null));
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $ID=clGetIncome('ID', $AParams, null);
    $IDFrom=clGetIncome('IDFrom', $AParams, null);
    $IDBind=clGetIncome('IDBind', $AParams, BIND_DEFAULT);

    $Result=rcUnknown;

    // Если дескрипт передан то обрабатываем его
    if ($ID==null) $Result='EmptyIDDescript';
    else
    {
        $Descript = new TDescript();
        $Result = $Descript->Assign($ID, null, $IDSite);
        if ($Result == rcOk)
        {
            $From = new TDescript();
            $Result = $From->Read($IDFrom, $IDSite);
            if ($Result ==rcOk)
            {
                $Result = $Descript->BindBegin($From);
                $Result = $Descript->Unbind($From, $IDBind);
                $Result = $Descript->BindEnd($From);
            }
            unset($From);
        }
        unset($Descript);
    }

    // возвращение результата
    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}
