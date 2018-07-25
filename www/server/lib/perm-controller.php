<?php
/**
 * @file
 * Контроллер прав
 */
require_once('dsconnector.php');

/** Проверить доступность требуемой функциональности по правам
 *
 * @param	String	$permMode режим по правам
 * @param	String	$permOperation операция
 * @return	Boolean доступность требуемой функциональности
 */
function getPerm($permMode, $permOperation='') {
	$obj=getPermController();
	$result=false;
	if ($obj) $result=$obj->getPerm($permMode, $permOperation);
	return $result;
}
/** Выполнить аутентификацию пользователя
 *
 * @param	String	$login
 * @param	String	$password
 * @return	Boolean	успешность аутентификации
 */
function execConnect($login, $password) {
	$obj=getPermController();
	if (!$obj) return false;
	return $obj->execConnect($login, $password);
}
/** Сбросить аутентифицированного пользователя
 *
 * @return	Boolean	успешность
 */
function execDisconnect() {
	$obj=getPermController();
	if (!$obj) return false;
	return $obj->execDisconnect();
}
/** Получить параметр, сохраненный для аутентифицированного пользователя
 *
 * @param	String	$name имя параметра
 * @param	String	$default значение по умолчанию
 * @return	String	значение параметра
 */
function getPP($name, $default='') {
	$obj=getPermController();
	if (!$obj) return $default;
	return $obj->getPP($name, $default);
}

/** Класс контроллер прав
 */
class PermController extends DSConnector{
/// Конструктор, регистрация экземпляра класса
	function __construct() {
		global $_objPermController;
		$_objPermController=$this;
	}
/** Проверить доступность требуемой функциональности по правам
 *
 * @param	String	$mode режим по правам
 * @param	String	$operation операция
 * @return	Boolean доступность требуемой функциональности
 */
	public function getPerm($mode, $operation) {
		if (!getPP('ok')) return false;
		if (getPP('sys')) return true;
		if ($mode=='connected') return true;
		if ($mode=='sys') {
			return false;
		}
		if ($mode=='adm') {
			if (getPP('adm')) return true;
			return false;
		}
		if ($mode=='sysref') {
			if ($operation=='read') return true;
			return false;
		}
		if ($mode=='admref') {
			if ($operation=='read') return true;
			if (getPP('adm')) return true;
			return false;
		}
		return false;
	}
/** Выполнить аутентификацию пользователя
 *
 * @param	String	$login
 * @param	String	$password
 * @return	Boolean	успешность аутентификации
 */
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
/** Сбросить аутентифицированного пользователя
 *
 * @return	Boolean	успешность
 */
	public function execDisconnect() {
		$lstClear=Array();
		foreach($_SESSION as $name=>$value) {
			if (substr($name, 0, strlen('connect_'))=='connect_') $lstClear[]=$name;
		}
		foreach($lstClear as $name) unset($_SESSION[$name]);
		return true;
	}
/** Получить параметр, сохраненный для аутентифицированного пользователя
 *
 * @param	String	$name имя параметра
 * @param	String	$default значение по умолчанию
 * @return	String	значение параметра
 */
	public function getPP($name, $default='') {
		$result=$default;
		if (array_key_exists("connect_{$name}", $_SESSION)) $result=$_SESSION["connect_{$name}"];
		return $result;
	}
}

/** Получить актуальный контроллер прав
 *
 * @return	PermController объект контроллера прав
 */
function getPermController() {
	global $_objPermController;
	if ($_objPermController instanceof PermController) return $_objPermController;
	
	$fileNamePermController=pathConcat(getCfg('path.root'), getCfg('path.root.module'), 'perm.php');
	if (file_exists($fileNamePermController)) {
		$_objPermController=include_once($fileNamePermController);
		if ($_objPermController instanceof PermController) return $_objPermController;
	}
	$_objPermController=new PermController();
	return $_objPermController;
}
/// Актуальный контроллер прав
$_objPermController=null;
