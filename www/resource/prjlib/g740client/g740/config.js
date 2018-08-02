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
			iconSizeDefault: 'small',			// размер иконок по умолчанию - small, medium, large
			appColorScheme: 'black',			// цветовая схема приложения - black, red, cti

			mainFormLoginUrl: '',				// Путь до HTML страницы аутетификации, если не задана, то стандартная аутетификации
			dialogLogin: {
				loginUrl: '',
				iconUrl: '',
				isReloadBeforeLogin: false,						// Перед отображением диалога логина вызывать перечитку
				isEnabled: true,								// Показывать стандартный диалог авторизации
				width: '350px',									// ширина диалога
				height: '180px',								// высота диалога
				iconWidth: '128px',								// ширина иконки
				title: ''
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
				'unmarkall': 'g740-icons-unmarkall',
				'move': 'g740-icons-move',
				'copy': 'g740-icons-copy',
				'join': 'g740-icons-join',
				'link': 'g740-icons-link',
				'httpget': 'g740-icons-httpget',
				'httpput': 'g740-icons-httpput',
				
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
				'go': 'g740-icons-go',
				'menu': 'g740-icons-menu',

				'table': 'g740-icons-table',
				'config': 'g740-icons-config',
				'computer': 'g740-icons-computer',
				'database': 'g740-icons-database',
				'backup': 'g740-icons-backup',
				'restore': 'g740-icons-restore',
				'email': 'g740-icons-email',
				'folder': 'g740-icons-folder',
				'page': 'g740-icons-page',
				'dbtable': 'g740-icons-dbtable',
				'dbfield': 'g740-icons-dbfield',
				'help': 'g740-icons-help',

				'user': 'g740-icons-user',
				'usergroup': 'g740-icons-usergroup',
				'userrole': 'g740-icons-userrole',

				'check-on': 'g740-icons-check-on',
				'check-off': 'g740-icons-check-off',
				
				'clipboard.copy': 'g740-icons-clipboard-copy',
				'clipboard.cut': 'g740-icons-clipboard-cut',
				'clipboard.paste': 'g740-icons-clipboard-paste'
			},
			getIconClassName : function(icon, size) {
				if (size!='large' && size!='medium') size='small';
				
				var result='';
				if (this._items[icon]) result=this._items[icon];
				
				if (result && typeof(result)=='object' && result[size]) {
					result=result[size];
				}
				if (typeof(result)!='string') result='';
				if (result) result+=' g740-iconsize-'+size;
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
		
		g740.appColorScheme={
			_items: {
				black: {
					className: 'app-color-black',
					panelExpanderLookOpacityMax: 0.55
				},
				red: {
					className: 'app-color-red',
					panelExpanderLookOpacityMax: 0.55
				},
				cti: {
					className: 'app-color-cti',
					panelTreeMenuWhiteIcons: true,
					panelExpanderLookOpacityMax: 0.01
				}
			},
			getItem: function() {
				var objItem=this._items[g740.config.appColorScheme];
				if (!objItem) objItem=this._items['black'];
				return objItem;
			}
		};
		
		return g740;
	}
);