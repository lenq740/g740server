<?php
/**
 * @file
 * G740Server, контроллер экранной формы истории правки
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** FormController экранной формы истории правки
 */
class FormSysDbLog extends FormController {
	protected function getDefinitionMacro($params=Array()) {
		if (!getPerm('sysdblog','read')) throw new Exception('У Вас нет прав на просмотр журнала истории правки');
		$result=parent::getDefinitionMacro($params);

		$result['%filter.table%']='';
		$result['%filter.rowid%']='';
		$result['%filter.parent%']='';
		$result['%filter.parentid%']='';
		$result['%filter.mode.list%']='список;удаленные';
		$result['%filter.mode%']='список';
		
		$table=$params['table'];
		if (!$table) throw new Exception('Не задан обязательный параметр table');
		$result['%filter.table%']=$table;

		$rowid=$params['rowid'];
		if ($rowid) {
			$result['%filter.rowid%']=$rowid;
			$result['%filter.mode.list%']='строка;список;удаленные';
			$result['%filter.mode%']='строка';
		}
		
		$parent=$params['parent'];
		$parentid=$params['parentid'];
		if ($parent && $parentid) {
			$result['%filter.parent%']=$parent;
			$result['%filter.parentid%']=$parentid;
		}
		
		try {
			$dataSourceTable=getDataSource($table);
			if ($parent && $parentid) {
				$caption="Подчиненные строки таблицы '{$dataSourceTable->tableCaption}'";
			}
			else {
				$caption="История правки строки таблицы '{$dataSourceTable->tableCaption}'";
			}
		}
		catch(Exception $e) {
		}
		$result['%caption%']=$caption;
		
		return $result;
	}
}
return new FormSysDbLog();
