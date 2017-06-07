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
	<request name="move" js_enabled="get('#this[@mark].id')">
		<param name="from.id" js_value="get('#this[@mark].id')"/>
		<param name="from.type" js_value="get('#this[@mark].row.type')"/>
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
		{	// создаем и сохраняем новую строку в $rec
			$p=Array();
			$p['klsparent']=$params['id'];
			$ord=$this->dataSourceSysMenu->getOrdAppendLast($p);
			$p=Array();
			$p['id']=$params['from.id'];
			$p['ord']=$ord;
			$p['klsparent']=$params['id'];
			$lst=$this->dataSourceSysMenu->execUpdate($p);
			if (count($lst)!=1) throw new Exception('Ошибка при переносе - не удалось перенести строку!!!');
			$rec=$lst[0];
		}
		{	// пополняем $rec необходимыми атрибутами и возвращаем
			$rec['row.destmode']='last';
			$rec['row.focus']=1;
			$result[]=$rec;
		}
		return $result;
	}
}
return new DataSource2_TreeMenu();
?>