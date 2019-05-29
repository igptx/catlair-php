<?php

/**************************************************************************************
 * Catlair PHP Copyright (C) 2019  a@itserv.ru
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
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Descript
 * Система описание сущностей Descript
 * Базовый объект. Распространяет права. Участвует в поиске.
 */


include "descript_utils.php";

define("CONTENT_NONE", 'none');
define("CONTENT_ALL", 'all');
define("RIGHT_UPDATE", 'right_update');
define("RIGHT_INSERT", 'right_insert');
define("RIGHT_DELETE", 'right_delete');
define("FOF_FOE", 'Foe');
define("FOF_FRIEND", 'Frend');


class TDescript
{
    // Параметры
    public $ID = null;                // Идентификатор дескрипта
    public $IDSite = null;          // Идетификатор сайта которому принадлежит дескрипт
    public $IDSiteCurrent = null;   // Идетификатор сайта для которого десрипт загружен
    public $Type = null;            // Тип дескрипта
    // Внутренние объекты
    private $XML;                   // Объект simpleXML содержащий дескрипт
    private $Casted;                // Флаг того что успешно прошла процедура преобразования объекта через Cast
    public $Child;                  // Объект simpleXML связей объекта в сторону детей
    public $Parent;                 // Объект simpleXML связей объекта в сторону родителей
    private $Arrays = [];           // Список загруженных массивов таких как GET POST и тд


    /*
     * Конструктор
     */
    public function __construct()
    {
    }



    public function Assign($AID, $AType, $AIDSite)
    {
        $Result = clDescriptIDValidate($AID);
        if ($Result==rcOk)
        {
            $this -> ID = $AID;
            $this -> Type = $AType;
            $this -> IDSite = $AIDSite;
            $this -> IDSiteCurrent = $AIDSite;
        }
        return rcOk;
    }



    /**
     * Формирование ранее не существующего дескрипта
     * AID - идентификтаор дескрипта
     * AType - тип дескрипта
     * AIDSite - идентификтор сайта
     *
     * Проверяется наличие файла дескрипта и в случае отсутствия дескрипт создается
     * Функция не производит создание файла дескрипта.
     * Cохраниение производится Flush()
     *
     * Выполняет подготовку дескрипта
     */
    public function Create($AID, $AType, $AIDSite)
    {
        clBeg('');
        $Result = rcUnknown;
        if ( $AID==null ) $AID = clGUID();
        $Result = clDescriptIDValidate($AID);
        if ($Result==rcOk)
        {
            // Получение имени файла
            $FileName = clDescriptFile($AIDSite, $AID);
            // Формирование XML случае его отсутствия
            if ($FileName!==false && !file_exists($FileName))
            {
                $XMLSource = XML_HEADER.'<descript/>';
                $this -> XML = new SimpleXMLElement($XMLSource);
                if ($this->XML !== false)
                {
                    //  Установка свойств дескрипа
                    $this -> ID = $AID;
                    $this -> Type = $AType;
                    $this -> IDSite = $AIDSite;
                    $this -> IDSiteCurrent = $AIDSite;
                    // Сохранение свойств в XML
                    $this -> XML -> addChild('ID', $this -> ID); // Сохранинли идентификатор
                    $this -> XML -> addChild('Type', $this -> Type); // Сохранили тип
                    $this -> XML -> addChild('Site', $this -> IDSite); // Сохранили сайт
                    // Результат усперешн
                    $Result = rcOk;
                }
                else $Result = 'ErrorCreateXML';
            } else $Result = 'DescriptAlredyExists';
        }
        // Возвращение результата
        clEnd($Result);
        return $Result;
    }



    /**
     * Читает дескрипт по указанным параметрам
     * в сдучае если дескрипта нет на указанном сайте читает из дефаултного
     * $AID - иденттификатор дескрипта
     * $AIDSite - сайт
     *
     * Выполняет подготовку дескрипта
     */
    public function Read($AID, $AIDSite)
    {
        clBeg('');
        // Получение имени файла
        $Result = clDescriptIDValidate($AID);
        if ($Result==rcOk)
        {
            $IDSiteSource = $AIDSite;
            $FileName = clDescriptFile($AIDSite, $AID);
            // Если файла не существует и запрашиваемый сайт не дефаултный запрашиваме из дефаултного
            if ($AIDSite!=SITE_DEFAULT && !file_exists($FileName))
            {
                $FileName = clDescriptFile(SITE_DEFAULT, $AID);
                $IDSiteSource = SITE_DEFAULT;
            }
            // Если файл все же нашли то пытаемся его загрузить
            if (file_exists($FileName))
            {
                clDeb('File name ['.$FileName.']');
                // Загрузка файла
                $this->XML = simplexml_load_file($FileName);
                if (property_exists($this,'XML'))
                {
                    $this->Type = $this->Get('Type', 'unknown');
                    $this->ID = $this->Get('ID', '');
                    $this->IDSite = $IDSiteSource;
                    $this->IDSiteCurrent = $AIDSite;
                    $Result=rcOk;
                }
                else
                {
                    $Result = 'ErrorParsXML';
                    clErr('XML pars error [' . $FileName . ']');
                }
            }
            else
            {
                $Result = 'FileNotFound';
                clErr('File not found ['.$FileName.']');
            }
        }
        // Возвращаем результат
        clEnd($Result);
        return $Result;
    }



    public function GetFOFStatus()
    {
        if ($this->IDSite === $this->IDSiteCurrent) $Result = FOF_FRIEND;
        else  $Result = FOF_FOE;
        return $Result;
    }



    /**
     * возвращает дескрипт по идентификатору исходя из сайта
     * в случае отсутсвия дескрипта для сайта выбыирает его из умолчального сайта.
     * в случае отсутствия дескрипта для умолчального сайта возвращается false.
     *
     * $AID - идентификатор дескрипта
     * $ASite - идентификатор сайта
     *
     * Выполняет подготовку дескрипта
     */
    public function ReadExisting($AID, $AIDSite)
    {
        $Result = $this->Read($AID, $AIDSite);
        if ($Result!=rcOk) $Result = $this->Read($AID, SITE_DEFAULT);
        return $Result;
    }



    /**
     * Проверка есть ли у дескрипта заголовок xml те он готов к исопльзованию
     * Подготовлен ли дескрипт
     */
    public function Prepared()
    {
        return $this->XML!=null && $this->ID!=null && $this->IDSite!=null && $this->Type!=null;
    }


