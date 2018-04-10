<?php
// Экранная форма FormMain
class FormMain extends FormController {
	protected function getDefinitionMacro($params=Array()) {
		$result=parent::getDefinitionMacro($params);
		$result['$background$']=pathConcat(getCfg('path.root.html.entry'),getCfg('path.root.resource'),'logoscreen/background.jpg');
		return $result;
	}
}
return new FormMain();
