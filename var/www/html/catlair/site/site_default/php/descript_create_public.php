<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * still@itserv.ru
 */


/*
 * Пользовательское создание новго дескрипта из формы
 */
function DescriptCreatePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $IDLang = clGetLang(clGetIncome('IDLang', $AParams, null));
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $IDParent = clGetIncome('IDParent', $AParams, '');
    $IDBind = clGetIncome('IDBind', $AParams, BIND_DEFAULT);
    $IDType = clGetIncome('IDType', $AParams, TYPE_DEFAULT);
    $ID = clGetIncome('ID', $AParams, '');
    $Caption = clGetIncome('Caption', $AParams, $ID);

    if ($ID=='') $ID=clGUID();
    $Result = rcUnknown;

    if ($IDType==null || $IDType=='') $Result = 'DescriptTypeUnknown';
    else
    {
        $d = new TDescript('');
        $Result = $d->Create($ID, $IDType, $Site);
        if ($Result==rcOk)
        {
            // Запись параметров дескрипта
            $d->SetLang($IDLang, 'Caption', $Caption);
            // Установка связей для родителя
            $Parent=new TDescript();
            $Result = $Parent->Read($IDParent, $Site);
            $Result = $d->BindBegin($Parent);
            $Result = $d->Bind($Parent, $IDBind, false);
            $Result = $d->Bind($Parent, BIND_RIGHT, false);
            $Result = $d->BindEnd($Parent);
            unset($Parent);

            if ($Result == rcOk)
            {
                $Result = $d->Flush();
                if ($Result == rcOk)
                {
                    $AResult->Set('ID', $ID);
                    $AResult->Set('IDParent', $IDParent);
                }
            }
        }
        unset($d);
    }
    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}
