<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'systablecategory'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** Класс DataSource для таблицы 'systablecategory'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Systablecategory_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='systablecategory';
		$this->tableCaption='Категория таблицы';
		$this->permMode='root';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// name - Категория
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Категория';
			$fld['maxlength']='255';
			$fld['len']='12';
			$fld['notnull']='1';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// ord - №пп
			$fld=Array();
			$fld['name']='ord';
			$fld['type']='num';
			$fld['caption']='№пп';
			$fld['len']='5';
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем связи ссылочной целостности
 */
	protected function initReferences() {
		$result=Array();
		{	//  systablecategory.id -> systable.klssystablecategory
			$ref=Array();
			$ref['mode']='restrict';
			$ref['from.table']='systablecategory';
			$ref['from.field']='id';
			$ref['to.table']='systable';
			$ref['to.field']='klssystablecategory';
			$result['systable.klssystablecategory']=$ref;
		}
		return $result;
	}

/** Выполнить операцию append, вычисляем ord
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execAppend($params=Array()) {
		$params['#request.mode']='last';
		$sql=<<<SQL
select 
	max(systablecategory.ord) as ord
from
	systablecategory
SQL;
		$rec=$this->pdoFetch($sql);
		$ord=$rec['ord'];
		if (!$ord) $ord=0;
		$params['ord']=$ord+10;
		$result=parent::execAppend($params);
		return $result;
	}

	public function execSave($params=Array()) {
		$result=parent::execSave($params);
		return $result;
	}


/** Переопределяем секцию fields SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции fields SQL запроса select
 */
	public function getSelectFields($params=Array()) {
		$result=<<<SQL
	systablecategory.*
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
	systablecategory
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
systablecategory.ord, systablecategory.id
SQL;
	}
}
return new DataSource_Systablecategory_System();