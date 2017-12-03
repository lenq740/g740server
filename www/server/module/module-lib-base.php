<?php
/**
Библиотека функций - базовый набор
@package module
@subpackage module-lib-base
*/

//------------------------------------------------------------------------------
// Преобразования для подстановок
//------------------------------------------------------------------------------
/**
Преобразовать строку для подстановки в MySql
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2MySql($str) {
	return mysql_escape_string($str);
}
/**
Преобразовать строку для Xml
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2Xml($str) {
	$from=Array('&','"',"'", '<','>');
	$to=Array('&amp;','&quot;','&apos;','&lt;','&gt;');
	return str_replace($from, $to, $str);
}
/**
Преобразовать строку для атрибута Xml
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2XmlAttr($str) {
	$result=str2Xml($str);
	$from=Array("\n","\r","\t");
	$to=Array('&#xA;','&#xD;','&#x9;');
	return str_replace($from, $to, $result);
}
/**
Преобразовать строку для аттрибутов в кавычках
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2Attr($str) {
	$from=Array('&','"',"'",'<','>',"\n","\r","\t");
	$to=Array('&amp;','&quot;','&apos;','&lt;','&gt;',' ','',' ');
	return str_replace($from, $to, $str);
	//return htmlspecialchars($str,ENT_QUOTES);
}
/**
Преобразование строки к написанию для HTML
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2Html($str) {
	$str=trim($str);
	$str=str_replace('<','&#060;',$str);
	$str=str_replace('>','&#062;',$str);
	$str=str_replace("\n",'<br>',$str);
	return $str;
}
/**
Преобразование строки к написанию для XLS
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2Xls($str) {
	$str=str_replace('"','&#34;',$str);
	$str=str_replace('<','&#060;',$str);
	$str=str_replace('>','&#062;',$str);
	$str=str_replace("\n",' ',$str);
	$str=str_replace("\r",' ',$str);
	return $str;
}
/**
Преобразование строки для строки JavaScript
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2JavaScript($str) {
	$from=Array("\\",'"',"'","\n");
	$to=Array("\\\\",'\"',"\'",'');
	return str_replace($from, $to, $str);
}
/**
Преобразование строки для строки вставки в PHP в одинарные кавычки
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function str2Php($str) {
	$from=Array("\\","'","\n");
	$to=Array("\\\\","\'",'');
	return str_replace($from, $to, $str);
}
/**
Проговаривалка для чисел и их единиц измерения
@param	Num		$value	число
@param	String	$d1		единица измерения для 1 (например: рубль)
@param	String	$d2		единица измерения для 2 (например: рубля)
@param	String	$d5		единица измерения для 5 (например: рублей)
@return	String текстовое представления числа с единицами измерения
*/
function num2TxtEdIzm($value, $d1, $d2, $d5) {
	$result=$d5;
	$value=abs($value);
	$n1=$value % 10;
	$n2=floor($value/10) % 10;
	if ($n2!=1) {
		if ($n1==1) $result=$d1;
		if (($n1>=2) && ($n1<=4)) $result=$d2;
	}
	return $result;
}
/**
Вспомогательная функция от проговаривалки чисел, текстовое представления тройки чисел
@param	Num		$a		сотни
@param	Num		$b		десятки
@param	Num		$c		еденицы
@param	Num		$d		1 единицы, 2 тысячи, 3 миллионы, 4 миллиарды 
@param	Boolean	$isMan	мужской род
@return	String текстовое представления тройки чисел
*/
function num2TxtGo($a,$b,$c,$d,$isMan) {
	$result='';
    if ($a==9) $result='девятьсот ';
    if ($a==8) $result='восемьсот ';
    if ($a==7) $result='семьсот ';
    if ($a==6) $result='шестьсот ';
    if ($a==5) $result='пятьсот ';
    if ($a==4) $result='четыреста ';
    if ($a==3) $result='триста ';
    if ($a==2) $result='двести ';
    if ($a==1) $result='сто ';
    if ($b==1) {
      if ($c==9) $result.='девятнадцать ';
      if ($c==8) $result.='восемнадцать ';
      if ($c==7) $result.='семнадцать ';
      if ($c==6) $result.='шестнадцать ';
      if ($c==5) $result.='пятнадцать ';
      if ($c==4) $result.='четырнадцать ';
      if ($c==3) $result.='тринадцать ';
      if ($c==2) $result.='двенадцать ';
      if ($c==1) $result.='одиннадцать ';
      if ($c==0) $result.='десять ';
    }
    else {
      if ($b==9) $result.='девяносто ';
      if ($b==8) $result.='восемьдесят ';
      if ($b==7) $result.='семьдесят ';
      if ($b==6) $result.='шестьдесят ';
      if ($b==5) $result.='пятьдесят ';
      if ($b==4) $result.='сорок ';
      if ($b==3) $result.='тридцать ';
      if ($b==2) $result.='двадцать ';

      if ($c==9) $result.='девять ';
      if ($c==8) $result.='восемь ';
      if ($c==7) $result.='семь ';
      if ($c==6) $result.='шесть ';
      if ($c==5) $result.='пять ';
      if ($c==4) $result.='четыре ';
      if ($c==3) $result.='три ';
      if ($c==2) {
      	if (($d==2) || (($d==1) && !$isMan)) {
	      	$result.='две ';
	    }
	    else {
	      	$result.='два ';
	    }
      }
      if ($c==1) {
      	if (($d==2) || (($d==1) && !$isMan)) {
	      	$result.='одна ';
	    }
	    else {
	      	$result.='один ';
	    }
      }
    }
    if ($result!='') {
	    $d1='';
	    $d2='';
	    $d5='';
	    if ($d==2) {
		    $d1='тысяча ';
	    	$d2='тысячи ';
		    $d5='тысяч ';
	    }
	    if ($d==3) {
		    $d1='миллион ';
	    	$d2='миллиона ';
		    $d5='миллионов ';
	    }
	    if ($d==4) {
		    $d1='миллиард ';
	    	$d2='миллиарда ';
		    $d5='миллиардов ';
	    }
	    $result.=num2TxtEdIzm($a*100+$b*10+$c, $d1, $d2, $d5);
    }
	return $result;
}
/**
Проговаривалка для чисел без единиц измерения
@param	Num		$value	число
@param	Boolean	$isMan	мужской род
@return	String текстовое представления числа без единиц измерения
*/
function num2Txt($value, $isMan) {
	$result='';
	$d=4;
	$nn=1000000000;
	while ($d>0) {
		$r=floor(abs($value)/$nn) % 1000;
		$c=$r % 10;
		$b=floor($r/10) % 10;
		$a=floor($r/100) % 10;
		$result.=num2TxtGo($a,$b,$c,$d,$isMan);
    	$d--;
    	$nn=floor($nn/1000);
	}
	if ($value==0) $result='ноль ';
	if ($value<0) $result='минус '.$result;
	return $result;
}
/**
Проговаривалка для денег
@param	Num		$value	число
@return	String текстовое представления денег 
*/
function money2Txt($value) {
	$r=floor(abs($value));
	$c=floor(abs($value*100)) % 100;
	if ($value<0) $r=-$r;
	$result=num2Txt($r, true).num2TxtEdIzm($r, 'рубль', 'рубля', 'рублей').' '.num2Txt($c, false).num2TxtEdIzm($c, 'копейка', 'копейки', 'копеек');
	return $result;
}
/**
Проговаривалка для месяца
@param	Num		$value	месяц 1 - 12
@return	String текстовое представление месяца
*/
function month2Txt($value) {
	$result='';
	if ($value==1) $result='января';
	if ($value==2) $result='февраля';
	if ($value==3) $result='марта';
	if ($value==4) $result='апреля';
	if ($value==5) $result='мая';
	if ($value==6) $result='июня';
	if ($value==7) $result='июля';
	if ($value==8) $result='августа';
	if ($value==9) $result='сентября';
	if ($value==10) $result='октября';
	if ($value==11) $result='ноября';
	if ($value==12) $result='декабря';
	return $result;
}

