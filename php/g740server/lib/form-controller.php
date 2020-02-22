<?php
/**
 * @file
 * G740Server, экранных форм.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс предок контроллеров экранных форм
 */
class FormController extends DSConnector {
/// Имя экранной формы
	public $form='';
/// Путь до папки размещения экранной формы
	public $path='';
	
/** Вернуть описание экранной формы
 *
 * Обычно описание экранной формы берется из XML файла, в нем производятся макрозамены
 * @param	Array	$params
 * @return	strXml описание экранной формы
 */
	public function getStrXmlDefinition($params=Array()) {
		$macro=$this->getDefinitionMacro($params);
		$macro['$toolBarBase$']=$this->getToolBarRequestsBase();
		$macro['$toolBarShift$']=$this->getToolBarRequestsShift();
		$macro['$toolBarBaseShift$']=$macro['$toolBarBase$']."\n".$macro['$toolBarShift$'];
		
		$result=$this->getDefinitionTemplate($params, $macro);
		
		$from=Array();
		$to=Array();
		foreach($macro as $key=>$value) {
			if (substr($key,0,1)=='%') {
				$from[]=str2XmlAttr($key);
				$to[]=str2XmlAttr($value);
			} else {
				$from[]=$key;
				$to[]=$value;
			}
		}
		$result=str_replace($from, $to, $result);
		return $result;
	}
/** Вызывается после обработки запроса источником данных. Позволяет дописать что-нибудь в ответ.
 *
 * @param	Array	$params
 * @param	Array	$events
 */
	public function go($params=Array(), $events=Array()) {
		$strXml=$this->getResponse($params, $events);
		if ($strXml) writeXml($strXml);
		return true;
	}
/** Удобный способ обработки запросов к экранной форме или связанными с ней источниками данных
 *
 * @param	Array	$params
 * @param	Array	$events
 * @return	strXml текст ответа
 */
	protected function getResponse($params=Array(), $events=Array()) {
		$result='';
	}
/** Вернуть список макрозамен для описания экранной формы
 *
 * @param	Array	$params
 * @return	Array список макрозамен
 */
	protected function getDefinitionMacro($params=Array()) {
		$result=Array();
		$result['%form%']=$this->form;
		$urlRoot=getCfg('urlRoot');
		if ($urlRoot=='/') $urlRoot='';
		$result['%urlRoot%']=$urlRoot;
		return $result;
	}
/** Вернуть шаблон описания экранной формы, обычно берется из файла
 *
 * @param	Array	$params
 * @param	Array	$macro
 * @return	string шаблон описания экранной формы
 */
	protected function getDefinitionTemplate($params=Array(), $macro=Array()) {
		$fileName=$macro['%TemplateFileName%'];
		if ($fileName) {
			$fileName=str_replace('\\','/',$fileName);
			if (strpos($fileName,'/')===false) {
				$fileName=pathConcat(
					$this->path,
					$fileName
				);
			}
		}
		if (!$fileName) {
			$lstName=explode('.', $this->form);
			$formName=$lstName[count($lstName)-1];
			$fileName=pathConcat(
				$this->path,
				$formName.'.xml'
			);
		}
		if (!is_file($fileName)) throw new Exception('Не найден файл с XML описанием экранной формы '.$fileName);
		$result=file_get_contents($fileName);
		return $result;
	}
/** Вернуть описание главного меню, по описанию из таблицы sysappmenu
 *
 * @return	string описание главного меню, по описанию из таблицы sysappmenu
 */
	protected function getSysAppMenuBar() {
		$dataSourceSysAppMenu=getDataSource('sysappmenu');
		if (!$dataSourceSysAppMenu) return '';
		$lst=$dataSourceSysAppMenu->execRefresh();
		
		$items=Array();
		$rowRoot=Array('name'=>'root', 'id'=>'0');
		$items['0']=$rowRoot;
		foreach($lst as &$row) {
			$id=$row['id'];
			$items[$id]=$row;
		}
		unset($row);

		foreach($lst as &$row) {
			unset($rowParent);
			$id=$row['id'];
			$parentid=$row['parentid'];
			if(!$items[$parentid]) continue;
			$rowParent=&$items[$parentid];
			if (!$rowParent['childs']) $rowParent['childs']=Array();
			$childs=&$rowParent['childs'];
			$childs[]=$id;
			unset($childs);
		}
		unset($row);
		unset($rowParent);
		
		$result='';
		$row=$items['0'];
		if ($row['childs']) foreach($row['childs'] as $childid) {
			$resultChild=$this->_getSysAppMenuBarRecursive($childid, $items);
			if (!$resultChild) continue;
			if ($result) $result.="\n<separator/>\n";
			$result.=$resultChild;
		}
		if (!$result) {
			$result=<<<XML
<request caption="|">
</request>
XML;
		}
		if (getPP('login')) $login=str2Attr(getPP('login'));
		$result=<<<XML
<menubar login="{$login}">
{$result}
</menubar>
XML;
		return $result;
	}
/** Вспомогательная рекурсивная процедура для построения описания главного меню
 *
 * @param	string	$id
 * @param	Array	$items
 * @return	string xml описание
 */
	protected function _getSysAppMenuBarRecursive($id, &$items) {
		$result='';
		
		$row=$items[$id];
		if (!$row) return $result;
		$permMode=trim($row['permmode']);
		$permOper=trim($row['permoper']);
		if (($permMode || $permOper) && !getPerm($permMode, $permOper)) return $result;
		
		if ($row['childs']) foreach($row['childs'] as $childid) {
			$resultChild=$this->_getSysAppMenuBarRecursive($childid, $items);
			if (!$resultChild) continue;
			if ($result) $result.="\n";
			$result.=$resultChild;
		}
		if ($result) {
			$caption=str2XmlAttr($row['name']);
			$icon=str2XmlAttr($row['icon']);
			$result=<<<XML
<request caption="{$caption}" icon="{$icon}" style="icontext">
{$result}
</request>
XML;
		}
		else {
			$caption=str2XmlAttr($row['name']);
			$icon=str2XmlAttr($row['icon']);
			$form=str2XmlAttr($row['form']);
			
			$params='';
			if (trim($row['params'])) foreach(explode("\n",$row['params']) as $p) {
				$n=strpos($p, '=');
				if ($n===false) continue;
				$name=str2XmlAttr(mb_substr($p, 0, $n));
				$value=str2XmlAttr(mb_substr($p, $n+1, 9999));
				if ($params) $params.="\n";
				$params.=<<<XML
	<param name="{$name}" value="{$value}"/>
XML;
				
			}
			
			$result=<<<XML
<request name="form" form="{$form}" caption="{$caption}" icon="{$icon}" style="icontext">
{$params}
</request>
XML;
		}
		return $result;
	}
/// Вернуть базовые request для toolbar
	protected function getToolBarRequestsBase() {
		$result=<<<XML
<request name="undo"/>
<request name="save"/>
<request name="refresh"/>
<separator/>
<request name="append"/>
<request name="delete"/>
XML;
		return $result;
	}
/// Вернуть request shift для toolbar
	protected function getToolBarRequestsShift() {
		$result=<<<XML
<separator/>
<request name="shift" mode="first"/>
<request name="shift" mode="before"/>
<request name="shift" mode="after"/>
<request name="shift" mode="last"/>
XML;
		return $result;
	}
}

