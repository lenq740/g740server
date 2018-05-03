<?php
/**
 * @file
 * Генератор отчетов в виде таблиц с подитогами
 */

/** Класс, генератор отчетов в виде таблиц с подитогами
 *
 * Создание:
 *	$objReport=new ReportBuilder($params)
 *		$params['fields']	- описание колонок отчета
 *		$params['rows']		- массив строк, возвращаемых запросом
 *		$para['sql']		- SQL запрос
 * Описание колонок
 *		$field['caption']		- заголовок
 *		$field['subcaption']	- заголовок
 *		$field['fieldname']		- имя поля
 *		$field['type']			- str, num, date
 *		$field['dec']			- количество десятичных знаков после запятой
 *		$field['width']			- ширина колонки
 *		$field['tgroup']		- группировать по коллонке (итог на изменение значения)
 *		$field['tsum']			- 1, sum, count
 * Публичные методы:
 *	$objReport->SetFields($fields)	- задать новые описания fields
 *	$objReport->Get();				- Отчет
 */
class ReportBuilder {

/** массив описаний колонок отчета
 *
 * - $field['caption'] - заголовок
 * - $field['subcaption'] - заголовок
 * - $field['fieldname'] - имя поля
 * - $field['type'] - str, num, date
 * - $field['dec'] - количество десятичных знаков после запятой
 * - $field['width'] - ширина колонки
 * - $field['tgroup'] - группировать по коллонке (итог на изменение значения)
 * - $field['tsum'] - 1, sum, count
 */
	protected $fields=null;
	
/// количество колонок отчета
	protected $fieldsCount=0;
/// количество колонок, по которым производится группировка
	protected $itogCount=0;
/// позиция первой колонки, по которой производится суммирование
	protected $indexTSum=0;
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
		
		if (!($para['rows'] || $para['sql'])) {
			$rows=Array();
			$rows[]=Array();
			$para['rows']=$rows;
		}
		if ($para['rows']) {
			$this->rows=$para['rows'];
		}
		else {
//			if (!$para['sql']) throw new Exception('Не задан para[rows] и para[sql]');
			$this->rows=Array();
			@$q=mysql_query($para['sql']);
			if (!$q) throw new Exception('Ошибка в SQL запросе '.mysql_error());
			while ($rec=mysql_fetch_assoc($q)) {
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
			$rec=$fields[$i];
			if (!$rec['tgroup']) continue;
			unset($rec['tsum']);
			$this->fields[]=$rec;
		}
//	Потом колонки, по которым нет группировки и итогов
		for ($i=0; $i<$count; $i++) {
			$rec=$fields[$i];
			if ($rec['tgroup']) continue;
			if ($rec['tsum']) continue;
			$this->fields[]=$rec;
		}
//	Потом колонки итогов
		for ($i=0; $i<$count; $i++) {
			$rec=$fields[$i];
			if ($rec['tgroup']) continue;
			if (!$rec['tsum']) continue;
			$this->fields[]=$rec;
		}
		
		$this->fieldsCount=count($this->fields);
		$this->ItogInit();
	}
/** Построить строки заголовка таблицы, если есть общие подзаголовки, объединяющие несколько колонок
 *
 * @return	string	HTML текст заголовка таблицы
 */
	private function GetTrCaptionIfSubCaption() {
		$result='';
		$result.="\n".<<<HTML
	<tr class="caption">
HTML;
		$fields=&$this->fields;
		$count=$this->fieldsCount;
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
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['issubcaption']) {
				$caption=str2html($fld['subcaption']);
				$result.="\n".<<<HTML
		<td colspan="{$fld['subcaption-count']}">{$caption}</td>
HTML;
				$i+=$fld['subcaption-count']-1;
			}
			else {
				$caption=str2html($fld['caption']);
				if (!$caption) $caption=str2html($fld['fieldname']);
				if ($fld['subcaption']) $caption=str2html($fld['subcaption']).' '.$caption;
				$style='';
				if ($fld['width']) $style.="width:".$fld['width'];
				$result.="\n".<<<HTML
		<td style="{$style}" rowspan="2">{$caption}</td>
HTML;
			}
		}
		$result.="\n".<<<HTML
	</tr>
	<tr class="caption">
