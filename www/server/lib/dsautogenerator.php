<?php
/**
 * @file
 * Автоматический генератор объектов доступа к данным DataSource по описанию структуры базы данных
 *
 */
require_once('dsconnector.php');
require_once('datasource-controller.php');

/**
 * Класс автоматической генерации источников данных
 *
 * Структура таблиц, ссылочная целостность, поля и атрибуты полей должны быть описаны в таблицах: systable, sysfield, sysfieldparams.
 * По этим описаниям автоматически строятся объекты доступа к данным DataSource. В последствии эти объекты можно использовать непосредственно,
 * а можно вносить в них изменения, порождая потомков.
 */
class DSAutoGenerator extends DSConnector {
/// Описание структуры базы данных, взятое из systable и sysfield
	protected $dataStructure=Array();
/// Описание ссылочной целостности и связей
	protected $dataStructureRef=Array();
/// Флажок - логировать процесс генерации через echo
	protected $isEcho=true;
/** Создать экземпляр объекта DSAutoGenerator
 *
 * @param	Array	$params
 *
 * param['isEcho']
 */
	function __construct($params=Array()) {
		if ($params['isEcho']) $this->isEcho=true;
		$this->initDataStructure();
	}
/** Загрузить описание структуры данных из базы в переменные
 *
 * @return	boolean успешность выполнения загрузки
 */
	protected function initDataStructure() {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::initDataStructure';
		if ($this->isEcho) {
			echo '<div class="message">Инициализация описания структуры данных '; flush();
		}
		$sql=<<<SQL
select
	systable.*
from
	systable
		left join systablecategory on systablecategory.id=systable.klssystablecategory
order by systablecategory.ord, systablecategory.id, systable.tablename, systable.id
SQL;
		$q=$this->pdo($sql);
		while($rec=$this->pdoFetch($q)) {
			$this->_initDataStructureTable($rec['tablename']);
			if ($this->isEcho) {
				echo '. '; flush();
			}
		}
		$this->_initDataStructureRef();
		if ($this->isEcho) {
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
		return true;
	}
/** Вспомогательная процедура, для загрузки описания структуры данных таблицы
 *
 * @param	string	$tableName таблица
 * @return	boolean успешность выполнения загрузки
 */
	protected function _initDataStructureTable($tableName) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::_initDataStructureTable';
		if ($tableName!=$this->str2Sql($tableName)) throw new Exception($errorMessage.', недопустимое имя таблицы '.$tableName);
		$sql="select * from systable where tablename='{$tableName}'";
		$rec=$this->pdoFetch($sql);
		$klssystable=$rec['id'];
		if (!$klssystable) throw new Exception($errorMessage.', недопустимое имя таблицы '.$tableName);

		unset($dsT);
		$dsT=Array();
		$dsT['name']=$rec['tablename'];
		$dsT['caption']=$rec['name'];
		$dsT['permmode']=$rec['permmode'];
		$dsT['selectorderby']=$rec['orderby'];
		$dsT['selectfields']=$rec['fields'];

		$dsT['fields']=Array();
		$sql=<<<SQL
select 
	sysfield.*,
	sysfieldtype.g740type as sysfieldtype_g740type,
	reftable.tablename as reftable_tablename
from 
	sysfield
		left join sysfieldtype on sysfieldtype.id=sysfield.klssysfieldtype
		left join systable reftable on reftable.id=sysfield.klsreftable
where 
	sysfield.klssystable='{$klssystable}'
SQL;
		$q=$this->pdo($sql);
		while($rec=$this->pdoFetch($q)) {
			$klssysfield=$rec['id'];
			unset($dsF);
			$dsF=Array();
			$dsF['name']=$rec['fieldname'];
			$dsF['caption']=$rec['name'];
			$dsF['maxlength']=$rec['maxlength'];
			$dsF['type']=$rec['sysfieldtype_g740type'];
			if ($dsF['type']=='id') {
				$dsT['id']=$rec['fieldname'];
			}

			if ($rec['isnotempty']) $dsF['isnotempty']=1;
			if ($rec['ismain']==1) $dsF['ismain']=1;
			if ($rec['len']) $dsF['len']=$rec['len'];
			if ($rec['dec']) $dsF['dec']=$rec['dec'];
			if ($rec['isstretch']==1) $dsF['stretch']=1;
			
			if ($rec['sysfieldtype_g740type']=='ref' && $rec['reftable_tablename']) {
				$dsF['reftable']=$rec['reftable_tablename'];
				if ($rec['reflink']) $dsF['link']=$rec['reflink'];
				if ($rec['isrefrestrict']) $dsF['isrefrestrict']=$rec['isrefrestrict'];
				if ($rec['isrefcascade']) $dsF['isrefcascade']=$rec['isrefcascade'];
				if ($rec['isrefclear']) $dsF['isrefclear']=$rec['isrefclear'];
				if ($rec['isref121']) $dsF['isref121']=$rec['isref121'];
			}
			
			$sql=<<<SQL
select * 
from 
	sysfieldparams
where
	sysfieldparams.klssysfield='{$klssysfield}'
SQL;
			$qParams=$this->pdo($sql);
			while($recParams=$this->pdoFetch($qParams)) {
				if ($dsF['type']=='list' || $dsF['type']=='icons' || $dsF['type']=='radio') {
					if ($recParams['name']=='list') $dsF['list']=trim($recParams['val']);
					if ($recParams['name']=='basetype') $dsF['basetype']=trim($recParams['val']);
				}

				if ($recParams['name']=='readonly' && trim($recParams['val'])==1) $dsF['readonly']=1;
				if ($recParams['name']=='js_readonly') $dsF['js_readonly']=trim($recParams['val']);
				if ($recParams['name']=='visible' && trim($recParams['val'])=='0') $dsF['visible']='0';
				if ($recParams['name']=='js_visible') $dsF['js_visible']=trim($recParams['val']);
				if ($recParams['name']=='rows' && $dsF['type']=='memo') $dsF['rows']=trim($recParams['val']);
				if ($recParams['name']=='save' && trim($recParams['val'])==1) $dsF['save']=1;
				if ($recParams['name']=='default') $dsF['default']=trim($recParams['val']);
				if ($recParams['name']=='fullname') $dsF['fullname']=trim($recParams['val']);
				if ($recParams['name']=='dlgwidth') $dsF['dlgwidth']=trim($recParams['val']);
				if ($recParams['name']=='change') {
					$dsF['change']=1;
					$str=trim($recParams['val']);
					$dsChangeParams=Array();
					foreach(explode("\n",$str) as $paramIndex=>$param) {
						$a=explode('=',$param);
						if (count($a)==1) $dsChangeParams[$a[0]]='';
						if (count($a)>=2) $dsChangeParams[$a[0]]=$a[1];
					}
					if (count($dsChangeParams)>0) $dsF['change.params']=$dsChangeParams;
				}
				if ($recParams['name']=='ref' && $dsF['type']=='ref') {
					$str=trim($recParams['val']);
					$dsRefParams=Array();
					foreach(explode("\n",$str) as $paramIndex=>$param) {
						$a=explode('=',$param);
						if (count($a)==1) $dsRefParams[$a[0]]='';
						if (count($a)>=2) $dsRefParams[$a[0]]=$a[1];
					}
					if (count($dsRefParams)>0) $dsF['ref.params']=$dsRefParams;
				}
			}
			$dsT['fields'][$rec['fieldname']]=&$dsF;
			unset($dsF);
		}
		$this->dataStructure[$tableName]=&$dsT;
		unset($dsT);
		return true;
	}
/** Вспомогательная процедура, для загрузки описания ссылочной целостности и связей
 *
 * @return	boolean успешность выполнения загрузки
 */
	protected function _initDataStructureRef() {
		$this->dataStructureRef=Array();
		foreach($this->dataStructure as $tableName=>$dsTable) {
			if(!$dsTable['fields']) continue;
			foreach($dsTable['fields'] as $fieldName=>$dsF) {
				if ($dsF['type']!='ref') continue;
				if (!$dsF['reftable']) continue;
				if ($dsF['isrefrestrict']==1) {
					$res=Array();
					$res['mode']='restrict';
					$res['from.table']=$tableName;
					$res['from.field']=$dsF['name'];
					$res['to.table']=$dsF['reftable'];
					$res['to.field']='id';
					$this->dataStructureRef[]=$res;
				}
				else if ($dsF['isrefcascade']==1) {
					$res=Array();
					$res['mode']='cascade';
					$res['from.table']=$dsF['reftable'];
					$res['from.field']='id';
					$res['to.table']=$tableName;
					$res['to.field']=$dsF['name'];
					$this->dataStructureRef[]=$res;
				}
				else if ($dsF['isrefclear']==1) {
					$res=Array();
					$res['mode']='clear';
					$res['from.table']=$dsF['reftable'];
					$res['from.field']='id';
					$res['to.table']=$tableName;
					$res['to.field']=$dsF['name'];
					$this->dataStructureRef[]=$res;
				}
				else {
					$res=Array();
					$res['mode']='';
					$res['from.table']=$dsF['reftable'];
					$res['from.field']='id';
					$res['to.table']=$tableName;
					$res['to.field']=$dsF['name'];
					$this->dataStructureRef[]=$res;
				}
			}
		}
		return true;
	}
/** Сгенерить источники данных
 *
 * @param	Array $params
 */
	public function goDataSources($params=Array()) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::goDataSources';
		if ($this->isEcho) {
			echo '<div class="message">Генерация классов DataSource '; flush();
		}
		
