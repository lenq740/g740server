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
public function getFields() {
	if ($this->fields) return $this->fields;
	$this->fields=Array();
	{	// tablename - Таблица
		$fld=Array();
		$fld['name']='tablename';
		$fld['type']='string';
		$fld['caption']='Таблица';
		$fld['maxlength']='255';
		$fld['len']='12';
		$fld['notnull']='1';
		$this->fields[]=$fld;
	}
	{	// name - Описание
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Описание';
		$fld['maxlength']='255';
		$fld['len']='25';
		$this->fields[]=$fld;
	}
	{	// klssystablecategory - Ссылка на категорию таблицы
		$fld=Array();
		$fld['name']='klssystablecategory';
		$fld['type']='ref';
		$fld['caption']='Ссылка на категорию таблицы';
		$fld['notnull']='1';
		$fld['reftable']='systablecategory';
		$this->fields[]=$fld;
	}
	{	// isstatic - Статичная таблица
		$fld=Array();
		$fld['name']='isstatic';
		$fld['type']='check';
		$fld['caption']='Статичная таблица';
		$fld['len']='4';
		$this->fields[]=$fld;
	}
	{	// isdynamic - Динамичная таблица
		$fld=Array();
		$fld['name']='isdynamic';
		$fld['type']='check';
		$fld['caption']='Динамичная таблица';
		$fld['len']='4';
		$this->fields[]=$fld;
	}
	{	// issystem - Системная таблица
		$fld=Array();
		$fld['name']='issystem';
		$fld['type']='check';
		$fld['caption']='Системная таблица';
		$fld['len']='4';
		$this->fields[]=$fld;
	}
	{	// orderby - Сортировка
		$fld=Array();
		$fld['name']='orderby';
		$fld['type']='memo';
		$fld['caption']='Сортировка';
		$fld['stretch']='1';
		$this->fields[]=$fld;
	}
	{	// fields - Дополнительные поля
		$fld=Array();
		$fld['name']='fields';
		$fld['type']='memo';
		$fld['caption']='Дополнительные поля';
		$fld['stretch']='1';
		$this->fields[]=$fld;
	}
	{	// permmode - Режим по правам
		$fld=Array();
		$fld['name']='permmode';
		$fld['type']='string';
		$fld['caption']='Режим по правам';
		$fld['maxlength']='255';
		$fld['len']='12';
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
	}
	return $this->fields;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
public function getReferences() {
	$result=Array();
	{	// systable.id --cascade--> sysfield.klssystable
		$ref=Array();
		$ref['mode']='cascade';
		$ref['from.table']='systable';
		$ref['from.field']='id';
		$ref['to.table']='sysfield';
		$ref['to.field']='klssystable';
		$result[]=$ref;
	}
	{	// sysfield.klsreftable --restrict--> systable.id
		$ref=Array();
		$ref['mode']='restrict';
		$ref['from.table']='sysfield';
		$ref['from.field']='klsreftable';
		$ref['to.table']='systable';
		$ref['to.field']='id';
		$result[]=$ref;
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
	if (array_key_exists('filter.id', $params)) {
		if ($this->isGUID) {
			$value=$this->guid2Sql($params['filter.id']);
		}
		else {
			$value=$this->php2Sql($params['filter.id']);
		}
		$result.="\n"."and `systable`.id='{$value}'";
	}
	if ($params['filter.klssystablecategory']!='') {
		$value=$this->php2Sql($params['filter.klssystablecategory']);
		$result.="\n"."and `systable`.`klssystablecategory`='{$value}'";
	}
	if (isset($params['filter.isstatic'])) {
		$value=$this->php2Sql($params['filter.isstatic']);
		$result.="\n"."and `systable`.`isstatic`='{$value}'";
	}
	if (isset($params['filter.isdynamic'])) {
		$value=$this->php2Sql($params['filter.isdynamic']);
		$result.="\n"."and `systable`.`isdynamic`='{$value}'";
	}
	if (isset($params['filter.issystem'])) {
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
public function getStrXmlDefinitionFields($params=Array()) {
	$result=<<<XML
<fields>
<field name="tablename" type="string" caption="Таблица" notnull="1" len="12" maxlength="255"/>
<field name="name" type="string" caption="Описание" len="25" maxlength="255"/>
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
?>