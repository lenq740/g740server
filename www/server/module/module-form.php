<?php
/**
Библиотека источников данных
@package module
@subpackage module-form
*/

require_once('module-lib.php');
/**
Класс предок для экранной формы
@package module
@subpackage module-form
*/
class FormController {
	public function getStrXmlDefinition($params=Array()) {
		$macro=$this->getDefinitionMacro($params);
		$result=$this->getDefinitionTemplate($params, $macro);
		$from=Array();
		$to=Array();
		foreach($macro as $key=>$value) {
			if (substr($key,0,1)=='%') {
				$from[]=str2Attr($key);
				$to[]=str2Attr($value);
			} else {
				$from[]=$key;
				$to[]=$value;
			}
		}
		$result=str_replace($from, $to, $result);
		return $result;
	}
	public function go($params=Array(), $events=Array()) {
		return true;
	}
	protected function getDefinitionMacro($params=Array()) {
		$result=Array();
		$result['%form%']=$params['#request.form'];
		$urlRoot=getCfg('urlRoot');
		if ($urlRoot=='/') $urlRoot='';
		$result['%urlRoot%']=$urlRoot;
		return $result;
	}
	protected function getDefinitionTemplate($params=Array(), $macro=Array()) {
		$form=$params['#request.form'];
		$fileName=$macro['%TemplateFileName%'];
		if (!$fileName) {
			$pathForm=getCfg('pathForm');
			$fileName="{$pathForm}/xml/{$form}.xml";
		}
		if (!file_exists($fileName)) throw new Exception('Не найден файл с XML описанием экранной формы '.$fileName);
		$result=file_get_contents($fileName);
		return $result;
	}
}

function getFormController($name) {
	global $registerFormController;
	$pathForm=getCfg('pathForm');
	
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	if ($name!=$str) throw new Exception("Недопустимое имя экранной формы '{$name}'");
	if ($registerFormController[$name]) return $registerFormController[$name];

	$fileName="{$pathForm}/{$name}.php";
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof FormController) $registerFormController[$name]=$obj;
	}
	
	if (!$registerFormController[$name]) $registerFormController[$name]=new FormController();
	return $registerFormController[$name];
}
$registerFormController=Array();
?>