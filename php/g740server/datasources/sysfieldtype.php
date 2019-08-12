<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysfieldtype'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource для таблицы 'sysfieldtype'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysfieldtype_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysfieldtype';
		$this->tableCaption='Тип поля';
		$this->permMode='root';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// name - Тип
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Тип';
			$fld['maxlength']='255';
			$fld['len']='12';
			$fld['notnull']='1';
			$result[]=$fld;
		}
		{	// isid - id
			$fld=Array();
			$fld['name']='isid';
			$fld['type']='check';
			$fld['caption']='id';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// isref - Ссылка
			$fld=Array();
			$fld['name']='isref';
			$fld['type']='check';
			$fld['caption']='Ссылка';
			$fld['len']='6';
			$result[]=$fld;
		}
		{	// isdec - Число
			$fld=Array();
			$fld['name']='isdec';
			$fld['type']='check';
			$fld['caption']='Число';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// isstr - Строка
			$fld=Array();
			$fld['name']='isstr';
			$fld['type']='check';
			$fld['caption']='Строка';
			$fld['len']='6';
			$result[]=$fld;
		}
		{	// isdat - Дата
			$fld=Array();
			$fld['name']='isdat';
			$fld['type']='check';
			$fld['caption']='Дата';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// defvalue - Значение по умолчанию
			$fld=Array();
			$fld['name']='defvalue';
			$fld['type']='string';
			$fld['caption']='Значение по умолчанию';
			$fld['maxlength']='255';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// jstype - Тип в JavaScript
			$fld=Array();
			$fld['name']='jstype';
			$fld['type']='string';
			$fld['caption']='Тип в JavaScript';
			$fld['maxlength']='255';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// g740type - Тип в g740
			$fld=Array();
			$fld['name']='g740type';
			$fld['type']='string';
			$fld['caption']='Тип в g740';
			$fld['maxlength']='255';
			$fld['len']='15';
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
		
		return $result;
	}
/** Переопределяем секцию fields SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции fields SQL запроса select
 */
	public function getSelectFields($params=Array()) {
		$result=<<<SQL
	sysfieldtype.*
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
	sysfieldtype
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
sysfieldtype.ord, sysfieldtype.id
SQL;
	}
}
return new DataSource_Sysfieldtype_System();