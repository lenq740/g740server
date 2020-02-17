<?php
/**
 * @file
 * G740Server, список допустимых полей для логирования
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

/** Класс DataSource_Sysdblogfields_System - список допустимых полей для логирования
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 */
class DataSource_Sysdblogfields_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysdblogfields';
		$this->tableCaption='Поля для логирования';
		$this->permMode='sysdblog';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// name - Имя поля
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Имя поля';
			$fld['maxlength']='255';
			$fld['len']='15';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем допустимые запросы
 */
	protected function initRequests() {
		$result=Array();
		$result[]=Array(
			'name'=>'refresh',
			'permoper'=>'read'
		);
		$result[]=Array(
			'name'=>'refreshrow',
			'permoper'=>'read'
		);
		return $result;
	}
/** Переопределяем обработку запроса refresh
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execRefresh($params=Array()) {
		$table=$params['filter.table'];
		if (!$table) throw new Exception('Не задан обязательный параметр table');
		$dataSource=getDataSource($table);
		$fields=$dataSource->getLogFields();
		$result=Array();
		foreach($fields as &$fld) {
			if (isset($params['filter.id']) && $fld['name']!=$params['filter.id']) continue;
			$row=Array();
			$row['id']=$fld['name'];
			$row['name']=$fld['caption'] ?? $fld['name'];
			$result[]=$row;
		}
		return $result;
	}
}
return new DataSource_Sysdblogfields_System();