    /**
     * Проверка есть ли у дескрипта заголовок xml те он готов к исопльзованию
     * Подготовлен ли дескрипт
     */
    public function PreparedResult()
    {
        return $this->Prepared() ? rcOk : 'DescriptNotPrepared';
    }


    /**
     * Проверяе наличие пути для хранения десрипта и созадет его в случае отсутсвия
     */
    public function CheckPath()
    {
        $Path = clDataPath($this->IDSiteCurrent, $this->ID);
        if (file_exists($Path)) $Result=rcOk;
        else
        {
            if (mkdir($Path, FILE_RIGHT, true)) $Result=rcOk;
            else $Result = 'ErrorCreateDescriptFolder';
        }
        return $Result;
    }



    /**
     * Запись подготовленного дескрипта на диск после серии изменений
     */
    public function Flush()
    {
        clBeg('');

        $Result=$this->RightCheck(RIGHT_UPDATE);
        if ($Result == rcOk)
        {
            if (!$this->Prepared()) $Result = 'DescriptIsNotPrepared';
            else
            {
                /* Проверка каталога */
                $Result = $this->CheckPath();
                if ($Result == rcOk)
                {
                    $FileName = clDescriptFile($this->IDSiteCurrent, $this->ID);
                    clDeb('File ['.$FileName.']');

                    // Преобразование в тип и выполненеие действия
                    $dc = $this->Cast();
                    if (method_exists($dc, 'OnBeforeFlush')) $Result = $dc->OnBeforeFlush();
                    else $Result = rcOk;

                    // Сохранение файла
                    if ($Result == rcOk)
                    {
                        if ($this->XML->asXML($FileName)) $Result=rcOk;
                        else $Result='ErrorFlushDescriptXML';
                    }

                    // Исполнение после сохранения
                    if (method_exists($dc, 'OnAfterFlush')) $Result = $dc->OnAfterFlush();
                    else $Result = rcOk;

                    // Истребление кастованного объекта если он таковой
                    if ($dc->GetCasted()) unset($ds);

                    // Отправка дескрипта на индексацию
                    if ($Result==rcOk) $Result=$this->Index(LANG_DEFAULT, false);
                }
            }
        }
        clEnd($Result);
        return $Result;
    }


    /***************************************************************************
     * Работа с параметрами
     */

    /**
     * Чтение параметра дескрипта
     * $AKey - имя ключа
     * $ADefault - результат при отсутсвии ключа
     */
    public function Get($AKey, $ADefault)
    {
        if ($this->XML && $this->XML->$AKey) $r=(string)$this->XML->$AKey;
        else $r = $ADefault;
        return (string)$r;
    }



    /**
     * Запись параметра дескрипта
     * $AKey - имя ключа
     * $AValue - значение ключа
     */
    public function Set($AKey, $AValue)
    {
        if ($this->XML)
        {
            if (!$this->XML->$AKey) $this->XML->addChild($AKey);
            $this->XML->$AKey = $AValue;
        }
        return rcOk;
    }



    public function SetID($AID)
    {
        if ($this->Prepared())
        {
            $this->ID = $AID;
            $this->Set('ID',$AID);
        }
    }




    /**
     * Чтение параметра дескрипта с языком
     */
    public function GetLang($ALang, $AKey, $ADefault)
    {
        if ($this->XML && $this->XML->Langs && $this->XML->Langs->$ALang->$AKey) $r=$this->XML->Langs->$ALang->$AKey;
        else $r = $ADefault;
        return (string)$r;
    }



    /**
     * Чтение параметра дескрипта с языком.
     * в случае отсутсвия параметра на языке $ALang возвращается параметр на дефаултном языке
     * в противном случае возвращается $ADefault
     */
    public function GetLangAny($ALang, $AKey, $ADefault)
    {
        clBeg('');

        if ($this->XML && $this->XML->Langs && $this->XML->Langs->$ALang->$AKey) $r=$this->XML->Langs->$ALang->$AKey;
        else
            if ($this->XML && $this->XML->Langs && $this->XML->Langs->LANG_DEFAULT->$AKey) $r=$this->XML->Langs->LANG_DEFAULT->$AKey;
            else $r = $ADefault;
        clEnd('');
        return (string)$r;
    }



    /**
     * Запись параметра дескрипта с языком
     */
    public function SetLang($ALang, $AKey, $AValue)
    {
        // Проверка наличия (создание) ключа Langs
        if ($this->XML)
        {
            $Langs = $this -> XML -> Langs;
            if (!$Langs) $Langs = $this -> XML -> addChild('Langs', '');

            /// Проверка наличия (создание) ключа Lang
            $Lang = $Langs -> $ALang;
            if (!$Lang) $Lang = $Langs -> addChild($ALang, '');

            $Key = $Lang -> $AKey;
            if (!$Key) $Key = $Lang -> addChild($AKey, '');

            $this->XML->Langs->$ALang->$AKey = $AValue;
        }
        return rcOk;
    }


    /*
     * установка из инкаминг параметров
     * функция получает параметр из инкаминг параметров
     * если их не читает из самого дескрипта
     * а если и там нет, то использует умолчальный параметр
     * $AParam - имя параметра
     */
    public function SetIncome($AParam, &$AParams, $ADefault)
    {
        $this->Set($AParam, clGetIncome($AParam, $AParams, $this->Get($AParam, $ADefault)));
    }

    public function SetLangIncome($AParam, $AIDLang, &$AParams, $ADefault)
    {
        $this->SetLang($AIDLang, $AParam, clGetIncome($AParam, $AParams, $this->GetLang($AIDLang, $AParam, $ADefault)));
    }



    /**
     * преобразование дескрипта из элементарного.
     * после создания дескрипта он является  элементарным и не типизированным.
     * после кнвертации возвращается копия дескрипта
     * подгружаются файл с именем класса classname.php
     * !!! элементарный дескрипт исходник разрушать нельзя до разрушения копии
     */
    public function &Cast()
    {
        clBeg('');
        $ClassName = "T".$this->Type; // определяем тип класса
        clDeb('Convert to class [' . $ClassName .']');

        // проверяем зарегистрирован ли класс
        if (!class_exists($ClassName))
        {
            // если нет то грузим файл PHP с соответсвующим классом
            $FileName = clLibraryFileAny($this->Type, $this->IDSite);
            clDeb('Library ['.$FileName.']');
            if ($FileName && file_exists($FileName)) include ($FileName);
            else clDeb('Library ['.$FileName.'] not found');
        }

        // еще раз проверяем зарегистрирован ли класс
        if (class_exists($ClassName))
        {
            $Result = new $ClassName ();
            $Result->Type = $this->Type;
            $Result->ID = $this->ID;
            $Result->IDSite = $this->IDSite;
            $Result->IDSiteCurrent = $this->IDSiteCurrent;
            $Result->XML = $this->XML;
            $Result->Child = $this->Child;
            $Result->Parent = $this->Parent;
            $Result->Casted = true;
        }
        else
        {
            $Result = $this;
            $Result->Casted = false;
        }
        clEnd('');
        return $Result;
    }


