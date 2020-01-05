<?php
/**
 * @file
 * G740Server, генератор отчетов в виде таблиц с подитогами
 */

/** Класс, генератор отчетов в виде таблиц с подитогами
 *
 * Создание:
 *	$objReport=new RepGenerator($params)
 *		$params['fields']	- описание колонок отчета
 *		$params['rows']		- массив строк, возвращаемых запросом
 *		$params['sql']		- SQL запрос
 *		$params['total']	- 1|0 нужен подсчет итогов
 *		$params['format']	- 'html'|'xls' формат
 * Описание колонок
 *		$field['caption']			- заголовок
 *		$field['subcaption']		- заголовок
 *		$field['fieldname']			- имя поля
 *		$field['type']				- string, num, date
 *		$field['dec']				- количество десятичных знаков после запятой
 *		$field['width']				- ширина колонки
 *
 *		$field['tgroup']			- 0|1 группировать, заголовки и итоги по изменению
 *		$field['tgroup.title']		- 1|0 формировать заголовок
 *		$field['tgroup.itog']		- 1|0|Текст подитога - формировать подитог
 *		$field['tgroup.column']		- 0|1 отображать колонку в таблице
 *		$field['tgroup.field']		- поле, изменение которого отслеживается
 *
 *		$field['tsum']				- 1, 'sum', 'count'
 * Признак игнорирования строки
 *		$row['row_visible']=0
 * Публичные методы:
 *	$objReport->SetFields($fields)	- задать новые описания fields
 *	$objReport->Get();				- Отчет
 *
 * ----------------------------------------------------------------
 * НЕДОДЕЛКИ!!!!!
 * - двухуровневая шапка таблицы
 * - нормальное сворачивание и разворачивание, нужны значки + -, возможно вертикальные палочки
 * - хорошо бы найти где-нибудь вариант сворачиваемых таблиц и спереть дизайн
 * - CSS под печать
 * - Excel
 * - Word ???
 * - документация
 */
