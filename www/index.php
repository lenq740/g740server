<?php
/**
 * @file
 * Точка входа сайта
 */
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors','Off');
ini_set('log_errors','On');
ini_set('error_log','server/log/logerr.txt');

include_once('server/config/.config.php');
include_once('server/lib/page-controller.php');

$timeZone=getCfg('timezone');
if ($timeZone) ini_set('date.timezone', $timeZone);

function myShutdownHandler() {
	global $isProcessEnded;
	if (!$isProcessEnded) {
		echo 'При формировании страницы произошла ошибка...';
	}
}
register_shutdown_function('myShutdownHandler');

$isProcessEnded=false;
try {
//	вариант подключения генератора страниц
	goPageController();
/*
	{	// вариант подключения клиента g740
		$info=Array();
		$info['title']='Заготовка проекта';
		$info['path-g740client']='/'.pathConcat(getCfg('href.root'),getCfg('path.root.resource'),'g740client');
		$info['path-g740icons-css']='/'.pathConcat($info['path-g740client'],'icons','icons.css');
		$info['class-app-color']='app-color-red';
		$info['config-urlServer']='/'.pathConcat(getCfg('href.root'),getCfg('path.root.server'),'index.php');
		$info['config-mainFormName']='formMainWithMenuBar';
		$info['config-login-isReloadBeforeLogin']=true;
		$info['config-login-loginUrl']='/'.pathConcat(getCfg('href.root'),getCfg('path.root.resource'),'logoscreen','index.php');
		echo getPageControllerG740Client($info, getCfg('g740.icons'));
	}
*/
	$isProcessEnded=true;
}
catch (Exception $e) {
}