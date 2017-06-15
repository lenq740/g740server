//-----------------------------------------------------------------------------
// Конфигурационные настройки
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};

// Общие настройки проекта
		g740.config={
			urlServer: 'server/',				// Точка входа для управляющих серверных скриптов
			urlPhpInfo: 'server/phpinfo.php',	// Путь до утилиты phpinfo
			mainFormName: 'formMain',			// Имя главной формы приложения
			mainFormDomNode: 'FormPanelMain',	// Узел DOM, в ктором размещается главная форма приложения
			timeoutRefreshChilds: 300,			// Время ожидания перед перечиткой дочерних наборов строк
			timeoutMaxRequest: 10000,			// Максимальное время ожидания ответа
			isTraceEnabled: false,				// Включен режим трассировки
			charwidth: 9,						// усредненная ширина символа в пикселях
			charlabelwidth: 7,					// усредненная ширина символа в пикселях для метки
			charheight: 14,						// высота символа в пикселях
			markNewStyle: false,                // выполнять комманду mark в новом стиле - на сервере, с множественным выбором

			mainFormLoginUrl: '',				// Путь до HTML страницы, на фоне которой должен работать диалог авторизации
			dialogLogin: {
				loginUrl: '',
				iconUrl: '',
				isEnabled: true,								// Показывать стандартный диалог авторизации
				width: '350px',									// ширина диалога
				height: '180px',								// высота диалога
				iconWidth: '128px',								// ширина иконки
				title: ''
			},
			init: function () {
			    g740.rowsetRequestInfo.init();
			}
		};
// Поддержка иконок для дерева, кнопочек и меню, нужна таблица стилей icons.css
		g740.icons={
			_items: {
				'default': 'g740-icons-default',
				'save': 'g740-icons-save',
				'undo': 'g740-icons-undo',
				'refresh': 'g740-icons-refresh',
				'refreshrow': 'g740-icons-refreshrow',
				'append': 'g740-icons-append',
				'append.into': 'g740-icons-append-into',
				'delete': 'g740-icons-delete',
				'expand': 'g740-icons-expand',
				'collapse': 'g740-icons-collapse',
				'mark': 'g740-icons-mark',
				'markclear': 'g740-icons-markclear',
				'mark-add': 'g740-icons-mark-add',
				'mark-remove': 'g740-icons-mark-remove',
				'mark-off': 'g740-icons-mark-off',
				'move': 'g740-icons-move',
				'copy': 'g740-icons-copy',
				'join': 'g740-icons-join',
				'link': 'g740-icons-link',
				'httpget': 'g740-icons-httpget',
				
				'shift.first': 'g740-icons-shift-first',
				'shift.last': 'g740-icons-shift-last',
				'shift.after': 'g740-icons-shift-after',
				'shift.before': 'g740-icons-shift-before',
				'disconnect': 'g740-icons-disconnect',
				'print': 'g740-icons-print',
				'word': 'g740-icons-word',
				'excel': 'g740-icons-excel',
				'find': 'g740-icons-find',
				
				'ok': 'g740-icons-ok',
				'cancel': 'g740-icons-cancel',
				'clear': 'g740-icons-clear',

				'flag-blue': 'g740-icons-flag-blue',
				'flag-green': 'g740-icons-flag-green',
				'flag-orange': 'g740-icons-flag-orange',
				'flag-purple': 'g740-icons-flag-purple',
				'flag-red': 'g740-icons-flag-red',

				'table': 'g740-icons-table',
				'config': 'g740-icons-config',
				'coins': 'g740-icons-coins',
				'computer': 'g740-icons-computer',
				'database': 'g740-icons-database',
				'backup': 'g740-icons-backup',
				'restore': 'g740-icons-restore',
				'drivecd': 'g740-icons-drivecd',
				'email': 'g740-icons-email',
				'folder': 'g740-icons-folder',
				'user': 'g740-icons-user',
				'keyboard': 'g740-icons-keyboard',
				'layers': 'g740-icons-layers',
				'layout': 'g740-icons-layout',
				'lorry': 'g740-icons-lorry',
				'magnifier': 'g740-icons-magnifier',
				'page': 'g740-icons-page',
				'plugin': 'g740-icons-plugin',
				'bell': 'g740-icons-bell',
				'chart': 'g740-icons-chart',
				'dbtable': 'g740-icons-dbtable',
				'asterisk': 'g740-icons-asterisk',
				'help': 'g740-icons-help',
				'clipboard.copy': 'g740-icons-clipboard-copy',
				'clipboard.cut': 'g740-icons-clipboard-cut',
				'clipboard.paste': 'g740-icons-clipboard-paste',
				
				'login': 'g740-icons-login',
				'error': 'g740-icons-error',
				'alert': 'g740-icons-alert',
				'question': 'g740-icons-question'
			},
			getIconClassName : function(icon) {
				var result='';
				if (this._items[icon]) result=this._items[icon];
				return result;
			}
		};
// Поддержка цветовых схем, нужна таблица стилей color.css
		g740.colorScheme={
			// Зарегистрированные цветовые схемы
			_items: {
				white: {
					className: 'g740-color-white',
					classNameReadOnly: 'g740-color-white-readOnly'
				},
				gray: {
					className: 'g740-color-gray',
					classNameReadOnly: 'g740-color-gray-readOnly'
				},
				green: {
					className: 'g740-color-green',
					classNameReadOnly: 'g740-color-green-readOnly'
				},
				red: {
					className: 'g740-color-red',
					classNameReadOnly: 'g740-color-red-readOnly'
				},
				blue: {
					className: 'g740-color-blue',
					classNameReadOnly: 'g740-color-blue-readOnly'
				},
				yellow: {
					className: 'g740-color-yellow',
					classNameReadOnly: 'g740-color-yellow-readOnly'
				},
				cyan: {
					className: 'g740-color-cyan',
					classNameReadOnly: 'g740-color-cyan-readOnly'
				}
			},
			// Вернуть описание цветовой схемы по названию цвета
			getColorItem: function(color) {
				var objItem=this._items[color];
				if (!objItem) objItem=this._items['white'];
				return objItem;
			}
		};
		return g740;
	}
);
