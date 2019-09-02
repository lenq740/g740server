<?php
/**
 * @file
 * G740Server, контроллер визуализации страниц сайта
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

require_once('lib-base.php');
require_once('lib-html.php');
require_once('perm-controller.php');
require_once('datasource-controller.php');

/** Класс PageViewer - предок визуализаторов страниц сайта
 */
class PageViewer {
/** href страницы в зависимости от параметров
 *
 * @param	Array	$params
 * @return	String	href страницы
 */
	public function getHref($params=Array()) {
		return '';
	}
/** Отобразить страницу
 *
 * @param	Array	$params
 */
	public function go($params=Array()) {
		$p=$this->getPageParams($params);
		foreach($p as $name=>$value) $params[$name]=$value;
		$html=$this->getPage($params);
		$this->sendHttpHeaders($params);
		echo $html;
	}
/** Получить дополнительные параметры страницы
 *
 * @param	Array	$params
 * @return	Array	дополнительные параметры страницы
 */
	protected function getPageParams($params=Array()) {
		$result=Array();
		$result['head.h1']='Абстрактный предок PageViewer';
		return $result;
	}
/** HTTP заголовки ответа
 *
 * @param	Array	$params
 */
	protected function sendHttpHeaders($params=Array()) {
		header('Content-type: text/html; charset=utf-8');
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
	}
/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	protected function getPage($params=Array()) {
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
	protected function templatePage($info=Array(), $params=Array()) {
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
/** Содержимое страницы
 *
 * @param	Array	$params
 * @return	String	Содержимое страницы
 */
	protected function getPage($params=Array()) {
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
	protected function templatePage($info=Array(), $params=Array()) {
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
		$info['html-title']=str2Html($params['project.title']?$params['project.title']:$params['head.h1']);
		$info['attr-description']=str2Attr($params['project.description']?$params['project.description']:$params['head.h1']);
		$info['attr-keywords']='';
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
		if (count($lst)==0) return '';
		$lst[count($lst)-1]['current']=1;
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
			'name'=>'home'
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
/// начитанный список строк
	protected $items=null;
/// условия для начитки списка строк (без параметров пагинации)
	public $filter=Array();
/// уникальный идентификатор экземпляра виджета
	public $guid='';

	function __construct() {
		$this->guid=getGUID();
	}

/** Содержимое виджета
 */
	public function get() {
		return '';
	}
}

/** Класс контроллера адресов страниц PageUrlController
 *
 * если используется контроллер адресов страниц, то необходимо
 * в файле php/pages/.controller.php разместить потомка PageUrlController, 
 * в котором переопределить метод разбора входных параметров getParams().
 * Простейший пример можно посмотреть в php/g740server/pages/.controller.php
 */
class PageUrlController {
/** Разбор входных параметров страницы
 *
 * @return	Array	параметры PageViewer
 */
	public function getParams() {
		throw new Exception('Обращение к абстрактному методу PageUrlController.getParams()');
	}
/** Перевести адрес страницы в массив url по уровням вложенности
 *
 * @return	Array	массив url по уровням вложенности
 */
	public function getLstUrl() {
		//trace($_SERVER["REQUEST_URI"]);
		$scriptUrl=$_SERVER["SCRIPT_NAME"];
		$pos=mb_strripos($scriptUrl, '/index.php');
		if ($pos===false) throw new Exception('Не удалось обработать адрес входного запроса, _SERVER[SCRIPT_NAME] не содержит /index.php');
		$url=trim(mb_strtolower(mb_substr($_SERVER["REQUEST_URI"],$pos+1,999)));
		$pos=mb_strpos($url,'#');
		if ($pos!==false) $url=mb_substr($url, 0, $pos);
		$pos=mb_strpos($url,'?');
		if ($pos!==false) $url=mb_substr($url, 0, $pos);
		$result=explode('/',$url);
		while(count($result)>0 && trim($result[count($result)-1])=='') unset($result[count($result)-1]);
		return $result;
	}
}

/** Получить адрес страницы по параметрам
 *
 * @param	Array	$params
 * @return	string	адрес страницы
 */
function getPageHref($params) {
	$objPageViewer=getPageViewer($params['page']);
	return $objPageViewer->getHref($params);
}

/** Получить PageViewer по имени
 *
 * @param	string	$page
 * @return	PageViewer
 */
function getPageViewer($page) {
	global $_registerPageViewer;
	if ($page!=str2FileName($page)) throw new Exception("Недопустимое имя PageViewer: {$page}");
	if ($_registerPageViewer[$page]) return $_registerPageViewer[$page];

	$fileName=pathConcat(
		getCfg('path.root'),
		getCfg('path.root.pages', pathConcat(getCfg('path.root.php'),'pages')),
		"{$page}.php"
	);
	if (!file_exists($fileName)) {
		$fileName=pathConcat(
			getCfg('path.root'),
			getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
			'pages',
			"{$page}.php"
		);
	}
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof PageViewer) $_registerPageViewer[$page]=$obj;
	}
	if (!$_registerPageViewer[$page]) throw new Exception("Не найден PageViewer: {$page}");
	return $_registerPageViewer[$page];
}
/** Получить Widget по имени
 *
 * @param	string	$name
 * @return	Widget
 */
function getWidget($name) {
	global $_registerWidget;
	if ($name!=str2FileName($name)) throw new Exception("Недопустимое имя виджета: {$name}");
	if (!$_registerWidget[$name]) {
		$fileName=pathConcat(
			getCfg('path.root'),
			getCfg('path.root.widgets',pathConcat(getCfg('path.root.php'),'widgets')),
			"{$name}.php"
		);
		if (!file_exists($fileName)) {
			$fileName=pathConcat(
				getCfg('path.root'),
				getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
				'widgets',
				"{$name}.php"
			);
		}
		if (file_exists($fileName)) {
			$obj=include_once($fileName);
			if ($obj instanceof Widget) $_registerWidget[$name]=$obj;
		}
	}
	if (!$_registerWidget[$name]) throw new Exception("Не найден виджет: {$name}");
	
	$widgetClassName=get_class($_registerWidget[$name]);
	$objWidget=new $widgetClassName();
	$objWidget->name=$name;
	return $objWidget;
}
/** Получить актуальный для проекта контроллер адресов страниц PageUrlController
 *
 * @return	PageUrlController
 */
function getPageUrlController() {
	global $_objPageUrlController;
	if ($_objPageUrlController) return $_objPageUrlController;
	$fileName=pathConcat(
		getCfg('path.root'),
		getCfg('path.root.pages', pathConcat(getCfg('path.root.php'),'pages')),
		'.controller.php'
	);
	if (!file_exists($fileName)) {
		$fileName=pathConcat(
			getCfg('path.root'),
			getCfg('path.root.g740server', pathConcat(getCfg('path.root.php'),'g740server')),
			'pages',
			'.controller.php'
		);
	}
	if (file_exists($fileName)) {
		$obj=include_once($fileName);
		if ($obj instanceof PageUrlController) $_objPageUrlController=$obj;
	}
	if (!$_objPageUrlController) throw new Exception('Не найден PageUrlController');
	return $_objPageUrlController;
}
/// Список зарегистрированных виджетов
$_registerWidget=Array();
/// Список зарегистрированных страниц PageViewer
$_registerPageViewer=Array();
/// Объект PageUrlController
$_objPageUrlController=null;