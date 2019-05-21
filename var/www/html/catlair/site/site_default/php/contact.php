<?php

/**
 * Конакт
 * Catlair PHP
 *
 * Работа с контактом
 *
 * still@itserv.ru
 */

define("TYPE_CONTACT" ,'Contact'); // умолчальный идентификатор дескрипта


class TContact extends TDescript
{
    function __construct()
    {
       $this->Type = TYPE_CONTACT;
    }

    /*
     *
     */
    public function Send($AIDLang, $ASubject, $AContent, $AFrom)
    {
        clBeg('');
        if (!$this->Prepared()) $Result='ContactNotPrepared';
        else
        {
            $To = $this->GetLang($AIDLang, 'Caption', '');
            if ($To=='') $Result='UnknowRecipientContact';
            else
            {
                $Headers = 'From: ' . $AFrom . "\r\n".
                           'Reply-To: ' . $AFrom . "\r\n" .
                           'MIME-Version: 1.0' . "\r\n" .
                           'Content-type: text/html; charset="UTF-8"' . "\r\n" .
                           'X-Mailer: Catlair PHP';
                ini_set('SMTP', 'localhost');
                ini_set('smtp_port', '25');

                if (mail($To, $ASubject, $AContent, $Headers)) $Result=rcOk;
                else $Result='ErrorSendMail';
            }
        }
        clEnd($Result);
        return $Result;
    }
}
