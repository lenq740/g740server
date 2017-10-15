<?php
session_start();
ini_set('display_errors','On');
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
header("Content-type: text/xml; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
require_once('config/.config.php');
require_once('module/module-lib-base.php');
require_once('module/module-lib-g740server.php');
require_once('module/module-perm.php');
require_once('module/module-datasource.php');
require_once('module/module-form.php');
$objResponseWriter=initObjResponseWriter();
try {
	initDocRequest();				// Считываем запрос
	$pdoDB=new PDODataConnectorMySql(
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost')
	); // Устанавливаем соединение с базой данных
	$pdoDB->beginTransaction();
	try {
		$pdoDB->openConnection();
		for ($xmlRequest=$rootRequest->firstChild; $xmlRequest!=null; $xmlRequest=$xmlRequest->nextSibling) {
			if ($xmlRequest->nodeName!='request') continue;

			// Начитка основных параметров запроса
			{
				$requestName=xmlGetAttr($xmlRequest,'name','');
				$requestMode=xmlGetAttr($xmlRequest,'mode','');
				$params=getParams($xmlRequest);		// все переданные параметры, в формате PHP
				$params['paginator.from']=xmlGetAttr($xmlRequest,'paginator.from','');
				$params['paginator.count']=xmlGetAttr($xmlRequest,'paginator.count','');
				
				$form=$pdoDB->str2Sql(xmlGetAttr($xmlRequest,'form',''));
				if ($form) {
					$str=$pdoDB->str2Sql($form);
					$str=str_replace('/','',$str);
					$str=str_replace("\\",'',$str);
					$str=str_replace('*','',$str);
					$str=str_replace('?','',$str);
					if ($form!=$str) throw new Exception("Имя формы '{$form}' содержит недопустимые символы");
					if ($str!=str2Attr($str)) throw new Exception("Имя формы '{$form}' содержит недопустимые символы");
				}
				$rowset=xmlGetAttr($xmlRequest,'rowset','');
				if ($rowset) {
					$str=$pdoDB->str2Sql($rowset);
					if ($rowset!=$str) throw new Exception("Имя набора строк '{$rowset}' содержит недопустимые символы");
					if ($str!=str2Attr($str)) throw new Exception("Имя набора строк '{$rowset}' содержит недопустимые символы");
				}
				$datasource=xmlGetAttr($xmlRequest,'datasource','');
				if ($datasource) {
					$str=$pdoDB->str2Sql($datasource);
					$str=str_replace('/','',$str);
					$str=str_replace("\\",'',$str);
					$str=str_replace('*','',$str);
					$str=str_replace('?','',$str);
					if ($str!=$datasource) throw new Exception("Имя источника данных '{$datasource}' содержит недопустимые символы");
					if ($str!=str2Attr($str)) throw new Exception("Имя источника данных '{$datasource}' содержит недопустимые символы");
				}

				$params['id']=xmlGetAttr($xmlRequest,'id','');
				if ($params['id']) {
					if ($params['id']!=$pdoDB->str2Sql($params['id'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['id']}' параметра id содержит недопустимые символы");
					if ($params['id']!=str2Attr($params['id'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['id']}' параметра id содержит недопустимые символы");
				}
				$params['row.new']=xmlGetAttr($xmlRequest,'row.new','');
				if ($params['row.new']!='') $params['row.new']=true;

				// параметры, специфичные для узлов дерева
				$params['row.type']=xmlGetAttr($xmlRequest,'row.type','');
				if ($params['row.type']) {
					if ($params['row.type']!=$pdoDB->str2Sql($params['row.type'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['row.type']}' параметра row.type содержит недопустимые символы");
					if ($params['row.type']!=str2Attr($params['row.type'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['row.type']}' параметра row.type содержит недопустимые символы");
				}
				$params['row.parentid']=xmlGetAttr($xmlRequest,'row.parentid','');
				if ($params['row.parentid']) {
					if ($params['row.parentid']!=$pdoDB->str2Sql($params['row.parentid'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['row.parentid']}' параметра row.parentid содержит недопустимые символы");
					if ($params['row.parentid']!=str2Attr($params['row.parentid'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['row.parentid']}' параметра row.parentid содержит недопустимые символы");
				}
				$params['row.parenttype']=xmlGetAttr($xmlRequest,'row.parenttype','');
				if ($params['row.parenttype']) {
					if ($params['row.parenttype']!=$pdoDB->str2Sql($params['row.parenttype'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['row.parenttype']}' параметра row.parenttype содержит недопустимые символы");
					if ($params['row.parenttype']!=str2Attr($params['row.parenttype'])) throw new Exception("В источнике данных '{$datasource}' значение '{$params['row.parenttype']}' параметра row.parenttype содержит недопустимые символы");
				}
				$params['#request.name']=$requestName;
				$params['#request.mode']=$requestMode;
				$params['#request.form']=$form;
				$params['#request.rowset']=$rowset;
				$params['#request.datasource']=$datasource;
			}

			// Обработка запросов уровнем приложения
			{
				// Обработка запроса на авторизацию
				if ($requestName=='connect') {
					execConnect($params);
					$objResponseWriter->startElement('response');
					$objResponseWriter->writeAttribute('name', 'ok');
					$objResponseWriter->endElement();
					continue;
				}
				if ($requestName=='disconnect') {
					execDisconnect($params);
					throw new Exception('');
				}
				// Проверка актуальности авторизации
				if (!getPerm('connected','')) throw new Exception('');
				// Запрос на описание структуры экранной формы
				if ($requestName=='form') {
					if (!$form) throw new Exception("В запросе '{$requestName}' не задано имя формы");
					$objForm=getFormController($form);
					$objResponseWriter->startElement('response');
					$objResponseWriter->writeAttribute('name', 'ok');
					$def=$objForm->getStrXmlDefinition($params);
					writeXml($def);
					$objResponseWriter->endElement();
					continue;
				}
			}
			
			// Обработка запросов уровнем источника данных и уровнем формы
			{
				//if (!$datasource) throw new Exception("В запросе '{$requestName}' не задано имя источника данных");
				$events=Array(); // ассоциативный массив для передаче от источника данных в форму важных событий, на которое форма может захотеть среагировать
				if ($datasource && $datasource!="filter") {
					$objDataSource=getDataSource($datasource);
					if ($objDataSource) {
						$objDataSource->writeXmlExec($params);
					} else {
						throw new Exception("Нет обработчика для источника данных '{$datasource}'");
					}
				}
				// Обработка запросов уровнем формы
				if ($form) {
					$objForm=getFormController($form);
					$objForm->go($params, $events);
				}
				continue;
			}
			throw new Exception("Неизвестный запрос {$requestName}");
		}
		$pdoDB->closeConnection();
		if ($pdoDB->inTransaction()) $pdoDB->commit();
	}
	catch (Exception $e) {
		if ($pdoDB->inTransaction()) $pdoDB->rollBack();
		throw new Exception($e->getMessage());
	}
}
catch (Exception $e) {
	unset($objResponseWriter);
	$objResponseWriter=initObjResponseWriter();
	$objResponseWriter->startElement('response');
	{
		if (getPerm('connected','')) {
			$objResponseWriter->writeAttribute('name', 'error');
		} else {
			$objResponseWriter->writeAttribute('name', 'disconnected');
		}
		$objResponseWriter->writeAttribute('message', $e->getMessage());
	}
	$objResponseWriter->endElement();
}
$objResponseWriter->endElement();
$objResponseWriter->endDocument();
echo $objResponseWriter->outputMemory(true);
?>