<?php
/**
 * @file
 * G740Server, контроллер прав
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
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
/** В течении одного сеанса работать от имени пользователя root
 *
 * @return	Boolean	успешность
 */
function execConnectAsRoot() {
	$obj=getPermController();
	if (!$obj) return false;
	return $obj->execConnectAsRoot();
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
		if (!getPP('isconnected')) return false;
		if ($mode=='readonly') {
			if ($operation=='read') return true;
			return false;
		}
		if (getPP('isroot')) return true;
		if ($mode=='connected') return true;
		if ($mode=='root') {
			return false;
		}
		if ($mode=='adm') {
			if (getPP('isadm')) return true;
			return false;
		}
		if ($mode=='rootref') {
			if ($operation=='read') return true;
			return false;
		}
		if ($mode=='admref') {
			if ($operation=='read') return true;
			if (getPP('isadm')) return true;
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
		session_start();
		try {
			if ($login=='root' && $password=='1') {
				$_SESSION['connect_isconnected']=true;
				$_SESSION['connect_isroot']=true;
				$_SESSION['connect_login']='root';
				$this->onAfterConnection();
				return true;
			}
			if ($login=='admin' && $password=='1') {
				$_SESSION['connect_isconnected']=true;
				$_SESSION['connect_isadm']=true;
				$_SESSION['connect_login']='admin';
				$this->onAfterConnection();
				return true;
			}
		}
		finally {
			if (getCfg('csrftoken.enabled')) $_SESSION['connect_csrftoken']=getGUID();
			session_write_close();
		}
		return false;
	}
/** Сбросить аутентифицированного пользователя
 *
 * @return	Boolean	успешность
 */
	public function execDisconnect() {
		session_start();
		try {
			$lstClear=Array();
			foreach($_SESSION as $name=>$value) {
				if (substr($name, 0, strlen('connect_'))=='connect_') $lstClear[]=$name;
			}
			foreach($lstClear as $name) unset($_SESSION[$name]);
		}
		finally {
			session_write_close();
		}
		return true;
	}
/** В течении одного сеанса работать от имени пользователя root
 */
	public function execConnectAsRoot() {
		$this->setPP('login', getCfg('root.fio','madmin'), true);
		$this->setPP('userid', getCfg('root.id',-999), true);
		$this->setPP('isconnected', true , true);
		$this->setPP('isroot', true , true);
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
		if (array_key_exists($name, $this->localPP)) $result=$this->localPP[$name];
		return $result;
		return $result;
	}
/** Сохранить параметр аутентифицированного пользователя
 *
 * @param	String	$name имя параметра
 * @param	String	$value значение параметра
 * @param	Boolean	$isLocal сохранить локально, только на время текущего сеанса
 * @return	Boolean	успешность
 */
	public function setPP($name, $value, $isLocal=false) {
		if ($isLocal) {
			$this->localPP[$name]=$value;
		}
		else {
			$_SESSION["connect_{$name}"]=$value;
		}
		return true;
	}
/** Событие, вызываемое посля каждого успешного соединения, используется для выполнения плановых работ
 *
 */
	protected function onAfterConnection() {
		deleteOldLogFiles();
	}
/// Локальные переменные прав, которые действуют на один сеанс, и не должны быть сохранены в сессии
	protected $localPP=Array();
}

/** Получить актуальный контроллер прав
 *
 * @return	PermController объект контроллера прав
 */
function getPermController() {
	global $_objPermController;
	if ($_objPermController instanceof PermController) return $_objPermController;
	
	$fileNamePermController=pathConcat(getCfg('path.root'), getCfg('path.root.php'), 'perm.php');
	if (file_exists($fileNamePermController)) {
		$_objPermController=include_once($fileNamePermController);
		if ($_objPermController instanceof PermController) return $_objPermController;
	}
	$_objPermController=new PermController();
	return $_objPermController;
}
/// Актуальный контроллер прав
$_objPermController=null;