		$lstFiles=Array();
		foreach($this->dataStructure as $tableName=>$tbl) {
			if ($tbl['id']!='id') continue;
			$p=$params;
			$p['tableName']=$tableName;
			$value=<<<PHP
<?php
{$this->getDataSource($p)}
?>
PHP;
			$fileName=$tableName.'-autogen.php';
			$fullName=pathConcat(getCfg('path.root'), getCfg('path.root.datasources'), 'autogen', $fileName);
			$this->writeFile($fullName, $value);
			$lstFiles[$fileName]=true;
			if ($this->isEcho) {
				echo '. '; flush();
			}
		}
		if ($this->isEcho) {
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}

		if ($this->isEcho) {
			echo '<div class="message">Очистка лишних файлов в папке DataSource '; flush();
		}
		foreach(glob(pathConcat(getCfg('path.root'), getCfg('path.root.datasources'), 'autogen', '*.php')) as $key=>$fullName) {
			$fileName=basename($fullName);
			if ($lstFiles[$fileName]) continue;
			unlink($fullName);
			if ($this->isEcho) {
				echo '. '; flush();
			}
		}
		if ($this->isEcho) {
			echo 'Ok!</div>'; flush();
			echo '<script>document.body.scrollIntoView(false)</script>'; flush();
		}
	}
