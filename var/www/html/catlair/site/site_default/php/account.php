<?php

/**
 * Catlair PHP
 * Copyright (C) 2019 a@itserv.ru
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Accont records
 */

define("TYPE_ACCOUNT",  'Account'); // умолчальный идентификатор дескрипта

/**
 * Учетные записи
 */


class TAccount extends TDescript
{
    function __construct()
    {
        $this->Type = TYPE_ACCOUNT;
    }


    /*
     * Расчет хэша для пароля
     */
    private function HashByPassword($APassword)
    {
        return md5($this->Get('ID','').$APassword);
    }



    /*
     * Set hash for password
     */
    public function SetPassword($APassword)
    {
        clBeg('');
        $this->Set('Hash', $this->HashByPassword($APassword));
        $Result = $this->Flush();
        clEnd($Result);
        return $Result;
    }



    /*
     * Check password
     */
    public function CheckPassword($APassword)
    {
        return ($this->Get('Hash','') == $this -> HashByPassword($APassword));
    }



    /**
     * Построитель контента
     */
    public function &ContentBuild($AIDLang, $AIDSite, &$AResult)
    {
        clLog('Account', ltBeg);
        // вызов сборки стандартных параметров
        $this->ContentBuildInherited($AIDLang, $AIDSite, $AResult);
        // сборка специфичных параметров
        $AResult -> Set('IDUser', $this->Get('IDUser', ''));
        clLog('', ltEnd);
        return $this;
    }



    /*
     *
     */
    public function Update($ADescript, $ALang, &$AResult)
    {
        if (array_key_exists('IDUser', $_POST)) $ADescript->Set('IDUser', $_POST['IDUser']);
        return true;
    }

}
