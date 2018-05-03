<?php
class DataSource_Sysfieldtype extends DataSource {
// Инициализация констант
function __construct() {
	$this->tableName='sysfieldtype';
	$this->tableCaption='Тип поля';
	$this->permMode='sys';
	$this->isGUID=false;
}
// Тут описываются поля источника данных
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
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
protected function initReferences() {
	$result=Array();
	{	//  sysfieldtype.id -> sysfield.klssysfieldtype
		$ref=Array();
		$ref['mode']='restrict';
		$ref['from.table']='sysfieldtype';
		$ref['from.field']='id';
		$ref['to.table']='sysfield';
		$ref['to.field']='klssysfieldtype';
		$result['sysfield.klssysfieldtype']=$ref;
	}
	return $result;
}
// Этот метод возвращает список полей для запроса select
public function getSelectFields($params=Array()) {
	$result=<<<SQL
	`sysfieldtype`.*
SQL;
	return $result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom($params=Array()) {
	$result=<<<SQL
	`sysfieldtype`
SQL;
	return $result;
}
// Этот метод Этот метод возвращает секцию where для запроса select
public function getSelectWhere($params=Array()) {
	$result='';
	if (isset($params['filter.id'])) {
		$value=$this->php2SqlIn($params['filter.id']);
		if ($value!='') $result.="\n"."and `sysfieldtype`.id in ({$value})";
	}
	if ($params['filter.isid']!='') {
		$value=$this->php2Sql($params['filter.isid']);
		$result.="\n"."and `sysfieldtype`.`isid`='{$value}'";
	}
	if ($params['filter.isref']!='') {
		$value=$this->php2Sql($params['filter.isref']);
		$result.="\n"."and `sysfieldtype`.`isref`='{$value}'";
	}
	if ($params['filter.isdec']!='') {
		$value=$this->php2Sql($params['filter.isdec']);
		$result.="\n"."and `sysfieldtype`.`isdec`='{$value}'";
	}
	if ($params['filter.isstr']!='') {
		$value=$this->php2Sql($params['filter.isstr']);
		$result.="\n"."and `sysfieldtype`.`isstr`='{$value}'";
	}
	if ($params['filter.isdat']!='') {
		$value=$this->php2Sql($params['filter.isdat']);
		$result.="\n"."and `sysfieldtype`.`isdat`='{$value}'";
	}
	return $result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy($params=Array()) {
	return <<<SQL
sysfieldtype.ord, sysfieldtype.id
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFieldsDemo($params=Array()) {
	$result=<<<XML
<fields>
<field name="name" type="string" caption="Тип" notnull="1" len="12" maxlength="255"/>
<field name="isid" type="check" caption="id" len="5"/>
<field name="isref" type="check" caption="Ссылка" len="6"/>
<field name="isdec" type="check" caption="Число" len="5"/>
<field name="isstr" type="check" caption="Строка" len="6"/>
<field name="isdat" type="check" caption="Дата" len="5"/>
<field name="defvalue" type="string" caption="Значение по умолчанию" len="15" maxlength="255"/>
<field name="jstype" type="string" caption="Тип в JavaScript" len="15" maxlength="255"/>
<field name="g740type" type="string" caption="Тип в g740" len="15" maxlength="255"/>
<field name="ord" type="num" caption="№пп" len="5"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Sysfieldtype();