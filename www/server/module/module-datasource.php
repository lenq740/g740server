<?php
/**
Библиотека источников данных
@package module
@subpackage module-datasource
*/
require_once('module-dsconnector.php');

/**
Класс предок для источника данных
@package module
@subpackage module-datasource
*/
class DataSource extends DSConnector{
	public $tableName='';			// таблица, обязательно должно быть задано в потомке, автоматически заполняется автогенератором классов
	public $tableCaption='';		// Название таблицы, обязательно должно быть задано в потомке, автоматически заполняется автогенератором классов
	public $permMode='';			// Режим прав, если не задан, то tableName
	public $selectOtherFields='';	// Добавляется к списку полей в select, может быть переопределено в потомке
	public $isGUID=false;			// В качестве id используется GUID
	public $isSaveOnAppend=false;	// Сохранять в базе при добавлении
	public $selectLimit=0;			// Ограничение на максимальное кол-во возвращаемых строк

	public function getPerm($permOper='read', $requestName='', $params=Array()) {
		$permMode=$this->permMode;
		if (!$permMode) $permMode=$this->tableName;
		$p=$params;
		$p['#request.name']=$requestName;
		return getPerm($permMode, $permOper);
	}
	
	public function writeXmlExec($params=Array()) {
		//$startTime=microtime(true);
		global $objResponseWriter;
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		$objResponseWriter->startElement('response');
		$objResponseWriter->writeAttribute('name', 'ok');
		$datasource=$this->tableName;
		if ($params['#request.datasource']) $datasource=$params['#request.datasource'];
		$objResponseWriter->writeAttribute('datasource', $datasource);
		if ($params['#request.rowset']) $objResponseWriter->writeAttribute('rowset', $params['#request.rowset']);
		
		$paginatorFrom=$params['paginator.from'];
		$paginatorCount=$params['paginator.count'];
		if ($paginatorFrom || $paginatorCount) {
			if ($requestName=='refresh') {
				if (!$paginatorFrom) $paginatorFrom=0;
				$sql=$this->getSelectCount($params);
				$rec=$this->pdoFetch($sql);
				$paginatorAll=$rec['n'];
				$objResponseWriter->writeAttribute('paginator.from', $paginatorFrom);
				$objResponseWriter->writeAttribute('paginator.all', $paginatorAll);
			}
		}
		
		if ($requestName=='definition') {
			writeXml($this->getStrXmlDefinition($params));
		} else {
			$rows=$this->exec($params);
			//trace(sprintf('exec - %.4F',microtime(true)-$startTime));
			$this->writeXmlRows($rows);
			//trace(sprintf('toXml - %.4F',microtime(true)-$startTime));
		}
		$objResponseWriter->endElement();
		return true;
	}
	protected function writeXmlRows($lst) {
		global $objResponseWriter;
		
		$lstFldT=Array();
		$lstFldT['id']='string';
		$lstFldT['id.change']='string';
		$lstFldT['row.readonly']='check';
		$lstFldT['row.color']='string';
		$lstFldT['row.destmode']='string';
		$lstFldT['row.destid']='string';
		$lstFldT['row.focus']='check';
		$lstFldT['row.delete']='check';
		$lstFldT['row.new']='check';
		$lstFldT['row.type']='string';
		$lstFldT['row.icon']='string';
		$lstFldT['row.empty']='check';
		$lstFldT['row.final']='check';
		foreach($this->getFields() as $key=>$fld) {
			$name=$fld['name'];
			$t=$fld['type'];
			if (!$t) $t='string';
			$lstFldT[$name]=$t;
			$lstFldT[$name.'.change']=$t;
			$lstFldT[$name.'.readonly']='check';
			$lstFldT[$name.'.visible']='check';
			$lstFldT[$name.'.color']='string';
		}
		foreach ($lst as $rec) {
			if ($rec['row.new']==1) {
				$rowName='append';
			} else if ($rec['row.delete']==1) {
				$rowName='delete';
			} else if ($rec['row.change']==1) {
				$rowName='change';
			} else {
				$rowName='row';
			}
			$objResponseWriter->startElement($rowName);
			
			$lstMemo=Array();
			foreach($rec as $name=>$value) {
				$t=$lstFldT[$name];
				if ($t=='memo' && $value) {
					$lstMemo[$name]=$value;
				} else if ($t=='string') {
					$objResponseWriter->writeAttribute($name,$value);
				} else {
					$objResponseWriter->writeAttribute($name,php2g($value,$t));
				}
			}
			if ($lstMemo) foreach($lstMemo as $name=>$value) {
				$objResponseWriter->startElement('field');
				$objResponseWriter->writeAttribute('name',$name);
				$objResponseWriter->text($value);
				$objResponseWriter->endElement();
			}
			
			$objResponseWriter->endElement();
		}
		return true;
	}
	public function getStrXmlDefinition($params=Array()) {
		$datasource=$this->tableName;
		if ($params['#request.datasource']) $datasource=$params['#request.datasource'];
		$attrDataSource='datasource="'.str2Attr($datasource).'"';
		{	// $attrRowset
			$attrRowset='';
			if ($params['#request.rowset']) $attrRowset='rowset="'.str2Attr($params['#request.rowset']).'"';
		}
		{	// $attrReadOnly
			$attrReadOnly='';
			if (!$this->getPerm('write')) $attrReadOnly='readonly="1"';
		}
		$result=<<<XML
<rowset {$attrDataSource} {$attrRowset} {$attrReadOnly}>
{$this->getStrXmlDefinitionSections($params)}
</rowset>
XML;
		return $result;
	}
	public function getStrXmlDefinitionSections($params=Array(), $requests=null) {
		$result=<<<XML
{$this->getStrXmlDefinitionRequests($params, $requests)}
{$this->getStrXmlDefinitionFields($params)}
XML;
		return $result;
	}
	public function getStrXmlDefinitionRequests($params=Array(), $requests=null) {
		if (!$requests) $requests=$this->getRequests();
		return $this->autoGenXmlDefinitionRequests($requests);
	}
	public function getStrXmlDefinitionFields($params=Array()) {
		return $this->autoGenXmlDefinitionFields();
	}
	public function getStrXmlDefItem($params=Array(), $dataSource, $rowType, $treeName='name', $treeDescription='name', $strXmlAddRequests='', $strXmlAddFields='') {
		$requests=$dataSource->getRequests();
		$r=$requests['refresh'];
		$r['name']='expand';
		$requests['expand']=$r;
		unset($requests['refreshrow']);
		$strXmlR=$dataSource->getStrXmlDefinitionRequests($params, $requests);
		if ($strXmlAddRequests) {
			$xmlR=strXml2DomXml($strXmlR);
			$xmlAdd=strXml2DomXml($strXmlAddRequests);
			while ($xmlAdd->firstChild) $xmlR->appendChild($xmlAdd->firstChild);
			$strXmlR=domXml2StrXml($xmlR);
		}
		
		$strXmlF=$dataSource->getStrXmlDefinitionFields($params);
		$xmlF=strXml2DomXml($strXmlF);
		if ($strXmlAddFields) {
			$xmlAdd=strXml2DomXml($strXmlAddFields);
			while ($xmlAdd->firstChild) $xmlF->appendChild($xmlAdd->firstChild);
		}
		xmlSetAttr($xmlF, 'name', $treeName);
		xmlSetAttr($xmlF, 'description', $treeDescription);
		$strXmlF=domXml2StrXml($xmlF);
		$attrRowType=str2Attr($rowType);
		$result=<<<XML
<section row.type="{$attrRowType}">
{$strXmlR}
{$strXmlF}
</section>
XML;
		return $result;
	}
	
