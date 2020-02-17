<?php
/**
 * @file
 * G740Server, генератор отчетов в виде таблиц с подитогами
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс, генератор отчетов в виде таблиц с подитогами
 *
 * Создание:
 *	$objReport=new ReportBuilder($params)
 *		$params['fields']	- описание колонок отчета
 *		$params['rows']		- массив строк, возвращаемых запросом
 *		$params['sql']		- SQL запрос
 *		$params['total']	- 1|0 нужен подсчет итогов
 *		$params['format']	- 'html'|'xls' формат
 * Описание колонок
 *		$field['caption']			- заголовок
 *		$field['subcaption']		- заголовок
 *		$field['fieldname']			- имя поля
 *		$field['type']				- str, num, date, html
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
 */
class ReportBuilder {
/// список полей
	protected $fields=null;
	
/// формат html или xls
	protected $format='html';
/// класс таблицы
	protected $tableClassName='table table-bordered';
/// наличие итогов
	protected $isTotal=true;
	
	protected $isWord=false;
	
	
/// количество колонок, по которым производится группировка
	protected $itogCount=0;
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
	function __construct($para)	{
		if (!$para) throw new Exception('Не задан para');
		if (!$para['fields']) throw new Exception('Не задан para[fields]');
		$this->SetFields($para['fields']);
		if (isset($para['total'])) $this->isTotal=$para['total']?true:false;
		if ($para['format']=='xls') $this->format='xls';
		if ($para['format']=='word') $this->isWord=true;
		
		if (!($para['rows'] || $para['sql'])) {
			$rows=Array();
			$rows[]=Array();
			$para['rows']=$rows;
		}
		if ($para['rows']) {
			$this->rows=$para['rows'];
		}
		else {
			$this->rows=Array();
			$pdoDB=getPDO();
			$q=$pdoDB->pdo($para['sql']);
			while ($rec=$pdoDB->pdoFetch($q)) {
				$this->rows[]=$rec;
			}
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
	public function SetFields($fields) {
		$this->fields=Array();
		$count=count($fields);
//	Сначала все колонки, по которым группировка
		for ($i=0; $i<$count; $i++) {
			$fld=$fields[$i];
			if (!$fld['tgroup']) continue;
			unset($fld['tsum']);
			$this->fields[]=$fld;
		}
//	Потом колонки, по которым нет группировки и итогов
		for ($i=0; $i<$count; $i++) {
			$fld=$fields[$i];
			if ($fld['tgroup']) continue;
			if ($fld['tsum']) continue;
			$this->fields[]=$fld;
		}
		
//	Потом колонки итогов
		$tsumCount=0;
		for ($i=0; $i<$count; $i++) {
			$fld=$fields[$i];
			if ($fld['tgroup']) continue;
			if (!$fld['tsum']) continue;
			$this->fields[]=$fld;
			$tsumCount++;
		}
		if ($tsumCount==0) $this->isTotal=false;
		
		$this->ItogInit();
	}
/** Построить строки заголовка таблицы, если есть общие подзаголовки, объединяющие несколько колонок
 *
 * @return	string	HTML текст заголовка таблицы
 */
	private function GetTrCaptionIfSubCaption() {
		
		$fields=Array();
		foreach($this->fields as &$fld) {
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			$fields[]=$fld;
		}
		unset($fld);
		
		$count=count($fields);
		$isChanged=true;
		while ($isChanged) {
			$isChanged=false;
			for ($i=($count-1); $i>=0; $i--) {
				$fld=&$fields[$i];
				if (!$fld['subcaption']) continue;
				if (!$fld['subcaption-count']) {
					$fld['subcaption-count']=1;
					$isChanged=true;
				}
				if ($i>=($count-1)) continue;
				$fldPred=&$fields[($i+1)];
				if ($fld['subcaption']==$fldPred['subcaption']) {
					if ($fld['subcaption-count']!=($fldPred['subcaption-count']+1)) {
						$fld['subcaption-count']=$fldPred['subcaption-count']+1;
						$fld['issubcaption']=true;
						$fldPred['issubcaption']=true;
						$isChanged=true;
					}
				}
			}
		}
		unset($fld);
		unset($fldPred);
		
		$result="\n".<<<HTML
<tr>
HTML;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['issubcaption']) {
				$caption=str2Html($fld['subcaption'], $this->format);
				$result.="\n".<<<HTML
	<th colspan="{$fld['subcaption-count']}">{$caption}</th>
HTML;
				$i+=$fld['subcaption-count']-1;
			}
			else {
				$caption=str2Html($fld['caption'], $this->format);
				if (!$caption) $caption=str2Html($fld['fieldname'], $this->format);
				if ($fld['subcaption']) $caption=str2Html($fld['subcaption'], $this->format).' '.$caption;
				$style='';
				if ($fld['width']) $style.="width:".$fld['width'];
				$result.="\n".<<<HTML
	<th style="{$style}" rowspan="2">{$caption}</th>
HTML;
			}
		}
		unset($fld);
		$result.="\n".<<<HTML
</tr>
<tr>
HTML;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if (!$fld['issubcaption']) continue;
			$caption=str2Html($fld['caption'], $this->format);
			if (!$caption) $caption=str2Html($fld['fieldname'], $this->format);
			$style='';
			if ($fld['width']) $style.="width:".$fld['width'];
			$result.="\n".<<<HTML
	<th style="{$style}">{$caption}</th>
HTML;
		}
		unset($fld);
		$result.="\n".<<<HTML
</tr>
HTML;
		return $result;
	}
