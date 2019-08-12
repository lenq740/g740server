<?php
/**
 * @file
 * G740Server, виджет WidgetBaseTable
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс WidgetBaseTable
 */
class WidgetBaseTable extends Widget {
/// вернуть пусто если нет подходящих строк
	public $isTableClearIfEmpty=false;
/// нужен заголовок таблицы
	public $isTableTHead=false;
/// черезстрочная раскраска строк таблицы
	public $isTableClassStriped=true;
/// рамка у таблицы
	public $isTableClassBordered=true;
/// выделение строки под указателем мыши
	public $isTableClassHover=true;
/// сжатый размер таблицы
	public $isTableClassCondensed=true;
/// имя класса таблицы
	public $tableClassName='';
/// имя метода для вычисления класса строки $this->objCallback->$onGetTrClass($dataStorageItem)
	public $onGetTrClass='';
/** массив описателей колонок таблицы
 *
 * - $column['field'] по умолчанию из имени колонки
 * - $column['caption'] по умолчанию из источника данных
 * - $column['width'] ширина
 * - $column['onGetTdClass'] имя метода для вычисления класса строки $this->objCallback->$onGetTdClass($dataStorageItem, $columnName)
 * - $column['onGetTdHtml'] имя метода для вычисления html содержимого td $this->objCallback->$onGetTdHtml($dataStorageItem, $columnName)
 * - $column['onGetTdHref'] имя метода для вычисления ссылки $this->objCallback->$onGetTdHref($dataStorageItem, $columnName)
 */
	public $tableColumns=Array();

/** массив описателей анкеты
 *
 * - $column['field'] по умолчанию из имени колонки
 * - $column['caption'] по умолчанию из источника данных
 * - $column['onGetValue'] имя метода для вычисления значения поля $this->objCallback->$onGetValue($dataStorageItem, $columnName)
 */
	public $anketaColumns=Array();
/// количество колонок разметки bootstrap для анкеты
	public $anketaWidth=6;
/// возможность выделения текущей строки таблицы
	public $isTableRowSelectable=false;
	
/// параметр пагинации - кол-во строк на странице
	public $paginatorCount=0;
/// параметр пагинации - текущая страница
	public $paginatorPage=-1;
/// текущее id строки
	public $currentId=0;
/// имя метода для вычисления класса строки $this->objCallback->$onPaginatorHref($paginatorPage)
	public $onPaginatorHref='';
/// вычесляемый параметр - количество страниц пагинации
	protected $_pageCount=0;
	
/// вернуть содержимое виджета	
	public function get() {
		$errorMessage="Ошибка при обращении к WidgetBaseTable::get()";
		if (!($this->objDataStorage instanceof DataStorage)) throw new Exception($errorMessage.' Не задан DataStorage');
		if (!is_array($this->tableColumns)) throw new Exception($errorMessage.' Не задан список колонок таблицы');
		if (count($this->tableColumns)==0) throw new Exception($errorMessage.' Список колонок таблицы пуст');
		unset($this->filter['paginator.from']);
		unset($this->filter['paginator.count']);
		
		$filter=$this->filter;
		if ($this->paginatorCount>0) {
			$rowCount=$this->objDataStorage->getRowCount($this->filter);
			
			$p=floor(($rowCount-1)/$this->paginatorCount)+1;
			if ($p>1 && ($rowCount-($p-1)*$this->paginatorCount)<0.2*$this->paginatorCount) $p--;
			$this->_pageCount=$p;
			
			if ($this->currentId && ($this->paginatorPage<0)) {
				$rowNumber=$this->objDataStorage->getRowNumber($this->filter);
				$p=floor($rowNumber/$this->paginatorCount);
				$this->paginatorPage=$p;
			}
			if ($this->paginatorPage<0) $this->paginatorPage=0;
			if ($this->paginatorPage>=$this->_pageCount) $this->paginatorPage>=$this->_pageCount-1;

			$filter['paginator.from']=$this->paginatorPage*$this->paginatorCount;
			$filter['paginator.count']=$this->paginatorCount;
			if ($this->paginatorPage>=($this->_pageCount-1)) $filter['paginator.count']=$this->paginatorCount*2;
		}
		$this->items=$this->objDataStorage->getItems($filter);
		if ($this->isTableClearIfEmpty && count($this->items)==0) return '';

		// приводим в порядк массив описателей колонок таблицы
		$newTableColumns=Array();
		foreach($this->tableColumns as $columnName=>$column) {
			if (is_string($column)) {
				$columnName=$column;
				$column=Array();
			}
			if (!is_string($columnName)) continue;
			if (!is_array($column)) $column=Array();
			$field=$column['field']?$column['field']:$columnName;
			$fld=$this->objDataStorage->getField($field);
			if ($fld) $column['fld']=$fld;
			$newTableColumns[$columnName]=$column;
		}
		$this->tableColumns=$newTableColumns;

		// приводим в порядк массив описателей колонок анкеты
		$newAnketaColumns=Array();
		foreach($this->anketaColumns as $columnName=>$column) {
			if (is_string($column)) {
				$columnName=$column;
				$column=Array();
			}
			if (!is_string($columnName)) continue;
			if (!is_array($column)) $column=Array();
			$field=$column['field']?$column['field']:$columnName;
			$fld=$this->objDataStorage->getField($field);
			if ($fld) $column['fld']=$fld;
			$newAnketaColumns[$columnName]=$column;
		}
		$this->anketaColumns=$newAnketaColumns;
		
		$info=Array();
		$info['html-table']=$this->getTable();
		$info['html-paginator']=$this->getPaginator();
		$info['html-anketa']=$this->getAnketa();
		$info['html-script']=$this->getScript();
		$result=$this->template($info);
		return $result;
	}
/** шаблон виджета
 *
 * @param	Array	$info параметры формирования виджета
 * @return	string шаблон виджета
 */
	protected function template($info=Array()) {
		if ($info['html-anketa']) {
			$tableCol=12-intval($this->anketaWidth);
			$anketaCol=intval($this->anketaWidth);
			$result=<<<HTML
<div class="row">
<div class="col-xs-{$tableCol}">
{$info['html-table']}
{$info['html-paginator']}
</div>
<div class="col-xs-{$anketaCol}">
{$info['html-anketa']}
</div>
{$info['html-script']}
</div>
HTML;
		}
		else {
			$result=<<<HTML
{$info['html-table']}
{$info['html-paginator']}
{$info['html-script']}
HTML;
		}
		return $result;
	}
/// содержимое таблицы
	protected function getTable() {
		$htmlTHead=$this->getTableThead();
		$htmlTBody=$this->getTableTbody();
		
		$class='table';
		if ($this->isTableClassStriped) $class.=' table-striped';
		if ($this->isTableClassBordered) $class.=' table-bordered';
		if ($this->isTableClassHover) $class.=' table-hover';
		if ($this->isTableClassCondensed) $class.=' table-condensed';
		if ($this->tableClassName) $class.=' '.$this->tableClassName;
		
		$result=<<<HTML
<table class="{$class}" id="widget-{$this->guid}-table">
{$htmlTHead}
<tbody>
{$htmlTBody}
</tbody>
</table>
{$htmlPaginator}
HTML;
		return $result;
	}
/// содержимое заголовка таблицы
	protected function getTableThead() {
		if (!$this->isTableTHead) return '';
		$html='';
		foreach($this->tableColumns as $columnName=>$column) {
			$field=$column['field']?$column['field']:$columnName;
			$caption=$column['caption']?$column['caption']:'';
			$width=$column['width']?$column['width']:'';
			$style='';
			if ($width) {
				if ($style) $style.=';';
				$style.="width:{$width}";
			}
			if (!$caption) {
				$fld=$column['fld'];
				if ($fld && $fld['caption']) $caption=$fld['caption'];
			}
			if (!$caption) $caption=$field;
			$htmlCaption=str2Html($caption);
			if ($style) $style='style="'.$style.'"';
			if ($html) $html.="\n";
			$html.=<<<HTML
<th {$style}>{$htmlCaption}</th>
HTML;
		}
		$result=<<<HTML
<thead>
{$html}
</thead>
HTML;
		return $result;
	}
/// содержимое тела таблицы
	protected function getTableTbody() {
		$result='';
		foreach($this->items as &$item) {
			$htmlTR='';
			foreach($this->tableColumns as $columnName=>$column) {
				$field=$column['field']?$column['field']:$columnName;
				$getTdClass=$column['onGetTdClass']?$column['onGetTdClass']:'';
				
				$getTdHtml=$column['onGetTdHtml']?$column['onGetTdHtml']:'';
				$getTdHref=$column['onGetTdHref']?$column['onGetTdHref']:'';
				$width=(!$this->isTableTHead && $column['width'])?$column['width']:'';
				$style='';
				$htmlTD='';
				if ($getTdHtml) {
					$htmlTD=$this->objCallback->$getTdHtml($item, $columnName);
				}
				else {
					$fld=$column['fld'];
					$t='';
					if ($fld) $t=$fld['type'];
					if (!$t) $t='string';
					if ($t=='date') {
						$htmlTD=$item->getDateHtml($field);
					}
					else {
						$htmlTD=$item->getHtml($field);
					}
				}
				if ($width) {
					if ($style) $style.=';';
					$style.="width:{$width}";
				}
				if ($style) $style='style="'.$style.'"';
				
				if ($getTdHref) {
					$htmlTD=<<<HTML
<a href="{$this->objCallback->$getTdHref($item, $columnName)}">{$htmlTD}</a>
HTML;
				}
				$classTD='';
				if ($getTdClass) $classTD=$this->objCallback->$getTdClass($item, $columnName);
				if ($classTD) $classTD='class="'.$classTD.'"';
				$htmlTD=<<<HTML
<td {$classTD} {$style}>{$htmlTD}</td>
HTML;
				if ($htmlTR) $htmlTR.="\n";
				$htmlTR.=$htmlTD;
			}
			$getTrClass=$this->onGetTrClass;
			$classTR='';
			if ($getTrClass) $classTR=$this->objCallback->$getTrClass($item);
			if ($item->getId()==$this->currentId) {
				if ($classTR) $classTR.=' ';
				$classTR.='info';
			}
			if ($classTR) $classTR='class="'.$classTR.'"';
			$attrId=str2Attr($item->getId());
			
			$onTrClick='';
			if ($this->isTableRowSelectable) $onTrClick="window['widget-{$this->guid}-script'].onRowSelect(this.id)";
			if ($onTrClick) $onTrClick='onclick="'.$onTrClick.'"';
			
			$dataAttr='';
			foreach($this->anketaColumns as $columnName=>$column) {
				$field=$column['field']?$column['field']:$columnName;
				$onGetValue=$column['onGetValue'];
				$value='';
				if ($onGetValue) {
					$value=$this->objCallback->$onGetValue($item, $columnName);
				}
				else {
					$fld=$column['fld'];
					$t='';
					if ($fld) $t=$fld['type'];
					if (!$t) $t='string';
					if ($t=='date') {
						$value=$item->getDateHtml($field);
					}
					else {
						$value=$item->get($field);
					}
				}
				if ($value!='') {
					if ($dataAttr) $dataAttr.=' ';
					$dataAttr.='data-'.$columnName.'="'.str2Attr($value).'"';
				}
			}
			
			
			if ($result) $result.="\n";
			$result.=<<<HTML
<tr {$classTR} id="{$attrId}" {$onTrClick} {$dataAttr}>
{$htmlTR}
</tr>
HTML;
		}
		return $result;
	}
/// заготовка анкеты
	protected function getAnketa() {
		$result='';
		if (count($this->anketaColumns)==0) return $result;
		$result=<<<HTML
<table class="table table-bordered table-condensed" id="widget-{$this->guid}-anketa">
<tbody>
</tbody>
</table>
HTML;
		return $result;
	}
/// пагинатор
	protected function getPaginator() {
		$result='';
		if (!$this->paginatorCount) return $result;
		if ($this->_pageCount<=1) return $result;
		
		$minPage=$this->paginatorPage-3;
		if ($minPage<0) $minPage=0;
		$maxPage=$minPage+6;
		if ($maxPage>=($this->_pageCount-1)) $maxPage=($this->_pageCount-1);
		
		$html='';
		$onPaginatorHref=$this->onPaginatorHref;
		for($indexPage=$minPage; $indexPage<=$maxPage; $indexPage++) {
			if ($html) $html.="\n";
			$htmlPage=$indexPage+1;
			$href='#';
			if ($onPaginatorHref) $href=str2Attr($this->objCallback->$onPaginatorHref($indexPage));
			$class='';
			if ($indexPage==$this->paginatorPage) $class='active';
			if ($class) $class='class="'.$class.'"';
			$html.=<<<HTML
<li {$class}><a href="{$href}">{$htmlPage}</a></li>
HTML;
		}
		
		$predHref='#';
		$predClass='disabled';
		$nextHref='#';
		$nextClass='disabled';
		if ($this->paginatorPage>0) {
			if ($onPaginatorHref) $predHref=str2Attr($this->objCallback->$onPaginatorHref($this->paginatorPage-1));
			$predClass='';
		}
		if ($this->paginatorPage<($this->_pageCount-1)) {
			if ($onPaginatorHref) $nextHref=str2Attr($this->objCallback->$onPaginatorHref($this->paginatorPage+1));
			$nextClass='';
		}
		$result=<<<HTML
<nav>
    <ul class="pagination">
<li class="{$predClass}"><a href="{$predHref}"> Предыдущая </a></li>
{$html}
<li class="{$nextClass}"><a href="{$nextHref}"> Следующая </a></li>
    </ul>
</nav>
HTML;
		return $result;
	}
/// JavaScript
	protected function getScript() {
		$lstAnketaColumns='';
		foreach($this->anketaColumns as $columnName=>$column) {
			$field=$column['field']?$column['field']:$columnName;
			$caption=$column['caption']?$column['caption']:'';
			if (!$caption) {
				$fld=$column['fld'];
				if ($fld && $fld['caption']) $caption=$fld['caption'];
			}
			if (!$caption) $caption=$field;
			$jsColumnName=str2JavaScript($columnName);
			$jsCaption=str2JavaScript($caption);

			if ($lstAnketaColumns) $lstAnketaColumns.=",\n";
			$lstAnketaColumns.=<<<HTML
{attr: 'data-{$jsColumnName}',caption: '{$jsCaption}'}
HTML;
		}
		
		$result=<<<HTML
window['widget-{$this->guid}-script']={
	lstAnketaColumns: [{$lstAnketaColumns}],
	onRowSelect: function(id) {
		$('#widget-{$this->guid}-table .info').removeClass('info');
		$('#widget-{$this->guid}-table #'+id).addClass('info');
		if (window['widget-{$this->guid}-script'].lstAnketaColumns.length>0) {
			window['widget-{$this->guid}-script'].doRefreshAnketa(id);
		}
	},
	doRefreshAnketa: function(id) {
		var jqAnketaBody=$('#widget-{$this->guid}-anketa tbody');
		var jqTableTr=$('#widget-{$this->guid}-table #'+id);
		jqAnketaBody.children('tr').remove();
		for(var columnIndex in window['widget-{$this->guid}-script'].lstAnketaColumns) {
			var column=window['widget-{$this->guid}-script'].lstAnketaColumns[columnIndex];

			var value=jqTableTr.attr(column.attr);
			if (!value) continue;
			console.log({column: column, value: value});
			
			var domTr=document.createElement('tr');
			var domTdCaption=document.createElement('td');
			var domText=document.createTextNode(column.caption);
			domTdCaption.appendChild(domText);
			domTr.appendChild(domTdCaption);
			var domTdValue=document.createElement('td');
			var domText=document.createTextNode(value);
			domTdValue.appendChild(domText);
			domTr.appendChild(domTdValue);
			jqAnketaBody.append(domTr);
		}
	}
};
HTML;
		if ($this->currentId && $lstAnketaColumns) {
			$jsCurrentId=str2JavaScript($this->currentId);
			$result.="\n".<<<HTML
window['widget-{$this->guid}-script'].doRefreshAnketa('{$jsCurrentId}');
HTML;
		}
		$result=<<<HTML
<script>
{$result}
</script>
HTML;
		return $result;
	}
}
return new WidgetBaseTable();