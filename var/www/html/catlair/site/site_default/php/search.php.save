<?php

/**
 * Система индексации
 *
 * Индексация начинается с Begin()
 * Далее при помощи Pars() добавляется различные наборы текста
 * Завершается командой flush()
 *
 * Суть индексации
 * Каждому индексируемому объекту создается файл с перечнем уникальных слов (поисковых ключей).
 * Каждый поисковый ключ создает файл индекс в котором содержится имена объктов
 * индексации.
 * При поиске по ключу берется индексный файл и из него возвращается перечень объектов
 * индексации. Если поисковых ключей нескольго в запросе то ищутся пересечения объектов
 * индексации. В резульате возвращается список ключей в которых присутствую все поисковые слова.
 *
 * still@itserv.ru
 */

define("ptLike", 'like'); // Разбиение ключа для поиска по лайку
define("ptStrict", 'strict'); // Не производится разбиенье ключа
define("ptFind", 'find'); // Разбиение строки для произведения поиска

define("clDiv", chr(10)); // символ разбиения словарей и хранилищ индексных файлов

class TIndex
{
    private $Link; // идентификатор объекта индексации возвращаемая припоиске
    private $WordNew=array(); // Добавляемые
    private $WordCurrent=array(); // текущий массив ключей загружаемый



    /*
     *  Запуск индексации для указанного ALink
     */
    public function &Begin($ALink)
    {
        $this->Link = $ALink;
        clInf('Indexating begin for objrct ['.$ALink.']');
        return $this;
    }



    /*
     * Парсинг текста
     * добавляет в индексацию новый перечень слов из текста
     * $AText - текст
     * $AType - тип разбиеня на слова
     */
    public function Pars($AText, $AType)
    {
//        clBeg('');
        // Новый перечень слов
        $Income = $this->TextToWord($AText, $AType);
        $this->WordNew=array_merge($this->WordNew, $Income);
        $this->WordNew=array_unique($this->WordNew);
//        clDump('Words', $this->WordNew);
//        clDeb('Count income [' . count($Income) . ']');
//        clDeb('Count unique [' . count($this->WordNew) . ']');
//        clEnd('');
    }



    /*
     * Завершение парсинга и сохранение результатов.
     */
    public function Flush($ALang, $ASite, $AStoreRebuild)
    {
        clBeg('');
        $Buffer='';

        // Определение файла уникальных слов
        $FileName = $this->StoreFile($ALang, $ASite);
        clLog('File unique words: '.$FileName,  ltDeb);

        // Загрузка предыдущего массива уникальных слов в случае если
        // не полный ребилд и файл существует
        if (!$AStoreRebuild && file_exists($FileName))
        {
            $FileHandle = fopen($FileName, 'r');
            $FileSize = filesize($FileName);
            if ($FileHandle && $FileSize>0)
            {
                clLog('File unique words was read',  ltDeb);
                $Buffer=fread($FileHandle, $FileSize);
                fclose($FileHandle);
            }
        }

        // Разбиение буффера на массив слов
        $this->WordCurrent = explode(clDiv, $Buffer);
        clDeb('Words current array: ' . count($this->WordCurrent));
        clDeb('Words new array: ' . count($this->WordNew));

        // Поиск слов различий для добавления удаления
        $WordAdd=array_diff($this->WordNew, $this->WordCurrent);
        $WordDel=array_diff($this->WordCurrent, $this->WordNew);
        clDeb('Words for add: ' . count($WordAdd));
        clDeb('Words for del: ' . count($WordDel));

        // Создание всех ключей на йдаление добавление ключей
        $KeyInsAll = $this->WordToKey($WordAdd);
        $KeyDelAll = $this->WordToKey($WordDel);

        // Ищем пересечение которые необходимо добавить-удалить исходя из всех ключей
        $KeyIns = array_diff($KeyInsAll, $KeyDelAll);
        $KeyDel = array_diff($KeyDelAll, $KeyInsAll);
        clDeb('Keys for add:' . count($KeyIns));
        clDeb('Keys for del:' . count($KeyDel));

        $Result = rcOk;

        // Внесение изменений в индексные файлы
        foreach($KeyIns as $Key) $Result = $this->KeyInsert($Key, $ALang, $ASite);
        foreach($KeyDel as $Key) $Result = $this->KeyDelete($Key, $ALang, $ASite);

        // Сохранение буфера в файл уникальный слов
        $Buffer = implode(clDiv, $this->WordNew);
        $FileHandle = fopen($FileName, 'w+');
        if ($FileHandle)
        {
            fwrite($FileHandle, $Buffer);
            fclose($FileHandle);
        } else $Result = 'ErrorFlushIndexWord';

        clEnd($Result);
        return $Result;
    }


