<?php
class DataSourceModify_Sysfield extends DataSourceModify {
	public function getFields($fields) {
		return $fields;
	}
	public function getSelectFields($selectFields) {
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
return new DataSourceModify_Sysfield();
?>