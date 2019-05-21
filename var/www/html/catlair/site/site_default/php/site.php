<?php

/*
###############################################################################
# Сайты. Дескрипт.
# Библиотека работы с сайтам
###############################################################################
*/


class TSite
{
 public $Type = 'Site';
 public $Descript = null;

 public function Prepare($ALang)
 {
  $Result = LoadContent('DescriptForm'.$this->Type.'.html');
  $Form = $Result['Content'];
//  $Form = str_replace('%Login%', $this->Descript->Get('Login', ''), $Form);
  return $Form;
 }


 public function Update($ALang, &$AResult)
 {
//  if (array_key_exists('Login', $_POST)) $this->Descript->Set('Login', $_POST['Login']);
  return true;
 }

}