    public function &SetEnabled($AValue)
    {
        if ($AValue) $this->Set('Enabled', 'on');
        else $this->Set('Enabled', 'off');
        return $this;
    }


    public function GetEnabled()
    {
        return $this->Prepared() && $this->Get('Enabled', 'on') == 'on';
    }



    public function GetEnabledStatus()
    {
        if ($this->GetEnabled()) $Result = 'On';
        else $Result = 'Off';
        return $Result;
    }




    public function GetCasted()
    {
        return $this->Casted;
    }



    /*
     * Возвращает ID изображения
     */
    public function GetIDImage($AIDDefault)
    {
        // Смотрим из текущей записи
        $Result = $this->Get('IDImage', null);
        // проверяем если файл то его и возвращаем
        if ($Result == null && $this->Type == TYPE_FILE) $Result = $this->ID;
        if ($Result == null)
        {
            // Из типа объекта
            $t=new TDescript();
            if ($t->Read($this->Type, $this->IDSiteCurrent)==rcOk) $Result = $t->Get('IDImage', null);
            else $Result = $AIDDefault;
            unset($t);
        }
        return $Result;
    }


//------------------------------------------------------------------------------
// Проверка права дескрипта
//------------------------------------------------------------------------------

    public function RightCheck($AIDRight)
    {
//        $r=$this->ChildRead();
//        if ($r==rcOk)
//        {
//            global $clSession;
//            $List=array_intersect($clSession->Role, $this->ChildByBind($AIDRight));
//            if (count($List)==0) $r='NoRight_'.$AIDRight;
//        }
        global $clSession;
        /* если сессия существует те не cli, и сессия гостевая тогда ошибка s*/
        if  ($clSession != null && $clSession->IsGuest()) $Result = 'no_'.$AIDRight;
        else $Result = rcOk;
        return $Result;
    }



    /*
     * Удаление дескрипта
     */
    public function DeleteInternal()
    {
        clBeg('');
        $Result=$this->RightCheck(RIGHT_DELETE);
        if ($Result==rcOk)
        {
            if ($this->GetFOFStatus() == FOF_FOE) $Result = 'FOFIsNotDelete';
            else
            {
                if (!$this->Prepared()) $Result = 'DescriptIsNotPrepared';
                else
                {
                    // запрет удаления рута
                    if ($this->ID == 'root') $Result='UnableDeleteRoot';
                    else
                    {
                        // Очистка детей
                        if ($Result==rcOk) $Result=$this->ChildRead();
                        if ($Result==rcOk) $Result=$this->ChildsPurge();
                        // Очистка родителей
                        if ($Result==rcOk) $Result=$this->ParentRead();
                        if ($Result==rcOk) $Result=$this->ParentsPurge();
                        // Удаление контента файлов и переиндексация по языкам
                        if ($Result==rcOk)
                        {
                            foreach ($this->XML->Langs->children() as $Key=>$Value)
                            {
                                $Lang=(string)$Key;
                                $this->ContentDelete($Key, false);
                                /*Переиндексация*/
                                $Index = new TIndex();
                                $Index->Begin($this->ID);
                                $Result = $Index->Flush($Lang, $this->IDSite, false);
                                unset($Index);
                            }
                        }

                        // Удаление самого дескрипта
                        if ($Result==rcOk)
                        {
                            // Получение имени файла
                            $Path = clDataPath($this->IDSite, $this->ID);
                            if (clDeleteFolder($Path)) $Result = rcOk;
                            else $Result='DescriptDeleteError';
                        }
                    }
                }
            }
        }

        clEnd($Result);
        return $Result;
    }


    public function Delete()
    {
        return $this->DeleteInternal();
    }


/******************************************************************************
 * Работа с массивами
 *
 * Массивы зраняться в отдельных файлах от дескриптов
 */

    /**
     * Сохраняет массив в файл для дескрипта
     * $AKey - имя ключа
     * $AAarray - массив
     */
    public function ArrayStore($AKey, &$AArray)
    {
        clBeg('');
        $Result=$this->RightCheck(RIGHT_UPDATE);
        if ($Result==rcOk)
        {
            if ($this->Prepared())
            {
                $XMLSource = XML_HEADER.'<'.$AKey.'/>';
                $XML = new SimpleXMLElement($XMLSource);
                if ($this->XML === false) $Result = 'Error create XML';
                else
                {
                    // Сборка XML
                    foreach ($AArray as $Key=>$Value)
                    {
                        $XML->$Key = $Value;
                    }

                    /* Проверка каталога */
                    $Result = $this->CheckPath();
                    if ($Result == rcOk)
                    {
                        // Выгузка файла
                        $FileName = clArrayFile($this->IDSiteCurrent, $this->ID, $AKey);
                        if ($XML->asXML($FileName)) $Result=rcOk;
                        else $Result='ErrorFlushDescriptXML';
                    }
                }
            }
        }
        clEnd('');
        return $Result;
    }



    /**
     * Загружает массив с диска, cохраняет его в списке массивов (кэш) и возвращаетего в виде массива
     * $AKey - имя ключа
     * $AAarray - массив
     */
    public function &ArrayLoad($AName)
    {
        clBeg('');
        $this->Arrays[$AName] = [];
        if ($this->Prepared())
        {
            // Загрзка файла
            $FileName = clArrayFileAny($this->IDSiteCurrent, $this->ID, $AName);
            if (file_exists($FileName))
            {
                $XML = simplexml_load_file($FileName);
                if ($XML) foreach ($XML as $Key=>$Value) $this->Arrays[$AName][$Key] = $Value;
            }
        }
        clEnd('Read [' . count($this->Arrays[$AName]) . '] rec');
        return $this->Arrays[$AName];
    }



