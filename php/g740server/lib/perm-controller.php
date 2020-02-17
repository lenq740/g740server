<?php
/**
 * @file
 * G740Server, контроллер прав
 *
 * @copyright 2018-2020 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

require_once('dsconnector.php');

/** Проверить доступность требуемой функциональности по правам
 *
 * @param	String	$permMode режим по правам
 * @param	String	$permOperation операция
 * @return	Boolean доступность требуемой функциональности
 */
function getPerm($permMode, $permOperation='') {
	$obj=getPermController();
	$result=false;
	if ($obj) $result=$obj->getPerm($permMode, $permOperation);
	return $result;
}
/** Выполнить аутентификацию пользователя
 *
 * @param	String	$login
 * @param	String	$password
 * @return	Boolean	успешность аутентификации
 */
function execConnect($login, $password) {
	$obj=getPermController();
	if (!$obj) return false;
	return $obj->execConnect($login, $password);
}
/** Сбросить аутентифицированного пользователя
 *
 * @return	Boolean	успешность
 */
function execDisconnect() {
	$obj=getPermController();
	if (!$obj) return false;
	return $obj->execDisconnect();
}
/** В течении одного сеанса работать от имени пользователя root
 *
 * @return	Boolean	успешность
 */
function execConnectAsRoot() {
	$obj=getPermController();
	if (!$obj) return false;
	return $obj->execConnectAsRoot();
}
/** Получить параметр, сохраненный для аутентифицированного пользователя
 *
 * @param	String	$name имя параметра
 * @param	String	$default значение по умолчанию
 * @return	String	значение параметра
 */
function getPP($name, $default='') {
	$obj=getPermController();
	if (!$obj) return $default;
	return $obj->getPP($name, $default);
}

/** Класс контроллер прав
 */
