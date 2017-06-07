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
function getPerm($mode, $operation, $params=Array()) {
	if (!$_SESSION['connect_ok']) return false;
	if ($_SESSION['connect_okroot']) return true;
	if ($mode=='connected') return true;
	if ($mode=='sys') {
		if ($_SESSION['connect_okroot']) return true;
		return false;
	}
	if ($mode=='sysref') {
		if ($operation=='read') return true;
		return false;
	}
	if ($mode=='adm') {
		if ($_SESSION['connect_okadm']) return true;
		return false;
	}
	if ($mode=='admref') {
		if ($_SESSION['connect_okadm'] && $operation=='read') return true;
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
	if ($params['login']=='root' && $params['password']=='740') {
		$_SESSION['connect_ok']=true;
		$_SESSION['connect_okroot']=true;
	}
	if ($params['login']=='admin' && $params['password']=='1') {
		$_SESSION['connect_ok']=true;
		$_SESSION['connect_okadmin']=true;
	}
	return true;
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
	unset($_SESSION['connect_okroot']);
	unset($_SESSION['connect_okadmin']);
	return true;
}
?>