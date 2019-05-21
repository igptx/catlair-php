<?php

//-----------------------------------------------------------------------------
// Специфические процедуры для объекта типа bind
//-----------------------------------------------------------------------------

function clBindPrepare($ADescript, $ALang, &$Result)
{
 $Form = clDescriptContentByID('bind_edit_form.html');

 $Result->Set('IDBindConductor', $ADescript->Get('IDBindConductor', ''));
 $Result->Set('ColorR', $ADescript->Get('ColorR', ''));
 $Result->Set('ColorG', $ADescript->Get('ColorG', ''));
 $Result->Set('ColorB', $ADescript->Get('ColorB', ''));
 $Result->Set('ColorA', $ADescript->Get('ColorA', ''));

 return $Form;
}


function clBindUpdate($ADescript, $ALang)
{
 $ADescript->Set('IDBindConductor', clGetIncome('IDBindConductor', null, ''));
 $ADescript->Set('ColorR', clGetIncome('ColorR', null, ''));
 $ADescript->Set('ColorG', clGetIncome('ColorG', null, ''));
 $ADescript->Set('ColorB', clGetIncome('ColorB', null, ''));
 $ADescript->Set('ColorA', clGetIncome('ColorA', null, ''));
 return true;
}


function clBindConductor($AIDBind)
{
 $d=new TDescript($AIDBind);
 $d->Read();
 $r=trim($d->Get('IDBindConductor', ''));
 unset($d);
 return $r;
}