/** Построить строки заголовка таблицы, если нет общих подзаголовков
 *
 * @return	string	HTML текст заголовка таблицы
 */
	private function GetTrCaptionIfNotSubCaption() {
		$result='';
		$fields=&$this->fields;
		$count=count($this->fields);
		$result.="\n".<<<HTML
<tr>
HTML;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			$caption=str2Html($fld['caption'], $this->format);
			if (!$caption) $caption=str2Html($fld['fieldname'], $this->format);
			
			if ($this->isWord) {
				if ($fld['width']) {
					$result.="\n".<<<HTML
<th width=80 style="width:{$fld['width']}">{$caption}</th>
HTML;
				}
				else {
					$result.="\n".<<<HTML
<th>{$caption}</th>
HTML;
				}
			}
			else {
				if ($fld['width']) {
					$caption=<<<HTML
<div style="width:{$fld['width']}">{$caption}</div>
HTML;
				}
				$result.="\n".<<<HTML
<th>{$caption}</th>
HTML;
			}
		}
		$result.="\n".<<<HTML
</tr>
HTML;
		return $result;
	}
/** Построить строки заголовка таблицы
 * @return	string	HTML текст заголовка таблицы
 */
	protected function GetTrCaption() {
		$fields=&$this->fields;
		$count=count($this->fields);
		$isSubCaption=false;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			if ($fld['subcaption']) {
				$isSubCaption=true;
				break;
			}
		}
		if ($isSubCaption) {
			$result=$this->GetTrCaptionIfSubCaption();
		}
		else {
			$result=$this->GetTrCaptionIfNotSubCaption();
		}
		return $result;
	}
	
/** Построить ячейку таблицы
 *
 * @param	string	$value значение ячейки
 * @param	string	$type тип str, num, date
 * @param	num	$dec количество знаков после запятой
 * @return	string	HTML текст ячейки таблицы
 */
	protected function GetTd($value, $type='str', $dec=0) {
		$result='';
		if ($type=='html') {
			$htmlValue=$value;
		}
		else {
			$htmlValue=str2Html($value, $this->format);
		}
		if ($type!='date' && $type!='num') $type='str';
		if ($type=='date') $htmlValue=date2Html($value);
		if ($type=='num') {
			if (!$dec) $dec=0;
			$htmlValue=num2Str($value, $dec);
		}
		if ($htmlValue=='') $htmlValue='&nbsp;';
		$class='type-'.$type;
		if ($this->format=='xls') {
			if ($type=='str') $class='xl-text';
			if ($type=='num') {
				$class='xl-num';
				if ($dec==1 || $dec==2 || $dec==3) $class.=$dec;
			}
		}
		$result.="\n".<<<HTML
		<td class="{$class}">{$htmlValue}</td>
HTML;
		return $result;
	}
