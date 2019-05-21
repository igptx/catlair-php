<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * still@itserv.ru
 */


/*
 * Пользовательскbq редактор дескрипта из формы
 */
function DescriptUpdatePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Получение параметров
    $IDLang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    // Получение обязательных параметров
    $ID=clGetIncome('ID', $AParams, '');
    $IDType = clGetIncome('IDType', $AParams, TYPE_DEFAULT);

    if ($IDType==null || $IDType=='') $Result = 'DescriptTypeUnknown';
    else
    {
        $d = new TDescript();
        // Пытаемся прочитать дескрипт
        $Result = $d->Read($ID, $IDSite);
        if ($Result == rcOk)
        {
            // дочитываем параемтры
            // пользовательские параметры
            $d->Set('Type', $IDType);
            // прользовательские параметры
            $d->SetIncome('IDTypeDefault', $AParams, '');
            $d->SetEnabled( clGetIncome('Enabled', $AParams, 'off') == 'on' );
            $d->SetLangIncome('Caption', $IDLang, $AParams, '');
            // внешний вид
            $d->SetIncome('IDImage', $AParams, '');
            $d->SetIncome('ColorR', $AParams, 0);
            $d->SetIncome('ColorG', $AParams, 0);
            $d->SetIncome('ColorB', $AParams, 0);
            $d->SetIncome('ColorA', $AParams, 0);
            // Закидываем в дескрипт весь пост
            $d->ArrayStore('Post', $_POST);
            // Закидываем в дескрипт весь сервер
            $d->ArrayStore('Server', $_SERVER);
            // проверяем специфическую библиотеку для контента
//            $c = $d->Cast();
//            if ($c->Casted())
//            {
//                if (method_exists($c, 'OnBeforeUpdate') $c->OnBeforeUpdate($AParams);
//                unset();
//            }
            // сохраняем библиотеку
            $Result = $d->Flush();
            if ($Result == rcOk) $AResult->Set('ID', $ID);
        }
        unset($d);
    }
    $AResult->SetCode($Result);
    clEnd('');
    return true;
}