    /*
     * Поиск по поисковым ключам в индексе и возвращенеи списка Links
     * $AText - перечень поисковых слов
     * $ALang - язык на котором производится поиск
     * $ASite - сайт для которого произвоидится поиск
     */
    public function Find($AText, $ALang, $ASite)
    {
        function NotEmpty($AParam)
        {
            return $AParam!='';
        }

        clBeg('');
        $Result = array();
        $WordList = $this->TextToWord($AText, ptFind);
        foreach ($WordList as $Key)
        {
            $Links=$this->LinkRead($Key, $ALang, $ASite);
            if (count($Result)==0) $Result=array_merge($Result, $Links);
            else $Result=array_intersect($Result, $Links);
        }
        $Result = array_unique($Result);
        $Result = array_filter($Result, 'NotEmpty');

        clEnd('Find ['.count($Result).'] rec');
        return $Result;
    }


    /**************************************************************************
     * Работа со индексными файлами ссылок и ссылками
     */
    private function IndexPathRoot($ASite, $ALang)
    {
        return clRootPath() . '/site/' .$ASite . '/index/' . $ALang;
    }


    /*
     * Function return file path to the index file witn links
     * $AKey - поисковый ключ
     * $ALang - язык на котором выполняется запрос
     * $ASite - сайт для которого выполняется запрос
     * $ACreate - создавать ли индесный путь вы случае отсутсвия
     */
    private function IndexPath($AKey,  $ASite, $ALang)
    {
        // Сбрка пути
        $p = $this->IndexPathRoot($ASite, $ALang);
        // Добавка пути путей исходя из длины строки
        $l=mb_strlen($AKey, 'UTF-8');
        if ($l>1) $p .= '/' . mb_substr($AKey, 0, 1, 'UTF-8');
        if ($l>2) $p .= '/' . mb_substr($AKey, 0, 2, 'UTF-8');
        if ($l>3) $p .= '/' . mb_substr($AKey, 0, 3, 'UTF-8');
        return $p;
    }



    /*
     * Получение имений индексного файла по пути и ключу
     */
    private function IndexFile($APath, $AKey)
    {
        return $APath . '/' . $AKey . '.idx';
    }



    /*
     * Сохранение файла ссылок из массива
     */
    public function LinkWrite($AKey, $ASite, $ALang, &$ALinks)
    {
        // получение файлового пути
        $AKey = trim($AKey);
        $FilePath = $this->IndexPath($AKey, $ASite, $ALang);
        // если путь отсусвует создаем его
        if (!file_exists($FilePath))
        {
            try
            {
                mkdir($FilePath, FILE_RIGHT, true);
            }
            catch (Exception $e)
            {
                clErr($FilePath);
            }
        }
        // Get file name
        $FileName=$this->IndexFile($FilePath, $AKey);

        // Сохранение файла
        if (count($ALinks)<=1)
        {
            // Удаленеи файла в случае если ссылка одна или менее
            unlink($FileName);
            $Result=rcOk;
        }
        else
        {
            // Сохранение индексного файла ссылок
            $FileHandle = fopen($FileName, 'w');
            if ($FileHandle===false) $Result='ErrorWriteLinkFile';
            else
            {
                $Text=implode(clDiv,$ALinks);
                fwrite($FileHandle, $Text);
                fclose($FileHandle);
                $Result=rcOk;
            }
        }
        return $Result;
    }


    /*
     * Чтение файла ссылок и возвращение их в виде массива
     */
    public function &LinkRead($AKey, $ALang, $ASite)
    {
        /*Получение файловго имени по запрошенному сайту*/
        $FilePath = $this->IndexPath($AKey,  $ASite, $ALang);
        $FileName = $this->IndexFile($FilePath, $AKey);

        /*В случае если сайт не дефаултный и индескного файла нет, пытаемся запросить в дефайлтном*/
        if (!file_exists($FileName) && $ASite!=SITE_DEFAULT)
        {
            $FilePath = $this->IndexPath($AKey,  SITE_DEFAULT, $ALang);
            $FileName = $this->IndexFile($FilePath, $AKey);
        }

        $Buffer='';
        /* Если файл есть - читаем его */
        if (file_exists($FileName))
        {
            $FileHandle = fopen($FileName, 'r');
            if ($FileHandle)
            {
                $Buffer=fread($FileHandle, filesize($FileName));
                fclose($FileHandle);
            }
            else clWar('Error opened index file ['.$FileName.']');
        }
        $Result = explode(clDiv, $Buffer);
        return $Result;
    }