class PermController extends DSConnector{
/// Конструктор, регистрация экземпляра класса
	function __construct() {
		global $_objPermController;
		$_objPermController=$this;
	}
/** Проверить доступность требуемой функциональности по правам
 *
 * @param	String	$mode режим по правам
 * @param	String	$operation операция
 * @return	Boolean доступность требуемой функциональности
 */
	public function getPerm($mode, $operation) {
		if (!getPP('isconnected')) return false;
		if ($mode=='readonly') {
			if ($operation=='read') return true;
			return false;
		}
		if (getPP('isroot')) return true;
		if ($mode=='connected') return true;
		if ($mode=='root') {
			return false;
		}
		if ($mode=='adm') {
			if (getPP('isadm')) return true;
			return false;
		}
		if ($mode=='rootref') {
			if ($operation=='read') return true;
			return false;
		}
		if ($mode=='admref') {
			if ($operation=='read') return true;
			if (getPP('isadm')) return true;
			return false;
		}
		if ($mode=='sysdblog') {
			if ($operation=='read') {
				if (getPP('isadm')) return true;
			}
			return false;
		}
		return false;
	}
/** Выполнить аутентификацию пользователя
 *
 * @param	String	$login
 * @param	String	$password
 * @return	Boolean	успешность аутентификации
 */
	public function execConnect($login, $password) {
		$this->execDisconnect();
		session_start();
		try {
			if ($login=='root' && $password=='1') {
				$_SESSION['connect_isconnected']=true;
				$_SESSION['connect_isroot']=true;
				$_SESSION['connect_login']='root';
				$this->onAfterConnection();
				return true;
			}
			if ($login=='admin' && $password=='1') {
				$_SESSION['connect_isconnected']=true;
				$_SESSION['connect_isadm']=true;
				$_SESSION['connect_login']='admin';
				$this->onAfterConnection();
				return true;
			}
		}
		finally {
			if (getCfg('csrftoken.enabled')) $_SESSION['connect_csrftoken']=getGUID();
			session_write_close();
		}
		return false;
	}
/** Сбросить аутентифицированного пользователя
 *
 * @return	Boolean	успешность
 */
	public function execDisconnect() {
		session_start();
		try {
			$lstClear=Array();
			foreach($_SESSION as $name=>$value) {
				if (substr($name, 0, strlen('connect_'))=='connect_') $lstClear[]=$name;
			}
			foreach($lstClear as $name) unset($_SESSION[$name]);
		}
		finally {
			session_write_close();
		}
		return true;
	}
/** В течении одного сеанса работать от имени пользователя root
 */
	public function execConnectAsRoot() {
		$this->setPP('login', getCfg('root.fio','madmin'), true);
		$this->setPP('userid', getCfg('root.id',-999), true);
		$this->setPP('isconnected', true , true);
		$this->setPP('isroot', true , true);
	}
/** Получить параметр, сохраненный для аутентифицированного пользователя
 *
 * @param	String	$name имя параметра
 * @param	String	$default значение по умолчанию
 * @return	String	значение параметра
 */
	public function getPP($name, $default='') {
		$result=$default;
		if (array_key_exists("connect_{$name}", $_SESSION)) $result=$_SESSION["connect_{$name}"];
		if (array_key_exists($name, $this->localPP)) $result=$this->localPP[$name];
		return $result;
		return $result;
	}
/** Сохранить параметр аутентифицированного пользователя
 *
 * @param	String	$name имя параметра
 * @param	String	$value значение параметра
 * @param	Boolean	$isLocal сохранить локально, только на время текущего сеанса
 * @return	Boolean	успешность
 */
	public function setPP($name, $value, $isLocal=false) {
		if ($isLocal) {
			$this->localPP[$name]=$value;
		}
		else {
			$_SESSION["connect_{$name}"]=$value;
		}
		return true;
	}
/** Событие, вызываемое посля каждого успешного соединения, используется для выполнения плановых работ
 *
 */
	protected function onAfterConnection() {
		deleteOldLogFiles();
	}

/** Обработка логирования данных
 *
 * @param	Array	$info
 *
 * - $info['table'] - таблица
 * - $info['operation'] - операция (ins, upd, del)
 * - $info['rowNew'] - ассоциативный массив новых значений
 * - $info['rowOld'] - ассоциативный массив старых значений
 * - $info['rowFields'] - список отслеживаемых в rowNew и rowOld полей
 * - $info['child'] - дочерняя таблица
 * - $info['childid'] - id дочерней строки
 * - $info['childname'] - имя поля дочерней строки
 * - $info['childvalue'] - значение поля дочерней строки
 */
	public function doLog($info=Array()) {
		$errorMessage='Ошибка при обращении к логированию данных';
		$sqlUser=getPP('login');
		$table=$info['table'];
		if (!$table) throw new Exception("{$errorMessage}. Не задан обязательный параметр table");
		try {
			$dataSourceTable=getDataSource($table);
		}
		catch(Exception $e) {
		}
		if (!$dataSourceTable) throw new Exception("{$errorMessage}. Не найден источник данных для таблицы '{$table}'");
		if (!$dataSourceTable->isLogEnabled) return false;
		$sqlTable=$this->str2Sql($table);
		
		$operation=$info['operation'];
		$rowOld=$info['rowOld'] ?? Array();
		$rowNew=$info['rowNew'] ?? Array();
		$row=($rowNew['id']) ? $rowNew : $rowOld;
		
		$lstParentInfo=$dataSourceTable->getLogParentInfo($row);
		if (is_array($lstParentInfo[0])) {
			$parentInfo=$lstParentInfo[0];
		}
		else {
			$parentInfo=$lstParentInfo;
			$lstParentInfo=Array($parentInfo);
		}

		$sqlParent='';
		$sqlParentId='';
		$parentInfo=$lstParentInfo[0];
		if ($parentInfo && $parentInfo['parent'] && $parentInfo['parentid']) {
			$sqlParent=$this->str2Sql($parentInfo['parent']);
			$sqlParentId=$this->str2Sql($parentInfo['parentid']);
		}
		
		$d=new DateTime();
		$sqlD=$d->format('Y-m-d');
		$sqlT=$d->format('H:i:s');

		$driverName=$this->getDriverName();
		$D0='';
		$D1='';
		if ($driverName=='mysql') {
			$D0='`';
			$D1='`';
		}
		else if ($driverName=='sqlsrv') {
			$D0='[';
			$D1=']';
		}
		else if ($driverName=='pgsql') {
			$D0='"';
			$D1='"';
		}
		else {
			throw new Exception("Неизвестный драйвер базы данных '{$driverName}'");
		}


		$isLogExecuted=false;
		if ($operation=='del') {
			$sqlRowId=$this->str2Sql($rowOld['id']);
			if (!$sqlRowId) return false;
			$value=$dataSourceTable->getLogRow2Text($rowOld);
			if (!$value) return false;
			if ($dataSourceTable->isLogEnabled!=='virtual') {
				$sqlValue=$this->str2Sql(mb_substr($value,0,1024,'utf-8'));
				$sql=<<<SQL
insert into sysdblog(
	{$D0}parent{$D1},
	{$D0}parentid{$D1},
	{$D0}table{$D1},
	{$D0}field{$D1},
	{$D0}rowid{$D1},
	{$D0}operation{$D1},
	{$D0}value{$D1},
	{$D0}user{$D1},
	{$D0}d{$D1},
	{$D0}t{$D1}
)
values (
	'{$sqlParent}',
	'{$sqlParentId}',
	'{$sqlTable}',
	'',
	'{$sqlRowId}',
	'del',
	'{$sqlValue}',
	'{$sqlUser}',
	'{$sqlD}',
	'{$sqlT}'
)
SQL;
				$this->pdo($sql);
			}
			$isLogExecuted=true;
		}
		else {
			$sqlRowId=$this->str2Sql($rowNew['id']);
			if (!$sqlRowId) return false;
			$lstFields=$dataSourceTable->getLogFields();
			$sqlOperation=($operation=='ins')?'ins':'upd';
			$sqlChild='';
			$sqlChildId='';
			if ($info['child'] && $info['childid']) {
				$sqlChild=$this->str2Sql($info['child']);
				$sqlChildId=$this->str2Sql($info['childid']);
			}
			$lstRowFields=Array();
			if (isset($info['rowFields'])) {
				if (is_array($info['rowFields'])) {
					foreach($info['rowFields'] as $fieldName) {
						$lstRowFields[$fieldName]=$fieldName;
					}
				}
				else {
					$fieldName=$info['rowFields'];
					$lstRowFields[$fieldName]=$fieldName;
				}
			}
			
			foreach($lstFields as $fld) {
				$field=$fld['name'];
				if (!$field) continue;
				if ($lstRowFields && !$lstRowFields[$field]) continue;
				$value='';
				if ($operation=='ins') {
					$value=$rowNew[$field];
					if (!$value) continue;
				}
				else {
					if ($rowOld && $rowNew[$field]==$rowOld[$field]) continue;
					$value=$rowNew[$field];
				}
				
				if ($dataSourceTable->isLogEnabled!=='virtual') {
					if ($fld['type']=='date') {
						$value=date2Html(mb_substr($value, 0, 10));
					}
					if ($fld['type']=='check') {
						$value=($value) ? 'Да' : 'Нет';
					}
					$sqlField=$this->str2Sql($field);
					$sqlValue=$this->str2Sql($value);
					$sql=<<<SQL
insert into sysdblog(
	{$D0}parent{$D1},
	{$D0}parentid{$D1},
	{$D0}table{$D1},
	{$D0}field{$D1},
	{$D0}rowid{$D1},
	{$D0}operation{$D1},
	{$D0}value{$D1},
	{$D0}child{$D1},
	{$D0}childid{$D1},
	{$D0}user{$D1},
	{$D0}d{$D1},
	{$D0}t{$D1}
)
values (
	'{$sqlParent}',
	'{$sqlParentId}',
	'{$sqlTable}',
	'{$sqlField}',
	'{$sqlRowId}',
	'{$sqlOperation}',
	'{$sqlValue}',
	'{$sqlChild}',
	'{$sqlChildId}',
	'{$sqlUser}',
	'{$sqlD}',
	'{$sqlT}'
)
SQL;
					$this->pdo($sql);
				}
				$isLogExecuted=true;
			}
		}
	
		if (!$isLogExecuted) return true;
		
		if ($operation=='del') {
			$value='Удалена строка: '.$dataSourceTable->getLogRow2Text($rowOld);
			$childid=$rowOld['id'];
		}
		else if ($operation=='ins') {
			$value='Добавлена строка: '.$dataSourceTable->getLogRow2Text($rowNew);
			$childid=$rowNew['id'];
		}
		else {
			$value='Изменена строка: '.$dataSourceTable->getLogRow2Text($rowNew);
			$childid=$rowNew['id'];
		}

		foreach($lstParentInfo as $parentInfo) {
			$parent=$parentInfo['parent'];
			if (!$parent) continue;
			$parentid=$parentInfo['parentid'];
			if (!$parentid) continue;
			$fieldName=$parentInfo['parentfield'] ? $parentInfo['parentfield'] : $table;

			try {
				$dataSourceParent=getDataSource($parent);
			}
			catch(Exception $e) {
			}
			if (!$dataSourceParent) continue;
			if (!$dataSourceParent->isLogEnabled) continue;
			if ($operation=='del') {
				if ($dataSourceParent->getIsDeleteExecuted()) continue;
			}
			
			$rowParent=$dataSourceParent->getRow($parentid);
			if (!$rowParent['id']) continue;
			$rowParent[$fieldName]=$value;
			$p=Array(
				'operation'=>'upd',
				'rowNew'=>$rowParent,
				'rowFields'=>[$fieldName]
			);
			if ($dataSourceTable->isLogEnabled!=='virtual' && $childid) {
				$p['child']=$table;
				$p['childid']=$childid;
			}
			$dataSourceParent->doLog($p);
		}
		
	}
	
/// Локальные переменные прав, которые действуют на один сеанс, и не должны быть сохранены в сессии
	protected $localPP=Array();
}

/** Получить актуальный контроллер прав
 *
 * @return	PermController объект контроллера прав
 */
function getPermController() {
	global $_objPermController;
	if ($_objPermController instanceof PermController) return $_objPermController;
	
	$fileNamePermController=pathConcat(getCfg('path.root'), getCfg('path.root.php'), 'perm.php');
	if (file_exists($fileNamePermController)) {
		$_objPermController=include_once($fileNamePermController);
		if ($_objPermController instanceof PermController) return $_objPermController;
	}
	$_objPermController=new PermController();
	return $_objPermController;
}
/// Актуальный контроллер прав
$_objPermController=null;