/** Инициализировать структуры данных, предназначенные для хранения промежуточных итогов
 */
	private function ItogInit() {
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=0;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if (!$fld['tgroup']) break;
			$itog=Array();
			$itog['value']=null;
			if ($fld['tgroup.field']) {
				$itog['valueid']=null;
			}

			$itog['fieldname']=$fld['fieldname'];
			for($k=0; $k<$count; $k++) {
				$fldK=&$fields[$k];
				if (!$fldK['tsum']) continue;
				$itog['field_'.$fldK['fieldname']]=0;
			}
			$fld['itog']=$itog;
			$itogCount++;
		}
		
		$this->total=Array();
		for($k=0; $k<$count; $k++) {
			$fldK=&$fields[$k];
			if (!$fldK['tsum']) continue;
			$this->total['field_'.$fldK['fieldname']]=0;
		}

		$this->itogCount=$itogCount;
	}
/** Добавить значения очередной строки к промежуточным итогам
 *
 * @param	Array	$rec строка отчета
 */
	private function ItogAdd($rec) {
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
					if ($fldK['tsum']=='count') {
						$itog['field_'.$fieldName]+=1;
					}
					else {
						$value=$rec[$fieldName];
						if (!$value) $value=0;
						$itog['field_'.$fieldName]+=$value;
					}
				}
			}
		}
		
		if (!isset($rec['row_visible']) || $rec['row_visible']) {
			for($k=$itogCount; $k<$count; $k++) {
				$fldK=&$fields[$k];
				if (!$fldK['tsum']) continue;
				$fieldName=$fldK['fieldname'];
				if ($fldK['tsum']=='count') {
					$this->total['field_'.$fieldName]+=1;
				}
				else {
					$value=$rec[$fieldName];
					if (!$value) $value=0;
					$this->total['field_'.$fieldName]+=$value;
				}
			}
		}
	}
/** Опредилить старший из изменившихся итогов
 *
 * @param	Array	$rec строка отчета
 * @return	num	колонка, соответствующая изменившимся итогам, если итоги не нужны, то -1
 */
	private function ItogTestLevel($rec) {
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
 * @return	string	HTML текст строки таблицы
 */
	private function GetTitleTr($level, $rec) {
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=$this->itogCount;
		if ($level<0) throw new Exception('GetTitleTr level<0');
		if ($level>=$itogCount) throw new Exception('GetTitleTr level>=itogCount');

		$fld=&$fields[$level];
		if (isset($fld['tgroup.title']) && !$fld['tgroup.title']) return '';
		$itog=&$fld['itog'];
		$fieldName=$fld['fieldname'];
		$htmlValue=str2Html($rec[$fieldName], $this->format);
		$colSpan=0;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			$colSpan++;
		}
		$result="\n".<<<HTML
<tr class="title">
	<td colspan="{$colSpan}"><div class="level{$level}">{$htmlValue}</div></td>
</tr>
HTML;
		return $result;
	}