    /*
     * Проверка наличия ссылки в индексном файле ключа
     */
    public function LinkExists($ALinks)
    {
        return array_search($this->Link, $ALinks);
    }



    /*
     * Сохранение ссыдки в индексный файл по ключу
     */
    public function KeyInsert($AKey, $ALang, $ASite)
    {
        $Result = rcOk;
        if ($AKey!='')
        {
            $Links=$this->LinkRead($AKey, $ALang, $ASite);
            if ($this->LinkExists($Links)===false)
            {
                array_push($Links, $this->Link);
                $Result = $this->LinkWrite($AKey, $ASite, $ALang, $Links);
            }
        }
        return $Result;
    }



    /*
     * Удаление ссылки из индексного файла по ключу
     */
    public function KeyDelete($AKey, $ALang, $ASite)
    {
        $Result = rcOk;
        if ($AKey!='')
        {
            $Links = $this->LinkRead($AKey, $ALang, $ASite);
            $iLink = $this->LinkExists($Links);
            if ($iLink!==false)
            {
                unset($Links[$iLink]);
                $Result = $this->LinkWrite($AKey,  $ASite, $ALang, $Links);
            }
        }
        return $Result;
    }



/*
 * Преобразование текста в массив слов
 * Принцип разбиения зависит от $AType ptLike, ptStrict, ptFind
 * Удаляютс все знаки препинания и иные символы за исключением алфавита цифр и _
 */

 private function &TextToWord($AText, $AType)
 {
  // В случае если обрааботка производится для поисковой строки

  switch ($AType)
  {
   case ptFind:
    $AText = preg_replace('/:/','',$AText);
    // Очистка текста от знаков припинания и прочего
    $AText = preg_replace('/[^a-zA-Zа-яА-Я0-9_]/ui', ' ', mb_strtolower($AText/*, 'UTF-8'*/));
    $AText = preg_replace('/  +/',' ',$AText);
   break;
   case ptLike:
    // Очистка текста от знаков припинания за исключением _
    $AText = preg_replace('/[^a-zA-Zа-яА-Я0-9_]/ui', ' ', mb_strtolower($AText/*, 'UTF-8'*/));
    $AText = preg_replace('/  +/',' ',$AText);
   break;
   case ptStrict:
    // Очистка текста от знаков припинания за ислючением .,_ замена их на ''
    $AText = preg_replace('/[^a-zA-Zа-яА-Я0-9.,_]/ui', '', mb_strtolower($AText, 'UTF-8'));
    $AText = '#'.$AText;
   break;
  }

  $Result = explode(' ', $AText);

  // Создание массива уникальных слов
  $Result = array_unique($Result);

  // Возвращаем массив результат
  return $Result;
 }



//------------------------------------------------------------------------------
// Преобразование массива слов в массив ключей
//------------------------------------------------------------------------------

 public function WordToKey(&$AWords)
 {
  $Keys=array();
  foreach($AWords as $Word)
  {
   $SharpPos= mb_strpos($Word, '#');
   if ($SharpPos===false)
   {
    $l=mb_strlen($Word, 'UTF-8');
    $i=0;
    while ($i<=$l)
    {
     $Key = mb_substr($Word, 0, $i, 'UTF-8');
     array_push($Keys, $Key);
     $i++;
    }
   }
   else
   {
    array_push($Keys, mb_substr($Word, $SharpPos+1, null, 'UTF-8'));
   }
  }
  $Keys = array_unique($Keys);
  return $Keys;
 }



    /**
     * Возвращает имя файл в котором храняться все уникальные выражения слова
     */
    private function StoreFile($AIDLang, $AIDSite)
    {
        // Сбрка пути
        $FilePath = clDataPath($AIDSite, $this->Link);
        if (!file_exists($FilePath)) mkdir($FilePath, FILE_RIGHT, true);
        // Возвращаем финальный путь
        return $FilePath.'/unique_'.$AIDLang.'.txt';
    }



    /**
     *
     */
    public function Clear($AIDSite, $AIDLang)
    {
        clBeg('');
        $IndexPath = $this->IndexPathRoot($AIDSite, $AIDLang);
        clDeb('Index path ['.$IndexPath.']');
        clDeleteFolder($IndexPath);
        clEnd('');
    }
}
