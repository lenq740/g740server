<?php
/**
 * @file
 * Контроллер утилит
 */
require_once('lib-base.php');
require_once('datasource-controller.php');
require_once('dsautogenerator.php');
 
/** Класс предок для утилит
 */
class UtilController {
	public function getParams() {
		return Array();
	}
	public function go($params=Array(), $isEcho=false) {
	}
}
/** Получить объект утилиты по имени
 *
 * @param	String	$name имя формы
 * @return	FormController объект контроллера формы
 */
function getUtilController($name) {
	global $_registerUtilController;
	
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
	if ($_registerUtilController[$name]) return $_registerUtilController[$name];

	$fileNameUtil=pathConcat(getCfg('path.root'), getCfg('path.root.utils'),"{$name}.php");
	if (file_exists($fileNameUtil)) {
		$obj=include_once($fileNameUtil);
		if ($obj instanceof UtilController) $_registerUtilController[$name]=$obj;
	}
	
	if (!$_registerUtilController[$name]) throw new Exception("Недопустимое имя утилиты '{$name}'");
	return $_registerUtilController[$name];
}
/** Выполнить утилиту по имени
 *
 * @param	String	$name имя формы
 * @return	FormController объект контроллера формы
 */
function execUtilController($name, $isEcho=false) {
	$obj=getUtilController($name);
	$params=$obj->getParams();
	return $obj->go($params, $isEcho);
}
$_registerUtilController=Array();