/** Получить объект контроллера формы по имени
 *
 * @param	String	$name имя формы
 * @return	FormController объект контроллера формы
 */
function getFormController($name) {
	global $_registerFormController;
	if ($_registerFormController[$name]) return $_registerFormController[$name];
	
	$lstName=explode('.', $name);
	foreach($lstName as $itemName) {
		if ($itemName!=str2FileName($itemName)) throw new Exception("Недопустимое имя экранной формы '{$name}'");
	}
	$formName=$lstName[count($lstName)-1];
	
	$pathName=pathConcat(
		getCfg('path.root'),
		getCfg('path.root.forms', pathConcat(getCfg('path.root.php'),'forms'))
	);
	$pathWithFormName=$pathName;
	$pathWithoutFormName=$pathName;
	for($i=0; $i<count($lstName); $i++) {
		$itemName=$lstName[$i];
		$pathWithFormName=pathConcat($pathWithFormName, $itemName);
		if ($i<(count($lstName)-1)) $pathWithoutFormName=$pathWithFormName;
	}
	// Найдена папка с именем формы
	if (is_dir($pathWithFormName)) {
		$fileName=pathConcat($pathWithFormName, $formName.'.php');
		if (is_file($fileName)) {
			$obj=include_once($fileName);
			if ($obj instanceof FormController) {
				$_registerFormController[$name]=$obj;
			}
		}
		if (!$_registerFormController[$name]) {
			$_registerFormController[$name]=new FormController();
		}
		$obj=$_registerFormController[$name];
		if ($obj) $obj->path=$pathWithFormName;
	}

	if (!$_registerFormController[$name] && is_dir($pathWithoutFormName)) {
		// Найден файл с именем формы
		$fileNamePHP=pathConcat($pathWithoutFormName, $formName.'.php');
		$fileNameXML=pathConcat($pathWithoutFormName, $formName.'.xml');
		if (is_file($fileNamePHP)) {
			$obj=include_once($fileNamePHP);
			if ($obj instanceof FormController) {
				$_registerFormController[$name]=$obj;
			}
		}
		else if (is_file($fileNameXML)) {
			$_registerFormController[$name]=new FormController();
		}
		$obj=$_registerFormController[$name];
		if ($obj) $obj->path=$pathWithoutFormName;
	}

	if (!$_registerFormController[$name]) {
		// Описание формы найдено в g740server
		$pathG740ServerFormName=pathConcat(
			getCfg('path.root'),
			getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
			'forms'
		);
		if (is_dir($pathG740ServerFormName)) {
			$fileNamePHP=pathConcat($pathG740ServerFormName, $formName.'.php');
			$fileNameXML=pathConcat($pathG740ServerFormName, $formName.'.xml');
			if (is_file($fileNamePHP)) {
				$obj=include_once($fileNamePHP);
				if ($obj instanceof FormController) {
					$_registerFormController[$name]=$obj;
				}
			}
			else if (is_file($fileNameXML)) {
				$_registerFormController[$name]=new FormController();
			}
			$obj=$_registerFormController[$name];
			if ($obj) $obj->path=$pathG740ServerFormName;
		}
	}
	
	$obj=$_registerFormController[$name];
	if (!$obj) throw new Exception("Не найдено описание экранной формы '{$name}'");
	$obj->form=$name;
	return $obj;
}
/// Кэш контроллеров форм
$_registerFormController=Array();
