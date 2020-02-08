<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysfield'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource для таблицы 'sysfield'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysfield_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysfield';
		$this->tableCaption='Поле таблицы';
		$this->permMode='root';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// klssystable - Ссылка на родительскую таблицу
			$fld=Array();
			$fld['name']='klssystable';
			$fld['type']='ref';
			$fld['caption']='Ссылка на родительскую таблицу';
			$fld['notnull']='1';
			$fld['reftable']='systable';
			$result[]=$fld;
		}
		{	// fieldname - Поле
			$fld=Array();
			$fld['name']='fieldname';
			$fld['type']='string';
			$fld['caption']='Поле';
			$fld['maxlength']='255';
			$fld['len']='15';
			$fld['notnull']='1';
			$result[]=$fld;
		}
		{	// name - Описание поля
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Описание поля';
			$fld['maxlength']='255';
			$fld['len']='25';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// isnotempty - Не пусто
			$fld=Array();
			$fld['name']='isnotempty';
			$fld['type']='check';
			$fld['caption']='Не пусто';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// ismain - Main
			$fld=Array();
			$fld['name']='ismain';
			$fld['type']='check';
			$fld['caption']='Main';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// isstretch - Stretch
			$fld=Array();
			$fld['name']='isstretch';
			$fld['type']='check';
			$fld['caption']='Stretch';
			$fld['len']='6';
			$result[]=$fld;
		}
		{	// klssysfieldtype - Ссылка на тип поля
			$fld=Array();
			$fld['name']='klssysfieldtype';
			$fld['type']='ref';
			$fld['caption']='Ссылка на тип поля';
			$fld['reftable']='sysfieldtype';
			$result[]=$fld;
		}
		{	// maxlength - Максимальная длина
			$fld=Array();
			$fld['name']='maxlength';
			$fld['type']='num';
			$fld['caption']='Максимальная длина';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// len - Длина
			$fld=Array();
			$fld['name']='len';
			$fld['type']='num';
			$fld['caption']='Длина';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// dec - После запятой
			$fld=Array();
			$fld['name']='dec';
			$fld['type']='num';
			$fld['caption']='После запятой';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// klsreftable - Ссылка на связанную таблицу
			$fld=Array();
			$fld['name']='klsreftable';
			$fld['type']='ref';
			$fld['caption']='Ссылка на связанную таблицу';
			$fld['reftable']='systable';
			$fld['refalias']='reftable';
			$result[]=$fld;
		}
		{	// reflink - Имя ссылки
			$fld=Array();
			$fld['name']='reflink';
			$fld['type']='string';
			$fld['caption']='Имя ссылки';
			$fld['maxlength']='255';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// isrefrestrict - Restrict связь
			$fld=Array();
			$fld['name']='isrefrestrict';
			$fld['type']='check';
			$fld['caption']='Restrict связь';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// isrefcascade - Cascade связь
			$fld=Array();
			$fld['name']='isrefcascade';
			$fld['type']='check';
			$fld['caption']='Cascade связь';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// isrefclear - Clear связь
			$fld=Array();
			$fld['name']='isrefclear';
			$fld['type']='check';
			$fld['caption']='Clear связь';
			$fld['len']='5';
			$result[]=$fld;
		}
		{	// isref121 - 1 к 1 связь
			$fld=Array();
			$fld['name']='isref121';
			$fld['type']='check';
			$fld['caption']='1 к 1 связь';
			$fld['len']='5';
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
		{	// systable_tablename - Таблица
			$fld=Array();
			$fld['name']='systable_tablename';
			$fld['fieldname']='tablename';
			$fld['table']='systable';
			$fld['type']='string';
			$fld['caption']='Таблица';
			$fld['maxlength']='255';
			$fld['len']='12';
			$fld['notnull']='1';
			$fld['refname']='tablename';
			$fld['refid']='klssystable';
			$result[]=$fld;
		}
		{	// systable_name - Описание
			$fld=Array();
			$fld['name']='systable_name';
			$fld['fieldname']='name';
			$fld['table']='systable';
			$fld['type']='string';
			$fld['caption']='Описание';
			$fld['maxlength']='255';
			$fld['len']='25';
			$fld['refname']='name';
			$fld['refid']='klssystable';
			$result[]=$fld;
		}
		{	// sysfieldtype_name - Тип
			$fld=Array();
			$fld['name']='sysfieldtype_name';
			$fld['fieldname']='name';
			$fld['table']='sysfieldtype';
			$fld['type']='string';
			$fld['caption']='Тип';
			$fld['maxlength']='255';
			$fld['len']='12';
			$fld['notnull']='1';
			$fld['refname']='name';
			$fld['refid']='klssysfieldtype';
			$result[]=$fld;
		}
		{	// sysfieldtype_g740type - Тип в g740
			$fld=Array();
			$fld['name']='sysfieldtype_g740type';
			$fld['fieldname']='g740type';
			$fld['table']='sysfieldtype';
			$fld['type']='string';
			$fld['caption']='Тип в g740';
			$fld['maxlength']='255';
			$fld['len']='15';
			$fld['refname']='g740type';
			$fld['refid']='klssysfieldtype';
			$result[]=$fld;
		}
		{	// reftable_tablename - Таблица
			$fld=Array();
			$fld['name']='reftable_tablename';
			$fld['fieldname']='tablename';
			$fld['table']='systable';
			$fld['alias']='reftable';
			$fld['type']='string';
			$fld['caption']='Таблица';
			$fld['maxlength']='255';
			$fld['len']='12';
			$fld['notnull']='1';
			$fld['refname']='tablename';
			$fld['refid']='klsreftable';
			$result[]=$fld;
		}
		{	// reftable_name - Описание
			$fld=Array();
			$fld['name']='reftable_name';
			$fld['fieldname']='name';
			$fld['table']='systable';
			$fld['alias']='reftable';
			$fld['type']='string';
			$fld['caption']='Описание';
			$fld['maxlength']='255';
			$fld['len']='25';
			$fld['refname']='name';
			$fld['refid']='klsreftable';
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем связи ссылочной целостности
 */
	protected function initReferences() {
		$result=Array();
		{	//  sysfield.klssystable -> systable.id
			$ref=Array();
			$ref['mode']='cascade';
			$ref['from.table']='sysfield';
			$ref['from.field']='klssystable';
			$ref['to.table']='systable';
			$ref['to.field']='id';
			$result['sysfield.klssystable']=$ref;
		}
		{	//  sysfield.klssysfieldtype -> sysfieldtype.id
			$ref=Array();
			$ref['mode']='restrict';
			$ref['from.table']='sysfield';
			$ref['from.field']='klssysfieldtype';
			$ref['to.table']='sysfieldtype';
			$ref['to.field']='id';
			$result['sysfield.klssysfieldtype']=$ref;
		}
		{	//  sysfield.klsreftable -> systable.id
			$ref=Array();
			$ref['mode']='restrict';
			$ref['from.table']='sysfield';
			$ref['from.field']='klsreftable';
			$ref['to.table']='systable';
			$ref['to.field']='id';
			$result['sysfield.klsreftable']=$ref;
		}
		{	//  sysfield.id -> sysfieldparams.klssysfield
			$ref=Array();
			$ref['mode']='cascade';
			$ref['from.table']='sysfield';
			$ref['from.field']='id';
			$ref['to.table']='sysfieldparams';
			$ref['to.field']='klssysfield';
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
	sysfield.*,
	systable.tablename as systable_tablename,
	systable.name as systable_name,
	sysfieldtype.name as sysfieldtype_name,
	sysfieldtype.g740type as sysfieldtype_g740type,
	reftable.tablename as reftable_tablename,
	reftable.name as reftable_name
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
	sysfield
		left join systable on systable.id=sysfield.klssystable
		left join sysfieldtype on sysfieldtype.id=sysfield.klssysfieldtype
		left join systable reftable on reftable.id=sysfield.klsreftable
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
sysfield.klssystable,
sysfield.ord, sysfield.id
SQL;
	}
}
return new DataSource_Sysfield_System();