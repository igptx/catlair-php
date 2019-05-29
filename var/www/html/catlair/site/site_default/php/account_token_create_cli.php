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
 * CLI интерфейс
 * Change password for accounts
 * php /var/www/html/catlair/site/site_default/php/account_token_create_cli.php lab chiv
 */

include "debug.php";
include "utils.php";
include "result.php";
include "descript.php";
include "catlair.php";
include "search.php";

include "account.php";

$clLoger->Start(true); // start logger

clBeg('');
clInf('Change token for account.');

/* Get site */
if (count($argv)>1) $Site = $argv[1];
else
{
        clInf('Site ['.SITE_DEFAULT.']: ');
        $Site = readline();
        if ($Site=='') $Site = SITE_DEFAULT;
}

/* Get IDAccount*/
if (count($argv)>2) $IDAccount = $argv[2];
else
{
        clInf('Account: ');
        $IDAccount = readline();
}



/* Check param login */
if ($IDAccount == '') clWar('Account empty');
else
{
    $d = new TAccount();
    if ($d->Read($IDAccount, $Site)==rcOk)
    {
        $d->TokenCreate();
        $d->TokenFlush();
        clInf('TokenCreate: ' . $d->TokenCreate());
        clInf('TokenSave: ' . $d->TokenFlush());
    }
    else clWar('Error');
    unset($d);
}
clEnd('');
$clLoger->Stop();