/** Вернуть описание автосгенеренного источника данных
 *
 * @param	Array $params
 * @return	String описание автосгенеренных источников данных
 *
 *	params['tableName']
 */
	public function getDataSource($params=Array()) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::getDataSource';
		$D='$';
		$tableName=$params['tableName'];
		if (!$tableName) throw new Exception($errorMessage.', не задан обязательный параметр tableName');
		$dsTable=&$this->dataStructure[$tableName];
		if (!$dsTable) throw new Exception($errorMessage.', недопустимое значение параметра tableName='.$tableName);
		$className='DataSource_'.mb_strtoupper(mb_substr($tableName,0,1)).mb_strtolower(mb_substr($tableName,1,999));
		
		$fields=Array();
		$aliases=Array();
		$p=$params;
		unset($p['aliasName']);
		unset($p['level']);
		$this->_buildDataSourceInfo($p, $fields, $aliases);
		
		$p=$params;
		$p['fields']=&$fields;
		$p['aliases']=&$aliases;
		$fields=$this->_convertFieldsToDataSourceFields($p);
		unset($p);
		

		$driverName=$this->getDriverName();
		$dataSource=new DataSource();
		$resultIsGUID=getCfg('datasourceIsGUID')?'true':'false';

		$dataSourceModify=getDataSourceModify($tableName);

		if ($dataSourceModify) $fields=$dataSourceModify->getFields($fields);
		$resultGetFields=$this->_getDataSourceFields($fields, $tableName);
		
		$resultGetSelectFields=$dataSource->autoGenGetSelectFields($fields, $tableName, $dsTable['selectfields'], $driverName);
		$resultGetSelectFrom=$dataSource->autoGenGetSelectFrom($fields, $tableName, $driverName);
		$resultGetSelectWhere=$dataSource->autoGenGetSelectWhere($fields, $tableName, $driverName);
		if ($driverName=='mysql') {
			$resultGetSelectOrderBy="`{$tableName}`.id";
		} else if ($driverName=='sqlsrv') {
			$resultGetSelectOrderBy="[{$tableName}].id";
		} else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}
		if ($dsTable['selectorderby']) $resultGetSelectOrderBy=$dsTable['selectorderby'];
		$resultGetRef=$this->_getDataSourceTableRef($params);
		$resultXmlDefinitionFields=$dataSource->autoGenXmlDefinitionFields($fields, $tableName);
		$resultGetRequests='';
		if ($dataSourceModify) {
			$resultGetSelectFields=$dataSourceModify->getSelectFields($resultGetSelectFields);
			$resultGetSelectFrom=$dataSourceModify->getSelectFrom($resultGetSelectFrom);
			$resultGetSelectOrderBy=$dataSourceModify->getSelectOrderBy($resultGetSelectOrderBy);
		}

		unset($dataSourceModify);
		
		$result=<<<PHP
