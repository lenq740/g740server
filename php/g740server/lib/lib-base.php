<?php
/**
 * @file
 * G740Server, библиотека функций - базовый набор функций
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

//------------------------------------------------------------------------------
// Функции преобразования для подстановок в HTML, XML, PHP, XLS и т.д.
//------------------------------------------------------------------------------
/** Преобразовать строку для подстановки в MySql
 * 
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2MySql($str) {
	$from=Array('\\',"'",'\n','\r','\t','\0');
	$to=Array('\\\\',"''",'\\n','\\r','\\t','\\0');
	return str_replace($from, $to, $str);
}
/** Преобразовать строку для подстановки в XML
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2Xml($str) {
	$from=Array('&','"',"'", '<','>');
	$to=Array('&amp;','&quot;','&apos;','&lt;','&gt;');
	return str_replace($from, $to, $str);
}
/** Преобразовать строку для подстановки в атрибут Xml
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2XmlAttr($str) {
	$result=str2Xml($str);
	$from=Array("\n","\r","\t");
	$to=Array('&#xA;','&#xD;','&#x9;');
	return str_replace($from, $to, $result);
}
/** Преобразовать строку для подстановки в атрибут в двойных кавычках
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2Attr($str) {
	$from=Array('&','"',"'",'<','>',"\n","\r","\t");
	$to=Array('&amp;','&quot;','&apos;','&lt;','&gt;',' ','',' ');
	return str_replace($from, $to, $str);
	//return htmlspecialchars($str,ENT_QUOTES);
}
/** Преобразование строки для подстановки в HTML
 *
 * @param	string	$str исходная строка
 * @param	string	$format html|xls
 * @return	string преобразованная строка
 */
function str2Html($str, $format='html') {
	$str=trim($str);
	if ($format=='xls') {
		$str=str_replace('"','&#34;',$str);
		$str=str_replace('<','&#060;',$str);
		$str=str_replace('>','&#062;',$str);
		$str=str_replace("\n",' ',$str);
		$str=str_replace("\r",' ',$str);
	}
	else {
		$str=str_replace('<','&#060;',$str);
		$str=str_replace('>','&#062;',$str);
		$str=str_replace("\n",'<br>',$str);
	}
	return $str;
}
/** Преобразование строки для подстановки в XLS
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2Xls($str) {
	$str=str_replace('"','&#34;',$str);
	$str=str_replace('<','&#060;',$str);
	$str=str_replace('>','&#062;',$str);
	$str=str_replace("\n",' ',$str);
	$str=str_replace("\r",' ',$str);
	return $str;
}
/** Преобразование строки для строки JavaScript
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2JavaScript($str) {
	$from=Array("\\",'"',"'","\n","\r");
	$to=Array("\\\\",'\"',"\'",'','');
	return str_replace($from, $to, $str);
}
/** Преобразование строки для строки вставки в PHP в одинарные кавычки
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2Php($str) {
	$from=Array("\\","'","\n");
	$to=Array("\\\\","\'",'');
	return str_replace($from, $to, $str);
}
/** Проверка наличия параметра в массиве, ошибка если параметр отсутствует
 *
 * @param	Array	$params массив
 * @param	string	$name имя параметра
 * @return	string значение параметра
 */
function testParam(&$params, $name) {
	if (!isset($params[$name])) throw new Exception('Не задан обязательный параметр '.$name);
	return $params[$name];
}
/** Проговаривалка для чисел и их единиц измерения
 *
 * @param	num		$value	число
 * @param	string	$d1		единица измерения для 1 (например: рубль)
 * @param	string	$d2		единица измерения для 2 (например: рубля)
 * @param	string	$d5		единица измерения для 5 (например: рублей)
 * @return	string текстовое представления числа с единицами измерения
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
/** Вспомогательная функция от проговаривалки чисел, текстовое представления тройки чисел
 *
 * @param	num		$a		сотни
 * @param	num		$b		десятки
 * @param	num		$c		еденицы
 * @param	num		$d		1 единицы, 2 тысячи, 3 миллионы, 4 миллиарды 
 * @param	boolean	$isMan	мужской род
 * @return	string текстовое представления тройки чисел
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
/** Проговаривалка для чисел без единиц измерения
 *
 * @param	num		$value	число
 * @param	boolean	$isMan	мужской род
 * @return	string текстовое представления числа без единиц измерения
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
/** Проговаривалка для денег
 *
 * @param	num		$value	число
 * @return	string текстовое представления денег 
 */
function money2Txt($value) {
	$r=floor(abs($value));
	$c=floor(abs($value*100)) % 100;
	if ($value<0) $r=-$r;
	$result=num2Txt($r, true).num2TxtEdIzm($r, 'рубль', 'рубля', 'рублей').' '.num2Txt($c, false).num2TxtEdIzm($c, 'копейка', 'копейки', 'копеек');
	return $result;
}
/** Проговаривалка для месяца
 *
 * @param	num		$value	месяц 1 - 12
 * @param	num		$case 1,2 (один январь, два января)
 * @return	string текстовое представление месяца
 */
function month2Txt($value, $case=2) {
	$result='';
	if ($case==1) {
		if ($value==1) $result='январь';
		if ($value==2) $result='февраль';
		if ($value==3) $result='март';
		if ($value==4) $result='апрель';
		if ($value==5) $result='май';
		if ($value==6) $result='июнь';
		if ($value==7) $result='июль';
		if ($value==8) $result='август';
		if ($value==9) $result='сентябрь';
		if ($value==10) $result='октябрь';
		if ($value==11) $result='ноябрь';
		if ($value==12) $result='декабрь';
	}
	else {
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
	}
	return $result;
}
/** Преобразование даты формата YYYY-MM-DD к виду DD.MM.YYYY, подходит для вставки в HTML
 *
 * @param	string	$value дата
 * @param	string	$format html|xls
 * @return	string преобразованная строка
 */
function date2Html($value, $format='html') {
	if (!testDate($value)) return '';
	if (is_a($value,'DateTime')) {
		$result=$value->format('d.m.Y');
	}
	else {
		$result=substr($value,8,2).'.'.substr($value,5,2).'.'.substr($value,0,4);
	}
	return str2Html($result, $format);
}
/** Проверка корректности даты в формате YYYY-MM-DD
 *
 * @param	string	$value дата
 * @return	boolean корректность даты
 */
