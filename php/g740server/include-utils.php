<?php
/**
 * @file
 * G740Server, include точки входа для обработки запросов к утилитам
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

///@cond
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors','On');
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

include_once("{$config['path.root.g740server']}/lib/lib-base.php");

// отключаем буфферизацию вывода
ob_end_flush();

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
if (isset($_REQUEST['sessionid'])) session_id($_REQUEST['sessionid']);
session_start();
session_write_close();

includeLib('perm-controller.php');
includeLib('datasource-controller.php');
includeLib('ext-controller.php');

echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Утилиты</title>
	<link rel="stylesheet" type="text/css" href="{$pathResource}/utils/reset.css">
	<link rel="stylesheet" type="text/css" href="{$pathResource}/utils/utils.css">
</head>
<body>
HTML;
flush();
try {
	$dStart=new DateTime();
	
	echo '<div class="message">Устанавливаем соединение с базой данных ... '; flush();
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
	echo 'Ok!</div>';
	try {
		echo '<div class="message">Начинаем транзакцию ... '; flush();
		$pdoDB->beginTransaction();
		echo 'Ok!</div>';
		
		echo '<div class="message">Устанавливаем лимиты среды выполнения ... '; flush();
		if (!ini_set('max_execution_time', getCfg('util.max_execution_time','99999'))) throw new Exception('Не удалось задать увеличенное время для выполнения скрипта');
		if (!ini_set('memory_limit', getCfg('util.memory_limit','256M'))) throw new Exception('Не удалось задать увеличенный объем памяти для выполнения сервиса');
		echo 'Ok!</div>';
		
		$name=$_REQUEST['name'];
		if (!$name) throw new Exception('Не задан обязательный параметр name');
		$obj=getUtilController($name);
		$isSysExtLog=$obj->isSysExtLog;
		if ($isSysExtLog) {
			$sqlD=$dStart->format('Y-m-d');
			$sqlT=$dStart->format('H:i');
			$sqlName=$pdoDB->str2Sql($name);
			$sql=<<<SQL
insert into sysextlog (d, tstart, name, message) values ('{$sqlD}', '{$sqlT}', '{$sqlName}', 'Выполняется ...');
SQL;
			$pdoDB->pdo($sql);
			$sysextlogid=$pdoDB->lastInsertId();
		}

		$params=$obj->getParams();
		$params['echo']=true;
		$obj->go($params);

		if ($isSysExtLog) {
			$sqlTEnd=(new DateTime())->format('H:i');
			$sql=<<<SQL
update sysextlog set 
	tend='{$sqlTEnd}',
	message='Завершено успешно'
where
	sysextlog.id='{$sysextlogid}'
SQL;
			$pdoDB->pdo($sql);
		}

		echo '<br><br><div class="message">Подтверждаем транзакцию ... '; flush();
		if ($pdoDB->inTransaction()) $pdoDB->commit();
		echo 'Ok!</div>'; flush();
		echo '<div class="ok">Операция завершена успешно!!!</div>'; flush();
	}
	catch (Exception $e) {
		if ($pdoDB->inTransaction()) {
			echo '<div class="error">Откатываем транзакцию ...'; flush();
			$pdoDB->rollBack();
			echo '</div>'; flush();
		}
		
		if ($isSysExtLog) {
			$pdoDB->beginTransaction();
			$sql=<<<SQL
delete from sysextlog where id='{$sysextlogid}'
SQL;
			$pdoDB->pdo($sql);
			$sqlTEnd=(new DateTime())->format('H:i');
			$sqlMessage=$pdoDB->str2Sql($e->getMessage());
			$sql=<<<SQL
insert into sysextlog (
	d,
	tstart,
	tend,
	name,
	message,
	iserror
) values (
	'{$sqlD}',
	'{$sqlT}',
	'{$sqlTEnd}',
	'{$sqlName}',
	'{$sqlMessage}',
	1
)
SQL;
			$pdoDB->pdo($sql);
			if ($pdoDB->inTransaction()) $pdoDB->commit();
		}
		throw new Exception($e->getMessage());
	}
}
catch (Exception $e) {
	echo "\n".<<<HTML
	<div class="error">
		<h2>Произошла ошибка!!!</h2>
		<div class="message">{$e->getMessage()}</div>
	</div>
HTML;
	flush();
}
echo '<script>document.body.scrollIntoView(false)</script>'; flush();
echo "\n".<<<HTML
</body>
</html>
HTML;
flush();
///@endcond