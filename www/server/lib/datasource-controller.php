<?php
/**
 * @file
 * Библиотека - модель данных
 */
require_once('dsconnector.php');
require_once('lib-g740server.php');

/** Класс DataSource - предок для классов источников данных
 *
 * Для каждой таблицы SQL сервера создается свой класс потомок DataSource и порождается экземпляр объекта этого класса.
 */
class DataSource extends DSConnector{
/// таблица, обязательно должно быть задано в потомке, автоматически заполняется автогенератором классов
	public $tableName='';
/// название таблицы, обязательно должно быть задано в потомке, автоматически заполняется автогенератором классов
	public $tableCaption='';
/// Режим прав, если не задан, то tableName
	public $permMode='';
/// Добавляется к списку полей в select, может быть переопределено в потомке
	public $selectOtherFields='';
/// В качестве id используется GUID
	public $isGUID=false;
/// Сохранять в базе при добавлении
	public $isSaveOnAppend=false;
/// Ограничение на максимальное кол-во возвращаемых строк
	public $selectLimit=0;

/** Проверка доступности выполнения операции по правам в контексте выполнения запроса
 *
 * @param	string	$permOper опрерация (read, write)
 * @param	string	$requestName запрос
 * @param	Array	$params контекст выполнения запроса
 * @return	boolean доступность выполнения операции
 */
	public function getPerm($permOper='read', $requestName='', $params=Array()) {
		$permMode=$this->permMode;
		if (!$permMode) $permMode=$this->tableName;
		return getPerm($permMode, $permOper);
	}
	
/** Выполнить запрос, записать ответ в XMLWriter согласно протоколу G740
 *
 * @param	Array	$params контекст выполнения запроса
 */
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
				$paginatorAll=$this->getRowCount($params);
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
/** Записать результаты выполнения запроса, полученные в виде массива, в ответ XMLWriter согласно протоколу G740
 *
 * @param	Array	$lst результат выпонения запроса
 */
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
/** Вернуть описание источника данных согласно протоколу G740
 *
 * @param	Array	$params контекст выполнения запроса
 * @return	strXml описание источника данных согласно протоколу G740
 */
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
			if (!getPerm($this->permMode?$this->permMode:$this->tableName, 'write')) $attrReadOnly='readonly="1"';
		}
		$result=<<<XML
<rowset {$attrDataSource} {$attrRowset} {$attrReadOnly}>
{$this->getStrXmlDefinitionSections($params)}
</rowset>
XML;
		return $result;
	}
/** Вернуть описание допустимых запросов и полей источника данных согласно протоколу G740
 *
 * @param	Array	$params контекст выполнения
 * @param	Array	$requests позволяет переопределить список запросов
 * @return	strXml описание допустимых запросов и полей источника данных согласно протоколу G740
 */
	public function getStrXmlDefinitionSections($params=Array(), $requests=null) {
		$result=<<<XML
{$this->getStrXmlDefinitionRequests($params, $requests)}
{$this->getStrXmlDefinitionFields($params)}
XML;
		return $result;
	}
/** Вернуть описание допустимых запросов источника данных согласно протоколу G740
 *
 * @param	Array	$params контекст выполнения
 * @param	Array	$requests позволяет переопределить список запросов
 * @return	strXml описание допустимых запросов и полей источника данных согласно протоколу G740
 */
	public function getStrXmlDefinitionRequests($params=Array(), $requests=null) {
		if (!$requests) $requests=$this->getRequests();
		return $this->autoGenXmlDefinitionRequests($requests);
	}
/** Вернуть описание полей источника данных согласно протоколу G740
 *
 * @param	Array	$params контекст выполнения
 * @return	strXml описание допустимых запросов и полей источника данных согласно протоколу G740
 */
	public function getStrXmlDefinitionFields($params=Array()) {
		return $this->autoGenXmlDefinitionFields();
	}
