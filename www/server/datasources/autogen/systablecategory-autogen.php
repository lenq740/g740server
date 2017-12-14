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
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
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
	if (isset($params['filter.id'])) {
		$value=$this->php2SqlIn($params['filter.id']);
		if ($value!='') $result.="\n"."and `systablecategory`.id in ({$value})";
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
<field name="name" type="string" caption="Категория" notnull="1" stretch="1" len="12" maxlength="255"/>
<field name="ord" type="num" caption="№пп" len="5"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Systablecategory();
?>