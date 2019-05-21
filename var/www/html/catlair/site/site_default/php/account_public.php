<?php
/**
 * Публичный функционал учетных записей
 * Catlair PHP
 */


/**
 * Административная смена пароля
 */
function &AccountPasswordAdminPublic($AParams, $AResult)
{
    // получение параметров
    clBeg('');
    global $clSession;
    $IDSite = clGetIncome('IDSite', $AParams, $clSession->GetSite());
    $ID = clGetIncome('ID', $AParams, null);
    $Password = clGetIncome('Password', $AParams, null);
    $Check = clGetIncome('Check', $AParams, null);
    // основное тело
    if ($Password=='' || $Password==null) $Result='PaswordEmpty';
    else
    {
        if ($Password!=$Check) $Result='PasswordNotEqual';
        else
        {
            $d = new TAccount();
            $Result = $d->Read($ID, $IDSite);
            if ($Result == rcOk) $Result = $d->SetPassword($Password);
            unset($d);
        }
    }
    // обработка результата и завершение
    $AResult->SetCode($Result);
    clEnd($Result);
    return $AResult;
}


/**
 * Публичная пользовательская смена пароля
 */
function AccontPasswordUserPublic($XML, $Content)
{
}