/**
Преобразование даты формата YYYY-MM-DD к виду DD.MM.YYYY, подходит для вставки в HTML
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function date2Html($str) {
	if (!$str) return '';
	$result=substr($str,8,2).'.'.substr($str,5,2).'.'.substr($str,0,4);
	return str2Html($result);
}
/**
Привести в порядок строковое представление времени, к виду hh:mm
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function normTime($str) {
	$result='';
	for($i=0; $i<mb_strlen($str); $i++) {
		$c=mb_substr($str,$i,1);
		if ($c>='0' && $c<='9') $result.=$c;
	}
	if ($result=='') return $result;
	while(mb_strlen($result)<4) $result='0'.$result;
	$h=mb_substr($result,0,2);
	$m=mb_substr($result,2,2);
	if ($m>59) {
		$h='00';
		$m='00';
	}
	if ($h>=24) {
		$h='00';
		$m='00';
	}
	$result=$h.':'.$m;
	return $result;
}
/**
Приведение телефонного номера России к стандартному виду 8(903)550-25-12
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function getTelNorm($str) {
	$n=mb_strlen($str,'utf-8');
	$result='';
	for($i=0; $i<$n; $i++) {
		$c=mb_substr($str,$i,1,'utf-8');
		if (($c>='0') && ($c<='9')) $result.=$c;
	}
	$n=mb_strlen($result,'utf-8');
	if ($n<10) return '';
	if ($n==10) $result='8'.$result;
	if ($n>11) $result=mb_substr($result,$n-11,11,'utf-8');
	if (mb_substr($result,0,1,'utf-8')=='7') $result='8'.mb_substr($result,1,10,'utf-8');
	if (mb_substr($result,0,1,'utf-8')!='8') return '';
	return mb_substr($result,0,1,'utf-8').'('.mb_substr($result,1,3,'utf-8').')'.mb_substr($result,4,3,'utf-8').'-'.mb_substr($result,7,2,'utf-8').'-'.mb_substr($result,9,2,'utf-8');
}
/**
Приведение e-mail к стандартному виду
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function getEMailNorm($str) {
	return trim(mb_strtolower($str,'utf-8'));
}
/**
Вытаскиваем подстроку по словам, не длинее заданного значения, с ... на конце если не уместилось
@param	String	$str исходная строка
@param	Integer	$maxLength максимальная длина
@return	String преобразованная строка
*/
function substrByWord($str, $maxLength) {
	$str=str_replace("\n",' ',$str);
	$str=str_replace("\r",' ',$str);
	$str=str_replace("\t",' ',$str);
	$str=str_replace('.',' ',$str);
	$str=str_replace(',',' ',$str);
	$str=str_replace('!',' ',$str);
	$str=str_replace(':',' ',$str);
	$str=str_replace('"',' ',$str);
	$str=str_replace("'",' ',$str);
	$str=str_replace('+',' ',$str);
	$str=str_replace('-',' ',$str);
	$str=str_replace('(',' ',$str);
	$str=str_replace(')',' ',$str);
	
	$arr=Array();
	foreach(explode(' ',$str) as $key=>$value) {
		$value=trim($value);
		if ($value=='') continue;
		$arr[]=$value;
	}
	$result='';
	$nn=count($arr);
	$eln='...';
	for ($i=0; $i<$nn; $i++) {
		$value=$arr[$i];
		$s=$result;
		if ($s!='') $s.=' ';
		$s.=trim($value);
		if (mb_strlen($s.$eln,'utf-8')>$maxLength) break;
		if ($i==($nn-1)) $eln='';
		$result=$s;
	}
	if ($result!='') {
		$result.=$eln;
	}
	else {
		$result=mb_substr($str, 0, $maxLength, 'utf-8');
	}
	return $result;
}

