<?php

/**
 * Catlair PHP
 * Пользовательские публичные функции
 * Индексация дескрипта
 *
 * still@itserv.ru
 *
 * 16.04.2019
 */


/**
 * Формирование списка доменных имен и сайтов зарегистрированных на сервере
 */




function SiteListPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    /* получение параметров */
    $IDLang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $IDSite = clGetIncome('IDSite', $AParams,  $clSession->GetSite());

    /* Создание перечня дескпритов */
    $Domains = new TDescripts();

    $DomainMask = clDomainListPath()."/"."*";
    clDeb($DomainMask);
    foreach (glob($DomainMask) as $FileName)
    {
        if (!is_dir($FileName))
        {
            $DomainName = pathinfo($FileName, PATHINFO_BASENAME); /* Имя домена */

            $Domain = new TDomain();
            $Domain->ReadByName($DomainName);
            $DomainIDSite = $Domain->GetArrayValue('Post','IDSiteTarget',null); /* Иденификатор сайа */

            $Site = new TDescript();
            $Site->Read($DomainIDSite, $DomainIDSite);
            $CaptionSite = $Site->GetLang($IDLang, 'Caption', $DomainName);

            unset($site);
            unset($Domain);

            $Record=['ID'=>$DomainIDSite, 'CaptionSite'=>$CaptionSite, 'DomainName'=>$DomainName];
            $Domains->Insert($Record);
        }
    }

    /* Подготовка контента */
    $Content = '';
    $ContentRecord = clDescriptContentByID($AParams['record'], $IDSite, $IDLang);
    $Domains->Sort('CaptionSite', 1);
    $Content=$Domains->Content($ContentRecord);
    unset($Domains);

    /* Возвращаем результат */
    $AResult->SetContent($Content);

    $Result=rcOk;
    $AResult->SetCode($Result);

    clEnd($Result);
    return true;
}