function testDate($value) {
	if (!$value) return false;
	if (is_a($value,'DateTime')) return true;
	$value=trim($value);
	if (strlen($value)<10) return false;
	$value=substr($value,0,10);
	$lst=explode('-',$value);
	if (count($lst)!=3) return false;
	$year=intval($lst[0]);
	$month=intval($lst[1]);
	$day=intval($lst[2]);
	if (!checkdate($month, $day, $year)) return false;
	return true;
}
/** Форматирование числа
 *
 * @param	num		$value	число
 * @param	num		$dec	количество десятичных знаков
 * @return	string отформатированное строковое представление
 */
function num2Str($value, $dec=0) {
	if (!$value) $value=0;
	return number_format($value, $dec, '.', '');
}

/** Преобразование строки к ESCAPE, utf-8
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
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
/** Обратное преобразование ESCAPE
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
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
/** Преобразовать строку для корректного имени файла в Windows
 * 
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function str2FileName($str) {
	$lstFrom=Array('"', "'", '`', '/', "\\", '*', '?', '+', '=', '[', ']', ':', '«', ',', '<', '>', '|');
	$lstTo=Array('_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_', '_');
	$result=trim(str_replace($lstFrom, $lstTo, $str));
	return $result;
}


//------------------------------------------------------------------------------
// Функции работы со строками
//------------------------------------------------------------------------------
/** Преобразование строкового представление времени к виду hh:mm
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function normTime($str) {
	$result='';
	$min=intval($str);
	if ($min>0 && $min<100 && $str==(string)$min) {
		$m=$min%60;
		$h=intval(($min-$m)/60);
		if ($m<10) $m='0'.$m;
		if ($h<10) $h='0'.$h;
		$str=$h.':'.$m;
	}
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
	if ($result=='00:00') $result='';
	return $result;
}
/** Приведение телефонного номера России к стандартному виду 8(495)123-45-67
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
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
/** Приведение e-mail к стандартному виду
 *
 * @param	string	$str исходная строка
 * @return	string преобразованная строка
 */
function getEMailNorm($str) {
	return trim(mb_strtolower($str,'utf-8'));
}
/** Вытаскиваем подстроку по словам, не длинее заданного значения, с ... на конце если не уместилось
 *
 * @param	string	$str исходная строка
 * @param	num		$maxLength максимальная длина
 * @return	string преобразованная строка
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
/** Проверка на корректность простого элемента, образующего URL:
 *
 * используется для проверки, при задании пользователем url адреса сущности
 * допустимы маленькие латинские буквы, цифры, тире и подчеркивание
 * знаки '/','#','?','&' недопустимы, так как это разделители между элементами
 * @param	string	$str исходная строка
 * @return	boolean признак корректности
 */
function testSimpleUrlItem($str) {
	return preg_match("/^[a-z0-9\-_]+$/",$str);
}
/** Проверить, начинается ли $str с $s
 *
 * @param	string	$str исходная строка
 * @param	string	$s подстрока
 * @return	boolean результат проверки
 */
function isStrStarting($str, $s) {
	$strLen=mb_strlen($str,'utf-8');
	$sLen=mb_strlen($s,'utf-8');
	if ($strLen<sLen) return false;
	return mb_substr($str, 0, $sLen,'utf-8')==$s;
}
/** Проверить, заканчивается ли $str на $strSubstr
 *
 * @param	string	$str исходная строка
 * @param	string	$strSubstr подстрока
 * @return	boolean результат проверки
 */
function isStrEnding($str, $strSubstr) {
	$strLen=mb_strlen($str,'utf-8');
	$sLen=mb_strlen($s,'utf-8');
	if ($strLen<sLen) return false;
	return mb_substr($str, $strLen-$sLen, $sLen,'utf-8')==$s;
}
/** Сгенерить GUID
 *
 * @return	string GUID
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
/** Проверить GUID на корректность
 *
 * @param	string	$guid
 * @return	boolean результат проверки
 */
function testGUID($guid) {
	if (strlen($guid)!=36) return false;
	return preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $guid)?true:false;
}
/** Шифруем пароль
 *
 * @param	string	$password пароль
 * @return	string зашифрованный пароль
 */
function cryptPassword($password) {
	return md5($password . getCfg('crypt.md5.key'));
}

/** Обратимое шифрование, ключ 32 цифры от 0 до F (128 бит)
 *
 * @param	string	$plaintext текст
 * @return	string зашифрованный текст
 */
function enCryptText($plaintext) {
	$cipher = 'aes-128-cbc';
	$key=getCfg('crypt.128.key');
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = openssl_random_pseudo_bytes($ivlen);
	$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
	$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);

	$ciphertext = base64_encode($iv.$hmac.$ciphertext_raw);
	$from=Array('+','/','=');
	$to=Array('й','ы','я');
	$ciphertext=str_replace($from, $to, $ciphertext);
	
	return $ciphertext;
}
/** Расшифровка, ключ 32 цифры от 0 до F (128 бит)
 *
 * @param	string	$ciphertext зашифрованный текст
 * @return	string исходный текст
 */
function deCryptText($ciphertext) {
	$cipher = 'aes-128-cbc';
	$key=getCfg('crypt.128.key');

	$from=Array('й','ы','я');
	$to=Array('+','/','=');
	$ciphertext=str_replace($from, $to, $ciphertext);
	$c = base64_decode($ciphertext);
	
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = substr($c, 0, $ivlen);
	$hmac = substr($c, $ivlen, $sha2len=32);
	$ciphertext_raw = substr($c, $ivlen+$sha2len);
	$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
	$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
	if (hash_equals($hmac, $calcmac))//с PHP 5.6+ сравнение, не подверженное атаке по времени
	{
		return $original_plaintext;
	}
	return '';
}

//------------------------------------------------------------------------------
// Конфигурационные настройки
//------------------------------------------------------------------------------
/** Вернуть значение настроечной константы
 *
 * @param	string	$name имя настройки
 * @param	string	$default значение по умолчанию
 * @return	string значение настроечной константы
 */
function getCfg($name, $default='') {
	global $config;
	if (isset($config[$name])) return $config[$name];
	return $default;
}