class RepGenerator extends DSConnector {
/// список полей
	protected $fields=null;
	protected $fieldsByName=null;
/// наличие итогов
	protected $isTotal=true;
/// количество колонок, по которым производится группировка
	protected $itogCount=0;
	protected $columnCount=0;
	protected $columnTSumPosition=0;
/// массив итогов
	protected $total=null;
/// массив строк отчета
	protected $rows=null;
	
/** Конструктор, создать экземпляр объекта
 *
 * @param	Array	$para
 *
 * - para['fields']	- описание колонок отчета
 * - para['rows']	- строки
 * - para['sql']	- SQL запрос
 */
	function __construct($para=Array())	{
		if (!is_array($para['fields'])) throw new Exception('Ошибка при обращении к RepGenerator, не задан параметр fields');
		$this->setFields($para['fields']);
		if (isset($para['total'])) $this->isTotal=$para['total']?true:false;
		
		if (!($para['rows'] || $para['sql'])) {
			$rows=Array();
			$rows[]=Array();
			$para['rows']=$rows;
		}
		if ($para['rows']) {
			$this->rows=$para['rows'];
		}
		else if ($para['sql']) {
			$this->rows=Array();
			$q=$this->pdo($para['sql']);
			while ($rec=$this->pdoFetch($q)) {
				$this->rows[]=$rec;
			}
		}
		else {
			throw new Exception('Ошибка при обращении к RepGenerator, не заданы параметры rows и sql');
		}
		
	}
/** Деструктор, уничтожить экземпляр объекта
 */
	function __destruct() {
		unset($this->fields);
		unset($this->rows);
		unset($this->total);
	}
/** Задать описатель колонок отчета
 *
 * @param	Array $fields массив описаний колонок отчета
 */
	protected function setFields($fields) {
		$itogCount=0;
		$this->fields=Array();
		$this->fieldsByName=Array();
//	Сначала все колонки, по которым группировка, без отображения колонки
		foreach($fields as $fld) {
			if (!$fld['fieldname']) continue;
			if (!$fld['tgroup']) continue;
			if ($fld['tgroup.column']) continue;
			unset($fld['tsum']);
			$this->fields[]=$fld;
			$itogCount++;
		}
//	Потом все колонки, по которым группировка, с отображением колонки
		foreach($fields as $fld) {
			if (!$fld['fieldname']) continue;
			if (!$fld['tgroup']) continue;
			if (!$fld['tgroup.column']) continue;
			unset($fld['tsum']);
			$this->fields[]=$fld;
			$itogCount++;
		}
//	Потом колонки, по которым нет группировки и итогов
		foreach($fields as $fld) {
			if (!$fld['fieldname']) continue;
			if ($fld['tgroup']) continue;
			if ($fld['tsum']) continue;
			$this->fields[]=$fld;
		}
		
//	Потом колонки итогов
		$tsumCount=0;
		foreach($fields as $fld) {
			if (!$fld['fieldname']) continue;
			if ($fld['tgroup']) continue;
			if (!$fld['tsum']) continue;
			$this->fields[]=$fld;
			$tsumCount++;
		}
		if ($tsumCount==0) $this->isTotal=false;
		$this->itogCount=$itogCount;
		$this->columnCount=count($this->fields);
		$columnTSumPosition=0;
		$index=0;
		foreach($this->fields as &$fld) {
			$fieldName=$fld['fieldname'];
			$this->fieldsByName[$fieldName]=&$fld;
			if ($fld['tsum'] && !$columnTSumPosition) $columnTSumPosition=$index;
			unset($fld);
			$index++;
		}
		$this->columnTSumPosition=$columnTSumPosition;
	}
/** Инициализировать структуры данных, предназначенные для хранения промежуточных итогов
 */
	protected function itogInit() {
		foreach($this->fields as &$fld) {
			if (!$fld['tgroup']) break;
			$itog=Array();
			$itog['value']=null;
			if ($fld['tgroup.field']) {
				$itog['valueid']=null;
			}

			$itog['fieldname']=$fld['fieldname'];
			foreach($this->fields as &$fldK) {
				if (!$fldK['tsum']) continue;
				$itog['field_'.$fldK['fieldname']]=0;
			}
			unset($fldK);
			$fld['itog']=$itog;
		}
		unset($fld);
		
		$this->total=Array();
		foreach($this->fields as &$fldK) {
			if (!$fldK['tsum']) continue;
			$this->total['field_'.$fldK['fieldname']]=0;
		}
		unset($fldK);
	}

/** Добавить значения очередной строки к промежуточным итогам
 *
 * @param	Array	$rec строка отчета
 */
	protected function generateItogAdd($rec) {
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=$this->itogCount;
		for ($i=0; $i<$itogCount; $i++) {
			$fld=&$fields[$i];
			$itog=&$fld['itog'];
			$fieldName=$fld['fieldname'];
			$itog['value']=$rec[$fieldName];
			if ($fld['tgroup.field']) {
				$fieldName=$fld['tgroup.field'];
				$itog['valueid']=$rec[$fieldName];
			}
			if (!isset($rec['row_visible']) || $rec['row_visible']) {
				for($k=$itogCount; $k<$count; $k++) {
					$fldK=&$fields[$k];
					if (!$fldK['tsum']) continue;
					$fieldName=$fldK['fieldname'];
					$value=0;
					if ($fldK['tsum']=='count') {
						if ($rec[$fieldName]) $value=1;
					}
					else {
						$value=$rec[$fieldName];
						if (!$value) $value=0;
					}
					$itog['field_'.$fieldName]+=$value;
				}
			}
		}
		if (!isset($rec['row_visible']) || $rec['row_visible']) {
			for($k=$itogCount; $k<$count; $k++) {
				$fldK=&$fields[$k];
				if (!$fldK['tsum']) continue;
				$fieldName=$fldK['fieldname'];
				$value=0;
				if ($fldK['tsum']=='count') {
					if ($rec[$fieldName]) $value=1;
				}
				else {
					$value=$rec[$fieldName];
					if (!$value) $value=0;
				}
				$this->total['field_'.$fieldName]+=$value;
			}
		}
	}
/** Опредилить старший из изменившихся итогов
 *
 * @param	Array	$rec строка отчета
 * @return	num	колонка, соответствующая изменившимся итогам, если итоги не нужны, то -1
 */
	protected function generateItogTestLevel($rec) {
		$result=-1;
		$fields=&$this->fields;
		$itogCount=$this->itogCount;
		for ($i=0; $i<$itogCount; $i++) {
			$fld=&$fields[$i];
			$itog=&$fld['itog'];
			if ($fld['tgroup.field']) {
				$fieldName=$fld['tgroup.field'];
				if ($itog['valueid']!=$rec[$fieldName]) {
					$result=$i;
					break;
				}
			}
			else {
				$fieldName=$fld['fieldname'];
				if ($itog['value']!=$rec[$fieldName]) {
					$result=$i;
					break;
				}
			}
		}
		return $result;
	}
/** Построить строку заголовков в таблице отчета
 *
 * @param	num	$level Колонка, соответствующая итогам
 * @return	Array	результирующие строки заголовка
 */
	protected function generateTitle($level, $rec) {
		$errorName='Ошибка при обращении к RepGenerator::generateTitle';
		$result=Array();
		$itogCount=$this->itogCount;
		if ($level<0) throw new Exception("{$errorName}, level<0");
		if ($level>=$itogCount) throw new Exception("{$errorName}, level>=itogCount");

		$fld=&$this->fields[$level];
		if (isset($fld['tgroup.title']) && !$fld['tgroup.title']) return $result;
		$fieldName=$fld['fieldname'];
		$res=Array();
		$res[$fieldName]=$rec[$fieldName];
		$res['#mode']=Array(
			'mode'=>'title',
			'level'=>$level,
			'fieldname'=>$fieldName
		);
		$result[]=$res;
		return $result;
	}
/** Построить строку итогов в таблице отчета
 *
 * @param	num	$level Колонка, соответствующая итогам
 * @return	Array	результирующие строки итога
 */
	protected function generateItog($level) {
		$errorName='Ошибка при обращении к RepGenerator::generateItog';
		$result=Array();
		$itogCount=$this->itogCount;
		if ($level<0) throw new Exception("{$errorName}, level<0");
		if ($level>=$itogCount) throw new Exception("{$errorName} level>=itogCount");

		$fldItog=&$this->fields[$level];
		$fieldName=$fldItog['fieldname'];
		$itog=&$fldItog['itog'];
		if ($fldItog['tgroup.itog']) {
			$res=Array();
			$res['#mode']=Array(
				'mode'=>'itog',
				'level'=>$level,
				'fieldname'=>$fieldName
			);
			if ($fldItog['tgroup.itog']==1) {
				$value='Итого по '.$itog['value'];
			}
			else {
				$value=$fldItog['tgroup.itog'];
			}
			$res[$fieldName]=$value;
			foreach($this->fields as &$fld) {
				if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
				if (!$fld['tsum']) continue;
				$fieldName=$fld['fieldname'];
				$value=$itog['field_'.$fieldName];
				$res[$fieldName]=$this->getValueField($value,$fieldName,'itog');
			}
			$result[]=$res;
		}
		for ($i=$level; $i<$itogCount; $i++) {
			$fld=&$this->fields[$i];
			$itog=&$fld['itog'];
			foreach($this->fields as &$fldK) {
				if (!$fldK['tsum']) continue;
				$itog['field_'.$fldK['fieldname']]=0;
			}
		}
		return $result;
	}
/** Построить строку общих итогов в таблице отчета
 *
 * @return	string	HTML текст строки таблицы
 */
	protected function generateTotal() {
		$result=Array();
		if (!$this->isTotal) return $result;
		$res=Array();
		$res['#mode']=Array(
			'mode'=>'total'
		);
		foreach($this->fields as &$fld) {
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			if (!$fld['tsum']) continue;
			$fieldName=$fld['fieldname'];
			$value=$this->total['field_'.$fieldName];
			$res[$fieldName]=$this->getValueField($value,$fieldName,'itog');
		}
		$result[]=$res;
		return $result;
	}
/** Построить очередную строку отчета
 *
 * @param	Array	$rec строка отчета
 * @param	boolean	$isFirstRec признак первой строки
 * @return	Array	результирующие строки таблицы со строками подитогов в виде массива
 */
	protected function generateRows($rec, $isFirstRec) {
		$result=Array();
		$itogCount=$this->itogCount;
		if ($isFirstRec) {
			for($i=0; $i<$itogCount; $i++) {
				$lst=$this->generateTitle($i, $rec);
				foreach($lst as $res) $result[]=$res;
			}
		}
		else {
			$level=$this->generateItogTestLevel($rec);
			if ($level>-1) {
				if ($itogCount>0) {
					for ($i=$itogCount-1; $i>=$level; $i--) {
						$lst=$this->generateItog($i);
						foreach($lst as $res) $result[]=$res;
					}
				}
				for ($i=$level; $i<$itogCount; $i++) {
					$lst=$this->generateTitle($i, $rec);
					foreach($lst as $res) $result[]=$res;
				}
			}
		}
		if (!isset($rec['row_visible']) || $rec['row_visible']) {
			$res=Array();
			$res['#mode']=Array(
				'mode'=>'row'
			);
			foreach($this->fields as $fld) {
				$fieldName=$fld['fieldname'];
				$res[$fieldName]=$this->getValueField($rec[$fieldName], $fieldName);
			}
			$result[]=$res;
		}
		$this->generateItogAdd($rec);
		return $result;
	}

