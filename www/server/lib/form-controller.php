<?php
/**
 * @file
 * Контроллер экранных форм
 */

/** Класс предок контроллеров экранных форм
 */
class FormController {
/** Вернуть описание экранной формы
 *
 * Обычно описание экранной формы берется из XML файла, в нем производятся макрозамены
 * @param	Array	$params
 * @return	strXml описание экранной формы
 *
 *  - param['#request.form'] - имя экранной формы
 */
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
/** Вызывается после обработки запроса источником данных. Позволяет дописать что-нибудь в ответ.
 *
 * @param	Array	$params
 * @param	Array	$events
 */
	public function go($params=Array(), $events=Array()) {
		return true;
	}
/** Вернуть список макрозамен для описания экранной формы
 *
 * @param	Array	$params
 * @return	Array список макрозамен
 */
	protected function getDefinitionMacro($params=Array()) {
		$result=Array();
		$result['%form%']=$params['#request.form'];
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
		$form=$params['#request.form'];
		$fileName=$macro['%TemplateFileName%'];
		if (!$fileName) {
			$fileName=pathConcat(getCfg('path.root'), getCfg('path.root.forms'),'xml',"{$form}.xml");
		}
		if (!file_exists($fileName)) throw new Exception('Не найден файл с XML описанием экранной формы '.$fileName);
		$result=file_get_contents($fileName);
		return $result;
	}
/** Вернуть описание главного меню, по описанию из таблицы sysmenu
 *
 * @return	string описание главного меню, по описанию из таблицы sysmenu
 */
	protected function getSysMenuBar() {
		$dataSourceSysMenu=getDataSource('sysmenu');
		if (!$dataSourceSysMenu) return '';
		$lst=$dataSourceSysMenu->execRefresh();
		
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
			$klsparent=$row['klsparent'];
			if(!$items[$klsparent]) continue;
			$rowParent=&$items[$klsparent];
			if (!$rowParent['childs']) $rowParent['childs']=Array();
			$childs=&$rowParent['childs'];
			$childs[]=$id;
			unset($childs);
		}
		unset($row);
		unset($rowParent);
		
		$result='';
		$row=$items['0'];
		if ($row['childs']) foreach($row['childs'] as $klschild) {
			$resultChild=$this->_getSysMenuBarRecursive($klschild, $items);
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
		if ($_SESSION['connect_login']) $login=str2Attr($_SESSION['connect_login']);
		$result=<<<XML
<menubar login="{$login}">
{$result}
</menubar>
XML;
		return $result;
	}
/// Вспомогательная рекурсивная процедура для построения описания главного меню
	protected function _getSysMenuBarRecursive($id, &$items) {
		$result='';
		
		$row=$items[$id];
		if (!$row) return $result;
		$permMode=trim($row['permmode']);
		$permOper=trim($row['permoper']);
		if (($permMode || $permOper) && !getPerm($permMode, $permOper)) return $result;
		
		if ($row['childs']) foreach($row['childs'] as $klschild) {
			$resultChild=$this->_getSysMenuBarRecursive($klschild, $items);
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
}

/** Получить объект контроллера формы по имени
 *
 * @param	String	$name имя формы
 * @return	FormController объект контроллера формы
 */
function getFormController($name) {
	global $_registerFormController;
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	if ($name!=$str) throw new Exception("Недопустимое имя экранной формы '{$name}'");
	if ($_registerFormController[$name]) return $_registerFormController[$name];

	$fileName=pathConcat(getCfg('path.root'), getCfg('path.root.forms'), "{$name}.php");
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof FormController) $_registerFormController[$name]=$obj;
	}
	
	if (!$_registerFormController[$name]) $_registerFormController[$name]=new FormController();
	return $_registerFormController[$name];
}
/// Кэш контроллеров форм
$_registerFormController=Array();