	protected $fields=null;
	public function getFields() {
		if ($this->fields) return $this->fields;
		$this->fields=Array();
		return $this->fields;
	}
	
	protected $_fieldsByName=null;
	public function getField($name) {
		if (!$this->_fieldsByName) {
			$this->_fieldsByName=Array();
			foreach($this->getFields() as $key=>$fld) $this->_fieldsByName[$fld['name']]=$fld;
		}
		return $this->_fieldsByName[$name];
	}
	
	protected $_requests=null;
	public function getRequests() {
		if ($this->_requests) return $this->_requests;
		$this->_requests=Array();
		{	// refresh
			$r=Array();
			$r['name']='refresh';
			$r['permoper']='read';
			$this->_requests['refresh']=$r;
		}
		{	// refreshrow
			$r=Array();
			$r['name']='refreshrow';
			$r['permoper']='read';
			$this->_requests['refreshrow']=$r;
		}
		{	// append
			$r=Array();
			$r['name']='append';
			$r['permoper']='write';
			$this->_requests['append']=$r;
		}
		{	// copy
			$r=Array();
			$r['name']='copy';
			$r['permoper']='write';
			$this->_requests['copy']=$r;
		}
		{	// save
			$r=Array();
			$r['name']='save';
			$r['permoper']='write';
			$this->_requests['save']=$r;
		}
		{	// delete
			$r=Array();
			$r['name']='delete';
			$r['permoper']='write';
			$this->_requests['delete']=$r;
		}
		{	// shift
			if ($this->getField('ord')) {
				$r=Array();
				$r['name']='shift';
				$r['permoper']='write';
				$this->_requests['shift']=$r;
			}
		}
		return $this->_requests;
	}
	protected function getRequest($request) {
		$r=$this->getRequests();
		return $r[$request];
	}
	
