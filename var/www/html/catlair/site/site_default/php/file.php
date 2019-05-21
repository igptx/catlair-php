<?php

/**
 * Файлы
 * Catlair PHP
 *
 * Работа с дескриптами как с файлами.
 *
 * still@itserv.ru
 */

define("TYPE_FILE" ,'File'); // умолчальный идентификатор дескрипта


class TFile extends TDescript
{
    function __construct()
    {
       $this->Type = TYPE_FILE;
    }



    /*
     * Возвращает полное имя файла связей дескрипта c родителями
     */
    public function BinaryFile($AIDSite, $AIDLang)
    {
        return clDataPath($AIDSite, $this->ID) . '/file_' . $AIDLang;
    }



    /*
     * Возвращает полное имя файла для различных языков и сайтов
     */
    public function BinaryFileAny($AIDLang)
    {
        $File = $this->BinaryFile($this->IDSiteCurrent, $AIDLang);
        if (!file_exists($File))
        {
            $File = $this->BinaryFile($this->IDSiteCurrent, LANG_DEFAULT);
            if (!file_exists($File))
            {
                $File = $this->BinaryFile(SITE_DEFAULT, $AIDLang);
                if (!file_exists($File))
                {
                    $File = $this->BinaryFile(SITE_DEFAULT, LANG_DEFAULT);
                    if (!file_exists($File)) $File = false;
                }
            }
        }
        return $File;
    }



    /**
     * Удалявет файл
     */
    public function Delete()
    {
//        $Path = $this->BinaryPath(LANG_DEFAULT);
//        $File = $this->BinaryFile($Path, $Ext);
    }



    /**
     * Импортирует файл для дескрипта из переданного файла и укладывает его в структуру
     */
    public function Import($AFile, $AIDLang)
    {
        clBeg('');
        if (!file_exists($AFile)) $Result = 'FileNotExist';
        else
        {
            $Path = clDataPath($this->IDSiteCurrent, $this->ID);
            if (!file_exists($Path)) mkdir($Path, FILE_RIGHT, true);
            $File = $this->BinaryFile($this->IDSiteCurrent, $AIDLang);
            clDeb('Import file to ['.$File.']');
            if (rename($AFile, $File))
            {
                $Result = rcOk;
                $this->Set('MIME', mime_content_type($File));
                $this->Set('Extention', pathinfo($File, PATHINFO_EXTENSION));
            }
            else $Result = 'ErrorImportDescriptFile';
        }
        clEnd($Result);
        return $Result;
    }



    /**
     * Отправляет файл клиенту как файл
     */
    public function &Send()
    {
        global $clSession;
        $IDLang = $clSession->GetLanguage();
        $File = $this->BinaryFileAny($IDLang);
        if ($File) clSendFile($this->ID, $File);
        return $this;
    }



/******************************************************************************
 * Работа с файлом как с графикой
 */

   /*
    * Создание кэш имени
    */
    public function CacheFile($AIDLang, &$AParams)
    {
        clBeg('');
        /* Генерация пути */
        $File = clDataPath($this->IDSiteCurrent, $this->ID) . '/img_cache_' . $AIDLang;
        foreach ($AParams as $Key => $Value) $File .= '&' . (string)$Key . '=' . (string)$Value;
//        $File = $File . '.' . strtolower(pathinfo ($this->ID, PATHINFO_EXTENSION));
        clEnd($File);
        return $File;
    }



    public function CacheDelete($AIDLang)
    {
        clBeg('');
        $Mask = clDataPath($this->IDSiteCurrent, $this->ID) . '/img_cache_' . $AIDLang . '*';
        /* удаление файла */
        clBeg('Delete files by mask ['.$Mask.']');
        foreach (glob($Mask) as $File)
        {
            clDeb('File delete ['.$File.']');
            unlink($File);
        }
        clEnd('Delete end');
        /* возвращение результата s*/
        $Result=rcOk;
        clEnd($Result);
        return $Result;
    }



