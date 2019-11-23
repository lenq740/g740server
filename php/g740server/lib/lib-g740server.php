<?php
/**
 * @file
 * G740Server, библиотека функций - расширение базового набора под server G740
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

require_once('dsconnector.php');
require_once('lib-base.php');

//------------------------------------------------------------------------------
// Преобразования для подстановок
//------------------------------------------------------------------------------
/** Преобразовать данные из G740 в PHP
 *
 * @param	string	$value данные в формате G740
 * @param	string	$type тип данных в формате G740
 * @return	anytype преобразованные данные
 */
function g2php($value, $type) {
	$result=$value;
	if ($type=='date') {
		try {
			$result=null;
			$value=trim($value);
			$n=mb_strlen($value,'utf-8');
			if (($n==10) && (mb_substr($value,4,1)=='-') && (mb_substr($value,7,1)=='-')) {
				$y=mb_substr($value,0,4);
				$m=mb_substr($value,5,2);
				$d=mb_substr($value,8,2);
				$result=new DateTime();
				$result->setDate($y,$m,$d);
			}
		}
		catch (Exception $e) {
			$result=null;
		}
	}
	if ($type=='check') {
		$result=($value=='1');
	}
	return $result;
}
/** Преобразовать данные из PHP в G740
 *
 * @param	anytype	$value данные
 * @param	anytype	$type тип данных
 * @return	string данные преобразованные в формат G740
 */
function php2g($value, $type='') {
	$result=$value;
	if ($value===null) $result='';
	if (!$type) {
		$t=gettype($value);
		if (t=='boolean') $type='check';
		if ((t=='integer') || (t=='double')) $type='num';
		if (t=='object') {
			if (get_class($value)=='DateTime') $type='date';
		}
	}
	if ($type=='string') {
	} else if ($type=='date') {
		try {
			$t=gettype($value);
			if ($value==null) $result='';
			else {
				$t=gettype($value);
				if ($t=='string') {
					$result=mb_substr($value,0,10);
				} else if (t=='object' && get_class($value)=='DateTime') {
					$result=$value->format('Y-m-d');
				} else {
					$result='';
				}
			}
		}
		catch (Exception $e) {
			$result='';
		}
	} else if ($type=='check') {
		$result='0';
		if ($value) $result='1';
	}
	return $result;
}

//------------------------------------------------------------------------------
// Работа с запросами
//------------------------------------------------------------------------------
/// XML документ - поступивший HTTP запрос
global $docRequest;
$docRequest=null;
/// корневой XML узел запроса
global $rootRequest;
$rootRequest=null;

/** Прочитать запрос из php://input в $docRequest
 *
 * @return	Xml docRequest
 */
function initDocRequest() {
	global $docRequest;
	global $rootRequest;
	$docRequest = new DOMDocument("1.0", "utf-8");
	try {
		$docRequest->load('php://input');
	}
	catch (Exception $e) {
	}
	if (!is_object($docRequest)) throw new Exception('Системная ошибка! Не передан запрос!');
	$rootRequest=$docRequest->documentElement;
	if (!is_object($rootRequest)) throw new Exception('Системная ошибка! Не передан запрос!');
	if ($rootRequest->nodeName!='root') throw new Exception('Системная ошибка! У xml документа запроса корневой узел не root!');
	if (xmlGetAttr($rootRequest,'type','')!='g740') throw new Exception('Системная ошибка! У xml документа запроса атрибут type не g740!');
	return $docRequest;
}
/** Построить ассоциативный массив из параметров запроса
 *
 * @param	Xml		$xmlRequest XML узел запроса
 * @return	Array начитанные параметры запроса
 */
function getParams($xmlRequest) {
	$result=Array();
	$result['id']=xmlGetAttr($xmlRequest,'id','');
	for ($xmlParam=$xmlRequest->firstChild; $xmlParam!=null; $xmlParam=$xmlParam->nextSibling) {
		if ($xmlParam->nodeName!='param') continue;
		$name=xmlGetAttr($xmlParam, 'name', '');
		if (!$name) continue;
		if (substr($name,0,1)=='#') continue;
		$t=xmlGetAttr($xmlParam,'type','');
		$value='';
		for ($xmlParamChild=$xmlParam->firstChild; $xmlParamChild!=null; $xmlParamChild=$xmlParamChild->nextSibling) {
			if ($xmlParamChild->nodeType==XML_CDATA_SECTION_NODE || $xmlParamChild->nodeType==XML_TEXT_NODE) {
				$value=$xmlParamChild->data;
				break;
			}
		}
		$result[$name]=g2php($value,$t);
	}
	return $result;
}

//------------------------------------------------------------------------------
// Работа с ответами
//------------------------------------------------------------------------------
/// ассоциативный массив для передаче от источника данных в форму важных событий, на которое форма может захотеть среагировать
global $events;
$events=Array();
/// вспомогательный XML документ - используется для формирования ответа
global $docTemp;
$docTemp=new DOMDocument("1.0", "utf-8");
$docTemp->loadXml('<temp></temp>');

/// XMLWriter для формирования ответа
global $objResponseWriter;
$objResponseWriter=null;

/** Записать в $objResponseWriter начало ответа
 *
 * @return	XMLWriter $objResponseWriter
 */
function initObjResponseWriter() {
	global $objResponseWriter;
	$objResponseWriter=new XMLWriter();
	$objResponseWriter->openMemory();
	$objResponseWriter->startDocument('1.0', 'UTF-8');
	$objResponseWriter->startElement('root');
	$objResponseWriter->writeAttribute('type','g740');
	return $objResponseWriter;
}
/** Записать в $objResponseWriter очередной кусок ответа
 *
 * @param	string	$str
 * @return	XMLWriter objResponseWriter
 */
function writeXml($str) {
	global $objResponseWriter;
	$objResponseWriter->writeRaw($str);
}
/** Записать в $objResponseWriter описание экранной формы
 *
 * @param	string	$fileName
 * @param	Array	$params ассоциативный массив для подстановок в описании формы
 * @return	XMLWriter objResponseWriter
 */
function writeXmlForm($fileName, $params=null) {
	global $pathXmlForm;
	$fileNameFull=$pathXmlForm.'/'.$fileName;
	if (!file_exists($fileNameFull)) throw new Exception('Не найден файл с XML описанием экранной формы '.$fileNameFull);
	$strForm=file_get_contents($fileNameFull);
	if ($params) {
		$from=Array();
		$to=Array();
		foreach($params as $key=>$value) {
			if (substr($key,0,1)=='%') {
				$from[]=str2Attr($key);
				$to[]=str2Attr($value);
			} else {
				$from[]=$key;
				$to[]=$value;
			}
		}
		$strForm=str_replace($from, $to, $strForm);
	}
	return writeXml($strForm);
}
/** Парсер строки в XML элемент документа $docTemp
 *
 * @param	string	$strXml текст XML
 * @return	Xml узел $docTemp
 */
