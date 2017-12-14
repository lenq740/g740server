<?php
/**
Система проверки прав
@package module-lib
@subpackage module-perm
*/

class PermControllerPrj extends PermController {
	public function getPerm($mode, $operation) {
		$result=parent::getPerm($mode, $operation);
		return $result;
	}
	public function execConnect($login, $password) {
		$result=parent::execConnect($login, $password);
		return $result;
	}
	public function execDisconnect() {
		$result=parent::execDisconnect();
		return $result;
	}
}
$objPermController=new PermControllerPrj();
?>