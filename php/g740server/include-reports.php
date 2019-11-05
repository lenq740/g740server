<?php
/**
 * @file
 * G740Server, include точки входа контроллера отчетов
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

///@cond
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors','On');

include_once("{$config['path.root.g740server']}/lib/lib-base.php");

// задаем корень проекта относительно точки входа
$config['path.root']=realpath(getCfg('path.root'));
$pathResource=pathConcat(
	getCfg('href.root','/'),
	getCfg('path.root.resource')
);

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
session_write_close();

includeLib('lib-html.php');
includeLib('perm-controller.php');
includeLib('datasource-controller.php');
includeLib('ext-controller.php');

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
		$name=$_REQUEST['name'];
		if (!$name) throw new Exception('Не задан обязательный параметр name');
		$obj=getReportController($name);
		$params=$obj->getParams();
		$result=$obj->go($params);
		echo $result;
		if ($pdoDB->inTransaction()) {
			if (!$pdoDB->commit()) throw new Exception('Не удалось подтвердить транзакцию...');
		}
	}
	catch (Exception $e) {
		if ($pdoDB->inTransaction()) $pdoDB->rollBack();
		throw new Exception($e->getMessage());
	}
}
catch (Exception $e) {
	header("Content-type: text/plain; charset=utf-8");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	echo $e->getMessage();
}
///@endcond