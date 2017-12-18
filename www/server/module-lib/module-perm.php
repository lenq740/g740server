<?php
/**
Система проверки прав
@package module-lib
@subpackage module-perm
*/

/**
Проверить доступность требуемой функциональности по правам
@param	String	$mode исходная строка
@param	String	$operation исходная строка
@return	Boolean доступность требуемой функциональности
*/
function getPerm($mode, $operation) {
	global $_objPermController;
	if (!$_objPermController) return false;
	if (!($_objPermController instanceof PermController)) return false;
	return $_objPermController->getPerm($mode, $operation);
}
/**
Выполнить аутентификацию пользователя
@param	String	$login
@param	String	$password
@return	Boolean	успешность аутентификации
*/
function execConnect($login, $password) {
	global $_objPermController;
	if (!$_objPermController) return false;
	if (!($_objPermController instanceof PermController)) return false;
	return $_objPermController->execConnect($login, $password);
}
/**
Сбросить аутентифицированного пользователя
@return	Boolean	успешность
*/
function execDisconnect() {
	global $_objPermController;
	if (!$_objPermController) return false;
	if (!($_objPermController instanceof PermController)) return false;
	return $_objPermController->execDisconnect();
}
/**
Получить параметр, сохраненный для аутентифицированного пользователя
@param	String	$name имя параметра
@param	String	$default значение по умолчанию
@return	String	значение параметра
*/
function getPP($name, $default='') {
	if (array_key_exists("connect_{$name}", $_SESSION)) return $_SESSION["connect_{$name}"];
	return $default;
}

/**
Класс контроллер прав
@package module-lib
@subpackage module-perm
*/
class PermController {
	function __construct() {
		global $_objPermController;
		$_objPermController=$this;
	}
	public function getPerm($mode, $operation) {
		if (!$_SESSION['connect_ok']) return false;
		if ($_SESSION['connect_sys']) return true;
		if ($mode=='connected') return true;
		if ($mode=='sys') {
			return false;
		}
		if ($mode=='adm') {
			if ($_SESSION['connect_adm']) return true;
			return false;
		}
		if ($mode=='sysref') {
			if ($operation=='read') return true;
			return false;
		}
		if ($mode=='admref') {
			if ($operation=='read') return true;
			if ($_SESSION['connect_adm']) return true;
			return false;
		}
		return false;
	}
	public function execConnect($login, $password) {
		$this->execDisconnect();
		if ($login=='root' && $password=='1') {
			$_SESSION['connect_ok']=true;
			$_SESSION['connect_sys']=true;
			$_SESSION['connect_login']='root';
			return true;
		}
		if ($login=='admin' && $password=='1') {
			$_SESSION['connect_ok']=true;
			$_SESSION['connect_adm']=true;
			$_SESSION['connect_login']='admin';
			return true;
		}
		return false;
	}
	public function execDisconnect() {
		$lstClear=Array();
		foreach($_SESSION as $name=>$value) {
			if (substr($name, 0, strlen('connect_'))=='connect_') $lstClear[]=$name;
		}
		foreach($lstClear as $name) unset($_SESSION[$name]);
		return true;
	}
}
$_objPermController=null;
return new PermController();