class {$className} extends DataSource {
// Инициализация констант
function __construct() {
	{$D}this->tableName='{$tableName}';
	{$D}this->tableCaption='{$dsTable['caption']}';
	{$D}this->permMode='{$dsTable['permmode']}';
	{$D}this->isGUID={$resultIsGUID};
PHP;
		if ($dsTable['selectfields']) {
			$result.="\n".<<<PHP
	{$D}this->selectOtherFields=<<<SQL
{$dsTable['selectfields']}
SQL;
PHP;
		}
		$result.="\n".<<<PHP
}
// Тут описываются поля источника данных
protected function initFields() {
	{$D}result=Array();
{$resultGetFields}
	return {$D}result;
}
// Тут описываются связи с другими источниками данных для реализации ссылочной целостности
protected function initReferences() {
	{$D}result=Array();
{$resultGetRef}
	return {$D}result;
}
// Этот метод возвращает список полей для запроса select
public function getSelectFields({$D}params=Array()) {
	{$D}result=<<<SQL
{$resultGetSelectFields}
SQL;
	return {$D}result;
}
// Этот метод возвращает секцию from для запроса select
public function getSelectFrom({$D}params=Array()) {
	{$D}result=<<<SQL
{$resultGetSelectFrom}
SQL;
	return {$D}result;
}
// Этот метод Этот метод возвращает секцию where для запроса select
public function getSelectWhere({$D}params=Array()) {
{$resultGetSelectWhere}
	return {$D}result;
}
// Этот метод возвращает строку order by для запроса select
public function getSelectOrderBy({$D}params=Array()) {
	return <<<SQL
{$resultGetSelectOrderBy}
SQL;
}
// Этот метод демонстрирует результаты метода getStrXmlDefinitionFields
public function getStrXmlDefinitionFields({$D}params=Array()) {
	{$D}result=<<<XML
{$resultXmlDefinitionFields}
XML;
	return {$D}result;
}
}
return new {$className}();
PHP;
		return $result;
	}
