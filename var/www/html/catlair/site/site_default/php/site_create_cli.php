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
 * Создание сайта
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

$clLoger->Start(true); // start logger

clBeg('Site create');

if (count($argv)>1) $IDSite = $argv[1];
else
{
        clInf('ID site: ');
        $IDSite = readline();
}

// Check param login
if ($IDSite == '') $Result = 'ID site not found';
else
{
   $Site = new TDescript();
   $Result = $Site->Create($IDSite, TYPE_SITE, $IDSite);
   if ($Result==rcOk)
   {
       // Запись параметров сайта
       $Site->SetLang(LANG_DEFAULT, 'Caption', $IDSite);
       // Установка связей в родительскюу папку home
       $Parent = new TDescript();
       $Result = $Parent->Read(FOLDER_HOME, $IDSite);
       $Result = $Site->BindBegin($Parent);
       $Result = $Site->Bind($Parent, BIND_DEFAULT, false);
       $Result = $Site->Bind($Parent, BIND_RIGHT, false);
       $Result = $Site->BindEnd($Parent);
       unset($Parent);
       if ($Result == rcOk) $Result = $Site->Flush();
   }
   unset($Site);
}

clEnd($Result);

$clLoger->Stop();
