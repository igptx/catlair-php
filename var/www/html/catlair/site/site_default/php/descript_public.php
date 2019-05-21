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
 * Пользовательские публичные функции работы с дескриптами
 */


/**
 * 
 */
function DescriptCaptionByIDPublic($AParams, $AResult)
{
    clBeg('');
    global $clSession;
    $ID = clGetIncome('ID', $AParams, null);
    $Lang = clGetIncome('IDLang', $AParams, $clSession->GetLanguage());
    $Site = clGetIncome('IDSite', $AParams,  $clSession->GetSite());
    $AResult->Set('ID', $ID);

    // Чтение дескрипта и отправка его параметром
    $d = new TDescript();
    $Result = $d->Read($ID, $Site);
    if ($Result==rcOk) $Caption = $d->GetLang($Lang, 'Caption', $ID);
    else $Caption='';

    $AResult->Set('Caption', $Caption);
    $AResult->SetCode(rcOk);
    unset($d);
    clEnd($Result);
    return true;
}



//==============================================================================
// Публичные функции для работы со связями объекта
//==============================================================================

//------------------------------------------------------------------------------
// Возвращает перечени детей для дескрипта ID
//------------------------------------------------------------------------------
// Возвращаемый список запакованых в div. Надо переделать на какой нить объект
// с возможностью указания строки через шаблон.
//------------------------------------------------------------------------------

function DescriptChildListPublic($AXML, $AContent)
{
 global $clSession;
 $IDLanguage = $clSession->GetLanguage();

 $IDBind=clGetIncome('IDBind', $AXML, null);
 $IDSite=clGetIncome('IDSite', $AXML, null);
 $ID=clGetIncome('ID', $AXML, null);

 if ($ID!=null)
 {
  $d = new TDescript();
  if ($d->Read($ID, $IDSite)==rcOk)
  {
   // Формирование списка идентификаторов по списку детей
   $d->ChildRead();

   $l=new TDescripts;
   foreach ($d->Child as $Bind)
   {
    if ($IDBind=='' || $Bind->attributes()->IDBind==$IDBind)
    {
     $RecordID=(string)$Bind->attributes()->IDChild;
     $RecordIDBind=(string)$Bind->attributes()->IDBind;
     $Record = array('ID'=>$RecordID, 'IDBind'=>$RecordIDBind);
     $l->Insert($Record);
    }
   }

   // Формирование буфера; результата
   $l->Group('ID', 'RecordCount');
   $l->ChildCount($IDBind);
   $l->Caption($IDLanguage, 'ID', 'Caption');
   $l->Caption($IDLanguage, 'IDBind', 'CaptionBind');
   $AContent=$l->Content('<div class="Record" ID="%ID%" Caption="%Caption%" CaptionBind="%CaptionBind%" CountChild="%ChildCount%" CountRecord="%CountRecord%"></div>');
   unset($l);
  }
  unset($d);
 }
 return $AContent;
}


//------------------------------------------------------------------------------
// Список ссылок
//------------------------------------------------------------------------------

function DescriptBindListPublic($AXML, $AContent)
{
 clLog('Descript binds begin', ltBeg);

 global $clSession;
 $IDLanguage = $clSession->GetLanguage();

 $ID=clGetIncome('ID', $AXML, null);
 if ($ID!=null)
 {
  $d=new TDescript();
  $d->ChildRead($ID);

  $t=array();
  foreach ($d->Child as $Bind) array_push($t, (string)$Bind->attributes()->IDBind);

  $l=new TDescripts;
  $l->LoadFromArray($t, 'ID');
  $l->Group('ID', 'Count');
  $l->Caption($IDLanguage,'ID','Caption');
  $AContent=$l->Content($AContent);
  unset($l);

  unset($d);
 }
 clLog('Descript binds end', ltEnd);
 return $AContent;
}


//------------------------------------------------------------------------------
// Список ссылок для формы управления
//------------------------------------------------------------------------------

