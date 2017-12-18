<?php
/**
Класс, содержащий интерфейс доступа к базе данных для DataSource
@package module-lib
@subpackage module-datasource
*/
class DSConnector {
	public $pdoName='default';
	// Доступ к базе данных
	public function getPDO() {
		return getPDO($this->pdoName);
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
	public function php2SqlIn($value) {
		$result='';
		if (is_array($value)) {
			foreach($value as $val) {
				if ($val=='') continue;
				if ($result) $result.=', ';
				$result.="'{$this->php2Sql($val)}'";
			}
			if (!$result) $result="''";
		}
		else if ($value!='') {
			$result.="'{$this->php2Sql($value)}'";
		}
		return $result;
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

/**
Получить объект соединения по имени
@param	String	$name имя соединения
@return	PDODataConnector объект соединения
*/
function getPDO($name='default') {
	global $_registerPDO;
	$result=$_registerPDO[$name];
	if (!$result) throw new Exception("Недопустимое имя соединения '{$name}'");
	return $result;
}
function regPDO($pdoDB, $name='default') {
	global $_registerPDO;
	$_registerPDO[$name]=$pdoDB;
}
$_registerPDO=Array();
