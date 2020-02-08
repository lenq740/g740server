<?php
/**
 * @file
 * G740Server, утилита резервного копирования данных
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/// Класс контроллера утилиты резервного копирования данных
class UtilityBackup extends UtilController {
/// Наименование утилиты
	public $caption='Экспорт таблиц';

/** Разбор входных параметров
 *
 * @return	Array	параметры
 */
	public function getParams() {
		$params=Array();
		$params['isdatastru']=$this->str2Sql($_REQUEST['isdatastru']);
		$params['issystem']=$this->str2Sql($_REQUEST['issystem']);
		$params['isstatic']=$this->str2Sql($_REQUEST['isstatic']);
		$params['isdynamic']=$this->str2Sql($_REQUEST['isdynamic']);
		if ($_REQUEST['isall']) {
			$params['isdatastru']=1;
			$params['issystem']=1;
			$params['isstatic']=1;
			$params['isdynamic']=1;
		}
		$params['issql']=($_REQUEST['issql']==1)?1:0;
		$params['ismxldatastru']=($_REQUEST['ismxldatastru']==1)?1:0;
		$params['ismxlmenu']=($_REQUEST['ismxlmenu']==1)?1:0;
		return $params;
	}
/** Формирование результата запроса
 *
 * @param	Array	$params параметры
 * @return	String	результат
 */
	public function go($params=Array()) {
		$isSQL=$params['issql'];
		if (!getPerm(getCfg('perm.utils.backup','root'),'write')) throw new Exception('У Вас нет прав на выполнение резервного копирования');
		
		$dbUtility = new DBUtility();
		$path=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup');
		if (!is_dir($path)) mkdir($path, 0777, true);
		if (!is_dir($path)) throw new Exception("Не удалось создать папку для выгрузки: '{$path}'");
		
		if ($params['ismxldatastru']) {
			$dbUtility->xmlFileName=str_replace("\\","/",realpath($path)).'/datamodel.xml';
			echo <<<HTML
<div class="section">
<h2>Экспорт структуры базы в виде XML описания</h2>
HTML;
			echo '<div class="message">Экспорт ... ';flush();
			$dbUtility->exportDataStruToXml();
			echo 'Ok!</div>';
			echo '</div>'; flush();
		}
		else if ($params['ismxlmenu']) {
			$dbUtility->xmlFileName=str_replace("\\","/",realpath($path)).'/menu.xml';
			echo <<<HTML
<div class="section">
<h2>Экспорт главного меню системы в виде XML описания</h2>
HTML;
			flush();
			$dbUtility->exportSysAppMenuToXml();
			echo '</div>'; flush();
		}
		else {
			if ($isSQL) {
				$dbUtility->sqlFileName=str_replace("\\","/",realpath($path)).'/backup.sql';
				if (is_file($dbUtility->sqlFileName)) unlink($dbUtility->sqlFileName);
			}
			else {
				$dbUtility->xmlFileName=str_replace("\\","/",realpath($path)).'/backup.xml';
			}
			
			$lstDataStru=Array();
			$lstDataStru[]='systablecategory';
			$lstDataStru[]='systable';
			$lstDataStru[]='sysfieldtype';
			$lstDataStru[]='sysfield';
			$lstDataStru[]='sysfieldparams';
			if ($params['isdatastru']) {
				echo <<<HTML
<div class="section">
<h2>Резервное копирование описателей структуры базы</h2>
HTML;
				flush();
				foreach($lstDataStru as $index=>$tableName) {
					$p=Array();
					$p['tableName']=$tableName;
					$p['isEcho']=true;
					echo '<div class="message">'.$tableName.': '; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					if ($isSQL) {
						$dbUtility->exportTable($p);
					}
					else {
						$dbUtility->saveTable($p);
					}
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
				echo '</div>'; flush();
			}
			if ($params['issystem']) {
				echo <<<HTML
<div class="section">
<h2>Резервное копирование системных справочников</h2>
HTML;
				flush();
				
				$lstSystem=Array();
				$sql=<<<SQL
select systable.*
from 
	systable
where 
	systable.issystem='1' and
	systable.tablename not in (
		'sysextlog',
		'sysconfig'
	)
order by systable.tablename
SQL;
				$q=$this->pdo($sql);
				while ($rec=$this->pdoFetch($q)) {
					$table=$rec['tablename'];
					if ($params['isdatastru'] && in_array($table, $lstDataStru)) continue;
					$lstSystem[$table]=$table;
				}
				if (!$params['isdatastru']) {
					foreach($lstDataStru as $table) $lstSystem[$table]=$table;
				}
					
				$lstSystem['sysappmenu']='sysappmenu';
				foreach($lstSystem as $table) {
					$p=Array();
					$p['tableName']=$table;
					$p['isEcho']=true;
					echo '<div class="message">'.str2Html($table).': '; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					if ($isSQL) {
						$dbUtility->exportTable($p);
					}
					else {
						$dbUtility->saveTable($p);
					}
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
				echo '</div>'; flush();
			}
			if ($params['isstatic']) {
				echo <<<HTML
<div class="section">
<h2>Резервное копирование статических таблиц</h2>
HTML;
				flush();
			
			$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isstatic='1' and
	systable.issystem='0' and
	systable.tablename not in (
		'sysextlog',
		'sysconfig'
	)
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
				$q=$this->pdo($sql);
				while ($rec=$this->pdoFetch($q)) {
					$p=Array();
					$p['tableName']=$rec['tablename'];
					$p['isEcho']=true;
					echo '<div class="message">'.str2Html($rec['tablename']).': '; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					
					if ($isSQL) {
						$dbUtility->exportTable($p);
					}
					else {
						$dbUtility->saveTable($p);
					}
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
				echo '</div>'; flush();
			}
			if ($params['isdynamic']) {
				echo <<<HTML
<div class="section">
<h2>Резервное копирование динамических таблиц</h2>
HTML;
				flush();
				
				$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isdynamic='1' and
	systable.issystem='0' and
	systable.tablename not in (
		'sysextlog',
		'sysconfig'
	)
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
				$q=$this->pdo($sql);
				while ($rec=$this->pdoFetch($q)) {
					$p=Array();
					$p['tableName']=$rec['tablename'];
					$p['isEcho']=true;
					echo '<div class="message">'.str2Html($rec['tablename']).': '; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					
					if ($isSQL) {
						$dbUtility->exportTable($p);
					}
					else {
						$dbUtility->saveTable($p);
					}
					echo '</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
				echo '</div>'; flush();
			}
			if (!$isSQL) $dbUtility->xmlSave();
		}
		
		unset($dbUtility);
		echo 'Ok!</div>'; flush();
		echo '<script>document.body.scrollIntoView(false)</script>'; flush();
	}
}
return new UtilityBackup();
