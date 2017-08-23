<?php
// Набор строк treedatamodel
class DataSource2_TreeDataModel extends DataSource {
	protected $dataSourceSysTableCategory=null;
	protected $dataSourceSysTable=null;
	protected $dataSourceSysField=null;

	// Инициализация констант
	function __construct() {
		$this->dataSourceSysTableCategory=getDataSource('systablecategory');
		$this->dataSourceSysTable=getDataSource('systable');
		$this->dataSourceSysField=getDataSource('sysfield');

		$this->tableName='treedatamodel';
		$this->tableCaption='Модель данных';
		$this->permMode='sysref';
	}
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
	<request name="unmarkall" js_enabled="get('#markcount')"/>

	<fields name="name" description="name">
		<field name="name" type="string" caption="Имя"/>
	</fields>
</section>
XML;
		}
		{	// section systablecategory
			$addRequests=<<<XML
<requests>
	<request name="shift"/>
	<request name="move" js_enabled="get('#this[systable].#markcount')">
		<param name="from.id" js_value="get('#this[systable].#mark')"/>
		<param name="from.type" value="systable"/>
	</request>
</requests>
XML;
			$result.="\n".$this->getStrXmlDefItem($params, $this->dataSourceSysTableCategory, 'systablecategory', 'name', 'name', $addRequests);
		}
		{	// section systable
			$addRequests=<<<XML
<requests>
	<request name="mark"/>
</requests>
XML;
			$result.="\n".$this->getStrXmlDefItem($params, $this->dataSourceSysTable, 'systable', 'tablename', 'name', $addRequests);
		}
		{	// section sysfield
			$addRequests=<<<XML
<requests>
	<request name="shift"/>
</requests>
XML;
			$result.="\n".$this->getStrXmlDefItem($params, $this->dataSourceSysField, 'sysfield', 'fieldname', 'name', $addRequests);
		}
		return $result;
	}
	
	public function exec($params=Array()) {
		$pdoDB=$this->getPDO();
		$rowParentType=$params['row.parenttype'];
		$requestName=$params['#request.name'];
		if ($requestName=='move') {
			$rowFromType=$params['from.type'];
			$rowFromId=$pdoDB->str2Sql($params['from.id']);
			$rowType=$params['row.type'];
			if ($rowType=='systablecategory' && $rowFromType=='systable') {
				return $this->execSysTableMove($params);
			}
		}
		if ($rowParentType=='root') return $this->execSysTableCategory($params);
		if ($rowParentType=='systablecategory') return $this->execSysTable($params);
		if ($rowParentType=='systable') return $this->execSysField($params);
		throw new Exception("Древовидный источник данных '{$this->$tableName}' не поддерживает тип родительского узла '{$rowParentType}'");
	}
	
	protected function execSysTableCategory($params) {
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		$result=Array();
		
		$p=$params;
		$p['#request.notord']=1;
		if ($requestName=='refresh' || $requestName=='expand' || $requestName=='append' || $requestName=='save' || $requestName=='delete' || $requestName=='shift') {
			if ($requestName=='expand') $p['#request.name']='refresh';
			if ($requestName=='append') $p['name']='<Новая категория>';
			$result=$this->dataSourceSysTableCategory->exec($p);
		} else {
			throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
		}
		foreach($result as $index=>$rec) {
			if ($rec['ord']) unset($rec['ord']);
			$rec['row.type']='systablecategory';
			$rec['row.icon']='folder';
			$result[$index]=$rec;
		}
		return $result;
	}
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
		} else {
			throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
		}
		foreach($result as $index=>$rec) {
			if ($rec['ord']) unset($rec['ord']);
			$rec['row.type']='systable';
			$rec['row.icon']='dbtable';
			$result[$index]=$rec;
		}
		return $result;
	}
	protected function execSysField($params) {
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		$result=Array();
		
		$p=$params;
		$p['#request.notord']=1;							// отключаем передачу ord клиенту при пересортировке
		$p['filter.klssystable']=$params['row.parentid'];
		$p['mode.treedatamodel']=1;							// отключаем поля information_schema.columns
		if ($requestName=='refresh' || $requestName=='expand' || $requestName=='append' || $requestName=='save' || $requestName=='delete' || $requestName=='shift') {
			if ($requestName=='expand') $p['#request.name']='refresh';
			if ($requestName=='append') $p['fieldname']='<Новое поле>';
			$result=$this->dataSourceSysField->exec($p);
		} else {
			throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
		}
		foreach($result as $index=>$rec) {
			if ($rec['ord']) unset($rec['ord']);
			$rec['row.type']='sysfield';
			$rec['row.icon']='drivecd';
			$rec['row.final']=1;
			$result[$index]=$rec;
		}
		return $result;
	}
	
	protected function execSysTableMove($params) {
		$result=Array();
		$klssystablecategory=$this->str2Sql($params['id']);
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
		return $result;
	}
}
return new DataSource2_TreeDataModel();
?>