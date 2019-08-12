<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysconfig'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource для таблицы 'sysconfig'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysconfig_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysconfig';
		$this->tableCaption='Конфигурации площадки';
		$this->permMode='readonly';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// code - Код
			$fld=Array();
			$fld['name']='code';
			$fld['type']='string';
			$fld['caption']='Код';
			$fld['maxlength']='32';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// val - Значение
			$fld=Array();
			$fld['name']='val';
			$fld['type']='string';
			$fld['caption']='Значение';
			$fld['maxlength']='64';
			$fld['stretch']='1';
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
	sysconfig.*
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
	sysconfig
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
sysconfig.id
SQL;
	}
}
return new DataSource_Sysconfig_System();