/**
Преобразование строки к ESCAPE, utf-8
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function escape($str) {
    $escape_chars = "%u0410 %u0430 %u0411 %u0431 %u0412 %u0432 %u0413 %u0433 %u0490 %u0491 %u0414 %u0434 %u0415 %u0435 %u0401 %u0451 %u0404 %u0454 %u0416 %u0436 %u0417 %u0437 %u0418 %u0438 %u0406 %u0456 %u0419 %u0439 %u041A %u043A %u041B %u043B %u041C %u043C %u041D %u043D %u041E %u043E %u041F %u043F %u0420 %u0440 %u0421 %u0441 %u0422 %u0442 %u0423 %u0443 %u0424 %u0444 %u0425 %u0445 %u0426 %u0446 %u0427 %u0447 %u0428 %u0448 %u0429 %u0449 %u042A %u044A %u042B %u044B %u042C %u044C %u042D %u044D %u042E %u044E %u042F %u044F";
    $russian_chars = "А а Б б В в Г г Ґ ґ Д д Е е Ё ё Є є Ж ж З з И и І і Й й К к Л л М м Н н О о П п Р р С с Т т У у Ф ф Х х Ц ц Ч ч Ш ш Щ щ Ъ ъ Ы ы Ь ь Э э Ю ю Я я";
    $e = explode(" ",$escape_chars);
    $r = explode(" ",$russian_chars);
    $rus_array = str_split($str);
    $new_word = str_replace($r,$e,$rus_array);
    $new_word = str_replace(" ","%20",$new_word);
    $new_word = implode("",$new_word);
    return ($new_word);
}
/**
Обратное преобразование ESCAPE
@param	String	$str исходная строка
@return	String преобразованная строка
*/
function unescape($str){
    $escape_chars = "0410 0430 0411 0431 0412 0432 0413 0433 0490 0491 0414 0434 0415 0435 0401 0451 0404 0454 0416 0436 0417 0437 0418 0438 0406 0456 0419 0439 041A 043A 041B 043B 041C 043C 041D 043D 041E 043E 041F 043F 0420 0440 0421 0441 0422 0442 0423 0443 0424 0444 0425 0445 0426 0446 0427 0447 0428 0448 0429 0449 042A 044A 042B 044B 042C 044C 042D 044D 042E 044E 042F 044F";
    $russian_chars = "А а Б б В в Г г Ґ ґ Д д Е е Ё ё Є є Ж ж З з И и І і Й й К к Л л М м Н н О о П п Р р С с Т т У у Ф ф Х х Ц ц Ч ч Ш ш Щ щ Ъ ъ Ы ы Ь ь Э э Ю ю Я я";
    $e = explode(" ",$escape_chars);
    $r = explode(" ",$russian_chars);
    $rus_array = explode("%u",$str);
    $new_word = str_replace($e,$r,$rus_array);
    $new_word = str_replace("%20"," ",$new_word);
    return (implode("",$new_word));
}