    /**
     * Загружает массив с диска и засовывает его в результат
     * $AKey - имя ключа
     * $AAarray - массив
     */
    public function ArrayToResult($AKey, &$AResult)
    {
        clBeg('');
        $Result=rcOk;
        if ($this->Prepared())
        {
            // Загрзка файла
            $FileName = clArrayFileAny($this->IDSiteCurrent, $this->ID, $AKey);
            clDeb('File ['.$FileName.']');
            if (file_exists($FileName))
            {
                clDeb('Found array file ['.$FileName.']');
                $XML = simplexml_load_file($FileName);
                if (!$XML) $Result = 'ErrorLoadXML';
                else
                {
                    clDeb('Read XML');
                    foreach ($XML as $Key=>$Value)
                    {
                        $AResult->SetGroup($AKey, (string)$Key, (string)$Value);
                    }
                }
            }
        }
        clEnd($Result);
        return $Result;
    }



    /**
     * Возвращает значение из массива
     * $AArrayName - имя массива
     * $AKey - ключ массива
     * $ADefault - умолчальное значение
     */
    public function GetArrayValue($AName, $AKey, $ADefault)
    {
        /* Загрузка массива ключей если они ранее небыли загружены */
        if (!array_key_exists($AName, $this->Arrays)) $this->ArrayLoad($AName);
        /* получение значения */
        if (array_key_exists($AKey, $this->Arrays[$AName])) $Result = $this->Arrays[$AName][$AKey];
        else $Result = $ADefault;
        return (string)$Result;
    }



/*******************************************************************************
 * Работа с контентом дескрипта
 * Контент хранится в отдельном файле
 * Контент может быть взят из раличных фалов в зависимости от языка и сайта
 */



    /**
     * Возвращает контент из файла или false в случае невозможности получить контент
     */
    public function ContentRead($ALang)
    {
        clBeg('');
        $Result=false;
        $File = clDescriptContentFileAny($this->IDSite, $ALang, $this->ID);
        clDeb('File [' . $File . ']');
        if ($File) $Result=file_get_contents($File);
        clEnd('');
        return $Result;
    }



    /**
     * Записывает контент в файл
     */
    public function ContentWrite($ALang, $AContent)
    {
        /* проверка наличия прав */
        $Result=$this->RightCheck(RIGHT_UPDATE);
        if ($Result==rcOk)
        {
            /* Проверка наличия пути для контента */
            $Result = $this->CheckPath();
            if ($Result==rcOk)
            {
                $File = clDescriptContentFile($this->IDSiteCurrent, $ALang, $this->ID);
                clDeb('Content file ['.$File.']');
                if (trim($AContent) === '')
                {
                    // Удаляем файл если он есть при пустом дескрипте
                    if (file_exists($File))
                    {
                        if (!unlink($File)) $Result=rcOk;
                        else $Result='ErrorDeleteContentFile';
                    }
                }
                else
                {
                    // Сохраняем измененный конетнт
                    $f=fopen($File, 'w');
                    if ($f)
                    {
                        if (fwrite($f, $AContent)!==false) $Result=rcOk;
                        else $Result='ErrorWriteContent';
                        fclose($f);
                    }
                    else $Result='ErrorOpenContentFile';
                }
            }
        }

        // Отправка дескрипта на индексацию
        if ($Result=rcOk) $Result=$this->Index($ALang, false);
        return $Result;
    }


    /**
     * Удаляет конент
     * $AIDLang - язык на котором выполняется удаление контента
     * $AIndexate - выполнять ли переиндексацию после удаления
     */
    public function ContentDelete($ALang, $AIndexate)
    {
        $Result=$this->RightCheck(RIGHT_UPDATE);
        if ($Result==rcOk)
        {
            $File = clDescriptContentFile($this->IDSiteCurrent, $ALang, $this->ID);
            if (file_exists($File))
            {
                if (unlink($File)) $Result=rcOk;
                else $Result='ErrorDeleteContentFile';
            } $Result = rcOk;
            // Отправка дескрипта на индексацию
            if ($Result=rcOk && $AIndexate) $Result=$this->Index($ALang, false);
        }
        return $Result;
    }



    /**
     * Перенос файла контента в другой дескрипт
     * $ADest
     * $AIDLang - язык на котором выполняется перено контента
     *
     * Функция не выполняет индексацию дескрипта в который выполняется копирование
     */
    public function ContentCopy($ADest, $ALang)
    {
        clBeg('');
        $Result = rcOk;
        $FileSource = clDescriptContentFile($this->IDSite, $ALang, $this->ID);
        if (file_exists($FileSource))
        {
            $FileDest = clDescriptContentFile($this->IDSite, $ALang, $ADest);
            if (copy($FileSource, $FileDest)) $Result=rcOk;
            else $Result='ErrorMoveContentFile';
        }
        clEnd($Result);
        return $Result;
    }



    /*
     * подготовка контента из дескрипта.
     * $AIDLang - язык на котором выполняется построение контента
     * &$AResult - структура результата из файла result.php.
     */
    public function &ContentBuildInherited($AIDLang, $AIDSite, &$AResult)
    {
        clLog('', ltBeg);
        if ($this->Type==null || $this->Type=='') $r->Code('DescriptTypeUnknown');
        else
        {
            if ($this->ID==null) $r->Code('DescriptIDUnknown');
            else
            {
                // сбор основных обязательных параметров
                $AResult -> Set('ID', $this->ID);
                $AResult -> Set('Type', $this->Type);
                // пользовательские параметры
                $AResult -> Set('Caption', $this->GetLang($AIDLang, 'Caption', ''));
                $AResult -> Set('IDTypeDefault', $this->Get('IDTypeDefault', 0));
                // внешний вид
                $AResult -> Set('IDImage', $this->Get('IDImage', ''));
                $AResult -> Set('IDImagePreview', $this->GetIDImage(''));
                $AResult -> Set('ColorR', $this->Get('ColorR', 0));
                $AResult -> Set('ColorG', $this->Get('ColorG', 0));
                $AResult -> Set('ColorB', $this->Get('ColorB', 0));
                $AResult -> Set('ColorA', $this->Get('ColorA', 1));
                // настройки
                if ($this->Get('Indexate','')!='') $AResult->Set('Indexate', 'checked="checked"');
                else $AResult->Set('Indexate', '');
                if ($this->GetEnabled())
                {
                    $AResult->Set('Enabled', 'on');
                    $AResult->Set('EnabledFlag', 'On');
                }
                else
                {
                    $AResult->Set('Enabled', 'off');
                    $AResult->Set('EnabledFlag', 'Off');
                }
            }
        }
        clLog('', ltEnd);
        return $this;
    }



    /**
     * Стандартная внешняя функция обработки контента
     */
    public function &ContentBuild($AIDLang, $AIDSite, &$AResult)
    {
        $this->ContentBuildInherited($AIDLang, $AIDSite, $AResult);
        return $this;
    }


/*******************************************************************************
 * Работа со связями дескриптов
 * Родители объекта
 */

