<?php

/*
 * Система отладки
 * Catlair PHP
 *
 * Create: 2017
 * Update:
 * 05.03.2019
 *
 * still@itserv.ru
 */


include "license.php";

define ("ltBeg", '>');
define ("ltEnd", '<');
define ("ltInf", 'I');
define ("ltErr", 'X');
define ("ltWar", 'W');
define ("ltDeb", '#');

define ("chLine", '--------------------------------------------------------------------------------');
define ("chEnd", chr(10));
define ("EOL", chr(10));

class TLog
{
    // Приватная декларация
    private $ID;
    private $Handle = -1;
    private $Last;
    private $Begin;
    private $End;
    private $Shift = 0;

    // Публичные декларации
    public $Enabled = true; // Ведение лога. В случае false лог не ведется
    public $Job = true;
    public $Debug = true;
    public $Info = true;
    public $Error = true;
    public $Warning = true;

    private $Stack = null;

    private $Trace = [];

    public function Path()
    {
        return sys_get_temp_dir().'/catlair/log';
    }

    public function File()
    {
        return $this->Path().'/'.$this->ID;
    }



    public function Write($AString)
    {
        if ($this->Handle == -1) print($AString); // To console
        else fwrite($this->Handle, $AString); // To file
        return $this;
    }


    public function Start($AEnabled)
    {
        $this->Shift = 0;
        $this->Stack = [];

        /* сброс массива трассировок */
        $this->Trace = [];

        // Определение имени файла
        if (array_key_exists('REQUEST_URI', $_SERVER))
        {
            $File=$_SERVER['REQUEST_URI'];
            if ($File=='' || $File=="/") $File=__FILE__;
            $File = str_replace('/', '', $File);
            $File = str_replace('?', '', $File);
            $File = str_replace('%', '', $File);
            $File = str_replace('&', '__', $File);
            $File = str_replace('=', '~', $File);
            // Если имя файла слишком длинное то делаем из него MD5
            if (strlen($File)>255) $File = md5($File);
            $this->ID = $File . '.txt';
        }
        else $File="";

        $fp = $this->Path();
        $fn = $this->File();
        if ($File!="" && !file_exists($fp)) mkdir($fp, FILE_RIGHT, true);

        $this -> Enabled = $AEnabled;
        if ($File!="" && $this->Enabled) $this -> Handle =  fopen($this->File(), 'w+');
        else $this -> Handle = -1; // вывод на консоль

        // информация о начале логирования

        $this -> Begin = microtime(true);
        $this -> Last = $this -> Begin;

        $this -> Record('Start', ltBeg);
        $this -> Record(chLine, ltDeb);
        $this -> Record('Catlair trace log start: '.date('Y-m-d H:i:s'), ltDeb);
        $this -> Record(chLine, ltDeb);

        clLicense();

        if (isset($_COOKIE) && count($_SERVER)>0)
        {
            $this -> Record('$_SERVER', ltBeg);
            foreach ($_SERVER as $Key=>$Value) $this -> Record ($Key.'='.$Value, ltDeb);
            $this -> Record('$_SERVER', ltEnd);
        }

        if (isset($_COOKIE) && count($_COOKIE)>0)
        {
            $this -> Record('$_COOKIE', ltBeg);
            foreach ($_COOKIE as $Key=>$Value) $this -> Record($Key.'='.$Value, ltDeb);
            $this -> Record('$_COOKIE', ltEnd);
        }

        if (isset($_GET) && count($_GET)>0)
        {
            $this->Record('$_GET', ltBeg);
            foreach ($_GET as $Key=>$Value) $this->Record($Key.'='.$Value, ltDeb);
            $this->Record('$_GET', ltEnd);
        }

        if (count($_POST)>0)
        {
            $this->Record('$_POST', ltBeg);
            foreach ($_POST as $Key=>$Value) $this->Record($Key.'='.$Value, ltDeb);
            $this->Record('$_POST', ltEnd);
        }
    }



    public function Stop()
    {
        $this->End = microtime(true);
        $this->Record('Stop', ltEnd);
        $this->TraceOut();
        if ($this->Handle != -1 && $this->Enabled) fclose($this->Handle);
        else print(EOL.EOL);
    }



