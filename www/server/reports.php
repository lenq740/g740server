<?php
/**
Отчеты
@package lib
@subpackage report-controller
*/
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED);
require_once('config/.config.php');
require_once('lib/datasource-controller.php');
require_once('lib/dsautogenerator.php');

$config['path.root']=pathConcat('..',getCfg('path.root'));

/**
Класс предок для отчетов
@package lib
@subpackage report-controller
*/
class ReportController {
	public function getParams() {
		$params=Array();
		$params['type']='html';
		return $params;
	}
	public function go($params=Array()) {
		return '';
	}
}

/**
Получить объект отчета
@param	String	$name имя отчета
@return	ReportController объект отчета
*/
function getReportController($name) {
	global $_registerReportController;
	
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	$str=strtolower($str);
	if ($name!=$str) throw new Exception("Недопустимое имя отчета '{$name}'");
	if ($_registerReportController[$name]) return $_registerReportController[$name];

	$fileNameReport=pathConcat(getCfg('path.root'), getCfg('path.root.reports'),"{$name}.php");
	if (file_exists($fileNameReport)) {
		$obj=include_once($fileNameReport);
		if ($obj instanceof ReportController) $_registerReportController[$name]=$obj;
	}
	
	if (!$_registerReportController[$name]) throw new Exception("Недопустимое имя отчета '{$name}'");
	return $_registerReportController[$name];
}
function execReportController($name) {
	$obj=getReportController($name);
	$params=$obj->getParams();
	return $obj->go($params);
}
$_registerReportController=Array();

function getHtmlHead($params=Array()) {
	header("Content-type: text/html; charset=utf-8");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	$pathResource=pathConcat(getCfg('href.root'),getCfg('path.root.resource'));
	if ($pathResource!='') $pathResource='/'.$pathResource;
	$result=<<<HTML
<!doctype html>
<html lang="ru">
<head>
	<meta HTTP-EQUIV="content-type" content="text/html; charset=UTF-8"/>
	<link href="{$pathResource}/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet" media="screen"/>
	
	<script src="{$pathResource}/jquery/jquery-1.12.1.min.js"></script>
</head>
<body>
HTML;
	return $result;
}
function getHtmlFooter($params=Array()) {
	$pathResource=pathConcat(getCfg('href.root'),getCfg('path.root.resource'));
	if ($pathResource!='') $pathResource='/'.$pathResource;
	$result="\n".<<<HTML
	<script src="{$pathResource}/bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
HTML;
	return $result;
}

try {
	$pdoDB=new PDODataConnectorMySql(
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost')
	); // Устанавливаем соединение с базой данных
	regPDO($pdoDB,'default');
	try {
		$pdoDB->beginTransaction();
		$mode=$_REQUEST['mode'];
		if (!$mode) throw new Exception('Не задан обязательный параметр mode');
		$obj=getReportController($mode);
		if (!obj) throw new Exception("Задан недопустимый параметр mode='{$mode}'");
		$params=$obj->getParams();
		$text=$obj->go($params);
		if ($params['type']=='html') {
			echo getHtmlHead($params);
			echo $text;
			echo getHtmlFooter($params);
		}
		else {
			throw new Exception("Недопустимое значение параметра type='{$params['type']}'");
		}
		if ($pdoDB->inTransaction()) $pdoDB->commit();
	}
	catch (Exception $e) {
		if ($pdoDB->inTransaction()) $pdoDB->rollBack();
		throw new Exception($e->getMessage());
	}
}
catch (Exception $e) {
	header("Content-type: text/plain; charset=utf-8");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	echo $e->getMessage();
}