/** Вернуть описание связей для автосгенерации источника данных
 *
 * @param	Array $params
 * @return	String описание связей для автосгенерации источника данных
 *
 *	params['tableName']
 */
	protected function _getDataSourceTableRef($params=Array()) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::_getDataSourceTableRef';
		$D='$';
		$tableName=$params['tableName'];
		if (!$tableName) throw new Exception($errorMessage.', не задан обязательный параметр tableName');
		$dsTable=&$this->dataStructure[$tableName];
		if (!$dsTable) throw new Exception($errorMessage.', недопустимое значение параметра tableName='.$tableName);
		$result='';
		foreach($this->dataStructureRef as $key=>$ref) {
			$fromTable='';
			$fromField='';
			$toTable='';
			$toField='';
			$linkMode='';
			$linkName='';
			if ($ref['from.table']==$tableName) {
				$fromTable=$ref['from.table'];
				$fromField=$ref['from.field'];
				$toTable=$ref['to.table'];
				$toField=$ref['to.field'];
				$linkMode=$ref['mode'];
			}
			else if ($ref['to.table']==$tableName) {
				$fromTable=$ref['to.table'];
				$fromField=$ref['to.field'];
				$toTable=$ref['from.table'];
				$toField=$ref['from.field'];
				$linkMode=$ref['mode'];
			}
			else {
				continue;
			}
			
			if ($fromField!='id' && $toField=='id') {
				$linkName="{$fromTable}.{$fromField}";
			}
			else if ($fromField=='id' && $toField!='id') {
				$linkName="{$toTable}.{$toField}";
			}
			else {
				continue;
			}
			
			if ($result) $result.="\n";
			$result.=<<<PHP
{	//  {$fromTable}.{$fromField} -> {$toTable}.{$toField}
	{$D}ref=Array();
	{$D}ref['mode']='{$linkMode}';
	{$D}ref['from.table']='{$fromTable}';
	{$D}ref['from.field']='{$fromField}';
	{$D}ref['to.table']='{$toTable}';
	{$D}ref['to.field']='{$toField}';
	{$D}result['{$linkName}']={$D}ref;
}
PHP;
		}
		return $this->strTabShift($result,1);
	}
/** Вернуть описание полей для автосгенерации источника данных
 *
 * @param	Array $params
 * @return	String описание полей для автосгенерации источника данных
 *
 *	params['tableName']
 */
	protected function _getDataSourceFields($fields, $tableName) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::_getDataSourceFields';
		$D='$';
		$result='';
		foreach($fields as $key=>$fld) {
			$items='';
			foreach($fld as $name=>$value) {
				if ($items) $items.="\n";
				if (gettype($value)=='array') {
					$items.="\t\t"."{$D}fld['{$name}']=".var_export($value, true).";";
				}
				else {
					$items.="\t\t"."{$D}fld['{$name}']='{$value}';";
				}
			}
			if ($result) $result.="\n";
			$result.=<<<PHP
	{	// {$fld['name']} - {$fld['caption']}
		{$D}fld=Array();
{$items}
		{$D}result[]={$D}fld;
	}
PHP;
		}
		return $result;
	}
/** Построить массив описателей полей для источника данных
 *
 * @param	Array $params
 * @return	String описание автосгенеренных источников данных
 *
 * params['tableName']
 * params['fields']
 * params['aliases']
 */
	protected function _convertFieldsToDataSourceFields($params=Array()) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::_convertFieldsToDataSourceFields';
		$result=Array();
		$tableName=$params['tableName'];
		if (!$tableName) throw new Exception($errorMessage.', не задан обязательный параметр tableName');
		$fields=$params['fields'];
		$aliases=$params['aliases'];

		foreach($fields as $key=>$fld) {
			if ($fld['type']=='id') continue;
			$res=Array();
			$fieldName=$fld['name'];
			if ($tableName!=$fld['alias']) $fieldName=$fld['alias'].'_'.$fld['name'];
			$res['name']=$fieldName;
			if ($fieldName!=$fld['name']) $res['fieldname']=$fld['name'];
			
			$als=$aliases[$fld['alias']];
			if ($als) {
				if ($als['table']!=$tableName) $res['table']=$als['table'];
				if ($als['table']!=$als['alias']) $res['alias']=$als['alias'];
			}

			$res['type']=$fld['type'];
			$res['caption']=$fld['caption'];
			if ($fld['maxlength']) $res['maxlength']=$fld['maxlength'];
			if ($fld['len']) $res['len']=$fld['len'];
			if ($fld['list']) $res['list']=$fld['list'];
			if ($fld['basetype']) $res['basetype']=$fld['basetype'];
			if ($fld['dec']) $res['dec']=$fld['dec'];
			if ($fld['isnotempty']) $res['notnull']=1;
			if ($fld['save']) $res['save']=1;
			
			if ($tableName==$fld['alias']) {
				if ($fld['readonly']) $res['readonly']=$fld['readonly'];
				if ($fld['stretch']) $res['stretch']=1;
				if ($fld['default']) $res['default']=$fld['default'];
			}
			
			if ($fld['js_readonly']) $res['js_readonly']=$fld['js_readonly'];
			if ($fld['visible']) $res['visible']=$fld['visible'];
			if ($fld['js_visible']) $res['js_visible']=$fld['js_visible'];
			
			if (($fld['type']=='string' || $fld['type']=='memo') && !$fld['len']) $res['stretch']=1;
			if ($fld['change']==1) {
				$res['change']=$fld['change'];
				if ($fld['change.params']) $res['change.params']=$fld['change.params'];
			}
			if ($fld['type']=='ref') {
				if ($fld['reftable']) $res['reftable']=$fld['reftable'];
				if ($fld['ref.params']) $res['ref.params']=$fld['ref.params'];
				foreach($aliases as $aName=>$a) {
					if ($a['parent.alias']==$fld['alias'] && $a['parent.refid']==$fld['name']) {
						if ($a['alias']!=$a['table']) $res['refalias']=$a['alias'];
						break;
					}
				}
			}
			if ($fld['dlgwidth']) $res['dlgwidth']=$fld['dlgwidth'];
			if ($fld['alias']!=$tableName) {
				$res['refname']=$fld['name'];
				if ($fld['fullname']) $res['reftext']=$fld['fullname'];
				if ($als) {
					$refIdFieldName=$als['parent.refid'];
					if ($tableName!=$als['parent.alias']) $refIdFieldName=$als['parent.alias'].'_'.$als['parent.refid'];
					$res['refid']=$refIdFieldName;
				}
			}
			$result[]=$res;
		}
		return $result;
	}
