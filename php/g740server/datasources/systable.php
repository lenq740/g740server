<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'systable'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource для таблицы 'systable'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Systable_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='systable';
		$this->tableCaption='Таблица';
		$this->permMode='root';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// tablename - Таблица
			$fld=Array();
			$fld['name']='tablename';
			$fld['type']='string';
			$fld['caption']='Таблица';
			$fld['maxlength']='255';
			$fld['len']='12';
			$fld['notnull']='1';
			$result[]=$fld;
		}
		{	// name - Описание
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Описание';
			$fld['maxlength']='255';
			$fld['len']='25';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// klssystablecategory - Ссылка на категорию таблицы
			$fld=Array();
			$fld['name']='klssystablecategory';
			$fld['type']='ref';
			$fld['caption']='Ссылка на категорию таблицы';
			$fld['notnull']='1';
			$fld['reftable']='systablecategory';
			$result[]=$fld;
		}
		{	// isstatic - Статичная таблица
			$fld=Array();
			$fld['name']='isstatic';
			$fld['type']='check';
			$fld['caption']='Статичная таблица';
			$fld['len']='4';
			$result[]=$fld;
		}
		{	// isdynamic - Динамичная таблица
			$fld=Array();
			$fld['name']='isdynamic';
			$fld['type']='check';
			$fld['caption']='Динамичная таблица';
			$fld['len']='4';
			$result[]=$fld;
		}
		{	// issystem - Системная таблица
			$fld=Array();
			$fld['name']='issystem';
			$fld['type']='check';
			$fld['caption']='Системная таблица';
			$fld['len']='4';
			$result[]=$fld;
		}
		{	// orderby - Сортировка
			$fld=Array();
			$fld['name']='orderby';
			$fld['type']='memo';
			$fld['caption']='Сортировка';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// fields - Дополнительные поля
			$fld=Array();
			$fld['name']='fields';
			$fld['type']='memo';
			$fld['caption']='Дополнительные поля';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// permmode - Режим по правам
			$fld=Array();
			$fld['name']='permmode';
			$fld['type']='string';
			$fld['caption']='Режим по правам';
			$fld['maxlength']='255';
			$fld['len']='12';
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем связи ссылочной целостности
 */
	protected function initReferences() {
		$result=Array();
		{	//  systable.id -> sysfield.klssystable
			$ref=Array();
			$ref['mode']='cascade';
			$ref['from.table']='systable';
			$ref['from.field']='id';
			$ref['to.table']='sysfield';
			$ref['to.field']='klssystable';
			$result['sysfield.klssystable']=$ref;
		}
		{	//  systable.id -> sysfield.klsreftable
			$ref=Array();
			$ref['mode']='restrict';
			$ref['from.table']='systable';
			$ref['from.field']='id';
			$ref['to.table']='sysfield';
			$ref['to.field']='klsreftable';
			$result['sysfield.klsreftable']=$ref;
		}
		{	//  systable.klssystablecategory -> systablecategory.id
			$ref=Array();
			$ref['mode']='restrict';
			$ref['from.table']='systable';
			$ref['from.field']='klssystablecategory';
			$ref['to.table']='systablecategory';
			$ref['to.field']='id';
			$result['systable.klssystablecategory']=$ref;
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
	systable.*
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
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
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
systable.klssystablecategory, systable.tablename, systable.id
SQL;
	}
}
return new DataSource_Systable_System();