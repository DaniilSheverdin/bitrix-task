<?//пример обработчика, который при сохранении элемента переводит в транслит его заголовок, добавляет к заголовку текущую дату (для уникальности) и передает в поле "Символьный код"

// файл /bitrix/php_interface/init.php 
// регистрируем обработчик
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("CymCode", "OnBeforeIBlockElementAddHandler")); 

class CymCode 
{ 

// записывает все что передадут в /bitrix/log.txt 
function log_array() { 
   $arArgs = func_get_args(); 
   $sResult = ''; 
   foreach($arArgs as $arArg) { 
      $sResult .= "\n\n".print_r($arArg, true); 
   } 

   if(!defined('LOG_FILENAME')) { 
      define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'].'/bitrix/log.txt'); 
   } 
   AddMessage2Log($sResult, 'log_array -> '); 
} 
// создаем обработчик события "OnBeforeIBlockElementAdd" 
function OnBeforeIBlockElementAddHandler(&$arFields) 
{ 
   if(strlen($arFields["CODE"])<=0) 
   { 
      $arFields["CODE"] = CymCode::imTranslite($arFields["NAME"])."_".date('dmY'); 
           CymCode::log_array($arFields); // убрать после отладки 

      return; 
   } 
  } 



function imTranslite($str){ 
// транслитерация корректно работает на страницах с любой кодировкой 
// ISO 9-95 
   static $tbl= array(
      'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'g', 'з'=>'z',
      'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p',
      'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'ы'=>'y', 'э'=>'e', 'А'=>'A',
      'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ж'=>'G', 'З'=>'Z', 'И'=>'I',
      'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
      'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Ы'=>'Y', 'Э'=>'E', 'ё'=>"yo", 'х'=>"h",
      'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh", 'щ'=>"shch", 'ъ'=>"", 'ь'=>"", 'ю'=>"yu", 'я'=>"ya",
      'Ё'=>"YO", 'Х'=>"H", 'Ц'=>"TS", 'Ч'=>"CH", 'Ш'=>"SH", 'Щ'=>"SHCH", 'Ъ'=>"", 'Ь'=>"",
      'Ю'=>"YU", 'Я'=>"YA", ' '=>"_", '№'=>"", '«'=>"<", '»'=>">", '—'=>"-" 
   ); 
    return strtr($str, $tbl); 
 } 
}
?>