	public function exec($params=Array()) {
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		if ($requestName=='refresh') return $this->execRefresh($params);
		if ($requestName=='refreshrow') {
			$id=$params['id'];
			if (!$id) throw new Exception('Не задан id!!!');
			return $this->execRefresh(Array('filter.id'=>$id));
		}
		if ($requestName=='save') return $this->execSave($params);
		if ($requestName=='append') return $this->execAppend($params);
		if ($requestName=='copy') return $this->execCopy($params);
		if ($requestName=='delete') return $this->execDelete($params);
		if ($requestName=='shift') return $this->execShift($params);
		if ($requestName=='change') return $this->execChange($params);
		throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
	}
	public function execRefresh($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execRefresh';
		if (!$this->getPerm('read','refresh',$params)) throw new ExceptionNoReport('У Вас нет прав на чтение таблицы '.$this->tableCaption);
		$select=$this->getSelect($params);
		$fields=$this->getFields();
		foreach($fields as $key=>$fld) {
			$fld['sqlname']=strtolower($fld['name']);
			$fields[$key]=$fld;
		}
		$result=Array();
		$q=$this->pdo($select);
		while ($rec=$this->pdoFetch($q)) {
			$res=Array();
			if (isset($rec['id'])) $res['id']=$rec['id'];
			if (isset($rec['row_readonly'])) $res['row.readonly']=$rec['row_readonly'];
			if (isset($rec['row_color'])) $res['row.color']=$rec['row_color'];
			if (isset($rec['row_type'])) $res['row.type']=$rec['row_type'];
			if (isset($rec['row_icon'])) $res['row.icon']=$rec['row_icon'];
			if (isset($rec['row_empty'])) $res['row.empty']=$rec['row_empty'];
			if (isset($rec['row_final'])) $res['row.final']=$rec['row_final'];
			foreach($fields as $key=>$fld) {
				$name=$fld['name'];
				$sqlName=$fld['sqlname'];
				$res[$name]='';
				if (isset($rec[$sqlName])) $res[$name]=$rec[$sqlName];
				if (isset($rec[$sqlName.'_readonly'])) $res[$name.'.readonly']=$rec[$sqlName.'_readonly'];
				if (isset($rec[$sqlName.'_color'])) $res[$name.'.color']=$rec[$sqlName.'_color'];
				if (isset($rec[$sqlName.'_visible'])) $res[$name.'.visible']=$rec[$sqlName.'_visible'];
			}
			$result[]=&$res;
			unset($res);
		}
		return $result;
	}
	public function execSave($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execSave';
		if (!$this->getPerm('write','save',$params)) throw new ExceptionNoReport('У Вас нет прав на внесение изменений в строку таблицы '.$this->tableCaption);

		if ($params['row.new']==1) {
			return $this->execInsert($params);
		}
		else {
			return $this->execUpdate($params);
		}
		return Array();
	}
	public function execUpdate($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execUpdate';
		if (!$this->getPerm('write','save',$params)) throw new ExceptionNoReport('У Вас нет прав на внесение изменений в строку таблицы '.$this->tableCaption);
		$result=Array();
		$fields=$this->getFields();
		$id=$params['id'];
		$sqlId=($this->isGUID)?$this->guid2Sql($id):$this->str2Sql($id);
		
		$sqlFields='';
		$sqlDelim='';
		$driverName=$this->getDriverName();
		foreach($fields as $key=>$fld) {
			if ($fld['virtual']) continue;
			$alias=$this->tableName;
			if ($fld['table']) $alias=$fld['table'];
			if ($fld['alias']) $alias=$fld['alias'];
			if ($alias!=$this->tableName) continue;
			$name=$fld['name'];
			if (!array_key_exists($name,$params)) continue;
			//if (!isset($params[$name]) && $params[$name]!==null) continue;
			$sqlName=strtolower($name);
			$value=$params[$name];
			if ($value=='' && $fld['type']=='ref' && $this->isGUID) $value='00000000-0000-0000-0000-000000000000';
			
			if ($driverName=='mysql') {
				$sqlFields=$sqlFields . $sqlDelim . "`{$sqlName}` = ".((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
				$sqlDelim=',';
			}
			else if ($driverName=='sqlsrv') {
				if ($value=='' && $fld['type']=='date') $value=null;
				$sqlFields=$sqlFields . $sqlDelim . "[{$sqlName}] = ".((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
				$sqlDelim=',';
			}
		}
		if ($sqlFields!='') {
			if ($driverName=='mysql') {
				$sqlUpdate='update `' . $this->tableName . '` set ' . $sqlFields . " where id='{$sqlId}'";
			}
			else if ($driverName=='sqlsrv') {
				$sqlUpdate='update [' . $this->tableName . '] set ' . $sqlFields . " where id='{$sqlId}'";
			} else {
				throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
			}
			$this->pdo($sqlUpdate, 'Ошибка при правке строки таблицы '.$this->tableCaption);
		}
		
		if (!$id) $id='0';
		$result=$this->execRefresh(Array('filter.id'=>$id));
		$result=$this->onValid($result);
		$result=$this->onAfterSave($result, $params);
		return $result;
	}
	public function execInsert($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execInsert';
		if (!$this->getPerm('write','save',$params)) throw new ExceptionNoReport('У Вас нет прав на внесение изменений в строку таблицы '.$this->tableCaption);
		$result=Array();
		$fields=$this->getFields();
		$sqlFields='';
		$sqlValues='';
		$sqlDelim='';
		$driverName=$this->getDriverName();
		
		if ($this->isGUID) {
			$name='id';
			$sqlId=$this->guid2Sql($params['id']);
			$sqlName=strtolower($name);
			$sqlFields.=$sqlDelim . $sqlName;
			$sqlValues.=$sqlDelim . "'{$sqlId}'";
			$sqlDelim=',';
		}
		
		foreach($fields as $key=>$fld) {
			if ($fld['virtual']) continue;
			$alias=$this->tableName;
			if ($fld['table']) $alias=$fld['table'];
			if ($fld['alias']) $alias=$fld['alias'];
			if ($alias!=$this->tableName) continue;
			$name=$fld['name'];
			if (!isset($params[$name])) continue;
			$sqlName=strtolower($name);
			$value=$params[$name];
			if ($fld['type']=='ref' && $this->isGUID) $value=$this->guid2Sql($value);
			
			if ($driverName=='mysql') {
				$sqlFields.=$sqlDelim . '`' . $sqlName . '`';
				$sqlValues.=$sqlDelim . ((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
				$sqlDelim=',';
			} else if ($driverName=='sqlsrv') {
				if ($value=='' && $fld['type']=='date') $value=null;
				$sqlFields.=$sqlDelim . '[' . $sqlName . ']';
				$sqlValues.=$sqlDelim . ((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
				$sqlDelim=',';
			}
		}
		if ($driverName=='mysql') {
			$sqlInsert="insert into `{$this->tableName}` ({$sqlFields}) values ({$sqlValues})";
		} else if ($driverName=='sqlsrv') {
			$sqlInsert="insert into [{$this->tableName}] ({$sqlFields}) values ({$sqlValues})";
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		$this->pdo($sqlInsert, 'Ошибка при вставке строки таблицы '.$this->tableCaption);
		
		if ($this->isGUID) {
			$lastId=$params['id'];
		} else {
			$lastId=$this->getPDO()->lastInsertId();
		}
		if (!$lastId) $lastId='0';
		$result=$this->execRefresh(Array('filter.id'=>$lastId));
		$result=$this->onValid($result);
		$result=$this->onAfterSave($result, $params);
		return $result;
	}
	public function execCopy($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execCopy';
		if (!$this->getPerm('write','copy',$params)) throw new ExceptionNoReport('У Вас нет прав на правку таблицы '.$this->tableCaption);

		$p=$params;
		$p['filter.id']=$params['id'];
		$p['#request.name']='refresh';
		$select=$this->getSelect($p);
		$rec=$this->pdoFetch($select);
		
		$p=Array();
		foreach($this->getFields() as $fldIndex=>$fld) {
			$field=$fld['name'];
			$p[$field]=$rec[$field];
			if ($field=='name') $p[$field]=$rec[$field].' <копия>';
		}
		
		$p['id']=-1;
		if ($this->isGUID) $p['id']=getGUID();
		$p['row.new']=1;
		if ($this->getField('ord')) $p['ord']=$this->getOrdAppendAfter($params);
		$lst=$this->execSave($p);
		if (count($lst)!=1) throw new ExceptionNoReport('Ошибка при копировании - не удалось вставить строку!!!');
		$recResult=$lst[0];
		
		$recResult['row.destmode']='after';
		$recResult['row.destid']=$params['id'];
		$recResult['row.focus']=1;
		$result=Array();
		$result[]=$recResult;
		return $result;
	}
	public function execChange($params=Array()) {
		$result=Array();
		return $result;
	}
	
	// Обработка событий, в качестве параметра - результат операции
	protected function onValid($result=Array()) {
		$fields=$this->getFields();
		foreach($result as $rec) {
			foreach($fields as $fld) {
				$alias=$this->tableName;
				if ($fld['table']) $alias=$fld['table'];
				if ($fld['alias']) $alias=$fld['alias'];
				if ($alias!=$this->tableName) continue;
				
				if ($fld['notnull']!=1) continue;
				$name=$fld['name'];
				$sqlName=strtolower($name);
				$isEmpty=!$rec[$sqlName];
				if ($fld['type']=='ref' && $rec[$sqlName]=='00000000-0000-0000-0000-000000000000') $isEmpty=true;
				if ($isEmpty) throw new ExceptionNoReport('Не заполнено значение поля '.$fld['caption']);
			}
		}
		return $result;
	}
	protected function onAfterSave($result=Array(), $params=Array()) {
		return $result;
	}
	
	
	public function execDelete($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execDelete';
		if (!$this->getPerm('write','delete',$params)) throw new ExceptionNoReport('У Вас нет прав на удаление строки таблицы '.$this->tableCaption);
		
		if ($params['recursLevel']>15) throw new Exception('Удаление невозможно, обнаружилось зацикливание ссылок при анализе ссылочной целостности');
		$id=$params['id'];
		$sqlId=($this->isGUID)?$this->guid2Sql($id):$this->str2Sql($id);
		
		$idlist=$params['listOfId'];
		if (!$idlist && $id) $idlist="'{$sqlId}'";
		if (!$idlist) return Array();
		$refs=$this->getReferences();
		
		$driverName=$this->getDriverName();
		$p=$params;
		$p['listOfId']=$idlist;
		$p['refs']=$refs;
		$sqlSelect='';
		$sqlDelete='';
		if ($driverName=='mysql') {
			$this->_execDeleteRestrictMySql($p);
			$this->_execDeleteCascadeMySql($p);
			$this->_execDeleteClearMySql($p);
			$sqlSelect=<<<SQL
select id from `{$this->tableName}` where id in ({$idlist})
SQL;
			$sqlDelete=<<<SQL
delete from `{$this->tableName}` where id in ({$idlist})
SQL;
		} else if ($driverName=='sqlsrv') {
			$this->_execDeleteRestrictSqlSrv($p);
			$this->_execDeleteCascadeSqlSrv($p);
			$this->_execDeleteClearSqlSrv($p);
			$sqlSelect=<<<SQL
select id from [{$this->tableName}] where id in ({$idlist})
SQL;
			$sqlDelete=<<<SQL
delete from [{$this->tableName}] where id in ({$idlist})
SQL;
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		// Удаляем из основной таблицы
		$result=Array();
		$q=$this->pdo($sqlSelect);
		while($rec=$this->pdoFetch($q)) {
			$rec['row.delete']=1;
			$result[]=$rec;
		}
		$this->pdo($sqlDelete);
		return $result;
	}
	protected function _execDeleteRestrictMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteRestrictMySql';
		$idlist=$params['listOfId'];
		$refs=$params['refs'];
		foreach($refs as $key=>$ref) {
			if ($ref['mode']!='restrict') continue;
			$dataSourceRef=getDataSource($ref['from.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['from.table']);
			
			$sql=<<<SQL
select count(*) as n 
from `{$ref['from.table']}` 
where 
	`{$ref['from.table']}`.`{$ref['from.field']}` in (
		select `{$ref['to.field']}` 
		from 
			`{$ref['to.table']}` 
		where `{$ref['to.table']}`.id in ({$idlist})
	)
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['n']>0) throw new ExceptionNoReport("Удаление невозможно, значение используется в связанной таблице {$dataSourceRef->tableCaption} ({$dataSourceRef->tableName})");
		}
	}
	protected function _execDeleteRestrictSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteRestrictSqlSrv';
		$idlist=$params['listOfId'];
		$refs=$params['refs'];
		foreach($refs as $key=>$ref) {
			if ($ref['mode']!='restrict') continue;
			$dataSourceRef=getDataSource($ref['from.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['from.table']);
			
			$sql=<<<SQL
select count(*) as n 
from [{$ref['from.table']}]
where 
	[{$ref['from.table']}].[{$ref['from.field']}] in (
		select [{$ref['to.field']}]
		from 
			[{$ref['to.table']}]
		where [{$ref['to.table']}].id in ({$idlist})
	)
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['n']>0) throw new ExceptionNoReport("Удаление невозможно, значение используется в связанной таблице {$dataSourceRef->tableCaption} ({$dataSourceRef->tableName})");
		}
	}
	protected function _execDeleteCascadeMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteCascadeMySql';
		$idlist=$params['listOfId'];
		$refs=$params['refs'];
		foreach($refs as $key=>$ref) {
			if ($ref['mode']!='cascade') continue;
			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			$refIdList='';
			$sql=<<<SQL
select id
from
	`{$ref['to.table']}`
where
	`{$ref['to.table']}`.`{$ref['to.field']}` in ({$idlist})
SQL;
			$q=$this->pdo($sql, 'Удаление невозможно, ошибка в запросе проверки ссылочной целостности');
			while($rec=$this->pdoFetch($q)) {
				if ($refIdList) $refIdList.=", ";
				$refIdList.="'{$rec['id']}'";
			}
			$p=Array();
			$p['listOfId']=$refIdList;
			$p['recursLevel']=1;
			if ($params['recursLevel']) $p['recursLevel']=$params['recursLevel']+1;
			$dataSourceRef->execDelete($p);
		}
	}
	protected function _execDeleteCascadeSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteCascadeSqlSrv';
		$idlist=$params['listOfId'];
		$refs=$params['refs'];
		foreach($refs as $key=>$ref) {
			if ($ref['mode']!='cascade') continue;
			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			$refIdList='';
			$sql=<<<SQL
select id
from
	[{$ref['to.table']}]
where
	[{$ref['to.table']}].[{$ref['to.field']}] in ({$idlist})
SQL;
			$q=$this->pdo($sql, 'Удаление невозможно, ошибка в запросе проверки ссылочной целостности');
			while($rec=$this->pdoFetch($q)) {
				if ($refIdList) $refIdList.=", ";
				$refIdList.="'{$rec['id']}'";
			}
			$p=Array();
			$p['listOfId']=$refIdList;
			$p['recursLevel']=1;
			if ($params['recursLevel']) $p['recursLevel']=$params['recursLevel']+1;
			$dataSourceRef->execDelete($p);
		}
	}
	protected function _execDeleteClearMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteClearMySql';
		$idlist=$params['listOfId'];
		$refs=$params['refs'];
		foreach($refs as $key=>$ref) {
			if ($ref['mode']!='clear') continue;
			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			$refIdList='';
			$sql=<<<SQL
update `{$ref['to.table']}` set `{$ref['to.field']}`=0
where
	`{$ref['to.field']}` in ({$idlist})
SQL;
			$this->pdo($sql);
		}
	}
	protected function _execDeleteClearSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteClearSqlSrv';
		$idlist=$params['listOfId'];
		$refs=$params['refs'];
		foreach($refs as $key=>$ref) {
			if ($ref['mode']!='clear') continue;
			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			$refIdList='';
			$sql=<<<SQL
update [{$ref['to.table']}] set [{$ref['to.field']}]=0
where
	[{$ref['to.field']}] in ({$idlist})
SQL;
			$this->pdo($sql);
		}
	}

	public function execShift($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execShift';
		if (!$this->getPerm('write','shift',$params)) throw new ExceptionNoReport('У Вас нет прав на перемещение строки в таблице '.$this->tableCaption);
		$mode=$params['#request.mode'];
		if ($mode!='after' && $mode!='before' && $mode!='last' && $mode!='first') throw new Exception("Недопустимый параметр '{$mode}' операции перемещения строки");
		if (!$this->getField('ord')) throw new Exception('Нет поля ord, перемещение строки невозможно');
		$id=$params['id'];
		if (!$id) throw new Exception('Не задан параметр id, перемещение строки невозможно');
		$recResult=Array();
		$recResult['id']=$id;

		$select=$this->_getReorderSelect($params);
		if ($mode=='first') {
			$recFirst=Array();
			$q=$this->pdo($select);
			while ($rec=$this->pdoFetch($q)) {
				$recFirst=$rec;
				break;
			}
			if (isset($recFirst['id']) && $recFirst['id']!=$id) {
				$recResult['ord']=$recFirst['ord']-100;
				$this->_goReorderUpdate($id, $recResult['ord']);
			}
		}
		if ($mode=='last') {
			$recLast=Array();
			$q=$this->pdo($select);
			while ($rec=$this->pdoFetch($q)) {
				$recLast=$rec;
			}
			if (isset($recLast['id']) && $recLast['id']!=$id) {
				$recResult['ord']=$recLast['ord']+100;
				$this->_goReorderUpdate($id, $recResult['ord']);
			}
		}
		if ($mode=='after') {
			$recId=Array();
			$recAfter=Array();
			$q=$this->pdo($select);
			while ($rec=$this->pdoFetch($q)) {
				if (isset($recId['id'])) {
					$recAfter=$rec;
					break;
				}
				if ($rec['id']==$id) $recId=$rec;
			}
			if (isset($recId['id']) && isset($recAfter['id'])) {
				if ($recId['ord']==$recAfter['ord']) {
					$this->_goReorder($params);
					$recId=Array();
					$recAfter=Array();
					$q=$this->pdo($select);
					while ($rec=$this->pdoFetch($q)) {
						if (isset($recId['id'])) {
							$recAfter=$rec;
							break;
						}
						if ($rec['id']==$id) $recId=$rec;
					}
				}
			}
			if (isset($recId['id']) && isset($recAfter['id'])) {
				$ord=$recId['ord'];
				$recId['ord']=$recAfter['ord'];
				$recAfter['ord']=$ord;
				$this->_goReorderUpdate($recId['id'], $recId['ord']);
				$this->_goReorderUpdate($recAfter['id'], $recAfter['ord']);
				$recResult['ord']=$recId['ord'];
			}
		}
		if ($mode=='before') {
			$recId=Array();
			$recBefore=Array();
			$q=$this->pdo($select);
			while ($rec=$this->pdoFetch($q)) {
				if ($rec['id']==$id) {
					$recId=$rec;
					break;
				}
				$recBefore=$rec;
			}
			if (isset($recId['id']) && isset($recBefore['id'])) {
				if ($recId['ord']==$recBefore['ord']) {
					$this->_goReorder($params);
					$recId=Array();
					$recAfter=Array();
					$q=$this->pdo($select);
					while ($rec=$this->pdoFetch($q)) {
						if ($rec['id']==$id) {
							$recId=$rec;
							break;
						}
						$recBefore=$rec;
					}
				}
			}
			if (isset($recId['id']) && isset($recBefore['id'])) {
				$ord=$recId['ord'];
				$recId['ord']=$recBefore['ord'];
				$recBefore['ord']=$ord;
				$this->_goReorderUpdate($recId['id'], $recId['ord']);
				$this->_goReorderUpdate($recBefore['id'], $recBefore['ord']);
				$recResult['ord']=$recId['ord'];
			}
		}
		
		$recId=Array();
		$recBefore=Array();
		$q=$this->pdo($select);
		while ($rec=$this->pdoFetch($q)) {
			if ($rec['id']==$id) {
				$recId=$rec;
				break;
			}
			$recBefore=$rec;
		}
		if (isset($recBefore['id'])) {
			$recResult['row.destmode']='after';
			$recResult['row.destid']=$recBefore['id'];
		} else {
			$recResult['row.destmode']='first';
		}
		$result=Array();
		$result[]=$recResult;
		return $result;
	}
	public function execAppend($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execAppend';
		foreach($this->getFields() as $key=>$fld) {
			$name=$fld['name'];
			if (isset($params["filter.{$name}"]) && !isset($params[$name])) $params[$name]=$params["filter.{$name}"];
		}
		if (!$this->getPerm('write','append',$params)) throw new ExceptionNoReport('У Вас нет прав на добавление в таблицу '.$this->tableCaption);
		
		$mode=$params['#request.mode'];
		if ($mode!='first' && $mode!='after' && $mode!='before') $mode='last';
		$id=$params['id'];
		
		$result=Array();
		
		$recResult=Array();
		$newId=-1;
		if ($this->isGUID) $newId=getGUID();
		$recResult['id']=$newId;
		$recResult['row.new']=1;
		foreach($this->getFields() as $key=>$fld) {
			$name=$fld['name'];
			if ($params[$name]) $recResult[$name]=$params[$name];
			if ($recResult[$name] && $fld['type']=='ref' && $fld['reftable']) {
				$dataSourceRef=getDataSource($fld['reftable']);
				$p=Array();
				$p['filter.id']=$recResult[$name];
				$p['id']=$recResult[$name];
				$lst=$dataSourceRef->execRefresh($p);
				if (count($lst)==1) {
					$rec=$lst[0];
					foreach($this->getFields() as $keyRefName=>$fldRefName) {
						if ($fldRefName['refid']!=$name) continue;
						$recResult[$fldRefName['name']]=$rec[$fldRefName['refname']];
					}
				}
			}
		}
		if (!isset($recResult['ord']) && $this->getField('ord')) {
			if ($mode=='first') $recResult['ord']=$this->getOrdAppendFirst($params);
			if ($mode=='last') $recResult['ord']=$this->getOrdAppendLast($params);
			if ($mode=='after') $recResult['ord']=$this->getOrdAppendAfter($params);
			if ($mode=='before') $recResult['ord']=$this->getOrdAppendBefore($params);
		}
		if ($this->isSaveOnAppend) {
			$lst=$this->execInsert($recResult);
			if (count($lst)!=1) throw new Exception('Ошибка при сохранении добавляемой строки');
			$recResult=$lst[0];
			$recResult['row.focus']=1;
		}
		$recResult['row.destmode']=$mode;
		if ($mode=='after' || $mode=='before') $recResult['row.destid']=$id;
		$result[]=$recResult;
		return $result;
	}
	public function getOrdAppendFirst($params=Array()) {
		if (!$this->getField('ord')) return 0;
		$select=$this->_getReorderSelect($params);
		$ord=200;
		$q=$this->pdo($select);
		while ($rec=$this->pdoFetch($q)) {
			$ord=$rec['ord'];
			break;
		}
		return $ord-100;
	}
	public function getOrdAppendLast($params=Array()) {
		if (!$this->getField('ord')) return 0;
		$select=$this->_getReorderSelect($params);
		$ord=0;
		$q=$this->pdo($select);
		$isFirst=true;
		while ($rec=$this->pdoFetch($q)) {
			if ($isFirst) {
				$ord=$rec['ord'];
				$isFirst=false;
			}
			if ($rec['ord']>$ord) $ord=$rec['ord'];
		}
		return $ord+100;
	}
	protected function getOrdAppendAfter($params=Array(), $isNoReorder=false) {
		if (!$this->getField('ord')) return 0;
		$id=$params['id'];
		$isFound=false;
		$ordPrev=0;
		$ordNext=0;
		$select=$this->_getReorderSelect($params);
		$q=$this->pdo($select);
		while ($rec=$this->pdoFetch($q)) {
			if ($rec['id']==$id) {
				$isFound=true;
				$ordPrev=$rec['ord'];
				$ordNext=$ordPrev+200;
				continue;
			}
			if ($isFound) {
				$ordNext=$rec['ord'];
				break;
			}
		}
		if (!$isFound) return 0;
		if ($ordNext>=$ordPrev) {
			$ord=round($ordPrev+($ordNext-$ordPrev)/3.0);
		} else {
			$ord=$ordPrev+200;
		}
		if (!$isNoReorder && ($ord==$ordPrev || $ord==$ordNext)) {
			// Принудительно пересортировываем, а затем возращаем значение после пересортировки
			$this->_goReorder($params, true);
			return $this->getOrdAppendAfter($params, true);
		}
		return $ord;
	}
	protected function getOrdAppendBefore($params=Array(), $isNoReorder=false) {
		if (!$this->getField('ord')) return 0;
		$id=$params['id'];
		$isFound=false;
		$ordPrev=false;
		$ordNext=0;
		$select=$this->_getReorderSelect($params);
		$q=$this->pdo($select);
		while ($rec=$this->pdoFetch($q)) {
			if ($rec['id']==$id) {
				$isFound=true;
				$ordNext=$rec['ord'];
				if ($ordPrev===false) $ordPrev=$ordNext-200;
				break;
			}
			$ordPrev=$rec['ord'];
		}
		if (!$isFound) return 0;
		if ($ordNext>=$ordPrev) {
			$ord=round($ordPrev+($ordNext-$ordPrev)/3.0);
		} else {
			$ord=$ordNext-200;
		}
		if (!$isNoReorder && ($ord==$ordPrev || $ord==$ordNext)) {
			// Принудительно пересортировываем, а затем возращаем значение после пересортировки
			$this->_goReorder($params, true);
			return $this->getOrdAppendBefore($params, true);
		}
		return $ord;
	}
	
	protected function _goReorder($params=Array(), $isReorderAll=false) {
		$errorMessage='Ошибка при обращении к DataSource::_goReorder';
		if (!$this->getField('ord')) return true;
		if (!$this->getPerm('write','reorder',$params)) throw new ExceptionNoReport('У Вас нет прав на пересортировку строк в таблице '.$this->tableCaption);
		$select=$this->_getReorderSelect($params);
		
		if (!$isReorderAll) {
			$ordPred=999999;
			$q=$this->pdo($select);
			while ($rec=$this->pdoFetch($q)) {
				if ($ordPred==$rec['ord']) {
					$isReorderAll=true;
					break;
				}
				$ordPred=$rec['ord'];
			}
		}
		if (!$isReorderAll) return true;
		
		$ord=100;
		$q=$this->pdo($select);
		while ($rec=$this->pdoFetch($q)) {
			if ($rec['ord']!=$ord) {
				$rec['ord']=$ord;
				$this->_goReorderUpdate($rec['id'], $rec['ord']);
			}
			$ord+=100;
		}
		return true;
	}
	protected function _getReorderSelect($params=Array()) {
		$p=$params;
		$p['#request.name']='reorder';
		return $this->getSelect($p);
	}
	protected function _goReorderUpdate($id, $ord) {
		$driverName=$this->getDriverName();
		$sqlId=($this->isGUID)?$this->guid2Sql($id):$this->str2Sql($id);
		$sqlOrd=$this->str2Sql($ord);
		
		if ($driverName=='mysql') {
			$sql="update `{$this->tableName}` set ord='{$sqlOrd}' where id='{$sqlId}'";
		} else if ($driverName=='sqlsrv') {
			$sql="update [{$this->tableName}] set ord='{$sqlOrd}' where id='{$sqlId}'";
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		$this->pdo($sql);
		return true;
	}

	// Работа с временной таблицей ключей
	public function saveToTmpTable($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::saveToTmpTable';
		if (!$this->getPerm('read','refresh',$params)) throw new ExceptionNoReport('У Вас нет прав на чтение из таблицы '.$this->tableCaption);
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') return $this->_saveToTmpTableMySql($params);
		if ($driverName=='sqlsrv') return $this->_saveToTmpTableSqlSrv($params);
		return '';
	}
	protected function _saveToTmpTableMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_saveToTmpTableMySql';
		$result=getGUID();
		if ($paginatorFrom || $paginatorCount) {
			if (!$paginatorFrom) $paginatorFrom=0;
			if (!$paginatorCount) $paginatorCount=500;
			$sql=<<<SQL
insert into tmptablelist (list, value)
select 
	'{$result}' as list,
	`{$this->tableName}`.id
from
{$this->getSelectFrom($params)}
where 1=1
{$this->getSelectWhere($params)}
order by
{$this->getSelectOrderBy($params)}
limit {$paginatorFrom}, {$paginatorCount}
SQL;
		}
		else {
			$sql=<<<SQL
insert into tmptablelist (list, value)
select 
	'{$result}' as list,
	`{$this->tableName}`.id
from
{$this->getSelectFrom($params)}
where 1=1
{$this->getSelectWhere($params)}
SQL;
		}
		$this->pdo($sql);
		return $result;
	}
	protected function _saveToTmpTableSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_saveToTmpTableSqlSrv';
		$result=getGUID();
		if ($paginatorFrom || $paginatorCount) {
			if (!$paginatorFrom) $paginatorFrom=0;
			if (!$paginatorCount) $paginatorCount=500;
			$sql=<<<SQL
insert into ##tmptablelist (list, value)
select top {$paginatorCount} 
	'{$result}' as list,
	a.id
from
(
select 
	[{$this->tableName}].id,
	,ROW_NUMBER() over (
	order by
{$this->getSelectOrderBy($params)}
	) as [row_number]
from
{$this->getSelectFrom($params)}
where
	1=1
{$this->getSelectWhere($params)}
) a
where
  a.[row_number]>{$paginatorFrom}
order by a.[row_number]
SQL;
		}
		else {
			$sql=<<<SQL
insert into ##tmptablelist (list, value)
select 
	'{$result}' as list,
	[{$this->tableName}].id
from
{$this->getSelectFrom($params)}
where 1=1
{$this->getSelectWhere($params)}
SQL;
		}
		$this->pdo($sql);
		return $result;
	}
	
	// Добавляет в список результатов результаты из подчиненного источника данных, полезно для подготовки отчетов
	public function appendChilds(&$list, &$childs, $childName, $childFieldName) {
		$rows=Array();
		$n=count($list);
		for($i=0; $i<$n; $i++) {
			unset($row);
			$row=&$list[$i];
			$id=$row['id'];
			$rows[$id]=&$row;
		}
		$n=count($childs);
		for($i=0; $i<$n; $i++) {
			unset($child);
			unset($row);
			$child=&$childs[$i];
			$klsrow=$child[$childFieldName];
			$row=&$rows[$klsrow];
			if (!$row) continue;
			if (!$row["#child.{$childName}"]) $row["#child.{$childName}"]=Array();
			$row["#child.{$childName}"][]=&$child;
		}
	}
	public function getSelectCount($params=Array()) {
		$selectFrom=$this->getSelectFrom($params);
		$selectWhere=$this->getSelectWhere($params);
		$result=<<<SQL
select count(*) as n
from
{$selectFrom}
SQL;
		if ($selectWhere) {
			$result.="\n".<<<SQL
where
	1=1
{$selectWhere}
SQL;
		}
		return $result;
	}
	public function getSelect($params=Array()) {
		$driverName=$this->getDriverName();
		$result='';
		if ($driverName=='mysql') {
			$result=$this->_getSelectMySql($params);
		} 
		else if ($driverName=='sqlsrv') {
			$result=$this->_getSelectSqlSrv($params);
		} 
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		return $result;
	}
	protected function _getSelectMySql($params=Array()) {
		$selectFields=$this->getSelectFields($params);
		$selectFrom=$this->getSelectFrom($params);
		$selectWhere=$this->getSelectWhere($params);
		$selectOrderBy=$this->getSelectOrderBy($params);
		$paginatorFrom=$params['paginator.from'];
		$paginatorCount=$params['paginator.count'];
		if (!$paginatorFrom && !$paginatorCount && $this->selectLimit) {
			$paginatorFrom=0;
			$paginatorCount=$this->selectLimit;
		}

		$result=<<<SQL
select
{$selectFields}
from
{$selectFrom}
SQL;
		if ($selectWhere) {
			$result.="\n".<<<SQL
where
	1=1
{$selectWhere}
SQL;
		}
		if ($selectOrderBy) {
			$result.="\n".<<<SQL
order by {$selectOrderBy}
SQL;
		}
		if ($paginatorFrom || $paginatorCount) {
			if (!$paginatorFrom) $paginatorFrom=0;
			if (!$paginatorCount) $paginatorCount=500;
			$result.="\n".<<<SQL
limit {$paginatorFrom}, {$paginatorCount}
SQL;
		}
		return $result;
	}
	protected function _getSelectSqlSrv($params=Array()) {
		$selectFields=$this->getSelectFields($params);
		$selectFrom=$this->getSelectFrom($params);
		$selectWhere=$this->getSelectWhere($params);
		$selectOrderBy=$this->getSelectOrderBy($params);
		$paginatorFrom=$params['paginator.from'];
		$paginatorCount=$params['paginator.count'];
		if (!$paginatorFrom && !$paginatorCount && $this->selectLimit) {
			$paginatorFrom=0;
			$paginatorCount=$this->selectLimit;
		}
		
		if (!$paginatorFrom && !$paginatorCount) {
			$result=<<<SQL
select
{$selectFields}
from
{$selectFrom}
SQL;
			if ($selectWhere) {
				$result.="\n".<<<SQL
where
	1=1
{$selectWhere}
SQL;
			}
			if ($selectOrderBy) {
				$result.="\n".<<<SQL
order by {$selectOrderBy}
SQL;
			}
		}
		else {
			if (!$paginatorFrom) $paginatorFrom=0;
			if (!$paginatorCount) $paginatorCount=500;
			if (!$selectOrderBy) $selectOrderBy="[{$this->tableName}].id";
			$result=<<<SQL
select top {$paginatorCount} 
	a.*
from
(
select 
{$selectFields}
	,ROW_NUMBER() over (order by {$selectOrderBy}) as [row_number]
from
{$selectFrom}
where
	1=1
{$selectWhere}
) a
where
  a.[row_number]>{$paginatorFrom}
order by a.[row_number]
SQL;
		}
		return $result;
	}
	
	public function getSelectFields($params=Array()) {
		return $this->autoGenGetSelectFields();
	}
	public function getSelectFrom($params=Array()) {
		return $this->autoGenGetSelectFrom();
	}
	public function getSelectWhere($params=Array()) {
		$result='';
		$code=$this->autoGenGetSelectWhere();
		eval($code);
		return $result;
	}
	public function getSelectOrderBy($params=Array()) {
		$result='';
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$result="`{$this->tableName}`.id";
		} else if ($driverName=='sqlsrv') {
			$result="[{$this->tableName}].id";
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		return $result;
	}
	
	public function autoGenGetFields($fields=null, $tableName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenGetFields';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;
		$D='$';
		$result='';
		foreach($fields as $key=>$fld) {
			$items='';
			foreach($fld as $name=>$value) {
				if ($items) $items.="\n";
				if (gettype($value)=='array') {
					$items.="\t\t"."{$D}fld['{$name}']=".var_export($value, true).";";
				}
				else {
					$items.="\t\t"."{$D}fld['{$name}']='{$value}';";
				}
			}
			if ($result) $result.="\n";
			$result.=<<<PHP
	{	// {$fld['name']} - {$fld['caption']}
		{$D}fld=Array();
{$items}
		{$D}this->fields[]={$D}fld;
	}
PHP;
		}
		return $result;
	}
	public function autoGenGetSelectFields($fields=null, $tableName=null, $selectOtherFields=null, $driverName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenGetSelectFields';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;
		if (!$selectOtherFields) $selectOtherFields=$this->selectOtherFields;
		if (!$driverName) $driverName=$this->getDriverName();
		
		$result='';
		if ($driverName=='mysql') {
			$result="\t"."`{$tableName}`.*";
		} else if ($driverName=='sqlsrv') {
			$result="\t"."[{$tableName}].*";
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		
		foreach($fields as $key=>$fld) {
			if ($fld['virtual']) continue;
			$table=$tableName;
			if ($fld['table']) $table=$fld['table'];
			$alias=$table;
			if ($fld['alias']) $alias=$fld['alias'];
			if ($alias==$tableName) continue;
			$fieldName=$fld['name'];
			if ($fld['fieldname']) $fieldName=$fld['fieldname'];
			$result.=','."\n\t";
			
			if ($driverName=='mysql') {
				$result.="`{$alias}`.`{$fieldName}` as `{$fld['name']}`";
			} else if ($driverName=='sqlsrv') {
				$result.="[{$alias}].[{$fieldName}] as [{$fld['name']}]";
			} else {
				throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
			}
		}
		if ($selectOtherFields) {
			if (mb_substr(trim($selectOtherFields),0,1)!=',') $result.=',';
			$result.="\n";
			$result.=$selectOtherFields;
		}
		return $result;
	}
	public function autoGenGetSelectFrom($fields=null, $tableName=null, $driverName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenGetSelectFrom';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;
		if (!$driverName) $driverName=$this->getDriverName();

		$result='';
		if ($driverName=='mysql') {
			$result="\t"."`{$tableName}`";
		} else if ($driverName=='sqlsrv') {
			$result="\t"."[{$tableName}]";
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		$aliases=$this->_getAliases($fields, $tableName);
		foreach($aliases as $alias=>$als) {
			if ($als['alias']==$tableName) continue;

			if ($driverName=='mysql') {
				if ($als['table']==$als['alias']) {
					$result.="\n\t\t"."left join `{$als['alias']}` on `{$als['alias']}`.id=`{$als['parent.alias']}`.`{$als['parent.refid']}`";
				} else {
					$result.="\n\t\t"."left join `{$als['table']}` `{$als['alias']}` on `{$als['alias']}`.id=`{$als['parent.alias']}`.`{$als['parent.refid']}`";
				}
			} else if ($driverName=='sqlsrv') {
				if ($als['table']==$als['alias']) {
					$result.="\n\t\t"."left join [{$als['alias']}] on [{$als['alias']}].id=[{$als['parent.alias']}].[{$als['parent.refid']}]";
				} else {
					$result.="\n\t\t"."left join [{$als['table']}] [{$als['alias']}] on [{$als['alias']}].id=[{$als['parent.alias']}].[{$als['parent.refid']}]";
				}
			} else {
				throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
			}
		}
		return $result;
	}
	public function autoGenGetSelectWhere($fields=null, $tableName=null, $driverName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenGetSelectFrom';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;
		if (!$driverName) $driverName=$this->getDriverName();
		$D='$';
		$B='';
		$E='';
		$tmpTable='';
		if ($driverName=='mysql') {
			$B='`';
			$E='`';
			$tmpTable='tmptablelist';
		} else if ($driverName=='sqlsrv') {
			$B='[';
			$E=']';
			$tmpTable='##tmptablelist';
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		$result=<<<PHP
	{$D}result='';
	if ({$D}params['filter.id']!='') {
		if ({$D}this->isGUID) {
			{$D}value={$D}this->guid2Sql({$D}params['filter.id']);
		}
		else {
			{$D}value={$D}this->php2Sql({$D}params['filter.id']);
		}
		{$D}result.="\\n"."and {$B}{$tableName}{$E}.id='{{$D}value}'";
	}
PHP;
		if ($tmpTable) {
			$result.="\n".<<<PHP
	if ({$D}params['filter.id.tmptable']!='') {
		{$D}value={$D}this->php2Sql({$D}params['filter.id.tmptable']);
		{$D}result.="\\n"."and {$B}{$tableName}{$E}.id in (select value from {$tmpTable} where {$tmpTable}.list='{{$D}value}')";
	}
PHP;
		}
		foreach($fields as $key=>$fld) {
			$table=$tableName;
			if ($fld['table']) $table=$fld['table'];
			$alias=$table;
			if ($fld['alias']) $alias=$fld['alias'];
			$fieldName=$fld['name'];
			if ($fld['field']) $fieldName=$fld['field'];
			$fullFieldName="{$B}{$alias}{$E}.{$B}{$fieldName}{$E}";
			$filterType=$fld['type'];
			if ($fld['filter']) {
				if ($fld['filter']['type']) $filterType=$fld['filter']['type'];
				if ($fld['filter']['field']) $fullFieldName=$fld['filter']['field'];
			} else {
				if ($fld['virtual']) continue;
				if ($alias!=$tableName) continue;
			}
			if ($filterType=='ref') {
				$result.="\n".<<<PHP
	if ({$D}params['filter.{$fld['name']}']!='') {
		{$D}value={$D}this->php2Sql({$D}params['filter.{$fld['name']}']);
		{$D}result.="\\n"."and {$fullFieldName}='{{$D}value}'";
	}
PHP;
				if ($tmpTable) {
					$result.="\n".<<<PHP
	if ({$D}params['filter.{$fld['name']}.tmptable']!='') {
		{$D}value={$D}this->php2Sql({$D}params['filter.{$fld['name']}.tmptable']);
		{$D}result.="\\n"."and {$fullFieldName} in (select value from {$tmpTable} where {$tmpTable}.list='{{$D}value}')";
	}
PHP;
				}
			} else if ($filterType=='check') {
				$result.="\n".<<<PHP
	if ({$D}params['filter.{$fld['name']}']!='') {
		{$D}value={$D}this->php2Sql({$D}params['filter.{$fld['name']}']);
		{$D}result.="\\n"."and {$fullFieldName}='{{$D}value}'";
	}
PHP;
			}
		}
		return $result;
	}
	public function autoGenXmlDefinitionFields($fields=null, $tableName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenXmlDefinition';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;

		$doc=xmlCreateDoc();
		$lstFieldsAttr=Array(
			'name',
			'type',
			'caption',
			'default',
			'notnull',
			'stretch',
			'list',
			'basetype',
			'len',
			'maxlength',
			'dec',
			'width',
			'dlgwidth',
			'save',
			'readonly',
			'js_readonly',
			'visible',
			'js_visible',
			'rows'
		);
		
		// Строим список подходящих refid
		$lstRefId=Array();
		foreach($fields as $key=>$fld) {
			if ($fld['type']!='ref') continue;
			$table=$tableName;
			if ($fld['table']) $table=$fld['table'];
			$alias=$table;
			if ($fld['alias']) $alias=$fld['alias'];
			if ($alias!=$tableName) continue;
			$lstRefId[$fld['name']]=$fld;
		}
		
		$xmlFields=$doc->createElement('fields');
		foreach($fields as $key=>$fld) {
			if ($fld['type']=='ref' && !$lstRefId[$fld['name']]) $fld['type']='string';
			
			$xmlField=$doc->createElement('field');
			foreach($lstFieldsAttr as $attrId=>$attrName) {
				if ($fld[$attrName]) xmlSetAttr($xmlField,$attrName,$fld[$attrName]);
			}
			if ($fld['type']=='ref') {
				$xmlRef=$doc->createElement('ref');
				xmlSetAttr($xmlRef,'datasource',$fld['reftable']);
				if ($fld['ref.params']) {
					foreach($fld['ref.params'] as $paramName=>$paramValue) {
						$xmlParam=$doc->createElement('param');
						xmlSetAttr($xmlParam,'name',$paramName);
						if ($paramValue!=='') xmlSetAttr($xmlParam,'js_value',$paramValue);
						$xmlRef->appendChild($xmlParam);
					}
				}
				$xmlField->appendChild($xmlRef);
			}
			if ($fld['change']==1) {
				$xmlChange=$doc->createElement('change');
				if ($fld['change.params']) {
					foreach($fld['change.params'] as $paramName=>$paramValue) {
						$xmlParam=$doc->createElement('param');
						xmlSetAttr($xmlParam,'name',$paramName);
						if ($paramValue) xmlSetAttr($xmlParam,'js_value',$paramValue);
						$xmlChange->appendChild($xmlParam);
					}
				}
				$xmlField->appendChild($xmlChange);
			}
			if ($fld['refid']) {
				if (!$lstRefId[$fld['refid']]) {
					xmlSetAttr($xmlField,'readonly',1);
				}
				else {
					xmlSetAttr($xmlField,'refid',$fld['refid']);
					if ($fld['refname']) xmlSetAttr($xmlField,'refname',$fld['refname']);
					if ($fld['reftext']) xmlSetAttr($xmlField,'reftext',$fld['reftext']);
				}
			}
			$xmlFields->appendChild($xmlField);
		}
		$doc->appendChild($xmlFields);
		$result=$doc->saveXML($xmlFields);
		$result=str_replace("><",">\n<",$result);
		$result=str_replace("\n<ref ","\n\t<ref ",$result);
		$result=str_replace("\n</ref","\n\t</ref",$result);
		$result=str_replace("\n<change>","\n\t<change>",$result);
		$result=str_replace("\n</change>","\n\t</change>",$result);
		$result=str_replace("\n<param ","\n\t\t<param ",$result);
		return $result;
	}
	public function autoGenXmlDefinitionRequests($requests=null, $tableName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenXmlDefinitionRequests';
		if (!$requests) $requests=$this->getRequests();
		if (!$tableName) $tableName=$this->tableName;
		
		$doc=xmlCreateDoc();

		$xmlRequests=$doc->createElement('requests');
		foreach($requests as $requestName=>$request) {
			$xmlRequest=$doc->createElement('request');
			xmlSetAttr($xmlRequest,'name',$request['name']);
			if ($request['mode']) xmlSetAttr($xmlRequest,'mode',$request['mode']);
			$permOper='read';
			if ($request['permoper']) $permOper=$request['permoper'];
			if (!$this->getPerm($permOper, $requestName)) xmlSetAttr($xmlRequest,'enabled','0');
			$xmlRequests->appendChild($xmlRequest);
		}
		$doc->appendChild($xmlRequests);
		
		$result=$doc->saveXML($xmlRequests);
		$result=str_replace("><",">\n<",$result);
		return $result;
	}
	
	protected function _getAliases($fields=null, $tableName=null) {
		$errorMessage='Ошибка при обращении к DataSource::_getAliases';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;
		$result=Array();
		$als=Array();
		$als['table']=$tableName;
		$als['alias']=$tableName;
		$result[$als['alias']]=$als;
		foreach($fields as $key=>$fld) {
			if ($fld['virtual']) continue;
			if ($fld['type']=='ref') {
				$table=$tableName;
				if ($fld['table']) $table=$fld['table'];
				$alias=$table;
				if ($fld['alias']) $alias=$fld['alias'];
				$fieldName=$fld['name'];
				if ($fld['fieldname']) $fieldName=$fld['fieldname'];
				$als=Array();
				$als['table']=$fld['reftable'];
				$als['alias']=$fld['reftable'];
				if ($fld['refalias']) $als['alias']=$fld['refalias'];
				$als['parent.table']=$table;
				$als['parent.alias']=$alias;
				$als['parent.refid']=$fieldName;
				if (!$result[$als['alias']]) $result[$als['alias']]=$als;
			}
		}
		return $result;
	}
}

/**
Получить объект источника данных по имени
@param	String	$name источник данных
@return	DataSource объект источника данных
*/
function getDataSource($name) {
	global $registerDataSource;
	
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	if ($name!=$str) throw new Exception("Недопустимое имя источника данных '{$name}'");
	if ($registerDataSource[$name]) return $registerDataSource[$name];

	$fileNameAutoGen=getCfg('pathDataSources')."/autogen/{$name}.php";
	if (file_exists($fileNameAutoGen)) {
		$obj=include_once($fileNameAutoGen);
		if ($obj instanceof DataSource) $registerDataSource[$name]=$obj;
	}
	
	$fileNameUserDef=getCfg('pathDataSources')."/{$name}.php";
	if (file_exists($fileNameUserDef)) {
		$obj=include_once($fileNameUserDef);
		if ($obj instanceof DataSource) $registerDataSource[$name]=$obj;
	}
	
	if (!$registerDataSource[$name]) throw new Exception("Недопустимое имя источника данных '{$name}'");
	return $registerDataSource[$name];
}
$registerDataSource=Array();
?>