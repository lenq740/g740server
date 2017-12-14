<?php
/**
Библиотека функций - расширение базового набора под формирование HTML страниц
@package module-lib
@subpackage module-lib-g740server
*/
require_once('module-lib-base.php');

/**
Преобразование текстового абзаца в HTML со сложными заменами
@param	String	$str исходная строка
@return	String преобращованная строка
*/
function str2ExtHtml($str) {
	$str=_str2HtmlSimpleReplace($str);		// Выполняем простые замены
	$str=_str2HtmlRTrim($str);				// Удаляем пробелы в конце строки
	$str=_str2HtmlBold($str);				// Обрабатываем жирный текст
	$str=_str2HtmlCfg($str);				// Обрабатываем $$cfg$$
	$str=_str2HtmlNoindex($str);			// Обрабатываем noindex
	$str=_str2HtmlH2($str);					// Обрабатываем заголовки h2
	$str=_str2HtmlUl($str);					// Обрабатываем списки
	$str=_str2HtmlDoubleCR($str);			// Обрабатываем двойной CR как смену абзаца
	$str=_str2HtmlCR($str);					// Обрабатываем одинарный CR как новую строку внутри абзаца
	$str=_str2HtmlDoubleBrace($str);		// Обрабатываем макроподстановку в двойных фигурных скобках
	return $str;
}
// Преобразование текстового абзаца в HTML, простые замены
function _str2HtmlSimpleReplace($str) {
	$str=str_replace('<b>','{{b}}',$str);
	$str=str_replace('</b>','{{/b}}',$str);
	$str=str_replace('<->','{{-}}',$str);
	$str=str_replace('<','&#060;',$str);
	$str=str_replace('>','&#062;',$str);
	$str=str_replace("\r",'',$str);
	$str=str_replace("\t",' ',$str);
	$str=str_replace('{{-}}','<->',$str);
	$str=str_replace('{{b}}','<b>',$str);
	$str=str_replace('{{/b}}','</b>',$str);
	return $str;
}
// Преобразование текстового абзаца в HTML, удаление пробелов в конце строки
function _str2HtmlRTrim($str) {
	$regExpr='{'.'[\040]+$'.'}m';
	$str=preg_replace_callback($regExpr, '_str2HtmlCallback', $str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка <b> и **
function _str2HtmlBold($str) {
	$regExpr=
	'{'.
		'\*\*.*?\*\*'.
		'|'.'<b>'.
		'|'.'</b>'.
	'}';
	$str=preg_replace_callback($regExpr, '_str2HtmlCallback', $str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка <noindex>
function _str2HtmlNoindex($str) {
	$from=Array('{{noindex}}','{{/noindex}}');
	$to=Array('<!--noindex-->','<!--/noindex-->');
	$result=str_replace($from, $to, $str);
	return $result;
}
// Преобразование текстового абзаца в HTML, обработка h2
function _str2HtmlH2($str) {
	$regExpr=
	'{'.'^[\040]*===.*?===$'.'}m';
	$str=preg_replace_callback($regExpr, '_str2HtmlCallback', $str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка $$cfg$$
function _str2HtmlCfg($str) {
	$regExpr=
	'{'.'\$\$.*?\$\$'.'}';
	$str=preg_replace_callback($regExpr, '_str2HtmlCallback', $str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка списков
function _str2HtmlUl($str) {
	$regExpr=
	'{'.'^-[\040].*$'.'}m';
	$str=preg_replace_callback($regExpr, '_str2HtmlCallback', $str);
	$str=str_replace('</ul>'."\n".'<ul>','',$str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка двойного CR как смена абзаца
function _str2HtmlDoubleCR($str) {
	if (strpos($str,"\n\n")===false) return $str;
	$str='<p>'.str_replace("\n\n",'</p><p>',$str).'</p>';

	$str=str_replace('<h2>','</p><h2>',$str);
	$str=str_replace('</h2>','</h2><p>',$str);

	$str=str_replace('<ul>','</p><ul>',$str);
	$str=str_replace('</ul>','</ul><p>',$str);
	
	$str=str_replace('<p></p>','',$str);
	$str=str_replace('<p>'."\n".'</p>','',$str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка одинарного CR
function _str2HtmlCR($str) {
	$str=str_replace("\n".'<h2>','<h2>',$str);
	$str=str_replace('</h2>'."\n",'</h2>',$str);

	$str=str_replace("\n".'<ul>','<ul>',$str);
	$str=str_replace('</ul>'."\n",'</ul>',$str);
	
	$str=str_replace("\n",'<br>',$str);
	return $str;
}
// Преобразование текстового абзаца в HTML, обработка макроподстановки {{}}
function _str2HtmlDoubleBrace($str) {
	$regExpr='{'.'\{\{.*?\}\}'.'}';
	$str=preg_replace_callback($regExpr, '_str2HtmlCallbackDoubleBrace', $str);
	return $str;
}
// Преобразование текстового абзаца в HTML, универсальная функция обратного вызова
function _str2HtmlCallback($matches) {
	$expr=$matches[0];
	if (trim($expr,' ')=='') return '';
	if ($expr=='<b>') return '<span class="bold">';
	if ($expr=='</b>') return '</span>';
	if (substr($expr,0,2)=='**') return '<span class="bold">'.substr($expr,2,strlen($expr)-4).'</span>';
	if (substr($expr,0,2)=="- ") return '<ul><li>'.substr($expr,2,strlen($expr)-2).'</li></ul>';
	if (substr(trim($expr),0,2)=="$$") {
		$expr=trim($expr);
		$name=mb_substr($expr,2,mb_strlen($expr)-4);
		return getCfg($name);
	}
	if (substr(trim($expr),0,3)=="===") {
		$expr=trim($expr);
		return '<h2>'.mb_substr($expr,3,mb_strlen($expr)-6).'</h2>';
	}
	return $expr;
}
// Преобразование текстового абзаца в HTML, функция обратного вызова макроса {{}}
function  _str2HtmlCallbackDoubleBrace($matches) {
	$expr=$matches[0];
	if (substr($expr,0,2)=='{{') {
		$regExpr=
'{^\{\{'.
	'(?:'.
		'\h*'.'|'.
		'href="(?<href>.*?)"'.'|'.
		'tel="(?<tel>.*?)"'.'|'.
		'mailto="(?<mailto>.*?)"'.'|'.
		
		'mode="(?<mode>.*?)"'.'|'.
		'klstrade="(?<klstrade>.*?)"'.'|'.
		'klstrades2s="(?<klstrades2s>.*?)"'.'|'.
		'klstradesection="(?<klstradesection>.*?)"'.'|'.
		'klstraderubric="(?<klstraderubric>.*?)"'.'|'.
		'page="(?<page>.*?)"'.'|'.
		
		'text="(?<text>.*?)"'.'|'.
		'cfg="(?<cfg>.*?)"'.'|'.
		'nofollow="(?<nofollow>.*?)"'.'|'.
		'\w*="\w*"'.
	')*'.
'\}\}}';
		$res=Array();
		preg_match($regExpr, $expr, $res);
		
		if ($res['cfg']) {
			return str2Html(getCfg($res['cfg']));
		}
		else {
			$target='';
			$nofollow='';
			$href='#';
			if ($res['tel']) {
				$attrTel=str2Attr($res['tel']);
				$htmlTel=str2Html($res['tel']);
				$result=<<<HTML
<a href="tel:{$attrTel}" class="tel">{$htmlTel}</a>
HTML;
				return $result;
			}
			else if ($res['mailto']) {
				$attrMailTo=str2Attr($res['mailto']);
				$htmlMailTo=str2Html($res['mailto']);
				$result=<<<HTML
<a href="mailto:{$attrMailTo}" class="mailto">{$htmlMailTo}</a>
HTML;
				return $result;
			}
			else if ($res['href']) {
				$href=str2Attr($res['href']);
				$target='target="_blank"';
			} else {
				$href=getHref($res);
			}
			if ($res['nofollow']) {
				$nofollow='rel="nofollow"';
			}
			$text=$href;
			if ($res['text']) $text=str2Html($res['text']);
			
			$attr='';
			if ($target) $attr.=' '.$target;
			if ($nofollow) $attr.=' '.$nofollow;
			$attr=trim($attr);
			if ($attr) $attr=' '.$attr.' ';
			
			$result='<a href="'.$href.'"'.$attr.'>'.$text.'</a>';
			return $result;
		}
	}
	return $expr;
}

/**
Выкидывание из текстового абзаца специфики HTML
@param	String	$str исходная строка
@return	String преобращованная строка
*/
function str2ExtText($str) {
	$str=_str2ExtTextSimpleReplace($str);	// Выполняем простые замены
	$str=_str2TextCfg($str);				// Обрабатываем $$cfg$$
	$str=_str2TextH2($str);					// Обрабатываем заголовки h2
	$str=_str2TextDoubleBrace($str);		// Обрабатываем макроподстановку в двойных фигурных скобках
	$str=trim($str);
	return $str;
}
// Преобразование текстового абзаца
function _str2ExtTextSimpleReplace($str) {
	$from=['<b>','</b>','**',"\r","\n","\t",'{{noindex}}','{{/noindex}}'];
	$to=['','','','',' ',' ','',''];
	$str=str_replace($from,$to,$str);
	$str=str_replace('  ',' ',$str);
	return $str;
}
// Преобразование текстового абзаца, обработка $$cfg$$
function _str2TextCfg($str) {
	$regExpr=
	'{'.'\$\$.*?\$\$'.'}';
	$str=preg_replace_callback($regExpr, '_str2TextCallback', $str);
	return $str;
}
// Преобразование текстового абзаца, обработка h2
function _str2TextH2($str) {
	$regExpr=
	'{'.'^[\040]*===.*?===$'.'}m';
	$str=preg_replace_callback($regExpr, '_str2TextCallback', $str);
	return $str;
}
// Преобразование текстового абзаца, обработка макроподстановки {{}}
function _str2TextDoubleBrace($str) {
	$regExpr='{'.'\{\{.*?\}\}'.'}';
	$str=preg_replace_callback($regExpr, '_str2TextCallbackDoubleBrace', $str);
	return $str;
}
// Преобразование текстового абзаца в HTML, универсальная функция обратного вызова
function _str2TextCallback($matches) {
	$expr=$matches[0];
	if (trim($expr,' ')=='') return '';
	if (substr(trim($expr),0,2)=="$$") {
		$expr=trim($expr);
		$name=mb_substr($expr,2,mb_strlen($expr)-4);
		return getCfg($name);
	}
	if (substr(trim($expr),0,3)=="===") {
		$expr=trim($expr);
		return mb_substr($expr,3,mb_strlen($expr)-6);
	}
	if (substr($expr,0,2)=="- ") return substr($expr,2,strlen($expr)-2);
	return $expr;
}
// Преобразование текстового абзаца в HTML, функция обратного вызова макроса {{}}
function  _str2TextCallbackDoubleBrace($matches) {
	$expr=$matches[0];
	if (substr($expr,0,2)=='{{') {
		$regExpr=
'{^\{\{'.
	'(?:'.
		'\h*'.'|'.
		'href="(?<href>.*?)"'.'|'.
		'tel="(?<tel>.*?)"'.'|'.
		'mailto="(?<mailto>.*?)"'.'|'.
		
		'mode="(?<mode>.*?)"'.'|'.
		'klstrade="(?<klstrade>.*?)"'.'|'.
		'klstrades2s="(?<klstrades2s>.*?)"'.'|'.
		'klstradesection="(?<klstradesection>.*?)"'.'|'.
		'klstraderubric="(?<klstraderubric>.*?)"'.'|'.
		'page="(?<page>.*?)"'.'|'.
		
		'text="(?<text>.*?)"'.'|'.
		'cfg="(?<cfg>.*?)"'.'|'.
		'nofollow="(?<nofollow>.*?)"'.'|'.
		'\w*="\w*"'.
	')*'.
'\}\}}';
		$res=Array();
		preg_match($regExpr, $expr, $res);
		if ($res['cfg']) {
			return getCfg($res['cfg']);
		}
		else {
			if ($res['tel']) {
				return $res['tel'];
			}
			else if ($res['mailto']) {
				return $res['mailto'];
			}
			else if ($res['text']) {
				return $res['text'];
			}
		}
	}
	return '';
}

// Сдвиг текстового блока вправо на заданное кол-во знаков табуляции
function strTabShift($str, $tabShift) {
	$strTab='';
	for($i=0; $i<$tabShift; $i++) $strTab.="\t";
	$str=str_replace("\r", "", $str);
	$isCrStart=(substr($str,0,1)=="\n");
	if (!$isCrStart) {
		$str="\n".$str;
	}
	$from=Array(
		"\n<?php",
		"\n?>",
		"\n"
	);
	$to=Array(
		"\r<?php",
		"\r?>",
		"\n".$strTab,
	);
	$str=str_replace($from, $to, $str);
	$str=str_replace("\r", "\n", $str);
	if (!$isCrStart) {
		$str=substr($str,1);
	}
	return $str;
}

?>