<?php

/**
 * Catlair PHP
 *
 * Декларация глобального указателя на данные по сессии SimpleXML
 * Данные формируются либо в функии clSessionCreate либо
 * загружаются функцией clSessionInit из файла
 * still@itserv.ru
 */

$clSession = null;


class TSession
{
    private $File = "";
    private $XML = null;
    public $Role = null;

    /**
     * Конструктор сессии.
     * Получает идентфикатор из куки или создает его.
     */
    public function __construct()
    {
        $Path = $this->Path();
        // Создание папки в которой будет хранится инфа по сессиями если ее нет
        if (!file_exists($Path)) mkdir($Path, FILE_RIGHT, true);
    }



    public function Open($ANew)
    {
        if ($ANew)
        {
            /* Создается новая сессия из имеющейся*/
            $ID = md5(uniqid().uniqid());
            if ($this->XML != null)
            {
                $this->SetID($ID); /* Устанавливаем новый id */
                $this->SetLogin(""); /* Устанавливаем пустой логин */
            }
        }
        else
        {
            /* Получение идентификатора из куки а если такового нет то по юзерагенту и адресу */
            if (array_key_exists('cl_session_id', $_COOKIE)) $ID = $_COOKIE['cl_session_id'];
            else $ID = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
            /* загрузка файла */
            $this->Load($ID);
        }

        /* Создание нового XML в случае если файл не был загружен или ранее не был создан */
        if ($this->XML == null)
        {
            /* создание нового файла сессии */
            $XMLSource = '<?xml version="1.0"?><session/>';
            $this->XML = simplexml_load_string ($XMLSource);
            $this->XML['Remote'] = $_SERVER['REMOTE_ADDR'];
            /* установка параметров */
            $this->SetLanguage(LANG_UNKNOWN);
            $this->SetSite(SITE_DEFAULT);
            $this->SetDomain(DOMAIN_UNKNOWN);
            $this->SetID($ID);
        }

        /* возвращаем на клиента ID... потом он придет с ним  */
        setcookie('cl_session_id', $ID, 0);
    }



    /**
     * Завершение и закрытие сесси, те фактически удаление файла с диска.
     * После создается новая сессия.
     */
    public function Close()
    {
        if (unlink($this->File($this->GetID()))) $Result=rcOk;
        else $Result='ErrorCloseSession';
        if ($Result==rcOk) $this->Open(true);
        return $Result;
    }


    /*
     * Загрузить данные по сессии
     */
    private function Load($AID)
    {
        // Читаем файл
        $File = $this->File($AID);
        if (file_exists($File))
        {
            // Попытки чтения в цикле информации по сесиии. Цикл, для тех случаев когда файл занят
            $XMLSource = '';
            do
            {
                $f = fopen($File, 'r');
                if ($f)
                {
                    $XMLSource = fread($f, 1024);
                    fclose($f);
                }
                else
                {
                    // усыпление для ожидания и что бы не грузить проц
                    usleep(10000);
                }
            } while ($XMLSource=='');
            $this->XML = simplexml_load_string ($XMLSource);
            $this->SetID($AID);
            $this->Role = explode(';',$this->XML['Role']);
        }
    }



    /**
     * Сохранение данных сесси в файл на диск. Обязательно вызывать как
     * минимум один раз при завершении скрипта index.php
     */
    public function Flush()
    {
        // устанавливаем маску, пишем файл, востанавливаем маску
        do {} while (!$this->XML->asXML($this->File($this->GetID())));
    }



    /**
     * Проверка сессии является ли гостевой
     */
    public function IsGuest()
    {
        return $this->GetLogin()=='';
    }



    private function Path()
    {
        return clRootPath().'/session';
    }



    private function File($AID)
    {
        return $this->Path().'/'.$AID.'.xml';
    }



    /**
     * Чтение параметра дескрипта
     * $AKey - имя ключа
     * $ADefault - результат при отсутсвии ключа
     */
    public function Get($AKey, $ADefault)
    {
        if ($this->XML[$AKey]) $Result = (string)$this->XML[$AKey];
        else $Result = $ADefault;
        return $Result;
    }



   /**
    * Запись параметра дескрипта
    * $AKey - имя ключа
    * $AValue - значение ключа
    */
    public function Set($AKey, $AValue)
    {
        if ($this->XML) $this->XML[$AKey] = $AValue;
        return rcOk;
    }



    private function SetID($AID)
    {
        $this->XML['ID'] = $AID;
        return true;
    }


    public function GetID()
    {
        return (string)$this->XML['ID'];
    }


    public function SetLanguage($ALanguage)
    {
        $this->XML['Language'] = $ALanguage;
        return true;
    }


    public function GetLanguage()
    {
        return  (string)$this->XML['Language'];
    }


    public function SetSite($ASite)
    {
        $this->XML['Site']=$ASite;
        return true;
    }


    public function GetSite()
    {
        return  (string)$this->XML['Site'];
    }


    public function SetDomain($ADomain)
    {
        $this->XML['Domain']=$ADomain;
        return true;
    }


    public function GetDomain()
    {
        $Result = (string)$this->XML['Domain'];
        return $Result;
    }


    public function SetLogin($ALogin)
    {
        $this->XML['Login']=$ALogin;
        return true;
    }


    public function GetLogin()
    {
        return (string)$this->XML['Login'];
    }


    /**
     * Очистка списка ролей
     */
    public function &RoleClear()
    {
        $this->Role=array ('guest');
        return $this;
    }


    /*
     * Построение списка ролей
     */
    public function RoleBuild()
    {
        $From=new TDescript();
//        $From->Read($this->GetLogin());
        $From->ParentRead();
        $Param=null;
        $this->Role=array();
        $From->TraceParent('bind_secure', null, null, $Param, $this->Role);
        unset($From);

        foreach($this->Role as $Value) clDeb($Value);
        $this->XML['Role']=implode(';',$this->Role);
    }
}
