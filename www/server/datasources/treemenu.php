<?php
// Набор строк treemenu
class DataSource2_TreeMenu extends DataSource {
	protected $dataSourceSysMenu=null;

	// Инициализация констант
	function __construct() {
		$this->dataSourceSysMenu=getDataSource('sysmenu');

		$this->tableName='treemenu';
		$this->tableCaption=$this->dataSourceSysMenu->tableCaption;
		$this->permMode='sysref';
	}
	
	public function getPerm($permOper='read', $requestName='', $params=Array()) {
		if ($permOper=='write' && $params['mode.treemenu']) return false;
		return parent::getPerm($permOper, $requestName, $params);
	}
	public function getStrXmlDefinitionRequests($params=Array(), $requests=null) {
		$strXmlRequests=$this->dataSourceSysMenu->getStrXmlDefinitionRequests($params, $requests);
		$xmlResult=strXml2DomXml($strXmlRequests);
		$strXmlAdd=<<<XML
<requests>
	<request name="expand"/>
	<request name="append" mode="into"/>
	<request name="mark"/>
	<request name="unmarkall" js_enabled="get('#markcount')"/>
	<request name="move" js_enabled="get('#markcount')">
		<param name="from.id" js_value="get('#mark')"/>
	</request>
</requests>
XML;
		$xmlAdd=strXml2DomXml($strXmlAdd);
		while($xmlAdd->firstChild) {
			$xmlResult->appendChild($xmlAdd->firstChild);
		}
		return domXml2StrXml($xmlResult);
	}
	public function getStrXmlDefinitionFields($params=Array()) {
		$strXmlFields=$this->dataSourceSysMenu->getStrXmlDefinitionFields($params);
		$xmlResult=strXml2DomXml($strXmlFields);
		xmlSetAttr($xmlResult, 'name', 'name');
		xmlSetAttr($xmlResult, 'description', 'name');
		return domXml2StrXml($xmlResult);
	}
	public function exec($params=Array()) {
		$requestName=$params['#request.name'];
		$requestMode=$params['#request.mode'];
		
		$result=Array();
		
		$p=$params;
		$p['filter.klsparent']=$params['row.parentid'];
		if ($params['row.parentid']=='root' && $params['row.parenttype']=='root') $p['filter.klsparent']='00000000-0000-0000-0000-000000000000';
		
		if ($requestName=='refresh' || $requestName=='expand' || $requestName=='append' || $requestName=='copy' || $requestName=='save' || $requestName=='delete' || $requestName=='shift') {
			if ($requestName=='expand') $p['#request.name']='refresh';
			if ($requestName=='append') $p['name']='<Новый узел>';
			$result=$this->dataSourceSysMenu->exec($p);
			if ($requestName=='append') {
				foreach($result as $key=>$rec) {
					$rec['row.type']='menuitem';
					$rec['row.empty']='1';
					$result[$key]=$rec;
				}
			}
		} else if ($requestName=='move') {
			$result=$this->execMove($p);
		} else {
			throw new Exception('Операция '.$requestName.' не поддерживается источником данных '.$this->tableName);
		}
		return $result;
	}
	protected function execMove($params=Array()) {
		if (!$this->getPerm('write','move',$params)) throw new Exception('У Вас нет прав на перенос строк в таблице '.$this->tableCaption);
		$result=Array();
		{  // Находим подходящий ord
			$p=Array();
			$p['klsparent']=$params['id'];
			$ord=$this->dataSourceSysMenu->getOrdAppendLast($p);
		}
		{  // Для избежания зацикливания, находим список родителей текущего узла
			$id=$this->str2Sql($params['id']);
			$parents=Array();
			while($id) {
				$parents[$id]=true;
				$sql="select klsparent from sysmenu where id='{$id}'";
				$rec=$this->pdoFetch($sql);
				$id=$rec['klsparent'];
			}
		}
		{  // Выполняем поток update
			$klsParent=$this->str2Sql($params['id']);
			$sqlUpdate='';
			$sqlUpdateCount=0;
			foreach(explode(',',$params['from.id']) as $id) {
				$id=$this->str2Sql($id);
				if ($parents[$id]) continue;
				$sqlUpdate.="\n".<<<SQL
update sysmenu set klsparent='{$klsParent}', ord='{$ord}' where id='{$id}';
SQL;
				$sqlUpdateCount++;
				$ord+=100;
				if ($sqlUpdateCount>500) {
					$this->pdo($sqlUpdate);
					$sqlUpdate='';
					$sqlUpdateCount=0;
				}
			}
			if ($sqlUpdateCount) {
				$this->pdo($sqlUpdate);
				$sqlUpdate='';
				$sqlUpdateCount=0;
			}
		}
		return $result;
	}
}
return new DataSource2_TreeMenu();
?>