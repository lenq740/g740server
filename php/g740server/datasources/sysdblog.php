<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysdblog'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource_Sysdblog_System - источник данных для таблицы 'sysdblog'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysdblog_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysdblog';
		$this->tableCaption='Лог правки содержимого базы данных';
		$this->permMode='sysdblog';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// parent - Наименование родительской таблицы
			$fld=Array();
			$fld['name']='parent';
			$fld['type']='string';
			$fld['caption']='Наименование родительской таблицы';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// parentid - id строки родительской таблицы
			$fld=Array();
			$fld['name']='parentid';
			$fld['type']='string';
			$fld['caption']='id строки родительской таблицы';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// table - Таблица
			$fld=Array();
			$fld['name']='table';
			$fld['type']='string';
			$fld['caption']='Таблица';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// field - Поле
			$fld=Array();
			$fld['name']='field';
			$fld['type']='string';
			$fld['caption']='Поле';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// caption - Наименование поля
			$fld=Array();
			$fld['name']='caption';
			$fld['type']='string';
			$fld['caption']='Наименование поля';
			$fld['len']='25';
			$result[]=$fld;
		}
		{	// rowid - id строки таблицы
			$fld=Array();
			$fld['name']='rowid';
			$fld['type']='string';
			$fld['caption']='id строки таблицы';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// operation - операция upd|ins|del
			$fld=Array();
			$fld['name']='operation';
			$fld['type']='string';
			$fld['caption']='Операция';
			$fld['maxlength']='3';
			$fld['len']='8';
			$result[]=$fld;
		}
		{	// iconoperation - опр
			$fld=Array();
			$fld['name']='iconoperation';
			$fld['type']='icons';
			$fld['caption']='Опер';
			$fld['len']='4';
			$result[]=$fld;
		}
		{	// value - Значение
			$fld=Array();
			$fld['name']='value';
			$fld['type']='string';
			$fld['caption']='Значение';
			$fld['maxlength']='1024';
			$fld['len']='15';
			$fld['stretch']=1;
			$result[]=$fld;
		}
		{	// child - Наименование дочерней таблицы
			$fld=Array();
			$fld['name']='child';
			$fld['type']='string';
			$fld['caption']='Наименование дочерней таблицы';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// childid - id строки дочерней таблицы
			$fld=Array();
			$fld['name']='childid';
			$fld['type']='string';
			$fld['caption']='id строки дочерней таблицы';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// user - пользователь
			$fld=Array();
			$fld['name']='user';
			$fld['type']='string';
			$fld['caption']='Пользователь';
			$fld['maxlength']='36';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// d - Дата
			$fld=Array();
			$fld['name']='d';
			$fld['type']='date';
			$fld['caption']='Дата';
			$result[]=$fld;
		}
		{	// t - Время
			$fld=Array();
			$fld['name']='t';
			$fld['type']='string';
			$fld['caption']='Время';
			$fld['maxlength']='8';
			$fld['len']='8';
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем допустимые запросы
 */
	protected function initRequests() {
		$result=Array();
		$result[]=Array(
			'name'=>'refresh',
			'permoper'=>'read'
		);
		$result[]=Array(
			'name'=>'refreshrow',
			'permoper'=>'read'
		);
		return $result;
	}
/** Переопределяем связи ссылочной целостности
 */
	protected function initReferences() {
		$result=Array();
		return $result;
	}
/** Переопределяем секцию fields SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции fields SQL запроса select
 */
	public function getSelectFields($params=Array()) {
		$result=<<<SQL
	sysdblog.*
SQL;
		return $result;
	}
/** Переопределяем секцию from SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции from SQL запроса select
 */
	public function getSelectFrom($params=Array()) {
		$result=<<<SQL
	sysdblog
SQL;
		return $result;
	}
/** Переопределяем секцию order by SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции order by SQL запроса select
 */
	public function getSelectOrderBy($params=Array()) {
		return <<<SQL
sysdblog.id desc
SQL;
	}
/** Переопределяем секцию where SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции where SQL запроса select
 */
	public function getSelectWhere($params=Array()) {
		$result=parent::getSelectWhere($params);
		
		$driverName=$this->getDriverName();
		$D0='';
		$D1='';
		if ($driverName=='mysql') {
			$D0='`';
			$D1='`';
		}
		else if ($driverName=='sqlsrv') {
			$D0='[';
			$D1=']';
		}
		else if ($driverName=='pgsql') {
			$D0='"';
			$D1='"';
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		
		if ($params['filter.parent']) {
			$value=$this->str2Sql($params['filter.parent']);
			$result.="\n".<<<SQL
and sysdblog.{$D0}parent{$D1}='{$value}'
SQL;
			if ($params['filter.parentid']) {
				$value=$this->str2Sql($params['filter.parentid']);
				$result.="\n".<<<SQL
and sysdblog.{$D0}parentid{$D1}='{$value}'
SQL;
			}
		}
		if ($params['filter.table']) {
			$value=$this->str2Sql($params['filter.table']);
			$result.="\n".<<<SQL
and sysdblog.{$D0}table{$D1}='{$value}'
SQL;
			if ($params['filter.rowid']) {
				$value=$this->str2Sql($params['filter.rowid']);
				$result.="\n".<<<SQL
and sysdblog.{$D0}rowid{$D1}='{$value}'
SQL;
			}
		}
		if ($params['filter.field']) {
			$value=$this->str2Sql($params['filter.field']);
			$result.="\n".<<<SQL
and sysdblog.{$D0}field{$D1}='{$value}'
SQL;
		}
		if ($params['filter.operation']) {
			$value=$this->str2Sql($params['filter.operation']);
			$result.="\n".<<<SQL
and sysdblog.{$D0}operation{$D1}='{$value}'
SQL;
		}
		if ($params['filter.text']) {
			$value=$this->str2SqlLike($params['filter.text']);
			$result.="\n".<<<SQL
and sysdblog.{$D0}value{$D1} like '%{$value}%'
SQL;
		}
		return $result;
	}
/** Переопределяем обработку запроса refresh
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execRefresh($params=Array()) {
		$result=parent::execRefresh($params);
		$lstTable=Array();
		foreach($result as &$row) {
			$table=$row['table'];
			if (!$table) continue;
			if (!isset($lstTable[$table])) {
				try {
					$dataSource=getDataSource($table);
					$fields=$dataSource->getLogFields();
				}
				catch(Exception $e) {
					$fields=Array();
				}
				
				$lstFields=Array();
				foreach($fields as &$fld) {
					$fieldName=$fld['name'];
					$lstFields[$fieldName]=$fld;
				}
				$lstTable[$table]=&$lstFields;
				unset($lstFields);
			}
			
			$caption='';
			$field=$row['field'];
			if ($field) {
				$lstFields=&$lstTable[$table];
				if (isset($lstFields[$field])) $caption=$lstFields[$field]['caption'];
				if (!$caption) $caption=$field;
				unset($lstFields);
			}
			$row['caption']=$caption;
			$icon='';
			if ($row['operation']=='del') $icon='deleted';
			if ($row['operation']=='ins') $icon='inserted';
			if ($row['operation']=='upd') $icon='updated';
			$row['iconoperation']=$icon;
		}
		return $result;
	}
}
return new DataSource_Sysdblog_System();
