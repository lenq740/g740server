<?php
// Экранная форма FormMain
class FormMain extends FormController {
	protected function getDefinitionMacro($params=Array()) {
		$result=parent::getDefinitionMacro($params);
		$result['$sysmenubar$']=$this->getSysMenuBar();
		return $result;
	}
}
return new FormMain();
?>