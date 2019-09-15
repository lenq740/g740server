<?php
/**
 * @file
 * G740Server, утилита восстановления из резервной копии
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/// Класс контроллера утилиты восстановления из резервной копии
class UtilityRestore extends UtilController {
/// Наименование утилиты
	public $caption='Импорт таблиц';

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
		if ($_REQUEST['issystem']) {
			$params['isdatastru']=1;
			$params['issystem']=1;
		}
		if ($_REQUEST['isall']) {
			$params['isdatastru']=1;
			$params['issystem']=1;
			$params['isstatic']=1;
			$params['isdynamic']=1;
		}
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
		if (!getPerm(getCfg('perm.utils.restore','root'),'write')) throw new Exception('У Вас нет прав на восстановление из резервной копии');

		$path=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup');
		if (!is_dir($path)) throw new Exception("Не найдена папка: '{$path}'");


		try {
			$dbUtility = new DBUtility();
			if (getCfg('sqlSuperUserLogin')) {
				$pdoSuperUser=newPDODataConnector(
					getCfg('sqlDriverName'),
					getCfg('sqlDbName'),
					getCfg('sqlSuperUserLogin'),
					getCfg('sqlSuperUserPassword'),
					getCfg('sqlCharSet'),
					getCfg('sqlHost'),
					getCfg('sqlPort')
				); // Устанавливаем соединение с базой данных
				$pdoSuperUser->beginTransaction();
				regPDO($pdoSuperUser,'superuser');
				$dbUtility->pdoName='superuser';
				$this->pdoName='superuser';
			}
			if ($params['ismxldatastru']) {
				$fileName=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup','datamodel.xml');
				if (!is_file($fileName)) throw new Exception("Файл '{$fileName}' не найден");
				$dbUtility->xmlFileName=str_replace("\\","/",realpath($fileName));
				echo '<h2>Импорт структуры базы из XML описания</h2>'; flush();
				echo <<<HTML
<div class="section">
<h3>Восстановление описателей структуры базы</h3>
HTML;
				flush();
				$dbUtility->importDataStruFromXml(true);

				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				echo '</div>'; flush();
			}
			else if ($params['ismxlmenu']) {
				$fileName=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup','menu.xml');
				if (!is_file($fileName)) throw new Exception("Файл '{$fileName}' не найден");
				$dbUtility->xmlFileName=str_replace("\\","/",realpath($fileName));
				echo '<h2>Импорт главного меню системы из XML описания</h2>'; flush();
				echo <<<HTML
<div class="section">
<h3>Импорт главного меню системы</h3>
HTML;
				flush();
				$dbUtility->importSysAppMenuFromXml();

				echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				echo '</div>'; flush();
			}
			else {
				$fileName=pathConcat(getCfg('path.root'),getCfg('path.root.export-import'),'backup','backup.xml');
				if (!is_file($fileName)) throw new Exception("Файл '{$fileName}' не найден");
				$dbUtility->xmlFileName=str_replace("\\","/",realpath($fileName));
				echo '<h2>Выполнение восстановления из резервной копии</h2>'; flush();

				$lstDataStru=Array();
				$lstDataStru[]='systable';
				$lstDataStru[]='systablecategory';
				$lstDataStru[]='sysfieldtype';
				$lstDataStru[]='sysfield';
				$lstDataStru[]='sysfieldparams';
				if ($params['isdatastru']) {
					echo <<<HTML
<div class="section">
<h3>Восстановление описателей структуры базы</h3>
HTML;
					flush();

					$p=Array();
					$p['tables']=$lstDataStru;
					$p['isEcho']=true;
					$dbUtility->loadTables($p);
					
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					echo '</div>'; flush();
				}
				if ($params['issystem']) {
					echo <<<HTML
<div class="section">
<h3>Восстановление системных справочников</h3>
HTML;
					flush();
					
					$p=Array();
					$lst=Array();
					$sql=<<<SQL
select systable.*
from 
	systable
where 
	systable.issystem='1'
order by systable.tablename
SQL;
					$q=$this->pdo($sql);
					while ($rec=$this->pdoFetch($q)) {
						$table=$rec['tablename'];
						if ($table=='sysappmenu') continue;
						if (in_array($table, $lstDataStru)) continue;
						$lst[]=$table;
					}
					$lst[]='sysappmenu';
					$p['tables']=$lst;
					$p['isEcho']=true;
					$dbUtility->loadTables($p);
					
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					echo '</div>'; flush();
				}
				if ($params['isstatic']) {
					echo <<<HTML
<div class="section">
<h3>Восстановление статических таблиц</h3>
HTML;
					flush();

					$lst=Array();
					$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isstatic='1' and
	systable.issystem='0'
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
					$q=$this->pdo($sql);
					while ($rec=$this->pdoFetch($q)) {
						$table=$rec['tablename'];
						if ($table=='sysappmenu') continue;
						if (in_array($table, $lstDataStru)) continue;
						$lst[]=$table;
					}

					$p=Array();
					$p['tables']=$lst;
					$p['isEcho']=true;
					$dbUtility->loadTables($p);
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					echo '</div>'; flush();
				}
				if ($params['isdynamic']) {
					echo <<<HTML
<div class="section">
<h3>Восстановление динамических таблиц</h3>
HTML;
					flush();

					$lst=Array();
					$sql=<<<SQL
select systable.*
from 
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
where 
	systable.isdynamic='1' and
	systable.issystem='0'
order by systablecategory.ord, systablecategory.id, systable.tablename
SQL;
					$q=$this->pdo($sql);
					while ($rec=$this->pdoFetch($q)) {
						$table=$rec['tablename'];
						if ($table=='sysappmenu') continue;
						if (in_array($table, $lstDataStru)) continue;
						$lst[]=$table;
					}

					$p=Array();
					$p['tables']=$lst;
					$p['isEcho']=true;
					$dbUtility->loadTables($p);
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
					echo '</div>'; flush();
				}
			}
			if ($pdoSuperUser) {
				if ($pdoSuperUser->inTransaction()) $pdoSuperUser->commit();
			}
		}
		catch (Exception $e) {
			if ($pdoSuperUser) {
				if ($pdoSuperUser->inTransaction()) $pdoSuperUser->rollBack();
			}
			throw new Exception($e->getMessage());
		}
	}
}
return new UtilityRestore();
