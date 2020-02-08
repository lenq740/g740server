<?php
/**
 * @file
 * G740Server, древовидный источник данных для главного меню проекта.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** Класс DataSource - древовидный источник данных для главного меню проекта.
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * Собран из таблицы 'sysappmenu'
 */
class DataSource_SysTreeMenu extends DataSource {
	protected $dataSourceSysAppMenu=null; ///< объект DataSource таблицы sysappmenu
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->dataSourceSysAppMenu=getDataSource('sysappmenu');
		$this->tableName='sysappmenu';
		$this->tableCaption='Главное меню';
		$this->permMode='rootref';
	}
/** Переопределяем XML описание древовидного источника данных
 *
 * @param	Array	$params контекст выполнения
 * @param	Array	$requests дополнительный список запросов
 * @return	string XML описание древовидного источника данных
 */
	public function getStrXmlDefinitionSections($params=Array(), $requests=null) {
		$result=<<<XML
<section>
	<requests>
		<request name="refresh"/>
		<request name="expand"/>
		<request name="save"/>
		<request name="append"/>
		<request name="append" mode="into"/>
		<request name="copy"/>
		<request name="delete"/>
		<request name="shift"/>
		<request name="move" js_enabled="get('rowset.markcount')">
			<param name="from.id" js_value="get('rowset.mark')"/>
		</request>
		<request name="mark" js_enabled="getplus('id')"/>
		<request name="unmarkall" js_enabled="get('rowset.markcount')"/>
	</requests>
	<fields name="name" description="description">
		<field name="parentid" type="string"/>
		<field name="name" type="string" caption="Пункт меню"/>
		<field name="description" type="string" caption="Описание пункта меню"/>
		<field name="icon" type="string" caption="Иконка"/>
		<field name="form" type="string" caption="Экранная форма"/>
		<field name="params" type="memo" caption="Параметры запуска"/>
		<field name="permmode" type="string"/>
		<field name="permoper" type="string"/>
		<field name="ord" type="num"/>
	</fields>
</section>
XML;
		return $result;
	}
/** Переопределяем обработку запросов
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function exec($params=Array()) {
		$rowParentType=$params['row.parenttype'];
		$rowParentId=$params['row.parentid'];
		$requestName=$params['#request.name'];
		if ($rowParentType=='root') return $this->exec4SysAppMenu($params);
		if ($rowParentType=='menu') return $this->exec4SysAppMenu($params);
		throw new Exception("Древовидный источник данных '{$this->$tableName}' не поддерживает тип родительского узла '{$rowParentType}'");
	}
/** Переопределяем обработку запросов для таблицы sysappmenu
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	protected function exec4SysAppMenu($params) {
		$rowParentType=$params['row.parenttype'];
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		$result=Array();
		$p=$params;
		if ($requestName=='move') {
			$id=$this->str2Sql($params['id']);
			if (!$id) throw new Exception('Не задан id!!!');
			$lstParent=Array();
			$childid=$id;
			while($childid>0) {
				if (isset($lstParent[$childid])) throw new Exception('Обнаружено зацикливание ссылок!!!');
				$lstParent[$childid]=$childid;
				$sql=<<<SQL
select sysappmenu.parentid
from
	sysappmenu
where
	id='{$childid}'
SQL;
				$row=$this->pdoFetch($sql);
				$childid=$this->str2Sql($row['parentid']);
			}
			$lstChilds=Array();
			foreach(explode(',',$params['from.id']) as $childid) {
				if (!isset($lstParent[$childid])) $lstChilds[$childid]=$childid;
			}
			$sqlChilds=$this->php2SqlIn($lstChilds);
			if ($sqlChilds) {
				$sql=<<<SQL
select max(sysappmenu.ord) as ord
from
	sysappmenu
where
	sysappmenu.parentid='{$id}'
SQL;
				$rec=$this->pdoFetch($sql);
				$ord=$rec['ord'];
				if (!$ord) $ord=0;
				
				$sql=<<<SQL
update sysappmenu set parentid='{$id}'
where
	id in ({$sqlChilds})
SQL;
				$this->pdo($sql);
				
				$sql="select id from sysappmenu where id in ({$sqlChilds})";
				$q=$this->pdo($sql);
				while($rec=$this->pdoFetch($q)) {
					$sysappmenuid=$rec['id'];
					$ord+=10;
					$sql="update sysappmenu set ord='{$ord}' where id='{$sysappmenuid}'";
					$this->pdo($sql);
				}
			}
		}
		else {
			if ($requestName=='expand') $p['#request.name']='refresh';
			if ($requestName=='append') $p['name']='<Новая строка меню>';
			if ($rowParentType=='root') {
				$p['filter.parentid']=-99;
			}
			else if ($rowParentType=='menu') {
				$p['filter.parentid']=$params['row.parentid'];
			}
			$result=$this->dataSourceSysAppMenu->exec($p);
		}
		
		
		foreach($result as &$row) {
			if ($row['id']>0 && !isset($row['icon'])) {
				$rrr=$this->dataSourceSysAppMenu->getRow($row['id']);
				$row['icon']=$rrr['icon'];
				$row['child_count']=$rrr['child_count'];
			}
			$row['row.icon']=$row['icon']?$row['icon']:'default';
			$row['row.type']='menu';
			if (!$row['child_count']) $row['row.empty']=1;
		}
		return $result;
	}
}
return new DataSource_SysTreeMenu();
