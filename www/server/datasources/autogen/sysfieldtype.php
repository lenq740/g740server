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
public function getFields() {
	if ($this->fields) return $this->fields;
	$this->fields=Array();
	{	// name - Тип
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Тип';
		$fld['maxlength']='255';
		$fld['len']='12';
		$fld['notnull']='1';
		$this->fields[]=$fld;
	}
	{	// isid - id
		$fld=Array();
		$fld['name']='isid';
		$fld['type']='check';
		$fld['caption']='id';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// isref - Ссылка
		$fld=Array();
		$fld['name']='isref';
		$fld['type']='check';
		$fld['caption']='Ссылка';
		$fld['len']='6';
		$this->fields[]=$fld;
	}
	{	// isdec - Число
		$fld=Array();
		$fld['name']='isdec';
		$fld['type']='check';
		$fld['caption']='Число';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// isstr - Строка
		$fld=Array();
		$fld['name']='isstr';
		$fld['type']='check';
		$fld['caption']='Строка';
		$fld['len']='6';
		$this->fields[]=$fld;
	}
	{	// isdat - Дата
		$fld=Array();
		$fld['name']='isdat';
		$fld['type']='check';
		$fld['caption']='Дата';
		$fld['len']='5';
		$this->fields[]=$fld;
	}
	{	// defvalue - Значение по умолчанию
		$fld=Array();
		$fld['name']='defvalue';
		$fld['type']='string';
		$fld['caption']='Значение по умолчанию';
		$fld['maxlength']='255';
		$fld['len']='15';
		$this->fields[]=$fld;
	}
	{	// jstype - Тип в JavaScript
		$fld=Array();
		$fld['name']='jstype';
		$fld['type']='string';
		$fld['caption']='Тип в JavaScript';
		$fld['maxlength']='255';
		$fld['len']='15';
		$this->fields[]=$fld;
	}
	{	// g740type - Тип в g740
		$fld=Array();
		$fld['name']='g740type';
		$fld['type']='string';
		$fld['caption']='Тип в g740';
		$fld['maxlength']='255';
		$fld['len']='15';
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
	return $this->fields;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
public function getReferences() {
	$result=Array();
	{	// sysfield.klssysfieldtype --restrict--> sysfieldtype.id
		$ref=Array();
		$ref['mode']='restrict';
		$ref['from.table']='sysfield';
		$ref['from.field']='klssysfieldtype';
		$ref['to.table']='sysfieldtype';
		$ref['to.field']='id';
		$result[]=$ref;
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
	if (array_key_exists('filter.id', $params)) {
		if ($this->isGUID) {
			$value=$this->guid2Sql($params['filter.id']);
		}
		else {
			$value=$this->php2Sql($params['filter.id']);
		}
		$result.="\n"."and `sysfieldtype`.id='{$value}'";
	}
	if ($params['filter.id.tmptable']!='') {
		$value=$this->php2Sql($params['filter.id.tmptable']);
		$result.="\n"."and `sysfieldtype`.id in (select value from tmptablelist where tmptablelist.list='{$value}')";
	}
	if (isset($params['filter.isid'])) {
		$value=$this->php2Sql($params['filter.isid']);
		$result.="\n"."and `sysfieldtype`.`isid`='{$value}'";
	}
	if (isset($params['filter.isref'])) {
		$value=$this->php2Sql($params['filter.isref']);
		$result.="\n"."and `sysfieldtype`.`isref`='{$value}'";
	}
	if (isset($params['filter.isdec'])) {
		$value=$this->php2Sql($params['filter.isdec']);
		$result.="\n"."and `sysfieldtype`.`isdec`='{$value}'";
	}
	if (isset($params['filter.isstr'])) {
		$value=$this->php2Sql($params['filter.isstr']);
		$result.="\n"."and `sysfieldtype`.`isstr`='{$value}'";
	}
	if (isset($params['filter.isdat'])) {
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
public function getStrXmlDefinitionFields($params=Array()) {
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
?>