	protected function getValueType($value='', $type='string', $dec=0) {
		$result=$value;
		if ($type=='num') {
			if ($value==null) $value=0;
			if (!$value) $value=0;
			if ($dec>0) {
				$result=number_format($value, $dec, '.', '');
			}
			else {
				$result=intval($value).'';
			}
		}
		if ($type=='date') {
			if (is_a($value,'DateTime')) {
				$value=$value->format('Y-m-d');
			}
			else {
				if (!testDate($value)) $value='';
				$result=substr($value,0,10);
			}
		}
		return trim($result);
	}
	protected function getValueField($value='', $fieldName='', $mode='') {
		$type='string';
		$dec=0;
		$fld=$this->fieldsByName[$fieldName];
		if ($fld) {
			if ($mode=='itog' && $fld['tsum']) {
				$type='num';
				$dec=0;
				if ($fld['tsum']!='count') {
					if ($type=='num' && $fld['dec']) $dec=$fld['dec'];
				}
			}
			else {
				if ($fld['type']) $type=$fld['type'];
				if ($type=='num' && $fld['dec']) $dec=$fld['dec'];
			}
		}
		$result=$this->getValueType($value,$type,$dec);
		return $result;
	}
/** Построить таблицу отчета
 *
 * @return	Array	результирующая таблица с подитогами в виде массива
 */
	public function generate() {
		$this->itogInit();
		$itogCount=$this->itogCount;
		$result=Array();
		$isFirst=true;
		foreach($this->rows as &$rec) {
			$lst=$this->generateRows($rec, $isFirst);
			foreach($lst as $res) $result[]=$res;
			$isFirst=false;
		}
		for ($i=$itogCount-1; $i>=0; $i--) {
			$lst=$this->generateItog($i);
			foreach($lst as $res) $result[]=$res;
		}
		if ($this->isTotal) {
			$lst=$this->generateTotal();
			foreach($lst as $res) $result[]=$res;
		}
		return $result;
	}