//------------------------------------------------------------------------------
// Пути
//------------------------------------------------------------------------------
/** Объеденить несколько элементов относительного пути
 *
 * @param	string	$item01 параметр  1
 * @param	string	$item02 параметр  2
 * @param	string	$item03 параметр  3
 * @param	string	$item04 параметр  4
 * @param	string	$item05 параметр  5
 * @param	string	$item06 параметр  6
 * @param	string	$item07 параметр  7
 * @param	string	$item08 параметр  8
 * @param	string	$item09 параметр  9
 * @param	string	$item10 параметр 10
 * @param	string	$item11 параметр 11
 * @param	string	$item12 параметр 12
 * @param	string	$item13 параметр 13
 * @param	string	$item14 параметр 14
 * @param	string	$item15 параметр 15
 * @return	String путь
 */
function pathConcat($item01='',$item02='',$item03='',$item04='',
	$item05='',$item06='',$item07='',$item08='',$item09='',
	$item10='',$item11='',$item12='',$item13='',$item14='',$item15='') {
	$lst=Array();
	if ($item01!='') $lst[]=$item01;
	if ($item02!='') $lst[]=$item02;
	if ($item03!='') $lst[]=$item03;
	if ($item04!='') $lst[]=$item04;
	if ($item05!='') $lst[]=$item05;
	if ($item06!='') $lst[]=$item06;
	if ($item07!='') $lst[]=$item07;
	if ($item08!='') $lst[]=$item08;
	if ($item09!='') $lst[]=$item09;
	if ($item10!='') $lst[]=$item10;
	if ($item11!='') $lst[]=$item11;
	if ($item12!='') $lst[]=$item12;
	if ($item13!='') $lst[]=$item13;
	if ($item14!='') $lst[]=$item14;
	if ($item15!='') $lst[]=$item15;
	$result='';
	$isFirst=true;
	$isFirstSlash=false;
	$isLastSlash=false;
	foreach($lst as $item) {
		$item=trim(str_replace('\\', '/', $item));
		if ($item=='') continue;
		if (mb_substr($item,0,1)=='/') {
			$item=mb_substr($item,1,mb_strlen($item,'utf-8')-1);
			if ($isFirst) $isFirstSlash=true;
		}
		$isFirst=false;
		$isLastSlash=false;
		if (mb_substr($item,-1)=='/' || $item=='') {
			$item=mb_substr($item,0,mb_strlen($item,'utf-8')-1);
			$isLastSlash=true;
		}
		if ($item=='') continue;
		if ($result!='') $result.='/';
		$result.=$item;
	}
	if ($isFirstSlash) $result='/'.$result;
	if ($isLastSlash) $result.='/';
	return $result;
}

/** Убрать слэш с начала и конца пути
 *
 * @param	string	$path путь
 * @return	String путь без начального и завершающего слэша
 */
function pathClearSlash($path) {
	$result=trim(str_replace('\\', '/', $path));
	while(mb_substr($result, 0, 1, 'utf-8')=='/') {
		$result=mb_substr($result, 1, mb_strlen($result,'utf-8')-1, 'utf-8');
	}
	while(mb_substr($result, -1, 1, 'utf-8')=='/') {
		$result=mb_substr($result, 0, mb_strlen($result,'utf-8')-1, 'utf-8');
	}
	return $result;
}
/** Удалить папку с содержимым
 *
 * @param	string	$dir путь до папки
 */
function rmdirFull($dir) {  
    $d=opendir($dir);  
    while(($entry=readdir($d))!==false) {
		if ($entry==".") continue;
		if ($entry=="..") continue;
		if (is_dir($dir."/".$entry)) {
			rmdirFull($dir."/".$entry);
			continue;
		}
		unlink($dir."/".$entry);  
    } 
    closedir($d);  
    rmdir ($dir);  
} 
/** Скопировать содержимое папки
 *
 * @param	string	$source исходная папка
 * @param	string	$destination результирующая папка
 */
function copydirFull($source, $destination) {
	if (is_file($destination)) unlink($destination);
	if (is_dir($destination)) rmdirFull($destination);
    if (is_dir($source)) {
		mkdir($destination, 0777, true);
		$lst=scandir($source);
		foreach($lst as $fileName) {
			if ($fileName=='.') continue;
			if ($fileName=='..') continue;
			copydirFull(
				pathConcat($source, $fileName),
				pathConcat($destination, $fileName)
			);
		}
    } else if (is_file($source)) {
		$info=pathinfo($destination);
		$dir=$info['dirname'];
		if (!is_dir($dir)) mkdir($dir, 0777, true);
		copy($source, $destination);
    }
}
/** Проверить файл на наличие и незанятость другим процессом
 *
 * @param	string	$fileName файл
 * @param	boolean	файл есть и никем не занят
 */
function testFileFree($fileName) {
	if (!is_file($fileName)) return false;
	$f=fopen($fileName,'r+');
	if ($f) {
		fclose($f);
		return true;
	}
	return false;
}

//------------------------------------------------------------------------------
// Работа с XML
//------------------------------------------------------------------------------
/** Вернуть значение атрибута XML узла
 *
 * @param	xml		$xml узел
 * @param	string	$attributeName имя атрибута
 * @param	string	$defValue значение по умолчанию
 * @return	string значение атрибута
 */
function xmlGetAttr($xml, $attributeName, $defValue) {
	if (!is_object($xml)) return $defValue;
	return xmlNodeValue($xml->getAttributeNode($attributeName), $defValue);
}
/** Проверить наличие аттрибута у XML узла
 *
 * @param	xml		$xml узел
 * @param	string	$attributeName имя атрибута
 * @return	boolean наличие атрибута
 */
function xmlIsAttr($xml, $attributeName) {
	if (!is_object($xml)) return false;
	if (!is_object($xml->getAttributeNode($attributeName))) return false;
	return true;
}
/** Задать значение атрибута XML узла
 *
 * @param	xml		$xml узел
 * @param	string	$attributeName имя атрибута
 * @param	string	$attributeValue значение атрибута
 * @return	boolean успешность выполнения операции
 */
function xmlSetAttr($xml, $attributeName, $attributeValue) {
	if (!is_object($xml)) return false;
	if (!mb_check_encoding($attributeValue,'UTF-8')) return false;
	$xml->setAttribute($attributeName, $attributeValue);
	return true;
}
/** Вернуть значение XML узла
 *
 * @param	xml		$xml узел
 * @param	string	$defValue значение по умолчанию
 * @return	string значение узла
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
/** Вернуть текстовое значение XML узла
 *
 * @param	xml		$xml узел
 * @return	string текстовое значение
 */
