<?php

/**********************************************************************************
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
 **********************************************************************************
 * Accont records
 */

define("TYPE_ACCOUNT",  'Account'); // умолчальный идентификатор дескрипта

/**
 * Учетные записи
 */


class TAccount extends TDescript
{
    private $IDToken; /* Token itentify */
    private $TokenParam; /* Token params array*/

    function __construct()
    {
        $this->Type = TYPE_ACCOUNT;
        $this->TokenParams = array();
        $this->IDToken = null;
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


    /************************************************************************************
     * Tokens
     */



    /*
     * Create token
     */
    public function TokenCreate()
    {
       $Result=$this->PreparedResult();
        if ($Result==rcOk)
        {
            $this->IDToken=str_pad(dechex(rand(0,hexdec('EFFFFFFF'))),8,'0');
            $this->SetTokenParam('IDAccount', $this->ID);
        }
        return $Result;
    }



    /*
    * Get Value by name from token
    */
    public function GetTokenParam ($AName, $ADefault)
    {
        return ($AName && $this->IDToken && $this->TokenParams[$AName]) ? $this->TokenParams[$AName] : $ADefault;
    }



    /*
    * Set Value by name for token
    */
    public function SetTokenParam ($AName, $AValue)
    {
        $Result=$this->PreparedResult();
        if ($Result==rcOk)
        {
            if ($AName==null || $AName=='') $Result='ErrorNameNotExists';
            else
                if ($this->IDToken==null || $this->IDToken=='') $Result='ErrorTokenNotCreate';
                else $this->TokenParams[$AName]=$AValue;
        }
        return $Result;
    }



    /*
    * Save Token account to file.
    */
    public function TokenFlush()
    {
        $Result=$this->PreparedResult();
        if ($Result==rcOk)
        {
            if ($this->IDToken==null) $Result='ErrorTokenNotCreate';
            else
            {
                $Path=clGetTokenPath($this->IDSiteCurrent);
                if ($Result==rcOk)
                {
                    $Path=clGetTokenFileName($Path, $this->IDToken);
                    $DirPath=pathinfo($Path,PATHINFO_DIRNAME);
//                    clInf('Path = '.$Path . '\nDirPath' . $DirPath);
                    if (!file_exists($DirPath) && !mkdir($DirPath, FILE_RIGHT, true)) $Result = 'ErrorCreateTokenFolder';
                    else file_put_contents($Path, json_encode($this->TokenParams));
                }
            }
        }
        return $Result;
    }



    /*
    * Load Token account from file.
    */
    public function TokenLoad($AToken)
    {
        $Result=$this->PreparedResult();
        if ($Result==rcOk)
        {
            if ($AToken==null || $AToken='') $Result='ErrorTokenNotExists';
            else
            {
                $Path=clGetTokenPath($this->IDSiteCurrent);
                if ($Result==rkOk)
                {
                    $Path=clGetTokenFileName($Path, $AToken);
                    if (!file_exists($Path)) $Result='ErrorFileTokenNotExists';
                    else
                    {
                        $json=json_decode(file_get_contents($Path));
                        if ($json) $Result='ErrorFormantJsonFile';
                        else $this->TokenParams=$json;
                    }
                }

            }
       }
       if ($Result==rcOk) $this->IDToken=$AToken;
       return $Result;
    }
}



/*
* Return Full Name File by name
*/

function clGetTokenFileName ($APath, $AName)
{
    $l=mb_strlen($AName, 'UTF-8');
    if ($l>1) $APath .= '/' . mb_substr($AName, 0, 1, 'UTF-8');
    if ($l>2) $APath .= '/' . mb_substr($AName, 0, 2, 'UTF-8');
    if ($l>3) $APath .= '/' . mb_substr($AName, 0, 3, 'UTF-8');
    return clPathControl($APath . '/' . $AName . '.json');
}



/*
 * Retun Token Path by site
f  */
function clGetTokenPath($AIDSite)
{
 return  clSitePath($AIDSite) . '/tokens';
}
