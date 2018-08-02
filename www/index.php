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
	$isProcessEnded=true;
}
catch (Exception $e) {
}