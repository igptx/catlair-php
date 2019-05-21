<?php

/******************************************************************************
 * Catlair PHP
 * Публичные функции библиотеки авторизации session.php
 *
 * still@itserv.ru
 */

/**
 * Авторизация пользователя
 */
function SessionLoginPublic($AParams, $AResult)
{
    clBeg("");
    global $clSession;
    // Get params
    $Login = clGetIncome('UserLogin', $AParams, '');
    $Password = clGetIncome('UserPassword', $AParams, '');
    $IDSite = clGetIncome('IDSite', $AParams, $clSession->GetSite());
    // Check param login
    if ($Login == '') $AResult->SetCode('LoginEmpty');
    else
    {
        if ($Password == '') $AResult->SetCode('PaswordEmpty');
        else
        {
            $d = new TAccount();
            if ($d->Read($Login, $IDSite)!=rcOk) $AResult->SetCode('AccountNotFound');
            else
            {
                if (!$d->CheckPassword($Password)) $AResult->SetCode('PasswordError');
                else
                {
                    global $clSession;
                    $clSession->Open(true);
                    $clSession->SetLogin($Login);
                    $clSession->RoleBuild();
                    $AResult->SetCode(rcOk); // возвращаем положительный результат
                }
            }
            unset($d);
        }
    }
    clEnd("");
    return true;
}



/**
 * Завершение сесии пользоватея
 */
function SessionLogoutPublic($AParams, $AResult)
{
    clBeg("");
    // Get params
    global $clSession;
    if ($clSession->IsGuest()) $Result = 'SessionAlreadyGuest';
    else $Result = $clSession->Close();
    $AResult->SetCode($Result);
    clEnd($Result);
    return true;
}



/**
 * Информация сессии
 */
function SessionInfoPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    // Определенеие переменных
    $IDLang = $clSession->GetLanguage();
    $IDSite = $clSession->GetSite();
    $Login = $clSession->GetLogin();
    $CaptionLanguage = clDescriptCaptionByID($IDLang, $IDSite, $IDLang);
    $CaptionSite = clDescriptCaptionByID($IDSite, $IDSite, $IDLang);

    $Account = new TAccount();
    if ($Account->Read($Login, $IDSite) == rcOk)
    {
        $IDUser = $Account->Get('IDUser','');
        $CaptionUser = clDescriptCaptionByID($IDUser, $IDSite, $IDLang);
    }
    else
    {
        $IDUser = '';
        $CaptionUser = '';
    }
    // Возвращаем параметры
    $AResult->Set('Login', $Login);
    $AResult->Set('IDLanguage', $IDLang);
    $AResult->Set('IDSite', $IDSite);
    $AResult->Set('CaptionLang', $CaptionLanguage);
    $AResult->Set('CaptionSite', $CaptionSite);
    $AResult->Set('IDUser', $IDUser);
    $AResult->Set('CaptionUser', $CaptionUser);
    // Вывод результата
    $AResult->SetCode(rcOk);
    clEnd('');
    return true;
}



function clSessionCurrentLogin()
{
 global $clSession;
 return $clSession->GetLogin();
}



//------------------------------------------------------------------------------
// Возвращает перечень ролей для текущей сессии
//------------------------------------------------------------------------------

function DescriptRoleListPublic($AXML, $AContent)
{
 global $clSession;
 $IDLanguage = $clSession->GetLanguage();
 $l=new TDescripts;
 $l->LoadFromArray($clSession->Role, 'ID');
 $l->Caption($IDLanguage, 'ID', 'Caption');
 $Result=$l->Content($AContent);
 unset($l);
 return $Result;
}
