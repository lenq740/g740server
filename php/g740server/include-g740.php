<?php
/**
 * @file
 * G740Server, include точки входа для обработки запросов протокола G740.
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */

///@cond
error_reporting((E_ALL | E_STRICT) & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors','On');
header("Content-type: text/xml; charset=utf-8");
header("X-UA-Compatible: IE=edge");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

include_once("{$config['path.root.g740server']}/lib/lib-base.php");

// задаем корень проекта относительно точки входа
$config['path.root']=realpath(getCfg('path.root'));

// Запускаем логирование ошибок в файл
$path=pathConcat(getCfg('path.root'), getCfg('path.root.log','log'));
if (!is_dir($path)) mkdir($path, 0777, true);
ini_set('display_errors','Off');
ini_set('log_errors','On');
$logFileName=pathConcat(getCfg('path.root'),getCfg('path.root.log'),date('Y-m-d').'-phperror.log');
ini_set('error_log',$logFileName);
$timeZone=getCfg('timezone');
if ($timeZone) ini_set('date.timezone', $timeZone);

if (getCfg('project.id')) ini_set('session.name',getCfg('project.id'));
session_start();
session_write_close();

includeLib('perm-controller.php');
includeLib('datasource-controller.php');
includeLib('form-controller.php');

$objResponseWriter=initObjResponseWriter();
try {
	initDocRequest();				// Считываем запрос
	$pdoDB=newPDODataConnector(
		getCfg('sqlDriverName'),
		getCfg('sqlDbName'),
		getCfg('sqlLogin'),
		getCfg('sqlPassword'),
		getCfg('sqlCharSet'),
		getCfg('sqlHost'),
		getCfg('sqlPort')
	); // Устанавливаем соединение с базой данных

	regPDO($pdoDB,'default');
	$pdoDB->beginTransaction();
	try {
		if (getCfg('csrftoken.enabled')) $requestToken=xmlGetAttr($rootRequest,'csrftoken','');
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
					if (!execConnect($params['login'],$params['password'])) throw new Exception('Неверный логин, пароль ...');
					if (getCfg('csrftoken.enabled') && getPerm('connected','')) {
						$value=getPP('csrftoken');
						if (!$value) {
							execDisconnect();
							throw new Exception('Внутренняя ошибка авторизации. Процедура execConnect() не проинициализировала csrftoken!!!');
						}
						$objResponseWriter->writeAttribute('csrftoken', $value);
					}
					$objResponseWriter->startElement('response');
					$objResponseWriter->writeAttribute('name', 'ok');
					$objResponseWriter->endElement();
					continue;
				}
				if ($requestName=='disconnect') {
					execDisconnect();
					throw new Exception('');
				}
				if ($requestName=='form' && $form=='disconnect') {
					execDisconnect();
					throw new Exception('');
				}
				// Проверка актуальности авторизации
				if (!getPerm('connected','')) throw new Exception('');
				
				if (getCfg('csrftoken.enabled')) {
					if (!getPP('csrftoken')) {
						execDisconnect();
						throw new Exception('Внутренняя ошибка инициализации csrftoken!!!');
					}
					if (getPP('csrftoken')!=$requestToken) {
						execDisconnect();
						throw new Exception('Обнаружена попытка хакерской атаки посредством CSRF уязвимости, соединение прервано');
					}
				}
				
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
		if ($pdoDB->inTransaction()) {
			if (!$pdoDB->commit()) throw new Exception('Не удалось подтвердить транзакцию...');
		}
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
///@endcond