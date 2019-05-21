<?php

/**
 * Catlair PHP
 *
 * домены
 *
 * 18.03.2019
 * still@itserv.ru
 */



class TDomain extends TDescript
{
    /**
     * Конструктор
     */
    public function __construct()
    {
    }



    /**
     * Создание дескрипта домена по имени домена
     */
    public function &ReadByName($ADomainName)
    {
        clBeg('');
        $FileName = clDomainListPath() . '/' . $ADomainName;
        $Site = SITE_UNKNOWN;
        if (file_exists($FileName))
        {
            $Size = filesize($FileName);
            if ($Size>0)
            {
                $File = fopen($FileName, 'r');
                if ($File!==false)
                {
                    $Site  = trim(fread($File, filesize($FileName)));
                    fclose($File);
                }
            }
            clDeb('Site ['.$Site.']');
            /* чтение информации о домене*/
            $this->Read($ADomainName, $Site);
        }
        clEnd('');
        return $Site;
    }



    /**
     * Создает ссылку в реестре доменов на текущий домен
     */
    public function OnAfterFlush()
    {
        clBeg('');
        $FilePath = clDomainListPath();
        if (!file_exists($FilePath)) mkdir($FilePath, FILE_RIGHT, true);
        $FileName = $FilePath.'/'.$this->ID;
        $File = fopen($FileName, 'w');
        if ($File === false) $Result='ErrorWriteDomainReestr';
        else
        {
            $IDSite = $this->GetArrayValue('Post', 'IDSiteTarget', SITE_DEFAULT);
            fwrite($File, $IDSite);
            fclose($File);
            $Result=rcOk;
        }
        clEnd('');
        return $Result;
    }


}



function clDomainListPath()
{
    return clRootPath() . '/domain';
}
