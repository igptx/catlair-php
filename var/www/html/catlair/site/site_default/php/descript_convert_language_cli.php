<?php
/******************************************************************************
 * Catlair PHP
 * CLI interface
 *
 ******************************************************************************
 * Convert language form source to destination for site.
 * - Маска идентификатора дескрипта (по умолчаию *)
 * - Идентификатор сайта (по умолчанию SITE_DEFAULT)
 * - Идентификатор языка source (по умолчанию LANG_DEFAULT)
 * - Идентификатор языка destination (по умолчанию LANG_DEFAULT)
 *
 * still@itserv.ru
 */

include "utils.php";
include "debug.php";
include "result.php";
include "descript.php";
include "catlair.php";
include "search.php";
include "file.php";

$clLoger->Start(true);

clBeg('Indexate descript.');

// Get descript IT
if (count($argv)>1) $IDDescriptMask = $argv[1];
else
{
        clInf('ID descript mask: [*]');
        $IDDescriptMask = readline();
        if ($IDDescriptMask=='') $IDDescriptMask = '*';
}

if (count($argv)>2) $IDSite = $argv[2];
else
{
        clInf('ID site ['.SITE_DEFAULT.']: ');
        $IDSite = readline();
        if ($IDSite=='') $IDSite = SITE_DEFAULT;
}


if (count($argv)>3) $IDLangFrom = $argv[3];
else
{
        clInf('ID lang from ['.LANG_DEFAULT.']: ');
        $IDLangFrom = readline();
        if ($IDLangFrom=='') $IDLangFrom = LANG_DEFAULT;
}


if (count($argv)>4) $IDLangTo = $argv[4];
else
{
        clInf('ID lang to: ');
        $IDLangTo = readline();
}


/* Check param login */
if ($IDDescriptMask == '') clWar('ID descript mask empty');
else
{
    if ($IDLangTo == '') clWar('ID language to is empty');
    else
    {
        clBeg('Convert begin');

        $FileMask = clDescriptsPath($IDSite).'/'.$IDDescriptMask;
        clDeb('FileMask ['.$FileMask.']');
        foreach (glob($FileMask) as $File)
        {
            /* Convert content*/
            $Source = $File.'/content_'.$IDLangFrom;
            $Dest = $File.'/content_'.$IDLangTo;
            if (file_exists($Source))
            {
                if (!rename($Source, $Dest)) clWar('Error convert from ['.$Source.'] to ['.$Dest.']');
            }

            /* Convert files*/
            $Source = $File.'/file_'.$IDLangFrom;
            $Dest = $File.'/file_'.$IDLangTo;
            if (file_exists($Source))
            {
                if (!rename($Source, $Dest)) clWar('Error convert from ['.$Source.'] to ['.$Dest.']');
            }

            /* Convert unique*/
            $Source = $File.'/unique_'.$IDLangFrom.'.txt';
            $Dest = $File.'/unique_'.$IDLangTo.'.txt';
            if (file_exists($Source))
            {
                if (!rename($Source, $Dest)) clWar('Error convert from ['.$Source.'] to ['.$Dest.']');
            }

            /* Delete img_cache */
            $mask = $File.'/img_cache*';
            array_map('unlink', glob($mask));

            $DescriptID = basename($File);
            $Descript = new TDescript();
            $Descript->Read($DescriptID, $IDSite);
            $Caption = $Descript->GetLang($IDLangFrom, 'Caption', '');
            if ($Caption!='')
            {
                $Descript->SetLang($IDLangTo, 'Caption',$Caption);
                $Descript->SetLang($IDLangFrom, 'Caption','');
            }
            $Descript->Flush();
            unset($Descript);
        }
        clEnd('');
    }
}
clEnd('');

$clLoger->Stop();




