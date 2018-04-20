<?php
/**
 * @file
 * контроллер визуализации страниц сайта
 */
require_once('lib-base.php');
require_once('lib-g740server.php');
require_once('lib-html.php');
require_once('perm-controller.php');
require_once('datasource-controller.php');
 
/** Класс PageViewer - предок визуализаторов страниц сайта
 */
class PageViewer {
	public $mode='abstract';
	
/** Конструктор, регистрация экземпляра класса
 */
	function __construct() {
		global $_lstModeOfObjPage;
		global $_htObjPage;
		if (!$_htObjPage[$this->mode]) $_lstModeOfObjPage[]=$this->mode;
		$_htObjPage[$this->mode]=$this;
	}
/** Разбор параметров для запроса к странице
 *
 * @param	Array	$lstUrl	массив адресов запроса
 * @return	Array	начитанные параметры
 */
	public function getInputParams($lstUrl=Array()) {
		return Array();
	}
/** Получить адрес ссылки по параметрам
 *
 * @param	Array	$params
 * @return	String	адрес ссылки по параметрам
 */
	public function getHref($params=Array()) {
		$errorMessage='Ошибка при обращении к PageViewer::getHref';
		throw new Exception($errorMessage.', обращение к абстрактному методу');
	}
/** Получить дополнительные параметры страницы
 *
 * @param	Array	$params
 * @return	Array	дополнительные параметры страницы
 */
	public function getPageParams($params=Array()) {
		$result=Array();
		$result['head.h1']='Абстрактный предок PageViewer';
		
		$hrefRoot=pathConcat(getCfg('href.root'));
		if ($hrefRoot!='') $hrefRoot='/'.$hrefRoot;
		$result['href.root']=$hrefRoot;
		
		$hrefResource=pathConcat(getCfg('href.root'),getCfg('path.root.resource'));
		if ($hrefResource!='') $hrefResource='/'.$hrefResource;
		$result['href.resource']=$hrefResource;
		return $result;
	}
/** Отослать HTTP заголовки ответа
 *
 * @param	Array	$params
 */
	public function sendHttpHeaders($params=Array()) {
		header('Content-type: text/html; charset=utf-8');
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
	}

/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	public function getPage($params=Array()) {
		$info=Array();
		$info['html-head']=$this->getPageHead($params);
		$info['html-h1']=str2Html($params['head.h1']);
		$info['html-body']=$this->getPageBody($params);
		$info['attr-href-root']=str2Attr($params['href.root']);
		$info['attr-href-resource']=str2Attr($params['href.resource']);
		$result=$this->templatePage($info, $params);
		return $result;
	}
/** Шаблон содержимого страницы
 *
 * @param	Array	$info
 * @param	Array	$params
 * @return	String	Шаблон содержимого страницы
 *
 * - $info['html-head']
 * - $info['html-body']
 * - $info['html-breadcrumb']
 * - $info['html-h1']
 * - $info['attr-href-root']
 * - $info['attr-href-resource']
 */
	protected function templatePage($info, $params) {
		return '';
	}
/** Заголовок страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое заголовка страницы
 */
	protected function getPageHead($params=Array()) {
		return '';
	}
/** Содержимое тела страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое тела страницы
 */
	protected function getPageBody($params=Array()) {
		return '';
	}
}

/** Класс PageViewerBootStrap - предок визуализаторов страниц сайта на BootStrap
 */