/** Рекурсивная процедура формирование списка полей и подключаемых таблиц
 *
 * @param	Array $params
 * @param	Array &$fields	- формируемый список полей
 * @param	Array &$aliases	- формируемый список имен таблиц, используемых в SQL запросе
 *
 * params['tableName'] - имя таблицы
 * params['aliasName'] - имя таблицы, используемое в SQL запросе
 * params['level'] - уровень рекурсии, не выше 4-х
 * fld['name'] - имя поля в таблице
 * fld['table']	- имя таблицы
 * fld['alias']	- имя таблицы, используемое в SQL запросе
 * als['table']	- имя таблицы
 * als['alias']	- имя таблицы, используемое в SQL запросе
 * $als['parent.alias']
 * $als['parent.refid']
 */
	protected function _buildDataSourceInfo($params, &$fields, &$aliases) {
		$errorMessage='Ошибка при обращении к DSAutoGenerator::_buildDataSourceInfo';

		$tableName=$params['tableName'];
		$isMain=$params['isMain'];
		if (!$tableName) throw new Exception($errorMessage.', не задан обязательный параметр tableName');
		unset($dsTable);
		$dsTable=&$this->dataStructure[$tableName];
		if (!$dsTable) throw new Exception($errorMessage.', недопустимое значение параметра tableName='.$tableName);

		$level=$params['level'];
		if (!$level) $level=0;
		if ($level>4) return false;

		$aliasName=$params['aliasName'];
		if (!$aliasName) $aliasName=$tableName;
		if (!$aliases[$aliasName]) {
			$als=Array();
			$als['table']=$tableName;
			$als['alias']=$aliasName;
			$aliases[$aliasName]=$als;
		}
		
		$lstRef=Array();
		// Пополняем $fields полями таблицы
		foreach($dsTable['fields'] as $fieldName=>$fld) {
			if ($fld['type']=='id') continue;
			if ($isMain && !$fld['ismain']) continue;
			$fld['alias']=$aliasName;
			$fields[]=$fld;
			if ($fld['type']=='ref') $lstRef[]=$fld;
		}
		// Рекурсивно пополняем $fields полями связанных таблиц
		foreach($lstRef as $keyRef=>$fldRef) {
			// формируем alias
			$refTable=$fldRef['reftable'];
			if (!$refTable) continue;
			if (!$this->dataStructure[$refTable]) continue;
			$refAlias=$refTable;
			if ($fldRef['link']) $refAlias=$fldRef['link'];
			$refAliasIndex=1;
			while($aliases[$refAlias]) {
				$refAlias=$refTable.'_'.$refAliasIndex;
				$refAliasIndex++;
			}
			$als=Array();
			$als['table']=$refTable;
			$als['alias']=$refAlias;
			$als['parent.alias']=$aliasName;
			$als['parent.refid']=$fldRef['name'];
			$aliases[$refAlias]=$als;
			
			$p=Array();
			$p['tableName']=$refTable;
			$p['aliasName']=$refAlias;
			$p['isMain']=true;
			$p['level']=$level+1;
			$this->_buildDataSourceInfo($p, $fields, $aliases);
		}
		return true;
	}
