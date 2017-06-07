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
<body class="g740">
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
					conf['isTraceEnabled']=false;				// Включен режим трассировки через FireBug
					conf['urlServer']='server/';				// Точка входа для управляющих серверных скриптов
					conf['mainFormName']='formMain';			// Имя главной формы приложения
					conf['mainFormDomNode']='FormPanelMain';	// Узел DOM, в ктором размещается главная форма приложения
					
					// Настройка диалога авторизации
					var confDialogLogin=conf['dialogLogin'];
					confDialogLogin.loginUrl='resource/logoscreen/';	// Путь до HTML страницы, на фоне которой должен работать диалог авторизации
				}

// Расширяем стандартный набор иконок, которые можно использовать в кнопочках и узлах дерева
				{
					// стандартный набор иконок размещен в client/dojog740/config.js в объекте g740.icons
					// имени иконки ставим в соответствие имя css класса
					// эти классы должны быть объявлены в client/dojog740/icons.css
					// чтобы имена классов не перемешивались, лучше их именовать по шаблону: g740-icons-<имя файла иконки>
					// сами файлы иконок лежат в client/dojog740/icons/ в формате png, прозрачный фон, размер 16x16px
					var icons=g740.icons._items;
/*
					icons['disabled']='sportbaby-icons-disabled';
					icons['sys']='sportbaby-icons-sys';
					icons['linkgo']='sportbaby-icons-linkgo';
					
					icons['geo']='sportbaby-icons-geo';
					icons['geocountry']='sportbaby-icons-geocountry';
					icons['georegion']='sportbaby-icons-georegion';
					icons['geookrug']='sportbaby-icons-geookrug';
					icons['geotown']='sportbaby-icons-geotown';
					icons['geotownmain']='sportbaby-icons-geotownmain';
					icons['geotown-off']='sportbaby-icons-geotown-off';
					icons['delivery']='sportbaby-icons-delivery';
					
					icons['traderubric']='sportbaby-icons-traderubric';
					icons['tradesection']='sportbaby-icons-tradesection';
					icons['tradeset']='sportbaby-icons-tradeset';
					icons['tradesetlost']='sportbaby-icons-tradesetlost';
					icons['trades2s']='sportbaby-icons-trades2s';
					icons['trade']='sportbaby-icons-trade';
					icons['recyclebin']='sportbaby-icons-recyclebin';
					icons['recalc']='sportbaby-icons-recalc';
					icons['import']='sportbaby-icons-import';
					icons['export']='sportbaby-icons-export';
					icons['load']='sportbaby-icons-load';
*/
				}

				g740.application.doG740ShowForm();
			}
		);
	</script>
</body>
</html>