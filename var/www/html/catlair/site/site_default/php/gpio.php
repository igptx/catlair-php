<?php

// Variables
$GPIO_PATH = '/sys/class/gpio/';
$GPIO_DIRECTION_OUT = 'out';
$GPIO_DIRECTION_IN = 'in';

//==============================================================================
// GPIO Object
//==============================================================================

class TGPIO
{

  // Constructor
  function __construct($ANumber)
  {
   $this->Number = $ANumber;
   $this->Enable();
  }


  function Enable()
  {
   if (!file_exists($this->Path()))
   {
    global $GPIO_PATH;
    $File = $GPIO_PATH . 'export';
    $f=fopen($File, 'w');
    if ($f!==false)
    {
     fwrite($f, $this->Number);
     fclose($f);
    }
   }
   return $this->Enabled();
  }


  function Enabled()
  {
   return file_exists($this->Path());
  }


  function Disable()
  {
   global $GPIO_PATH;
   $File = $GPIO_PATH . 'unexport';
   if (file_exists($this->Path()))
   {
    $f=fopen($File, 'w');
    if ($f!==false)
    {
     fwrite($f, $this->Number);
     fclose($f);
    }
   }
   return $this->Enabled();
  }


  function SetDirection($ADirection)
  {
   $File = $this->Path().'direction';
   $f=fopen($File, 'w');
   if ($f !== false)
   {
    fwrite($f, $ADirection);
    fclose($f);
    $Result = 'true';
   }
   else
   {
    $Result = 'false';
   }

   echo ('Direction: '.$this->GetDirection().' file: '.$File);
   return $Result;
  }


  function GetDirection()
  {
   $Result='unknown';
   $File = $this -> Path() . 'direction';
   $f=fopen($File, 'r');
   if ($f!==false)
   {
    $Result = fread($f, filesize($File));
    fclose($f);
   }
   return $Result;
  }


  function SetValue($AValue)
  {
   $File = $this->Path().'value';
   if ($AValue) $Value='1'; 
   else $Value='0';
   if ($this->Enabled() && is_writable($File)) 
   {
    file_put_contents($File, $Value);
    $Result = true;
   }
   else $Result = false;
   return $Result;
  }


  function GetValue()
  {
   $Result='unknown';
   $File = $this -> Path() . 'value';
   $f=fopen($File, 'r');
   if ($f!==false)
   {
    $Result = fread($f, filesize($File));
    fclose($f);
   }
   return $Result==1;
  }




  function Path()
  {
   global $GPIO_PATH;
   return $GPIO_PATH . 'gpio' . $this->Number . '/';
  }

}

?>