function xmlGetText($xml) {
	$result='';
	if (!is_object($xml)) return $result;
	for ($xmlItem=$xml->firstChild; $xmlItem!=null; $xmlItem=$xmlItem->nextSibling) {
		if ($xmlItem->nodeType==XML_TEXT_NODE) $result.=$xmlItem->nodeValue;
	}
	return $result;
}
/** Вернуть первый дочерний XML узел по $tagName
 *
 * @param	xml		$xml узел
 * @param	string	$tagName имя узла
 * @return	xml первый подходящий дочерний узел
 */
function xmlGetChild($xml, $tagName) {
	if (!is_object($xml)) return null;
	for ($xmlItem=$xml->firstChild; $xmlItem!=null; $xmlItem=$xmlItem->nextSibling) {
		if ($xmlItem->nodeName==$tagName) return $xmlItem;
	}
	return null;
}
/** Вернуть первый дочерний узел по $tagName и значению атрибута
 *
 * @param	xml		$xml узел
 * @param	string	$tagName имя узла
 * @param	string	$atrName имя атрибута
 * @param	string	$atrValue значение атрибута
 * @return	xml первый подходящий дочерний узел
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
/** Создать XML документ
 *
 * @return	xml документ XML
 */
function xmlCreateDoc() {
	return new DOMDocument("1.0","utf-8");
}
/** Создать и вставить XML узел
 *
 * @param	xml		$xmlOwner узел
 * @param	string	$tagName имя создаваемого узла
 * @param	xml		$xmlBefore узел, перед которым вставить новый
 * @return	xml созданный Xml узел
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
/** Создать и вставить текст
 *
 * @param	xml		$xmlOwner узел
 * @param	string	$text текст
 * @return	xml созданный Xml текстовый узел
 */
function xmlCreateText($xmlOwner, $text) {
	if (!is_object($xmlOwner)) return null;
	$xmlDoc=$xmlOwner->ownerDocument;
	if (!isset($xmlDoc)) $xmlDoc=$xmlOwner;
	$elem=$xmlDoc->createTextNode($text);
	$xmlOwner->appendChild($elem);
	return $elem;
}
/** Создать и вставить комментарий
 *
 * @param	xml		$xmlOwner узел
 * @param	string	$comment комментарий
 * @return	xml созданный Xml комментарий
 */
function xmlCreateComment($xmlOwner, $comment) {
	if (!is_object($xmlOwner)) return null;
	$xmlDoc=$xmlOwner->ownerDocument;
	if (!isset($xmlDoc)) $xmlDoc=$xmlOwner;
	$elem=$xmlDoc->createComment($comment);
	$xmlOwner->appendChild($elem);
	return $elem;
}
/** Создать и вставить CDATASection
 *
 * @param	xml		$xmlOwner узел
 * @param	string	$text CDATASection
 * @return	xml созданный Xml CDATASection
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
/** Класс расширения функционала PDO 
 *
 * удобен для выполнения запросов и получения результатов в виде ассоциативного массива
 */
class PDODataConnector extends PDO {
/// текст сообщения о последней ошибке
	public $lastErrorMessage='';
	
/** Получить имя драйвера SQL сервера
 *
 * в настоящее время доступны варианты mysql, pgsql, sqlsrv
 * @return	string имя драйвера SQL сервера
 */
	public function getDriverName() {
		throw new Exception('Обращение к абстрактной функции PDODataConnector::getDriverName');
	}
/** Подготовить строку для корректной вставки в SQL запрос
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2Sql($str) {
		throw new Exception('Обращение к абстрактной функции PDODataConnector::str2Sql');
	}
/** Подготовить строку для корректной вставки в SQL запрос в раздел like секции where
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2SqlLike($str) {
		throw new Exception('Обращение к абстрактной функции PDODataConnector::str2SqlLike');
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function php2Sql($value) {
		throw new Exception('Обращение к абстрактной функции PDODataConnector::php2Sql');
	}
/** Подготовить GUID для корректной вставки в SQL запрос
 *
 * @param	GUID	$str
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function guid2Sql($str) {
		if (!$str) $str='00000000-0000-0000-0000-000000000000';
		if (mb_strlen($str)!=36) $str='00000000-0000-0000-0000-000000000000';
		return $this->str2Sql($str);
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос в раздел in секции where
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос в раздел in секции where
 */
	public function php2SqlIn($value) {
		$result='';
		if (is_string($value)) {
			if (strpos($value,',')!==false) {
				$lst=explode(',', $value);
				$value=Array();
				foreach($lst as $index=>$val) {
					if ($val=='') continue;
					if ($val=='0') continue;
					$value[]=$val;
				}
			}
		}
		if (is_array($value)) {
			foreach($value as $val) {
				if ($val=='') continue;
				if ($val=='0') continue;
				if ($val==null) continue;
				if ($result) $result.=', ';
				$result.="'{$this->php2Sql($val)}'";
			}
			if (!$result) $result="''";
		}
		else if ($value!='' && $value!='0' && $value!=null) {
			$result.="'{$this->php2Sql($value)}'";
		}
		return $result;
	}
	
