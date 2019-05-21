<?php
/*
 * Обработка списков дескриптов
 * Формирует список данных из разных источников в виде массива объектов ключ=значение
 * Далее может собирать данные вокруг массива
 *
 * still@itserv.ru
 */

class TDescripts
{
    /*Массив значений*/
    public $Array = array();



/******************************************************************************
 * формирование данных
 * операции стрящие список ID дескриптов на которые в последствии могет быть
 * применены процедуры сбора данных Select* и операции
 */


    /*
     * Формирование списка данных из простого одномерного массива
     * AArray - одномерный массив
     * AKeyField - имя поля в которое будет помещено значение массива
     */
    public function &LoadFromArray(&$AArray, $AField)
    {
        foreach ($AArray as $Index=>$Value) array_push($this->Array, array($AField=>$Value));
        return $this;
    }



    /*
     * Формирование списка данных из массива нумерованных ключей в формате KeyN =>Value где N - индекс от 0 до ...
     * AArray - массив ключей
     * AFields - массив (перечень) имен полей
     */
    public function &LoadFromArrayValues(&$AArray, $AFields)
    {
        clBeg('');
        $Index = 0;
        do
        {
            $RecordExists = false;
            $Record = array();
            /* Перебор всех излвекаемых полей */
            foreach ($AFields as $Name)
            {
                /* Формируем имя возможного параметра */
                $FieldName = $Name . $Index;
                /* Проверяем наличие параметра во входящем массиве */
                $ValueExists = array_key_exists($FieldName, $AArray);
                $RecordExists = $RecordExists || $ValueExists;
                /* Если обнаружено значение то прописываем его в текущую запись, в противном случае прописываем Null*/
                if ($ValueExists) $Record[$Name] = (string)$AArray[$FieldName];
                else $Record[$Name] = null;
            }
            if ($RecordExists) array_push($this->Array, $Record);
            $Index++;
        }
        while ($RecordExists);
        clEnd('');
        return $this;
    }



    /*
     * Формирование списка данных из POST данными в формает KEY_NUMBER_FIELD
     * AField - имя ключа KEY
     * Процедура перебирает весь пост выбирая поля начниающиеся с $AKey
     * читает индекс очередной
     */
    public function &LoadFromPost($AGroup)
    {
        clBeg('');
        $LastIndex = null;
        /* Первый проход создание записей */
        foreach ($_POST as $Key=>$Value)
        {
            $a = explode('_', $Key, 3);
            if ($a[0]==$AGroup)
            {
                $Index = (int)$a[1];
                if ($LastIndex != $Index)
                {
                    array_push($this->Array, array());
                    $LastIndex = $Index;
                }
            }
        }
        /* Второй проход сбор данных */
        foreach ($_POST as $Key=>$Value)
        {
            $a = explode('_', $Key, 3);
            if ($a[0]==$AGroup)
            {
                $Index = (int)$a[1];
                $Name = $a[2];
                $this->Array[$Index][$Name] = $Value;
            }
        }
        clEnd('');
        return $this;
    }



    /*
     * Выбор записей по поисковым парамерам при помощи индексатора
     */
    public function LoadFromIndex($AIDSite, $AIDLang, $AFind, $AIDParent, $AIDBind)
    {
        clBeg('');
        /* Поиск по перечню детей */
        if ($AIDParent!=null)
        {
            $Parent = new TDescript();
            $Parent->Read($AIDParent, $AIDSite);
            $Parent->ChildRead();
            $ListChild = $Parent->ChildByBind($AIDBind);
            unset($Parent);
        }

        /* Поиск в идексиованных файлах */
        if ($AFind!='')
        {
            $Index = new TIndex();
            $ListFind = $Index->Find($AFind, $AIDLang, $AIDSite);
            unset($Index);
        }

        /* Определенеи результатов поиска в зависимости от родителя или строки поиск */
        if ($AIDParent==null && $AFind=='') $List = [];
        if ($AIDParent!=null && $AFind=='') $List = $ListChild;
        if ($AIDParent==null && $AFind!='') $List = $ListFind;
        if ($AIDParent!=null && $AFind!='') $List = array_intersect($ListChild, $ListFind);

        /* Построение списка дескриптов и возврат в виде записей */
        $this->LoadFromArray($List, 'ID'); // построение идеентификаторов
        clEnd('');
    }


/******************************************************************************
 * выборки из дескриптов
 */