HTML;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if (!$fld['issubcaption']) continue;
			$caption=str2html($fld['caption']);
			if (!$caption) $caption=str2html($fld['fieldname']);
			$style='';
			if ($fld['width']) $style.="width:".$fld['width'];
			$result.="\n".<<<HTML
		<td style="{$style}">{$caption}</td>
HTML;
		}
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
		$count=$this->fieldsCount;
		$result.="\n".<<<HTML
	<tr class="caption">
HTML;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			$caption=str2html($fld['caption']);
			if (!$caption) $caption=str2html($fld['fieldname']);
			$style='';
			if ($fld['width']) $style.="width:".$fld['width'];
			$result.="\n".<<<HTML
		<td style="{$style}">{$caption}</td>
HTML;
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
		$count=$this->fieldsCount;
		$isSubCaption=false;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['subcaption']) {
				$isSubCaption=true;
				break;
			}
		}
		if ($isSubCaption) return $this->GetTrCaptionIfSubCaption();
		return $this->GetTrCaptionIfNotSubCaption();
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
		$htmlValue=str2html($value);
		if ($type!='date' && $type!='num') $type='str';
		if ($type=='date') $htmlValue=date2html($value);
		if ($type=='num') {
			if (!$dec) $dec=0;
			$htmlValue=number_format($value, $dec, '.', " ");
		}
		if ($htmlValue=='') $htmlValue='&nbsp;';
		$result.="\n".<<<HTML
		<td class="type-{$type}">{$htmlValue}</td>
HTML;
		return $result;
	}
/** Инициализировать структуры данных, предназначенные для хранения промежуточных итогов
 */
	private function ItogInit() {
		$fields=&$this->fields;
		$count=$this->fieldsCount;
		$itogCount=0;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			if (!$fld['tgroup']) break;
			$itog=Array();
			$itog['value']=null;
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
		
		$this->indexTSum=$count-1;
		for ($i=$itogCount; $i<$count; $i++) {
			$fld=&$fields[$i];
			if ($fld['tsum']) {
				$this->indexTSum=$i;
				break;
			}
		}
		$this->itogCount=$itogCount;
	}