//------------------------------------------------------------------------------
// Работа со строками
//------------------------------------------------------------------------------
/**
Проверка на корректность простого элемента, образующего URL:
	используется для проверки, при задании пользователем url адреса сущности
	допустимы маленькие латинские буквы, цифры, тире и подчеркивание
	знаки '/','#','?','&' недопустимы, так как это разделители между элементами
@param	String	$str исходная строка
@return	Boolean признак корректности
*/
function testSimpleUrlItem($str) {
	return preg_match("/^[a-z0-9\-_]+$/",$str);
}
/**
Проверить, начинается ли $str с $s
@param	String	$str исходная строка
@param	String	$s подстрока
@return	Boolean результат проверки
*/
function isStrStarting($str, $s) {
	$strLen=mb_strlen($str,'utf-8');
	$sLen=mb_strlen($s,'utf-8');
	if ($strLen<sLen) return false;
	return mb_substr($str, 0, $sLen,'utf-8')==$s;
}
/**
Проверить, заканчивается ли $str на $s
@param	String	$str исходная строка
@param	String	$s подстрока
@return	Boolean результат проверки
*/
function isStrEnding($str, $strSubstr) {
	$strLen=mb_strlen($str,'utf-8');
	$sLen=mb_strlen($s,'utf-8');
	if ($strLen<sLen) return false;
	return mb_substr($str, $strLen-$sLen, $sLen,'utf-8')==$s;
}
/**
Сгенерить GUID
@return	String GUID
*/
function getGUID(){
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    }
	else {
        mt_srand((double)microtime()*10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $uuid = 
            substr($charid, 0, 8).'-'.
            substr($charid, 8, 4).'-'.
            substr($charid,12, 4).'-'.
            substr($charid,16, 4).'-'.
            substr($charid,20,12);
        return $uuid;
    }
}
/**
Шифруем пароль
@return	String зашифрованный пароль
*/
function cryptPassword($password) {
	return md5($password . getCfg('crypt.md5.key'));
}

