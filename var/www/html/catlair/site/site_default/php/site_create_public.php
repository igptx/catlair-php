<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 *
 * still@itserv.ru
 */


/*
 * Пользовательское создание новго сайта из формы
 */
function SiteCreatePublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    /* Получение параметров */
    $IDLang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $ID = clGetIncome('ID', $AParams, '');
    $Caption = clGetIncome('Caption', $AParams, $ID);
    $DomainName = $ID.'.domain';

    /* Если сессия гостевая и сессия не инсталляционная то ошибка */
    if ($clSession->IsGuest()) $Result = 'SessionIsNotAuth';
    else
    {
        if ($ID=='') $Result='UnknownIdentify';
        else
        {
            $d = new TDescript('');
            $Result = $d->Create($ID, TYPE_SITE, $ID);
            if ($Result==rcOk)
            {
                // Запись параметров дескрипта
                $d->SetLang($IDLang, 'Caption', $Caption);

                // Установка связей для родителя
                $Parent = new TDescript();
                $Result = $Parent->Read('home', $ID);
                $Result = $d->BindBegin($Parent);
                $Result = $d->Bind($Parent, BIND_DEFAULT, false);
                $Result = $d->Bind($Parent, BIND_RIGHT, false);
                $Result = $d->BindEnd($Parent);
                unset($Parent);

                if ($Result == rcOk)
                {
                    $Result = $d->Flush();
                    if ($Result == rcOk)
                    {
                        /* Создание домена */
                        $Domain = new TDomain();
                        $Result = $Domain -> Create($DomainName, TYPE_DOMAIN, $ID);
                        if ($Result == rcOk)
                        {
                            $Domain->SetLang($IDLang, 'Caption', $Caption);

                            $Post=['IDSiteTarget'=>$ID];
                            $Result = $Domain->ArrayStore('Post', $Post);

                            if ($Result==rcOk)
                            {
                                $Result = $Domain->BindBegin($d);
                                $Result = $Domain->Bind($d, BIND_DEFAULT, false);
                                $Result = $Domain->Bind($d, BIND_RIGHT, false);
                                $Result = $Domain->BindEnd($d);
                                if ($Result == rcOk)
                                {
                                    $Result = $Domain->Flush();
                                    if ($Result==rcOk)
                                    {
                                        $AResult->Set('IDSite', $ID);
                                        $AResult->Set('IDDomain', $DomainName);
                                    }
                                }
                            }
                        }
                        unset($Domain);
                    }
                }
            }
            unset($d);
        }
    }

    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}
