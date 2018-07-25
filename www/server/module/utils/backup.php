<?php
// Утилита Backup
class UtilityBackup extends UtilController {
	public function getParams() {
		$pdoDB=getPDO();
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
		}
		return $params;
	}
	public function go($params=Array(), $isEcho=false) {
		if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение системных утилит, увы и ах...');
		$pdoDB=getPDO();
		if ($isEcho) {
			echo '<h2>Выполнение резервного копирования</h2>'; flush();
		}
		if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение этой операции...');
		
		$dbUtility = new DBUtility();
		$path=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup');
		if (!is_dir($path)) mkdir($path);
		$dbUtility->xmlFileName=str_replace("\\","/",realpath($path)).'/backup.xml';
		
		if ($params['isdatastru']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Резервное копирование описателей структуры базы</h3>
HTML;
				flush();
			}
			$lst=Array();
			$lst[]='systablecategory';
			$lst[]='systable';
			$lst[]='sysfieldtype';
			$lst[]='sysfield';
			$lst[]='sysfieldparams';
			foreach($lst as $index=>$tableName) {
				$p=Array();
				$p['tableName']=$tableName;
				$p['isEcho']=true;
				if ($isEcho) {
					echo '<div class="message">'.$tableName.': '; flush();
				}
				$dbUtility->saveTable($p);
				if ($isEcho) {
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
			}
			if ($isEcho) {
				echo '</div>'; flush();
			}
		}
		if ($params['issystem']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Резервное копирование системных справочников</h3>
HTML;
				flush();
			}
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
				$p['tableName']=$rec['tablename'];
				$p['isEcho']=true;
				if ($isEcho) {
					echo '<div class="message">'.$rec['tablename'].': '; flush();
				}
				$dbUtility->saveTable($p);
				if ($isEcho) {
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
			}
			if ($isEcho) {
				echo '</div>'; flush();
			}
		}
		if ($params['isstatic']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Резервное копирование статических таблиц</h3>
HTML;
				flush();
			}
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
				$p['tableName']=$rec['tablename'];
				$p['isEcho']=true;
				if ($isEcho) {
					echo '<div class="message">'.$rec['tablename'].': '; flush();
				}
				$dbUtility->saveTable($p);
				if ($isEcho) {
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
			}
			if ($isEcho) {
				echo '</div>'; flush();
			}
		}
		if ($params['isdynamic']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Резервное копирование динамических таблиц</h3>
HTML;
				flush();
			}
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
				$p['tableName']=$rec['tablename'];
				$p['isEcho']=true;
				if ($isEcho) {
					echo '<div class="message">'.$rec['tablename'].': '; flush();
				}
				$dbUtility->saveTable($p);
				if ($isEcho) {
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
			}
			if ($isEcho) {
				echo '</div>'; flush();
			}
		}
		$dbUtility->xmlSave();
		unset($dbUtility);
		if ($isEcho) {
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
	}
}
return new UtilityBackup();
