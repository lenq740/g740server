<?php
/**
 * @file
 * G740Server, контроллер главной экранной формы приложения
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** FormController главной экранной формы приложения
 *
 * Шапка, главное меню, место под размещение экранных форм
 */
class FormMain extends FormController {
/** формируем макроподстановки для внедрения в XML описание экранной формы
 *
 * @param	Array	$params контекст вызова
 * @return	Array макроподстановки
 */
	protected function getDefinitionMacro($params=Array()) {
		$result=parent::getDefinitionMacro($params);
		$result['%login%']=getPP('login');
		$result['%title%']=getCfg('project.name');
		
		$result['%background%']=pathConcat(
			getCfg('href.root'),
			getCfg('path.root.resource'),
			getCfg('path.root.resource.logoscreen'),
			'background.jpg'
		);

		$result['%form%']='';
		$result['%menu.showonempty%']=1;
		return $result;
	}
}
return new FormMain();
