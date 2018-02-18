<?php
/**
Глобальные конфигурационные настройки
@package module
@subpackage config
*/
$config=Array();

//--------------------------------------------------------------------
// Пути - все пути без завершающего слеша!!!
//--------------------------------------------------------------------
// Ссылка на корень проекта
$config['href.root']='';

// Относительный путь от точки входа до корня проекта !!! надо переопределять в точке входа !!!
$config['path.root']='';

// Путь от корня проекта до доступных ресурсов
$config['path.root.resource']='resource';
$config['path.root.g740client']='resource/g740client';

// Путь от корня проекта до точки входа сервера G740
$config['path.root.server']='server';
// Путь от корня проекта до контроллеров источников данных
$config['path.root.datasources']='server/datasources';
// Путь от корня проекта до контроллеров форм
$config['path.root.forms']='server/forms';
// Путь от корня проекта до контроллеров утилит
$config['path.root.utils']='server/utils';
// Путь от корня проекта до модулей проекта
$config['path.root.module']='server/module';
// Путь от корня проекта до папки экспорта и импорта
$config['path.root.export-import']='server/export-import';
// Путь от корня проекта до логов
$config['path.root.log']='server/log';

// Служебные настройки админки
$config['datasourceIsGUID']=false;	// не использовать GUID в качестве ID

// Отладка
$config['trace.sql']=false;			// трассировать все SQL запросы
$config['trace.error.sql']=false;	// логировать все ошибки SQL запросов

// Ключ шифрования md5
$config['crypt.md5.key']='1234567890';

// Зашифрованный пароль пользователя root
//$config['root.password']='d0d2bfdca84f95981893d0ab69a03969';