function DescriptBindParentListPublic($AXML, $AContent)
{
 clLog('Descript binds begin', ltBeg);

 global $clSession;
 $IDLanguage = $clSession->GetLanguage();
 $ID=clGetIncome('ID', $AXML, null);
 $IDParent=clGetIncome('IDParent', $AXML, null);

 if ($ID!=null)
 {
  // Загрузка активных записей для дескрипта с $ID
  $d=new TDescript($ID);
  $d->ParentRead();

  $BindsOn=new TDescripts();
  foreach ($d->Parent as $Bind)
  {
   $RecordIDBind=(string)$Bind['IDBind'];
   $RecordIDParent=(string)$Bind['IDParent'];
   if ($RecordIDParent==$IDParent)
   {
    $Record=array('ID'=>$RecordIDBind, 'Enabled'=>'checked="checked"', 'Check'=>'true');
    $BindsOn->Insert($Record);
   }
  }

  // Получение всех записей связей
  $BindsAll=new TDescripts();
  $BindsAll->Search('Type:Bind', $IDLanguage);
  $BindsAll->Union($BindsOn, 'ID', array('Enabled'=>'', 'Check'=>'false'));
  $BindsAll->Caption($IDLanguage, 'ID', 'Caption');

  $AContent=$BindsAll->Content($AContent);

  unset($BindsOn);
  unset($BindsAll);
  unset($d);
 }
 clLog('Descript binds end', ltEnd);
 return $AContent;
}



//------------------------------------------------------------------------------
// Создание связи
//------------------------------------------------------------------------------

function DescriptBindPublic($AXML, $AContent)
{
 clLog('Descript public binding begin',ltBeg);

 // Получение параметров
 $ID=clGetIncome('ID', null, null);
 $IDTo=clGetIncome('IDTo', null, null);
 $IDBind=clGetIncome('IDBind', null, null);
 $IDRecurs=(boolean)clGetIncome('Recurs', null, false);

 // Вызов метода дескрипта
 $From=new TDescript($ID);
 $To=new TDescript($IDTo);
 $r=$From->BindBegin($To);
 $r=$From->Bind($To, $IDBind, true);
 $r=$From->BindEnd($To);
 unset($To);
 unset($From);

 // Возвращение результата
 $rt=new TResult;
 $rt->Code($r);
 $Result=$rt->End();
 unset($rt);

 clLog('Descript pablic binding end with result ['.$Result.']',ltEnd);
 return $Result;
}

//------------------------------------------------------------------------------
// Пользовательская установка связей дескрипта из формы
//------------------------------------------------------------------------------

function DescriptBindSetPublic($AXML, $AContent)
{
 // Callback функция рекурсивного обхода DescriptBindSetPublic
 function Before($ADescript, $AParams)
 {
  $ADescript->ChildRead();
  if ($AParams['Value']=='true') $AParams['From']->Bind($ADescript, $AParams['IDBind'], $AParams['Inherited']);
  if ($AParams['Value']=='false') $AParams['From']->Unbind($ADescript, $AParams['IDBind'], true);
  $ADescript->ChildFlush();
 }

 clLog('Descript bind setting public begin',ltBeg);

 // Получение параметров
 $ID=clGetIncome('ID', $AXML, null);
 $IDParent=clGetIncome('IDParent', $AXML, null);
 $Recurs=clGetIncome('Recurs', $AXML, '');

 $r='NoChange';
 $From=new TDescript($ID);
 $From->ParentRead();

 // Обход всех галочек
 foreach($_POST as $Key=>$Value)
 {
  $p=strpos($Key, 'Bind_');
  if ($p===0)
  {
   $IDBind = substr($Key, strlen('Bind_'));

   // Определенеи наследуемости на потомков
   $Inherited=clGetIncome('Inherited_'.$IDBind, $AXML, false);
   if ($Inherited=='on') $Inherited==true; else $Inherited==false;

   if ($Recurs=='on')
   {
    // Определение проводника и если он есть для текущей связи то...
    $IDBindConductor=clBindConductor($IDBind);
    if ($IDBindConductor!='')
    {
     // Рекурсивный обход начиная с родителя
     $Params=array('From'=>$From, 'IDBind'=>$IDBind, 'Value'=>$Value, 'Inherited'=>$Inherited);
     $Stack=null;
     $Start=new TDescript($IDParent);
     $Start->Trace($IDBindConductor, 'Before', null, $Params, $Stack);
     unset($Start);
    }
    $r=rcOk;
   }
   else
   {
    // Установка без рекурсивного обхода
    $Parent=new TDescript($IDParent);
    $Parent->ChildRead();
    if ($Value=='true') $r=$From->Bind($Parent, $IDBind, $Inherited);
    if ($Value=='false') $r=$From->Unbind($Parent, $IDBind, true);
    $Parent->ChildFlush();
    unset($Parent);
   }
  }
 }
 $From->ParentFlush();
 unset($From);

 // Возвращение результата
 $rt=new TResult;
 $rt->Code($r);
 $Result=$rt->End();
 unset($rt);

 clLog('Descript bind setting public end with result ['.$Result.']',ltEnd);
 return $Result;
}