    /*
     * Выборка основных параметров дескрипта к списку записей
     * $IDLanguage - язык построения
     * $FieldID - имя поля с идентификатором
     */
    public function &SelectDescript($AIDSite, $AIDLang, $AFieldID, $AIDBind)
    {
        clBeg('');
        foreach ($this->Array as &$Record)
        {
            if (!array_key_exists($AFieldID.'Descript', $Record))
            {
                $d=new TDescript();
                $r=$d->Read($Record[$AFieldID], $AIDSite);
            }
            else $d=$Record[$AFieldID.'Descript'];

            if ($d->Prepared())
            {
                // Сборка параметров
                $Record[$AFieldID.'Descript'] = $d;
                $Record['Caption'] = $d->GetLangAny($AIDLang, 'Caption', $d->ID);
                $Record['Indexate'] = $d->Get('Indexate', 'off');
                $Record['IDSite'] = $d->IDSite;
                $Record['FOF'] = $d->GetFOFStatus();
                $Record['Enabled'] = $d->GetEnabledStatus();
                // Изображение берется из самого дескрипта или из ссылки
                $Record['IDImagePreview'] = $d->GetIDImage('');

//                if ($d->ChildRead()==rcOk) $Record['ChildCount'] = $d->ChildCount($AIDBind);
//                else $Record['ChildCount']=0;
            }
            else $Record['Caption']=$r;
//            unset($d);
        }
        clEnd('');
        return $this;
    }



    /*
     * Выборка основных параметров дескрипта к списку записей
     * $IDLanguage - язык построения
     * $FieldID - имя поля с идентификатором
     */
    public function &SelectChildsCount($AIDSite, $AFieldID, $AIDBind)
    {
        clBeg('');
        foreach ($this->Array as &$Record)
        {
            if (!array_key_exists($AFieldID.'Descript', $Record))
            {
                $d=new TDescript();
                $r=$d->Read($Record[$AFieldID], $AIDSite);
            }
            else $d=$Record[$AFieldID.'Descript'];

            if ($d->Prepared())
            {
                if ($d->ChildRead()==rcOk) $Record['ChildCount'] = $d->ChildCount($AIDBind);
                else $Record['ChildCount']=0;
            }
        }
        clEnd('');
        return $this;
    }



    /*
     * Получение данных по ключевому полю из массивов дескрипта
     *
     * $AIDSite - идентификатор сайта
     * $AFieldID - идентификатор ключевого поля
     * $AArrayName - имя поля с идентификатором
     * $AFileds - перечень полей для выбора из массива дескрипта
     */
    public function &SelectDescriptArray($AIDSite, $AFieldID, $AArrayName, $AFields)
    {
        clBeg('');
        $Result = rcOk;
        foreach ($this->Array as &$Record)
        {
            if (!array_key_exists($AFieldID.'Descript', $Record))
            {
                // Создаем очредной дескрип и читаем его
                $d=new TDescript();
                $r=$d->Read($Record[$AFieldID], $AIDSite);
            }
            else $d=$Record[$AFieldID.'Descript'];

            if ($d->Prepared())
            {
                // Читаем массив из дескрипта
                $Post = $d->ArrayLoad($AArrayName);
                foreach ($AFields as $Key)
                {
                    // Получаем из поста значение по очередному ключу
                    if (array_key_exists($Key, $Post)) $Value=$Post[$Key];
                    else $Value=null;
                    $Record[$Key]=(string)$Value;
                }
            }
            else foreach ($AFields as $Key) $Record[$Key]=null;
//            unset($d);
        }
        clEnd($Result);
        return $this;
    }



