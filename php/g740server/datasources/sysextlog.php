<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysextlog'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource_Sysextlog_System - источник данных для таблицы 'sysextlog'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysextlog_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysextlog';
		$this->tableCaption='Лог фоновых процессов';
		$this->permMode='readonly';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// d - Дата
			$fld=Array();
			$fld['name']='d';
			$fld['type']='date';
			$fld['caption']='Дата';
			$result[]=$fld;
		}
		{	// tstart - Начало
			$fld=Array();
			$fld['name']='tstart';
			$fld['type']='string';
			$fld['caption']='Начало';
			$fld['maxlength']='5';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// tend - Окончание
			$fld=Array();
			$fld['name']='tend';
			$fld['type']='string';
			$fld['caption']='Окончание';
			$fld['maxlength']='5';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// name - Фоновый процесс
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Фоновый процесс';
			$fld['maxlength']='64';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// message - Результат
			$fld=Array();
			$fld['name']='message';
			$fld['type']='string';
			$fld['caption']='Результат';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// iserror - Ошибка
			$fld=Array();
			$fld['name']='iserror';
			$fld['type']='num';
			$fld['caption']='Ошибка';
			$result[]=$fld;
		}
		{ // icon
			$fld=Array();
			$fld['name']='icon';
			$fld['type']='icons';
			$fld['len']=3;
			$fld['caption']='!';
			$fld['virtual']=1;
			$fld['readonly']=1;
			$result[]=$fld;
		}
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
	sysextlog.*
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
	sysextlog
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
sysextlog.d desc, sysextlog.tstart, sysextlog.id
SQL;
	}
/** Переопределяем секцию where SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции where SQL запроса select
 */
	public function getSelectWhere($params=Array()) {
		$result=parent::getSelectWhere($params);
		if ($params['filter.name']) {
			$value=$this->str2Sql($params['filter.name']);
			$result.="\n".<<<SQL
and sysextlog.name='{$value}'
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
		foreach($result as &$row) {
			$row['icon']='';
			if ($row['iserror']) $row['icon']='error';
		}
		return $result;
	}
}
return new DataSource_Sysextlog_System();
