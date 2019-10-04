<?php
/**
 * @file
 * Глобальные конфигурационные настройки
 */

/// Название пректа краткое
$config['project.name']='Сервер G740';
/// Название пректа подробное
$config['project.description']='Заготовка для развертывания проекта';
/// id проекта, если задан, переопределяет PHPSESSIONID
$config['project.id']='';
/// html страница логотипа, на фоне которой открывается диалог логина
$config['project.dialogLogin.loginUrl']='/resource/logoscreen/';

/// список дополнительных подключаемых иконкок
/*
$config['project.icons.css']=Array(
	'anketa'=>'project-icons-anketa',
	'calendar'=>'project-icons-calendar',
);
*/

/// список дополнительных подключаемых иконкок
$config['project.icons.file']=Array(
	'anketa'=>Array('anketa.png','anketa-white.png'),
	'calendar'=>Array('calendar.png','calendar-white.png')
);


///@cond
// Ключ шифрования md5
$config['crypt.md5.key']='1234567890';
// Зашифрованный пароль пользователя root
//$config['root.password']='d0d2bfdca84f95981893d0ab69a03969';
///@endcond