    public function SendImage()
    {
        clBeg('');
        global $clSession;
        $IDLang = $clSession->GetLanguage();
        $IDSite = $clSession->GetSite();
        /* если количество параметров 1 то просто отдаем файл */
        if (count($_GET)==1) $this->Send();
        else
        {
            $BinaryFile = $this->BinaryFileAny($IDLang);
            if ($BinaryFile)
            {
                // определение атрибутов файла
                $this->CheckPath();
                $CacheFile = $this->CacheFile($IDLang, $_GET);
                $Ext = strtolower(pathinfo ($this->ID, PATHINFO_EXTENSION));
                if (!file_exists($CacheFile)) clImageBuildCache($BinaryFile, $CacheFile, $Ext, $_GET);
                if (file_exists($CacheFile)) clSendFile($this->ID, $CacheFile);
             }
        } // проверка параметров
        clEnd('');
    } // function SendImage

} // class TFile



function &clImageRead($AFile, $AExt)
{
    switch (strtolower($AExt))
    {
       case 'jpg':
       case 'jpeg': $r = imagecreatefromjpeg($AFile); break;
       case 'gif': $r = imagecreatefromgif($AFile); break;
       case 'png': $r = imagecreatefrompng($AFile); break;
       case 'bmp': $r = imagecreatefromwbmp($AFile); break;
       default: $r = null;
    }
    return $r;
}



function clImageWrite(&$AImage, $AFile, $AExt)
{
    switch (strtolower($AExt))
    {
        case 'jpg':
        case 'jpeg': $r = imagejpeg($AImage, $AFile, JPEG_QUALITY); break;
        case 'gif': $r = imagegif($AImage, $AFile); break;
        case 'png': $r = imagepng($AImage, $AFile); break;
        case 'bmp': $r = imagewbmp($AImage, $AFile); break;
        default: $r = null;
    }
    return $r;
}



/**
 * Построение файлового кэша для файла
 * $ASource - путь источник файла
 * $ADestination - путь направления файла
 * $AParams - перечень параметров URL обычно $_GET
 */
function clImageBuildCache($ASource, $ADestination, $AExt, &$AParams)
{
    clBeg('');
    list($wo, $ho) = getimagesize($ASource);
    $Source = clImageRead($ASource, $AExt);
    if ($Source)
    {
        //Выясняем размеры к которым надо привести изображение
        $Aspect = $wo/$ho;
        if (array_key_exists ('scalex', $AParams)) $wn = $AParams['scalex']; else $wn=$wo;
        if (array_key_exists ('scaley', $AParams)) $hn = $AParams['scaley']; else $hn=$wn / $Aspect;
        $Result = imagecreatetruecolor($wn, $hn);
        if ($AExt=='png')
        {
            imagealphablending($Result, false);
            imagesavealpha ($Result, true);
        }
         /* Непосредственно копирование скалирование */
        imagecopyresampled($Result, $Source, 0, 0, 0, 0, $wn, $hn, $wo, $ho);
        if (array_key_exists ('colorize', $AParams))
        {
            $val = $AParams['colorize'];
            $a=hexdec(substr($val, 0, 2)) * 0.5;
            $r=hexdec(substr($val, 2, 2));
            $g=hexdec(substr($val, 4, 2));
            $b=hexdec(substr($val, 6, 2));
            imagefilter($Result, IMG_FILTER_COLORIZE, $r,$g,$b,$a);
        }
        /* write cache to file */
        $Path = pathinfo ($ADestination, PATHINFO_DIRNAME);
        if (!file_exists($Path)) mkdir($Path, FILE_RIGHT, true);
        $OldMask = umask(0077);
        clImageWrite($Result, $ADestination, $AExt);
        umask($OldMask);
    }
    clEnd('');
}





/**
 * Низкоуровнеоая процедура отправки файлов.
 * Отправляет файл на клиента без проверки прав.
 */
function clSendFile($ACaption, $AFileName)
{
    clBeg('');
    if (file_exists($AFileName))
    {
        // определяем mime файла
        $Mime = mime_content_type($AFileName);
        if ($Mime=="") $Mime="application/octet-stream";
        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти
        // если этого не сделать файл будет читаться в память полностью
        if (ob_get_level())  ob_end_clean();
        // заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $Mime);
        header('Content-Disposition: attachment; filename="' . basename($ACaption).'"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($AFileName));
        // читаем файл и отправляем его пользователю
        if ($fd = fopen($AFileName, 'rb'))
        {
           while (!feof($fd)) print fread($fd, 1024);
           fclose($fd);
        }
    }
    else
    {
       print "FileNotFound ".$AFileName;
    }
    clEnd('');
}



function clDescriptFilePath($AIDSite)
{
    $Result = clSitePath($AIDSite) . '/file';
    return $Result;
}

