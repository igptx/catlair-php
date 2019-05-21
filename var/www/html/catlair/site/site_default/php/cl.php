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
 * Стартовый файл Catlair.
 * вебсервер должен быть настроен на открытие данного файла.
 */

/*Базовые библиотеки*/
include "utils.php";
include "debug.php";
include "catlair.php";
include "result.php";
include "descript.php";
include "descripts.php";
include "search.php";
/*Библиотеки объектов*/
include "session.php";
include "domain.php";
include "file.php";
include "account.php";

/*
 * Body
 */

$clSession = new TSession(); /* new session object */
$clSession->Open(false);

$clLoger->Info = $clSession->Get('LogInfo', false);
$clLoger->Debug = $clSession->Get('LogDebug', false);
$clLoger->Warning = $clSession->Get('LogWarning', false);
$clLoger->Error = $clSession->Get('LogError',false);
$clLoger->Job = $clSession->Get('LogJob', false);
$clLoger->Start($clSession->Get('LogEnabled', false)); /* start logger */

print(ContentBuild());

$clSession->Flush(); /* Save session data fo next use */

$clLoger->Stop();