    public function Record($AMessage, $AType)
    {
        /* проверка необходимсти логирования*/
        if
        (
            $this->Enabled &&
            (
                $AType==ltErr && $this->Error ||
                $AType==ltDeb && $this->Debug ||
                $AType==ltWar && $this->Warning ||
                $AType==ltInf && $this->Info ||
                ($AType==ltBeg || $AType==ltEnd) && $this->Job
            )
        )
        {
            $n=microtime(true);

            if ($AType==ltEnd)
            {
                /* возврат сдвижки */
                $this->Shift = $this->Shift - 1;
                if ($this->Shift < 0) $this->Shift = 0;
            }

            /* формирование основной строки */
            $rs = EOL . str_pad((string)(number_format(($n-$this->Last) * 1000, 2,'.',' ')), 9, ' ', STR_PAD_LEFT). ' ' . $AType . ' ' . str_repeat('.', $this->Shift*4).$AMessage;

            if ($AType==ltBeg)
            {
                /* сдвижка */
                $this->Shift = $this->Shift + 1;
                array_push($this->Stack, $n);
            }

            $this->Last = $n;

            if ($AMessage=='')
            {
                $arr=debug_backtrace();
                if (count($arr)>2)
                {
                    if ($AMessage=='' && $AType==ltEnd) $rs .= 'End of ' . (string)$arr[2]['function'];
                    else
                    {
                        $rs.=(string)$arr[2]['function'] . '(';
                        foreach($arr[2]['args'] as $Value)
                        {
                            switch (gettype($Value))
                            {
                                case 'string': if (strlen($Value)>51) $s = 's:'.substr($Value, 0, 50).'...'.strlen($Value); else $s='s:'.$Value; break;
                                case 'array': $s='ARRAY:'.(string)count($Value);break;
                                case 'boolean': $s='b:'.(string)$Value;break;
                                case 'integer': $s='i:'.(integer)$Value;break;
                                case 'double': $s='d:'.$Value;break;
                                case 'object': $s='OBJECT';break;
                                case 'RESOURCE': $s='RESOURCE';break;
                                case 'NULL': $s='NULL';break;
                            }
                            $s = str_replace(chEnd,' ', $s);
                            $rs.= '['.$s.']';
                        }
                        $rs.=')';
                    }
                }
            }


            /* вывод времени исполнения цикла между бегином и эндом */
            if ($AType==ltEnd && count($this->Stack)>0)
            {
                $Delta = $n-array_pop($this->Stack);
                $rs .= ' #' . number_format($Delta*1000, 2, '.', ' ').' ms';

                /* сохранение статистики */
                $arr=debug_backtrace();
                if (count($arr)>2)
                {
                    $FunctionName = (string)$arr[2]['function'];
                    if (array_key_exists($FunctionName, $this->Trace))
                    {
                        $this->Trace[$FunctionName]['Delta'] = $this->Trace[$FunctionName]['Delta'] + $Delta;
                        $this->Trace[$FunctionName]['Count']++;
                    }
                    else
                    {
                        $this->Trace[$FunctionName]['Delta'] = $Delta;
                        $this->Trace[$FunctionName]['Count'] = 1;
                    }
                }
            }


            // Out log information
            if ($this->Handle == -1) print($rs); // To console
            else fwrite($this->Handle, $rs); // To file
        }
    }



    private function TraceOut()
    {
        function TraceSort ($a, $b)
        {
            if ($a['Delta']>$b['Delta']) return -1;
            else if ($a['Delta']<$b['Delta']) return 1;
                else return 0;
        }
        $this->Record('Trace information', ltBeg);
        uasort($this->Trace, 'TraceSort');
        foreach ($this->Trace as $Key => $Value) $this -> Record
        (
            str_pad($Key, 30, ' ', STR_PAD_LEFT) .
            str_pad((string)(number_format($Value['Delta'] * 1000, 2,'.',' ')), 15, '.', STR_PAD_LEFT) . 'ms' .
            str_pad($Value['Count'], 10, '.', STR_PAD_LEFT),
            ltDeb
        );
        $this->Record('', ltEnd);
    }
}



/*
 * Функция для быстрого вызова отладчика
 */

$clLoger = new TLog();



function &clLog($AMessage, $AType)
{
    global $clLoger;
    $clLoger->Record($AMessage, $AType);
    return $clLoger;
}



function &clDeb($AMessage)
{
    global $clLoger;
    $clLoger->Record($AMessage, ltDeb);
    return $clLoger;
}



function &clInf($AMessage)
{
    global $clLoger;
    $clLoger->Record($AMessage, ltInf);
    return $clLoger;
}



function &clWar($AMessage)
{
    global $clLoger;
    $clLoger->Record($AMessage, ltWar);
    return $clLoger;
}



function &clErr($AMessage)
{
    global $clLoger;
    $clLoger->Record($AMessage, ltErr);
    return $clLoger;
}



function &clBeg($AMessage)
{
    global $clLoger;
    $clLoger->Record($AMessage, ltBeg);
    return $clLoger;
}



function &clEnd($AMessage)
{
    global $clLoger;
    $clLoger->Record($AMessage, ltEnd);
    return $clLoger;
}



function &clDump($ACaption, $AArray)
{
    global $clLoger;
    clBeg($ACaption . ' Count ['. count($AArray) .']');
    foreach ($AArray as $Key=>$Value) clDeb($Key . ' = "' . $Value . '"');
    clEnd('');
    return $clLoger;
}
