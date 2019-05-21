<?php

/*
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
 */

include("gpio.php");



define("stUp", 'up');
define("stDown", 'down');
define("stUnknown", 'unknown');



function GetInetState()
{
 $Command = 'ping 8.8.8.8 -c 1 -W 1 | grep " 0% packet loss"';
 $Result = shell_exec($Command);
 if (strpos($Result, ' 0% packet loss')!==false) $r=stUp;
  else $r=stDown;
 return $r;
}



function GetPingState($AHost)
{
 $Command = 'ping '.$AHost.' -c 1 -W 1 | grep " 0% packet loss"';
 $Result = shell_exec($Command);
 if (strpos($Result, ' 0% packet loss')!==false) $r=stUp;
  else $r=stDown;
 return $r;
}



function PowerOff()
{
 $Result = 'Ok';
 $Signal = new TGPIO('20');
 if (!$Signal -> Enabled()) $Result = 'ErrorSignalNotEnabled';

 if ($Result == 'Ok')
  if (!$Signal->SetDirection('out'))
   $Result='ErrorDirectionSetOut';

 if ($Result == 'Ok')
 {
  $Signal->SetValue(true);
  sleep(1); // Ждем и нажимаем кнопку еще раз. Какой то глюк с GPIO
  $Signal->SetValue(true);
  sleep(5);
  $Signal->SetValue(false);
  sleep(2); // Ожидаем что бы гарантировано машина пришла в состояние вкл-выкл
 }

 return $Result;
}



function PowerOn()
{
 $Result = 'Ok';
 $Signal = new TGPIO('20');
 if (!$Signal -> Enabled()) $Result = 'ErrorSignalNotEnabled';

 if ($Result == 'Ok')
  if (!$Signal->SetDirection('out'))
   $Result='ErrorDirectionSetOut';

 if ($Result == 'Ok')
 {
  $Signal->SetValue(true);
  sleep(1);
  $Signal->SetValue(true);
  sleep(1);
  $Signal->SetValue(false);
  sleep(2);
 }

 return $Result;
}



function GetPowerStatus()
{
 $Result = 'Ok';
 $Signal = new TGPIO('13');
 if (!$Signal -> Enabled()) $Result = 'ErrorSignalNotEnabled';

 if ($Result == 'Ok')
 {
  if ($Signal->GetValue()) $Result=stUp;
  else $Result=stDown;
 }

 return $Result;
}



function ResetHost()
{
 $Result = 'Ok';
 $Signal = new TGPIO('21');
 if (!$Signal -> Enabled()) $Result = 'ErrorSignalNotEnabled';

 if ($Result == 'Ok')
  if (!$Signal->SetDirection('out'))
   $Result='ErrorDirectionSetOut';

 if ($Result == 'Ok')
 {
  $Signal->SetValue(true);
  sleep(1);
  $Signal->SetValue(true);
  sleep(1);
  $Signal->SetValue(false);
 }

 return $Result;
}
