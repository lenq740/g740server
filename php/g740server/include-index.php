<?php
/**
 * @file
 * G740Server, include точки входа сайта
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

///@cond
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors','On');

include_once("{$config['path.root.g740server']}/lib/lib-base.php");

$config['path.root']=realpath(getCfg('path.root'));

// Запускаем логирование ошибок в файл
$path=pathConcat(getCfg('path.root'), getCfg('path.root.log','log'));
if (!is_dir($path)) mkdir($path, 0777, true);
ini_set('display_errors','Off');
ini_set('log_errors','On');
$logFileName=pathConcat(getCfg('path.root'),getCfg('path.root.log'),date('Y-m-d').'-phperror.log');
ini_set('error_log',$logFileName);
$timeZone=getCfg('timezone');
if ($timeZone) ini_set('date.timezone', $timeZone);

if (getCfg('project.id')) ini_set('session.name',getCfg('project.id'));
session_start();

try {
	$htmlProjectName=str2Html(getCfg('project.name'));
	
	$htmlFavIcon='';
	$projectFavIcon=getCfg('project.favicon', pathConcat(getCfg('href.root'),'favicon.png'));
	if ($projectFavIcon) {
		$path_info = pathinfo($projectFavIcon);
		$ext=strtolower($path_info['extension']);
		$attrFaveIcon=str2Attr($projectFavIcon);
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
	$attrPathG740Viewer=str2Attr(pathConcat(
		getCfg('href.root'),
		getCfg('path.root.resource'),
		getCfg('path.root.resource.g740viewer')
	));
	$jsPathG740Viewer=str2JavaScript(pathConcat(
		getCfg('href.root'),
		getCfg('path.root.resource'),
		getCfg('path.root.resource.g740viewer')
	));
	
	$attrIconSet=str2Attr(getCfg('project.iconset','m'));

	$attrPathIconsCSS=str2Attr(pathConcat(
		getCfg('href.root','/'),
		getCfg('path.root.resource'),
		getCfg('path.root.resource.icons'),
		'icons.css'
	));
	
	$htmlIcons='';
	foreach(getCfg('project.icons',Array()) as $iconName=>$iconClass) {
		$jsName=str2JavaScript($iconName);
		$jsClass=str2JavaScript($iconClass);
		if ($htmlIcons) $htmlIcons.="\n";
		$htmlIcons.=<<<HTML
	icons['{$jsName}']='{$jsClass}';
HTML;
	}
	$htmlScripts='';
	foreach(getCfg('project.scripts',Array()) as $script) {
		if (!$script) continue;
		$attrScript=str2Attr($script);
		if ($htmlScripts) $htmlScripts.="\n";
		$htmlScripts.=<<<HTML
	<script type="text/javascript" src="{$attrScript}"></script>
HTML;
	}
	$htmlCSS='';
	foreach(getCfg('project.css',Array()) as $css) {
		if (!$css) continue;
		$attrCSS=str2Attr($css);
		if ($htmlCSS) $htmlCSS.="\n";
			$htmlCSS.=<<<HTML
	<link rel="stylesheet" type="text/css" href="{$attrCSS}"/>
HTML;
	}

	$htmlConfig='';
	$urlServer=getCfg('project.urlServer', pathConcat(
		getCfg('href.root'),
		'g740.php'
	));
	$jsUrlServer=str2JavaScript($urlServer);
	$htmlConfig.="\n".<<<HTML
	conf['urlServer']='{$jsUrlServer}';
HTML;
	$jsMainFormName=str2JavaScript(getCfg('project.mainFormName', 'formMain'));
	$htmlConfig.="\n".<<<HTML
	conf['mainFormName']='{$jsMainFormName}';
HTML;
	
	if (getCfg('project.mainFormLoginUrl')) {
		$jsMainFormLoginUrl=str2JavaScript(getCfg('project.mainFormLoginUrl'));
		$htmlConfig.="\n".<<<HTML
	conf['mainFormLoginUrl']='{$jsMainFormLoginUrl}';
HTML;
	}
	$jsAppColorScheme=str2JavaScript(getCfg('project.appColorScheme','m'));
	$htmlConfig.="\n".<<<HTML
	conf['appColorScheme']='{$jsAppColorScheme}';
HTML;
	$jsIconSizeDefault=str2JavaScript(getCfg('project.iconSizeDefault','medium'));
	$htmlConfig.="\n".<<<HTML
	conf['iconSizeDefault']='{$jsIconSizeDefault}';
HTML;

	if (getCfg('project.dialogLogin.loginUrl')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.loginUrl'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['loginUrl']='{$jsValue}';
	confDialogLogin['isReloadBeforeLogin']=true;
HTML;
	}
	if (getCfg('project.dialogLogin.loginOpacity')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.loginOpacity'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['loginOpacity']='{$jsValue}';
HTML;
	}
	if (getCfg('project.dialogLogin.iconUrl')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.iconUrl'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['iconUrl']='{$jsValue}';
HTML;
	}
	if (getCfg('project.dialogLogin.width')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.width'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['width']='{$jsValue}';
HTML;
	}
	if (getCfg('project.dialogLogin.height')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.height'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['height']='{$jsValue}';
HTML;
	}
	if (getCfg('project.dialogLogin.iconWidth')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.iconWidth'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['iconWidth']='{$jsValue}';
HTML;
	}
	if (getCfg('project.dialogLogin.title')) {
		$jsValue=str2JavaScript(getCfg('project.dialogLogin.title'));
		$htmlConfig.="\n".<<<HTML
	confDialogLogin['title']='{$jsValue}';
HTML;
	}
	
	$result=<<<HTML
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Content-Language" content="ru"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>{$htmlProjectName}</title>

	{$htmlFavIcon}

	<link rel="stylesheet" type="text/css" href="{$attrPathG740Viewer}/g740/cssdojo/main.css"/>
	<link rel="stylesheet" type="text/css" href="{$attrPathG740Viewer}/g740/main.css"/>
	<link rel="stylesheet" type="text/css" href="{$attrPathG740Viewer}/g740/icons/iconset-{$attrIconSet}/icons.css"/>
	<link rel="stylesheet" type="text/css" href="{$attrPathIconsCSS}"/>
{$htmlCSS}

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
			baseUrl: "{$jsPathG740Viewer}/dojocompressed",
			packages: [
				{
					name: 'g740',
					location: '../g740'
				}
			]
		};
	</script>
	<script type="text/javascript" src="{$attrPathG740Viewer}/dojocompressed/dojo.js.uncompressed.js"></script>
	<script type="text/javascript" src="{$attrPathG740Viewer}/dojocompressed/g740-dojo.js"></script>
{$htmlScripts}
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
	echo $result;
}
catch (Exception $e) {
}
///@endcond