	public function getHtmlCSS() {
		$result=<<<HTML
<style type="text/css">
.params-report {
	border-left-width: 4px;
	border-left-style: solid;
	border-left-color: #888;
	background-color: #f9f9f9;
	padding-left: 18px !important;
	padding: 8px;
	font-size: 100%;
}
.params-report .params-right {
	text-align: right;
}
.params-report .params-bold {
	font-weight: bold;
}
.params-report .separator {
	height: 15px;
}

.reptable {
	margin-top: 20px;
}
.reptable th{
	text-align: center;
	font-weight: bold;
	background-color: #f5f5f5;
	overflow: hidden;
}
.reptable th div{
	margin: auto;
}
.reptable td {
}
.reptable td.delimiter {
	border-left-width:0px;
	border-right-width:0px;
	width:25px;
}

.reptable td.first {
	border-left-width:0px;
}
.reptable td.align-right {
	text-align: right;
	padding-right: 10px;
}
.reptable td.align-center {
	text-align: center;
}

.reptable tr.title{
	background-color: #fAfAfA;
	font-weight: bold;
	cursor: pointer;
}
.reptable tr.itog {
	vertical-align: top;
	background-color: #fAfAfA;
	font-weight: bold;
	cursor: pointer;
}
.reptable .total {
	vertical-align: top;
	background-color: #f5f5f5;
	font-weight: bold;
}
</style>

<script>
function onRepTableDblClick(domNode) {
	var lstNodes=[];
	var type='';
	if (domNode.dataset.type=='itog') {
		var level=domNode.dataset.level;
		for(var node=domNode.previousSibling; node; node=node.previousSibling) {
			if (node.tagName!='TR') continue;
			if (node.dataset && node.dataset.level==level && node.dataset.type=='title') {
				domNode=node;
				break;
			}
		}
	}
	if (!domNode.dataset.type=='title') return false;
	
	var level=domNode.dataset.level;
	for(var node=domNode.nextSibling; node; node=node.nextSibling) {
		if (node.tagName!='TR') continue;
		if (node.dataset && node.dataset.level==level) break;
		lstNodes.push(node);
	}
	if (!lstNodes.length) return false;
	
	var mode=domNode.dataset.mode;
	if (!mode) mode=0;
	mode++;
	if (mode>=3) mode=0;
	if (mode==2) {
		var isTitle=false;
		for(var i=0; i<lstNodes.length; i++) {
			var node=lstNodes[i];
			var t=node.dataset.type;
			if (t=='title' || t=='itog') {
				isTitle=true;
				break;
			}
		}
		if (!isTitle) mode=0;
	}
	domNode.dataset.mode=mode;
	
	if (mode==0) {
		for(var i=0; i<lstNodes.length; i++) {
			var node=lstNodes[i];
			removeClass(node,'hidden');
		}
	}
	if (mode==1) {
		for(var i=0; i<lstNodes.length; i++) {
			var node=lstNodes[i];
			var t=node.dataset.type;
			if (t=='title' || t=='itog') {
				if (parseInt(node.dataset.level)>(parseInt(level)+1)) {
					addClass(node, 'hidden');
				}
				else {
					removeClass(node, 'hidden');
				}
			}
			else {
				addClass(node,'hidden');
			}
		}
	}
	if (mode==2) {
		for(var i=0; i<lstNodes.length; i++) {
			var node=lstNodes[i];
			addClass(node,'hidden');
		}
	}
}

function addClass(node, className) {
	if (!node) return false;
	if ((' '+node.className+' ').indexOf(' '+className+' ')<0) node.className+=' '+className;
}
function removeClass(node, className) {
	if (!node) return false;
	if ((' '+node.className+' ').indexOf(' '+className+' ')>=0) {
		var lst=(' '+node.className+' ').split(' ');
		var str='';
		for(var i=0; i<lst.length; i++) {
			if (lst[i]=='') continue;
			if (lst[i]==className) continue;
			str+=' '+lst[i];
		}
		node.className=str;
	}
}

</script>
HTML;
		return $result;
	}
	public function getHtmlParams($params=Array()) {
		$result='';
		foreach($params as $name=>$value) {
			$htmlValue=str2Html($value);
			if ($name && is_string($name)) {
				$htmlValue=str2Html($name).': '.$htmlValue;
			}
			if ($result) $result.="\n";
			$result.=<<<HTML
<div>{$htmlValue}</div>
HTML;
		}
		if ($result) {
			$result=<<<HTML
<div class="params-report">
{$result}
</div>
HTML;
		}
		return $result;
	}
	public function getHtmlTable($table=Array()) {
		$result=<<<HTML
<table border="1" class="reptable table-hover table-condensed {$this->tableClassName}">
HTML;
		$result.="\n".$this->getHtmlTableHeader();
		foreach($table as &$row) {
			$result.="\n".$this->getHtmlTableRow($row);
		}
		$result.="\n".<<<HTML
</table>
HTML;
		return $result;
	}
	protected function getHtmlTableHeader() {
/*
		$isSubCaption=false;
		foreach($this->fields as &$fld) {
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			if ($fld['subcaption']) {
				$isSubCaption=true;
				break;
			}
		}
		if ($isSubCaption) {
			$result=$this->getHtmlTableHeaderWithSubCaption();
		}
		else {
			$result=$this->getHtmlTableHeaderWithoutSubCaption();
		}
*/
		$result=$this->getHtmlTableHeaderWithoutSubCaption();
		return $result;
	}
	protected function getHtmlTableHeaderWithSubCaption() {
	}
	protected function getHtmlTableHeaderWithoutSubCaption() {
		$result=<<<HTML
<tr>
HTML;
		$colSpanCount=1;
		foreach($this->fields as &$fld) {
			if ($fld['tgroup'] && !$fld['tgroup.column']) {
				$colSpanCount++;
			}
			else {
				$caption=str2Html($fld['caption']);
				if (!$caption) $caption=str2Html($fld['fieldname']);
				if ($fld['width']) {
					$caption=<<<HTML
<div style="width:{$fld['width']}">{$caption}</div>
HTML;
				}
				$result.="\n".<<<HTML
	<th colspan="{$colSpanCount}">{$caption}</th>
HTML;
				$colSpanCount=1;
			}
		}
		$result.="\n".<<<HTML
</tr>
HTML;
		return $result;
	}
	protected function getHtmlTableRow($row) {
		$result='';
		if (!$row) return $result;
		$mode=$row['#mode'];
		if (is_array($mode)) {
			if ($mode['mode']=='title') return $this->getHtmlTableRowTitle($row);
			if ($mode['mode']=='itog') return $this->getHtmlTableRowItog($row);
			if ($mode['mode']=='total') return $this->getHtmlTableRowTotal($row);
		}
		$firstTdClassName='';
		$result=<<<HTML
<tr>
HTML;
		foreach($this->fields as &$fld) {
			if ($fld['tgroup'] && !$fld['tgroup.column']) {
				$result.="\n".<<<HTML
	<td class="delimiter"></td>
HTML;
				$firstTdClassName="first";
			}
			else {
				$class='';
				$fieldName=$fld['fieldname'];
				$htmlValue=str2Html($row[$fieldName]);
				if ($fld['type']=='date') {
					$htmlValue=date2Html($row[$fieldName]);
					$class='align-center';
				}
				else if ($fld['type']=='num') {
					$class='align-right';
				}
				$result.="\n".<<<HTML
	<td class="{$firstTdClassName} {$class}">{$htmlValue}</td>
HTML;
			}
		}
		$result.="\n".<<<HTML
</tr>
HTML;
		return $result;
	}
	protected function getHtmlTableRowTitle($row) {
		$result='';
		if (!$row) return $result;
		$mode=$row['#mode'];
		if (!is_array($mode)) return $result;
		$level=$mode['level'];
		$fieldName=$mode['fieldname'];
		$colSpan=$this->columnCount-$level;

		$firstTdClassName='';
		$result=<<<HTML
<tr class="title" data-type="title" data-level="{$level}" ondblclick="onRepTableDblClick(this)" onselectstart="return false" onmousedown="return false">
HTML;
		for($i=0; $i<$level; $i++) {
			$result.="\n".<<<HTML
	<td class="delimiter"></td>
HTML;
			$firstTdClassName="first";
		}
		$htmlValue=str2Html($row[$fieldName]);
		$result.="\n".<<<HTML
	<td class="{$firstTdClassName}" colspan="{$colSpan}">{$htmlValue}</td>
</tr>
HTML;
		return $result;
	}
	protected function getHtmlTableRowItog($row) {
		$result='';
		if (!$row) return $result;
		$mode=$row['#mode'];
		if (!is_array($mode)) return $result;
		$level=$mode['level'];
		$fieldName=$mode['fieldname'];
		$colSpan=$this->columnTSumPosition-$level;

		$firstTdClassName='';
		$result=<<<HTML
<tr class="itog" data-type="itog" data-level="{$level}" ondblclick="onRepTableDblClick(this)" onselectstart="return false" onmousedown="return false">
HTML;
		for($i=0; $i<$level; $i++) {
			$result.="\n".<<<HTML
	<td class="delimiter"></td>
HTML;
			$firstTdClassName="first";
		}
		if ($colSpan>0) {
			$htmlValue=str2Html($row[$fieldName]);
			$result.="\n".<<<HTML
	<td colspan="{$colSpan}" class="{$firstTdClassName}">{$htmlValue}</td>
HTML;
			$firstTdClassName='';
		}
		for($i=$this->columnTSumPosition; $i<$this->columnCount; $i++) {
			$fld=&$this->fields[$i];
			$fieldName=$fld['fieldname'];
			$htmlValue=str2Html($row[$fieldName]);
			
			$class='align-right';
			//if ($fld['type']=='num') $class='align-right';
			
			$result.="\n".<<<HTML
	<td class="{$firstTdClassName} {$class}">{$htmlValue}</td>
HTML;
			$firstTdClassName='';
		}

		$result.="\n".<<<HTML
</tr>
HTML;
		return $result;
	}
	protected function getHtmlTableRowTotal($row) {
		$result='';
		if (!$row) return $result;
		$mode=$row['#mode'];
		if (!is_array($mode)) return $result;
		$colSpan=$this->columnTSumPosition;

		$firstTdClassName='';
		$result=<<<HTML
<tr class="total">
HTML;
		if ($colSpan>0) {
			$result.="\n".<<<HTML
	<td colspan="{$colSpan}" class="{$firstTdClassName}">Всего</td>
HTML;
			$firstTdClassName='';
		}
		for($i=$this->columnTSumPosition; $i<$this->columnCount; $i++) {
			$fld=&$this->fields[$i];
			$fieldName=$fld['fieldname'];
			$htmlValue=str2Html($row[$fieldName]);
			
			$class='align-center';
			if ($fld['type']=='num') $class='align-right';
			
			$result.="\n".<<<HTML
	<td class="{$firstTdClassName} {$class}">{$htmlValue}</td>
HTML;
			$firstTdClassName='';
		}

		$result.="\n".<<<HTML
</tr>
HTML;
		return $result;
	}
}