//------------------------------------------------------------------------------
// Конфигурационные настройки
//------------------------------------------------------------------------------
/**
Вернуть значение настроечной константы
@param	String	$name имя настройки
@param	String	$default значение по умолчанию
@return	String значение настроечной константы
*/
function getCfg($name, $default='') {
	global $config;
	if (isset($config[$name])) return $config[$name];
	return $default;
}
//------------------------------------------------------------------------------
// Пути
//------------------------------------------------------------------------------
/**
Объеденить несколько элементов относительного пути
@return	String зашифрованный пароль
*/
function pathConcat(
	$item01='',$item02='',$item03='',$item04='',$item05='',
	$item06='',$item07='',$item08='',$item09='',$item10='',
	$item11='',$item12='',$item13='',$item14='',$item15=''
	) {
	$lst=Array();
	if ($item01) $lst[]=$item01;
	if ($item02) $lst[]=$item02;
	if ($item03) $lst[]=$item03;
	if ($item04) $lst[]=$item04;
	if ($item05) $lst[]=$item05;
	if ($item06) $lst[]=$item06;
	if ($item07) $lst[]=$item07;
	if ($item08) $lst[]=$item08;
	if ($item09) $lst[]=$item09;
	if ($item10) $lst[]=$item10;
	if ($item11) $lst[]=$item11;
	if ($item12) $lst[]=$item12;
	if ($item13) $lst[]=$item13;
	if ($item14) $lst[]=$item14;
	if ($item15) $lst[]=$item15;
	$result='';
	foreach($lst as $item) {
		$item=trim(str_replace('\\', '/', $item));
		if (!$item) continue;
		if (mb_substr($item,0,1)=='/') $item=mb_substr($item,1,mb_strlen($item,'utf-8')-1);
		if (mb_substr($item,-1)=='/') $item=mb_substr($item,0,mb_strlen($item,'utf-8')-1);
		if (!$item) continue;
		if ($result) $result.='/';
		$result.=$item;
	}
	return $result;
}

