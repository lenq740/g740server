<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysfieldparams'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource для таблицы 'sysfieldparams'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysfieldparams_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysfieldparams';
		$this->tableCaption='Параметр поля';
		$this->permMode='root';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// klssysfield - Ссылка на поле
			$fld=Array();
			$fld['name']='klssysfield';
			$fld['type']='ref';
			$fld['caption']='Ссылка на поле';
			$fld['notnull']='1';
			$fld['reftable']='sysfield';
			$result[]=$fld;
		}
		{	// name - Параметр
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Параметр';
			$fld['maxlength']='255';
			$fld['len']='15';
			$fld['notnull']='1';
			$result[]=$fld;
		}
		{	// val - Значение
			$fld=Array();
			$fld['name']='val';
			$fld['type']='memo';
			$fld['caption']='Значение';
			$fld['len']='65';
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем связи ссылочной целостности
 */
	protected function initReferences() {
		$result=Array();
		{	//  sysfieldparams.klssysfield -> sysfield.id
			$ref=Array();
			$ref['mode']='cascade';
			$ref['from.table']='sysfieldparams';
			$ref['from.field']='klssysfield';
			$ref['to.table']='sysfield';
			$ref['to.field']='id';
			$result['sysfieldparams.klssysfield']=$ref;
		}
		return $result;
	}
/** Переопределяем секцию fields SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции fields SQL запроса select
 */
	public function getSelectFields($params=Array()) {
		$result=<<<SQL
	sysfieldparams.*
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
	sysfieldparams
		left join sysfield on sysfield.id=sysfieldparams.klssysfield
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
sysfieldparams.klssysfield, sysfieldparams.name
SQL;
	}
}
return new DataSource_Sysfieldparams_System();