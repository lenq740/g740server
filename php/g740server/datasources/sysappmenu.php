<?php
/**
 * @file
 * G740Server, источник данных для таблицы 'sysappmenu'.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** Класс DataSource для таблицы 'sysappmenu'
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * - mode.testperm исключает пункты меню, недоступные по правам
 */
class DataSource_Sysappmenu_System extends DataSource {
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->tableName='sysappmenu';
		$this->tableCaption='Главное меню системы';
		$this->permMode='rootref';
	}
/** Переопределяем поля источника данных
 */
	protected function initFields() {
		$result=Array();
		{	// parentid - Ссылка на родителя
			$fld=Array();
			$fld['name']='parentid';
			$fld['type']='ref';
			$fld['caption']='Ссылка на родителя';
			$fld['reftable']='sysappmenu';
			$fld['refalias']='sysappmenu_1';
			$result[]=$fld;
		}
		{	// name - Пункт меню
			$fld=Array();
			$fld['name']='name';
			$fld['type']='string';
			$fld['caption']='Пункт меню';
			$fld['maxlength']='255';
			$fld['len']='15';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// description - Дополнительные сведения
			$fld=Array();
			$fld['name']='description';
			$fld['type']='memo';
			$fld['caption']='Дополнительные сведения';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// form - Экранная форма
			$fld=Array();
			$fld['name']='form';
			$fld['type']='string';
			$fld['caption']='Экранная форма';
			$fld['maxlength']='255';
			$fld['len']='15';
			$result[]=$fld;
		}
		{	// icon - Иконка
			$fld=Array();
			$fld['name']='icon';
			$fld['type']='string';
			$fld['caption']='Иконка';
			$fld['maxlength']='255';
			$fld['len']='10';
			$result[]=$fld;
		}
		{	// params - Параметры вызова
			$fld=Array();
			$fld['name']='params';
			$fld['type']='memo';
			$fld['caption']='Параметры вызова';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// permmode - Права, режим
			$fld=Array();
			$fld['name']='permmode';
			$fld['type']='string';
			$fld['caption']='Права, режим';
			$fld['maxlength']='255';
			$fld['len']='10';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// permoper - Права, операция
			$fld=Array();
			$fld['name']='permoper';
			$fld['type']='string';
			$fld['caption']='Права, операция';
			$fld['maxlength']='255';
			$fld['len']='10';
			$fld['stretch']='1';
			$result[]=$fld;
		}
		{	// ord - №пп
			$fld=Array();
			$fld['name']='ord';
			$fld['type']='num';
			$fld['caption']='№пп';
			$result[]=$fld;
		}
		{	// child_count
			$fld=Array();
			$fld['name']='child_count';
			$fld['type']='num';
			$fld['caption']='Кол-во дочерних узлов';
			$fld['readonly']=1;
			$fld['virtual']=1;
			$result[]=$fld;
		}
		return $result;
	}
/** Переопределяем связи ссылочной целостности
 */
	protected function initReferences() {
		$result=Array();
		{	//  sysappmenu.id -> sysappmenu.parentid
			$ref=Array();
			$ref['mode']='cascade';
			$ref['from.table']='sysappmenu';
			$ref['from.field']='id';
			$ref['to.table']='sysappmenu';
			$ref['to.field']='parentid';
			$result['sysappmenu.parentid']=$ref;
		}
		return $result;
	}

/** Выполнить операцию append, вычисляем ord
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execAppend($params=Array()) {
		$params['#request.mode']='last';
		$parentid=$params['parentid'];
		if (!$parentid) $parentid=$params['filter.parentid'];
		if ($parentid) {
			$parentid=$this->str2Sql($parentid);
			$sql=<<<SQL
select 
	max(sysappmenu.ord) as ord
from
	sysappmenu
where
	sysappmenu.parentid='{$parentid}'
SQL;
			$rec=$this->pdoFetch($sql);
			$ord=$rec['ord'];
			if (!$ord) $ord=0;
			$params['ord']=$ord+10;
		}
		$result=parent::execAppend($params);
		return $result;
	}


/** Переопределяем секцию fields SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции fields SQL запроса select
 */
	public function getSelectFields($params=Array()) {
		$result=<<<SQL
	sysappmenu.*,
	child.count as child_count
SQL;
		return $result;
	}
/** Переопределяем секцию from SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции from SQL запроса select
 */
	public function getSelectFrom($params=Array()) {
		$result=<<<SQL
	sysappmenu
		left join (
			select
				child.parentid,
				count(*) as count
			from
				sysappmenu child
			group by child.parentid
		) child on child.parentid=sysappmenu.id
SQL;
		return $result;
	}
/** Переопределяем секцию order by SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции order by SQL запроса select
 */
	public function getSelectOrderBy($params=Array()) {
		return <<<SQL
sysappmenu.parentid, sysappmenu.ord, sysappmenu.id
SQL;
	}
/** Переопределяем секцию where SQL запроса select
 *
 * @param	Array	$params контекст выполнения
 * @return 	string текст секции where SQL запроса select
 */
	public function getSelectWhere($params=Array()) {
		$result=parent::getSelectWhere($params);
		$result.="\n".<<<SQL
	and sysappmenu.id>0
SQL;
		return $result;
	}
/** Переопределяем обработку запроса refresh
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function execRefresh($params=Array()) {
		$result=parent::execRefresh($params);
		if ($params['mode.testperm']) {
			$lst=$result;
			$result=Array();
			foreach($lst as &$row) {
				if ($row['permmode'] && !getPerm($row['permmode'], $row['permoper'])) continue;
				$result[]=$row;
			}
		}
		return $result;
	}
}
return new DataSource_Sysappmenu_System();