    /*
     * Возвращает полное имя файла связей дескрипта c родителями
     */
    public function ParentFile($AIDSite)
    {
        return clDataPath($AIDSite, $this->ID) . '/parents.xml';
    }



    /*
     * Читает файл с перечнемь родителей
     */
    public function ParentRead()
    {
        clBeg('');
        $this->Parent = null;

        $File = $this->ParentFile($this->IDSiteCurrent);
        if (!file_exists($File))
        {
            $File = $this->ParentFile($this->IDSite);
            if (!file_exists($File))
            {
                $XMLSource = '<?xml version="1.0"?><binds/>';
                $this->Parent = new SimpleXMLElement($XMLSource);
            }
        }

        if ($this->Parent == null)
        {
            $this->Parent=simplexml_load_file($File);
            clLog('Loaded parent from file ['.$File.']', ltDeb);
        }

        if ($this->Parent!=null) $Result=rcOk;
        else $Result='ErrorReadParent';

        clEnd($Result);
        return $Result;
    }



    /*
     * Сохраняет файл с перечнемь родителей
     */
    public function ParentFlush()
    {
        clBeg('');
        $Result = $this->RightCheck(RIGHT_UPDATE);
        if ($Result == rcOk)
        {
            /* Проверка наличия пути для контента */
            $Result = $this->CheckPath();
            if ($Result == rcOk)
            {
                /* файл родителей */
                $File = $this->ParentFile($this->IDSiteCurrent);
                clLog('File ['.$File.']', ltDeb);
                if ($this->Parent->asXML($File)) $Result=rcOk;
                else $Result='ErrorParentFlush';
            }
        }
        clEnd($Result);
        return $Result;
    }



    /**
     * начало изменения связей
     */
    public function BindBegin(&$ATo)
    {
        $r=rcOk;
        if ($r==rcOk) $r=$this->ParentRead();
        if ($r==rcOk) $r=$ATo->ChildRead();
        return $r;
    }



    /**
     * завершение изменения связей
     */
    public function BindEnd(&$ATo)
    {
        $r=rcOk;
        if ($r==rcOk) $r=$this->ParentFlush();
        if ($r==rcOk) $r=$ATo->ChildFlush();
        return $r;
    }



    /**
     * Добавляет родителя в список родителей объекта
     * односторонняя функция затрагивает только сам объект
     */
     public function ParentAdd($AIDParent, $AIDBind, $AInherited)
     {
        $Result=$this->RightCheck(RIGHT_UPDATE);
        if ($Result==rcOk)
        {
            $Bind = $this->ParentExist($AIDParent, $AIDBind);
            if ($Bind != null) $Result = 'ParentAlreadyExists';
            {
                $Bind = $this->Parent->addChild('bind');
                $Bind['IDParent']=$AIDParent;
                $Bind['IDBind']=$AIDBind;
                $Bind['Inherited']=$AInherited;
                $Result=rcOk;
            }
        }
        return $Result;
     }



    /**
     * Копирует родительские связи себе с другого объекта
     */
    public function ParentsCopyFrom($ASource)
    {
        clBeg('');
        $Result=rcOk;
        if ($ASource->Parent==null) $Result='SourceParentsNotLoaded';
        else
        {
            foreach ($ASource->Parent as $Bind)
            {
                if ($Result = rcOk)
                {
                    // Получени данных по очередной связи с родителем источника
                    $IDParent=$Bind['IDParent'];
                    $IDBind=$Bind['IDBind'];
                    $Inherited=$Bind['Inherited'];

                    clDeb('Found bind ['.$IDParent.'] bind ['.$IDBind.']');
                    // Создаем родителя для связи, создаем связь с родителем и сохраняем
                    $Parent = new TDescript();
                    $Result = $Parent->Read($IDParent, $this->IDSiteCurrent);
                    if ($Result==rcOk)
                    {
                        $this->BindBegin($Parent);
                        $this->Bind($Parent, $IDBind, $Inherited);
                        $this->BindEnd($Parent);
                    }
                    unset($Parent);
                }
            }
        }
        clEnd($Result);
        return $Result;
    }




    /**
     * Удаляет файл со списком родителей
     */
    public function ParentDelete()
    {
        $FileName = $this->ParentFile($this->IDSiteCurrent);
        if (file_exists($FileName) && !unlink($FileName)) $Result='ErrorDeleteParentFile';
        else $Result=rcOk;
        return $Result;
    }




    /*
     * Возвращае количество родителей объекта при переданной связи
     */
    public function ParentCount($AIDBind)
    {
        $Result=0;
        foreach ($this->Parent as $Bind)
        if ((string)$Bind['IDBind']==$AIDBind || $AIDBind=='*') $Result++;
        return $Result;
    }



//------------------------------------------------------------------------------
// Проверяет наличие родителя в перечне
//------------------------------------------------------------------------------

 public function ParentExist($AIDParent, $AIDBind)
 {
  $r=null;
  foreach ($this->Parent as $Bind)
  {
   if (((string)$Bind['IDParent']==$AIDParent || $AIDParent=='*') &&
       ((string)$Bind['IDBind']==$AIDBind || $AIDBind=='*')) $r=$Bind;
  }
  return $r;
 }



//------------------------------------------------------------------------------
// Удаляет объект из списка родителей объекта
//------------------------------------------------------------------------------

 public function ParentRemove($AIDParent, $AIDBind)
 {
  clLog('Parent ['.$AIDParent.'] remove from ['.$this->ID.'] begin', ltBeg);

  if ($this->Parent!=null)
  {
   do
   {
    $Delete=$this->ParentExist($AIDParent, $AIDBind);
    if ($Delete!=null)
    {
     clLog('Found bind ['.$this->ID.']<['.$AIDBind.']<['.$AIDParent.'] for deleting', ltDeb);
     $dom = dom_import_simplexml($Delete);
     $dom->parentNode->removeChild($dom);
     unset($dom);
    }
   } while ($Delete!=null);
   $Result=rcOk;
  } else $Result='ParentListNotLoaded';

  clLog('Parent remove end with result ['.$Result.']', ltEnd);
  return $Result;
 }


//------------------------------------------------------------------------------
// Удаляет из родителей ссылки на себя
//------------------------------------------------------------------------------

