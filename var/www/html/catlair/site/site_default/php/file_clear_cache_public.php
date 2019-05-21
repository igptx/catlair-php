<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * still@itserv.ru
 */


/*
 * очиска кэша для файла
 */
function FileClearCachePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;

    /* Получение параметров */
    $IDLang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $IDSite = clGetIncome('IDSite', $AParams, $clSession->GetSite());
    $ID = clGetIncome('ID', $AParams, null);

    /* выполнение */
    $d = new TFile();
    $Result = $d->Read($ID, $IDSite);
    if ($Result==rcOk) $Result = $d->CacheDelete($IDLang);
    unset($d);

    /* обработка резульата */
    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}
