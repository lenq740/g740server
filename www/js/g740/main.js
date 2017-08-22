//-----------------------------------------------------------------------------
// Главный модуль, подключает все необходимые модули проекта
//-----------------------------------------------------------------------------
define(
	[
		'dojo',
		'dojo/dom-geometry',
		'dojo/_base/fx', // fx.Animation
		'dojo/aspect',
		'dojo/_base/lang',
		'dojo/dom-class',
		'dojo/sniff', // has('ie') has('dijit-legacy-requires')
		'dojo/topic', // publish
		'dojo/when',
		'dojo/cookie', // cookie
		'dijit/registry', // registry.byId
		'dojo/on',

		'dojo/_base/kernel',
		'dojo/string',
		'dojo/i18n',
		'dojo/_base/config',
		'dojo/domReady!',

		'dojox/xml/parser',

		'dijit/_Widget',
		'dijit/_TemplatedMixin',

		'dijit/layout/BorderContainer',
		'dijit/layout/ContentPane',
		'dijit/layout/StackContainer',
		'dijit/layout/TabContainer',
		'dijit/layout/AccordionContainer',
		'dijit/TitlePane',

		'dojox/grid/DataGrid',
		'dojox/grid/cells',
		'dojox/grid/cells/dijit',

		'dijit/form/TextBox',
		'dijit/form/DateTextBox',
		'dijit/form/CheckBox',
		'dijit/form/RadioButton',
		'dijit/form/SimpleTextarea',

		'dijit/MenuBar',
		'dijit/Toolbar',
		'dijit/ToolbarSeparator',
		'dijit/form/Button',
		'dijit/form/DropDownButton',

		'dijit/Menu',
		'dijit/MenuItem',
		'dijit/MenuBar',
		'dijit/MenuBarItem',
		'dijit/PopupMenuItem',
		'dijit/PopupMenuBarItem',
		'dijit/MenuSeparator',

		'g740/config',
		'g740/localization',
		'g740/trace',
		'g740/convertor',
		'g740/xml',
		'g740/request',
		'g740/panels',
		'g740/widgets',
		'g740/dialog',

		'g740/widgetFields',
		'g740/panelGrid',
		'g740/panelTree',
		'g740/panelFields',
		'g740/panelFieldsMultiline',
		'g740/panelHtmlFields',
		'g740/Form',
		'g740/RowSet'
	],
	function (dojo, geom, fx, aspect, lang, domClass, has, topic, when, cookie, registry, on) {
	    if (typeof (g740) == 'undefined') g740 = {};
	    dojo.geom = geom;
	    dojo.fx = fx;
	    dojo.aspect = aspect;
	    dojo.lang = lang;
	    dojo.domClass = domClass;
	    dojo.has = has;
	    dojo.topic = topic;
	    dojo.when = when;
	    dojo.registry = registry;
		dojo.on=on;

	    document.body.oncontextmenu = function () { return false };
	    String.prototype.replaceAll = function (search, replace) {
	        return this.split(search).join(replace);
	    };
	    String.prototype.toHtml = function () {
	        return this.replaceAll('<', '&lt;').replaceAll('>', '&gt;');
	    };
	    g740.dom = {
	        append: function (name, owner, param) {
	            var e = document.createElement(name);
	            if (param) {
	                for (var i in param) {
	                    e[i] = param[i];
	                }
	            }
	            owner.appendChild(e);
	            return e;
	        },
	        appendText: function (owner, text) {
	            var t = document.createTextNode(text);
	            owner.appendChild(t);
	            return t;
	        }
	    };

	    // заглушка на рассчет динамически вычисляемых выражений
	    g740.js_eval = function (obj, js_expr, defa) {
	        if (typeof (defa) == 'undefined') defa = null;
	        if (typeof (js_expr) != 'string') return defa;
	        if (!js_expr) return defa;
	        try {
	            var result = defa;
	            try {
	                g740.js_eval_obj = obj;
	                result = eval(js_expr);
	            }
	            finally {
	                g740.js_eval_obj = null;
	            }
	        }
	        catch (e) {
	            result = defa;
	        }
	        return result;
	    };

	    function get(name) {
	        var result = false;
	        try {
	            if (g740.js_eval_obj && g740.js_eval_obj.doG740Get) result = g740.js_eval_obj.doG740Get(name);
	        }
	        catch (e) {
	            result = false;
	        }
	        return result;
	    }

	    g740.execDelay = {
	        _index: 0,
	        _indexMax: 100000,
	        _delayDefault: 100,
	        _list: {},
	        // Отложенное выполнение функции в контексте объекта
	        //	para.delay 	- задержка, если не задана - this._delayDefault
	        //	para.obj	- контекст выполнения
	        //	para.func	- функция
	        //	para.para	- параметры, которые надо передать функции
	        go: function (para) {
	            var procedureName = 'g740.execDelay.go';
	            if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
	            if (typeof (para.func) != 'function') g740.systemError(procedureName, 'errorValueUndefined', 'para.func');
	            if (!para.delay) para.delay = this._delayDefault;
	            this._list[this._index] = para;
	            window.setTimeout('g740.execDelay._exec(' + this._index + ')', para.delay);
	            this._index++;
	            if (this._index > this._indexMax) this._index = 0;
	        },
	        _exec: function (index) {
	            var para = this._list[index];
	            delete this._list[index];
	            if (typeof (para.func) == 'function') {
	                if (para.obj) {
	                    para.func.call(para.obj, para.para);
	                }
	                else {
	                    para.func(para.para);
	                }
	            }
	        }
	    };

	    g740.application = {
	        objPanel: null,
	        objForm: null,
	        objDialogLogin: null,
	        lstModalFormDialogs: [],
	        modalResults: {},

	        closeModalForm: function () {
				var objDialog=g740.application.getModalDialog();
                if (objDialog) objDialog.hide();
	        },
			getModalDialog: function() {
				var result=null;
	            if (g740.application.lstModalFormDialogs.length > 0) {
	                result = g740.application.lstModalFormDialogs[g740.application.lstModalFormDialogs.length - 1];
	            }
				return result;
			},
	        // Очистить приложение, уничтожить главную форму
	        clear: function () {
	            if (!g740.application.objPanel) g740.application.doG740StartUp();

	            for (var i = 0; i < g740.application.lstModalFormDialogs.length; i++) {
	                var obj = g740.application.lstModalFormDialogs[i];
	                obj.destroyRecursive();
	                g740.application.lstModalFormDialogs[i] = null;
	            }
	            g740.application.lstModalFormDialogs = [];

	            var lst = g740.application.objPanel.getChildren();
	            for (var i = 0; i < lst.length; i++) {
	                var obj = lst[i];
	                g740.application.objPanel.removeChild(obj);
	                obj.destroyRecursive();
	            }
	            g740.application.objForm = null;
	            g740.application.objDialogLogin = null;
	        },
	        // Отобразить главную форму приложения
	        //	formName	- имя запускаемой формы
	        //	G740params	- параметры запуска
	        //	attr		- ассоциативный массив атрибутов, не передаваемый на сервер
	        doG740ShowApplicationForm: function (para) {
	            var procedureName = 'g740.application.doG740ShowApplicationForm';
	            if (!para) para = {};
	            if (!para.formName) para.formName = g740.config.mainFormName;
	            if (!para.G740params) para.G740params = {};
	            if (!para.attr) para.attr = {};

	            g740.application.clear();
	            g740.application.objForm = new g740.Form(
					{
					    name: para.formName,
					    design: 'headline',
					    region: 'center',
					    style: 'height:100%; width:100%;'
					}, null
				);
	            g740.application.objPanel.addChild(g740.application.objForm);
	            g740.application.objForm.sendRequestForm();
	            return true;
	        },
	        // Послать запрос на отображение экранной формы
	        //	formName	- имя запускаемой формы
	        //	G740params	- параметры запуска
	        //	attr		- ассоциативный массив атрибутов, не передаваемый на сервер
	        doG740ShowForm: function (para) {
	            if (!para) para = {};
	            if (!para.formName) para.formName = g740.config.mainFormName;
	            if (!para.G740params) para.G740params = {};
	            if (!para.attr) para.attr = {};

	            if (!g740.application.objForm) return g740.application.doG740ShowApplicationForm(para);
	            if (!g740.application.objForm.objPanelForm) return g740.application.doG740ShowApplicationForm(para);

	            var objForm = new g740.Form(
					{
					    name: para.formName,
					    design: 'headline',
					    region: 'center',
					    style: 'height:100%; width:100%;'
					}, null
				);
				
				var res=objForm.sendRequestForm(para.G740params);
				if (!res) return false;
				
				if (objForm.isModal || (para.attr['modal'] == '1')) {
					var isClosable = objForm.isClosable;
					if (isClosable && para.attr['closable'] == '0') isClosable = false;

					var posBody = {
						w: document.body.clientWidth,
						h: document.body.clientHeight
					};

					if (!objForm.g740Width && para.attr['width']) objForm.g740Width = para.attr['width'];
					if (!objForm.g740Height && para.attr['height']) objForm.g740Height = para.attr['height'];

					var w = objForm.g740Width;
					var h = objForm.g740Height;
					objForm.isModal = true;

					var objDialog = new g740.DialogModalForm({
						title: objForm.title,
						attr: para.attr,
						closable: isClosable ? true : false,
						width: w,
						height: h
					});
					
					objDialog.addChild(objForm);
					objDialog.show();
					objForm.startup();
				}
				else {
					g740.application.objForm.objPanelForm.doG740ShowForm(objForm);
				}
				return true;
	        },
	        // Отобразить диалог Login для авторизации
	        doG740ShowLoginForm: function () {
				if (g740.config.mainFormLoginUrl) {
					window.open(g740.config.mainFormLoginUrl, '_self');
					return true;
				}
	            if (g740.application.objDialogLogin && g740.application.objDialogLogin.isObjectDestroed) g740.application.objDialogLogin = null;
	            if (g740.config.dialogLogin && !g740.application.objDialogLogin) {
	                g740.application.clear();
	                if (g740.config.dialogLogin.loginUrl) {
	                    var objLoginPanel = new g740.PanelWebBrowser(
							{
							    region: 'center',
							    style: 'height:100%; width:100%; border-width: 0px',
							    url: g740.config.dialogLogin.loginUrl
							},
							null
						);
	                    g740.application.objPanel.addChild(objLoginPanel);
	                }
	                if (g740.config.dialogLogin && g740.config.dialogLogin.isEnabled) g740.application.objDialogLogin = new g740.DialogLogin();
	            }
	            if (g740.application.objDialogLogin) g740.application.objDialogLogin.show();
	        },
	        // Создаем контейнер g740.application.objPanel в котором будет размещена главная форма приложения
	        doG740StartUp: function () {
	            var mainFormDomNode = 'FormPanelMain';
	            if (g740.config.mainFormDomNode) mainFormDomNode = g740.config.mainFormDomNode;
	            g740.application.objPanel = new dijit.layout.BorderContainer(
					{
					    design: 'headline',
					    region: 'center',
					    style: 'height:100%; width:100%'
					},
					mainFormDomNode
				);
	            g740.application.objPanel.startup();
	        }
	    };
	    g740.application.doG740StartUp();

		g740.size = {
			domLabel: null,
			getLabelWidth: function(text) {
				if (!g740.size.domLabel) {
					g740.size.domLabel=document.createElement('div');
					g740.size.domLabel.className='g740-size-label';
					document.body.appendChild(g740.size.domLabel);
				}
				g740.size.domLabel.innerHTML='';
				var domText=document.createTextNode(text);
				g740.size.domLabel.appendChild(domText);
				return g740.size.domLabel.offsetWidth;
			}
		}
		
	    return g740;
	}
);