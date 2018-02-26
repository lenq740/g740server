<?php
session_start();
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
require_once('server/config/.config.php');
require_once('server/lib/lib-base.php');
$config['path.root']=pathConcat('',getCfg('path.root'));
$pathResource=pathConcat(getCfg('path.root'),getCfg('path.root.resource'));
$pathG740Client=pathConcat($pathResource,getCfg('g740client'));
$pathG740Server=pathConcat(getCfg('path.root'),getCfg('path.root.server'),'index.php');
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9"/>
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Content-Language" content="ru"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Заготовка проекта</title>

	<link rel="stylesheet" type="text/css" href="<?php echo $pathG740Client; ?>/js/g740/cssdojo/main.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $pathG740Client; ?>/js/g740/main.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $pathG740Client; ?>/icons/icons.css"/>

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
			baseUrl: "<?php echo $pathG740Client; ?>/js/dojocompressed",
			packages: [
				{
					name: 'g740',
					location: '../g740'
				}
			]
		};
	</script>
	<script type="text/javascript" src="<?php echo $pathG740Client; ?>/js/dojocompressed/dojo.js.uncompressed.js"></script>
	<script type="text/javascript" src="<?php echo $pathG740Client; ?>/js/dojocompressed/g740-dojo.js"></script>
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
					conf['urlServer']='<?php echo $pathG740Server; ?>';	// Точка входа для управляющих серверных скриптов
					conf['mainFormName']='formMainWithMenuBar';			// Вариант главной экранной формы приложения с верхним меню вместо древовидного
					conf['mainFormDomNode']='FormPanelMain';			// Узел DOM, в ктором размещается главная форма приложения
					
					// Настройка диалога авторизации
					var confDialogLogin=conf['dialogLogin'];
					confDialogLogin.loginUrl='<?php echo $pathResource; ?>/logoscreen/';	// Путь до HTML страницы, на фоне которой должен работать диалог аунтетификации
					confDialogLogin.isReloadBeforeLogin=true;			// Перед аунтетификацией не надо выполнять полную перечитку
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