/** Построить строку итогов в таблице отчета
 *
 * @param	num	$level Колонка, соответствующая итогам
 * @return	string	HTML текст строки таблицы
 */
	private function GetItogTr($level) {
		$result='';
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=$this->itogCount;
		if ($level<0) throw new Exception('GetItogTr level<0');
		if ($level>=$itogCount) throw new Exception('GetItogTr level>=itogCount');

		$fld=&$fields[$level];
		$itog=&$fld['itog'];
		//if (!isset($fld['tgroup.itog']) || $fld['tgroup.itog']) {
		if ($fld['tgroup.itog']) {
			$result.="\n".<<<HTML
	<tr class="itog">
HTML;
			if ($fld['tgroup.itog']==1) {
				$htmlValue=str2Html('Итого по '.$itog['value'], $this->format);
			}
			else {
				$htmlValue=str2Html($fld['tgroup.itog'], $this->format);
			}
			
			$colSpan=0;
			for($i=0; $i<$count; $i++) {
				$fld=&$fields[$i];
				if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
				if ($fld['tsum']) break;
				$colSpan++;
			}
			$result.="\n".<<<HTML
	<td colspan="{$colSpan}"><b>{$htmlValue}</b></td>
HTML;

			for($i=0; $i<$count; $i++) {
				$fld=&$fields[$i];
				if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
				if (!$fld['tsum']) continue;
				$fieldName=$fld['fieldname'];
				$value=$itog['field_'.$fieldName];
				$t='num';
				$dec=$fld['dec'];
				if ($fld['tsum']=='count') $dec=0;
				$result.=$this->GetTd($value, $t, $dec);
			}
			
			$result.="\n".<<<HTML
	</tr>
HTML;
		}
		for ($i=$level; $i<$itogCount; $i++) {
			$fld=&$fields[$i];
			$itog=&$fld['itog'];
			for($k=0; $k<$count; $k++) {
				$fldK=&$fields[$k];
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
	protected function GetTotalTr() {
		$result='';
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=$this->itogCount;

		$result.="\n".<<<HTML
	<tr class="total">
HTML;
		$htmlValue=str2Html('Всего', $this->format);

		$colSpan=0;
		for($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			if ($fld['tsum']) break;
			$colSpan++;
		}
		$result.="\n".<<<HTML
	<td colspan="{$colSpan}"><b>{$htmlValue}</b></td>
HTML;

		for($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
			if (!$fld['tsum']) continue;
			$fieldName=$fld['fieldname'];
			$value=$this->total['field_'.$fieldName];
			$t='num';
			$dec=$fld['dec'];
			if ($fld['tsum']=='count') $dec=0;
			$result.=$this->GetTd($value, $t, $dec);
		}

		$result.="\n".<<<HTML
	</tr>
HTML;
		return $result;
	}

/** Построить строки итогов в таблице отчета
 *
 * @param	num	$level Колонка, соответствующая итогам, начиная с которых итоги нужны
 * @return	string	HTML текст строк таблицы
 */
	protected function GetItogTrAllLevel($level) {
		$result='';
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=$this->itogCount;
		if ($level<0) throw new Exception('GetItogTrAllLevel level<0');
		if ($itogCount>0) {
			for ($i=$itogCount-1; $i>=$level; $i--) {
				$result.=$this->GetItogTr($i);
			}
		}
		return $result;
	}
/** Построить очередную строку отчета
 *
 * @param	Array	$rec строка отчета
 * @param	boolean	$isFirstRec признак первой строки
 * @return	string	HTML текст строк таблицы
 */
	protected function GetRowTr($rec, $isFirstRec) {
		$result='';
		$fields=&$this->fields;
		$count=count($this->fields);
		$itogCount=$this->itogCount;
		
		if ($isFirstRec) {
			for($i=0; $i<$itogCount; $i++) {
				$result.=$this->GetTitleTr($i, $rec);
			}
		}
		else {
			$level=$this->ItogTestLevel($rec);
			if ($level>-1) {
				$result.=$this->GetItogTrAllLevel($level);
				for ($i=$level; $i<$itogCount; $i++) {
					$result.=$this->GetTitleTr($i, $rec);
				}
			}
		}
		if (!isset($rec['row_visible']) || $rec['row_visible']) {
			$result.="\n".<<<HTML
	<tr>
HTML;
			for ($i=0; $i<$count; $i++) {
				$fld=&$fields[$i];
				if ($fld['tgroup'] && !$fld['tgroup.column']) continue;
				$fieldName=$fld['fieldname'];
				$result.=$this->GetTd($rec[$fieldName], $fld['type'], $fld['dec']);
			}
			$result.="\n".<<<HTML
	</tr>
HTML;
		}
		$this->ItogAdd($rec);
		return $result;
	}
/** Построить таблицу отчета
 *
 * @return	string	HTML текст таблицы
 */
	public function GetReportTable() {
		$result='';
		if ($this->isWord) {
			$result.="\n".<<<HTML
<table border="1" class="bordered">
HTML;
		}
		else {
			$result.="\n".<<<HTML
<table border="1" class="table-report table-hover table-condensed {$this->tableClassName}">
HTML;
		}
		$result.=$this->GetTrCaption();
		
		$isFirst=true;
		foreach($this->rows as &$rec) {
			$result.=$this->GetRowTr($rec, $isFirst);
			$isFirst=false;
		}
		
		$result.=$this->GetItogTrAllLevel(0);
		if ($this->isTotal) {
			$result.=$this->GetTotalTr();
		}
		$result.="\n".<<<HTML
</table>
HTML;
		$this->ItogInit();
		return $result;
	}
/** Построить отчет
 *
 * @return	string	HTML текст отчета
 */
	public function Get() {
		$result='';
		$result.=$this->GetReportTable();
		return $result;
	}
}