/** Старый вариант облегчения построения секции описания дерева, оставлен для обратной совместимости
 */
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
/** Актуальный вариант построения секции описания дерева
 *
 * @param	Array	$params параметры вызова
 * @param	DataSource	$dataSource источник данных, используемый для формирования описания секции дерева
 * @return	strXml описание секции дерева
 *
 * params['row.type']
 * params['tree.name']
 * params['tree.description']
 * params['tree.default.icon']
 * params['tree.default.final']
 * params['xml.requests']
 * params['xml.fields']
 */
	public function getStrXmlDefinitionTreeSection($params=Array(), $dataSource) {
		$rowType=$params['row.type'];
		$treeName=$params['tree.name'];
		$treeDescription=$params['tree.description'];
		$treeDefaultIcon=$params['tree.default.icon'];
		$treeDefaultFinal=$params['tree.default.final'];
		$strXmlAddRequests=$params['xml.requests'];
		$strXmlAddFields=$params['xml.fields'];
		
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
		if ($treeName) xmlSetAttr($xmlF, 'name', $treeName);
		if ($treeDescription) xmlSetAttr($xmlF, 'description', $treeDescription);
		if ($treeDefaultIcon) xmlSetAttr($xmlF, 'default.icon', $treeDefaultIcon);
		if ($treeDefaultFinal) xmlSetAttr($xmlF, 'default.final', '1');
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

	
/// Описание полей источника данных
	protected $fields=null;
/** Первоначально проинициализировать описание полей источника данных, если описание полей надо переопределить, то делать это надо тут
 *
 * @return	Array описание полей источника данных
 */
	protected function initFields() {
		$result=Array();
		return $result;
	}
/** Вернуть описание полей источника данных
 *
 * @return	Array описание полей источника данных
 */
	public function getFields() {
		if (!$this->fields) $this->fields=$this->initFields();
		return $this->fields;
	}
/// Вспомогательный массив для облегчения поиска описания поля по его имени
	protected $_fieldsByName=null;
/** Вернуть описание поля источника данных по имени поля
 *
 * @param	string	$name имя поля
 * @return	Array описание поля источника данных
 */
	public function getField($name) {
		if (!$this->_fieldsByName) {
			$this->_fieldsByName=Array();
			foreach($this->getFields() as $key=>$fld) $this->_fieldsByName[$fld['name']]=$fld;
		}
		return $this->_fieldsByName[$name];
	}
	
/// Описание связей с другими источниками данных
	protected $references=null;
/** Первоначально проинициализировать описание связей с другими источниками данных
 *
 * @return	Array описание связей с другими источниками данных
 */
	protected function initReferences() {
		$result=Array();
		return $result;
	}
/** Вернуть описание связей с другими источниками данных
 *
 * @return	Array описание связей с другими источниками данных
 */
	public function getReferences() {
		if (!$this->references) $this->references=$this->initReferences();
		return $this->references;
	}
/** Вернуть описание связи с другим источником данных по имени связи
 *
 * @return	Array описание связи с другим источником данных по имени связи
 */
	public function getRef($name) {
		$result=null;
		if (!$this->references) $this->getReferences();
		if (!$this->references) return $result;
		$result=$this->references[$name];
		return $result;
	}
	
/// Описание выполняемых источником данных операций
	protected $_requests=null;
/** Первоначально проинициализировать описание выполняемых источником данных операций
 *
 * @return	Array описание выполняемых источником данных операций
 */
	protected function initRequests() {
		$result=Array();
		$result['refresh']=Array(
			'name'=>'refresh',
			'permoper'=>'read'
		);
		$result['refreshrow']=Array(
			'name'=>'refreshrow',
			'permoper'=>'read'
		);
		$result['append']=Array(
			'name'=>'append',
			'permoper'=>'write'
		);
		$result['copy']=Array(
			'name'=>'copy',
			'permoper'=>'write'
		);
		$result['save']=Array(
			'name'=>'save',
			'permoper'=>'write'
		);
		$result['delete']=Array(
			'name'=>'delete',
			'permoper'=>'write'
		);
		if ($this->getField('ord')) {
			$result['shift']=Array(
				'name'=>'shift',
				'permoper'=>'write'
			);
		}
		return $result;
	}
/** Вернуть описание выполняемых источником данных операций
 *
 * @return	Array описание выполняемых источником данных операций
 */
	public function getRequests() {
		if (!$this->_requests) $this->_requests=$this->initRequests();
		return $this->_requests;
	}
/** Вернуть описание выполняемой операции по ее имени
 *
 * @return	Array описание выполняемой операции по ее имени
 */
	protected function getRequest($request) {
		$r=$this->getRequests();
		return $r[$request];
	}
	
/** Выполнить операцию, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
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
/** Выполнить операцию refresh, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execRefresh($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execRefresh';
		if (!$this->getPerm('read','refresh',$params)) throw new Exception('У Вас нет прав на чтение таблицы '.$this->tableCaption);
		$select=$this->getSelect($params);
		$fields=$this->getFields();
		foreach($fields as $key=>$fld) {
			$fld['sqlname']=strtolower($fld['name']);
			$fields[$key]=$fld;
		}
		$result=Array();
		
		$errorMessage='Ошибка выполнения SQL запроса при чтении строк в таблице '.$this->tableCaption;
		try {
			$q=$this->pdo($select, $errorMessage);
		}
		catch (Exception $e) {
			throw new Exception($errorMessage);
		}
		
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
/** Выполнить операцию save, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execSave($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execSave';
		if (!$this->getPerm('write','save',$params)) throw new Exception('У Вас нет прав на внесение изменений в строку таблицы '.$this->tableCaption);

		if ($params['row.new']==1) {
			return $this->execInsert($params);
		}
		else {
			return $this->execUpdate($params);
		}
		return Array();
	}
/** Ветка update опрерации save
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execUpdate($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execUpdate';
		if (!$this->getPerm('write','save',$params)) throw new Exception('У Вас нет прав на внесение изменений в строку таблицы '.$this->tableCaption);
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
			
			$errorMessage='Ошибка выполнения SQL запроса при правке строки таблицы '.$this->tableCaption;
			try {
				$this->pdo($sqlUpdate, $errorMessage);
			}
			catch (Exception $e) {
				throw new Exception($errorMessage);
			}
		}
		
		if (!$id) $id='0';
		$result=$this->execRefresh(Array('filter.id'=>$id));
		$result=$this->onValid($result);
		$result=$this->onAfterSave($result, $params);
		return $result;
	}
/** Ветка insert опрерации save
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execInsert($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execInsert';
		if (!$this->getPerm('write','save',$params)) throw new Exception('У Вас нет прав на внесение изменений в строку таблицы '.$this->tableCaption);
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
		
		$errorMessage='Ошибка выполнения SQL запроса при вставке строки таблицы '.$this->tableCaption;
		try {
			$this->pdo($sqlInsert, $errorMessage);
		}
		catch (Exception $e) {
			throw new Exception($errorMessage);
		}

		if ($this->isGUID) {
			$lastId=$params['id'];
		} else {
			$lastId=$this->getPDO()->lastInsertId();
		}
		if (!$lastId) $lastId='0';
		$this->onAfterInsert($lastId);
		$result=$this->execRefresh(Array('filter.id'=>$lastId));
		$result=$this->onValid($result);
		$result=$this->onAfterSave($result, $params);
		return $result;
	}
/** Выполнить операцию copy, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execCopy($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execCopy';
		if (!$this->getPerm('write','copy',$params)) throw new Exception('У Вас нет прав на правку таблицы '.$this->tableCaption);

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
		if (count($lst)!=1) throw new Exception('Ошибка при копировании - не удалось вставить строку!!!');
		$recResult=$lst[0];
		
		$recResult['row.destmode']='after';
		$recResult['row.destid']=$params['id'];
		$recResult['row.focus']=1;
		$result=Array();
		$result[]=$recResult;
		return $result;
	}
/** Выполнить операцию change, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execChange($params=Array()) {
		$result=Array();
		return $result;
	}
	
/** Проверка результата операции save на корректность заполнения данных
 *
 * @param	Array	$result результат выполнения операции save
 * @return	Array результат выполнения операции save
 */
	protected function onValid($result=Array()) {
		$fields=$this->getFields();
		foreach($result as &$rec) {
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
				if ($isEmpty) throw new Exception('Не заполнено значение поля '.$fld['caption']);
			}
		}
		return $result;
	}
