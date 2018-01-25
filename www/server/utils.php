<?php
// Утилиты
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED);
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
require_once('config/.config.php');
require_once('lib/datasource-controller.php');
require_once('lib/dsautogenerator.php');
require_once('lib/utility-controller.php');

$config['path.root']=pathConcat('..',getCfg('path.root'));
$hrefRoot=getCfg('href.root');

echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Утилиты</title>
	<link rel="stylesheet" type="text/css" href="{$hrefRoot}/resource/utils/reset.css">
	<link rel="stylesheet" type="text/css" href="{$hrefRoot}/resource/utils/utils.css">
</head>
<body>
<h1>Утилиты</h1>
HTML;
flush();
try {
	if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение системных утилит, увы и ах...');
	echo '<div class="message">Устанавливаем соединение с базой данных ... '; flush();
	$pdoDB=new PDODataConnectorMySql(
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost')
	); // Устанавливаем соединение с базой данных
	regPDO($pdoDB,'default');
	echo 'Ok!</div>';
	try {
		echo '<div class="message">Начинаем транзакцию ... '; flush();
		$pdoDB->beginTransaction();
		echo 'Ok!</div>';
		echo '<div class="message">Устанавливаем максимальное время выполнения скрипта 99999 секунд ... '; flush();
		if (!ini_set('max_execution_time','99999')) throw new Exception('Не удалось задать увеличенное время для выполнения скрипта');
		echo 'Ok!</div>';
		
		$mode=$_REQUEST['mode'];
		if (!$mode) throw new Exception('Не задан обязательный параметр mode');
		execUtilController($mode, true);

		echo '<br><br><div class="message">Подтверждаем транзакцию ... '; flush();
		if ($pdoDB->inTransaction()) $pdoDB->commit();
		echo 'Ok!</div>'; flush();
		
		echo '<div class="ok">Операция завершена успешно!!!</div>'; flush();
	}
	catch (Exception $e) {
		echo '<div class="error">Откатываем транзакцию ...'; flush();
		if ($pdoDB->inTransaction()) $pdoDB->rollBack();
		echo '</div>'; flush();
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
?>