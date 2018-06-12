<?php
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors','Off');
ini_set('log_errors','On');
ini_set('error_log','log/logerr.txt');
require_once('config/.config.php');
require_once('lib/report-controller.php');

$config['path.root']=pathConcat('..',getCfg('path.root'));
$hrefRoot=getCfg('href.root');

try {
	$pdoDB=new PDODataConnectorMySql(
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost')
	); // Устанавливаем соединение с базой данных
	regPDO($pdoDB,'default');
	try {
		$pdoDB->beginTransaction();
		$mode=$_REQUEST['mode'];
		if (!$mode) throw new Exception('Не задан обязательный параметр mode');
		$obj=getReportController($mode);
		if (!obj) throw new Exception("Задан недопустимый параметр mode='{$mode}'");
		$params=$obj->getParams();
		$text=$obj->getBody($params);
		echo $obj->getHead($params);
		echo $text;
		echo $obj->getFooter($params);
		if ($pdoDB->inTransaction()) $pdoDB->commit();
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