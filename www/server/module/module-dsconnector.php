<?php
/**
Класс, содержащий интерфейс доступа к базе данных для DataSource
@package module
@subpackage module-datasource
*/
class DSConnector {
	// Доступ к базе данных
	public function getPDO() {
		global $pdoDB;
		if (!$pdoDB) throw new Exception('Не установлено соединение с базой данных');
		return $pdoDB;
	}
	public function getDriverName() {
		$pdoDB=$this->getPDO();
		return $pdoDB->getDriverName();
	}
	public function str2Sql($str) {
		$pdoDB=$this->getPDO();
		return $pdoDB->str2Sql($str);
	}
	public function guid2Sql($str) {
		if (!$str) $str='00000000-0000-0000-0000-000000000000';
		if (mb_strlen($str)!=36) $str='00000000-0000-0000-0000-000000000000';
		return $this->str2Sql($str);
	}
	public function php2Sql($value) {
		$pdoDB=$this->getPDO();
		return $pdoDB->php2Sql($value);
	}
	public function pdo($sql, $errorMessage='', $params=Array()) {
		$pdoDB=$this->getPDO();
		return $pdoDB->pdo($sql, $errorMessage, $params);
	}
	public function pdoFetch($sql, $errorMessage='', $params=Array()) {
		$pdoDB=$this->getPDO();
		return $pdoDB->pdoFetch($sql, $errorMessage, $params);
	}
	public function pdoRowCount() {
		$pdoDB=$this->getPDO();
		return $pdoDB->rowCount();
	}
}
?>