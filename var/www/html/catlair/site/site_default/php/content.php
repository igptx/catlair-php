<?php


//-----------------------------------------------------------------------------
// Контент
//-----------------------------------------------------------------------------

class TContent
{
 public $Type = 'Content';
 public $Descript = null;

 public function Prepare($ALang)
 {
  $Result = LoadContent('DescriptForm'.$this->Type.'.html');
  $Form = $Result['Content'];
//  $Form = str_replace('%File%', $this->Descript->GetLang($ALang, 'File', ''), $Form);
  return $Form;
 }


 public function Update($ALang, &$AResult)
 {
//  if (array_key_exists('File', $_POST)) $this->Descript->SetLang($ALang, 'File', $_POST['File']);
  return true;
 }

}


//------------------------------------------------------------------------------
// Читает содержимое из файла и возвращает его
//------------------------------------------------------------------------------
function clContentRead($ADescript)
{
 global $clSession;

 $Site=$clSession->GetSite();
 $Language=$clSession->GetLangeuge();

 if ($File!='')
 {
  if (file_exists($PathFile)) $Result['Content'] = file_get_contents ($PathFile);
  else
  {
   $Result['Error'] = 'FileNotFound';
   $Result['Message'] = 'Content file <b>'.$PathFile.'</b> not exist';
  }
 }
 else
 {
  $Result['Error'] = 'ParametrFileNotFound';
  $Result['Message'] = 'Parametr <b>file</b> not found';
 }
 return $Result;
}
