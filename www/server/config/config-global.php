<?php
/**
Глобальные конфигурационные настройки
@package module
@subpackage config
*/
$config=Array();

// Служебные настройки админки
$config['datasourceIsGUID']=false;					// не использовать GUID в качестве ID
$config['pathDataSources']='datasources';			// Путь до источников данных от точки входа сервера
$config['pathForm']='forms';						// Путь до экранных форм от точки входа сервера

// Отладка
$config['trace.sql']=false;			// трассировать все SQL запросы
$config['trace.error.sql']=false;	// логировать все ошибки SQL запросов

// Ключ шифрования md5
$config['crypt.md5.key']='1234567890';

// Пути относительно server
$config['path.import']='import';
$config['path.import.backup']='backup';
?>