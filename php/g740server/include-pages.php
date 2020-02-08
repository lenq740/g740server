<?php
/**
 * @file
 * G740Server, include точки входа для обработки страниц
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

///@cond
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors','On');

include_once("{$config['path.root.g740server']}/lib/lib-base.php");

// задаем корень проекта относительно точки входа
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
if (isset($_REQUEST['sessionid'])) session_id($_REQUEST['sessionid']);
session_start();
session_write_close();

includeLib('lib-html.php');
includeLib('perm-controller.php');
includeLib('datasource-controller.php');
includeLib('page-controller.php');

try {
	$pdoDB=newPDODataConnector(
		getCfg('sqlDriverName'),
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost'),
		getCfg('sqlPort')
	); // Устанавливаем соединение с базой данных
	regPDO($pdoDB,'default');
	try {
		$pdoDB->beginTransaction();
		$objPageUrlController=getPageUrlController();
		$params=$objPageUrlController->getParams();
		if (!$params['page']) $params['page']='404';
		
		if (getCfg('trace.pages',false)) trace($params);
		
		$objPage=getPageViewer($params['page']);
		$objPage->go($params);
		if ($pdoDB->inTransaction()) $pdoDB->commit();
	}
	catch (Exception $e) {
		if ($pdoDB->inTransaction()) $pdoDB->rollBack();
		throw new Exception($e->getMessage());
	}
}
catch (Exception $e) {
	errorLog($e);
	try {
		$objPage=getPageViewer('404');
		$objPage->go(Array(
			'message'=>$e->getMessage()
		));
	}
	catch(Exception $eee) {
	}
}
///@endcond