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

/**
Вернуть значение настроечной константы
@param	String	$name имя настройки
@param	String	$default значение по умолчанию
@return	String значение настроечной константы
*/
function getCfg($name, $default='') {
	global $config;
	if (isset($config[$name])) return $config[$name];
	return $default;
}
?>