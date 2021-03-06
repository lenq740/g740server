<?php
/**
 * @file
 * G740Server, контроллер утилит.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
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

/// Необходимо логировать работу в таблицу sysextlog
	public $isSysExtLog=false;
	
/// Логирование начала выполнения утилиты
	public function doSysExtLogStart($dStart) {
		$sqlD=$dStart->format('Y-m-d');
		$sqlT=$dStart->format('H:i');
		$sqlCaption=$this->str2Sql($this->caption);
		$sql=<<<SQL
insert into sysextlog (d, tstart, name, message) values ('{$sqlD}', '{$sqlT}', '{$sqlCaption}', 'Выполняется ...');
SQL;
		$this->pdo($sql);
		$this->_sysextlogid=$this->getPDO()->lastInsertId();
	}
/// Логирование завершения выполнения утилиты
	public function doSysExtLogEnd($dStart) {
		$sqlD=$dStart->format('Y-m-d');
		$sqlT=$dStart->format('H:i');
		$sqlTEnd=(new DateTime())->format('H:i');
		$sqlCaption=$this->str2Sql($this->caption);
		$sysextlogid=$this->str2Sql($this->_sysextlogid);
		
		$isUpdate=false;
		if ($sysextlogid) {
			$sql=<<<SQL
select count(*) as count
from
	sysextlog
where
	id='{$sysextlogid}'
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['count']>0) $isUpdate=true;
		}
		if ($isUpdate) {
			$sql=<<<SQL
update sysextlog set 
	tend='{$sqlTEnd}',
	message='Завершено успешно'
where
	sysextlog.id='{$sysextlogid}'
SQL;
			$this->pdo($sql);
		}
		else {
			$sql=<<<SQL
insert into sysextlog (d, tstart, tend, name, message) values ('{$sqlD}', '{$sqlT}', '{$sqlTEnd}', '{$sqlCaption}', 'Завершено успешно');
SQL;
			$this->pdo($sql);
			$this->_sysextlogid=$this->getPDO()->lastInsertId();
		}
	}
/// Логирование ошибки при выполнении утилиты
	public function doSysExtLogError($dStart, $e) {
		$sqlD=$dStart->format('Y-m-d');
		$sqlT=$dStart->format('H:i');
		$sqlTEnd=(new DateTime())->format('H:i');
		$sqlCaption=$this->str2Sql($this->caption);
		$sqlMessage=$this->str2Sql($e->getMessage());
		$sysextlogid=$this->str2Sql($this->_sysextlogid);
		
		$isUpdate=false;
		if ($sysextlogid) {
			$sql=<<<SQL
select count(*) as count
from
	sysextlog
where
	id='{$sysextlogid}'
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['count']>0) $isUpdate=true;
		}
		if ($isUpdate) {
			$sql=<<<SQL
update sysextlog set 
	tend='{$sqlTEnd}',
	message='{$sqlMessage}',
	iserror='1'
where
	sysextlog.id='{$sysextlogid}'
SQL;
			$this->pdo($sql);
		}
		else {
			$sql=<<<SQL
insert into sysextlog (d, tstart, tend, name, message, iserror) values ('{$sqlD}', '{$sqlT}', '{$sqlTEnd}', '{$sqlCaption}', '{$sqlMessage}', '1');
SQL;
			$this->pdo($sql);
			$this->_sysextlogid=$this->getPDO()->lastInsertId();
		}
	}
	protected $_sysextlogid=0;
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
 *
 * Бумага A4, размеры:  210 x 297mm, внутренний размер с учетом полей странцы: 190 x 285mm
 *
 * CSS подходит для MS-Word и HTML, все размеры задавать в mm
 *
 * Особенности HTML для MS-Word
 * - не понимает SVG
 * - не понимает несколько классов в одном HTML элементе (можно пытаться делать вложенные div, каждому задавая класс)
 * - похоже не понимает явного указания не разбивать страницу (page-break-inside: avoid), однако старается не разбивать tr в таблице, этим можно пользоваться
 */
	protected function getHeadWord($params=Array()) {
		header('Content-Type: application/msword; charset=utf-8');
		header('Content-Disposition: attachment;filename='.date("d-m-Y").'-'.time().'-report.doc');
		$result=<<<HTML
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>
	<meta http-equiv="Content-Language" content="ru">
	
	<style type="text/css">
@media print {
	.print_no_display {
		display:none;
	}
} 
.print_no_page_break {
	page-break-inside: avoid;
}
.print_page_break_after {
	page-break-after: always;
}
.print_page_break_before {
	page-break-before: always;
}
	
@page page_a4_landscape	{
	size: 297mm 210mm;
	margin:6mm;
	padding: 0mm;
	mso-page-orientation:landscape;
	mso-header-margin:0px;
	mso-footer-margin:0px;
	mso-paper-source:0;
}
div.page_a4_landscape {
	page:page_a4_landscape;
}

body {
	font-family: Arial;
	font-size: 11px;
  	cursor: default;
	background-color: white;
	padding: 0px;
	margin: 0px;
}
h1 {
	padding-left: 0px;
	font-size: 18px;
	color: black;
	text-align: left;
}
h2 {
	padding-left: 0px;
	font-size: 16px;
	color: black;
	text-align: left;
}
h3 {
	padding-left: 0px;
	font-size: 14px;
	color: black;
	text-align: left;
}
table.bordered{
	border-collapse: collapse;
}
table.bordered th {
	border-collapse: collapse;
	border-style: solid;
	border-color: gray;
	border-width: 1px
}
table.bordered td {
	border-collapse: collapse;
	border-style: solid;
	border-color: gray;
	border-width: 1px
}
	</style>
</head>
<body>
HTML;
		return $result;
	}
	
	protected function getPageBreakWord() {
		$result=<<<HTML
<br clear="all" style="mso-special-character:line-break;page-break-before:always"/>
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
/** Формирование панели кнопок для отчета в формате HTML
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