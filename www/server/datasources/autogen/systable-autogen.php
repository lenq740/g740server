<?php
class DataSource_Systable extends DataSource {
// Инициализация констант
function __construct() {
	$this->tableName='systable';
	$this->tableCaption='Таблица';
	$this->permMode='sys';
	$this->isGUID=false;
}
// Тут описываются поля источника данных
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
	{	// systablecategory_name - Категория
		$fld=Array();
		$fld['name']='systablecategory_name';
		$fld['fieldname']='name';
		$fld['table']='systablecategory';
		$fld['type']='string';
		$fld['caption']='Категория';
		$fld['maxlength']='255';
		$fld['len']='12';
		$fld['notnull']='1';
		$fld['refname']='name';
		$fld['refid']='klssystablecategory';
		$result[]=$fld;
	}
	return $result;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
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
// Этот метод возвращает список полей для запроса select
public function getSelectFields($params=Array()) {
	$result=<<<SQL
	`systable`.*,
	`systablecategory`.`name` as `systablecategory_name`
SQL;
	return $result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom($params=Array()) {
	$result=<<<SQL
	`systable`
		left join `systablecategory` on `systablecategory`.id=`systable`.`klssystablecategory`
SQL;
	return $result;
}
// Этот метод Этот метод возвращает секцию where для запроса select
public function getSelectWhere($params=Array()) {
	$result='';
	if (isset($params['filter.id'])) {
		$value=$this->php2SqlIn($params['filter.id']);
		if ($value!='') $result.="\n"."and `systable`.id in ({$value})";
	}
	if (isset($params['filter.klssystablecategory'])) {
		$value=$this->php2SqlIn($params['filter.klssystablecategory']);
		if ($value!='') $result.="\n"."and `systable`.`klssystablecategory` in ({$value})";
	}
	if ($params['filter.isstatic']!='') {
		$value=$this->php2Sql($params['filter.isstatic']);
		$result.="\n"."and `systable`.`isstatic`='{$value}'";
	}
	if ($params['filter.isdynamic']!='') {
		$value=$this->php2Sql($params['filter.isdynamic']);
		$result.="\n"."and `systable`.`isdynamic`='{$value}'";
	}
	if ($params['filter.issystem']!='') {
		$value=$this->php2Sql($params['filter.issystem']);
		$result.="\n"."and `systable`.`issystem`='{$value}'";
	}
	return $result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy($params=Array()) {
	return <<<SQL
systable.klssystablecategory, systable.tablename, systable.id
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFieldsDemo($params=Array()) {
	$result=<<<XML
<fields>
<field name="tablename" type="string" caption="Таблица" notnull="1" len="12" maxlength="255"/>
<field name="name" type="string" caption="Описание" stretch="1" len="25" maxlength="255"/>
<field name="klssystablecategory" type="ref" caption="Ссылка на категорию таблицы" notnull="1">
	<ref datasource="systablecategory"/>
</field>
<field name="isstatic" type="check" caption="Статичная таблица" len="4"/>
<field name="isdynamic" type="check" caption="Динамичная таблица" len="4"/>
<field name="issystem" type="check" caption="Системная таблица" len="4"/>
<field name="orderby" type="memo" caption="Сортировка" stretch="1"/>
<field name="fields" type="memo" caption="Дополнительные поля" stretch="1"/>
<field name="permmode" type="string" caption="Режим по правам" len="12" maxlength="255"/>
<field name="systablecategory_name" type="string" caption="Категория" notnull="1" len="12" maxlength="255" refid="klssystablecategory" refname="name"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Systable();