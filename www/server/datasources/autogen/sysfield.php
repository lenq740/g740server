<?php
class DataSource_Sysfield extends DataSource {
// Инициализация констант
function __construct() {
	$this->tableName='sysfield';
	$this->tableCaption='Поле таблицы';
	$this->permMode='sys';
	$this->isGUID=false;
}
// Тут описываются поля источника данных
public function getFields() {
	if ($this->fields) return $this->fields;
	$this->fields=Array();
	{	// klssystable - Ссылка на родительскую таблицу
		$fld=Array();
		$fld['name']='klssystable';
		$fld['type']='ref';
		$fld['caption']='Ссылка на родительскую таблицу';
		$fld['notnull']='1';
		$fld['reftable']='systable';
		$this->fields[]=$fld;
	}
	{	// fieldname - Поле
		$fld=Array();
		$fld['name']='fieldname';
		$fld['type']='string';
		$fld['caption']='Поле';
		$fld['maxlength']='255';
		$fld['len']='15';
		$fld['notnull']='1';
		$this->fields[]=$fld;
	}
	{	// name - Описание поля
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Описание поля';
		$fld['maxlength']='255';
		$fld['len']='25';
		$fld['stretch']='1';
		$this->fields[]=$fld;
	}
	{	// isnotempty - Не пусто
		$fld=Array();
		$fld['name']='isnotempty';
		$fld['type']='check';
		$fld['caption']='Не пусто';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// ismain - Main
		$fld=Array();
		$fld['name']='ismain';
		$fld['type']='check';
		$fld['caption']='Main';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// isstretch - Stretch
		$fld=Array();
		$fld['name']='isstretch';
		$fld['type']='check';
		$fld['caption']='Stretch';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// klssysfieldtype - Ссылка на тип поля
		$fld=Array();
		$fld['name']='klssysfieldtype';
		$fld['type']='ref';
		$fld['caption']='Ссылка на тип поля';
		$fld['reftable']='sysfieldtype';
		$this->fields[]=$fld;
	}
	{	// maxlength - Максимальная длина
		$fld=Array();
		$fld['name']='maxlength';
		$fld['type']='num';
		$fld['caption']='Максимальная длина';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// len - Длина
		$fld=Array();
		$fld['name']='len';
		$fld['type']='num';
		$fld['caption']='Длина';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// dec - После запятой
		$fld=Array();
		$fld['name']='dec';
		$fld['type']='num';
		$fld['caption']='После запятой';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// klsreftable - Ссылка на связанную таблицу
		$fld=Array();
		$fld['name']='klsreftable';
		$fld['type']='ref';
		$fld['caption']='Ссылка на связанную таблицу';
		$fld['reftable']='systable';
		$fld['refalias']='reftable';
		$this->fields[]=$fld;
	}
	{	// reflink - Имя ссылки
		$fld=Array();
		$fld['name']='reflink';
		$fld['type']='string';
		$fld['caption']='Имя ссылки';
		$fld['maxlength']='255';
		$fld['len']='15';
		$this->fields[]=$fld;
	}
	{	// isrefrestrict - Restrict связь
		$fld=Array();
		$fld['name']='isrefrestrict';
		$fld['type']='check';
		$fld['caption']='Restrict связь';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// isrefcascade - Cascade связь
		$fld=Array();
		$fld['name']='isrefcascade';
		$fld['type']='check';
		$fld['caption']='Cascade связь';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// isrefclear - Clear связь
		$fld=Array();
		$fld['name']='isrefclear';
		$fld['type']='check';
		$fld['caption']='Clear связь';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// isref121 - 1 к 1 связь
		$fld=Array();
		$fld['name']='isref121';
		$fld['type']='check';
		$fld['caption']='1 к 1 связь';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// ord - №пп
		$fld=Array();
		$fld['name']='ord';
		$fld['type']='num';
		$fld['caption']='№пп';
		$fld['len']='5';
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
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
		$this->fields[]=$fld;
	}
	return $this->fields;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
public function getReferences() {
	$result=Array();
	{	// sysfield.id --cascade--> sysfieldparams.klssysfield
		$ref=Array();
		$ref['mode']='cascade';
		$ref['from.table']='sysfield';
		$ref['from.field']='id';
		$ref['to.table']='sysfieldparams';
		$ref['to.field']='klssysfield';
		$result[]=$ref;
	}
	return $result;
}
// Этот метод возвращает список полей для запроса select
public function getSelectFields($params=Array()) {
	$result=<<<SQL
	`sysfield`.*,
	`systable`.`tablename` as `systable_tablename`,
	`systable`.`name` as `systable_name`,
	`sysfieldtype`.`name` as `sysfieldtype_name`,
	`sysfieldtype`.`g740type` as `sysfieldtype_g740type`,
	`reftable`.`tablename` as `reftable_tablename`,
	`reftable`.`name` as `reftable_name`
SQL;
	return $result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom($params=Array()) {
	$result=<<<SQL
	`sysfield`
		left join `systable` on `systable`.id=`sysfield`.`klssystable`
		left join `sysfieldtype` on `sysfieldtype`.id=`sysfield`.`klssysfieldtype`
		left join `systable` `reftable` on `reftable`.id=`sysfield`.`klsreftable`
SQL;
	return $result;
}
// Этот метод Этот метод возвращает секцию where для запроса select
public function getSelectWhere($params=Array()) {
	$result='';
	if ($params['filter.id']!='') {
		if ($this->isGUID) {
			$value=$this->guid2Sql($params['filter.id']);
		}
		else {
			$value=$this->php2Sql($params['filter.id']);
		}
		$result.="\n"."and `sysfield`.id='{$value}'";
	}
	if ($params['filter.id.tmptable']!='') {
		$value=$this->php2Sql($params['filter.id.tmptable']);
		$result.="\n"."and `sysfield`.id in (select value from tmptablelist where tmptablelist.list='{$value}')";
	}
	if ($params['filter.klssystable']!='') {
		$value=$this->php2Sql($params['filter.klssystable']);
		$result.="\n"."and `sysfield`.`klssystable`='{$value}'";
	}
	if ($params['filter.klssystable.tmptable']!='') {
		$value=$this->php2Sql($params['filter.klssystable.tmptable']);
		$result.="\n"."and `sysfield`.`klssystable` in (select value from tmptablelist where tmptablelist.list='{$value}')";
	}
	if ($params['filter.isnotempty']!='') {
		$value=$this->php2Sql($params['filter.isnotempty']);
		$result.="\n"."and `sysfield`.`isnotempty`='{$value}'";
	}
	if ($params['filter.ismain']!='') {
		$value=$this->php2Sql($params['filter.ismain']);
		$result.="\n"."and `sysfield`.`ismain`='{$value}'";
	}
	if ($params['filter.isstretch']!='') {
		$value=$this->php2Sql($params['filter.isstretch']);
		$result.="\n"."and `sysfield`.`isstretch`='{$value}'";
	}
	if ($params['filter.klssysfieldtype']!='') {
		$value=$this->php2Sql($params['filter.klssysfieldtype']);
		$result.="\n"."and `sysfield`.`klssysfieldtype`='{$value}'";
	}
	if ($params['filter.klssysfieldtype.tmptable']!='') {
		$value=$this->php2Sql($params['filter.klssysfieldtype.tmptable']);
		$result.="\n"."and `sysfield`.`klssysfieldtype` in (select value from tmptablelist where tmptablelist.list='{$value}')";
	}
	if ($params['filter.klsreftable']!='') {
		$value=$this->php2Sql($params['filter.klsreftable']);
		$result.="\n"."and `sysfield`.`klsreftable`='{$value}'";
	}
	if ($params['filter.klsreftable.tmptable']!='') {
		$value=$this->php2Sql($params['filter.klsreftable.tmptable']);
		$result.="\n"."and `sysfield`.`klsreftable` in (select value from tmptablelist where tmptablelist.list='{$value}')";
	}
	if ($params['filter.isrefrestrict']!='') {
		$value=$this->php2Sql($params['filter.isrefrestrict']);
		$result.="\n"."and `sysfield`.`isrefrestrict`='{$value}'";
	}
	if ($params['filter.isrefcascade']!='') {
		$value=$this->php2Sql($params['filter.isrefcascade']);
		$result.="\n"."and `sysfield`.`isrefcascade`='{$value}'";
	}
	if ($params['filter.isrefclear']!='') {
		$value=$this->php2Sql($params['filter.isrefclear']);
		$result.="\n"."and `sysfield`.`isrefclear`='{$value}'";
	}
	if ($params['filter.isref121']!='') {
		$value=$this->php2Sql($params['filter.isref121']);
		$result.="\n"."and `sysfield`.`isref121`='{$value}'";
	}
	return $result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy($params=Array()) {
	return <<<SQL
sysfield.klssystable, sysfield.ord, sysfield.id
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFields($params=Array()) {
	$result=<<<XML
<fields>
<field name="klssystable" type="ref" caption="Ссылка на родительскую таблицу" notnull="1">
	<ref datasource="systable"/>
</field>
<field name="fieldname" type="string" caption="Поле" notnull="1" len="15" maxlength="255"/>
<field name="name" type="string" caption="Описание поля" stretch="1" len="25" maxlength="255"/>
<field name="isnotempty" type="check" caption="Не пусто" len="5"/>
<field name="ismain" type="check" caption="Main" len="5"/>
<field name="isstretch" type="check" caption="Stretch" len="5"/>
<field name="klssysfieldtype" type="ref" caption="Ссылка на тип поля">
	<ref datasource="sysfieldtype"/>
</field>
<field name="maxlength" type="num" caption="Максимальная длина" len="5"/>
<field name="len" type="num" caption="Длина" len="5"/>
<field name="dec" type="num" caption="После запятой" len="5"/>
<field name="klsreftable" type="ref" caption="Ссылка на связанную таблицу">
	<ref datasource="systable"/>
</field>
<field name="reflink" type="string" caption="Имя ссылки" len="15" maxlength="255"/>
<field name="isrefrestrict" type="check" caption="Restrict связь" len="5"/>
<field name="isrefcascade" type="check" caption="Cascade связь" len="5"/>
<field name="isrefclear" type="check" caption="Clear связь" len="5"/>
<field name="isref121" type="check" caption="1 к 1 связь" len="5"/>
<field name="ord" type="num" caption="№пп" len="5"/>
<field name="systable_tablename" type="string" caption="Таблица" notnull="1" len="12" maxlength="255" refid="klssystable" refname="tablename"/>
<field name="systable_name" type="string" caption="Описание" len="25" maxlength="255" refid="klssystable" refname="name"/>
<field name="sysfieldtype_name" type="string" caption="Тип" notnull="1" len="12" maxlength="255" refid="klssysfieldtype" refname="name"/>
<field name="sysfieldtype_g740type" type="string" caption="Тип в g740" len="15" maxlength="255" refid="klssysfieldtype" refname="g740type"/>
<field name="reftable_tablename" type="string" caption="Таблица" notnull="1" len="12" maxlength="255" refid="klsreftable" refname="tablename"/>
<field name="reftable_name" type="string" caption="Описание" len="25" maxlength="255" refid="klsreftable" refname="name"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Sysfield();
?>