/** Добавить значения очередной строки к промежуточным итогам
 *
 * @param	Array	$rec строка отчета
 */
	private function ItogAdd($rec) {
		$fields=&$this->fields;
		$count=$this->fieldsCount;
		$itogCount=$this->itogCount;
		for ($i=0; $i<$itogCount; $i++) {
			$fld=&$fields[$i];
			$itog=&$fld['itog'];
			$fieldName=$fld['fieldname'];
			$itog['value']=$rec[$fieldName];
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
			$fieldName=$fld['fieldname'];
			if ($itog['value']!=$rec[$fieldName]) {
				$result=$i;
				break;
			}
		}
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
		$count=$this->fieldsCount;
		$itogCount=$this->itogCount;
		if ($level<0) throw new Exception('GetItogTr level<0');
		if ($level>=$itogCount) throw new Exception('GetItogTr level>=itogCount');

		$result.="\n".<<<HTML
	<tr class="itog">
HTML;
		if ($level>=1) {
			$colSpan=$level;
			$result.="\n".'<td colspan="$colSpan">&nbsp;</td>';
		}
/*		
		for ($i=0; $i<$level; $i++) {
			$fld=&$fields[$i];
			$itog=&$fld['itog'];
			$result.=$this->GetTd($itog['value']);
		}
*/
		$fld=&$fields[$level];
		$itog=&$fld['itog'];
		$htmlValue=str2html('Итого по '.$itog['value']);
		$colSpan=$this->indexTSum-$level;
		$result.="\n"."<td colspan='{$colSpan}'><b>{$htmlValue}</b></td>";
		for ($i=$this->indexTSum; $i<$count; $i++) {
			$fld=&$fields[$i];
			$fieldName=$fld['fieldname'];
			$value='';
			$t=$fld['type'];
			$dec=$fld['dec'];
			if ($fld['tsum']) {
				$value=$itog['field_'.$fieldName];
				$t='num';
				if ($fld['tsum']=='count') $dec=0;
			}
			$result.=$this->GetTd($value, $t, $dec);
		}
		$result.="\n".<<<HTML
	</tr>
HTML;
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
		$count=$this->fieldsCount;
		$itogCount=$this->itogCount;

		$result.="\n".<<<HTML
	<tr class="total">
HTML;
		$htmlValue=str2html('Всего');
		$colSpan=$this->indexTSum;
		$result.="\n"."<td colspan='{$colSpan}'>{$htmlValue}</td>";
		for ($i=$this->indexTSum; $i<$count; $i++) {
			$fld=&$fields[$i];
			$fieldName=$fld['fieldname'];
			$value='';
			$t=$fld['type'];
			$dec=$fld['dec'];
			if ($fld['tsum']) {
				$value=$this->total['field_'.$fieldName];
				$t='num';
				if ($fld['tsum']=='count') $dec=0;
			}
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
		$count=$this->fieldsCount;
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
		$count=$this->fieldsCount;
		$itogCount=$this->itogCount;

		if (!$isFirstRec) {
			$level=$this->ItogTestLevel($rec);
			if ($level>-1) $result.=$this->GetItogTrAllLevel($level);
		}

		$result.="\n".<<<HTML
	<tr class="row">
HTML;
		for ($i=0; $i<$count; $i++) {
			$fld=&$fields[$i];
			$fieldName=$fld['fieldname'];
			$result.=$this->GetTd($rec[$fieldName], $fld['type'], $fld['dec']);
		}
		$result.="\n".<<<HTML
	</tr>
HTML;
		$this->ItogAdd($rec);
		return $result;
	}
/** Построить таблицу отчета
 *
 * @return	string	HTML текст таблицы
 */
	protected function GetReportTable() {
		$result='';
		$result.="\n".<<<HTML
<table border="1" class="table-report">
HTML;
		$result.=$this->GetTrCaption();
		
		$isFirst=true;
		$rowCount=count($this->rows);
		for ($i=0; $i<$rowCount; $i++) {
			$rec=&$this->rows[$i];
			$result.=$this->GetRowTr($rec, $isFirst);
			$isFirst=false;
		}
		$result.=$this->GetItogTrAllLevel(0);
		$result.=$this->GetTotalTr();
		$result.="\n".<<<HTML
</table>
HTML;
		$this->ItogInit();
		return $result;
	}
/** Построить каскадную таблицу стилей
 *
 * @return	string	HTML текст таблицы стилей
 */
	protected function GetReportCss() {
		$result="\n".<<<HTML
<style type="text/css">
.table-report {
	width: 99%;
	font-size: 8pt; 
	border-collapse: collapse;
	border-style: single;
	border-color: black;
	border-width: 1px;
}
.table-report td {
	border-style: single;
	border-color: black;
	border-width: 1px;
	padding: 2px;
}
.table-report .caption td{
	text-align: center;
	font-weight: bold;
	font-size: 9pt; 
	background-color: lightgray;
}
.table-report .type-str {
}
.table-report .type-num {
	text-align: right;
}
.table-report .itog {
	vertical-align: top;
	height: 30px;
	background-color: lightgray;
	font-weight: bold;
	font-size: 9pt; 
}
.table-report .total {
	vertical-align: top;
	height: 30px;
	background-color: lightgray;
	font-weight: bold;
	font-size: 10pt; 
}
</style>
HTML;
		return $result;
	}
/** Построить отчет
 *
 * @return	string	HTML текст отчета
 */
	public function Get() {
		$result='';
		$result.=$this->GetReportCss();
		$result.=$this->GetReportTable();
		return $result;
	}
}
