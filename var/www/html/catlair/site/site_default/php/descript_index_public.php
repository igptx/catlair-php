<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 * Индексация дескрипта
 *
 * still@itserv.ru
 */



/**
 * Выполняет индексацию дескриптов
 * ID дескрипт с которого начинается индексация
 */

function DescriptIndexPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // получение параметров
    $Lang = clGetLang(clGetIncome('IDLang', $AParams, null));
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $ID=clGetIncome('ID', $AParams, null);

    // Если дескрипт передан то обрабатываем его
    if ($ID==null) $AResult->SetCode('EmptyIDDescript');
    else
    {
            $AResult->SetCode(rcOk);
    }

    clEnd($Result);
    return true;
}
