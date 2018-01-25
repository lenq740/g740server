<?php
/**
Библиотека функций - расширение базового набора под server G740
@package lib
@subpackage lib-g740server
*/
require_once('lib-base.php');

//------------------------------------------------------------------------------
// Преобразования для подстановок
//------------------------------------------------------------------------------
/**
Преобразовать данные из G740 в PHP
@param	String	$value данные в формате G740
@param	String	$type тип данных в формате G740
@return	AnyType преобразованные данные
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
/**
Преобразовать данные из PHP в G740
@param	AnyType	$value данные
@param	AnyType	$type тип данных
@return	String данные преобразованные в формат G740
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
$docRequest=null;		// Документ - запрос, который надо обработать
$rootRequest=null;		// Корневой узел запроса

/**
Прочитать запрос в $docRequest
*/
function initDocRequest() {
	global $docRequest;
	global $rootRequest;
	global $HTTP_RAW_POST_DATA;
	$docRequest = new DOMDocument("1.0", "utf-8");
	if (isset($HTTP_RAW_POST_DATA)) {
		try {
			$docRequest->loadXML($HTTP_RAW_POST_DATA);
		}
		catch (Exception $e) {
		}
	}
	else {
		 throw new Exception('HTTP_RAW_POST_DATA не задано');
	}
	if (!is_object($docRequest)) throw new Exception('Системная ошибка! Не передан запрос!');
	$rootRequest=$docRequest->documentElement;
	if (!is_object($rootRequest)) throw new Exception('Системная ошибка! Не передан запрос!');
	if ($rootRequest->nodeName!='root') throw new Exception('Системная ошибка! У xml документа запроса корневой узел не root!');
	if (xmlGetAttr($rootRequest,'type','')!='g740') throw new Exception('Системная ошибка! У xml документа запроса атрибут type не g740!');
	return $docRequest;
}
/**
Построить ассоциативный массив из параметров запроса
@param	Xml		$xmlRequest XML узел запроса
@return	mixed[] начитанные параметры запроса
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
$docTemp=new DOMDocument("1.0", "utf-8");
$docTemp->loadXml('<temp></temp>');

$objResponseWriter=null;
function initObjResponseWriter() {
	global $objResponseWriter;
	$objResponseWriter=new XMLWriter();
	$objResponseWriter->openMemory();
	$objResponseWriter->startDocument('1.0', 'UTF-8');
	$objResponseWriter->startElement('root');
	$objResponseWriter->writeAttribute('type','g740');
	return $objResponseWriter;
}
function writeXml($str) {
	global $objResponseWriter;
	$objResponseWriter->writeRaw($str);
}
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
	writeXml($strForm);
}
/**
Парсер строки в XML элемент документа $docTemp
@param	String	$strXml текст XML
@return	Xml узел $docTemp
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
function domXml2StrXml($domXml) {
	global $docTemp;
	return $docTemp->saveXML($domXml);
}

//------------------------------------------------------------------------------
// Backup, Restore
//------------------------------------------------------------------------------
/**
Сохранение таблицы в XML
@param	mixed[] para
<li>	para['xmlWriter']
<li>	para['tableName']
<li>	para['isEcho']
<li>	para['isOptimize']
*/
function saveTableToXmlWriter($para) {
	if (!$para) throw new Exception('Не задан para');
	$pdoDB=getPDO();
	$xmlWriter=$para['xmlWriter'];
	$tableName=$para['tableName'];
	$isEcho=$para['isEcho'];
	$isOptimize=$para['isOptimize'];
	
	$xmlWriter->startElement('table');
	$xmlWriter->writeAttribute('name', $tableName);

	$sql='select * from '.$tableName;
	$q=$pdoDB->pdo($sql);
	$i=0;
	while ($rec=$pdoDB->pdoFetch($q))
	{
		$xmlWriter->startElement('row');
		foreach($rec as $key=>$value) {
			$s=$value;
			$s=str_replace("\n",'~',$s);
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
	if ($isOptimize) {
		if ($isEcho) {
			echo 'optimize '; flush();
		}
		opimizeTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}
}
/**
Загрузка списка таблиц из XML
@param	mixed[] para
<li>	para['fileName']
<li>	para['tables']
<li>	para['isEcho']
<li>	para['isOptimize']
<li>	para['isDisableKeys']
*/
function loadTablesFromXmlReader($para) {
	if (!$para) throw new Exception('Не задан para');
	$fileName=$para['fileName'];
	if (!file_exists($fileName)) throw new Exception('Отсутствует файл '.$fileName);
	$isEcho=$para['isEcho'];
	$tables=$para['tables'];
	$tbl=Array();
	foreach($tables as $key=>$tableName) $tbl[strtolower($tableName)]=true;
	$xmlReader=new XMLReader();
	$xmlReader->open($fileName,'utf-8');
	while(true) {
		if($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName=='table') {
			$tableName=strtolower($xmlReader->getAttribute('name'));
			if ($tbl[$tableName]) {
				$p=Array();
				$p['xmlReader']=$xmlReader;
				$p['tableName']=$tableName;
				$p['isEcho']=$para['isEcho'];
				$p['isOptimize']=$para['isOptimize'];
				$p['isDisableKeys']=$para['isDisableKeys'];
				if ($isEcho) {
					echo '<div class="message">'.$tableName.': '; flush();
				}
				_loadTableFromXmlReader($p);
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
	$xmlReader->close();
	unset($xmlReader);
}
/**
Загрузка таблицы из XML
@param	mixed[] para
<li>	para['xmlReader']
<li>	para['tableName']
<li>	para['fields']
<li>	para['isEcho']
<li>	para['isOptimize']
<li>	para['isDisableKeys']
*/
function _loadTableFromXmlReader($para) {
	if (!$para) throw new Exception('Не задан para');
	$pdoDB=getPDO();
	$xmlReader=$para['xmlReader'];
	$tableName=$para['tableName'];
	$fields=$para['fields'];
	$isEcho=$para['isEcho'];
	$isOptimize=$para['isOptimize'];
	$isDisableKeys=$para['isDisableKeys'];
	if (!$fields) $fields=Array();

	if ($xmlReader->nodeType != XMLReader::ELEMENT) throw new Exception('Недопустимый текущий элемент');
	if ($xmlReader->localName!='table') throw new Exception('Недопустимый текущий элемент');
	if (strtolower($xmlReader->getAttribute('name'))!=$tableName) throw new Exception('Недопустимый текущий элемент');
	
	if ($isDisableKeys) {
		if ($isEcho) {
			echo 'disable keys '; flush();
		}
		$pdoDB->pdo("lock table {$tableName} write");
		$pdoDB->pdo("alter table {$tableName} disable keys");
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}

	if ($isEcho) {
		echo 'delete '; flush();
	}
	$pdoDB->pdo('TRUNCATE TABLE '.$tableName);
	if ($isOptimize) opimizeTable($tableName);
	if ($isEcho) {
		echo 'ok '; flush();
	}

	$i=0;
	$xmlReader->read();
	if (!$pdoDB->inTransaction()) $pdoDB->beginTransaction();
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
					if ($value!='') {
						$sqlFields=$sqlFields . $sqlDelim . '`'.$fieldName.'`';
						$value=str_replace('~', '\n', $value);
						$value=str_replace("'", '"', $value);
						$sqlValues=$sqlValues . $sqlDelim . "'" . $value . "'";
						$sqlDelim=',';
					}
				}
				if ($sqlDelim) {
					$sqlInsert='insert into ' . $tableName . ' (' . $sqlFields . ') values (' . $sqlValues . ')';
					$pdoDB->pdo($sqlInsert, "Ошибка при вставке строки в таблицу {$tableName}");
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
	$pdoDB->commit();

	if ($isDisableKeys) {
		if ($isEcho) {
			echo 'enable keys '; flush();
		}
		$pdoDB->pdo("alter table {$tableName} enable keys");
		$pdoDB->pdo("unlock tables");
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}

	if ($isEcho) {
		echo 'set max(id) '; flush();
	}
	$rec=$pdoDB->pdoFetch('select max(id) as id from '.$tableName);
	$maxId=$rec['id'];
	if (!$maxId) $maxId=0;
	$pdoDB->pdo('ALTER TABLE '.$tableName.' AUTO_INCREMENT='.$maxId);
	if ($isEcho) {
		echo 'ok '; flush();
	}
		
	if ($isOptimize) {
		if ($isEcho) {
			echo 'optimize '; flush();
		}
		opimizeTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}
}
/**
Оптимизация таблицы в базе
@param	String	$tableName
*/
function opimizeTable($tableName) {
	$pdoDB=getPDO();
	$sql='check table '.$tableName;
	$q=$pdoDB->pdo($sql);
	$sql='optimize table '.$tableName;
	$q=$pdoDB->pdo($sql);
}

//------------------------------------------------------------------------------
// Прочее
//------------------------------------------------------------------------------
/**
Класс ошибки, не требующей логирования
*/
class ExceptionNoReport extends Exception {
	protected $responseExec=Array();
	public function addResponseExec($exec) {
		$this->responseExec[]=$exec;
	}
	public function getResponseExec() {
		return $this->responseExec;
	}
}