	/// последний выполненный SQL запрос
	protected $_lastQuery=null;
/** Выполнить SQL запрос
 *
 * @param	string	$sql			SQL запрос
 * @param	string	$errorMessage	Текст сообщения об ошибке
 * @param	Array	$params			Параметры для подстановки в SQL запрос
 * @return	PDOStatement результат выполнения SQL запроса
 */
	public function pdo($sql, $errorMessage='', $params=Array()) {
		if (getCfg('trace.sql')) trace($sql."\n");
		$this->lastErrorMessage='';
		$result=$this->prepare($sql);
		if (!$result) {
			$errInfo=$this->errorInfo();
			$errorMessage.=' Ошибка в SQL запросе '.$errInfo[2]."\n".$sql;
			if (getCfg('trace.error.sql')) errorLog($errorMessage);
			$this->lastErrorMessage=$errInfo[2];
			throw new Exception($errorMessage);
		}
		if (!$result->execute($params)) {
			$errInfo=$result->errorInfo();
			$errorMessage.=' Ошибка в SQL запросе '.$errInfo[2]."\n".$sql;
			if (getCfg('trace.error.sql')) errorLog($errorMessage);
			$this->lastErrorMessage=$errInfo[2];
			throw new Exception($errorMessage);
		}
		$this->_lastQuery=$result;
		return $result;
	}
/** Вернуть ассоциативный массив строки результата выполнения запроса в виде 'имя поля' => 'значение поля'
 *
 * @param	anytype	$sql			строка SQL запроса или PDOStatement результата
 * @param	string	$errorMessage	Текст сообщения об ошибке
 * @param	Array	$params			Параметры для подстановки в SQL запрос
 * @return	Array ассоциативный массив результата выполнения SQL запроса
 */
	public function pdoFetch($sql, $errorMessage='', $params=Array()) {
		$q=null;
		$t=gettype($sql);
		if ($t=='string') $q=$this->pdo($sql, $errorMessage, $params);
		if ($t=='object' && $sql instanceof PDOStatement) $q=$sql;
		if (!$q) {
			$errorMessage.=' Неверный параметр sql';
			throw new Exception($errorMessage);
		}
		$result=$q->fetch(PDO::FETCH_ASSOC);
		if ($t=='string') $q->closeCursor();
		return $result;
	}
/** Вернуть кол-во строк в результате последнего запроса
 *
 * @return	num кол-во строк запроса
 */
	public function rowCount() {
		$result=0;
		if ($this->_lastQuery) $result=$this->_lastQuery->rowCount();
		return $result;
	}
/** Переопределение commit
 *
 * @return	boolean успешность
 */
	public function commit() {
		$result=parent::commit();
		if (!$result) {
			$errInfo=$this->errorInfo();
			$errorMessage='Ошибка при подтверждении SQL транзакции '.$errInfo[2];
			if (getCfg('trace.error.sql')) errorLog($errorMessage);
			$this->lastErrorMessage=$errInfo[2];
			throw new Exception($errorMessage);
		}
		return $result;
	}
}
/** Класс расширения функционала PDO для MySql
 */
