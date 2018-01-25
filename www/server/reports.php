<?php
// Отчеты
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
require_once('config/.config.php');
require_once('lib/datasource-controller.php');
require_once('lib/report-controller.php');

$config['path.root']=pathConcat('..',getCfg('path.root'));
$hrefRoot=getCfg('href.root');

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
	<link href="{$hrefRoot}/resource/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="wrap">
	<div class="container">
HTML;
flush();
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
		if (!ini_set('max_execution_time','99999')) throw new Exception('Не удалось задать увеличенное время для выполнения скрипта');
		
		$objReport->testReport();

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
<script src="{$hrefRoot}/resource/jquery-1.12.4.min.js"></script>
<script src="{$hrefRoot}/resource/bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
HTML;
flush();
?>