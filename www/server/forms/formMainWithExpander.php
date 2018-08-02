<?php
// Экранная форма FormMain
class FormMain extends FormController {
	protected function getDefinitionMacro($params=Array()) {
		$result=parent::getDefinitionMacro($params);
		$result['%login%']=getPP('login');
		$result['%title%']=getCfg('head.title');
		$result['%background%']=pathConcat(getCfg('path.root.html.entry'),getCfg('path.root.resource'),getCfg('path.root.resource.prjlib'),'logoscreen/background.jpg');
		return $result;
	}
}
return new FormMain();