//------------------------------------------------------------------------------
// Пользовательская операция link move copy remove
//------------------------------------------------------------------------------

function DescriptOperationPublic($AXML, $AContent)
{
 clLog('Descript operation begin',ltBeg);

 // Получение параметров

 $ID=clGetIncome('ID', $AXML, null);
 $IDFrom=clGetIncome('IDFrom', $AXML, null);
 $IDTo=clGetIncome('IDTo', $AXML, null);
 $Operation=clGetIncome('Operation', $AXML, null);

 clLog('Operation ['.$Operation.']',ltDeb);

 // Вызов метода дескрипта
 $r=rcOk;
 $d = new TDescript($ID);
 switch ($Operation)
 {
  case 'link':
   if ($r==rcOk)
   {
    $To=new TDescript($IDTo);
    $d->BindBegin($To);
    $d->Bind($To, 'bind_default', false);
    $d->BindEnd($To);
    unset($To);
   }
  break;
  case 'move':
   if ($r==rcOk) $d->Move($IDFrom, $IDTo, 'bind_default', false);
   if ($r==rcOk) $d->Move($IDFrom, $IDTo, 'bind_right', false);
  break;
  case 'copy':
   $r='CopyUnimplemented';
  break;
  case 'remove':
//   if ($r==rcOk) $d->Unbind($IDFrom, 'bind_default', true);
//   if ($r==rcOk) $d->Unbind($IDFrom, 'bind_right', true);
   $r='CopyUnimplemented';
  break;

  default: $r='UnknownOperation';
 }
 unset($d);

 // Возвращение результата
 $rt=new TResult;
 $rt->Code($r);
 $Result=$rt->End();
 unset($rt);

 clLog('Descript operation end with result ['.$Result.']',ltEnd);
 return $Result;
}


//==============================================================================
//
//==============================================================================
//------------------------------------------------------------------------------
// Пользовательская индексация дескриптов
//------------------------------------------------------------------------------

function DescriptIndexPublic($AXML, $AContent)
{

 function Callback($Descript, &$AParams)
 {
  return $Descript->Index($AParams['Lang'], false);
 }

 clLog('Descript indexing begin', ltBeg);

 // Чтение параметров
 $ID=clGetIncome('ID', null, '');
 $Type=clGetIncome('Type', null, '');

 // Чтение данных из сессии
 global $clSession;
 $Lang = $clSession->GetLanguage();
 $Site = $clSession->GetSite();

 // Запись в лог условий индексации
 clLog('ID ['.$ID.']', ltDeb);
 clLog('Language ['.$Lang.']', ltDeb);
 clLog('Site ['.$Site.']', ltDeb);

 // Обработка
 $Params = array('Lang'=>$Lang, 'Site'=>$Site);
 $Stack=null;
 $d=new TDescript($ID);

 switch ($Type)
 {
  case 'recursive': $r=$d->Trace('bind_default', 'Callback', null, $Params, $Stack); break;
  case 'single': $r=$d->Index($Lang, false); break;
  default: $r='UnknownIndexParam';
 }
 unset($d);

 // Оформление результата
 $rt=new TResult;
 $rt->Code($r);
 $Result=$rt->End();
 unset($rt);

 // Завершение
 clLog('Descript indexing end with result ['.$Result.']', ltEnd);
 return $Result;
}