/** Постобработка результатов операции save
 *
 * @param	Array	$result результат выполнения операции save
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции save
 */
	protected function onAfterSave($result=Array(), $params=Array()) {
		return $result;
	}
/** Постобработка insert
 *
 * @param	string	$id
 */
	protected function onAfterInsert($id) {
	}
	
/** Выполнить операцию delete, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execDelete($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execDelete';
		if (!$params['#recursLevel']) {
			if (!$this->getPerm('write','delete',$params)) throw new Exception('У Вас нет прав на удаление строки таблицы '.$this->tableCaption);
		}
		
		if ($params['#recursLevel']>15) throw new Exception('Удаление невозможно, обнаружилось зацикливание ссылок при анализе ссылочной целостности');
		if (!isset($params['id'])) return Array();
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return Array();
		
		$refs=$this->getReferences();
		$driverName=$this->getDriverName();
		$p=$params;
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
/** Ветка restrict обработки ссылочной целостности при удалении, для SQL сервера MySql
 *
 * @param	Array	$params контекст выполнения
 */
	protected function _execDeleteRestrictMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteRestrictMySql';
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return;
		$refs=$params['refs'];
		foreach($refs as &$ref) {
			if ($ref['mode']!='restrict') continue;
			if ($ref['from.table']!=$this->tableName) continue;
			if ($ref['from.field']!='id') continue;
			
			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			
			$sql=<<<SQL
select count(*) as n 
from `{$ref['to.table']}` 
where 
	`{$ref['to.table']}`.`{$ref['to.field']}` in ({$idlist})
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['n']>0) throw new Exception("Удаление невозможно, значение используется в связанной таблице {$dataSourceRef->tableCaption} ({$dataSourceRef->tableName})");
		}
	}
/** Ветка restrict обработки ссылочной целостности при удалении, для SQL сервера SqlSrv
 *
 * @param	Array	$params контекст выполнения
 */
	protected function _execDeleteRestrictSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteRestrictSqlSrv';
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return;
		$refs=$params['refs'];
		foreach($refs as &$ref) {
			if ($ref['mode']!='restrict') continue;
			if ($ref['from.table']!=$this->tableName) continue;
			if ($ref['from.field']!='id') continue;

			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			
			$sql=<<<SQL
select count(*) as n 
from [{$ref['to.table']}]
where 
	[{$ref['to.table']}].[{$ref['to.field']}] in ({$idlist})
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['n']>0) throw new Exception("Удаление невозможно, значение используется в связанной таблице {$dataSourceRef->tableCaption} ({$dataSourceRef->tableName})");
		}
	}
/** Ветка cascade обработки ссылочной целостности при удалении, для SQL сервера MySql
 *
 * @param	Array	$params контекст выполнения
 */
	protected function _execDeleteCascadeMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteCascadeMySql';
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return;
		$refs=$params['refs'];
		foreach($refs as &$ref) {
			if ($ref['mode']!='cascade') continue;
			if ($ref['from.table']!=$this->tableName) continue;
			if ($ref['from.field']!='id') continue;

			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			$refIdList=Array();
			$sql=<<<SQL
select id
from
	`{$ref['to.table']}`
where
	`{$ref['to.table']}`.`{$ref['to.field']}` in ({$idlist})
SQL;
			$q=$this->pdo($sql, 'Удаление невозможно, ошибка в запросе проверки ссылочной целостности');
			while($rec=$this->pdoFetch($q)) $refIdList[]=$rec['id'];
			if (count($refIdList)==0) return;
			$p=Array();
			$p['id']=$refIdList;
			$p['#recursLevel']=1;
			if ($params['#recursLevel']) $p['#recursLevel']=$params['#recursLevel']+1;
			$dataSourceRef->execDelete($p);
		}
	}