class PDODataConnectorMySql extends PDODataConnector {
/** Конструктор - устанавливает соединение с базой данных
 *
 * @param	string	$dbName имя базы
 * @param	string	$login логин
 * @param	string	$password пароль
 * @param	string	$charset кодировка (utf8)
 * @param	string	$host хост (localhost)
 * @param	string	$port порт
 * @return	PDODataConnectorMySql соединение с базой данных
 */
	function __construct($dbName, $login, $password, $charset='utf8', $host='localhost', $port='') {
		try {
			$str="{$this->getDriverName()}:dbname={$dbName};host={$host}";
			if ($port) $str.=";port={$port}";
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
/** Получить имя драйвера SQL сервера
 *
 * @return	string имя драйвера SQL сервера - mysql
 */
	public function getDriverName() {
		return 'mysql';
	}
/** Подготовить строку для корректной вставки в SQL запрос
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2Sql($str) {
		return str2MySql($str);
	}
/** Подготовить строку для корректной вставки в SQL запрос в раздел like секции where
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2SqlLike($str) {
		$result=$this->str2Sql($str);
		$from=Array('%','_');
		$to=Array('','');
		return str_replace($from, $to, $result);
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
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
/** Класс расширения функционала PDO для PostgreSQL
 */
class PDODataConnectorPgSql extends PDODataConnector {
/** Конструктор - устанавливает соединение с базой данных
 *
 * @param	string	$dbName имя базы
 * @param	string	$login логин
 * @param	string	$password пароль
 * @param	string	$charset кодировка (utf8)
 * @param	string	$host хост (localhost)
 * @param	string	$port порт
 * @return	PDODataConnectorMySql соединение с базой данных
 */
	function __construct($dbName, $login, $password, $charset='utf8', $host='localhost', $port='') {
		$str="{$this->getDriverName()}:dbname={$dbName};host={$host}";
		if ($port) $str.=";port={$port}";
		try {
			parent::__construct($str, $login, $password);
			$this->setAttribute(PDO::ATTR_PERSISTENT, true); // режим пула соединений
			$this->setAttribute(PDO::CASE_LOWER, true);	// имена полей приводить к маленьким буквам
			
			$timeZone=getCfg('timezone');
			if ($timeZone) {
// проверка текущий временной зоны: SELECT current_setting('TIMEZONE');
				$timeZone=$this->str2Sql($timeZone);
				$sql=<<<SQL
set timezone='{$timeZone}';
SQL;
				$this->pdo($sql);
			}
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
/** Получить имя драйвера SQL сервера
 *
 * @return	string имя драйвера SQL сервера - pgsql
 */
	public function getDriverName() {
		return 'pgsql';
	}
/** Подготовить строку для корректной вставки в SQL запрос
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2Sql($str) {
		$result=$this->quote($str);
		if (strlen($result)>2 && substr($result,0,1)=="'" && substr($result,-1)=="'") {
			$result=substr($result,1,strlen($result)-2);
		}
		else {
			$result='';
		}
		return $result;
	}
/** Подготовить строку для корректной вставки в SQL запрос в раздел like секции where
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2SqlLike($str) {
		$result=$this->str2Sql($str);
		$from=Array('%','_');
		$to=Array('','');
		return str_replace($from, $to, $result);
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function php2Sql($value) {
		$result='';
		$t=gettype($value);
		if ($t=='object' && get_class($value)=='DateTime') {
			$result=$value->format('Y-m-d');
		}
		else if ($t=='boolean') {
			$result=($value)?1:0;
		}
		else if ($value!==null) {
			$result=$this->str2Sql($value);
		}
		return $result;
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос в раздел in секции where
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос в раздел in секции where
 */
	public function php2SqlIn($value) {
		$result='';
		if (is_string($value)) {
			if (strpos($value,',')!==false) {
				$lst=explode(',', $value);
				$value=Array();
				foreach($lst as $index=>$val) {
					if ($val=='') continue;
					if ($val=='0') continue;
					$value[$val]=$val;
				}
			}
		}
		if (is_array($value)) {
			foreach($value as $val) {
				if ($val=='') continue;
				if ($val=='0') continue;
				if ($val==null) continue;
				if ($result) $result.=', ';
				$result.="'{$this->php2Sql($val)}'";
			}
			if (!$result) $result='null';
		}
		else if ($value!='' && $value!='0' && $value!=null) {
			$result.="'{$this->php2Sql($value)}'";
		}
		return $result;
	}
}
/** Класс расширения функционала PDO для MSSQL
 */
class PDODataConnectorMSSql extends PDODataConnector {
/** Конструктор - устанавливает соединение с базой данных
 *
 * @param	string	$dbName имя базы
 * @param	string	$login логин
 * @param	string	$password пароль
 * @param	string	$charset кодировка (utf8)
 * @param	string	$host хост (localhost)
 * @param	string	$port порт
 * @param	string	$transactionisolation уровень изоляции транзакции
 * @return	PDODataConnectorMySql соединение с базой данных
 */
	function __construct($dbName, $login, $password, $charset='utf8', $host='localhost', $port='', $transactionisolation='read_commited') {
		$str="{$this->getDriverName()}:App=TestPdo;ConnectionPooling=1;Server={$host};Database={$dbName}";
		if ($transactionisolation=='read_uncommited') $str.=';TransactionIsolation='.PDO::SQLSRV_TXN_READ_UNCOMMITTED;
		if ($transactionisolation=='read_commited') $str.=';TransactionIsolation='.PDO::SQLSRV_TXN_READ_COMMITTED;
		if ($transactionisolation=='repeatable_read') $str.=';TransactionIsolation='.PDO::SQLSRV_TXN_REPEATABLE_READ;
		if ($transactionisolation=='snapshot') $str.=';TransactionIsolation='.PDO::SQLSRV_TXN_SNAPSHOT;
		if ($transactionisolation=='serializable') $str.=';TransactionIsolation='.PDO::SQLSRV_TXN_SERIALIZABLE;
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
/** Получить имя драйвера SQL сервера
 *
 * @return	string имя драйвера SQL сервера - sqlsrv
 */
	public function getDriverName() {
		return 'sqlsrv';
	}
/** Подготовить строку для корректной вставки в SQL запрос
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2Sql($str) {
		$result=$this->quote($str);
		if (strlen($result)>2 && substr($result,0,1)=="'" && substr($result,-1)=="'") {
			$result=substr($result,1,strlen($result)-2);
		}
		else {
			$result='';
		}
		return $result;
	}
/** Подготовить строку для корректной вставки в SQL запрос в раздел like секции where
 *
 * @param	string	$str строка
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
	public function str2SqlLike($str) {
		$result=$this->str2Sql($str);
		$from=Array('%','_');
		$to=Array('','');
		return str_replace($from, $to, $result);
	}
/** Подготовить выражение PHP для корректной вставки в SQL запрос
 *
 * @param	anytype	$value выражение
 * @return	string строка подготовленная для корректной вставки в SQL запрос
 */
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
/** Выполнить SQL запрос
 *
 * @param	string	$sql			SQL запрос
 * @param	string	$errorMessage	Текст сообщения об ошибке
 * @param	Array	$params			Параметры для подстановки в SQL запрос
 * @return	PDOStatement результат выполнения SQL запроса
 */
	public function pdo($sql, $errorMessage='', $params=Array()) {
		if ($sql) $sql='set dateformat ymd;'."\n".$sql;
		$result=parent::pdo($sql, $errorMessage, $params);
		return $result;
	}
}

/** Установить соединение с базой данных
 *
 * @param	string	$driverName имя драйвера - mysql | pgsql | sqlsrv
 * @param	string	$dbName имя базы
 * @param	string	$login логин
 * @param	string	$password пароль
 * @param	string	$charset кодировка (utf8)
 * @param	string	$host хост (localhost)
 * @param	string	$port порт
 * @return	PDODataConnector соединение с базой данных
 */
function newPDODataConnector($driverName, $dbName, $login, $password, $charset='utf8', $host='localhost', $port='') {
	if ($driverName=='mysql') {
		return new PDODataConnectorMySql($dbName, $login, $password, $charset, $host, $port);
	}
	else if ($driverName=='pgsql') {
		return new PDODataConnectorPgSql($dbName, $login, $password, $charset, $host, $port);
	}
	else if ($driverName=='sqlsrv') {
		return new PDODataConnectorMSSql($dbName, $login, $password, $charset, $host, $port);
	}
	else {
		throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
	}
}

//------------------------------------------------------------------------------
// Работа с иллюстрациями
//------------------------------------------------------------------------------
/** Преобразовать id иллюстрации к месту размещения файла в файловой системе
 *
 * @param	num		$klsris id иллюстрации
 * @return	string место размещения файла в файловой системе
 */
function getRisFileNameFromId($klsris) {
	$name=str_pad(ltrim($klsris),2,'0',STR_PAD_LEFT);
	$dir=str_pad(substr($name,0,strlen($name)-2),3,'0',STR_PAD_LEFT);
	return $dir.'/'.ltrim($klsris);
}
/** Альтернативный вариант преобразования, с 3-х уровневой системой вложенности и 255-ю файлами в каждой папке
 *
 * @param	num		$id id иллюстрации
 * @param	string	$fileName имя файла
 * @return	string место размещения файла в файловой системе
 */
function getArtPathFromId($id, $fileName='') {
	$name=str_pad(ltrim(dechex($id)),8,'0',STR_PAD_LEFT);
	$result=substr($name,0,2).'/'.substr($name,2,2).'/'.substr($name,4,2).'/'.(($fileName)?$fileName:$name);
	return $result;
}
/** Изменить размер рисунка
 *
 * @param	string	$sourceFileName имя исходного файла
 * @param	string	$resultFileName имя измененного файла
 * @param	string	$resWidth ширина измененного файла
 * @param	string	$resHeight высота измененного файла
 * @param	boolean	$isCover способ вписать в размер - cover или contain
 * @return	boolean успешность выполнения операции
 */
function doRisResize($sourceFileName, $resultFileName, $resWidth, $resHeight, $isCover) {
	$imgQuality=85;
	$result=true;
	try {
		$sourceImgSize=getimagesize($sourceFileName);
		if (!$sourceImgSize) throw new Exception('Ошибка при открытии файла иллюстрации!!!');
		$sourceWidth=$sourceImgSize[0];
		$sourceHeight=$sourceImgSize[1];
		if (($sourceWidth<=0) || ($sourceHeight<=0)) throw new Exception('Не удалось определить размеры изображения!');
		$img=@imagecreatefromjpeg($sourceFileName);
		if (!$img) throw new Exception('Ошибка при открытии файла!');
		
		if ($isCover) {
			$srcWidth=$sourceWidth;
			$srcHeight=$sourceHeight;
			if (floor($sourceHeight*($resWidth/$resHeight))<$srcWidth) $srcWidth=floor($sourceHeight*($resWidth/$resHeight));
			if (floor($sourceWidth*($resHeight/$resWidth))<$srcHeight) $srcHeight=floor($sourceWidth*($resHeight/$resWidth));
			$resImg=imagecreatetruecolor($resWidth,$resHeight);
			imagecopyresampled(
				$resImg,
				$img,
				0,0,floor(($sourceWidth-$srcWidth)/2),floor(($sourceHeight-$srcHeight)/2),
				$resWidth,$resHeight,$srcWidth,$srcHeight
			);
			imagejpeg($resImg, $resultFileName, $imgQuality);
			imagedestroy($resImg);
		} 
		else {
			$kX=$resWidth/$sourceWidth;
			$kY=$resHeight/$sourceHeight;
			$k=$kX;
			if ($k>$kY) $k=$kY;
			if ($k>1) {
				copy($sourceFileName, $resultFileName);
			}
			else {
				$resWidth=floor($sourceWidth*$k);
				$resHeight=floor($sourceHeight*$k);
				$resImg=imagecreatetruecolor($resWidth,$resHeight);
				imagecopyresampled(
					$resImg,
					$img,
					0,0,
					0,0,
					$resWidth,$resHeight,
					$sourceWidth,$sourceHeight
				);
				imagejpeg($resImg, $resultFileName, $imgQuality);
				imagedestroy($resImg);
			}
		}
		imagedestroy($img);
	}
	catch (Exception $e) {
		$result=false;
	}
	return $result;
}

//------------------------------------------------------------------------------
// Всяко полезное
//------------------------------------------------------------------------------
/** Запустить сервис асинхронно через HTTP запрос
 *
 * @param	string	$serviceName
 * @param	Array	$params
 * @param	Boolean	$isWaiting
 * @return	Boolean результат работы
 */
function execService($serviceName, $params=Array(), $isWaiting=false) {
	includeLib('ext-controller.php');
	$objServiceController=getServiceController($serviceName);
	
	$href=pathConcat(
		$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'],
		getCfg('href.root','/'),
		'services.php'
	);
	$href.='?name='.escape($serviceName);
	if (session_id()) {
		$href.='&sessionid='.escape(session_id());
		if (getCfg('csrftoken.enabled') && getPP('csrftoken')) {
			$href.='&csrftoken='.escape(getPP('csrftoken'));
		}
	}
	foreach($params as $name=>$value) {
		$href.='&'.escape($name).'='.escape($value);
	}
	
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $href);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_HEADER, false);	

	if (!$isWaiting) {
		/*Опции запроса, чтобы не дожидаться ответа*/
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_NOBODY, true);
	}

	/*Выполняем*/
	curl_exec($ch);
	curl_close($ch);
	return true;
}
/** Запустить утилиту асинхронно через HTTP запрос
 *
 * @param	string	$utilityName
 * @param	Array	$params
 * @param	Boolean	$isWaiting
 * @return	Boolean результат работы
 */
function execUtility($utilityName, $params=Array(), $isWaiting=false) {
	includeLib('ext-controller.php');
	$objUtilController=getUtilController($utilityName);
	$href=pathConcat(
		$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'],
		getCfg('href.root','/'),
		'utils.php'
	);
	$href.='?name='.escape($utilityName);
	if (session_id()) {
		$href.='&sessionid='.escape(session_id());
		if (getCfg('csrftoken.enabled') && getPP('csrftoken')) {
			$href.='&csrftoken='.escape(getPP('csrftoken'));
		}
	}
	foreach($params as $name=>$value) {
		$href.='&'.escape($name).'='.escape($value);
	}
	
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL, $href);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_HEADER, false);	

	if (!$isWaiting) {
		/*Опции запроса, чтобы не дожидаться ответа*/
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_NOBODY, true);
	}

