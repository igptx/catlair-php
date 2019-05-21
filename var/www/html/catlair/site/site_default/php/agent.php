<?php

//-----------------------------------------------------------------------------
// Агенты
//-----------------------------------------------------------------------------

function clAgentPrepare($ADescript, $ALang)
{
 $Result = clDescriptContentByID('DescriptFormAgent.html');
 $Form = $Result['Content'];
 $Form = str_replace('%Login%', $ADescript->Get('Login', ''), $Form);
 return $Form;
}


function clAgentUpdate($ADescript, $ALang)
{
 if (array_key_exists('Login', $_POST)) $this->Descript->Set('Login', $_POST['Login']);
 return true;
}

