<?php
/**
 * @file
 * Библиотека функций - расширение базового набора под server G740
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
$docRequest=null;
/// корневой XML узел запроса
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
/// вспомогательный XML документ - используется для формирования ответа
$docTemp=new DOMDocument("1.0", "utf-8");
$docTemp->loadXml('<temp></temp>');

/// XMLWriter для формирования ответа
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
				$s=str_replace("\n",'~',$s);
				$s=str_replace("\r",'',$s);
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
			$sql="select * from [dbo].[{$tableName}]";
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
							$value=str_replace('~', '\n', $value);
							$sqlValues=$sqlValues . $sqlDelim . "'" . $value . "'";
							$sqlDelim=',';
						}
						else if ($driverName=='sqlsrv') {
							$sqlFields=$sqlFields . $sqlDelim . '['.$fieldName.']';
							$value=$this->str2Sql($value);
							$value=str_replace('~', '\n', $value);
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
set identity_insert [dbo].[{$tableName}] ON;
insert into [dbo].[{$tableName}] ({$sqlFields}) values ({$sqlValues});
SQL;
						}
						else {
							throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
						}
						$this->pdo($sqlInsert, "Ошибка при вставке строки в таблицу {$tableName}");
					}
					if ($isEcho && ($i % 500)==0) {
						$pdoDB->commit();
						echo '. '; flush();
						$pdoDB->beginTransaction();
					}
					$i++;
				}
			}
			if (!$xmlReader->read()) break;
		}
		if ($pdoDB->inTransaction()) $pdoDB->commit();
		if (!$pdoDB->inTransaction()) $pdoDB->beginTransaction();

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
}
