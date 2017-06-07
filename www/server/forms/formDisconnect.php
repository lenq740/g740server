<?php
// Экранная форма FormDisconnect
class FormDisconnect extends FormController {
	protected function getDefinitionMacro($params=Array()) {
		execDisconnect($params);
		throw new Exception('');
	}
}
return new FormDisconnect();
?>