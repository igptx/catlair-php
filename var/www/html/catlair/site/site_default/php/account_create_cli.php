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
 * Создание учетной записи для сайта
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

include "account.php";

$clLoger->Start(true); // start logger

clBeg('Account create');

if (count($argv)>1) $IDSite = $argv[1];
else
{
        clInf('ID site: ');
        $IDSite = readline();
}

if (count($argv)>2) $Login = $argv[2];
else
{
        clInf('Login: ');
        $Login = readline();
}

// Check param login
$Site = new TDescript();
$Result = $Site->Read($IDSite, $IDSite);
if ($Result!=rcOk) $Result = 'ID site not found';
else
{
    if ($Login == '') $Result = 'Login';
    else
    {
            $Account = new TAccount();
            $Account->Create($Login, TYPE_ACCOUNT, $IDSite);
            // Запись параметров domen
            $Account->SetLang(LANG_DEFAULT, 'Caption', $Login);
            // Установка связей c сайтом
            $Result = $Account->BindBegin($Site);
            $Result = $Account->Bind($Site, BIND_DEFAULT, false);
            $Result = $Account->Bind($Site, BIND_RIGHT, false);
            $Result = $Account->BindEnd($Site);
            unset($Parent);
            if ($Result == rcOk) $Result = $Account->Flush();
            unset($Account);
       }
}
unset($Site);

clEnd($Result);

$clLoger->Stop();
