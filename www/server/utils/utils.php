<?php
// Утилиты
session_start();
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED);
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
require_once('../config/.config.php');
require_once('../module/module-lib-base.php');
require_once('../module/module-lib-g740server.php');
require_once('../module/module-perm.php');
require_once('../module/module-backup.php');
require_once('../module/module-autogen.php');
require_once('../module/module-utility.php');

/**
BackUp
*/
function goBackup($params) {
	global $pdoDB;
	echo '<h2>Выполнение резервного копирования</h2>'; flush();
	if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение этой операции...');
	
	$path='../../import/backup';
	if (!is_dir($path)) mkdir($path);
	$fullpath=str_replace("\\","/",realpath($path));
	$fileName=$fullpath.'/backup.xml';
	
	$xmlWriter = new XMLWriter();
	$xmlWriter->openURI($fileName);
	$xmlWriter->startDocument('1.0','utf-8');
	$xmlWriter->startElement('tables');
	
	if ($params['isdatastru']) {
		echo <<<HTML
<div class="section">
<h3>Резервное копирование описателей структуры базы</h3>
HTML;
		flush();
		$lst=Array();
		$lst[]='systablecategory';
		$lst[]='systable';
		$lst[]='sysfieldtype';
		$lst[]='sysfield';
		$lst[]='sysfieldparams';
		foreach($lst as $index=>$tableName) {
			$p=Array();
			$p['xmlWriter']=$xmlWriter;
			$p['tableName']=$tableName;
			$p['isEcho']=true;
			$p['isOptimize']=true;
			echo '<div class="message">'.$tableName.': '; flush();
			saveTableToXmlWriter($p);
			echo '</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
		echo '</div>'; flush();
	}
	if ($params['issystem']) {
		echo <<<HTML
<div class="section">
<h3>Резервное копирование системных справочников</h3>
HTML;
		flush();
		$sql=<<<SQL
select systable.*
from 
	systable
where 
	systable.issystem=1
order by systable.tablename
SQL;
		$q=$pdoDB->pdo($sql);
		while ($rec=$pdoDB->pdoFetch($q)) {
			$p=Array();
			$p['xmlWriter']=$xmlWriter;
			$p['tableName']=$rec['tablename'];
			$p['isEcho']=true;
			$p['isOptimize']=true;
			echo '<div class="message">'.$rec['tablename'].': '; flush();
			saveTableToXmlWriter($p);
			echo '</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
		echo '</div>'; flush();
	}
	if ($params['isstatic']) {
		echo <<<HTML
<div class="section">
<h3>Резервное копирование статических таблиц</h3>
HTML;
		flush();
		$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isstatic=1 and
	systable.issystem=0
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
		$q=$pdoDB->pdo($sql);
		while ($rec=$pdoDB->pdoFetch($q)) {
			$p=Array();
			$p['xmlWriter']=$xmlWriter;
			$p['tableName']=$rec['tablename'];
			$p['isEcho']=true;
			$p['isOptimize']=true;
			echo '<div class="message">'.$rec['tablename'].': '; flush();
			saveTableToXmlWriter($p);
			echo '</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
		echo '</div>'; flush();
	}
	if ($params['isdynamic']) {
		echo <<<HTML
<div class="section">
<h3>Резервное копирование динамических таблиц</h3>
HTML;
		flush();
		$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isdynamic=1 and
	systable.issystem=0
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
		$q=$pdoDB->pdo($sql);
		while ($rec=$pdoDB->pdoFetch($q)) {
			$p=Array();
			$p['xmlWriter']=$xmlWriter;
			$p['tableName']=$rec['tablename'];
			$p['isEcho']=true;
			$p['isOptimize']=true;
			echo '<div class="message">'.$rec['tablename'].': '; flush();
			saveTableToXmlWriter($p);
			echo '</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
		echo '</div>'; flush();
	}
	$xmlWriter->endElement();
	$xmlWriter->endDocument();
	$xmlWriter->flush();
	unset($xmlWriter);
	echo 'Ok!</div>'; flush();
	echo '<script>document.body.scrollIntoView(false)</script>'; flush();
}

/**
Restore
*/
function goRestore($params) {
	global $pdoDB;

	$path='../../import/backup/backup.xml';
	if (!is_file($path)) throw new Exception("Файл '{$path}' не найден");
	$fileName=str_replace("\\","/",realpath($path));

	echo '<h2>Выполнение восстановления из резервной копии</h2>'; flush();
	if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение этой операции...');
	if (!file_exists($fileName)) throw new Exception('Отсутствует файл '.$fileName);
	$pdoDB->commit();

	if ($params['isdatastru']) {
		echo <<<HTML
<div class="section">
<h3>Восстановление описателей структуры базы</h3>
HTML;
		flush();
		$lst=Array();
		$lst[]='systablecategory';
		$lst[]='systable';
		$lst[]='sysfieldtype';
		$lst[]='sysfield';
		$lst[]='sysfieldparams';

		$p=Array();
		$p['fileName']=$fileName;
		$p['tables']=$lst;
		$p['isEcho']=true;
		$p['isOptimize']=true;
		$p['isDisableKeys']=true;
		loadTablesFromXmlReader($p);
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		echo '</div>'; flush();
	}


	if ($params['issystem']) {
		echo <<<HTML
<div class="section">
<h3>Восстановление системных справочников</h3>
HTML;
		$p=Array();
		$p['fileName']=$fileName;
		$lst=Array();
		$lst[]='systable';
		$p['tables']=$lst;
		$p['isEcho']=true;
		$p['isOptimize']=true;
		$p['isDisableKeys']=true;
		loadTablesFromXmlReader($p);
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();

		$lst=Array();
		$sql=<<<SQL
select systable.*
from 
	systable
where 
	systable.issystem=1
order by systable.tablename
SQL;
		$q=$pdoDB->pdo($sql);
		while ($rec=$pdoDB->pdoFetch($q)) {
			$lst[]=$rec['tablename'];
		}

		$p=Array();
		$p['fileName']=$fileName;
		$p['tables']=$lst;
		$p['isEcho']=true;
		$p['isOptimize']=true;
		$p['isDisableKeys']=true;
		loadTablesFromXmlReader($p);
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		
		echo '</div>'; flush();
	}

	if ($params['isstatic']) {
		echo <<<HTML
<div class="section">
<h3>Восстановление статических таблиц</h3>
HTML;

		$lst=Array();
		$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isstatic=1 and
	systable.issystem=0
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
		$q=$pdoDB->pdo($sql);
		while ($rec=$pdoDB->pdoFetch($q)) {
			$lst[]=$rec['tablename'];
		}

		$p=Array();
		$p['fileName']=$fileName;
		$p['tables']=$lst;
		$p['isEcho']=true;
		$p['isOptimize']=true;
		$p['isDisableKeys']=true;
		loadTablesFromXmlReader($p);
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		
		echo '</div>'; flush();
	}
	if ($params['isdynamic']) {
		echo <<<HTML
<div class="section">
<h3>Восстановление динамических таблиц</h3>
HTML;

		$lst=Array();
		$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isdynamic=1 and
	systable.issystem=0
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
		$q=$pdoDB->pdo($sql);
		while ($rec=$pdoDB->pdoFetch($q)) {
			$lst[]=$rec['tablename'];
		}

		$p=Array();
		$p['fileName']=$fileName;
		$p['tables']=$lst;
		$p['isEcho']=true;
		$p['isOptimize']=true;
		$p['isDisableKeys']=true;
		loadTablesFromXmlReader($p);
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		
		echo '</div>'; flush();
	}
	$pdoDB->beginTransaction();
}

/**
Автогенерация источников данных
*/
function goAutogenDataSources($params) {
	echo '<h2>Генерация классов DataSource</h2>'; flush();
	echo '<div class="section">'; flush();
	$params['isEcho']=true;
	$objAutoGenerator=new AutoGenerator($params);
	$params['path']=getCfg('pathDataSources').'/autogen/';
	$objAutoGenerator->goDataSources($params);
	unset($objAutoGenerator);
	echo 'Ok!</div>'; flush();
	echo '<script>document.body.scrollIntoView(false)</script>'; flush();
}

echo <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Утилиты</title>
	<link rel="stylesheet" type="text/css" href="reset.css?version=20160724">
	<link rel="stylesheet" type="text/css" href="utils.css?version=20160724">
</head>
<body>
<h1>Утилиты</h1>
HTML;
flush();
try {
	if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение системных утилит, увы и ах...');
	$config['pathDataSources']='../'.$config['pathDataSources'];
	echo '<div class="message">Устанавливаем соединение с базой данных ... '; flush();
	$pdoDB=new PDODataConnectorMySql(
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost')
	); // Устанавливаем соединение с базой данных
	echo 'Ok!</div>';
	try {
		echo '<div class="message">Начинаем транзакцию ... '; flush();
		$pdoDB->beginTransaction();
		echo 'Ok!</div>';
		echo '<div class="message">Устанавливаем максимальное время выполнения скрипта 99999 секунд ... '; flush();
		if (!ini_set('max_execution_time','99999')) throw new Exception('Не удалось задать увеличенное время для выполнения скрипта');
		echo 'Ok!</div>';
		
		$params=Array();
		$mode=$_REQUEST['mode'];
		if ($mode=='backup') {
			$params['isdatastru']=$pdoDB->str2Sql($_REQUEST['isdatastru']);
			$params['issystem']=$pdoDB->str2Sql($_REQUEST['issystem']);
			$params['isstatic']=$pdoDB->str2Sql($_REQUEST['isstatic']);
			$params['isdynamic']=$pdoDB->str2Sql($_REQUEST['isdynamic']);
			if ($_REQUEST['isall']) {
				$params['isdatastru']=1;
				$params['issystem']=1;
				$params['isstatic']=1;
				$params['isdynamic']=1;
			}
			goBackup($params);
		}
		if ($mode=='restore') {
			$params['isdatastru']=$pdoDB->str2Sql($_REQUEST['isdatastru']);
			$params['issystem']=$pdoDB->str2Sql($_REQUEST['issystem']);
			$params['isstatic']=$pdoDB->str2Sql($_REQUEST['isstatic']);
			$params['isdynamic']=$pdoDB->str2Sql($_REQUEST['isdynamic']);
			if ($_REQUEST['isall']) {
				$params['isdatastru']=1;
				$params['issystem']=1;
				$params['isstatic']=1;
				$params['isdynamic']=1;
			}
			goRestore($params);
		}
		if ($mode=='autogendatasources') {
			goAutogenDataSources($params);
		}

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