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
$hrefRoot=getCfg('href.root');

/**
Класс предок для отчетов
@package lib
@subpackage report-controller
*/
class ReportController {
	public function getParams() {
		$params=Array();
		$params['format']='html';
		if ($_REQUEST['format']=='xls') $params['format']='xls';
		if ($_REQUEST['format']=='word') $params['format']='word';
		return $params;
	}
	public function getHead($params=Array()) {
		if ($params['format']=='xls') {
			$result=$this->getHeadXls($params);
		}
		else if ($params['format']=='word') {
			$result=$this->getHeadWord($params);
		}
		else {
			$result=$this->getHeadHtml($params);
		}
		return $result;
	}
	public function getBody($params=Array()) {
		return '';
	}
	public function getFooter($params=Array()) {
		if ($params['format']=='xls') {
			$result=$this->getFooterXls($params);
		}
		else if ($params['format']=='word') {
			$result=$this->getFooterWord($params);
		}
		else {
			$result=$this->getFooterHtml($params);
		}
		return $result;
	}
	protected function getHeadHtml($params=Array()) {
		header("Content-type: text/html; charset=utf-8");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		$pathResource=pathConcat(getCfg('path.root'),getCfg('path.root.resource'));
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
	protected function getHeadXls($params=Array()) {
		header("Content-Type: application/vnd.ms-excel; charset=utf-8;");
		header("Content-Disposition: attachment;filename=".date("d-m-Y").'-'.time()."-export.xls");
		$result=<<<HTML
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Excel.Sheet>
<meta name=Generator content="Microsoft Excel 11">
<style>
<!--table
	{mso-displayed-decimal-separator:"\.";
	mso-displayed-thousand-separator:" ";}
@page
	{margin:.32in .21in .3in .31in;
	mso-header-margin:.22in;
	mso-footer-margin:.23in;
	mso-page-orientation:landscape;}
.font5
	{color:black;
	font-size:8.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Tahoma;
	mso-generic-font-family:auto;
	mso-font-charset:204;}
.xl-text 
	{mso-number-format:"\@";}
-->
</style>
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>форма</x:Name>
    <x:WorksheetOptions>
     <x:DefaultRowHeight>276</x:DefaultRowHeight>
     <x:FitToPage/>
     <x:FitToPage/>
     <x:Print>
      <x:ValidPrinterInfo/>
      <x:PaperSizeIndex>9</x:PaperSizeIndex>
      <x:Scale>55</x:Scale>
      <x:HorizontalResolution>-2</x:HorizontalResolution>
      <x:VerticalResolution>600</x:VerticalResolution>
     </x:Print>
     <x:Selected/>
     <x:Panes>
      <x:Pane>
       <x:Number>3</x:Number>
       <x:ActiveCol>2</x:ActiveCol>
      </x:Pane>
     </x:Panes>
     <x:ProtectContents>False</x:ProtectContents>
     <x:ProtectObjects>False</x:ProtectObjects>
     <x:ProtectScenarios>False</x:ProtectScenarios>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
  <x:WindowHeight>9312</x:WindowHeight>
  <x:WindowWidth>11340</x:WindowWidth>
  <x:WindowTopX>480</x:WindowTopX>
  <x:WindowTopY>48</x:WindowTopY>
  <x:ProtectStructure>False</x:ProtectStructure>
  <x:ProtectWindows>False</x:ProtectWindows>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<!--[if gte mso 9]>
<xml>
 <o:shapedefaults v:ext="edit" spidmax="3073"/>
</xml>
<![endif]-->
<!--[if gte mso 9]>
<xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="2"/>
 </o:shapelayout></xml>
<![endif]-->
</head>
<body link=blue vlink=purple class=xl27>
HTML;
		return $result;
	}
	protected function getHeadWord($params=Array()) {
		header('Content-Type: application/msword; charset=utf-8');
		$result=<<<HTML
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
	<meta http-equiv="Content-Language" content="ru">
	<link rel="stylesheet" type="text/css" href="css/config.css">
</head>
<body class="examinconfig">
HTML;
		return $result;
	}
	protected function getFooterHtml($params=Array()) {
		$pathResource=pathConcat(getCfg('path.root'),getCfg('path.root.resource'));
		$result="\n".<<<HTML
	<script src="{$pathResource}/bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
HTML;
		return $result;
	}
	protected function getFooterXls($params=Array()) {
		$result="\n".<<<HTML
</body>
</html>
HTML;
		return $result;
	}
	protected function getFooterWord($params=Array()) {
		$result="\n".<<<HTML
</body>
</html>
HTML;
		return $result;
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
		$text=$obj->getBody($params);
		echo $obj->getHead($params);
		echo $text;
		echo $obj->getFooter($params);
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