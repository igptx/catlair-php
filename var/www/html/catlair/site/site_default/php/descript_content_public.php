<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 * Рабочта дескриптом
 *
 * 16.03.2019
 *
 * still@itserv.ru
 */

/*
 *  Чтение контента
 */
function DescriptContentReadPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $Lang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $ID = clGetIncome('ID', $AParams, 0);
    // Создание дескрипта
    $d = new TDescript();
    $r = $d->Read($ID, $Site);
    if ($r==rcOk)
    {
        $AResult->Set('Caption', $d->GetLang($Lang, 'Caption', $ID));
        $AResult->Set('Content', $d->ContentRead($Lang, $Site));
        $AResult->Set('Indexate', $d->Get('Indexate', $ID));
    }
    unset($d);

    $AResult -> SetCode($r);
    clEnd('');
    return true;
}


/*
 *  Запись контента
 */
function DescriptContentWritePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $Lang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $ID = clGetIncome('ID', $AParams, 0);
    $Caption=clGetIncome('Caption', $AParams, '');
    $Content=clGetIncome('Content', $AParams, '');
    $Indexate=clGetIncome('Indexate', $AParams, '');
    // Сохранение данных
    $d = new TDescript();
    $r=$d->Read($ID, $Site);
    if ($r==rcOk) $r=$d->ContentWrite($Lang, $Content);
    if ($r==rcOk) $r=$d->SetLang($Lang, 'Caption', $Caption);
    if ($r==rcOk) $r=$d->Flush();
    if ($r==rcOk) $r=$d->Set('Indexate', $Indexate);
    if ($r==rcOk) $r=$d->Index($Lang, false);
    unset($d);
    // Завершение
    $AResult -> SetCode($r);
    clEnd('');
    return true;
}
