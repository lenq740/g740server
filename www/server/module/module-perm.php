<?php
/**
Система проверки прав
@package module
@subpackage module-perm
*/

/**
Проверить допустимость выполнения операции по правам
@param	String	$mode
@param	String	$operation
@return	Boolean допустимость выполнения операции по правам
*/
function getPerm($mode, $operation) {
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
/**
Залогиниться
@param	moxed[]	$params
<li>	para['login']
<li>	para['password']
@return	Boolean успешность
*/
function execConnect($params) {
	execDisconnect();
	if ($params['login']=='root' && $params['password']=='1') {
		$_SESSION['connect_ok']=true;
		$_SESSION['connect_sys']=true;
		$_SESSION['connect_login']='root';
		return true;
	}
	if ($params['login']=='admin' && $params['password']=='1') {
		$_SESSION['connect_ok']=true;
		$_SESSION['connect_adm']=true;
		$_SESSION['connect_login']='admin';
		return true;
	}
	return false;
}
/**
Отлогиниться
@param	moxed[]	$params
<li>	para['login']
<li>	para['password']
@return	Boolean успешность
*/
function execDisconnect() {
	unset($_SESSION['connect_ok']);
	unset($_SESSION['connect_sys']);
	unset($_SESSION['connect_adm']);
	unset($_SESSION['connect_login']);
	return true;
}
?>