/** Ветка cascade обработки ссылочной целостности при удалении, для SQL сервера SqlSrv
 *
 * @param	Array	$params контекст выполнения
 */
	protected function _execDeleteCascadeSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteCascadeSqlSrv';
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return;
		$refs=$params['refs'];
		foreach($refs as &$ref) {
			if ($ref['mode']!='cascade') continue;
			if ($ref['from.table']!=$this->tableName) continue;
			if ($ref['from.field']!='id') continue;

			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			$refIdList=Array();
			$sql=<<<SQL
select id
from
	[{$ref['to.table']}]
where
	[{$ref['to.table']}].[{$ref['to.field']}] in ({$idlist})
SQL;
			$q=$this->pdo($sql, 'Удаление невозможно, ошибка в запросе проверки ссылочной целостности');
			while($rec=$this->pdoFetch($q)) $refIdList[]=$rec['id'];
			if (count($refIdList)==0) return;
			$p=Array();
			$p['id']=$refIdList;
			$p['#recursLevel']=1;
			if ($params['#recursLevel']) $p['#recursLevel']=$params['#recursLevel']+1;
			$dataSourceRef->execDelete($p);
		}
	}
/** Ветка clear обработки ссылочной целостности при удалении, для SQL сервера MySql
 *
 * @param	Array	$params контекст выполнения
 */
	protected function _execDeleteClearMySql($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteClearMySql';
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return;
		$refs=$params['refs'];
		foreach($refs as &$ref) {
			if ($ref['mode']!='clear') continue;
			if ($ref['from.table']!=$this->tableName) continue;
			if ($ref['from.field']!='id') continue;

			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);
			
			$emptyValue='0';
			if ($dataSourceRef->isGUID) $emptyValue='00000000-0000-0000-0000-000000000000';
			$sql=<<<SQL
update `{$ref['to.table']}` set `{$ref['to.field']}`='{$emptyValue}'
where
	`{$ref['to.field']}` in ({$idlist})
SQL;
			$this->pdo($sql);
		}
	}
/** Ветка clear обработки ссылочной целостности при удалении, для SQL сервера SqlSrv
 *
 * @param	Array	$params контекст выполнения
 */
	protected function _execDeleteClearSqlSrv($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::_execDeleteClearSqlSrv';
		$idlist=$this->php2SqlIn($params['id']);
		if (!$idlist) return;
		$refs=$params['refs'];
		foreach($refs as &$ref) {
			if ($ref['mode']!='clear') continue;
			if ($ref['from.table']!=$this->tableName) continue;
			if ($ref['from.field']!='id') continue;

			$dataSourceRef=getDataSource($ref['to.table']);
			if (!$dataSourceRef) throw new Exception('Удаление невозможно, обнаружена ссылка на необъявленную таблицу '.$ref['to.table']);

			$emptyValue='0';
			if ($dataSourceRef->isGUID) $emptyValue='00000000-0000-0000-0000-000000000000';
			$sql=<<<SQL
update [{$ref['to.table']}] set [{$ref['to.field']}]='{$emptyValue}'
where
	[{$ref['to.field']}] in ({$idlist})
SQL;
			$this->pdo($sql);
		}
	}

/** Выполнить операцию shift, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execShift($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execShift';
		if (!$this->getPerm('write','shift',$params)) throw new Exception('У Вас нет прав на перемещение строки в таблице '.$this->tableCaption);
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
/** Выполнить операцию append, ответ вернуть в виде массива
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execAppend($params=Array()) {
		$errorMessage='Ошибка при обращении к DataSource::execAppend';
		foreach($this->getFields() as $key=>$fld) {
			$name=$fld['name'];
			if (isset($params["filter.{$name}"]) && !isset($params[$name])) $params[$name]=$params["filter.{$name}"];
		}
		if (!$this->getPerm('write','append',$params)) throw new Exception('У Вас нет прав на добавление в таблицу '.$this->tableCaption);
		
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
/** Получить поле ord, помещающее строку первой, в контексте выполнения операции
 *
 * @param	Array	$params контекст выполнения
 * @return	num значение поля ord
 */
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
/** Получить поле ord, помещающее строку последней, в контексте выполнения операции
 *
 * @param	Array	$params контекст выполнения
 * @return	num значение поля ord
 */
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
/** Получить поле ord, помещающее строку после указанной, в контексте выполнения операции
 *
 * @param	Array	$params контекст выполнения
 * @param 	boolean $isNoReorder запрет пересортировки строк при необходимости
 * @return	num значение поля ord
 */
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
/** Получить поле ord, помещающее строку перед указанной, в контексте выполнения операции
 *
 * @param	Array	$params контекст выполнения
 * @param 	boolean $isNoReorder запрет пересортировки строк при необходимости
 * @return	num значение поля ord
 */
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
	