//------------------------------------------------------------------------------
// Работа с XML
//------------------------------------------------------------------------------
/**
Вернуть значение атрибута узла
@param	Xml		$xml узел
@param	String	$attributeName имя атрибута
@param	String	$defValue значение по умолчанию
@return	String значение атрибута
*/
function xmlGetAttr($xml, $attributeName, $defValue) {
	if (!is_object($xml)) return $defValue;
	return xmlNodeValue($xml->getAttributeNode($attributeName), $defValue);
}
/**
Проверить наличие аттрибута у узла
@param	Xml		$xml узел
@param	String	$attributeName имя атрибута
@return	Boolean наличие атрибута
*/
function xmlIsAttr($xml, $attributeName) {
	if (!is_object($xml)) return false;
	if (!is_object($xml->getAttributeNode($attributeName))) return false;
	return true;
}
/**
Задать значение атрибута
@param	Xml		$xml узел
@param	String	$attributeName имя атрибута
@param	String	$attributeValue значение атрибута
@return	Boolean успешность выполнения операции
*/
function xmlSetAttr($xml, $attributeName, $attributeValue) {
	if (!is_object($xml)) return false;
	if (!mb_check_encoding($attributeValue,'UTF-8')) return false;
	$xml->setAttribute($attributeName, $attributeValue);
	return true;
}
/**
Вернуть текстовое значение узла
@param	Xml		$xml узел
@param	String	$defValue текстовое значение по умолчанию
@return	String текстовое значение
*/
function xmlNodeValue($xml, $defValue) {
	$result=null;
	if (!is_object($xml)) return $defValue;
	//attribute
	if ($xml->nodeType==2) {
		return $xml->value;
	}
	//text, cdatasection, comment
	if ($xml->nodeType==3 || $xml->nodeType==4 || $xml->nodeType==8) {
		$result=$xml->nodeValue;
	}
	if ($result===null) return $defValue;
	return $result;
}
function xmlGetText($xml) {
	$result='';
	if (!is_object($xml)) return $result;
	for ($xmlItem=$xml->firstChild; $xmlItem!=null; $xmlItem=$xmlItem->nextSibling) {
		if ($xmlItem->nodeType==XML_TEXT_NODE) $result.=$xmlItem->nodeValue;
	}
	return $result;
}
/**
Вернуть первый дочерний узел по $tagName
@param	Xml		$xml узел
@param	String	$tagName имя узла
@return	Xml первый подходящий дочерний узел
*/
function xmlGetChild($xml, $tagName) {
	if (!is_object($xml)) return null;
	for ($xmlItem=$xml->firstChild; $xmlItem!=null; $xmlItem=$xmlItem->nextSibling) {
		if ($xmlItem->nodeName==$tagName) return $xmlItem;
	}
	return null;
}
/**
Вернуть первый дочерний узел по $tagName и значению атрибута
@param	Xml		$xml узел
@param	String	$tagName имя узла
@param	String	$atrName имя атрибута
@param	String	$atrValue значение атрибута
@return	Xml первый подходящий дочерний узел
*/
function xmlGetChildByAttr($xml, $tagName, $atrName, $atrValue) {
	if (!is_object($xml)) return null;
	for ($xmlItem=$xml->firstChild; $xmlItem!=null; $xmlItem=$xmlItem->nextSibling) {
		if ($xmlItem->nodeName==$tagName) {
			if ($xmlItem->getAttribute($atrName)==$atrValue) return $xmlItem;
		}
	}
	return null;
}
/**
Создать XML документ
@return	Xml документ XML
*/
function xmlCreateDoc() {
	return new DOMDocument("1.0","utf-8");
}
/**
Создать и вставить узел
@param	Xml		$xmlOwner узел
@param	String	$tagName имя создаваемого узла
@param	Xml		$xmlBefore узел, перед которым вставить новый
@return	Xml созданный Xml узел
*/
function xmlCreateNode($xmlOwner, $tagName, $xmlBefore=null) {
	if (!is_object($xmlOwner)) return null;
	$xmlDoc=$xmlOwner->ownerDocument;
	if (!isset($xmlDoc)) $xmlDoc=$xmlOwner;
	$elem=$xmlDoc->createElement($tagName);
	if (is_object($xmlBefore)) {
		$xmlOwner->insertBefore($elem, $xmlBefore);
	}
	else {
		$xmlOwner->appendChild($elem);
	}
	return $elem;
}
/**
Создать и вставить текст
@param	Xml		$xmlOwner узел
@param	String	$text текст
@return	Xml созданный Xml текстовый узел
*/
function xmlCreateText($xmlOwner, $text) {
	if (!is_object($xmlOwner)) return null;
	$xmlDoc=$xmlOwner->ownerDocument;
	if (!isset($xmlDoc)) $xmlDoc=$xmlOwner;
	$elem=$xmlDoc->createTextNode($text);
	$xmlOwner->appendChild($elem);
	return $elem;
}
/**
Создать и вставить комментарий
@param	Xml		$xmlOwner узел
@param	String	$comment комментарий
@return	Xml созданный Xml комментарий
*/
function xmlCreateComment($xmlOwner, $comment) {
	if (!is_object($xmlOwner)) return null;
	$xmlDoc=$xmlOwner->ownerDocument;
	if (!isset($xmlDoc)) $xmlDoc=$xmlOwner;
	$elem=$xmlDoc->createComment($comment);
	$xmlOwner->appendChild($elem);
	return $elem;
}
/**
Создать и вставить CDATASection
@param	Xml		$xmlOwner узел
@param	String	$text CDATASection
@return	Xml созданный Xml CDATASection
*/
function xmlCreateCDATASection($xmlOwner, $text) {
	if (!is_object($xmlOwner)) return null;
	$xmlDoc=$xmlOwner->ownerDocument;
	if (!isset($xmlDoc)) $xmlDoc=$xmlOwner;
	$elem=$xmlDoc->createCDATASection($text);
	$xmlOwner->appendChild($elem);
	return $elem;
}

