<?php
/**
 * @file
 * G740Server, древовидный источник данных для модели данных.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
 
/** Класс DataSource - древовидный источник данных для модели данных.
 *
 * Системный класс. Не требует наличия описаний в модели данных. Поэтому выписан целиком, а не попрожден от автоматически построенного предка.
 * Адаптирован под MySql, PostGreSql, MsSql.
 *
 * Собран из таблиц 'systablecategory', 'systable'
 */
class DataSource_SysTreeDataModel extends DataSource {
	protected $dataSourceSysTableCategory=null; ///< объект DataSource таблицы systablecategory
	protected $dataSourceSysTable=null; 		///< объект DataSource таблицы systable
/** Переопределяем инициализацию констант
 */
	function __construct() {
		parent::__construct();
		$this->dataSourceSysTableCategory=getDataSource('systablecategory');
		$this->dataSourceSysTable=getDataSource('systable');

		$this->tableName='treedatamodel';
		$this->tableCaption='Модель данных';
		$this->permMode='rootref';
	}
	
/** Переопределяем XML описание древовидного источника данных
 *
 * @param	Array	$params контекст выполнения
 * @param	Array	$requests дополнительный список запросов
 * @return	string XML описание древовидного источника данных
 */
	public function getStrXmlDefinitionSections($params=Array(), $requests=null) {
		$result='';
		{	// sectionDefault
			$result.="\n".<<<XML
<section>
	<request name="refresh"/>
	<request name="expand"/>
	<request name="append"/>
	<request name="append" mode="into"/>
	<request name="delete"/>
	<request name="save"/>
	<request name="unmarkall" js_enabled="get('rowset.markcount')"/>

	<fields name="name" description="name">
		<field name="name" type="string" caption="Имя"/>
	</fields>
</section>
XML;
		}
		{	// section systablecategory
			$p=Array(
				'row.type'=>'systablecategory',
				'tree.name'=>'name',
				'tree.description'=>'name',
				'tree.default.icon'=>'folder',
				'xml.requests'=><<<XML
<requests>
	<request name="shift"/>
	<request name="move" js_enabled="get('#this[systable].rowset.markcount')">
		<param name="from.id" js_value="get('#this[systable].rowset.mark')"/>
		<param name="from.markederror" js_value="get('#this[systable].rowset.markcount')!=get('#this.rowset.markcount')"/>
	</request>
</requests>
XML
			);
			$result.="\n".$this->getStrXmlDefinitionTreeSection($p, $this->dataSourceSysTableCategory);
		}
		{	// section systable
			$p=Array(
				'row.type'=>'systable',
				'tree.name'=>'tablename',
				'tree.description'=>'name',
				'tree.default.icon'=>'dbtable',
				'tree.default.final'=>true,
				'xml.requests'=><<<XML
<requests>
	<request name="mark"/>
	<request name="expand" enabled="0"/>
</requests>
XML
			);
			$result.="\n".$this->getStrXmlDefinitionTreeSection($p, $this->dataSourceSysTable);
		}
		return $result;
	}
/** Переопределяем обработку запросов
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	public function exec($params=Array()) {
		$rowParentType=$params['row.parenttype'];
		$requestName=$params['#request.name'];
		if ($rowParentType=='root') return $this->execSysTableCategory($params);
		if ($rowParentType=='systablecategory') return $this->execSysTable($params);
		throw new Exception("Древовидный источник данных '{$this->$tableName}' не поддерживает тип родительского узла '{$rowParentType}'");
	}
/** Переопределяем обработку запросов для таблицы systablecategory
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	protected function execSysTableCategory($params) {
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		$result=Array();
		
		$p=$params;
		$lstRequests=Array('refresh', 'refreshrow', 'expand', 'append', 'save', 'delete', 'shift');
		if (array_search($requestName, $lstRequests)!==false) {
			if ($requestName=='expand') $p['#request.name']='refresh';
			if ($requestName=='append') $p['name']='<Новая категория>';
			$result=$this->dataSourceSysTableCategory->exec($p);
		}
		else if ($requestName=='move') {
			$klssystablecategory=$this->str2Sql($params['id']);
			
			if ($params['from.markederror']) throw new Exception('Часть помеченных узлов неподходящего типа, перенос невозможен!!!');
			$lstSysTable=explode(',',$params['from.id']);
			$lst='';
			foreach($lstSysTable as $klsSysTable) {
				if ($lst) $lst.=',';
				$lst.="'".$this->str2Sql($klsSysTable)."'";
			}
			if ($klssystablecategory && $lst) {
				$sql=<<<SQL
update systable set klssystablecategory='{$klssystablecategory}' where id in ({$lst})
SQL;
				$this->pdo($sql);
			}
		}
		else {
			throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
		}
		foreach($result as $index=>$rec) {
			$rec['row.type']='systablecategory';
			$rec['row.icon']='folder';
			$result[$index]=$rec;
		}
		return $result;
	}
/** Переопределяем обработку запросов для таблицы systable
 *
 * @param	Array	$params контекст выполнения
 * @return	Array результат выполнения операции
 */
	protected function execSysTable($params) {
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		$result=Array();
		
		$p=$params;
		$p['filter.klssystablecategory']=$params['row.parentid'];
		if ($requestName=='refresh' || $requestName=='expand' || $requestName=='append' || $requestName=='save' || $requestName=='delete' || $requestName=='shift') {
			if ($requestName=='expand') $p['#request.name']='refresh';
			if ($requestName=='append') $p['tablename']='<Новая таблица>';
			$result=$this->dataSourceSysTable->exec($p);
		}
		else {
			throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
		}
		foreach($result as $index=>$rec) {
			if ($rec['ord']) unset($rec['ord']);
			$rec['row.type']='systable';
			$rec['row.icon']='dbtable';
			$rec['row.final']=1;
			$result[$index]=$rec;
		}
		return $result;
	}
}
return new DataSource_SysTreeDataModel();