 public function ParentsPurge()
 {
  clBeg('');
  $Result=rcOk;
  if ($this->Parent!=null)
  {
   foreach ($this->Parent as $Bind)
   {
    $IDParent=$Bind['IDParent'];
    $IDBind=$Bind['IDBind'];
    $Parent = new TDescript();
    if ($Parent->Read($IDParent, $this->IDSite)==rcOk)
    {
     if ($Parent->ChildRead()==rcOk)
     {
      $Parent->ChildRemove($this->ID, $IDBind);
      $Parent->ChildFlush();
     }
    }
    unset($Parent);
   }
  } else $Result='ParentListNotLoaded';
  clEnd($Result);
  return $Result;
 }






/**************************************************************************
 * Дети объекта
 */

    /*
     * Возвращает полное имя файла связей дескрипта
     */
    public function ChildFile($AIDSite)
    {
        return clDataPath($AIDSite, $this->ID) . '/childs.xml';
    }


    /*
     * Читает список детей объектов
     */

    public function ChildRead()
    {
        clBeg('');
        $this->Child = null;

        $File = $this->ChildFile($this->IDSiteCurrent);
        if (!file_exists($File))
        {
            $File = $this->ChildFile($this->IDSite);
            if (!file_exists($File))
            {
                $XMLSource = '<?xml version="1.0"?><binds/>';
                $this -> Child = new SimpleXMLElement($XMLSource);
            }
        }

        if ($this->Child == null)
        {
            $this->Child = simplexml_load_file($File);
            clDeb('Loaded parent from file ['.$File.']');
        }

        if ($this->Child!=null) $Result=rcOk;
        else $Result='ErrorReadChild';

        clEnd($Result);
        return $Result;
    }



    /**
     * Копирует детские связи себе с другого объекта
     */
    public function ChildsCopyFrom($ASource)
    {
        clBeg('');
        $Result=rcOk;
        if ($ASource->Child == null) $Result='SourceChildsNotLoaded';
        else
        {
            foreach ($ASource->Child as $Bind)
            {
                if ($Result = rcOk)
                {
                    // Получени данных по очередной связи с ребенком из источника
                    $IDChild=$Bind['IDChild'];
                    $IDBind=$Bind['IDBind'];
                    $Inherited=$Bind['Inherited'];

                    // Создаем ребенка для связи, создаем связь с собой
                    $Child = new TDescript();
                    $Result = $Child->Read($IDChild, $this->IDSiteCurrent);
                    if ($Result==rcOk)
                    {
                        $Child->BindBegin($this);
                        $Child->Bind($this, $IDBind, $Inherited);
                        $Child->BindEnd($this);
                    }
                    unset($Child);
                }
            }
        }
        clEnd($Result);
        return $Result;
    }




    /**
     * Сохраняет список детей для объекта
     */

     public function ChildFlush()
     {
        clBeg('');
        /* Проверка прав для контента */
        $Result = $this->RightCheck(RIGHT_UPDATE);
        if ($Result == rcOk)
        {
            /* Проверка наличия пути для контента */
            $Result = $this->CheckPath();
            if ($Result == rcOk)
            {
                /* Child file */
                $File = $this->ChildFile($this->IDSiteCurrent);
                clDeb('File ['.$File.']');
                if ($this->Child->asXML($File)) $Result=rcOk;
                else $Result='ErrorChildFlush';
            }
        }
        clEnd($Result);
        return $Result;
     }


//------------------------------------------------------------------------------
// Проверка наличия дочернего объекта
//------------------------------------------------------------------------------

 public function ChildExist($AIDChild, $AIDBind)
 {
  $r=null;
  foreach ($this->Child as $Bind)
  {
   if (($AIDChild=='*' || (string)$Bind['IDChild']==$AIDChild) &&
       ($AIDBind=='*' || (string)$Bind['IDBind']==$AIDBind)) $r=$Bind;
  }
  return $r;
 }


//------------------------------------------------------------------------------
// Получение списка связей по фильтру
//------------------------------------------------------------------------------

 public function ChildList($AIDChild, $AIDBind)
 {
  $r=array();
  foreach ($this->Child as $Bind)
  {
   if (($AIDChild=='*' || (string)$Bind['IDChild']==$AIDChild) &&
       ($AIDBind=='*' || (string)$Bind['IDBind']==$AIDBind))
     array_push($r, array('IDChild'=>$Bind['IDChild'],'IDBind'=>$Bind['IDBind']));
  }
  return $r;
 }


//------------------------------------------------------------------------------
// Возвращает массив идентификаторов потомков по ID связи
//------------------------------------------------------------------------------

 public function ChildByBind($AIDBind)
 {
  $r=array();
  foreach ($this->Child as $Bind)
  {
   if ((string)$Bind['IDBind']==$AIDBind) array_push($r, (string)$Bind['IDChild']);
  }
  return $r;
 }


//------------------------------------------------------------------------------
// Заменяет связь одну на другую
//------------------------------------------------------------------------------

 public function ChildConvertBind($AIDBindFrom, $AIDBindTo)
 {
  foreach ($this->Child as $Bind)
  {
   if ((string)$Bind['IDBind']==$AIDBindFrom) $Bind['IDBind']=$AIDBindTo;
  }
  return rcOk;
 }

 public function ChildCount($AIDBind)
 {
  $Result=0;
  foreach ($this->Child as $Bind)
  {
   if ((string)$Bind['IDBind']==$AIDBind || $AIDBind=='') $Result++;
  }
  return $Result;
 }


    /**
     * Добавляет потомка в список детей объекта
     * односторонняя функция затрагивает только сам объект
     * требуется право RIGHT_INSERT
     */
     public function ChildAdd($AIDChild, $AIDBind)
     {
        $Result=$this->RightCheck(RIGHT_INSERT);
        if ($Result == rcOk)
        {
            $Bind = $this->ChildExist($AIDChild, $AIDBind);
            if ($Bind != null) $Result = 'ChildAlreadyExists';
            {
                $Bind = $this->Child->addChild('bind');
                $Bind['IDChild']=$AIDChild;
                $Bind['IDBind']=$AIDBind;
                $Result=rcOk;
            }
        }
        return $Result;
     }



//------------------------------------------------------------------------------
// Удаляет запись о потомке
//------------------------------------------------------------------------------

 public function ChildRemove($AIDChild, $AIDBind)
 {
  clLog('Child ['.$AIDChild.'] remove from ['.$this->ID.'] begin', ltBeg);

  if ($this->Child!=null)
  {
   do
   {
    $Delete=$this->ChildExist($AIDChild, $AIDBind);
    if ($Delete!=null)
    {
     clLog('Found bind ['.$this->ID.']<['.$AIDBind.']<['.$AIDChild.'] for deleting', ltDeb);
     $dom = dom_import_simplexml($Delete);
     $dom->parentNode->removeChild($dom);
     unset($dom);
    }
   } while ($Delete!=null);
   $Result=rcOk;
  } else $Result='ChildListNotLoaded';

  clLog('Child remove end with result ['.$Result.']', ltEnd);
  return $Result;
 }