    public function &SelectCaption($AIDSite, $AIDLang, $AFieldID, $AFieldCaption)
    {
        foreach ($this->Array as &$Record)
        {
            if (array_key_exists($AFieldID, $Record))
            {
                $d=new TDescript();
                $r=$d->Read($Record[$AFieldID], $AIDSite);
                if ($r==rcOk)
                {
                    $Caption=$d->GetLangAny($AIDLang, 'Caption', '');
                    if ($Caption=="") $Caption=$d->ID;
                    $Record[$AFieldCaption]=$Caption;
                }
                else $Record[$AFieldCaption]=$r;
                unset($d);
            }
        }
        return $this;
    }



    /*
     * Построение caption типов записей
     * $IDLanguage - язык построения
     * $FieldID - имя поля с идентификатором
     * $FieldCaptionType - имя поля куда будут помещены caption
     */
    public function &SelectCaptionType($AIDSite, $AIDLang, $AFieldID, $AFieldCaptionType)
    {
        foreach ($this->Array as &$Record)
        {
            $d=new TDescript();
            $r=$d->Read($Record[$AFieldID], $AIDSite);
            if ($r==rcOk) $Record[$AFieldCaptionType] = clDescriptCaptionByID($d->Type, $AIDSite, $AIDLang);
            else $Record[$AFieldCaptionType]=$d->Type;
            unset($d);
        }
        return $this;
    }



    /*
     * Установка значения полей
     */
    public function &Set($AFields)
    {
        foreach ($this->Array as &$Record)
        {
            foreach ($AFields as $Field=>$Value) $Record[$Field]=$Value;
        }
        return $this;
    }





/******************************************************************************
 * вывод данных
 */

    /*
     * Строит контент по списку записей используя шаблон
     * $ARecord - шаблон записи
     */
    public function BuildContent($ARecord)
    {
        clBeg('');
        $Result = '';
        foreach ($this->Array as &$Record)
        {
            $Content = $ARecord;
            foreach ($Record as $Key=>$Value)
            {
                $Content = str_replace('%'.$Key.'%', $Value, $Content);
            }
            $Result = $Result . $Content;
        }
        clEnd('');
        return $Result;
    }



    /*
     * Выводит значение в лог
     */
    public function &Dump()
    {
        clBeg('');
        $i=0;
        foreach ($this->Array as &$Record)
        {
            clDeb('['.$i.']');
            clBeg('{');
            foreach ($this->Array[$i] as $Key=>$Value)
            {
                clDeb($Key.'='.$Value);
            }
            clEnd('}');
            $i++;
        }
        clEnd('');
        return $this;
    }




    /**
     * Получение перечня записей с позиции
     * $ARecordCurrent - номер записи с которой необходимо начать построение
     * $ARecordCount - количество возвращаемых записей
     */
    public function &Cut($ARecordCurrent, $ARecordCount)
    {
        $this->Array = array_slice($this->Array, $ARecordCurrent, $ARecordCount);
        return $this;
    }



    /*
     * Сохранение списка дескриптов в виде результата TResult
     * $AResult - указаель на объект Result
     * $Group - имя группы в которую сохраняем
     */
    public function &BuildResult(&$AResult, $AGroup)
    {
        clBeg('');
        foreach ($this->Array as &$Record) $AResult->AddRecord($AGroup, $Record);
        clEnd('');
        return $this;
    }



    // Экстрагирует значения в массив по полю
    public function ExtractToArray($AKeyField)
    {
        $Result=array();
        foreach ($this->Array as &$Record) array_push($Result, $Record[$KeyField]);
        return $Result;
    }



/******************************************************************************
 * операции
 */


    /*
     * Сортировка по полю
     * $AField - имя поля
     * $ADirect - направление сортировки
     */
    public function &Sort($AField, $ADirect)
    {
        function DescriptsSortCmp($Params)
        {
            return function ($a,$b) use ($Params)
            {
                if ($a[$Params['Field']] > $b[$Params['Field']]) return $Params['Direct'];
                else if ($a[$Params['Field']] < $b[$Params['Field']]) return -$Params['Direct'];
                else return 0;
            };
        }
        usort($this->Array, DescriptsSortCmp(['Field'=>$AField, 'Direct'=>$ADirect]));
        return $Result;
    }



