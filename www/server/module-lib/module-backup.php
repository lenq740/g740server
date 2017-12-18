<?php
/**
Библиотека для BackUp, Restore таблиц базы данных через XML
@package module-lib
@subpackage module-backup
*/

/**
Сохранение таблицы в XML
@param	mixed[] para
<li>	para['xmlWriter']
<li>	para['tableName']
<li>	para['isEcho']
<li>	para['isOptimize']
*/
function saveTableToXmlWriter($para) {
	if (!$para) throw new Exception('Не задан para');
	$pdoDB=getPDO();
	$xmlWriter=$para['xmlWriter'];
	$tableName=$para['tableName'];
	$isEcho=$para['isEcho'];
	$isOptimize=$para['isOptimize'];
	
	$xmlWriter->startElement('table');
	$xmlWriter->writeAttribute('name', $tableName);

	$sql='select * from '.$tableName;
	$q=$pdoDB->pdo($sql);
	$i=0;
	while ($rec=$pdoDB->pdoFetch($q))
	{
		$xmlWriter->startElement('row');
		foreach($rec as $key=>$value) {
			$s=$value;
			$s=str_replace("\n",'~',$s);
			$xmlWriter->writeAttribute(strtolower($key),$s);
		}
		$xmlWriter->endElement();
		$i++;
		if ($isEcho && ($i % 500)==0) {
			$xmlWriter->flush();
			echo '. '; flush();
		}
	}
	$xmlWriter->endElement();
	$xmlWriter->flush();
	if ($isOptimize) {
		if ($isEcho) {
			echo 'optimize '; flush();
		}
		opimizeTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}
}
/**
Загрузка списка таблиц из XML
@param	mixed[] para
<li>	para['fileName']
<li>	para['tables']
<li>	para['isEcho']
<li>	para['isOptimize']
<li>	para['isDisableKeys']
*/
function loadTablesFromXmlReader($para) {
	if (!$para) throw new Exception('Не задан para');
	$fileName=$para['fileName'];
	if (!file_exists($fileName)) throw new Exception('Отсутствует файл '.$fileName);
	$isEcho=$para['isEcho'];
	$tables=$para['tables'];
	$tbl=Array();
	foreach($tables as $key=>$tableName) $tbl[strtolower($tableName)]=true;
	$xmlReader=new XMLReader();
	$xmlReader->open($fileName,'utf-8');
	while(true) {
		if($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName=='table') {
			$tableName=strtolower($xmlReader->getAttribute('name'));
			if ($tbl[$tableName]) {
				$p=Array();
				$p['xmlReader']=$xmlReader;
				$p['tableName']=$tableName;
				$p['isEcho']=$para['isEcho'];
				$p['isOptimize']=$para['isOptimize'];
				$p['isDisableKeys']=$para['isDisableKeys'];
				if ($isEcho) {
					echo '<div class="message">'.$tableName.': '; flush();
				}
				_loadTableFromXmlReader($p);
				if ($isEcho) {
					echo 'Ok!</div>'; flush();
					echo '<script>document.body.scrollIntoView(false)</script>'; flush();
				}
				unset($p);
				continue;
			}
			else {
				$xmlReader->next();
				continue;
			}
		}
		if (!$xmlReader->read()) break;
	}
	$xmlReader->close();
	unset($xmlReader);
}
/**
Загрузка таблицы из XML
@param	mixed[] para
<li>	para['xmlReader']
<li>	para['tableName']
<li>	para['fields']
<li>	para['isEcho']
<li>	para['isOptimize']
<li>	para['isDisableKeys']
*/
function _loadTableFromXmlReader($para) {
	if (!$para) throw new Exception('Не задан para');
	$pdoDB=getPDO();
	$xmlReader=$para['xmlReader'];
	$tableName=$para['tableName'];
	$fields=$para['fields'];
	$isEcho=$para['isEcho'];
	$isOptimize=$para['isOptimize'];
	$isDisableKeys=$para['isDisableKeys'];
	if (!$fields) $fields=Array();

	if ($xmlReader->nodeType != XMLReader::ELEMENT) throw new Exception('Недопустимый текущий элемент');
	if ($xmlReader->localName!='table') throw new Exception('Недопустимый текущий элемент');
	if (strtolower($xmlReader->getAttribute('name'))!=$tableName) throw new Exception('Недопустимый текущий элемент');
	
	if ($isDisableKeys) {
		if ($isEcho) {
			echo 'disable keys '; flush();
		}
		$pdoDB->pdo("lock table {$tableName} write");
		$pdoDB->pdo("alter table {$tableName} disable keys");
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}

	if ($isEcho) {
		echo 'delete '; flush();
	}
	$pdoDB->pdo('TRUNCATE TABLE '.$tableName);
	if ($isOptimize) opimizeTable($tableName);
	if ($isEcho) {
		echo 'ok '; flush();
	}

	$i=0;
	$xmlReader->read();
	if (!$pdoDB->inTransaction()) $pdoDB->beginTransaction();
	while(true) {
		if($xmlReader->nodeType == XMLReader::ELEMENT) {
			if ($xmlReader->localName=='table') break;
			if ($xmlReader->localName=='row'){
				$sqlFields='';
				$sqlValues='';
				$sqlDelim='';
				
				while ($xmlReader->moveToNextAttribute()) {
					$fieldName=strtolower($xmlReader->localName);
					$value=$xmlReader->value;
					if ($value!='') {
						$sqlFields=$sqlFields . $sqlDelim . '`'.$fieldName.'`';
						$value=str_replace('~', '\n', $value);
						$value=str_replace("'", '"', $value);
						$sqlValues=$sqlValues . $sqlDelim . "'" . $value . "'";
						$sqlDelim=',';
					}
				}
				if ($sqlDelim) {
					$sqlInsert='insert into ' . $tableName . ' (' . $sqlFields . ') values (' . $sqlValues . ')';
					$pdoDB->pdo($sqlInsert, "Ошибка при вставке строки в таблицу {$tableName}");
				}
				if ($isEcho && ($i % 500)==0) {
					$pdoDB->commit();
					echo '. '; flush();
					$pdoDB->beginTransaction();
				}
				$i++;
			}
		}
		if (!$xmlReader->read()) break;
	}
	$pdoDB->commit();

	if ($isDisableKeys) {
		if ($isEcho) {
			echo 'enable keys '; flush();
		}
		$pdoDB->pdo("alter table {$tableName} enable keys");
		$pdoDB->pdo("unlock tables");
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}

	if ($isEcho) {
		echo 'set max(id) '; flush();
	}
	$rec=$pdoDB->pdoFetch('select max(id) as id from '.$tableName);
	$maxId=$rec['id'];
	if (!$maxId) $maxId=0;
	$pdoDB->pdo('ALTER TABLE '.$tableName.' AUTO_INCREMENT='.$maxId);
	if ($isEcho) {
		echo 'ok '; flush();
	}
		
	if ($isOptimize) {
		if ($isEcho) {
			echo 'optimize '; flush();
		}
		opimizeTable($tableName);
		if ($isEcho) {
			echo 'ok '; flush();
		}
	}
}
/**
Оптимизация таблицы в базе
@param	String	$tableName
*/
function opimizeTable($tableName) {
	$pdoDB=getPDO();
	$sql='check table '.$tableName;
	$q=$pdoDB->pdo($sql);
	$sql='optimize table '.$tableName;
	$q=$pdoDB->pdo($sql);
}
