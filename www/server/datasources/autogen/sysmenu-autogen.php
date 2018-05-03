<?php
class DataSource_Sysmenu extends DataSource {
// Инициализация констант
function __construct() {
	$this->tableName='sysmenu';
	$this->tableCaption='Главное меню системы';
	$this->permMode='sysref';
	$this->isGUID=false;
	$this->selectOtherFields=<<<SQL
case when exists(select * from sysmenu child where child.klsparent=sysmenu.id)  then 0 else 1 end as row_empty,
"menuitem" as row_type,
sysmenu.icon as row_icon

SQL;
}
// Тут описываются поля источника данных
protected function initFields() {
	$result=Array();
	{	// klsparent - Ссылка на родителя
		$fld=Array();
		$fld['name']='klsparent';
		$fld['type']='ref';
		$fld['caption']='Ссылка на родителя';
		$fld['reftable']='sysmenu';
		$fld['refalias']='sysmenu_1';
		$result[]=$fld;
	}
	{	// name - Пункт меню
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Пункт меню';
		$fld['maxlength']='255';
		$fld['len']='25';
		$result[]=$fld;
	}
	{	// form - Экранная форма
		$fld=Array();
		$fld['name']='form';
		$fld['type']='string';
		$fld['caption']='Экранная форма';
		$fld['maxlength']='255';
		$fld['len']='15';
		$result[]=$fld;
	}
	{	// icon - Иконка
		$fld=Array();
		$fld['name']='icon';
		$fld['type']='string';
		$fld['caption']='Иконка';
		$fld['maxlength']='255';
		$fld['len']='10';
		$result[]=$fld;
	}
	{	// params - Параметры вызова
		$fld=Array();
		$fld['name']='params';
		$fld['type']='memo';
		$fld['caption']='Параметры вызова';
		$fld['stretch']='1';
		$result[]=$fld;
	}
	{	// permmode - Права, режим
		$fld=Array();
		$fld['name']='permmode';
		$fld['type']='string';
		$fld['caption']='Права, режим';
		$fld['maxlength']='255';
		$fld['len']='10';
		$fld['stretch']='1';
		$result[]=$fld;
	}
	{	// permoper - Права, операция
		$fld=Array();
		$fld['name']='permoper';
		$fld['type']='string';
		$fld['caption']='Права, операция';
		$fld['maxlength']='255';
		$fld['len']='10';
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
	{	// sysmenu_1_name - Пункт меню
		$fld=Array();
		$fld['name']='sysmenu_1_name';
		$fld['fieldname']='name';
		$fld['alias']='sysmenu_1';
		$fld['type']='string';
		$fld['caption']='Пункт меню';
		$fld['maxlength']='255';
		$fld['len']='25';
		$fld['refname']='name';
		$fld['refid']='klsparent';
		$result[]=$fld;
	}
	return $result;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
protected function initReferences() {
	$result=Array();
	{	//  sysmenu.id -> sysmenu.klsparent
		$ref=Array();
		$ref['mode']='cascade';
		$ref['from.table']='sysmenu';
		$ref['from.field']='id';
		$ref['to.table']='sysmenu';
		$ref['to.field']='klsparent';
		$result['sysmenu.klsparent']=$ref;
	}
	return $result;
}
// Этот метод возвращает список полей для запроса select
public function getSelectFields($params=Array()) {
	$result=<<<SQL
	`sysmenu`.*,
	`sysmenu_1`.`name` as `sysmenu_1_name`,
case when exists(select * from sysmenu child where child.klsparent=sysmenu.id)  then 0 else 1 end as row_empty,
"menuitem" as row_type,
sysmenu.icon as row_icon

SQL;
	return $result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom($params=Array()) {
	$result=<<<SQL
	`sysmenu`
		left join `sysmenu` `sysmenu_1` on `sysmenu_1`.id=`sysmenu`.`klsparent`
SQL;
	return $result;
}
// Этот метод Этот метод возвращает секцию where для запроса select
public function getSelectWhere($params=Array()) {
	$result='';
	if (isset($params['filter.id'])) {
		$value=$this->php2SqlIn($params['filter.id']);
		if ($value!='') $result.="\n"."and `sysmenu`.id in ({$value})";
	}
	if (isset($params['filter.klsparent'])) {
		$value=$this->php2SqlIn($params['filter.klsparent']);
		if ($value!='') $result.="\n"."and `sysmenu`.`klsparent` in ({$value})";
	}
	return $result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy($params=Array()) {
	return <<<SQL
sysmenu.klsparent, sysmenu.ord, sysmenu.id
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFieldsDemo($params=Array()) {
	$result=<<<XML
<fields>
<field name="klsparent" type="ref" caption="Ссылка на родителя">
	<ref datasource="sysmenu"/>
</field>
<field name="name" type="string" caption="Пункт меню" len="25" maxlength="255"/>
<field name="form" type="string" caption="Экранная форма" len="15" maxlength="255"/>
<field name="icon" type="string" caption="Иконка" len="10" maxlength="255"/>
<field name="params" type="memo" caption="Параметры вызова" stretch="1"/>
<field name="permmode" type="string" caption="Права, режим" stretch="1" len="10" maxlength="255"/>
<field name="permoper" type="string" caption="Права, операция" stretch="1" len="10" maxlength="255"/>
<field name="ord" type="num" caption="№пп" len="5"/>
<field name="sysmenu_1_name" type="string" caption="Пункт меню" len="25" maxlength="255" refid="klsparent" refname="name"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Sysmenu();