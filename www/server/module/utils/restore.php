<?php
// Утилита Restore
class UtilityRestore extends UtilController {
	public function getParams() {
		$pdoDB=getPDO();
		$params=Array();
		$mode=$_REQUEST['mode'];
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
		}
		return $params;
	}
	public function go($params=Array(), $isEcho=false) {
		if (!getPerm('sys','write')) throw new Exception('У Вас нет прав на выполнение системных утилит, увы и ах...');
		$pdoDB=getPDO();

		$dbUtility = new DBUtility();
		$fileName=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup','backup.xml');
		if (!is_file($fileName)) throw new Exception("Файл '{$fileName}' не найден");
		$dbUtility->xmlFileName=str_replace("\\","/",realpath($fileName));
		
		
		if ($isEcho) {
			echo '<h2>Выполнение восстановления из резервной копии</h2>'; flush();
		}

		if ($params['isdatastru']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Восстановление описателей структуры базы</h3>
HTML;
				flush();
			}
			$lst=Array();
			$lst[]='systablecategory';
			$lst[]='systable';
			$lst[]='sysfieldtype';
			$lst[]='sysfield';
			$lst[]='sysfieldparams';

			$p=Array();
			$p['tables']=$lst;
			$p['isEcho']=true;
			$dbUtility->loadTables($p);
			if ($isEcho) {
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				echo '</div>'; flush();
			}
		}


		if ($params['issystem']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Восстановление системных справочников</h3>
HTML;
				flush();
			}
			$p=Array();
			$lst=Array();
			$lst[]='systable';
			$p['tables']=$lst;
			$p['isEcho']=true;
			$dbUtility->loadTables($p);
			if ($isEcho) {
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
			}

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
				if ($rec['tablename']=='systable') continue;
				$lst[]=$rec['tablename'];
			}
			$p=Array();
			$p['tables']=$lst;
			$p['isEcho']=true;
			$dbUtility->loadTables($p);
			if ($isEcho) {
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				echo '</div>'; flush();
			}
		}

		if ($params['isstatic']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Восстановление статических таблиц</h3>
HTML;
				flush();
			}

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
			$p['tables']=$lst;
			$p['isEcho']=true;
			$dbUtility->loadTables($p);
			if ($isEcho) {
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				echo '</div>'; flush();
			}
		}
		if ($params['isdynamic']) {
			if ($isEcho) {
				echo <<<HTML
<div class="section">
<h3>Восстановление динамических таблиц</h3>
HTML;
				flush();
			}

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
			$p['tables']=$lst;
			$p['isEcho']=true;
			$dbUtility->loadTables($p);
			if ($isEcho) {
				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				echo '</div>'; flush();
			}
		}
	}
}
return new UtilityRestore();