    /*
     * Поиск первой записи в поле по значению
     * $AKeyField - поле
     * $AValue - значение
     */
    public function Find($AKeyField, $AValue)
    {
        $Result=null;
        $i=0;
        $c=count($this->Array);
        while ($i<$c && $Result === null)
        {
            if ( (string)$this->Array[$i][$AKeyField] == (string)$AValue) $Result = $i;
            $i++;
        }
        return $Result;
    }



    /*
     * Группирует записи по полю и сохраняет количество группированных записей
     */
    public function &Group($AKeyField, $ACountField)
    {
        $Result=new TDescripts;
        foreach ($this->Array as $Record)
        {
            $Search = $Result->Find($AKeyField, $Record[$AKeyField]);
            if ($Search === null)
            {
                $Record[$ACountField]=1;
                $Result->Insert($Record);
            }
            else
            {
                $Result->Array[$Search][$ACountField]++;
            }
        }
        $this->Array = $Result->Array;
        unset($Result);
        return $this;
    }



    /*
     * Переименование поля
     */
    public function &Rename($AFieldFrom, $AFieldTo)
    {
        foreach ($this->Array as &$Record)
        {
            $Record[$AFieldTo] = $Record[$AFieldFrom];
            unset( $Record[$AFieldFrom]);
        }
        return $this;
    }



    public function &Insert(&$ARecord)
    {
        array_push($this->Array, $ARecord);
        return $this;
    }



    public function RecordCount()
    {
        return Count($this->Array);
    }




 public function Search($AString, $AIDLanguage)
 {
  global $clSession;
  $IDSite = $clSession->GetSite();

  // Поиск в идексиованных файлах
  $Index = new TIndex;
  $Index->Prefix = $IDSite.'/'.$AIDLanguage;
  $Result = $Index->Find(trim(rawurldecode($AString)));
  unset($Index);

  $this->LoadFromArray($Result,'ID');
 }




 // пересечение с массивом по ключевому полю
 public function IntersectFromArray($AArray, $AKeyField)
 {
  $Result=array();
  foreach ($AArray as $Value)
  {
   $Record=$this($Find, $AKeyField, $AValue);
   if ($Record!=null) array_push($Result, $Record);
  }
  $this->Array=$Result;
  return true;
 }



 public function ChildCount($AIDBind)
 {
  foreach ($this->Array as &$Record)
  {
   $d=new TDescript($Record['ID']);
   $r=$d->ChildRead();
   if ($r==rcOk) $Record['ChildCount']=$d->ChildCount($AIDBind);
   else $Record['ChildCount']=0;
   unset($d);
  }
 }


 public function Content($AContent)
 {
  $Result='';
  foreach ($this->Array as $Record)
  {
   $Keys=array_keys($Record);
   $Line=$AContent;
   foreach ($Keys as $Key) $Line=str_replace('%'.$Key.'%', $Record[$Key], $Line);
   $Result.=$Line;
  }
  return $Result;
 }




    public function &Union($ADescripts, $AKeyField, $AEmptyRecord)
    {
        foreach ($this->Array as &$Record)
        {
            $Search=$ADescripts->Find($AKeyField, $Record[$AKeyField]);
            if ($Search==null) $Record = array_merge($Record, $AEmptyRecord);
            else $Record = array_merge($Record, $Search);
        }
        return $this;
    }


    /*
     * Фильтр по родителю
     */
    public function &FilterByParent($AIDSite, $AFielfdID, $AIDParent, $AIDBind)
    {
        $Result=null;
        $i=0;
        $c=count($this->Array);

        while ($i<$c && $Result==null)
        {
        $i++;
//            $Record =  $this->Array[$i];
//            $d=new TDescript();
//            $d->Assign($Record[$AFieldID], TYPE_DEFAULT, $AIDSite);
//            $d->ParetRead();
//            if ($d->ParentExist($AIDParent, $AIDBind)) $i++;
//            else array_splice($this->Array, i, 1);
//            unset($d);
        }
        return $this;
    }



    /**
     * Фильтр по родителю
     */
    public function &Filter($ACondition)
    {
        $Result=new TDescripts;
        foreach ($this->Array as $Record)
        {
            if (call_user_func($ACondition, $Record)) $Result->Insert($Record);
        }
        $this->Array = $Result->Array;
        return $this;
    }

} // Финал описания TDescripts