function strXml2DomXml($strXml) {
	global $docTemp;
	$dd=new DOMDocument("1.0", "utf-8");
	$dd->preserveWhiteSpace=false;
	try {
		$dd->loadXML($strXml);
	}
	catch (Exception $e) {
		throw new Exception('Ошибка в XML описании '.$strXml);
	}
	$result=$docTemp->importNode($dd->documentElement, true);
	unset($dd);
	return $result;
}
/** Преобразование XML узла в строковое представление
 *
 * @param	Xml	$domXml узел $docTemp
 * @return	strXml строковое представление
 */
function domXml2StrXml($domXml) {
	global $docTemp;
	return $docTemp->saveXML($domXml);
}

//------------------------------------------------------------------------------
// Backup, Restore
//------------------------------------------------------------------------------
/** Класс полезных утилит для работы с базами данных
 *
 * backup, restore, оптимизация таблиц и т.д.
 */
class DBUtility extends DSConnector{
/// имя XML файла для backup и restore
	public $xmlFileName='';

/// имя SQL файла для backup
	public $sqlFileName='';
	
/// экземпляр XMLWriter
	protected $xmlWriter=null;
/// экземпляр XMLReader
	protected $xmlReader=null;
	
/** Получить текущий или создать по имени файла объект XMLWriter
 *
 * @return	XMLWriter
 */
	public function getXmlWriter() {
		if (!$this->xmlWriter) {
			$this->xmlWriter = new XMLWriter();
			$this->xmlWriter->openURI($this->xmlFileName);
			$this->xmlWriter->setIndent(true);
			$this->xmlWriter->setIndentString ("\t");
			$this->xmlWriter->startDocument('1.0','utf-8');
			$this->xmlWriter->startElement('tables');
		}
		return $this->xmlWriter;
	}
/** Получить текущий или создать по имени файла объект XMLReader
 *
 * @return	XMLReader
 */
	public function getXmlReader() {
		if (!$this->xmlReader) {
			$this->xmlReader=new XMLReader();
			$this->xmlReader->open($this->xmlFileName,'utf-8');
		}
		return $this->xmlReader;
	}
/** Все отписать в файл и закрыть XMLWriter
 */
	public function xmlSave() {
		if ($this->xmlWriter) {
			$this->xmlWriter->endElement();
			$this->xmlWriter->endDocument();
			$this->xmlWriter->flush();
		}
		unset($this->xmlWriter);
		unset($this->xmlReader);
	}

/** Сохранение таблицы в XML
 *
 * @param	Array	$para параметры
 *
 * - $para['tableName']
 * - $para['isEcho']
 */
	public function saveTable($para) {
		if (!$para) throw new Exception('Не задан para');
		$xmlWriter=$this->getXmlWriter();
		$tableName=$para['tableName'];
		$isEcho=$para['isEcho'];
		$isOptimize=$para['isOptimize'];
		
		$xmlWriter->startElement('table');
		$xmlWriter->writeAttribute('name', $tableName);

		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$sql="select * from `{$tableName}`";
		}
		else if ($driverName=='sqlsrv') {
			$sql="select * from [dbo].[{$tableName}]";
		}
		else if ($driverName=='pgsql') {
			$sql=<<<SQL
select * from "{$tableName}"
SQL;
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		$q=$this->pdo($sql);
		$i=0;
		while ($rec=$this->pdoFetch($q))
		{
			$xmlWriter->startElement('row');
			foreach($rec as $key=>$value) {
				$s=$value;
				$xmlWriter->writeAttribute(strtolower($key),$s);
			}
			$xmlWriter->endElement();
			$i++;
			if ($isEcho && ($i % 500)==0) {
				$xmlWriter->flush();
				echo '. '; flush();
			}
		}
		$xmlWriter->endElement();
		$xmlWriter->flush();

		if ($isEcho) {
			echo 'optimize '; flush();
		}
		$this->opimizeTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}

/** Сохранение таблицы в виде скрипта SQL
 *
 * @param	Array	$para параметры
 *
 * - $para['tableName']
 * - $para['isEcho']
 */
	public function exportTable($para) {
		if (!$para) throw new Exception('Не задан para');
		$tableName=$para['tableName'];
		$isEcho=$para['isEcho'];

		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$sql="select * from `{$tableName}`";
		}
		else if ($driverName=='sqlsrv') {
			$sql="select * from [dbo].[{$tableName}]";
		}
		else if ($driverName=='pgsql') {
			$sql=<<<SQL
select * from "{$tableName}"
SQL;
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		
		if (!$handle=fopen($this->sqlFileName, 'a')) throw new Exception("Не удалось открыть файл '{$this->sqlFileName}'");
		try {
			$result=$this->beforeExportTable($tableName);
			if (fwrite($handle, "\n".$result) === FALSE) throw new Exception("Не удалось произвести запись файл '{$this->sqlFileName}'");

			$q=$this->pdo($sql);
			$i=0;
			while ($rec=$this->pdoFetch($q)) {
				$sqlFields='';
				$sqlValues='';
				$sqlDelim='';
				foreach($rec as $sqlName=>$value) {
					if ($driverName=='mysql') {
						$sqlFields.=$sqlDelim . '`' . $sqlName . '`';
						$sqlValues.=$sqlDelim . ((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
						$sqlDelim=',';
					}
					else if ($driverName=='sqlsrv') {
						if ($value==='') continue;
						$sqlFields.=$sqlDelim . '[' . $sqlName . ']';
						$sqlValues.=$sqlDelim . ((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
						$sqlDelim=',';
					}
					else if ($driverName=='pgsql') {
						$sqlFields.=$sqlDelim . '"' . $sqlName . '"';
						$sqlValues.=$sqlDelim . ((gettype($value)=='NULL')?"null":("'".$this->php2Sql($value)."'"));
						$sqlDelim=',';
					}
				}
				if ($driverName=='mysql') {
					$result="insert into `{$tableName}` ({$sqlFields}) values ({$sqlValues});";
				}
				else if ($driverName=='sqlsrv') {
					if ($sqlFields) {
						$result="insert into [{$tableName}] ({$sqlFields}) values ({$sqlValues});";
					}
					else {
						$result="insert into [{$tableName}] default values;";
					}
				}
				else if ($driverName=='pgsql') {
					if ($sqlFields) {
						$result='insert into "'.$tableName.'" ('.$sqlFields.') values ('.$sqlValues.');';
					}
					else {
						$result='insert into "'.$tableName.'" default values;';
					}
				}
				if (fwrite($handle, "\n".$result) === FALSE) throw new Exception("Не удалось произвести запись файл '{$this->sqlFileName}'");
				$i++;
				if (($i % 500)==0) {
					$i=0;
					if ($isEcho) {
						echo '. '; flush();
					}
				}
			}
			$result=$this->afterExportTable($tableName);
			if (fwrite($handle, "\n".$result."\n"."\n") === FALSE) throw new Exception("Не удалось произвести запись файл '{$this->sqlFileName}'");
		}
		finally {
			fclose($handle);
		}
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}
/** Скрипт предварительной обработки таблицы при сохранении в виде скрипта SQL
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function beforeExportTable($tableName) {
		$result='';
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$result=$this->beforeExportTableMySql($tableName);
		}
		else if ($driverName=='sqlsrv') {
			$result=$this->beforeExportTableSqlSrv($tableName);
		}
		else if ($driverName=='pgsql') {
			$result=$this->beforeExportTablePgSql($tableName);
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		return $result;
	}
/** Скрипт предварительной обработки таблицы при сохранении в виде скрипта SQL для MySql
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function beforeExportTableMySql($tableName) {
		$result=<<<SQL
lock table `{$tableName}` write;
alter table `{$tableName}` disable keys;
truncate table `{$tableName}`;
SQL;
		return $result;
	}
/** Скрипт предварительной обработки таблицы при сохранении в виде скрипта SQL для SqlSrv
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function beforeExportTableSqlSrv($tableName) {
		$result=<<<SQL
set dateformat ymd;
truncate table [dbo].[{$tableName}];
SQL;
		$sql=<<<SQL
select count(*) as n
from
	sys.identity_columns
where
	[object_id] = OBJECT_ID('[dbo].[{$tableName}]')
SQL;
		$rec=$this->pdoFetch($sql);
		if ($rec['n']) {
			$result.="\n".<<<SQL
set identity_insert [dbo].[{$tableName}] on;
SQL;
		}
		return $result;
	}
/** Скрипт предварительной обработки таблицы при сохранении в виде скрипта SQL для PgSql
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function beforeExportTablePgSql($tableName) {
		$result=<<<SQL
SET CONSTRAINTS ALL DEFERRED;
SET session_replication_role = replica;
delete from "{$tableName}";
SQL;
		return $result;
	}
/** Скрипт постобработки таблицы при сохранении в виде скрипта SQL
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function afterExportTable($tableName) {
		$result='';
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$result=$this->afterExportTableMySql($tableName);
		}
		else if ($driverName=='sqlsrv') {
			$result=$this->afterExportTableSqlSrv($tableName);
		}
		else if ($driverName=='pgsql') {
			$result=$this->afterExportTablePgSql($tableName);
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		return $result;
	}
/** Скрипт постобработки таблицы при сохранении в виде скрипта SQL для MySql
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function afterExportTableMySql($tableName) {
		$result=<<<SQL
alter table `{$tableName}` enable keys;
unlock tables;
check table {$tableName};
optimize table {$tableName};
SQL;

		$sql=<<<SQL
select count(*) as n 
from 
	information_schema.COLUMNS
where 
	TABLE_SCHEMA=database() 
and TABLE_NAME='{$tableName}' 
and COLUMN_NAME='id'
and EXTRA like '%auto_increment%'
SQL;
		$rec=$this->pdoFetch($sql);
		$isAutoIncrement=($rec['n']==1);
		if ($isAutoIncrement) {
			$rec=$this->pdoFetch("select max(id) as id from `{$tableName}`");
			$maxId=$rec['id'];
			if (!$maxId) $maxId=0;
			$result.="\n".<<<SQL
alter table `{$tableName}` AUTO_INCREMENT={$maxId};
SQL;
		}
		
		return $result;
	}
/** Скрипт постобработки таблицы при сохранении в виде скрипта SQL для SqlSrv
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function afterExportTableSqlSrv($tableName) {
		$result='';
		$sql=<<<SQL
select count(*) as n
from
	sys.identity_columns
where
	[object_id] = OBJECT_ID('[dbo].[{$tableName}]')
SQL;
		$rec=$this->pdoFetch($sql);
		if ($rec['n']) {
			$result=<<<SQL
set identity_insert [dbo].[{$tableName}] off;
SQL;
		}
		return $result;
	}
/** Скрипт постобработки таблицы при сохранении в виде скрипта SQL для PgSql
 *
 * @param	String $tableName
 * @return	String скрипт
 */
	protected function afterExportTablePgSql($tableName) {
		$result='';
		$sql=<<<SQL
select count(*) as n
from
	pg_class 
where
	relname = '{$tableName}_id_seq'
SQL;
		$rec=$this->pdoFetch($sql);
		if ($rec['n']) {
			$result.="\n".<<<SQL
select setval('{$tableName}_id_seq', GREATEST(1,(select max(id) from "{$tableName}")));
SQL;
		}
		return $result;
	}
	
/** Загрузка списка таблиц из XML
 *
 * @param	Array $para параметры
 *
 * - $para['tables']
 * - $para['isEcho']
 */
	public function loadTables($para) {
		if (!$para) throw new Exception('Не задан para');
		unset($this->xmlReader);
		$xmlReader=$this->getXmlReader();
		$isEcho=$para['isEcho'];
		$tables=$para['tables'];
		$tbl=Array();
		foreach($tables as $key=>$tableName) $tbl[strtolower($tableName)]=true;

		$driverName=$this->getDriverName();
		if ($driverName=='pgsql') {
			$sql='SET CONSTRAINTS ALL DEFERRED;';
			$this->pdo($sql);
			$sql="SET session_replication_role = replica;";
			$this->pdo($sql);
		}
		
		while(true) {
			if($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName=='table') {
				$tableName=strtolower($xmlReader->getAttribute('name'));
				if ($tbl[$tableName]) {
					$p=Array();
					$p['tableName']=$tableName;
					$p['isEcho']=$isEcho;
					if ($isEcho) {
						echo '<div class="message">'.$tableName.': '; flush();
					}
					$this->loadTable($p);
					if ($isEcho) {
						echo 'Ok!</div>'; flush();
						echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					}
					unset($p);
					continue;
				}
				else {
					$xmlReader->next();
					continue;
				}
			}
			if (!$xmlReader->read()) break;
		}
	}

/** Загрузка таблицы из XML
 *
 * @param	Array $para параметры
 *
 * - $para['tableName']
 * - $para['fields']
 * - $para['isEcho']
 */
	protected function loadTable($para) {
		if (!$para) throw new Exception('Не задан para');
		$xmlReader=$this->getXmlReader();
		$tableName=$para['tableName'];
		$fields=$para['fields'];
		$isEcho=$para['isEcho'];
		if (!$fields) $fields=Array();

		if ($xmlReader->nodeType != XMLReader::ELEMENT) throw new Exception('Недопустимый текущий элемент');
		if ($xmlReader->localName!='table') throw new Exception('Недопустимый текущий элемент');
		if (strtolower($xmlReader->getAttribute('name'))!=$tableName) throw new Exception('Недопустимый текущий элемент');

		if ($isEcho) {
			echo 'очистка '; flush();
		}
		$this->beforeLoadTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}

		$pdoDB=$this->getPDO();
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$sql="select * from `{$tableName}`";
		}
		else if ($driverName=='sqlsrv') {
			$isIdentityInsert=false;
			$sql=<<<SQL
select count(*) as n
from
	sys.identity_columns
where
	[object_id] = OBJECT_ID('[dbo].[{$tableName}]')
SQL;
			$rec=$this->pdoFetch($sql);
			if ($rec['n']) $isIdentityInsert=true;
			
			$sql="select * from [dbo].[{$tableName}]";
		}
		else if ($driverName=='pgsql') {
			$sql=<<<SQL
select * from "{$tableName}"
SQL;
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		
		
		$i=0;
		$xmlReader->read();
		while(true) {
			if($xmlReader->nodeType == XMLReader::ELEMENT) {
				if ($xmlReader->localName=='table') break;
				if ($xmlReader->localName=='row'){
					$sqlFields='';
					$sqlValues='';
					$sqlDelim='';
					
					while ($xmlReader->moveToNextAttribute()) {
						$fieldName=strtolower($xmlReader->localName);
						$value=$xmlReader->value;
						if ($value=='') continue;

						if ($driverName=='mysql') {
							$sqlFields=$sqlFields . $sqlDelim . '`'.$fieldName.'`';
							$value=$this->str2Sql($value);
							$sqlValues=$sqlValues . $sqlDelim . "'" . $value . "'";
							$sqlDelim=',';
						}
						else if ($driverName=='sqlsrv') {
							$sqlFields=$sqlFields . $sqlDelim . '['.$fieldName.']';
							$value=$this->str2Sql($value);
							$sqlValues=$sqlValues . $sqlDelim . "'" . $value . "'";
							$sqlDelim=',';
						}
						else if ($driverName=='pgsql') {
							$sqlFields=$sqlFields . $sqlDelim . '"'.$fieldName.'"';
							$value=$this->str2Sql($value);
							$sqlValues=$sqlValues . $sqlDelim . "'" . $value . "'";
							$sqlDelim=',';
						}
						else {
							throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
						}
					}
					if ($sqlDelim) {
						if ($driverName=='mysql') {
							$sqlInsert="insert into `{$tableName}` ({$sqlFields}) values ({$sqlValues})";
						}
						else if ($driverName=='sqlsrv') {
							$sqlInsert=<<<SQL
insert into [dbo].[{$tableName}] ({$sqlFields}) values ({$sqlValues});
SQL;
							if ($isIdentityInsert) {
								$sqlInsert="set identity_insert [dbo].[{$tableName}] ON;"."\n".$sqlInsert;
							}
						}
						else if ($driverName=='pgsql') {
							$sqlInsert=<<<SQL
insert into "{$tableName}" ({$sqlFields}) values ({$sqlValues});
SQL;
						}
						else {
							throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
						}
						$this->pdo($sqlInsert, "Ошибка при вставке строки в таблицу {$tableName}");
					}
					if ($isEcho && ($i % 500)==0) {
						if ($driverName=='mysql' || $driverName=='sqlsrv') {
							$pdoDB->commit();
							echo '. '; flush();
							$pdoDB->beginTransaction();
						}
						else if ($driverName=='pgsql') {
							echo '. '; flush();
						}
						else {
							throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
						}
					}
					$i++;
				}
			}
			if (!$xmlReader->read()) break;
		}
		if ($driverName=='mysql' || $driverName=='sqlsrv') {
			if ($pdoDB->inTransaction()) $pdoDB->commit();
			if (!$pdoDB->inTransaction()) $pdoDB->beginTransaction();
		}
		else if ($driverName=='pgsql') {
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		if ($isEcho) {
			echo 'оптимизация '; flush();
		}
		$this->afterLoadTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}

/** Обработка таблицы перед загрузкой в нее данных
 *
 * @param	String $tableName
 */
	protected function beforeLoadTable($tableName) {
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$this->beforeLoadTableMySql($tableName);
		}
		else if ($driverName=='sqlsrv') {
			$this->beforeLoadTableSqlSrv($tableName);
		}
		else if ($driverName=='pgsql') {
			$this->beforeLoadTablePgSql($tableName);
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
	}
/** Обработка таблицы перед загрузкой в нее данных для MySql
 *
 * @param	String $tableName
 */
	protected function beforeLoadTableMySql($tableName) {
		$this->pdo("lock table `{$tableName}` write");
		$this->pdo("alter table `{$tableName}` disable keys");
		$this->pdo("truncate table `{$tableName}`");
	}
/** Обработка таблицы перед загрузкой в нее данных для SqlSrv
 *
 * @param	String $tableName
 */
	protected function beforeLoadTableSqlSrv($tableName) {
		$this->pdo("truncate table [dbo].[{$tableName}]");
	}
/** Обработка таблицы перед загрузкой в нее данных для PgSql
 *
 * @param	String $tableName
 */
	protected function beforeLoadTablePgSql($tableName) {
		$sql=<<<SQL
delete from "{$tableName}"
SQL;
		$this->pdo($sql);
	}
	
/** Обработка таблицы после загрузки в нее данных
 *
 * @param	String $tableName
 */
	protected function afterLoadTable($tableName) {
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$this->afterLoadTableMySql($tableName);
		}
		else if ($driverName=='sqlsrv') {
			$this->afterLoadTableSqlSrv($tableName);
		}
		else if ($driverName=='pgsql') {
			$this->afterLoadTablePgSql($tableName);
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
	}
/** Обработка таблицы после загрузки в нее данных для MySql
 *
 * @param	string $tableName
 */
	protected function afterLoadTableMySql($tableName) {
		$this->pdo("alter table `{$tableName}` enable keys");
		$this->pdo("unlock tables");
		$this->opimizeTable($tableName);
		
		$sql=<<<SQL
select count(*) as n 
from 
	information_schema.COLUMNS
where 
	TABLE_SCHEMA=database() 
and TABLE_NAME='{$tableName}' 
and COLUMN_NAME='id'
and EXTRA like '%auto_increment%'
SQL;
		$rec=$this->pdoFetch($sql);
		$isAutoIncrement=($rec['n']==1);
		if ($isAutoIncrement) {
			$rec=$this->pdoFetch("select max(id) as id from `{$tableName}`");
			$maxId=$rec['id'];
			if (!$maxId) $maxId=0;
			$this->pdo("ALTER TABLE `{$tableName}` AUTO_INCREMENT={$maxId}");
		}
	}
/** Обработка таблицы после загрузки в нее данных для SqlSrv
 *
 * @param	String $tableName
 */
	protected function afterLoadTableSqlSrv($tableName) {
	}
/** Обработка таблицы после загрузки в нее данных для PgSql
 *
 * @param	String $tableName
 */
	protected function afterLoadTablePgSql($tableName) {
		$sql=<<<SQL
select count(*) as n
from
	pg_class 
where
	relname = '{$tableName}_id_seq'
SQL;
		$rec=$this->pdoFetch($sql);
		if ($rec['n']) {
			$sql=<<<SQL
select setval('{$tableName}_id_seq', GREATEST(1,(select max(id) from "{$tableName}")));
SQL;
			$this->pdo($sql);
		}
	}
	
/** Оптимизация таблицы
 *
 * @param	String	$tableName
 */
	public function opimizeTable($tableName) {
		$driverName=$this->getDriverName();
		if ($driverName=='mysql') {
			$this->opimizeTableMySql($tableName);
		}
		else if ($driverName=='sqlsrv') {
			$this->opimizeTableSqlSrv($tableName);
		}
		else if ($driverName=='pgsql') {
			$this->opimizeTablePgSql($tableName);
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
	}
/** Оптимизация таблицы для MySql
 *
 * @param	String	$tableName
 */
	protected function opimizeTableMySql($tableName) {
		$sql='check table '.$tableName;
		$q=$this->pdo($sql);
		$sql='optimize table '.$tableName;
		$q=$this->pdo($sql);
	}
/** Оптимизация таблицы для SqlSrv
 *
 * @param	String	$tableName
 */
	protected function opimizeTableSqlSrv($tableName) {
	}
/** Оптимизация таблицы для PgSql
 *
 * @param	String	$tableName
 */
	protected function opimizeTablePgSql($tableName) {
	}

/// Выгрузка описания структуры базы в XML
	public function exportDataStruToXml() {
		$xmlWriter = new XMLWriter();
		$xmlWriter->openURI($this->xmlFileName);
		$xmlWriter->setIndent(true);
		$xmlWriter->setIndentString ("\t");
		$xmlWriter->startDocument('1.0','utf-8');
		$xmlWriter->startElement('root');
		
		
		$lst=Array();
		$sql=<<<SQL
select * from systablecategory order by ord, id
SQL;
		$qSysTableCategory=$this->pdo($sql);
		while ($recSysTableCategory=$this->pdoFetch($qSysTableCategory)) {
			$klssystablecategory=$this->str2Sql($recSysTableCategory['id']);
			if (!isset($lst[$klssystablecategory])) {
				$lst[$klssystablecategory]=Array();
			}
			$lst[$klssystablecategory]['info']=$recSysTableCategory;
			$lst[$klssystablecategory]['child']=Array();
		}

		$sql=<<<SQL
select systable.* 
from 
	systable
		join systablecategory on systablecategory.id=systable.klssystablecategory
order by 
	systable.klssystablecategory, systable.tablename, systable.id
SQL;
		$qSysTable=$this->pdo($sql);
		while ($recSysTable=$this->pdoFetch($qSysTable)) {
			$klssystablecategory=$this->str2Sql($recSysTable['klssystablecategory']);
			if (!isset($lst[$klssystablecategory])) continue;
			$lstChild=&$lst[$klssystablecategory]['child'];
			$klssystable=$recSysTable['id'];
			$lstChild[$klssystable]=Array();
			$lstChild[$klssystable]['info']=$recSysTable;
			$lstChild[$klssystable]['child']=Array();
			unset($lstChild);
		}
		$sql=<<<SQL
select
	systablecategory.id as klssystablecategory,
	sysfield.*,
	sysfieldtype.g740type as sysfieldtype_name,
	reftable.tablename as reftable_tablename
from 
	sysfield
		join systable on systable.id=sysfield.klssystable
		join systablecategory on systablecategory.id=systable.klssystablecategory
		left join sysfieldtype on sysfieldtype.id=sysfield.klssysfieldtype
		left join systable reftable on reftable.id=sysfield.klsreftable
order by systablecategory.id, systable.id, sysfield.ord, sysfield.id
SQL;
		$qSysField=$this->pdo($sql);
		while ($recSysField=$this->pdoFetch($qSysField)) {
			$klssystablecategory=$this->str2Sql($recSysField['klssystablecategory']);
			$klssystable=$this->str2Sql($recSysField['klssystable']);
			$klssysfield=$this->str2Sql($recSysField['id']);
			$lstTable=&$lst[$klssystablecategory]['child'];
			$lstChild=&$lstTable[$klssystable]['child'];

			$lstChild[$klssysfield]=Array();
			$lstChild[$klssysfield]['info']=$recSysField;
			$lstChild[$klssysfield]['child']=Array();
			
			unset($lstChild);
			unset($lstTable);
		}

		$sql=<<<SQL
select
	systablecategory.id as klssystablecategory,
	systable.id as klssystable,
	sysfieldparams.*
from
	sysfieldparams
		join sysfield on sysfield.id=sysfieldparams.klssysfield
		join systable on systable.id=sysfield.klssystable
		join systablecategory on systablecategory.id=systable.klssystablecategory
		left join sysfieldtype on sysfieldtype.id=sysfield.klssysfieldtype
order by systablecategory.id, systable.id, sysfield.ord, sysfield.id
SQL;
		$qSysFieldParams=$this->pdo($sql);
		while ($recSysFieldParams=$this->pdoFetch($qSysFieldParams)) {
			$klssystablecategory=$this->str2Sql($recSysFieldParams['klssystablecategory']);
			$klssystable=$this->str2Sql($recSysFieldParams['klssystable']);
			$klssysfield=$this->str2Sql($recSysFieldParams['klssysfield']);
			$klssysfieldparams=$this->str2Sql($recSysFieldParams['id']);
			$lstTable=&$lst[$klssystablecategory]['child'];
			$lstField=&$lstTable[$klssystable]['child'];
			$lstChild=&$lstField[$klssysfield]['child'];

			$lstChild[$klssysfieldparams]=Array();
			$lstChild[$klssysfieldparams]['info']=$recSysFieldParams;
			
			unset($lstChild);
			unset($lstField);
			unset($lstTable);
		}
		
		foreach($lst as &$lstSysTableCategory) {
			$recSysTableCategory=$lstSysTableCategory['info'];
			$xmlWriter->startElement('section');
			$xmlWriter->writeAttribute('name', $recSysTableCategory['name']);
			foreach($lstSysTableCategory['child'] as &$lstSysTable) {
				$recSysTable=$lstSysTable['info'];
				$xmlWriter->startElement('table');
				$xmlWriter->writeAttribute('name', $recSysTable['tablename']);
				$xmlWriter->writeAttribute('caption', $recSysTable['name']);
				$xmlWriter->writeAttribute('permmode', $recSysTable['permmode']);
				if ($recSysTable['isstatic']) $xmlWriter->writeAttribute('static', '1');
				if ($recSysTable['isdynamic']) $xmlWriter->writeAttribute('dynamic', '1');
				if ($recSysTable['issystem']) $xmlWriter->writeAttribute('system', '1');
				$xmlWriter->writeAttribute('orderby', $recSysTable['orderby']);
				foreach($lstSysTable['child'] as &$lstSysField) {
					$recSysField=$lstSysField['info'];
					$xmlWriter->startElement('field');
					$xmlWriter->writeAttribute('name', $recSysField['fieldname']);
					$xmlWriter->writeAttribute('caption', $recSysField['name']);
					$xmlWriter->writeAttribute('type', $recSysField['sysfieldtype_name']);
					if ($recSysField['isnotempty']) $xmlWriter->writeAttribute('notempty', '1');
					if ($recSysField['ismain']) $xmlWriter->writeAttribute('main', '1');
					if ($recSysField['isstretch']) $xmlWriter->writeAttribute('stretch', '1');
					if ($recSysField['maxlength']) $xmlWriter->writeAttribute('maxlength', $recSysField['maxlength']);
					if ($recSysField['len']) $xmlWriter->writeAttribute('len', $recSysField['len']);
					if ($recSysField['dec']) $xmlWriter->writeAttribute('dec', $recSysField['dec']);
					if ($recSysField['klsreftable']) {
						$xmlWriter->writeAttribute('reftable', $recSysField['reftable_tablename']);
						if ($recSysField['reflink']) $xmlWriter->writeAttribute('reflink', $recSysField['reflink']);
						if ($recSysField['isrefrestrict']) $xmlWriter->writeAttribute('restrict', '1');
						if ($recSysField['isrefcascade']) $xmlWriter->writeAttribute('cascade', '1');
						if ($recSysField['isrefclear']) $xmlWriter->writeAttribute('clear', '1');
						if ($recSysField['isref121']) $xmlWriter->writeAttribute('121', '1');
						foreach($lstSysField['child'] as &$lstSysFieldParams) {
							$recSysFieldParams=$lstSysFieldParams['info'];
							$xmlWriter->startElement('param');
							$xmlWriter->writeAttribute('name', $recSysFieldParams['name']);
							if ($recSysFieldParams['val']) $xmlWriter->writeAttribute('value', $recSysFieldParams['val']);
							$xmlWriter->endElement();
						}
					}
					$xmlWriter->endElement();
				}
				$xmlWriter->endElement();
			}
			$xmlWriter->endElement();
		}

		$xmlWriter->endElement();
		$xmlWriter->endDocument();
		$xmlWriter->flush();
		unset($xmlWriter);
	}
/** Загрузка описания структуры базы из XML
 *
 * @param	boolean	$isEcho
 */
	public function importDataStruFromXml($isEcho=false) {
		$driverName=$this->getDriverName();
		{ // sysfieldtype
			$lstTable=Array();
			$lstFieldRefTable=Array();
			$lstType=Array();
			$sql="select * from sysfieldtype";
			$q=$this->pdo($sql);
			while($rec=$this->pdoFetch($q)) {
				$type=$rec['g740type'];
				$lstType[$type]=$rec['id'];
			}
		}
		{ // Анализ XML файла
			if ($isEcho) {
				echo '<div class="message">Анализ XML файла ... '; flush();
			}
			$xmlReader=new XMLReader();
			$xmlReader->open($this->xmlFileName,'utf-8');
			$klssystablecategory=0;
			$systablecategoryord=0;
			$klssystable=0;
			$klssysfield=0;
			$sysfieldord=0;
			$klssysfieldparams=0;
			
			$sysTableCategoryName='';
			$sysTableName='';
			$sysFieldName='';
			
			$lstSqlSysTableCategory=Array();
			$lstSqlSysTable=Array();
			$lstSqlSysField=Array();
			$lstSqlSysFieldParams=Array();
			
			while(true) {
				if ($xmlReader->nodeType == XMLReader::ELEMENT) {
					if ($xmlReader->localName=='section') {
						$sysTableCategoryName=$xmlReader->getAttribute('name');
						$sqlName=$this->str2Sql($sysTableCategoryName);

						$klssystablecategory++;
						$sql=<<<SQL
insert into systablecategory (id, name, ord) values ('{$klssystablecategory}', '{$sqlName}', '{$systablecategoryord}');
SQL;
						$lstSqlSysTableCategory[]=$sql;
						$systablecategoryord+=10;
						
					}
					else if ($xmlReader->localName=='table') {
						$sysTableName=$xmlReader->getAttribute('name');
						if (!$sysTableName) throw new Exception("Не задано имя таблицы в секции '{$sysTableCategoryName}'");
						if (isset($lstTable[$sysTableName])) throw new Exception("Дублируется имя таблицы '{$sysTableName}'");
						$sqlTableName=$this->str2Sql($sysTableName);
						$sqlName=$this->str2Sql($xmlReader->getAttribute('caption'));
						$sqlPermMode=$this->str2Sql($xmlReader->getAttribute('permmode'));
						
						$sqlIsStatic=$this->str2Sql($xmlReader->getAttribute('static'));
						if ($sqlIsStatic!='1') $sqlIsStatic='0';
						$sqlIsDynamic=$this->str2Sql($xmlReader->getAttribute('dynamic'));
						if ($sqlIsDynamic!='1') $sqlIsDynamic='0';
						$sqlIsSystem=$this->str2Sql($xmlReader->getAttribute('system'));
						if ($sqlIsSystem!='1') $sqlIsSystem='0';
						$sqlOrderBy=$this->str2Sql($xmlReader->getAttribute('orderby'));
						
						$klssystable++;
						$sql=<<<SQL
insert into systable 
	(id, klssystablecategory, tablename, name, permmode, isstatic, isdynamic, issystem, orderby) 
values 
	('{$klssystable}', '{$klssystablecategory}', '{$sqlTableName}', '{$sqlName}', '{$sqlPermMode}', '{$sqlIsStatic}', '{$sqlIsDynamic}', '{$sqlIsSystem}', '{$sqlOrderBy}');
SQL;
						$lstSqlSysTable[]=$sql;
						$sysfieldord=0;
						$lstTable[$sysTableName]=$klssystable;
					}
					else if ($xmlReader->localName=='field') {
						$sysFieldName=$xmlReader->getAttribute('name');
						if (!$sysFieldName) throw new Exception("Не задано имя поля в таблице '{$sysTableName}'");
						
						$sqlFieldName=$this->str2Sql($sysFieldName);
						$sqlName=$this->str2Sql($xmlReader->getAttribute('caption'));
						
						$t=$xmlReader->getAttribute('type');
						$sqlKlsSysFieldType=$lstType[$t];
						if (!$sqlKlsSysFieldType) {
							if ($t) throw new Exception("Неизвестный тип поля '{$t}'");
							$sqlKlsSysFieldType=0;
						}
						
						$sqlIsNotEmpty=$this->str2Sql($xmlReader->getAttribute('notempty'));
						if ($sqlIsNotEmpty!='1') $sqlIsNotEmpty='0';
						$sqlIsMain=$this->str2Sql($xmlReader->getAttribute('main'));
						if ($sqlIsMain!='1') $sqlIsMain='0';
						$sqlIsStretch=$this->str2Sql($xmlReader->getAttribute('stretch'));
						if ($sqlIsStretch!='1') $sqlIsStretch='0';
						$sqlMaxLength=intval($xmlReader->getAttribute('maxlength'));
						$sqlLen=intval($xmlReader->getAttribute('len'));
						$sqlDec=intval($xmlReader->getAttribute('dec'));
						

						$sqlRefLink='';
						$sqlIsRefRestrict='';
						$sqlIsRefCascade='';
						$sqlIsRefClear='';
						$sqlIsRef121='';

						$reftable=$xmlReader->getAttribute('reftable');
						if ($reftable) {
							$sqlRefLink=$this->str2Sql($xmlReader->getAttribute('reflink'));
							$sqlIsRefRestrict=$this->str2Sql($xmlReader->getAttribute('restrict'));
							$sqlIsRefCascade=$this->str2Sql($xmlReader->getAttribute('cascade'));
							$sqlIsRefClear=$this->str2Sql($xmlReader->getAttribute('clear'));
							$sqlIsRef121=$this->str2Sql($xmlReader->getAttribute('121'));
						}
						if (!$sqlRefLink) $sqlRefLink='';
						if ($sqlIsRefRestrict!='1') $sqlIsRefRestrict='0';
						if ($sqlIsRefCascade!='1') $sqlIsRefCascade='0';
						if ($sqlIsRefClear!='1') $sqlIsRefClear='0';
						if ($sqlIsRef121!='1') $sqlIsRef121='0';
						
						$sqlDecFieldName='dec';
						if ($driverName=='mysql') $sqlDecFieldName='`dec`';

						$klssysfield++;
						$sql=<<<SQL
insert into sysfield
	(id, klssystable, fieldname, name, klssysfieldtype, isnotempty, ismain, isstretch, maxlength, len, {$sqlDecFieldName}, reflink, isrefrestrict, isrefcascade, isrefclear, isref121, ord)
values 
	('{$klssysfield}', '{$klssystable}', '{$sqlFieldName}', '{$sqlName}', '{$sqlKlsSysFieldType}', '{$sqlIsNotEmpty}', '{$sqlIsMain}', '{$sqlIsStretch}', '{$sqlMaxLength}', '{$sqlLen}', '{$sqlDec}', '{$sqlRefLink}', '{$sqlIsRefRestrict}', '{$sqlIsRefCascade}', '{$sqlIsRefClear}', '{$sqlIsRef121}', '{$sysfieldord}');
SQL;
						$lstSqlSysField[]=$sql;
						$sysfieldord+=10;
						if ($reftable) $lstFieldRefTable[$klssysfield]=$reftable;
					}
					else if ($xmlReader->localName=='param') {
						$sqlName=$this->str2Sql($xmlReader->getAttribute('name'));
						$sqlVal=$this->str2Sql($xmlReader->getAttribute('value'));
						if (!$sqlVal) $sqlVal='';
						
						$sql=<<<SQL
insert into sysfieldparams
	(klssysfield, name, val) 
values 
	('{$klssysfield}', '{$sqlName}', '{$sqlVal}');
SQL;
						$lstSqlSysFieldParams[]=$sql;
					}
				}
				if (!$xmlReader->read()) break;
			}
			foreach($lstFieldRefTable as $klssysfield=>$reftable) {
				if (!$reftable) continue;
				$klssystable=$lstTable[$reftable];
				if (!$klssystable) throw new Exception("Недопустимое имя справочника '{$reftable}'");
				if ($klssystable) {
					$sql="update sysfield set klsreftable='{$klssystable}' where id='{$klssysfield}';";
					$lstSqlSysField[]=$sql;
				}
			}
			if ($isEcho) {
				echo 'Ok!</div>'; flush();
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			}
		}

		$driverName=$this->getDriverName();
		if ($driverName=='pgsql') {
			$sql='SET CONSTRAINTS ALL DEFERRED;';
			$this->pdo($sql);
			$sql="SET session_replication_role = replica;";
			$this->pdo($sql);
		}


		{ // systablecategory
			if ($isEcho) {
				echo '<div class="message">systablecategory ... '; flush();
			}
			$this->beforeLoadTable('systablecategory');
			foreach($lstSqlSysTableCategory as $sql) {
				if ($driverName=='sqlsrv') $sql="set identity_insert [dbo].[systablecategory] ON;"."\n".$sql;
				$this->pdo($sql);
			}
			$this->afterLoadTable('systablecategory');
			if ($isEcho) {
				echo 'Ok!</div>'; flush();
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			}
		}
		{ // systable
			if ($isEcho) {
				echo '<div class="message">systable ... '; flush();
			}
			$this->beforeLoadTable('systable');
			foreach($lstSqlSysTable as $sql) {
				if ($driverName=='sqlsrv') $sql="set identity_insert [dbo].[systable] ON;"."\n".$sql;
				$this->pdo($sql);
			}
			$this->afterLoadTable('systable');
			if ($isEcho) {
				echo 'Ok!</div>'; flush();
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			}
		}
		{ // sysfield
			if ($isEcho) {
				echo '<div class="message">sysfield ... '; flush();
			}
			$this->beforeLoadTable('sysfield');
			foreach($lstSqlSysField as $sql) {
				if ($driverName=='sqlsrv') $sql="set identity_insert [dbo].[sysfield] ON;"."\n".$sql;
				$this->pdo($sql);
			}
			$this->afterLoadTable('sysfield');
			if ($isEcho) {
				echo 'Ok!</div>'; flush();
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			}
		}
		{ // sysfieldparams
			if ($isEcho) {
				echo '<div class="message">sysfieldparams ... '; flush();
			}
			$this->beforeLoadTable('sysfieldparams');
			foreach($lstSqlSysFieldParams as $sql) {
				$this->pdo($sql);
			}
			$this->afterLoadTable('sysfieldparams');
			if ($isEcho) {
				echo 'Ok!</div>'; flush();
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			}
		}
	}

/// Выгрузка главного меню системы в XML
	public function exportSysAppMenuToXml() {
		$lst=Array();
		$sql=<<<SQL
select sysappmenu.* 
from 
	sysappmenu
where
	sysappmenu.id>0
order by sysappmenu.parentid, sysappmenu.ord, sysappmenu.id
SQL;
		$q=$this->pdo($sql);
		while($rec=$this->pdoFetch($q)) {
			$parentid=$rec['parentid'];
			if (!isset($lst[$parentid])) $lst[$parentid]=Array();
			$lst[$parentid][]=$rec;
		}
		
		if (!$this->xmlWriter) {
			$this->xmlWriter = new XMLWriter();
			$this->xmlWriter->openURI($this->xmlFileName);
			$this->xmlWriter->setIndent(true);
			$this->xmlWriter->setIndentString ("\t");
			$this->xmlWriter->startDocument('1.0','utf-8');
			$this->xmlWriter->startElement('root');
		}
		
		foreach($lst[-99] as &$rec) {
			$this->_exportSysAppMenuToXmlLstItem($lst, $rec);
		}
		
		
		$this->xmlWriter->endElement();
		$this->xmlWriter->endDocument();
		$this->xmlWriter->flush();
		unset($this->xmlWriter);
	}
/** Вспомогательная рекурсивная функция выгрузки
 *
 * @param	Array	$lst
 * @param	Array	$rec
 */
	protected function _exportSysAppMenuToXmlLstItem(&$lst, &$rec) {
		$this->xmlWriter->startElement('node');
		if ($rec['name']) $this->xmlWriter->writeAttribute('name', $rec['name']);
		if ($rec['description']) $this->xmlWriter->writeAttribute('description', $rec['description']);
		if ($rec['icon']) $this->xmlWriter->writeAttribute('icon', $rec['icon']);
		if ($rec['permmode']) $this->xmlWriter->writeAttribute('permmode', $rec['permmode']);
		if ($rec['permoper']) $this->xmlWriter->writeAttribute('permoper', $rec['permoper']);
		$id=$rec['id'];
		if (isset($lst[$id])) {
			foreach($lst[$id] as &$recChild) {
				$this->_exportSysAppMenuToXmlLstItem($lst, $recChild);
			}
		}
		else {
			if ($rec['form']) $this->xmlWriter->writeAttribute('form', $rec['form']);
			if ($rec['params']) {
				$lst=explode("\n",$rec['params']);
				foreach($lst as $param) {
					$param=trim($param);
					if (!$param) continue;
					$name=$param;
					$value='';
					$n=strpos($param,'=');
					if ($n!==false) {
						$name=trim(substr($param,0,$n));
						$value=trim(substr($param,$n+1,999));
					}
					
					$this->xmlWriter->startElement('param');
					if ($name) $this->xmlWriter->writeAttribute('name', $name);
					if ($value) $this->xmlWriter->writeAttribute('value', $value);
					$this->xmlWriter->endElement();
				}
			}
		}
		$this->xmlWriter->endElement();
	}

/// Загрузка главного меню системы из XML
	public function importSysAppMenuFromXml() {
		$xmlDoc = new DOMDocument("1.0", "utf-8");
		$xmlDoc->load($this->xmlFileName);
		$xmlRoot=$xmlDoc->documentElement;
		$this->importSysAppMenuIdCounter=0;
		$this->_importSysAppMenuFromXmlPrepareXmlDoc($xmlRoot);
		
		$this->beforeLoadTable('sysappmenu');
		
		$sql=<<<SQL
insert into sysappmenu (id, parentid, name, description) values (-99, -99, 'root', 'root');
SQL;
		if ($this->getDriverName()=='sqlsrv') {
			$sql="set identity_insert dbo.sysappmenu ON;"."\n".$sql;
		}
		$this->pdo($sql);
		
		for ($xmlChild=$xmlRoot->firstChild; $xmlChild!=null; $xmlChild=$xmlChild->nextSibling) {
			if ($xmlChild->nodeType!=1) continue;
			if ($xmlChild->nodeName!='node') continue;
			$this->_importSysAppMenuFromXmlExecSQL($xmlChild);
		}
		
		$this->afterLoadTable('sysappmenu');
	}
/// счетчик id для загрузки главного меню системы
	protected $importSysAppMenuIdCounter=0;
/** Простановка id для загрузки главного меню системы
 *
 * @param	xml	$xmlNode
 */
	protected function _importSysAppMenuFromXmlPrepareXmlDoc($xmlNode) {
		$ord=0;
		for ($xmlChild=$xmlNode->firstChild; $xmlChild!=null; $xmlChild=$xmlChild->nextSibling) {
			if ($xmlChild->nodeType!=1) continue;
			if ($xmlChild->nodeName!='node') continue;
			$this->idcounter++;
			xmlSetAttr($xmlChild, 'id', $this->idcounter);
			xmlSetAttr($xmlChild, 'ord', $ord);
			$ord+=10;
		}
		for ($xmlChild=$xmlNode->firstChild; $xmlChild!=null; $xmlChild=$xmlChild->nextSibling) {
			if ($xmlChild->nodeType!=1) continue;
			if ($xmlChild->nodeName!='node') continue;
			$this->_importSysAppMenuFromXmlPrepareXmlDoc($xmlChild);
		}
	}
/** Формирование SQL запроса для загрузки главного меню системы
 *
 * @param	xml	$xmlNode
 */
	protected function _importSysAppMenuFromXmlExecSQL($xmlNode) {
		$sqlParentId=-99;
		$xmlParentNode=$xmlNode->parentNode;
		if ($xmlParentNode->nodeName=='node') $sqlParentId=$this->str2Sql(xmlGetAttr($xmlParentNode, 'id', '-99'));
		$sqlId=$this->str2Sql(xmlGetAttr($xmlNode, 'id', ''));
		$sqlOrd=$this->str2Sql(xmlGetAttr($xmlNode, 'ord', '0'));
		$sqlName=$this->str2Sql(xmlGetAttr($xmlNode, 'name', ''));
		$sqlDescription=$this->str2Sql(xmlGetAttr($xmlNode, 'description', ''));
		$sqlIcon=$this->str2Sql(xmlGetAttr($xmlNode, 'icon', ''));
		$sqlForm=$this->str2Sql(xmlGetAttr($xmlNode, 'form', ''));
		$sqlPermMode=$this->str2Sql(xmlGetAttr($xmlNode, 'permmode', ''));
		$sqlPermOper=$this->str2Sql(xmlGetAttr($xmlNode, 'permoper', ''));
		$param='';
		for ($xmlChild=$xmlNode->firstChild; $xmlChild!=null; $xmlChild=$xmlChild->nextSibling) {
			if ($xmlChild->nodeType!=1) continue;
			if ($xmlChild->nodeName!='param') continue;
			$name=xmlGetAttr($xmlChild, 'name', '');
			$value=xmlGetAttr($xmlChild, 'value', '');
			if (!$name) continue;
			if ($param) $param.="\n";
			$param.=$name;
			if ($value) $param.="=".$value;
		}
		$sqlParams=$this->str2Sql($param);
		
		$sql=<<<SQL
insert into sysappmenu(id, parentid, name, description, icon, form, permmode, permoper, params, ord)
values ('{$sqlId}', '{$sqlParentId}', '{$sqlName}', '{$sqlDescription}', '{$sqlIcon}', '{$sqlForm}', '{$sqlPermMode}', '{$sqlPermOper}', '{$sqlParams}', '{$sqlOrd}');
SQL;
		if ($this->getDriverName()=='sqlsrv') {
			$sql="set identity_insert dbo.sysappmenu ON;"."\n".$sql;
		}
		$this->pdo($sql);
		
		for ($xmlChild=$xmlNode->firstChild; $xmlChild!=null; $xmlChild=$xmlChild->nextSibling) {
			if ($xmlChild->nodeType!=1) continue;
			if ($xmlChild->nodeName!='node') continue;
			$this->_importSysAppMenuFromXmlExecSQL($xmlChild);
		}
	}
}