   /**
    * Удаляет из детей ссылки на себя
    */

    public function ChildsPurge()
    {
        $Result=rcOk;
        if ($this->Child!=null)
        {
            foreach ($this->Child as $Bind)
            {
                $IDChild=$Bind['IDChild'];
                $IDBind=$Bind['IDBind'];
                $Child = new TDescript();
                if ($Child->Read($IDChild, $this->IDSite)==rcOk)
                {
                    if ($Child->ParentRead()==rcOk)
                    {
                        $Child->ParentRemove($this->ID, $IDBind);
                        $Child->ParentFlush();
                    }
                }
                unset($Child);
            }
        } else $Result='ChildListNotLoaded';
        return $Result;
    }



    /*
     * Удаляет файл со списком детей
     */
    public function ChildDelete()
    {
        $Result=rcOk;
        $FileName = $this->ChildFile($this->IDSiteCurrent);
        if (file_exists($FileName))
        if (!unlink($FileName)) $Result='ErrorDeleteChildFile';
        return $Result;
    }



    /*
     * Включение индексации
     */
    public function SetIndexate($AIndexate)
    {
        if ($AIndexate) $this->Set('Indexate','on');
        else$this->Set('Indexate','');
        return $Result;
    }



    /*
     * Статус индексакции контента
     */
    public function GetIndexate()
    {
        return (string)$this->Get('Indexate','')=='on';
    }



    /**
     * Индексирует дескрит для полнотектстового поиска
     */
    public function Index($ALang, $AStoreRebuild)
    {
        clBeg('');
        if (!$this->Prepared()) $Result = '$DescriptNotPrepared';
        else
        {
            // Создание индексатора
            $Index = new TIndex();
            $Index->Begin($this->ID);
            // Индексация идентификатора для точного поиска
            $Index->Pars(clIndexIDString($this->ID), ptStrict);
            // Индексация для поиска по типу
            $Index->Pars(clIndexTypeString($this->Type), ptStrict);
            // Индексация параметорв из массива поста
            $Post = $this->ArrayLoad('Post');
            foreach ($Post as $Key=>$Value)
            {
                clDeb($Key.'='.$Value);
                if (strpos($Key, 'ID')===0) $Index->Pars(clIndexArrayString($Key,$Value), ptStrict);
            }
            // Индексация идентификаторва для плавного поиска
            $Index->Pars((string)$this->ID, ptLike);
            // Индексация заголовка
            $Index->Pars((string)$this->GetLang($ALang, 'Caption', ''), ptLike);
            // Индексация для поиска по типу
            $Index->Pars($this->Type, ptLike);
            // Индексация по заголовку типа
            $Index->Pars(clDescriptCaptionByID($this->Type, $this->IDSite, $ALang), ptLike);
            // Индексация контента в случае если она включена
            if ($this->GetIndexate())
            {
                $Content = (string) $this->ContentRead($ALang);
                $Index->Pars($Content,  ptLike);
            }
            //Выгрузка результатов индексации
            $Result = $Index->Flush($ALang, $this->IDSite, $AStoreRebuild);
            //Удаление индексатора
            unset($Index);
        }
        clEnd($Result);
        return $Result;
    }



//------------------------------------------------------------------------------
// Рекурсивная трасировка дерва вниз начиная с текущего дескрипта по его детям
//------------------------------------------------------------------------------
 public function Trace($AIDBind, $ABefore, $AAfter, &$AParams, &$AStack)
 {
  clBeg('Tracing begin');
  // создание перечня обследованых узлов
  if ($AStack===null) $AStack=array();
  // Выполнеение действия над узлом
  if ($ABefore!=null) $Result=call_user_func($ABefore, $this, $AParams);
  // Сохранение узла как обработанного
  array_push($AStack, $this->ID);
  // Обработка
  $Result=$this->ChildRead();
  if ($Result==rcOk)
  {
   foreach ($this->Child as $Bind)
   {
    $IDChild=$Bind['IDChild'];
    $IDBind=$Bind['IDBind'];
    if (($AIDBind=='*' || $IDBind==$AIDBind) && array_search($IDChild, $AStack)===false)
    {
     $Child = new TDescript();
     if ($Child->Read($IDChild, $this->IDSite)==rcOk)
     {
        $Child->Trace($AIDBind, $ABefore, $AAfter, $AParams, $AStack);
     }
     unset($Child);
    }
   }
  }
  if ($AAfter!=null) $Result=call_user_func($AAfter, $this, $AParams);
  clEnd('Tracing end');
  return $Result;
 }



    //------------------------------------------------------------------------------
    // Рекурсивная трасировка дерва верх начиная 1с текущего дескрипта по его детям
    //------------------------------------------------------------------------------
    public function TraceParent($AIDBind, $ABefore, $AAfter, &$AParams, &$AStack)
    {
        clBeg('Tracing parent begin');

        // создание перечня обследованых узлов
        if ($AStack===null) $AStack=array();
        // Выполнеение действия над узлом
        if ($ABefore!=null) $Result=call_user_func($ABefore, $this, $AParams);
        // Сохранение узла как обработанного
        array_push($AStack, $this->ID);
        // Обработка
        $Result=$this->ParentRead();
        if ($Result==rcOk)
        {
            foreach ($this->Parent as $Bind)
            {
                $IDParent=$Bind['IDParent'];
                $IDBind=$Bind['IDBind'];
                if (($AIDBind=='*' || $IDBind==$AIDBind) && array_search($IDParent, $AStack)===false)
                {
                    $Parent = new TDescript();
                    if ($Parent->Read($IDParent, $this->IDSite)==rcOk)
                    {
                    $Parent->TraceParent($AIDBind, $ABefore, $AAfter, $AParams, $AStack);
                    }
                    unset($Parent);
                }
            }
        }
        if ($AAfter!=null) $Result=call_user_func($AAfter, $this, $AParams);

        clEnd('Tracing parent end');
        return $Result;
    }