//------------------------------------------------------------------------------
// Работа с базой данных через PDO
//------------------------------------------------------------------------------
$pdoDB=null;
/**
Класс расширения функционала PDO
@package module
@subpackage module-lib
*/
class PDODataConnectorAbstract extends PDO {
	public function getDriverName() {
		throw new Exception('Обращение к абстрактной функции PDODataConnectorAbstract::getDriverName');
	}
	public function str2Sql($str) {
		throw new Exception('Обращение к абстрактной функции PDODataConnectorAbstract::str2Sql');
	}
	public function php2Sql($value) {
		throw new Exception('Обращение к абстрактной функции PDODataConnectorAbstract::php2Sql');
	}
	public function rowCount() {
		return $this->_rowCount;
	}
	protected $_rowCount=0;
	public function pdo($sql, $errorMessage='', $params=Array()) {
		if (getCfg('trace.sql')) trace($sql."\n");
		
		$result=$this->prepare($sql);
		if (!$result) {
			$errInfo=$this->errorInfo();
			$errorMessage.=' Ошибка в SQL запросе '.$errInfo[2]."\n".$sql;
			if (getCfg('trace.error.sql')) errorLog($errorMessage);
			throw new Exception($errorMessage);
		}
		if (!$result->execute($params)) {
			$errInfo=$result->errorInfo();
			$errorMessage.=' Ошибка в SQL запросе '.$errInfo[2]."\n".$sql;
			if (getCfg('trace.error.sql')) errorLog($errorMessage);
			throw new Exception($errorMessage);
		}
		$this->_rowCount=$result->rowCount();
		return $result;
	}
	public function pdoFetch($sql, $errorMessage='', $params=Array()) {
		$q=null;
		$t=gettype($sql);
		if ($t=='string') $q=$this->pdo($sql, $errorMessage, $params);
		if ($t=='object' && $sql instanceof PDOStatement) $q=$sql;
		if (!$q) {
			$errorMessage.=' Неверный параметр sql';
			throw new Exception($errorMessage);
		}
		return $q->fetch(PDO::FETCH_ASSOC);
	}
	public function openConnection() {
	}
	public function closeConnection() {
	}
}
/**
Класс расширения функционала PDO для MySql
@package module
@subpackage module-lib
*/
class PDODataConnectorMySql extends PDODataConnectorAbstract {
	function __construct($dbName, $login, $password, $charset='utf8', $host='localhost') {
		try {
			$str="{$this->getDriverName()}:dbname={$dbName};host={$host}";
			parent::__construct($str, 
				$login, $password,
				array(
					PDO::ATTR_PERSISTENT => true, 	// режим пула соединений
					PDO::ERRMODE_SILENT => true		// не выдавать Exception в случае ошибки 
				)
			);
			$this->query("SET CHARSET {$charset}");
			$this->setAttribute(PDO::CASE_LOWER, true);	// имена полей приводить к маленьким буквам
		}
		catch(Exception $e) {
			$msg=$e->getMessage();
			if (!mb_check_encoding($msg,'UTF-8')) {
				if (mb_check_encoding($msg,'Windows-1251')) $msg=mb_convert_encoding($msg, 'UTF-8', 'Windows-1251');
				else $msg=mb_convert_encoding($msg, 'UTF-8', mb_detect_encoding($msg));
			}
			throw new Exception("Не удалось установить соединение с базой данных. {$msg}");
		}
	}
	public function getDriverName() {
		return 'mysql';
	}
	public function str2Sql($str) {
		return mysql_escape_string($str);
	}
	public function php2Sql($value) {
		$result='';
		if (is_object($value) && (get_class($value)=='DateTime')) {
			$result=$value->format('Y-m-d');
		}
		else if ($value!==null) {
			$result=$this->str2Sql($value);
		}
		return $result;
	}
	public function openConnection() {
		$sql=<<<SQL
create temporary table if not exists tmptablelist (
	list varchar(36) not null,
	value varchar(36) not null,
	index (list)
);
SQL;
		$this->pdo($sql);
	}
	public function closeConnection() {
		$sql=<<<SQL
truncate table tmptablelist;
SQL;
		$this->pdo($sql);
	}
}
/**
Класс расширения функционала PDO для PostgreSQL
@package module
@subpackage module-lib
*/
class PDODataConnectorPgSql extends PDODataConnectorAbstract {
	function __construct($dbName, $login, $password, $charset='utf8', $host='localhost') {
		$str="{$this->getDriverName()}:dbname={$dbName};host={$host}";
		try {
			parent::__construct($str, 
				$login, $password,
				array(
					PDO::ATTR_PERSISTENT => true, 	// режим пула соединений
					PDO::ERRMODE_SILENT => true		// не выдавать Exception в случае ошибки 
				)
			);
			$this->setAttribute(PDO::CASE_LOWER, true);	// имена полей приводить к маленьким буквам
		}
		catch(Exception $e) {
			$msg=$e->getMessage();
			if (!mb_check_encoding($msg,'UTF-8')) {
				if (mb_check_encoding($msg,'Windows-1251')) $msg=mb_convert_encoding($msg, 'UTF-8', 'Windows-1251');
				else $msg=mb_convert_encoding($msg, 'UTF-8', mb_detect_encoding($msg));
			}
			throw new Exception("Не удалось установить соединение с базой данных. {$msg}");
		}
	}
	public function getDriverName() {
		return 'pgsql';
	}
	public function str2Sql($str) {
		return mysql_escape_string($str);
	}
	public function php2Sql($value) {
		$result='';
		if (is_object($value) && (get_class($value)=='DateTime')) {
			$result=$value->format('Y-m-d');
		}
		else if ($value!==null) {
			$result=$this->str2Sql($value);
		}
		return $result;
	}
}
/**
Класс расширения функционала PDO для MSSQL
@package module
@subpackage module-lib
*/
class PDODataConnectorMSSql extends PDODataConnectorAbstract {
	function __construct($dbName, $login, $password, $charset='utf8', $host='localhost') {
		$str="{$this->getDriverName()}:App=TestPdo;ConnectionPooling=1;Server={$host};Database={$dbName}";
		try {
			parent::__construct($str, $login, $password);
		}
		catch(Exception $e) {
			$msg=$e->getMessage();
			if (!mb_check_encoding($msg,'UTF-8')) {
				if (mb_check_encoding($msg,'Windows-1251')) $msg=mb_convert_encoding($msg, 'UTF-8', 'Windows-1251');
				else $msg=mb_convert_encoding($msg, 'UTF-8', mb_detect_encoding($msg));
			}
			throw new Exception("Не удалось установить соединение с базой данных. {$msg}");
		}
	}
	public function getDriverName() {
		return 'sqlsrv';
	}
	public function str2Sql($str) {
		return str_replace("'", '"', $str);
	}
	public function php2Sql($value) {
		$result='';
		if (is_object($value) && (get_class($value)=='DateTime')) {
			$result=$value->format('Y-m-d');
		}
		else if ($value!==null) {
			$result=$this->str2Sql($value);
		}
		return $result;
	}
	public function openConnection() {
		$sql="select object_id('tempdb..[##tmptablelist]') as objectid";
		$rec=$this->pdoFetch($sql);
		if (!$rec['objectid']) {
			$sql=<<<SQL
create table ##tmptablelist (
	list uniqueidentifier not null,
	value varchar(36) not null
);
SQL;
			$this->pdo($sql);
		}
	}
	public function closeConnection() {
		$sql=<<<SQL
delete from ##tmptablelist;
SQL;
		$this->pdo($sql);
	}
}

