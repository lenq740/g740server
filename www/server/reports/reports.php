<?php
// Отчеты
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
require_once('../config/.config.php');
require_once('../module/module-lib.php');
require_once('../module/module-perm.php');
require_once('../module/module-datasource.php');

function testReport() {
	$dataSourceSysTable=getDataSource('systable');
	$dataSourceSysField=getDataSource('sysfield');

	$tmpTableCode=$dataSourceSysTable->saveToTmpTable();
	$lstSysTable=$dataSourceSysTable->execRefresh(Array('filter.id.tmptable'=>$tmpTableCode));
	$lstSysField=$dataSourceSysField->execRefresh(Array('filter.klssystable.tmptable'=>$tmpTableCode));
	$dataSourceSysTable->appendChilds($lstSysTable, $lstSysField, 'sysfield', 'klssystable');
	
	$htmlSysTables='';
	foreach($lstSysTable as $rowSysTable) {
		$htmlSysFields='';
		foreach($rowSysTable['#child.sysfield'] as $rowSysField) {
			$htmlName=str2Html($rowSysField['name']);
			$htmlSysFields.="\n".<<<HTML
<li>{$htmlName}</li>
HTML;
		}
		$htmlSysTableName=str2Html($rowSysTable['name']);
		$htmlSysTables.="\n".<<<HTML
<tr>
	<td>{$htmlSysTableName}</td>
	<td>{$htmlSysFields}</td>
</tr>
HTML;
	}
	$result=<<<HTML
<h1>Пример отчета</h1>
<table class="table table-bordered">
<thead>
	<tr>
		<th>Таблицы</th>
		<th>Поля</th>
	</tr>
</thead>
{$htmlSysTables}
</table>
HTML;
	return $result;
}

echo <<<HTML
<!DOCTYPE html>
<html lang="ru-ru">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta charset="utf-8">
	<title>Отчет</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Bootstrap -->
	<link href="bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="wrap">
	<div class="container">
HTML;
flush();
try {
	$config['pathDataSources']='../'.$config['pathDataSources'];
	$pdoDB=new PDODataConnectorMySql(
		$sqlDbName, 
		$sqlLogin, 
		$sqlPassword, 
		$sqlCharSet, 
		$sqlHost
	); // Устанавливаем соединение с базой данных
	try {
		$pdoDB->beginTransaction();
		$pdoDB->openConnection();
		if (!ini_set('max_execution_time','99999')) throw new Exception('Не удалось задать увеличенное время для выполнения скрипта');
		
		echo testReport();

		$pdoDB->closeConnection();
		if ($pdoDB->inTransaction()) $pdoDB->commit();
	}
	catch (Exception $e) {
		if ($pdoDB->inTransaction()) $pdoDB->rollBack();
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
echo "\n".<<<HTML
	</div>
</div>
<script src="jquery-1.12.4.min.js"></script>
<script src="bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
HTML;
flush();
?>