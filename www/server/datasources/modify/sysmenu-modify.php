<?php
class DataSourceModify_Sysmenu extends DataSourceModify {
	public function getFields($fields) {
		return $fields;
	}
	public function getSelectFields($selectFields) {
		$selectFields.=",\n".<<<SQL
case when exists(select * from sysmenu child where child.klsparent=sysmenu.id)  then 0 else 1 end as row_empty,
'menuitem' as row_type,
sysmenu.icon as row_icon
SQL;
		return $selectFields;
	}
	public function getSelectFrom($selectFrom) {
		return $selectFrom;
	}
	public function getSelectOrderBy($selectOrderBy) {
		return $selectOrderBy;
	}
	public function getRequests($requests) {
		return $requests;
	}
}
return new DataSourceModify_Sysmenu();