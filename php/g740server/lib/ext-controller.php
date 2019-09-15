<?php
/**
 * @file
 * G740Server, контроллер утилит.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

require_once('lib-base.php');
require_once('dsconnector.php');

//------------------------------------------------------------------------------
// Внешние контроллеры
//------------------------------------------------------------------------------
/** Класс предок внешних контроллеров
 */
class ExtController extends DSConnector {
/// Логировать работу в таблицу sysextlog
	public $isSysExtLog=false;

/** Разбор входных параметров
 *
 * @return	Array	параметры
 */
	public function getParams() {
		$result=Array();
		return $result;
	}
/** Запуск внешнего контроллера
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function go($params=Array()) {
		$result='';
		return $result;
	}
}
/** Класс предок контроллеров утилит
 */
class UtilController extends ExtController {
/// Возможность запускать с правами root - но только из localhost
	public $isCanExecutedAsRoot=false;
/// Наименование утилиты
	public $caption='Утилита';
}
/** Класс предок контроллеров сервисов
 */
class ServiceController extends ExtController {
/** Запуск сервиса
 *
 * @param	Array	$params параметры
 */
	public function go($params=Array()) {
		$result=$this->getResult($params);
		$this->sendHttpHeaders($params);
		echo $result;
	}
/** Формирование результата обращения к сервису в виде строки
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function getResult($params=Array()) {
		$result='';
		return $result;
	}
/** Отсылка HTTP заголовков
 *
 * @param	Array	$params
 */
	protected function sendHttpHeaders($params=Array()) {
		$format=$params['format'];
		if ($format=='html') {
			header("Content-type: text/html; charset=utf-8");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
		else if ($format=='xml') {
			header("Content-type: text/xml; charset=utf-8");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
		else if ($format=='text') {
			header("Content-type: text/plain; charset=utf-8");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
		else if ($format=='json') {
			header("Content-type: application/json; charset=utf-8");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
		}
	}
}
/** Класс предок контроллеров отчетов
 */
class ReportController extends ExtController {
/** Разбор входных параметров
 *
 * @return	Array	параметры
 */
	public function getParams() {
		$result=Array();
		$result['format']='html';
		if ($_REQUEST['format']=='xls') $result['format']='xls';
		if ($_REQUEST['format']=='word') $result['format']='word';
		return $result;
	}
/** Формирование отчета
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function go($params=Array()) {
		$resultBody=$this->getBody($params);
		$result=$this->getHead($params)."\n".$resultBody."\n".$this->getFooter($params);
		return $result;
	}
/** Формирование заголовка отчета
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
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
/** Формирование тела отчета
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function getBody($params=Array()) {
		return '';
	}
/** Формирование окончания отчета
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
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
/** Формирование заголовка отчета для HTML формата
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getHeadHtml($params=Array()) {
		header("Content-type: text/html; charset=utf-8");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		$pathResource=pathConcat(getCfg('href.root'),getCfg('path.root.resource'));
		$result=<<<HTML
<!doctype html>
<html lang="ru">
<head>
	<meta HTTP-EQUIV="content-type" content="text/html; charset=UTF-8"/>
	<link href="{$pathResource}/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet"/>
	<link href="{$pathResource}/reports/report.css" rel="stylesheet"/>
	<script src="{$pathResource}/jquery-1.12.4.min.js"></script>
</head>
<body>
HTML;
		return $result;
	}
/** Формирование заголовка отчета для XLS формата
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getHeadXls($params=Array()) {
		header("Content-Type: application/vnd.ms-excel; charset=utf-8;");
		header("Content-Disposition: attachment;filename=".date("d-m-Y").'-'.time()."-report.xls");
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
.xl-num
	{mso-number-format:"\@";}
.xl-num1
	{
		mso-style-parent:xl-num;
		mso-number-format:"0\.0";
	}
.xl-num2
	{
		mso-style-parent:xl-num;
		mso-number-format:"0\.00";
	}
.xl-num3
	{
		mso-style-parent:xl-num;
		mso-number-format:"0\.000";
	}
.xl-num4
	{
		mso-style-parent:xl-num;
		mso-number-format:"0\.0000";
	}
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
/** Формирование заголовка отчета для WORD формата
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getHeadWord($params=Array()) {
		header('Content-Type: application/msword; charset=utf-8');
		header('Content-Disposition: attachment;filename='.date("d-m-Y").'-'.time().'-report.doc');
		$result=<<<HTML
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
	<meta http-equiv="Content-Language" content="ru">
</head>
<body>
HTML;
		return $result;
	}
/** Формирование окончания отчета для HTML формата
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getFooterHtml($params=Array()) {
		$pathResource=pathConcat(getCfg('href.root'),getCfg('path.root.resource'));
		$result="\n".<<<HTML
	<script src="{$pathResource}/bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
HTML;
		return $result;
	}
/** Формирование окончания отчета для XLS формата
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getFooterXls($params=Array()) {
		$result="\n".<<<HTML
</body>
</html>
HTML;
		return $result;
	}
/** Формирование окончания отчета для WORD формата
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getFooterWord($params=Array()) {
		$result="\n".<<<HTML
</body>
</html>
HTML;
		return $result;
	}
/** Формирование панелей кнопок для отчета в формате HTML
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	protected function getToolBarPrint($params) {
		$result='';
		$format=$params['format'];
		if ($format!='html') return $result;
		
		$href=str2JavaScript($_SERVER['REQUEST_URI']);
		$htmlBtn='';
		if (!$params['btn.print.disabled']) {
			if ($htmlBtn) $htmlBtn.="\n";
			$htmlBtn.=<<<HTML
<div class="btn print" title="Распечатать" onclick="window.print()"></div>
HTML;
		}
		if (!$params['btn.word.disabled']) {
			if ($htmlBtn) $htmlBtn.="\n";
			$htmlBtn.=<<<HTML
<div class="btn word" title="Выгрузить в Word" onclick="window.open('{$href}&format=word')"></div>
HTML;
		}
		if (!$params['btn.xls.disabled']) {
			if ($htmlBtn) $htmlBtn.="\n";
			$htmlBtn.=<<<HTML
<div class="btn xls" title="Выгрузить в Excel" onclick="window.open('{$href}&format=xls')"></div>
HTML;
		}
		
		if ($htmlBtn) {
			$class='toolbarprint';
			if ($params['width100']) $class.=' width100';
			$result.="\n".<<<HTML
<div class="{$class}">
{$htmlBtn}
	<div style="clear:both"></div>
</div>
<div class="toolbarprintmargin"></div>
HTML;
		}
		return $result;
	}
}

/** Получение объекта утилиты по имени
 *
 * @param	String	$name имя формы
 * @return	UtilController объект контроллера
 */
function getUtilController($name) {
	global $_registerUtilController;
	if ($name!=str2FileName($name)) throw new Exception("Недопустимое имя утилиты '{$name}'");
	if ($_registerUtilController[$name]) return $_registerUtilController[$name];

	$fileName=pathConcat(
		getCfg('path.root'), 
		getCfg('path.root.utils', pathConcat(getCfg('path.root.php'),'utils')),
		"{$name}.php"
	);
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof UtilController) $_registerUtilController[$name]=$obj;
	}
	
	if (!$_registerUtilController[$name]) {
		$fileName=pathConcat(
			getCfg('path.root'), 
			getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
			'utils',
			"{$name}.php"
		);
		if (file_exists($fileName)) {
			$obj=include_once($fileName);
			if ($obj instanceof UtilController) $_registerUtilController[$name]=$obj;
		}
	}
	
	if (!$_registerUtilController[$name]) throw new Exception("Недопустимое имя утилиты '{$name}'");
	$result=$_registerUtilController[$name];
	if (!($result instanceof UtilController)) throw new Exception("Недопустимый тип контроллера утилиты '{$name}'");

	return $result;
}
/** Получение объекта сервиса по имени
 *
 * @param	String	$name имя формы
 * @return	ServiceController объект контроллера
 */
function getServiceController($name) {
	global $_registerServiceController;
	if ($name!=str2FileName($name)) throw new Exception("Недопустимое имя сервиса '{$name}'");
	if ($_registerServiceController[$name]) return $_registerServiceController[$name];

	$fileName=pathConcat(
		getCfg('path.root'), 
		getCfg('path.root.services', pathConcat(getCfg('path.root.php'),'services')),
		"{$name}.php"
	);
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof ServiceController) $_registerServiceController[$name]=$obj;
	}
	
