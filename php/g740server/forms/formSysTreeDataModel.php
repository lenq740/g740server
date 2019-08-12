<?php
/**
 * @file
 * G740Server, контроллер экранной формы модели данных
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** FormController экранной формы модели данных
 */
class FormSysTreeDataModel extends FormController {
/** формируем макроподстановки для внедрения в XML описание экранной формы
 *
 * @param	Array	$params контекст вызова
 * @return	Array макроподстановки
 *
 * - проверяем наличие прав
 */
	protected function getDefinitionMacro($params=Array()) {
		$result=parent::getDefinitionMacro($params);
		$result['%permEnabledSysWrite%']=(getPerm('root','write')) ? 1 : 0;
		return $result;
	}
}
return new FormSysTreeDataModel();