class PageViewerBootStrap extends PageViewer {
	public $mode='abstract.bootstrap';

/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	public function getPage($params=Array()) {
		$info=Array();
		$info['html-head']=$this->getPageHead($params);
		$info['html-h1']=str2Html($params['head.h1']);
		$info['html-breadcrumb']=$this->getBreadCrumb($params);
		$info['html-body']=$this->getPageBody($params);
		$info['attr-href-root']=str2Attr($params['href.root']);
		$info['attr-href-resource']=str2Attr($params['href.resource']);
		$result=$this->templatePage($info, $params);
		return $result;
	}
/** Шаблон содержимого страницы
 *
 * @param	Array	$info
 * @param	Array	$params
 * @return	String	Шаблон содержимого страницы
 *
 * - $info['html-head']
 * - $info['html-body']
 * - $info['html-breadcrumb']
 * - $info['html-h1']
 * - $info['attr-href-root']
 * - $info['attr-href-resource']
 */
	protected function templatePage($info, $params) {
		$result=<<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
{$info['html-head']}
</head>
<body>

<div class="container">
{$info['html-breadcrumb']}
	<div class="row">
		<div class="col-xs-12">
<h1>{$info['html-h1']}</h1>

<div class="h1-delimiter"></div>

{$info['html-body']}
		</div>
	</div>
</div>

<script src="{$info['attr-href-resource']}/jquery-1.12.4.min.js"></script>
<script src="{$info['attr-href-resource']}/bootstrap-3.3.6/js/bootstrap.js"></script>
</body>
</html>
HTML;
		return $result;
	}

/** Заголовок страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое заголовка страницы
 */
	protected function getPageHead($params=Array()) {
		$info=Array();
		$info['html-title']=str2Html($params['head.title']?$params['head.title']:$params['head.h1']);
		$info['attr-description']=str2Attr($params['head.description']?$params['head.description']:$params['head.h1']);
		$info['attr-keywords']=str2Attr($params['head.keywords']?$params['head.keywords']:$params['head.h1']);
		$info['attr-href-root']=str2Attr($params['href.root']);
		$info['attr-href-resource']=str2Attr($params['href.resource']);
		$result=$this->templatePageHead($info, $params);
		return $result;
	}
/** Шаблон заголовка страницы
 *
 * @param	Array	$info
 * @param	Array	$params
 * @return	String	Содержимое заголовка страницы
 *
 * - $info['html-title']
 * - $info['attr-description']
 * - $info['attr-keywords']
 * - $info['attr-href-root']
 * - $info['attr-href-resource']
 */
	protected function templatePageHead($info=Array(), $params=Array()) {
		$result=<<<HTML
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{$info['html-title']}</title>
<meta name="Description" content="{$info['attr-description']}">
<meta name="KeyWords" content="{$info['attr-keywords']}">
<link rel="shortcut icon" href="{$info['attr-href-root']}/favicon.ico" type="image/x-icon"/>
<link href="{$info['attr-href-resource']}/bootstrap-3.3.6/css/bootstrap.css" rel="stylesheet">
<link href="{$info['attr-href-resource']}/bootstrap-3.3.6/css/bootstrap-theme.css" rel="stylesheet">
HTML;
		return $result;
	}

/** Хлебные крошки
 *
 * @param	Array	$params
 * @return	String	Содержимое тела страницы
 */
	protected function getBreadCrumb($params) {
		$htmlBreadCrumb='';
		$lst=$this->getBreadCrumbList($params);
		if (count($lst)>0) $lst[count($lst)-1]['current']=1;
		foreach($lst as $p) {
			$info=Array();
			$info['href']=str2Attr($p['href']);
			$info['html-name']=str2Html($p['name']);
			$info['is-current']=$p['current']?1:0;
			$html=$this->templateBreadCrumbItem($info, $params);
			if ($htmlBreadCrumb) $htmlBreadCrumb.="\n";
			$htmlBreadCrumb.=$html;
		}
		$info=Array();
		$info['html-breadcrumb']=$htmlBreadCrumb;
		$result=$this->templateBreadCrumb($info, $params);
		return $result;
	}
/** Массив описаний хлебных крошек
 *
 * @param	Array	$params
 * @return	Array	Массив описаний хлебных крошек
 */
	protected function getBreadCrumbList($params) {
		$result=Array();
		$result[]=Array(
			'href'=>'/',
			'name'=>'Сайт ЦТИ'
		);
		return $result;
	}
/** Шаблон элемента хлебных крошек
 *
 * @param	Array	$info
 * @param	Array	$params
 * @return	String	Содержимое заголовка страницы
 *
 * - $info['href']
 * - $info['html-name']
 * - $info['is-current']
 */
	protected function templateBreadCrumbItem($info=Array(), $params=Array()) {
		$result=<<<HTML
<li><a href="{$info['href']}">{$info['html-name']}</a></li>
HTML;
		if ($info['is-current']) {
			$result=<<<HTML
<li class="active">{$info['html-name']}</li>
HTML;
		}
		return $result;
	}
/** Шаблон хлебных крошек
 *
 * @param	Array	$info
 * @param	Array	$params
 * @return	String	Содержимое заголовка страницы
 *
 * - $info['html-breadcrumb']
 */
	protected function templateBreadCrumb($info=Array(), $params=Array()) {
		$result=<<<HTML
<ul class="breadcrumb">
{$info['html-breadcrumb']}
</ul>
HTML;
		return $result;
	}
}