	/*Выполняем*/
	curl_exec($ch);
	curl_close($ch);
	return true;
}
/** Выполнить сервис синхронно, внутри потока
 *
 * @param	string	$serviceName
 * @param	Array	$params разобранные параметры для метода getResult сервиса
 * @return	string результат работы
 */
function getServiceResult($serviceName, $params=Array()) {
	includeLib('ext-controller.php');
	$objServiceController=getServiceController($serviceName);
	$result=$objServiceController->getResult($params);
	return $result;
}

/** Выполнить утилиту синхронно, внутри потока
 *
 * @param	string	$utilityName
 * @param	Array	$params разобранные параметры для метода getResult сервиса
 * @return	string результат работы
 */
function getUtilityResult($utilityName, $params=Array()) {
	includeLib('ext-controller.php');
	$objUtilController=getUtilController($utilityName);
	ob_start();
	try {
		$result=$objUtilController->go($params);
	}
	finally {
		$result=ob_get_contents();
		ob_end_clean();
	}
	return $result;
}
/** Запустить утилиту через CLI
 *
 * @param	string	$utilityName
 * @param	Array	$params
 * @param	Boolean	$isWaiting
 * @return	string результат работы
 *
 * Путь до интерпретатора php берется из настройки path.cli.php
 */
function execUtilityCli($utilityName, $params=Array(), $isWaiting=false) {
	$pathPHP=getCfg('path.cli.php');
	$cmd="{$pathPHP} utils.php name=".escapeshellcmd($utilityName);
	if (session_id()) {
		$cmd.=' sessionid='.escapeshellcmd(session_id());
		if (getCfg('csrftoken.enabled') && getPP('csrftoken')) {
			$cmd.=' csrftoken='.escapeshellcmd(getPP('csrftoken'));
		}
	}
	foreach($params as $name=>$value) {
		$cmd.=' '.escapeshellcmd($name).'='.escapeshellcmd($value);
	}

	$result='';
	$dir=getcwd();
	try {
		$path=getCfg('path.root');
		if ($path) chdir($path);
		
		if (!$isWaiting) {
			if (strtoupper(substr(PHP_OS, 0, 3))=='WIN') {
				$handle=popen('start /b '.$cmd.' > nul', 'r');
				if ($handle!==false) pclose($handle);
			}
			else {
				exec($cmd." > /dev/null &");
			}
		}
		else {
			exec($cmd, $output);
			$result=implode("\n", $output);
		}
	}
	finally {
		chdir($dir);
	}
	return $result;
}


/** Подключить библиотеку из папки lib
 *
 * @param	string	$libFileName
 */
function includeLib($libFileName) {
	$fileName=pathConcat(
		getCfg('path.root'),
		getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
		'lib',
		$libFileName
	);
	include_once($fileName);
}
/** Подключить библиотеку из папки extlib
 *
 * @param	string	$libFileName
 */
