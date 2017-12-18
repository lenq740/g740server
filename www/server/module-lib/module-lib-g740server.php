<?php
/**
Библиотека функций - расширение базового набора под server G740
@package module-lib
@subpackage module-lib-g740server
*/
require_once('module-lib-base.php');

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