/** Записать строку в файл
 *
 * @param	string $fileName имя файла
 * @param	string $value содержимое
 */
	protected function writeFile($fileName, $value)	{
		$errorMessage='Ошибка при обращении к DSAutoGenerator::writeFile';
		if (!$handle = fopen($fileName, 'w')) throw new Exception($errorMessage.", не удалось создать файл {$fileName}");
		if (fwrite($handle, $value) === FALSE) throw new Exception($errorMessage.", не удалось произвести запись файл {$fileName}");
		fclose($handle);
	}
/** Сдвиг текстового блока вправо на заданное кол-во знаков табуляции
 *
 * @param	string	$str
 * @param	num	$tabShift
 * @return	string	сдвинутый текстовый блок
 */
	protected function strTabShift($str='', $tabShift=1) {
		$strTab='';
		for($i=0; $i<$tabShift; $i++) $strTab.="\t";
		$str=str_replace("\r", "", $str);
		$isCrStart=(substr($str,0,1)=="\n");
		if (!$isCrStart) {
			$str="\n".$str;
		}
		$from=Array(
			"\n<?php",
			"\n?>",
			"\n"
		);
		$to=Array(
			"\r<?php",
			"\r?>",
			"\n".$strTab,
		);
		$str=str_replace($from, $to, $str);
		$str=str_replace("\r", "\n", $str);
		if (!$isCrStart) {
			$str=substr($str,1);
		}
		return $str;
	}
}

/** Класс предок для модификатора DataSource при автогенерации
 *
 * Позволяет внести изменения в процесс автогенерации DataSource
 */
class DataSourceModify {
/** Модифицировать описания полей
 *
 * @param	Array	$fields - список полей
 * @return	Array модифицированный список полей
 */
	public function getFields($fields) {
		return $fields;
	}
/** Модифицировать секцию fields запроса select
 *
 * @param	string	$selectFields секция fields запроса select
 * @return	string модифицированная секция fields запроса select
 */
	public function getSelectFields($selectFields) {
		return $selectFields;
	}
/** Модифицировать секцию from запроса select
 *
 * @param	string	$selectFrom секция fields запроса select
 * @return	string модифицированная секция from запроса select
 */
	public function getSelectFrom($selectFrom) {
		return $selectFrom;
	}
/** Модифицировать секцию order by запроса select
 *
 * @param	string	$selectOrderBy секция order by запроса select
 * @return	string модифицированная секция order by запроса select
 */
	public function getSelectOrderBy($selectOrderBy) {
		return $selectOrderBy;
	}
}
/** Получить объект модификатора класса источника данных по имени
 *
 * @param	String	$name имя источника данных
 * @return	DataSourceModify объект модификатора класса источника данных
 */
function getDataSourceModify($name) {
	global $registerDataSourceModify;
	
	$str=$name;
	$str=str_replace('"','',$str);
	$str=str_replace("'",'',$str);
	$str=str_replace("`",'',$str);
	$str=str_replace('/','',$str);
	$str=str_replace("\\",'',$str);
	$str=str_replace('*','',$str);
	$str=str_replace('?','',$str);
	if ($name!=$str) throw new Exception("Недопустимое имя источника данных '{$name}'");
	if ($registerDataSource[$name]) return $registerDataSource[$name];

	$fileNameModify=pathConcat(getCfg('path.root'), getCfg('path.root.datasources'), 'modify', "{$name}-modify.php");
	if (file_exists($fileNameModify)) {
		$obj=include_once($fileNameModify);
		if ($obj instanceof DataSourceModify) $registerDataSourceModify[$name]=$obj;
	}
	
	$result=$registerDataSourceModify[$name];
	if (!$result) $result=null;
	return $result;
}
/// Кэш модификаторов DataSourceModify
$registerDataSourceModify=Array();
