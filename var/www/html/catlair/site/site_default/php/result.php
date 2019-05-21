<?php

define ("rcOk", 'Ok');
define ("rcUnknown", 'UnknownError');

/******************************************************************************
 * Catlair PHP
 *
 * Объект обработки результатов исполнения публичных процедур
 * в Content принимается собстветнно контент
 * в процессе работы производится сборка параметров при помощи Set()
 * при завершении сборки вызывается End().
 * в зависимости от его наличия контента производится либо подмена ключей
 * %key% в нем, либо параметры собираются в XML структуру.
 *
 * Струкура резульата
 * <Catlair>
 *      <Header Code="Код_Ошибки" Message="Сообщение об ошибке"/>
 *      <Params>
 *          <Name1>Значение</Name1>
 *          <Name2>Значение</Name2>
 *          ...
 *      </Params>
 *      <Data>
 *          <Group1 Type="Array">
 *              <Record1 Key1="Значение" Key2="Значение" ....>
 *              <Record2 Key1="Значение" Key2="Значение" ....>
 *              ...
 *          </Group1>
 *      </Data>
 * </Catlair>
 *
 * still@itserv.ru
 */



class TResult
{
    private $Code = null; // Result code
    private $Message = null; // Result message
    private $Content = null; // Content with macro in %Example%
    private $Params = null; // Array of params
    private $Data = array(); // Array of records
    private $Detale = array(); // Detale for arrays of records



    /*
     * Constructor
     */
    public function __construct()
    {
        $this->Params = [];
    }



    /*
     * Set content
     */
    public function &SetContent($AContent)
    {
        $this->Content = $AContent;
        return $this;
    }

    /*
     * Get content
     */
    public function GetContent()
    {
        return $this->Content;
    }



    /**
     * Set result code
     */
    public function &SetCode($AValue)
    {
        $this->Code = $AValue;
        return $this;
    }



    /**
     * Get result code
     */
    public function GetCode()
    {
        return $this->Code;
    }



    /**
     * Set result message
     */
    public function &SetMessage($AValue)
    {
        $this->Message = $AValue;
        return $this;
    }



    /**
     * Set result message
     */
    public function GeMessage($AValue)
    {
        return $this->Message;
    }



    /**
     * Устанавливает значение параметра
     */
    public function &Set($AName, $AValue)
    {
        $this->Params[$AName] = $AValue;
        return $this;
    }



    /**
     * Устанавливает значение параметра
     */
    public function Get($AName, $ADafaultValue)
    {
        return $this->Params[$AName];
    }



    /**
     * Добавление ассоциативного массива как как очередная запись в сегдмент Data
     * $AGroup - имя группы записей. Если ранее отсутствовала - создается.
     * $AArray - массив именованых ключей в виде записи
     */
    public function &SetGroup($AGroup, $AName, $AValue)
    {
        // создание массива
        if (!array_key_exists($AGroup, $this->Detale)) $this->Detale[$AGroup] = [];
        $this->Detale[$AGroup][$AName]=$AValue;
        return $this;
    }



    /**
     * Добавление ассоциативного массива как как очередная запись в сегдмент Data
     * $AGroup - имя группы записей. Если ранее отсутствовала - создается.
     * $AArray - массив именованых ключей в виде записи
     */
    public function &AddRecord($AGroup, &$AArray)
    {
        // создание массива
        if (!array_key_exists($AGroup, $this->Data)) $this->Data[$AGroup] = [];
        array_push($this->Data[$AGroup], $AArray);
        return $this;
    }



    /*
     * Завершает сбор параметров и возвращает результат
     * в случае если подменный контент отсутствовал возвращается xml
     */
    public function End()
    {
        clBeg('');
        global $clSession;
        $r = '';

        /*Получение собщения  по коду ошибки*/
        if ($this->Code==rcOk) $Message = 'Ok';
        else
        {
            if (clDescriptExistsAny($clSession->GetSite(), $this->Code)==false) $Message = $this->Code;
            else $Message = clDescriptContentByID($this->Code, $clSession->GetSite(), clGetLang(null));
        }

        /**/
        if ($this->Content!=null)
        {
            // добавление Code и Message что бы они подменились
            $this->Set('Code', $this->Code);
            $this->Set('Message', $Message);

            // подмена параметров
            foreach ($this->Params as $Key => $Value)
            {
                $this->Content = str_replace('%'.$Key.'%', $Value, $this->Content);
                clLog('Result replace:'.$Key.'='.$Value, ltInf);
            }
            $r=$this->Content;
        }
        else
        {
            // входящий контент осутсвует - строим XML
            $r=XML_HEADER;
            $r.='<CatLair>';
            $r.='<Header Code="' . $this->Code . '" ' . 'Message="' . $Message .'"/>';

            clDeb('Result message ['.$Message.']');
            clDeb('Result code ['.$this->Code.']');

            // Сборка прочих параметров в список
            $r.='<Params>';
            foreach ($this->Params as $Key => $Value)
            {
                // построение конента из параметра
                $r.='<'.$Key.'>' . encodeURIComponent($Value) . '</'.$Key.'>';
                // вывод в лог параметра
                $ValueLen = strlen($Value);
                if ($ValueLen > 50) $s=substr($Value, 0, 49) . '...' . $ValueLen;
                else $s = $Value;
                clLog('Result param:' . $Key . '=' . $s, ltInf);
            }
            $r.='</Params>';

            // Сборка списков в ключ Data
            $r.='<Data>';
            foreach ($this->Data as $Name => $Array)
            {
                //Сборка очередного списка
                $r.='<'.$Name.' Type="Array">';
                foreach ($Array as $Index => $Value)
                {
                    // Сборка записи списка
                    $r.='<Record_'.$Index;
                    foreach ($Value as $NameP => $ValueP)
                    {
                        if (gettype($ValueP)!=='object')
                        {
                            $r .= ' ' . $NameP . '="';
                            $r .= encodeURIComponent((string)$ValueP).'"';
                        }
                    }
                    $r.='/>';
                }
                $r.='</'.$Name.'>';
                clLog('Data param:' . $Name, ltInf);
            }
            $r.='</Data>';


            // Сборка списков в ключ Detale
            $r.='<Detale>';
            foreach ($this->Detale as $Name => $Array)
            {
                //Сборка очередного списка
                $r.='<'.$Name;
                foreach ($Array as $Key => $Value)
                {
                    // Сборка записи списка
                    if (gettype($Value)!=='object') $r.=' '.$Key.'="'.encodeURIComponent($Value).'"';
                }
                $r.='/>';
            }

            $r.='</Detale>';
            $r.='</CatLair>';
        }

        unset ($this->Params);
        clEnd('');
        return $r;
    }
}
