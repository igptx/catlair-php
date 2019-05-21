<?php
/******************************************************************************
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
 *****************************************************************************
 * CLI интерфейс
 * Создание домена для сайта
 *
 * Параметры
 * - Маска идентификатора дескрипта (по умолчаию *)
 */

include "utils.php";
include "debug.php";
include "result.php";
include "descript.php";
include "catlair.php";
include "search.php";
include "file.php";

include "domain.php";

$clLoger->Start(true); // start logger

clBeg('Site create');

if (count($argv)>1) $IDSite = $argv[1];
else
{
        clInf('ID site: ');
        $IDSite = readline();
}

if (count($argv)>2) $IDDomain = $argv[2];
else
{
        clInf('ID domain: ');
        $IDDomain = readline();
}

// Check param login
$Site = new TDescript();
$Result = $Site->Read($IDSite, $IDSite);
if ($Result!=rcOk) $Result = 'ID site not found';
else
{
    if ($IDDomain == '') $Result = 'ID domain not found';
    else
    {
            $Domain = new TDomain();
            $Result = $Domain->Create($IDDomain, TYPE_DOMAIN, $IDSite);
            if ($Result==rcOk)
            {
                // Запись параметров domen
                $Domain->SetLang(LANG_DEFAULT, 'Caption', $IDDomain);
                /* Сохранение параметров домена */
                $Post=['IDSiteTarget'=>$IDSite];
                $Result = $Domain->ArrayStore('Post', $Post);
                // Установка связей c сайтом
                $Result = $Domain->BindBegin($Site);
                $Result = $Domain->Bind($Site, BIND_DEFAULT, false);
                $Result = $Domain->Bind($Site, BIND_RIGHT, false);
                $Result = $Domain->BindEnd($Site);
            }
            if ($Result == rcOk) $Result = $Domain->Flush();
            unset($Domain);
       }
}
unset($Site);

clEnd($Result);

$clLoger->Stop();