function includeExtLib($libFileName) {
	$fileName=pathConcat(
		getCfg('path.root'),
		getCfg('path.root.extlib'),
		$libFileName
	);
	include_once($fileName);
}
/** Подключить библиотеку из папки module
 *
 * @param	string	$libFileName
 */
function includeModule($libFileName) {
	$fileName=pathConcat(
		getCfg('path.root'),
		getCfg('path.root.php'),
		$libFileName
	);
	include_once($fileName);
}

/** Преобразовать файл к HTML (требует LibreOffice!!!)
 *
 * @param	string	$sourceFileName имя исходного файла
 * @param	string	$resultPath имя папки для результата
 * @param	num		$maxTime максимальное время ожидания (в секундах)
 * @return	boolean успешность выполнения операции
 */
function convertFileToHtml($sourceFileName, $resultPath, $maxTime=10) {
	if (!getCfg('path.libreoffice')) throw new Exception("Не установлен пакет libreoffice!!!");
	if (!is_file($sourceFileName)) throw new Exception("Не найден исходный файл '{$sourceFileName}'");
	$pathinfo=pathinfo($sourceFileName);
	$ext=$pathinfo['extension'];
	
	if (!is_dir($resultPath)) mkdir($resultPath,0777, true);
	$resultFileName=pathConcat($resultPath, 'index.'.$ext);
	copy($sourceFileName, $resultFileName);
	
	$cmd=getCfg('path.libreoffice').' --headless --convert-to html '.'index.'.$ext;
	$dir=getcwd();
	try {
		chdir($resultPath);
		if (getCfg('path.libreoffice.temp')) putenv('HOME='.getCfg('path.libreoffice.temp'));
		exec($cmd);
	}
	finally {
		chdir($dir);
	}
	$fileNameWaiting=pathConcat(
		$resultPath,
		'index.html'
	);
	for($i=0; $i<$maxTime; $i++) {
		if (is_file($fileNameWaiting)) {
			unlink($resultFileName);
			break;
		}
		sleep(1);
	}
	if (!is_file($fileNameWaiting)) throw new Exception('Преобразование выполнить не удалось!!!');
	return true;
}
/** Преобразовать PDF к JPG 
 *
 * @param	string	$sourceFileName имя исходного файла
 * @param	string	$resultFileName имя файла результата
 * @param	num		$maxTime максимальное время ожидания (в секундах)
 * @return	boolean успешность выполнения операции
 */
function convertPdfToJpg($sourceFileName, $resultFileName, $maxTime=10) {
	if (!getCfg('path.imagemagick')) throw new Exception("Не установлен пакет imagemagick!!!");
	if (!is_file($sourceFileName)) throw new Exception("Не найден исходный файл '{$sourceFileName}'");
	
	$pathinfo=pathinfo($resultFileName);
	$dirname=$pathinfo['dirname'];
	if (!is_dir($dirname)) mkdir($dirname, 0777, true);
	
	$cmd=getCfg('path.imagemagick')." -density 150 {$sourceFileName}[0] {$resultFileName}";
	exec($cmd);
	
	if (!is_file($resultFileName)) throw new Exception('Преобразование выполнить не удалось!!!');
	return true;
}
/** Трассировка
 *
 * @param	anytype	$value выражение которое надо поместить в log файл
 */
function trace($value) {
	$path=pathConcat(getCfg('path.root'), getCfg('path.root.log','log'));
	if (!is_dir($path)) mkdir($path, 0777, true);
	$fileName=pathConcat(getCfg('path.root'),getCfg('path.root.log'),date('Y-m-d').'-trace.log');
	if (!$handle = fopen($fileName, 'a')) throw new Exception("Не удалось открыть файл '{$fileName}'");
	if (gettype($value)=='string') {
		$str=$value;
	} else {
		$str=var_export($value, true);
	}
	$str.="\n";
	$str.="--------------------------\n";
	if (fwrite($handle, $str) === FALSE) throw new Exception("Не удалось произвести запись файл '{$fileName}'");
	fclose($handle);
}
/** Логирование ошибок
 *
 * @param	Exception	$e ошибка
 */
function errorLog($e) {
	$path=pathConcat(getCfg('path.root'), getCfg('path.root.log','log'));
	if (!is_dir($path)) mkdir($path, 0777, true);
	$fileName=pathConcat(getCfg('path.root'),getCfg('path.root.log'),date('Y-m-d').'-logerror.log');
	if (!$handle = fopen($fileName, 'a')) throw new Exception("Не удалось открыть файл '{$fileName}'");
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
	if (fwrite($handle, $result) === FALSE) throw new Exception("Не удалось произвести запись файл '{$fileName}'");
	fclose($handle);
}
/** Удаление старых лог файлов, количество дней хранения из настройки getCfg('log.day.limit')
 *
 */
function deleteOldLogFiles() {
	$path=pathConcat(getCfg('path.root'), getCfg('path.root.log','log'));
	if (!is_dir($path)) return false;
    $dir=opendir($path);
	$lstDel=Array();
	
	$logDayLimit=intval(getCfg('log.day.limit', 30));
	$ddd=new DateTime();
	$ddd->modify("-{$logDayLimit} day");
    while(($fileName=readdir($dir))!==false) {
		if ($fileName==".") continue;
		if ($fileName=="..") continue;
		$fullName=pathConcat($path, $fileName);
		if (is_dir($fullName)) continue;
		$fileinfo=pathinfo($fileName);
		$ext=strtolower($fileinfo['extension']);
		if ($ext!='log') continue;
		
		if (!is_file($fullName)) continue;
		$d=new DateTime(date('Y-m-d',filemtime($fullName)));
		if ($d<=$ddd) {
			$lstDel[]=$fileName;
			continue;
		}
    }
	foreach($lstDel as $fileName) {
		$fullName=pathConcat($path, $fileName);
		if (is_file($fullName)) unlink($fullName);
	}
}
/** Инициализация настроек: логирование ошибок в файл и задание часового пояса
 *
 */
function initPhpErrorLog() {
	$path=pathConcat(getCfg('path.root'), getCfg('path.root.log','log'));
	if (!is_dir($path)) mkdir($path, 0777, true);
	ini_set('display_errors','Off');
	ini_set('log_errors','On');
	$logFileName=pathConcat(getCfg('path.root'),getCfg('path.root.log'),date('Y-m-d').'-phperror.log');
	ini_set('error_log',$logFileName);
	$timeZone=getCfg('timezone');
	if ($timeZone) ini_set('date.timezone', $timeZone);
}
