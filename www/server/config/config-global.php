<?php
/**
Глобальные конфигурационные настройки
@package module
@subpackage config
*/
$config=Array();

// Пути
$config['path.root']='';
$config['path.server']='server';
$config['path.datasources']='datasources';
$config['path.forms']='forms';
$config['path.utils']='utils';

$config['path.import']='import';
$config['path.import.backup']='backup';


// Служебные настройки админки
$config['datasourceIsGUID']=false;	// не использовать GUID в качестве ID

// Отладка
$config['trace.sql']=true;			// трассировать все SQL запросы
$config['trace.error.sql']=false;	// логировать все ошибки SQL запросов

// Ключ шифрования md5
$config['crypt.md5.key']='1234567890';

// Зашифрованный пароль пользователя root
//$config['root.password']='d0d2bfdca84f95981893d0ab69a03969';
