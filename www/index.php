<?php
session_start();
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9"/>
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Content-Language" content="ru"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Заготовка проекта</title>

	<link rel="stylesheet" type="text/css" href="js/g740/cssdojo/main.css"/>
	<link rel="stylesheet" type="text/css" href="js/g740/main.css"/>
	<link rel="stylesheet" type="text/css" href="resource/icons.css"/>

<!-- подключаем сжатую версию Dojo -->
	<script type="text/javascript">
		dojoConfig = {
			has: {
				"dojo-firebug": true,
				"dojo-debug-messages": true
			},
			cacheBust: true,
			parseOnLoad: false,
			async: true,
			baseUrl: "js/dojocompressed",
			packages: [
				{
					name: 'g740',
					location: '../g740'
				}
			]
		};
	</script>
	<script type="text/javascript" src="js/dojocompressed/dojo.js.uncompressed.js"></script>
	<script type="text/javascript" src="js/dojocompressed/g740-dojo.js"></script>
</head>
<body class="g740 app-color-red">
<!-- Выделяем место под размещение главной формы приложения -->
	<div id="FormPanelMain"></div>
<!--[if IE]>
<script>
	document.documentElement.className+=' IE';
</script>
<![endif]-->
	<script>
		require(
			[
				'g740',
				'dojo/domReady!'
			],
			function() {
// Тут подстраиваем клиентскую оболочку под конкретные особенности проекта
				{
					var conf=g740.config;
					conf['urlServer']='server/';					// Точка входа для управляющих серверных скриптов
					//conf['mainFormName']='formMain';				// Имя главной формы приложения
					conf['mainFormName']='formMainWithMenuBar';		// Вариант главной экранной формы приложения с верхним меню вместо древовидного
					conf['mainFormDomNode']='FormPanelMain';		// Узел DOM, в ктором размещается главная форма приложения
					
					// Настройка диалога авторизации
					var confDialogLogin=conf['dialogLogin'];
					confDialogLogin.loginUrl='resource/logoscreen/';	// Путь до HTML страницы, на фоне которой должен работать диалог аунтетификации
					confDialogLogin.isReloadBeforeLogin=false;			// Перед аунтетификацией не надо выполнять полную перечитку
				}

// Расширяем стандартный набор иконок, которые можно использовать в кнопочках и узлах дерева
				{
					var icons=g740.icons._items;
					icons['disabled']='starter-icons-disabled';
				}
				g740.application.doG740ShowForm();
			}
		);
	</script>
</body>
</html>