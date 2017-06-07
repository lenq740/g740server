<?php
// Экранная форма FormTreeDataModel
class FormTreeDataModel extends FormController {
	protected function getDefinitionMacro($params=Array()) {
		$result=parent::getDefinitionMacro($params);
		$result['%permEnabledSysWrite%']=(getPerm('sys','write')) ? 1 : 0;
		return $result;
	}
}
return new FormTreeDataModel();
?>