	if (!$_registerServiceController[$name]) {
		$fileName=pathConcat(
			getCfg('path.root'), 
			getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
			'services',
			"{$name}.php"
		);
		if (file_exists($fileName)) {
			$obj=include_once($fileName);
			if ($obj instanceof ServiceController) $_registerServiceController[$name]=$obj;
		}
	}
	
	if (!$_registerServiceController[$name]) throw new Exception("Недопустимое имя сервиса '{$name}'");
	$result=$_registerServiceController[$name];
	if (!($result instanceof ServiceController)) throw new Exception("Недопустимый тип контроллера сервиса '{$name}'");
	
	return $result;
}
/** Получение объекта отчета по имени
 *
 * @param	String	$name имя формы
 * @return	ServiceController объект контроллера
 */
function getReportController($name) {
	global $_registerReportController;
	if ($name!=str2FileName($name)) throw new Exception("Недопустимое имя отчета '{$name}'");
	if ($_registerReportController[$name]) return $_registerReportController[$name];

	$fileName=pathConcat(
		getCfg('path.root'), 
		getCfg('path.root.reports', pathConcat(getCfg('path.root.php'),'reports')),
		"{$name}.php"
	);
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof ReportController) $_registerReportController[$name]=$obj;
	}
	
	if (!$_registerReportController[$name]) {
		$fileName=pathConcat(
			getCfg('path.root'), 
			getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
			'reports',
			"{$name}.php"
		);
		if (file_exists($fileName)) {
			$obj=include_once($fileName);
			if ($obj instanceof ReportController) $_registerReportController[$name]=$obj;
		}
	}
	
	if (!$_registerReportController[$name]) throw new Exception("Недопустимое имя отчета '{$name}'");
	$result=$_registerReportController[$name];
	if (!($result instanceof ReportController)) throw new Exception("Недопустимый тип контроллера отчета '{$name}'");
	return $result;
}
/// Кэш утилит
$_registerUtilController=Array();
/// Кэш сервисов
$_registerServiceController=Array();
/// Кэш отчетов
$_registerReportController=Array();