    public function RenameID($ANewID)
    {
        clBeg('');
        $Result=$this->RightCheck(RIGHT_UPDATE);
        if ($Result==rcOk)
        {
            if (clDescriptExists($this->IDSite, $ANewID)) $Result = 'IdentifyExists';
            else
            {
                if (!$this->Prepared()) $Result = 'DescriptNotPrepared';
                else
                {
                    // Копирование дескрипта
                    $New = new TDescript();
                    $Result = $New->Read($this->ID, $this->IDSiteCurrent);
                    if ($Result == rcOk)
                    {
                        // Устанавливаем новый идентификатор
                        $New->SetID($ANewID);

                        // Контент переносится по перечню языков
                        foreach ($this->XML->Langs->children() as $Key=>$Value)
                        {
                            $Lang = (string)$Key;
                            $Result = $this->ContentCopy($ANewID, $Lang);
                        }
                        /* Сохранние нового дескрипта */
                        $Result = $New->Flush();

                        /* Пересановка связей*/
                        if ($Result==rcOk) $Result = $this->Replace($New);

                        // Удаление текущего дескрипта исходника
                        if ($Result == rcOk) $Result = $this->Delete();
                    }
                    unset($New);
                }
            }
        }
        clEnd($Result);
        return $Result;
    }


/*****************************************************************************************************
 * управление связями объекта
 */



    /**
     * Привязка объекта к родителю
     */
    public function Bind(&$AParent, $AIDBind, $AInherited)
    {
        clBeg('');
        if ( $this->ID == $AParent->ID) $Result='CanNotLinkItself';
        else
        {
            // Говорим родителю что у него есть новый ребенок
            $Result = $AParent->ChildAdd($this->ID, $AIDBind);
            // Если получилось то говорим что объект привязан к родителю
            if ($Result == rcOk) $Result = $this->ParentAdd($AParent->ID, $AIDBind, $AInherited);
        }
        clEnd($Result);
        return $Result;
    }



    /*
     * Разрывание связи объекта и родителя
     */
    public function Unbind(&$AParent, $AIDBind)
    {
        clBeg('');

        $r=rcOk;
        if ($this->ID=='root' && $AParent->ID=='') $r='UnableRootUnbind';
        if ($AParent->ID!='')
        {
            if ($r==rcOk) $r=$AParent->ChildRemove($this->ID, $AIDBind);
            if ($r==rcOk) $r=$this->ParentRemove($AParent->ID, $AIDBind);
        } $r=rcOk;

        // Перенос объекта в trash если он никуда не ссылается и не root
        if ($r==rcOk && $this->ID!='root' && $this->ParentCount('bind_default')==0)
        {
            $Trash = new TDescript();
            $Trash->Read(FOLDER_TRASH, $this->IDSite);
            $this->BindBegin($Trash);
            $r=$this->Bind($Trash, BIND_DEFAULT, false);
            $this->BindEnd($Trash);
            unset($Trash);
        }

        // Завершение
        clEnd($r);
        return $r;
    }



    /*
     * Замещает связи свои другим объектом в одителях и детях
     */
    public function Replace($AOther)
    {
        clBeg('');
        if ($this->GetFOFStatus()=='Foe') $Result='FoeDoesNotReplace';
        else
        {
            $Result=$this->RightCheck(RIGHT_UPDATE);

            /* Операция с родителями */
            if ($Result==rcOk)
            {
                $Result = $this->ParentRead();
                if ($Result ==rcOk )
                {
                    foreach ($this->Parent as $Bind)
                    {
                        $IDParent=$Bind['IDParent'];
                        $IDBind=$Bind['IDBind'];
                        $Inherited=$Bind['Inherited'];

                        $Parent = new TDescript();
                        $Result = $Parent->Read($IDParent, $this->IDSite);
                        /*Связываем с другой объект с родителями*/
                        if ($Result == rcOk) $Result = $AOther->BindBegin($Parent);
                        if ($Result == rcOk) $Result = $AOther->Bind($Parent, $IDBind, $Inherited);
                        if ($Result == rcOk) $Result = $AOther->BindEnd($Parent);
                        /*Разрываем связи родителей с текущим объектом*/
                        if ($Result == rcOk) $Result = $this->BindBegin($Parent);
                        if ($Result == rcOk) $Result = $this->Unbind($Parent, $IDBind);
                        if ($Result == rcOk) $Result = $this->BindEnd($Parent);

                    }
                }
            }

            /* Операция с детьми */
            if ($Result==rcOk)
            {
                $Result = $this->ChildRead();
                if ($Result ==rcOk )
                {
                    foreach ($this->Child as $Bind)
                    {
                        $IDChild=$Bind['IDChild'];
                        $IDBind=$Bind['IDBind'];

                        $Child = new TDescript();
                        $Result = $Child->Read($IDChild, $this->IDSite);

                        if ($Result == rcOk) $Result = $Child->BindBegin($AOther);
                        if ($Result == rcOk) $Result = $Child->Bind($AOther, $IDBind, true);
                        if ($Result == rcOk) $Result = $Child->BindEnd($AOther);

                        if ($Result == rcOk) $Result = $Child->BindBegin($this);
                        if ($Result == rcOk) $Result = $Child->Unbind($this, $IDBind);
                        if ($Result == rcOk) $Result = $Child->BindEnd($this);
                    }
                }
            }

        }
        clEnd($Result);
        return $Result;
    }




    /**
     * Перемещает объект из одного родителя в другой копируя связи
     */

    public function Move($AIDFrom, $AIDTo, $AIDBind)
    {
        clBeg('');
        if ($this->GetFOFStatus()=='Foe') $Result='FOFDoesNotMove';
        else
        {
            clLog('Moving Descript ['.$this->ID.'] from ['.$AIDFrom.'] to ['.$AIDTo.'] bind ['.$AIDBind.']', ltDeb);

            if ($AIDFrom==$AIDTo) $Result='EqualFromAndTo';
            else
            {
                $To=new TDescript();
                $Result = $To->Read($AIDTo, $this->IDSiteCurrent);
                if ($Result==rcOk) $Result = $this->BindBegin($To);
                if ($Result==rcOk) $Result = $this->Bind($To, $AIDBind, false);
                if ($Result==rcOk) $Result = $this->BindEnd($To);
                unset($To);

                $From=new TDescript();
                if ($Result==rcOk) $Result = $From->Read($AIDFrom, $this->IDSiteCurrent);
                if ($Result==rcOk) $Result = $this->BindBegin($From);
                if ($Result==rcOk) $Result = $this->Unbind($From, $AIDBind, true);
                if ($Result==rcOk) $Result = $this->BindEnd($From);
                unset($From);
            }

            //  $this->Index(LANG_DEFAULT);
        }
        clEnd($Result);
        return $Result;
    }
}
