// Расширение SimpleXML
class SimpleXmlEx extends SimpleXMLElement
{

 //Получение атрибута
 function get_attr($attr,$value=false)
 {
  if($value)
  {
   foreach($this->attributes() as $e_attr => $e_value)
    if($e_attr==$attr&&$e_value==$value) return $e_value;
  }
  else
  {
   foreach($this->attributes() as $e_attr => $e_value)
    if($e_attr==$attr) return $e_value;
  }
  return false;
 }


 //Удаление атрибута
 function del_attr($attr,$value=false)
 {
  $dom = dom_import_simplexml($this);
  if($value)
  {
   if ($this->get_attr($attr,$value)) $dom->removeAttribute($attr);
  }
  else
  {
   if ($this->get_attr($attr)) $dom->removeAttribute($attr);
  }
 }


 //Добавление/изменение атрибута
 function add_attr($attr,$value=false)
 {
  $dom = dom_import_simplexml($this);
  if($value)
  {
   if($this->get_attr($attr)) $dom->removeAttribute($attr);
   $dom->setAttribute($attr,$value);
  }
  else
  {
   if($this->get_attr($attr)) $dom->removeAttribute($attr);
   $dom->setAttribute($attr,'');
  }
 }


 // Проверка атрибута на наличие потомков
 function is_leaf()
 {
  return !$this->children()?true:false;
 }


 //Удаление ноды
 function del_node()
 {
  $dom = dom_import_simplexml($this);
  $dom->parentNode->removeChild($dom);
 }


 // Приведение 2-х мерного массива к 1 мерному
 private function array_repack($array)
 {
  $ret = false;
  foreach($array as $elm)
  {
   if(is_array($elm)) foreach($elm as $elm2) $ret[]=$elm2;
   else $ret[]=$elm;
  }
  return $ret;
 }


 // Поиск элементов среди потомков по имени
 function search_child_by_name($name)
 {
  $rez = false;
  $ret = false;
  if(!$this->is_leaf())
  {
   foreach($this->children() as $key => $child )
   {
    $rez[] = $child->search_child_by_name($name);
   }
   foreach($rez as $key => $elm)
   {
    if(!$elm) unset($rez[$key]);
   }
  }
  if($this->getName()==$name) $rez[]=$this;
  if($rez) $ret = $this->array_repack($rez); return $ret;
 }


 // Поиск элементов по атрибутам
 function search_child_by_attr($attr,$val=false)
 {
  $rez = false;
  $ret = false;
  if(!$this->is_leaf())
  {
   foreach($this->children() as $key => $child )
   {
    $rez[] = $child->search_child_by_attr($attr,$val);
   }
   foreach($rez as $key => $elm) if(!$elm) unset($rez[$key]);
  }
  if($val)
  {
   if($this->get_attr($attr,$val)) $rez[]=$this;
  }
  else
  {
   if($this->get_attr($attr)) $rez[]=$this;
  }
  if($rez) $ret = $this->array_repack($rez); return $ret;
 }


 // Удаление элементов по имени
 function del_child_by_name($name)
 {
  $childs = $this->search_child_by_name($name);
  foreach($childs as $child) $child->del_node();
 }


 //Удаление элементов по атрибутам
 function del_child_by_attr($attr,$value=false)
 {
  $childs = $this->search_child_by_attr($attr,$value);
  foreach($childs as $child) $child->del_node();
 }

}
