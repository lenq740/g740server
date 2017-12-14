<?php
class DataSource_Sysfieldparams extends DataSource {
// Инициализация констант
function __construct() {
	$this->tableName='sysfieldparams';
	$this->tableCaption='Параметр поля';
	$this->permMode='sys';
	$this->isGUID=false;
}
// Тут описываются поля источника данных
protected function initFields() {
	$result=Array();
	{	// klssysfield - Ссылка на поле
		$fld=Array();
		$fld['name']='klssysfield';
		$fld['type']='ref';
		$fld['caption']='Ссылка на поле';
		$fld['notnull']='1';
		$fld['reftable']='sysfield';
		$result[]=$fld;
	}
	{	// name - Параметр
		$fld=Array();
		$fld['name']='name';
		$fld['type']='string';
		$fld['caption']='Параметр';
		$fld['maxlength']='255';
		$fld['len']='15';
		$fld['notnull']='1';
		$result[]=$fld;
	}
	{	// val - Значение
		$fld=Array();
		$fld['name']='val';
		$fld['type']='memo';
		$fld['caption']='Значение';
		$fld['len']='65';
		$result[]=$fld;
	}
	{	// sysfield_fieldname - Поле
		$fld=Array();
		$fld['name']='sysfield_fieldname';
		$fld['fieldname']='fieldname';
		$fld['table']='sysfield';
		$fld['type']='string';
		$fld['caption']='Поле';
		$fld['maxlength']='255';
		$fld['len']='15';
		$fld['notnull']='1';
		$fld['refname']='fieldname';
		$fld['refid']='klssysfield';
		$result[]=$fld;
	}
	{	// sysfield_name - Описание поля
		$fld=Array();
		$fld['name']='sysfield_name';
		$fld['fieldname']='name';
		$fld['table']='sysfield';
		$fld['type']='string';
		$fld['caption']='Описание поля';
		$fld['maxlength']='255';
		$fld['len']='25';
		$fld['stretch']='1';
		$fld['refname']='name';
		$fld['refid']='klssysfield';
		$result[]=$fld;
	}
	{	// sysfield_klssysfieldtype - Ссылка на тип поля
		$fld=Array();
		$fld['name']='sysfield_klssysfieldtype';
		$fld['fieldname']='klssysfieldtype';
		$fld['table']='sysfield';
		$fld['type']='ref';
		$fld['caption']='Ссылка на тип поля';
		$fld['reftable']='sysfieldtype';
		$fld['refname']='klssysfieldtype';
		$fld['refid']='klssysfield';
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
		$fld['refid']='sysfield_klssysfieldtype';
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
		$fld['refid']='sysfield_klssysfieldtype';
		$result[]=$fld;
	}
	return $result;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
protected function initReferences() {
	$result=Array();
	{	//  sysfieldparams.klssysfield -> sysfield.id
		$ref=Array();
		$ref['mode']='cascade';
		$ref['from.table']='sysfieldparams';
		$ref['from.field']='klssysfield';
		$ref['to.table']='sysfield';
		$ref['to.field']='id';
		$result['sysfieldparams.klssysfield']=$ref;
	}
	return $result;
}
// Этот метод возвращает список полей для запроса select
public function getSelectFields($params=Array()) {
	$result=<<<SQL
	`sysfieldparams`.*,
	`sysfield`.`fieldname` as `sysfield_fieldname`,
	`sysfield`.`name` as `sysfield_name`,
	`sysfield`.`klssysfieldtype` as `sysfield_klssysfieldtype`,
	`sysfieldtype`.`name` as `sysfieldtype_name`,
	`sysfieldtype`.`g740type` as `sysfieldtype_g740type`
SQL;
	return $result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom($params=Array()) {
	$result=<<<SQL
	`sysfieldparams`
		left join `sysfield` on `sysfield`.id=`sysfieldparams`.`klssysfield`
		left join `sysfieldtype` on `sysfieldtype`.id=`sysfield`.`klssysfieldtype`
SQL;
	return $result;
}
// Этот метод Этот метод возвращает секцию where для запроса select
public function getSelectWhere($params=Array()) {
	$result='';
	if (isset($params['filter.id'])) {
		$value=$this->php2SqlIn($params['filter.id']);
		if ($value!='') $result.="\n"."and `sysfieldparams`.id in ({$value})";
	}
	if (isset($params['filter.klssysfield'])) {
		$value=$this->php2SqlIn($params['filter.klssysfield']);
		if ($value!='') $result.="\n"."and `sysfieldparams`.`klssysfield` in ({$value})";
	}
	return $result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy($params=Array()) {
	return <<<SQL
sysfieldparams.klssysfield, sysfieldparams.name
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFields($params=Array()) {
	$result=<<<XML
<fields>
<field name="klssysfield" type="ref" caption="Ссылка на поле" notnull="1">
	<ref datasource="sysfield"/>
</field>
<field name="name" type="string" caption="Параметр" notnull="1" len="15" maxlength="255"/>
<field name="val" type="memo" caption="Значение" len="65"/>
<field name="sysfield_fieldname" type="string" caption="Поле" notnull="1" len="15" maxlength="255" refid="klssysfield" refname="fieldname"/>
<field name="sysfield_name" type="string" caption="Описание поля" stretch="1" len="25" maxlength="255" refid="klssysfield" refname="name"/>
<field name="sysfield_klssysfieldtype" type="string" caption="Ссылка на тип поля" refid="klssysfield" refname="klssysfieldtype"/>
<field name="sysfieldtype_name" type="string" caption="Тип" notnull="1" len="12" maxlength="255" readonly="1"/>
<field name="sysfieldtype_g740type" type="string" caption="Тип в g740" len="15" maxlength="255" readonly="1"/>
</fields>
XML;
	return $result;
}
}
return new DataSource_Sysfieldparams();
?>