/** Класс Widget - предок виджетов
 */
class Widget {
/// имя виджета
	public $name='';
/// источник данных
	public $objDataStorage=null;
/// родительский объект, используемый для обработки событий
	public $objCallback=null;
/// заранее начитанный список строк
	public $items=null;
/// условия для начитки списка строк
	public $filter=Array();

/** Содержимое виджета
 *
 * @param	Array	$info
 * @return	String	Содержимое виджета
 *
 * - $info['objPageViewer']
 * - $info['datasource']
 * - $info['objDataStorage']
 * - $info['items']
 */
	public function get() {
		return '';
	}
}

/** Получить PageViewer по имени
 *
 * @param	string	$mode
 * @return	PageViewer
 */
function getObjPage($mode) {
	global $_htObjPage;
	$result=$_htObjPage[$mode];
	if (!$result) $result=$_htObjPage['default'];
	if (!$result) throw new Exception("Не найдена страница '{$mode}'");
	if (!$result instanceof PageViewer) throw new Exception("Не найдена страница '{$mode}'");
	return $result;
}
/** Получить Widget по имени
 *
 * @param	string	$name
 * @return	Widget
 */
function getWidget($name) {
	global $_registerWidget;
	
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	if ($name!=$str) throw new Exception("Недопустимое имя виджета '{$name}'");
	if (!$_registerWidget[$name]) {
		$fileName=pathConcat(getCfg('path.root'), getCfg('path.root.module'), 'widgets', "{$name}.php");
		if (file_exists($fileName)) {
			$obj=include_once($fileName);
			if ($obj instanceof Widget) $_registerWidget[$name]=$obj;
		}
		if (!$_registerWidget[$name]) throw new Exception("Виджет не зарегистрирован '{$name}'");
	}
	$widgetClassName=get_class($_registerWidget[$name]);
	$objWidget=new $widgetClassName();
	$objWidget->name=$name;
	return $objWidget;
}

/** Разобрать входные параметры URL
 *
 * Разбираем URL из $_SERVER["REQUEST_URI"] URL может быть либо одноуровневым, 
 * исходя из принудительных имен страниц либо многоуровневым исходя из структуры сайта 

 * @return	Array	начитанные параметры
 */
function getInputParams() {
	global $_lstModeOfObjPage;
	$result=Array();
	// Вытаскиваем из запроса URL
	$scriptUrl=$_SERVER["SCRIPT_NAME"];
	//trace($_SERVER["REQUEST_URI"]);
	
	$pos=mb_strripos($scriptUrl, '/index.php');
	if ($pos===false) throw new Exception('Не удалось обработать адрес входного запроса, _SERVER[SCRIPT_NAME] не содержит /index.php');
	$url=trim(mb_strtolower(mb_substr($_SERVER["REQUEST_URI"],$pos+1,999)));
	$pos=mb_strpos($url,'#');
	if ($pos!==false) $url=mb_substr($url, 0, $pos);
	$pos=mb_strpos($url,'?');
	if ($pos!==false) $url=mb_substr($url, 0, $pos);
	if ($url!=str2MySql($url)) throw new Exception("Некорректный параметр, url={$url}");

	$lstUrl=explode('/',$url);
	while(count($lstUrl)>0 && trim($lstUrl[count($lstUrl)-1])=='') unset($lstUrl[count($lstUrl)-1]);

	foreach($_lstModeOfObjPage as $mode) {
		$objPage=getObjPage($mode);
		if (!$objPage) continue;
		$result=$objPage->getInputParams($lstUrl);
		if ($result['mode']) break;
	}
	if (!$result['mode']) $result['mode']='404';
	$objPage=getObjPage($result['mode']);
	if ($objPage) {
		$p=$objPage->getPageParams($result);
		foreach($p as $key=>$value) $result[$key]=$value;
	}
	return $result;
}
/** Получить адрес ссылки по параметрам
 * @param	Array	$params
 * @return	String	адрес ссылки по параметрам
 */
function getHref($params=Array()) {
	$objPage=getObjPage($params['mode']);
	$href='';
	if ($objPage) $href=$objPage->getHref($params);
	return '/'.pathConcat(getCfg('href.root'), $href);
}

$_lstModeOfObjPage=Array();
$_htObjPage=Array();
$_registerWidget=Array();
