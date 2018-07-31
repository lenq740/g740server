<?php
/**
 * @file
 * контроллер визуализации страниц сайта
 */
require_once('lib-base.php');
require_once('lib-html.php');
require_once('perm-controller.php');
require_once('datasource-controller.php');
 
/** Класс PageViewer - предок визуализаторов страниц сайта
 */
class PageViewer {
/// имя визуализатора страницы
	public $mode='abstract';
	
/// Конструктор, регистрация экземпляра класса
	function __construct() {
		global $_registerObjPage;
		if (!$_registerObjPage[$this->mode]) $_registerObjPage[$this->mode]=$this;
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
/// имя визуализатора страницы
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

/** Получить PageViewer по имени
 *
 * @param	string	$mode
 * @return	PageViewer
 */
function getObjPage($mode) {
	global $_registerObjPage;
	$result=$_registerObjPage[$mode];
	if (!$result) $result=$_registerObjPage['default'];
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
	global $_registerObjPage;
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

	foreach($_registerObjPage as $objPage) {
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

/** Точка входа контроллера страниц
 */
function goPageController($isTrace=false) {
	try {
		$pdoDB=new PDODataConnectorMySql(
			getCfg('sqlDbName'),
			getCfg('sqlLogin'),
			getCfg('sqlPassword'),
			getCfg('sqlCharSet'),
			getCfg('sqlHost')
		);
		regPDO($pdoDB,'default');
		$pdoDB->beginTransaction();
		try {
			$filePages=pathConcat(getCfg('path.root'),getCfg('path.root.module'),'pages','pages.php');
			if (file_exists($filePages)) include_once($filePages);
			
			$params=getInputParams();	// Ищем страницу и начитываем для нее параметры
			
			if ($isTrace) trace($params);
			
			$objPage=getObjPage($params['mode']);
			$html=$objPage->getPage($params);
			$objPage->sendHttpHeaders($params);
			if ($pdoDB->inTransaction()) $pdoDB->commit();
		}
		catch (Exception $e) {
			if ($pdoDB->inTransaction()) $pdoDB->rollBack();
			throw new Exception($e->getMessage());
		}
	}
	catch (Exception $e) {
		errorLog($e);
		$objPage=getObjPage('error');
		if ($objPage) {
			$p=$objPage->getPageParams($params);
			foreach($p as $name=>$value) $params[$name]=$value;
			$html=$objPage->getPage($params);
			$objPage->sendHttpHeaders($params);
		}
	}
	if ($html) echo $html;
}

/** Шаблон подключения визуализатора g740
 *
 * @param	Array	$info
 * @param	Array	$icons
 * @return	string шаблон подключения визуализатора g740
 *
 * - $info['title'] - заголовок проекта
 * - $info['favicon'] - favicon
 * - $info['path-g740client'] - путь до g740client
 * - $info['path-g740icons-css'] - путь до css файла с иконками
 * - $info['iconset'] - набор иконок - 'default', 'cti'
 *
 * - $info['config-urlServer'] - путь до точки входа серверных скриптов
 * - $info['config-mainFormName'] - 'formMain', 'formMainWithMenuBar'
 * - $info['config-appColorScheme'] - 'black', 'red', 'cti'
 * - $info['config-iconSizeDefault'] - 'small', 'medium', 'large'
 * - $info['config-login-mainFormLoginUrl'] - путь до страницы аутентификации, если она сделана отдельно
 * - $info['config-login-loginUrl']
 * - $info['config-login-iconUrl']
 * - $info['config-login-isReloadBeforeLogin']
 * - $info['config-login-width']
 * - $info['config-login-height']
 * - $info['config-login-iconWidth']
 * - $info['config-login-title']
 */
function getPageControllerG740Client($info=Array(), $icons=Array()) {
	$htmlTitle=str2Html($info['title']);
	$attrPathG740Client=str2Attr($info['path-g740client']);
	$jsPathG740Client=str2JavaScript($info['path-g740client']);
	$attrPathG740IconsCSS=str2Attr($info['path-g740icons-css']);
	
	$htmlConfig='';
	if ($info['config-urlServer']) {
		$jsUrlServer=str2JavaScript($info['config-urlServer']);
		$htmlConfig.="\n".<<<HTML
	conf['urlServer']='{$jsUrlServer}';
HTML;
	}
	if ($info['config-mainFormName']) {
		$jsMainFormName=str2JavaScript($info['config-mainFormName']);
		$htmlConfig.="\n".<<<HTML
	conf['mainFormName']='{$jsMainFormName}';
HTML;
	}
	if ($info['config-login-mainFormLoginUrl']) {
		$jsMainFormLoginUrl=str2JavaScript($info['config-login-mainFormLoginUrl']);
		$htmlConfig.="\n".<<<HTML
	conf['mainFormLoginUrl']='{$jsMainFormLoginUrl}';
HTML;
	}
	if ($info['config-appColorScheme']) {
		$jsAppColorScheme=str2JavaScript($info['config-appColorScheme']);
		$htmlConfig.="\n".<<<HTML
	conf['appColorScheme']='{$jsAppColorScheme}';
HTML;
	}
	if ($info['config-iconSizeDefault']) {
		$jsIconSizeDefault=str2JavaScript($info['config-iconSizeDefault']);
		$htmlConfig.="\n".<<<HTML
	conf['iconSizeDefault']='{$jsIconSizeDefault}';
HTML;
	}
	if (isset($info['config-login-loginUrl'])) {
		$jsValue=str2JavaScript($info['config-login-loginUrl']);
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['loginUrl']='{$jsValue}';
HTML;
	}
	if (isset($info['config-login-iconUrl'])) {
		$jsValue=str2JavaScript($info['config-login-iconUrl']);
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['iconUrl']='{$jsValue}';
HTML;
	}
	if (isset($info['config-login-isReloadBeforeLogin'])) {
		$jsValue=$info['config-login-isReloadBeforeLogin']?'true':'false';
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['isReloadBeforeLogin']='{$jsValue}';
HTML;
	}
	if (isset($info['config-login-width'])) {
		$jsValue=str2JavaScript($info['config-login-width']);
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['width']='{$jsValue}';
HTML;
	}
	if (isset($info['config-login-height'])) {
		$jsValue=str2JavaScript($info['config-login-height']);
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['height']='{$jsValue}';
HTML;
	}
	if (isset($info['config-login-iconWidth'])) {
		$jsValue=str2JavaScript($info['config-login-iconWidth']);
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['iconWidth']='{$jsValue}';
HTML;
	}
	if (isset($info['config-login-title'])) {
		$jsValue=str2JavaScript($info['config-login-title']);
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['title']='{$jsValue}';
HTML;
	}
	
	$htmlFavIcon='';
	if ($info['favicon']) {
		$path_info = pathinfo($info['favicon']);
		$ext=strtolower($path_info['extension']);
		$attrFaveIcon=str2Attr($info['favicon']);
		if ($ext=='ico') {
			$htmlFavIcon=<<<HTML
<link rel="shortcut icon" href="{$attrFaveIcon}" type="image/x-icon"/>
HTML;
		}
		else if ($ext=='png') {
			$htmlFavIcon=<<<HTML
<link rel="shortcut icon" href="{$attrFaveIcon}" type="image/png"/>
HTML;
		}
		else {
			$htmlFavIcon=<<<HTML
<link rel="shortcut icon" href="{$attrFaveIcon}" type="image"  {$ext}/>
HTML;
		}
	}
	
	$htmlIcons='';
	foreach($icons as $iconName=>$iconClass) {
		$jsName=str2JavaScript($iconName);
		$jsClass=str2JavaScript($iconClass);
		if ($htmlIcons) $htmlIcons.="\n";
		$htmlIcons.=<<<HTML
	icons['{$jsName}']='{$jsClass}';
HTML;
	}
	
	$iconset='default';
	if (isset($info['iconset'])) $iconset=$info['iconset'];
	
	
	$result=<<<HTML
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9"/>
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Content-Language" content="ru"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>{$htmlTitle}</title>

	{$htmlFavIcon}	
	
	<link rel="stylesheet" type="text/css" href="{$attrPathG740Client}/g740/cssdojo/main.css"/>
	<link rel="stylesheet" type="text/css" href="{$attrPathG740Client}/g740/main.css"/>
	<link rel="stylesheet" type="text/css" href="{$attrPathG740Client}/g740/icons/iconset-{$iconset}/icons.css"/>
	<link rel="stylesheet" type="text/css" href="{$attrPathG740IconsCSS}"/>
	
<!-- подключаем сжатую версию Dojo -->
	<script type="text/javascript">
		dojoConfig = {
			has: {
				"dojo-firebug": true,
				"dojo-debug-messages": true
			},
			cacheBust: true,
			parseOnLoad: false,
			async: true,
			baseUrl: "{$jsPathG740Client}/dojocompressed",
			packages: [
				{
					name: 'g740',
					location: '../g740'
				}
			]
		};
	</script>
	<script type="text/javascript" src="{$attrPathG740Client}/dojocompressed/dojo.js.uncompressed.js"></script>
	<script type="text/javascript" src="{$attrPathG740Client}/dojocompressed/g740-dojo.js"></script>
</head>
<body>
<!-- Выделяем место под размещение главной формы приложения -->
	<div id="FormPanelMain"></div>
<!--[if IE]>
<script>
	document.documentElement.className+=' IE';
</script>
<![endif]-->
	<script>
		require(
			[
				'g740',
				'dojo/domReady!'
			],
			function() {
// Конфигурируем визуализатор
	var conf=g740.config;
	var confDialogLogin=conf['dialogLogin'];
	conf['mainFormDomNode']='FormPanelMain';	// Узел DOM, в ктором размещается главная форма приложения
{$htmlConfig}

// Расширяем стандартный набор иконок, которые можно использовать в кнопочках и узлах дерева
	var icons=g740.icons._items;
{$htmlIcons}

	g740.application.go();
			}
		);
	</script>
</body>
</html>
HTML;
	return $result;
}

/// Список зарегистрированных страниц 
$_registerObjPage=Array();
/// Список зарегистрированных виджетов
$_registerWidget=Array();
