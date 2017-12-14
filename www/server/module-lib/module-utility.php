<?php
/**
Утилиты для пересчета вычисляемых полей, и перегенерации файлов
@package module-lib
@subpackage module-utility
*/

/**
Класс предок для утилит
@package module-lib
@subpackage module-utility
*/
class UtilController {
	public function getParams() {
		return Array();
	}
	public function go($params=Array(), $isEcho=false) {
	}
}

/**
Получить объект утилиты
@param	String	$name имя утилиты
@return	UtilController объект утилиты
*/
function getUtilController($name) {
	global $registerUtilController;
	
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	$str=strtolower($str);
	if ($name!=$str) throw new Exception("Недопустимое имя утилиты '{$name}'");
	if ($registerUtilController[$name]) return $registerUtilController[$name];

	$fileNameUtil=getCfg('path.utils')."/{$name}.php";
	if (file_exists($fileNameUtil)) {
		$obj=include_once($fileNameUtil);
		if ($obj instanceof UtilController) $registerUtilController[$name]=$obj;
	}
	
	if (!$registerUtilController[$name]) throw new Exception("Недопустимое имя утилиты '{$name}'");
	return $registerUtilController[$name];
}
function execUtilController($name) {
	$obj=getUtilController($name);
	$params=$obj->getParams();
	return $obj->go($params);
}
$registerUtilController=Array();
?>