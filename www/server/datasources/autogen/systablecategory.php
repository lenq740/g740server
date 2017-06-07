<?php
class DataSource_Systablecategory extends DataSource {
// Инициализация констант
function __construct() {
	$this->tableName='systablecategory';
	$this->tableCaption='Категория таблицы';
	$this->permMode='sys';
	$this->isGUID=false;
}
// Тут описываются поля источника данных
public function getFields() {
	if ($this->fields) return $this->fields;
	$this->fields=Array();
	{	// name - Категория
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Категория';
		$fld['maxlength']='255';
		$fld['len']='12';
		$fld['notnull']='1';
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
	{	// systable.klssystablecategory --restrict--> systablecategory.id
		$ref=Array();
		$ref['mode']='restrict';
		$ref['from.table']='systable';
		$ref['from.field']='klssystablecategory';
		$ref['to.table']='systablecategory';
		$ref['to.field']='id';
		$result[]=$ref;
	}
	return $result;
}
// Этот метод возвращает список полей для запроса select
public function getSelectFields($params=Array()) {
	$result=<<<SQL
	`systablecategory`.*
SQL;
	return $result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom($params=Array()) {
	$result=<<<SQL
	`systablecategory`
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
		$result.="\n"."and `systablecategory`.id='{$value}'";
	}
	return $result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy($params=Array()) {
	return <<<SQL
systablecategory.ord, systablecategory.id
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFields($params=Array()) {
	$result=<<<XML
<fields>
<field name="name" type="string" caption="Категория" notnull="1" len="12" maxlength="255"/>
<field name="ord" type="num" caption="№пп" len="5"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Systablecategory();
?>