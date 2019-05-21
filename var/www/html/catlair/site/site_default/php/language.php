<?php
/******************************************************************************
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
 ******************************************************************************
 *
 * Language library
 *
 ******************************************************************************
 */

/*
 * Return IDLanguage
 */

function clGetLang($AValue)
{
    switch ($AValue)
    {
        case null:
        case '':
        case 'language_site':
            global $clSession;
            $Result = $clSession->GetLanguage();
            break;
        case 'language_default':
            $Result = LANG_DEFAULT;
            break;
        default:
            $Result = $AValue;
            break;
    }
    return $Result;
}