/** Выполнить пересортировку строк в контексте
 *
 * @param	Array	$params контекст выполнения
 * @param 	boolean $isReorderAll пересортировка всех строк контекста
 */
	protected function _goReorder($params=Array(), $isReorderAll=false) {
		$errorMessage='Ошибка при обращении к DataSource::_goReorder';
		if (!$this->getField('ord')) return true;
		if (!$this->getPerm('write','reorder',$params)) throw new Exception('У Вас нет прав на пересортировку строк в таблице '.$this->tableCaption);
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
/** Вернуть SQL запрос select для вычисления текущих значений поля ord контекста выполнения
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст SQL запроса select
 */
	protected function _getReorderSelect($params=Array()) {
		$p=$params;
		$p['#request.name']='reorder';
		return $this->getSelect($p);
	}
/** Отписать в SQL сервер новое значение поля ord строки
 *
 * @param	string $id
 * @param	num $ord
 */
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
/** Вычислить кол-во строк в результате запроса для заданного контекста
 *
 * @param	Array	$params контекст выполнения
 * @return 	num кол-во строк в результате запроса для заданного контекста
 */
	public function getRowCount($params=Array()) {
		$select=$this->getSelectRowCount($params);
		$errorMessage='Ошибка выполнения SQL запроса при подсчете кол-ва строк в таблице '.$this->tableCaption;
		try {
			$rec=$this->pdoFetch($select, $errorMessage);
		}
		catch (Exception $e) {
			$this->getSelect($params);
			throw new Exception($errorMessage);
		}
		$result=$rec['rowcount'];
		if (!$result) $result=0;
		return $result;
	}
/** Вернуть SQL запрос select для вычисления кол-ва строк для заданного контекста
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст SQL запроса select
 */
	protected function getSelectRowCount($params=Array()) {
		$selectFrom=$this->getSelectFrom($params);
		$selectWhere=$this->getSelectWhere($params);
		$result=<<<SQL
select count(*) as rowcount
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

/** Вычисление порядкового номера (0 - не найдено) строки с заданным id в результате запроса
 *
 * @param	Array	$params контекст выполнения
 * @param	string	$id id разыскиваемой строки
 * @return 	num порядковый номер (0 - не найдено) строки с заданным id в результате запроса
 */
	public function getRowNumber($params=Array(), $id) {
		$select=$this->getSelectRowNumber($params, $id);
		$errorMessage='Ошибка выполнения SQL запроса при вычислении порядкового номера строки в таблице '.$this->tableCaption;
		try {
			$rec=$this->pdoFetch($select, $errorMessage);
		}
		catch (Exception $e) {
			$this->getSelect($params);
			throw new Exception($errorMessage);
		}
		$result=$rec['rownumber'];
		if (!$result) $result=0;
		return $result;
	}
/** Вернуть SQL запрос select для вычисления порядкового номера строки
 *
 * @param	Array	$params контекст выполнения
 * @param	string	$id id разыскиваемой строки
 * @return 	string текст SQL запроса select
 */
	protected function getSelectRowNumber($params=Array(), $id) {
		$driverName=$this->getDriverName();
		$result='';
		if ($driverName=='mysql') {
			$result=$this->_getSelectRowNumberMySql($params, $id);
		} 
		else if ($driverName=='sqlsrv') {
			$result=$this->_getSelectRowNumberSqlSrv($params, $id);
		} 
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		return $result;
	}
/** Вернуть SQL запрос select для вычисления порядкового номера строки, для SQL сервера MySql
 *
 * @param	Array	$params контекст выполнения
 * @param	string	$id id разыскиваемой строки
 * @return 	string текст SQL запроса select
 */
	protected function _getSelectRowNumberMySql($params=Array(), $id) {
		$selectFrom=$this->getSelectFrom($params);
		$selectWhere=$this->getSelectWhere($params);
		$selectOrderBy=$this->getSelectOrderBy($params);
		$sqlId=($this->isGUID)?$this->guid2Sql($id):$this->str2Sql($id);
		$result=<<<SQL
select
  @n:=@n+1 as n,
  case when `{$this->tableName}`.id='{$sqlId}' then @n else 0 end as rownumber
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
		$result=<<<SQL
set @n:=0;
select max(rownumber) as rownumber
from
(
{$result}
) a;
SQL;
		return $result;
	}
/** Вернуть SQL запрос select для вычисления порядкового номера строки, для SQL сервера SqlSrv
 *
 * @param	Array	$params контекст выполнения
 * @param	string	$id id разыскиваемой строки
 * @return 	string текст SQL запроса select
 */
	protected function _getSelectRowNumberSqlSrv($params=Array(), $id) {
		$selectFrom=$this->getSelectFrom($params);
		$selectWhere=$this->getSelectWhere($params);
		$selectOrderBy=$this->getSelectOrderBy($params);
		$sqlId=($this->isGUID)?$this->guid2Sql($id):$this->str2Sql($id);
		$result=<<<SQL
select
	max(rownumber) as rownumber
from
(
select
	[{$this->tableName}].id,
	ROW_NUMBER() over (order by {$selectOrderBy}) as rownumber
from
{$selectFrom}
where
	1=1
{$selectWhere}
) a
where
	id='{$sqlId}'
SQL;
		return $result;
	}
	
/** Вернуть SQL запрос select для контекста выполнения
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст SQL запроса select
 */
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
/** Вернуть SQL запрос select для контекста выполнения, для SQL сервера MySql
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст SQL запроса select
 */
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
/** Вернуть SQL запрос select для контекста выполнения, для SQL сервера SqlSrv
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст SQL запроса select
 */
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
/** Вернуть секцию fields SQL запроса select для контекста выполнения
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции fields SQL запроса select
 */
	public function getSelectFields($params=Array()) {
		return $this->autoGenGetSelectFields();
	}
/** Вернуть секцию from SQL запроса select для контекста выполнения
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции from SQL запроса select
 */
	public function getSelectFrom($params=Array()) {
		return $this->autoGenGetSelectFrom();
	}
/** Вернуть секцию where SQL запроса select для контекста выполнения
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции where SQL запроса select
 */
	public function getSelectWhere($params=Array()) {
		$result='';
		$code=$this->autoGenGetSelectWhere();
		eval($code);
		return $result;
	}
/** Вернуть секцию order by SQL запроса select для контекста выполнения
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции order by SQL запроса select
 */
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
	
/// Метод для автогенерации массива с описанием списка полей источника данных
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
/// Метод для автогенерации секции from запроса select
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
/// Метод для автогенерации секции where запроса select
	public function autoGenGetSelectWhere($fields=null, $tableName=null, $driverName=null) {
		$errorMessage='Ошибка при обращении к DataSource::autoGenGetSelectFrom';
		if (!$fields) $fields=$this->getFields();
		if (!$tableName) $tableName=$this->tableName;
		if (!$driverName) $driverName=$this->getDriverName();
		$D='$';
		$B='';
		$E='';
		if ($driverName=='mysql') {
			$B='`';
			$E='`';
		} else if ($driverName=='sqlsrv') {
			$B='[';
			$E=']';
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		$result=<<<PHP
	{$D}result='';
	if (isset({$D}params['filter.id'])) {
		{$D}value={$D}this->php2SqlIn({$D}params['filter.id']);
		if ({$D}value!='') {$D}result.="\\n"."and {$B}{$tableName}{$E}.id in ({{$D}value})";
	}
PHP;
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
	if (isset({$D}params['filter.{$fld['name']}'])) {
		{$D}value={$D}this->php2SqlIn({$D}params['filter.{$fld['name']}']);
		if ({$D}value!='') {$D}result.="\\n"."and {$fullFieldName} in ({{$D}value})";
	}
PHP;
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
/// Метод для автогенерации раздела XML описания списка полей источника данных
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
/// Метод для автогенерации раздела XML описания списка запросов источника данных
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

			$permMode=$this->permMode;
			if (!$permMode) $permMode=$tableName;
			$permOper='read';
			if ($request['permoper']) $permOper=$request['permoper'];
			if (!getPerm($permMode, $permOper)) xmlSetAttr($xmlRequest,'enabled','0');

			$xmlRequests->appendChild($xmlRequest);
		}
		$doc->appendChild($xmlRequests);
		
		$result=$doc->saveXML($xmlRequests);
		$result=str_replace("><",">\n<",$result);
		return $result;
	}
	
/// Вспомогательный метод для автогенерации
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

/** Класс кэширующего хранилища данных DataStorage
 *
 * Обеспечивает доступ к данным только на чтение. Работает с базой данных через DataSource. 
 * Кэширует начитанные данные, представляя их в виде дерева. Поддерживает автоматическую подчитку связанных данных.
 * Механизм удобен для формирования HTML страниц на стороне сервера.
 */
class DataStorage {
/** Создать экземпляр объекта DataStorage - по одному объекту на таблицу
 *
 * @param	string	$tableName таблица
 */
	function __construct($tableName) {
		$this->dataSource=getDataSource($tableName);
		$this->tableName=$this->dataSource->tableName;
		$this->items=Array();
	}
/** Получить элемент DataItem по id, если нет в базе, то возвращается специальный пустой элемент с id=false
 *
 * @param	string	$id
 * @return	DataItem строка таблицы
 */
	public function getItem($id) {
		$result=$this->items[$id];
		if (!$result) {
			if ($id!='' && $id!='0' && $id!='00000000-0000-0000-0000-000000000000') {
				$this->getItems(Array('filter.id'=>$id));
				$result=$this->items[$id];
			}
		}
		if (!$result) {
			$this->items[$id]=new DataItem($this->tableName, false);
			$result=$this->items[$id];
		}
		return $result;
	}
/** Получить список элементов DataItem по условию
 *
 * @param	Array $params параметры для передачи в DataSource.execRefresh()
 * @return	Array список элементов DataItem
 */
	public function getItems($params=Array()) {
		$lst=$this->dataSource->execRefresh($params);
		$result=Array();
		foreach($lst as &$row) {
			$id=$row['id'];
			if (!$this->items[$id]) $this->items[$id]=new DataItem($this->tableName, $id);
			$item=$this->items[$id];
			$item->values=$row;
			$result[]=$item;
		}
		return $result;
	}
/** Вычислить общее количество строк в результате запроса (без учета paginator.count)
 *
 * @param	Array $params параметры для передачи в DataSource.execRefresh()
 * @return	num общее количество строк в результате запроса (без учета paginator.count)
 */
	public function getRowCount($params=Array()) {
		return $this->dataSource->getRowCount($params);
	}
/** Вычислить порядковый номер (0 - не найдено) строки в результате запроса (без учета paginator.count)
 *
 * @param	Array $params параметры для передачи в DataSource.execRefresh()
 * @param	string $id
 * @return	num порядковый номер (0 - не найдено) строки в результате запроса (без учета paginator.count)
 */
	public function getRowNumber($params=Array(), $id) {
		return $this->dataSource->getRowNumber($params, $id);
	}
/** Проверить наличие строки в начитанном кэше
 *
 * @param	string $id
 * @return	boolean наличие строки в начитанном кэше
 */
	public function getIsItem($id) {
		return $this->items[$id]?true:false;
	}
/** Вернуть источник данных DataSource, через который происходит взаимодействие с базой данных
 *
 * @return	DataSource источник данных DataSource, через который происходит взаимодействие с базой данных
 */
	public function getDataSource() {
		return $this->dataSource;
	}
/** Вернуть описание поля
 *
 * @param	string $name имя поля
 * @return	Array описание поля
 */
	public function getField($name) {
		return $this->getDataSource()->getField($name);
	}
/** Вернуть описание связи
 *
 * @param	string $name имя связи (<таблица>.<поле>, где поле не id)
 * @return	Array описание связи
 */
	public function getRef($name) {
		return $this->getDataSource()->getRef($name);
	}
/** Загрузить недостающие строки связанной таблицы
 *
 * @param	string $refName имя связи (см _getRefNameForItem и _getRefNameForItems)
 * @param	DataItem $fromItem элемент DataItem, для которого подгружаются значения связи, если не задан, подгружаются для всех начитанных в кэше элементов
 */
	public function _loadRefItems($refName, $fromItem=null) {
		$ref=$this->getRef($refName);
		if (!$ref) throw new Exception("Попытка обращения к несуществующей ссылке '{$refName}' в таблице '{$this->tableName}'");
		if ($ref['from.table']!=$this->tableName) throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		if (!$ref['to.table']) throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		if ($ref['from.field']!='id' && $ref['to.field']!='id') throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		
		$lstItems=Array();
		if ($fromItem) {
			if (!$fromItem->refs[$refName])	$lstItems[]=$fromItem;
		}
		else {
			foreach($this->items as $item) {
				if (!$item->refs[$refName]) $lstItems[]=$item;
			}
		}
		foreach($lstItems as $item) {
			if (!$item->refs[$refName]) $item->refs[$refName]=($ref['to.field']=='id')?true:Array();
		}
		
		$refDataStorage=getDataStorage($ref['to.table']);
		$lstValues=Array();
		$values=Array();
		foreach($lstItems as $item) {
			$value=$item->get($ref['from.field']);
			if (!$value || $value=='0') continue;
			if ($value=='00000000-0000-0000-0000-000000000000') continue;
			if ($values[$value]) continue;
			if ($ref['to.field']=='id') {
				if ($refDataStorage->getIsItem($value)) continue;
			}
			$values[$value]=true;
			$lstValues[]=$value;
		}
		
		$refItems=$refDataStorage->getItems(Array("filter.{$ref['to.field']}"=>$lstValues));
		if ($ref['from.field']=='id') {
			foreach($refItems as $refItem) {
				$value=$refItem->get($ref['to.field']);
				$item=$this->getItem($value);
				if (!$item->refs[$refName]) $item->refs[$refName]=Array();
				$item->refs[$refName][]=$refItem->getId();
			}
		}
	}
/** Преобразует краткое название связи многие к одному в полное название связи
 *
 * @param	string $name допустимое краткое название связи многие к одному - имя поля текущей таблицы, по которому связь
 * @param	string полное название связи (<таблица>.<поле>)
 */
	public function _getRefNameForItem($name) {
		if ($this->getRef($name)) return $name;
		if (strpos($name,'.')===false) {
			$str=$this->tableName.'.'.$name;
			if ($this->getRef($str)) return $str;
		}
		return $name;
	}
/** Преобразует краткое название связи один ко многим в полное название связи
 *
 * @param	string $name допустимое краткое название связи один ко многим - имя связанной таблицы, если с этой таблицей есть только одна связь
 * @param	string полное название связи (<таблица>.<поле>)
 */
	public function _getRefNameForItems($name) {
		global $_refNameForItems;
		if ($this->getRef($name)) return $name;
		if ($_refNameForItems[$name]) return $_refNameForItems[$name];
		if (strpos($name,'.')===false) {
			$result='';
			$resCount=0;
			foreach($this->getDataSource()->getReferences() as $refName=>$ref) {
				if ($ref['from.table']!=$this->tableName) continue;
				if ($ref['from.field']!='id') continue;
				if ($ref['to.table']==$name) {
					$result=$refName;
					$resCount++;
				}
			}
			if ($result && $resCount==1) {
				$_refNameForItems[$name]=$result;
				return $result;
			}
		}
		return $name;
	}
/// Кэш для ускорения преобразования кратких названий связи в полные названия
	protected $_refNameForItems=Array();
	
/// Таблица
	protected $tableName='';
/// Источник данных DataSource
	protected $dataSource=null;
/// Кэш начитанных элементов DataItem
	protected $items=null;
}

/** Класс элемента строки кэширующего хранилища данных DataStorage
 */
class DataItem {
/** Создать экземпляр объекта DataItem
 *
 * @param	string	$tableName таблица
 * @param	string	$id
 */
	function __construct($tableName, $id) {
		$this->tableName=$tableName;
		$this->getDataStorage();
		$this->id=$id;
		$this->values=Array();
		$this->refs=Array();
	}
/** Вернуть id элемента
 *
 * @return	string id элемента
 */
	public function getId() {
		return $this->id;
	}
/** Вернуть значение поля
 *
 * @param	string	$fieldName имя поля
 * @return	anytype значение поля
 */
	public function get($fieldName) {
		if ($fieldName=='id') return $this->getId();
		if (!$this->getDataStorage()->getField($fieldName)) throw new Exception("Попытка обращения к несуществующему полю '{$this->tableName}.{$fieldName}'");
		return $this->values[$fieldName];
	}
/** Вернуть преобразованное к HTML значение поля
 *
 * @param	string	$fieldName имя поля
 * @return	string преобразованное к HTML значение поля
 */
	public function getHtml($fieldName) {
		return str2Html($this->get($fieldName));
	}
/** Вернуть преобразованное к HTML значение поля типа date
 *
 * @param	string	$fieldName имя поля
 * @return	string преобразованное к HTML значение поля типа date
 */
	public function getDateHtml($fieldName) {
		return date2Html($this->get($fieldName));
	}
/** Вернуть преобразованное к XML атрибуту значение поля
 *
 * @param	string	$fieldName имя поля
 * @return	string преобразованное к XML атрибуту значение поля
 */
	public function getAttr($fieldName) {
		return str2Attr($this->get($fieldName));
	}
/** Вернуть преобразованное к вставке в строку JavaScript значение поля
 *
 * @param	string	$fieldName имя поля
 * @return	string преобразованное к вставке в строку JavaScript значение поля
 */
	public function getJavaScript($fieldName) {
		return str2JavaScript($this->get($fieldName));
	}
/** Вернуть связанный DataItem для связи многие к одному (to.field='id')
 *
 * @param	string	$refName имя связи (можно краткое)
 * @param	boolean	$isLoadAll если true то пытается подгрузить одним запросом все связанные элементы для начитанного кэша
 * @return	DataItem связанный DataItem для связи многие к одному
 */
	public function getRefItem($refName, $isLoadAll=false) {
		$refName=$this->getDataStorage()->_getRefNameForItem($refName);
		$ref=$this->getDataStorage()->getRef($refName);
		if (!$ref) throw new Exception("Попытка обращения к несуществующей ссылке '{$refName}' в таблице '{$this->tableName}'");
		if ($ref['from.table']!=$this->tableName) throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		if (!$ref['to.table']) throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		if ($ref['to.field']!='id') throw new Exception("Попытка обращения к множественной ссылке '{$refName}' как к одиночной в таблице '{$this->tableName}'");

		$refDataStorage=getDataStorage($ref['to.table']);
		$value=$this->get($ref['from.field']);
		
		if (!$this->refs[$refName]) {
			if ($isLoadAll) {
				$this->getDataStorage()->_loadRefItems($refName);
			}
			if (!$this->refs[$refName]) $this->refs[$refName]=true;
		}
		return $refDataStorage->getItem($value);
	}
/** Вернуть список связанных DataItem для связи один ко многим (from.field='id')
 *
 * @param	string	$refName имя связи (можно краткое)
 * @param	boolean	$isLoadAll если true то пытается подгрузить одним запросом все связанные элементы для начитанного кэша
 * @return	Array список связанных DataItem
 */
	public function getRefItems($refName, $isLoadAll=false) {
		$refName=$this->getDataStorage()->_getRefNameForItems($refName);
		$ref=$this->getDataStorage()->getRef($refName);
		if (!$ref) throw new Exception("Попытка обращения к несуществующей ссылке '{$refName}' в таблице '{$this->tableName}'");
		if ($ref['from.table']!=$this->tableName) throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		if (!$ref['to.table']) throw new Exception("Попытка обращения к некорректной ссылке '{$refName}' в таблице '{$this->tableName}'");
		if ($ref['to.field']=='id') throw new Exception("Попытка обращения к одиночной ссылке '{$refName}' как к множественной в таблице '{$this->tableName}'");
		if ($ref['from.field']!='id') throw new Exception("Попытка обращения к одиночной ссылке '{$refName}' как к множественной в таблице '{$this->tableName}'");

		if (!$this->refs[$refName]) {
			if ($isLoadAll) {
				$this->getDataStorage()->_loadRefItems($refName);
			}
			else {
				$this->getDataStorage()->_loadRefItems($refName, $this);
			}
		}
		
		$refDataStorage=getDataStorage($ref['to.table']);
		$result=Array();
		$lst=&$this->refs[$refName];
		if (is_array($lst)) foreach($lst as $refId) {
			$result[]=$refDataStorage->getItem($refId);
		}
		return $result;
	}
/** Очистить кэш связанных элементов, дабы при следующем чтении данные заново начитались
 *
 * @param	string	$refName имя связи (можно краткое), если не задано, то очистить кэш всех связей для элемента
 */
	public function clearRefItems($refName='') {
		if ($refName) {
			unset($this->refs[$refName]);
		}
		else {
			$this->refs=Array();
		}
	}
/** Вернуть DataStorage элемента
 *
 * @return	DataStorage
 */
	public function getDataStorage() {
		return getDataStorage($this->tableName);
	}
/// Таблица
	protected $tableName='';
/// Id
	protected $id=null;
/// Значения полей
	public $values=null;
/// Кэш связей
	public $refs=null;
}

/** Получить объект источника данных DataSource по имени
 *
 * Объекты подгружаются динамически по требованию. Имя согласовано с именем файла, в котором объект объявлен и создан.
 * @param	string	$name источник данных
 * @return	DataSource объект источника данных
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

	$fileNameAutoGen=pathConcat(getCfg('path.root'), getCfg('path.root.datasources'), 'autogen', "{$name}-autogen.php");
	if (file_exists($fileNameAutoGen)) {
		$obj=include_once($fileNameAutoGen);
		if ($obj instanceof DataSource) $registerDataSource[$name]=$obj;
	}
	
	$fileNameUserDef=pathConcat(getCfg('path.root'), getCfg('path.root.datasources'), "{$name}.php");
	if (file_exists($fileNameUserDef)) {
		$obj=include_once($fileNameUserDef);
		if ($obj instanceof DataSource) $registerDataSource[$name]=$obj;
	}
	
	if (!$registerDataSource[$name]) throw new Exception("Недопустимое имя источника данных '{$name}'");
	return $registerDataSource[$name];
}
/// Кэш загруженных объектов DataSource
$registerDataSource=Array();
/** Получить объект хранилища данных DataStorage по имени
 *
 * @param	string	$tableName таблица
 * @return	DataStorage
 */
function getDataStorage($tableName) {
	global $_registerDataStorage;
	if (!$_registerDataStorage[$tableName]) $_registerDataStorage[$tableName]=new DataStorage($tableName);
	return $_registerDataStorage[$tableName];
}
/// Кэш загруженных объектов DataStorage
$_registerDataStorage=Array();