//------------------------------------------------------------------------------
// Трассировка
//------------------------------------------------------------------------------
// Трасировка
function trace($value) {
	if (!is_dir('log')) mkdir('log');
	if (!$handle = fopen('log/trace.txt', 'a')) throw new Exception("Не удалось открыть файл 'log/trace.txt'");
	if (gettype($value)=='string') {
		$str=$value;
	} else {
		$str=var_export($value, true);
	}
	$str.="\n";
	$str.="--------------------------\n";
	if (fwrite($handle, $str) === FALSE) throw new Exception("Не удалось произвести запись файл log/trace.txt");
	fclose($handle);
}

// Логирование ошибок
function errorLog($e) {
	if (!is_dir('log')) mkdir('log');
	if (!$handle = fopen('log/logerr.txt', 'a')) throw new Exception("Не удалось открыть файл 'log/logerr.txt'");
	if (is_string($e)) {
		$result=date("[d-M-Y H:i:s e]").' Error'."\n".$e."\n".'------------'."\n";
	}
	else {
		$result=date("[d-M-Y H:i:s e]").' PHP Exception: '.$e->getMessage().' in '.$e->getFile().' on '.$e->getLine()."\n";
		$lst=$e->getTrace();
		foreach($lst as $index=>$item) {
			$result.="\t{$item['file']}\t{$item['function']}\t{$item['line']}\n";
		}
	}
	if (fwrite($handle, $result) === FALSE) throw new Exception("Не удалось произвести запись файл log/logerr.txt");
	fclose($handle);
}
?>