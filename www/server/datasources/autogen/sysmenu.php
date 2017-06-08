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
public function getFields() {
	if ($this->fields) return $this->fields;
	$this->fields=Array();
	{	// klsparent - Ссылка на родителя
		$fld=Array();
		$fld['name']='klsparent';
		$fld['type']='ref';
		$fld['caption']='Ссылка на родителя';
		$fld['reftable']='sysmenu';
		$fld['refalias']='sysmenu_1';
		$this->fields[]=$fld;
	}
	{	// name - Пункт меню
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Пункт меню';
		$fld['maxlength']='255';
		$fld['len']='25';
		$fld['notnull']='1';
		$this->fields[]=$fld;
	}
	{	// form - Экранная форма
		$fld=Array();
		$fld['name']='form';
		$fld['type']='string';
		$fld['caption']='Экранная форма';
		$fld['maxlength']='255';
		$fld['len']='15';
		$this->fields[]=$fld;
	}
	{	// icon - Иконка
		$fld=Array();
		$fld['name']='icon';
		$fld['type']='string';
		$fld['caption']='Иконка';
		$fld['maxlength']='255';
		$fld['len']='10';
		$this->fields[]=$fld;
	}
	{	// params - Параметры вызова
		$fld=Array();
		$fld['name']='params';
		$fld['type']='memo';
		$fld['caption']='Параметры вызова';
		$fld['stretch']='1';
		$this->fields[]=$fld;
	}
	{	// permmode - Права, режим
		$fld=Array();
		$fld['name']='permmode';
		$fld['type']='string';
		$fld['caption']='Права, режим';
		$fld['maxlength']='255';
		$fld['len']='10';
		$this->fields[]=$fld;
	}
	{	// permoper - Права, операция
		$fld=Array();
		$fld['name']='permoper';
		$fld['type']='string';
		$fld['caption']='Права, операция';
		$fld['maxlength']='255';
		$fld['len']='10';
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
	{	// sysmenu_1_name - Пункт меню
		$fld=Array();
		$fld['name']='sysmenu_1_name';
		$fld['fieldname']='name';
		$fld['alias']='sysmenu_1';
		$fld['type']='string';
		$fld['caption']='Пункт меню';
		$fld['maxlength']='255';
		$fld['len']='25';
		$fld['notnull']='1';
		$fld['refname']='name';
		$fld['refid']='klsparent';
		$this->fields[]=$fld;
	}
	return $this->fields;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
public function getReferences() {
	$result=Array();
	{	// sysmenu.id --cascade--> sysmenu.klsparent
		$ref=Array();
		$ref['mode']='cascade';
		$ref['from.table']='sysmenu';
		$ref['from.field']='id';
		$ref['to.table']='sysmenu';
		$ref['to.field']='klsparent';
		$result[]=$ref;
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
	if (array_key_exists('filter.id', $params)) {
		if ($this->isGUID) {
			$value=$this->guid2Sql($params['filter.id']);
		}
		else {
			$value=$this->php2Sql($params['filter.id']);
		}
		$result.="\n"."and `sysmenu`.id='{$value}'";
	}
	if ($params['filter.id.tmptable']!='') {
		$value=$this->php2Sql($params['filter.id.tmptable']);
		$result.="\n"."and `sysmenu`.id in (select value from tmptablelist where tmptablelist.list='{$value}')";
	}
	if ($params['filter.klsparent']!='') {
		$value=$this->php2Sql($params['filter.klsparent']);
		$result.="\n"."and `sysmenu`.`klsparent`='{$value}'";
	}
	if ($params['filter.klsparent.tmptable']!='') {
		$value=$this->php2Sql($params['filter.klsparent.tmptable']);
		$result.="\n"."and `sysmenu`.`klsparent` in (select value from tmptablelist where tmptablelist.list='{$value}')";
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
public function getStrXmlDefinitionFields($params=Array()) {
	$result=<<<XML
<fields>
<field name="klsparent" type="ref" caption="Ссылка на родителя">
	<ref datasource="sysmenu"/>
</field>
<field name="name" type="string" caption="Пункт меню" notnull="1" len="25" maxlength="255"/>
<field name="form" type="string" caption="Экранная форма" len="15" maxlength="255"/>
<field name="icon" type="string" caption="Иконка" len="10" maxlength="255"/>
<field name="params" type="memo" caption="Параметры вызова" stretch="1"/>
<field name="permmode" type="string" caption="Права, режим" len="10" maxlength="255"/>
<field name="permoper" type="string" caption="Права, операция" len="10" maxlength="255"/>
<field name="ord" type="num" caption="№пп" len="5"/>
<field name="sysmenu_1_name" type="string" caption="Пункт меню" notnull="1" len="25" maxlength="255" refid="klsparent" refname="name"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Sysmenu();
?>