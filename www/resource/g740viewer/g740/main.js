/**
 * G740Viewer
 * Copyright 2017-2019 Galinsky Leonid lenq740@yandex.ru
 * Licensed under the BSD license
 */

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
		
		'dijit/Calendar',

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
		'g740/Form',
		'g740/RowSet'
	],
	function (dojo, geom, fx, aspect, lang, domClass, has, topic, when, cookie, registry, on) {
	    if (typeof (g740)=='undefined') g740={};
	    dojo.geom=geom;
	    dojo.fx=fx;
	    dojo.aspect=aspect;
	    dojo.lang=lang;
	    dojo.domClass=domClass;
	    dojo.has=has;
	    dojo.topic=topic;
	    dojo.when=when;
	    dojo.registry=registry;
		dojo.on=on;

	    document.body.oncontextmenu=function () { return false };
	    String.prototype.replaceAll=function (search, replace) {
	        return this.split(search).join(replace);
	    };
	    String.prototype.toHtml=function () {
	        return this.replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');
	    };
	    g740.dom={
	        append: function (name, owner, param) {
	            var e=document.createElement(name);
	            if (param) {
	                for (var i in param) {
	                    e[i]=param[i];
	                }
	            }
	            owner.appendChild(e);
	            return e;
	        },
	        appendText: function (owner, text) {
	            var t=document.createTextNode(text);
	            owner.appendChild(t);
	            return t;
	        }
	    };

	    // Вычисление динамически вычисляемых выражений
		g740.js_eval_obj=null;
	    g740.js_eval=function (obj, js_expr, defa) {
	        if (typeof (defa)=='undefined') defa=null;
			var t=typeof (js_expr);
			if (t!='function' && t!='string') return defa;
			if (js_expr=='') return defa;
	        try {
				var result=defa;
				var oldEvalObj=g740.js_eval_obj;
	            try {
	                g740.js_eval_obj=obj;
					if (t=='string') result=eval(js_expr);
					if (t=='function') result=js_expr.call(obj);
	            }
	            finally {
	                g740.js_eval_obj=oldEvalObj;
	            }
	        }
	        catch (e) {
				try {
					console.log({
						expr: js_expr,
						error: e.toString()
					});
				}
				catch (e) {
				}
	            result=defa;
	        }
	        return result;
	    };
	    function get(name, defa) {
	        var result=false;
			if (name=='IE') {
				return dojo.hasClass(document.documentElement,'IE')?1:0;
			}
	        try {
	            if (g740.js_eval_obj && g740.js_eval_obj.doG740Get) result=g740.js_eval_obj.doG740Get(name);
	        }
	        catch (e) {
				try {
					console.log(e.toString());
				}
				catch (e) {
				}
	            result=false;
	        }
			if (typeof(defa)!='undefined' && !result) return defa;
			result=g740.convertor.toG740(result);
			if (result=='0') result=0;
	        return result;
	    };
	    function getplus(name) {
			try {
				var result=get(name,0);
				if (result>0) return result;
			}
	        catch (e) {
				try {
					console.log(e.toString());
				}
				catch (e) {
				}
	        }
			return '';
	    };
		function set(rowsetName, fieldName, value) {
			try {
				if (!g740.js_eval_obj) return false;
				var objForm=null;
				if (g740.js_eval_obj.g740className=='g740.Form') objForm=g740.js_eval_obj;
				if (g740.js_eval_obj.g740className=='g740.RowSet') objForm=g740.js_eval_obj.objForm;
				if (!objForm) return false;
				var objRowSet=objForm.rowsets[rowsetName];
				if (!objRowSet) return false;
				objRowSet.setFieldProperty({
					fieldName: fieldName,
					value: value
				});
			}
			catch (e) {
				return false;
			}
			return true;
		};
		function exec(name, p0, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, p11, p12, p13, p14, p15) {
			var result=false;
			try {
				var obj=g740.js_eval_obj;
				if (!obj) {
					if (g740.application.objForm && g740.application.objForm.script && g740.application.objForm.script[name]) {
						obj=g740.application.objForm;
					}
					else {
						var focusedForm=g740.application.getFocusedForm();
						if (focusedForm && focusedForm.script && focusedForm.script[name]) obj=focusedForm;
					}
				}
				if (!obj) return false;
				var f='';
				if (obj.script && obj.script[name]) f=obj.script[name];
				if (!f && obj.objForm && obj.objForm.script && obj.objForm.script[name]) f=obj.objForm.script[name];
				if (typeof(f)=='function') result=f(p0, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, p11, p12, p13, p14, p15);
			}
	        catch (e) {
	            result=false;
	        }
			return result;
		};
		function execRequest(requestName, requestMode, params) {
			var focusedForm=g740.application.getFocusedForm();
			if (!focusedForm) return false;
			if (!focusedForm.getRequest(requestName, requestMode)) {
				var f=g740.application.objForm;
				if (f && f.getRequest(requestName, requestMode)) focusedForm=f;
			}
			
			var G740params={};
			if (params) {
				for(var name in params) G740params[name]=g740.convertor.toG740(params[name]);
			}
			return focusedForm.exec({
				requestName: requestName,
				requestMode: requestMode,
				G740params: G740params
			});
		};
		function execShowForm(formName, params, attr) {
			var G740params={};
			if (params) {
				for(var name in params) G740params[name]=g740.convertor.toG740(params[name]);
			}
			if (!attr) attr={};
			return g740.application.doG740ShowForm({
				formName: formName,
				G740params: G740params,
				attr: attr
			});
		};
		
		window.get=get;
		window.getplus=getplus;
		window.set=set;
		window.exec=exec;
		window.execRequest=execRequest;
		window.execShowForm=execShowForm;
		
	    g740.execDelay={
	        _index: 0,
	        _indexMax: 100000,
	        _delayDefault: 100,
	        _list: {},
	        // Отложенное выполнение функции в контексте объекта
	        //	para.delay 	- задержка, если не задана - this._delayDefault
	        //	para.obj	- контекст выполнения
	        //	para.func	- функция
	        //	para.para	- параметры, которые надо передать функции
	        go: function(para) {
	            var procedureName='g740.execDelay.go';
	            if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
	            if (typeof (para.func) != 'function') g740.systemError(procedureName, 'errorValueUndefined', 'para.func');
	            if (!para.delay) para.delay=this._delayDefault;

				var index=this._index;
				this._index++;
				if (this._index>this._indexMax) this._index=0;
				this._list[index]=para;
				if (para.delay<=0) para.delay=1;
				window.setTimeout('g740.execDelay._exec('+index+')', para.delay);
	        },
	        _exec: function(index) {
	            var para=this._list[index];
	            delete this._list[index];
				if (typeof (para.func)=='function') {
					if (para.obj) {
						para.func.call(para.obj, para.para);
					}
					else {
						para.func(para.para);
					}
				}
	        }
	    };

	    g740.application={
	        objPanel: null,
	        objForm: null,
	        objDialogLogin: null,
	        lstModalFormDialogs: [],
	        modalResults: {},
			isModeLoginDialog: false,

			getUrlParams: function() {
				var result={};
				var lst=window.location.search.substr(1).split('&');
				for (var i=0; i<lst.length; i++) {
					var str=lst[i];
					var n=str.indexOf('=');
					if (n<=0) continue;
					var name=str.substr(0,n);
					var value=str.substr(n+1,9999);
					result[name]=value;
				}
				return result;
			},
			goReload: function(mode) {
				var url=window.location.protocol+'//'+window.location.hostname;
				if (window.location.port) url+=':'+window.location.port;
				if (window.location.pathname) url+=window.location.pathname;
				if (mode) url+='?mode='+mode;
				window.open(url, '_self');
				return true;
			},
			getIsModeLoginDialog: function() {
				if (!g740.config.dialogLogin) return false;
				if (!g740.config.dialogLogin.isReloadBeforeLogin) return false;
				return this.isModeLoginDialog;
			},
	        closeModalForm: function () {
				var objDialog=g740.application.getModalDialog();
                if (objDialog) objDialog.hide();
	        },
			getModalDialog: function() {
				var result=null;
	            if (g740.application.lstModalFormDialogs.length > 0) {
	                result=g740.application.lstModalFormDialogs[g740.application.lstModalFormDialogs.length - 1];
	            }
				return result;
			},
			getFocusedForm: function() {
				var result=null;
				var objDialog=g740.application.getModalDialog();
				if (objDialog) result=objDialog.getObjForm();
				if (!result) {
					var result=g740.application.objForm;
					if (g740.application.objForm.objPanelForm && g740.application.objForm.objPanelForm.getFocusedForm) {
						var r=g740.application.objForm.objPanelForm.getFocusedForm();
						if (r) result=r;
					}
				}
				return result;
			},
			closeFocusedForm: function() {
				if (g740.application.getModalDialog()) {
					g740.application.closeModalForm();
				}
				else if (g740.application.objForm && g740.application.objForm.objPanelForm) {
					var objPanelForm=g740.application.objForm.objPanelForm;
					if (objPanelForm.closeFocusedForm) objPanelForm.closeFocusedForm();
				}
			},
	        // Очистить приложение, уничтожить главную форму
	        clear: function () {
	            if (!g740.application.objPanel) g740.application.doG740StartUp();

	            for (var i=0; i < g740.application.lstModalFormDialogs.length; i++) {
	                var obj=g740.application.lstModalFormDialogs[i];
	                obj.destroyRecursive();
	                g740.application.lstModalFormDialogs[i]=null;
	            }
	            g740.application.lstModalFormDialogs=[];

	            var lst=g740.application.objPanel.getChildren();
	            for (var i=0; i < lst.length; i++) {
	                var obj=lst[i];
	                g740.application.objPanel.removeChild(obj);
	                obj.destroyRecursive();
	            }
	            g740.application.objForm=null;
	            g740.application.objDialogLogin=null;
	        },
	        // Отобразить главную форму приложения
	        //	formName	- имя запускаемой формы
	        //	G740params	- параметры запуска
	        //	attr		- ассоциативный массив атрибутов, не передаваемый на сервер
	        doG740ShowApplicationForm: function (para) {
	            var procedureName='g740.application.doG740ShowApplicationForm';
	            if (!para) para={};
	            if (!para.formName) para.formName=g740.config.mainFormName;
	            if (!para.G740params) para.G740params={};
	            if (!para.attr) para.attr={};

	            g740.application.clear();
	            g740.application.objForm=new g740.Form(
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
	            if (!para) para={};
	            if (!para.formName) para.formName=g740.config.mainFormName;
	            if (!para.G740params) para.G740params={};
	            if (!para.attr) para.attr={};
				
	            if (!g740.application.objForm) return g740.application.doG740ShowApplicationForm(para);
	            if (!g740.application.objForm.objPanelForm) return g740.application.doG740ShowApplicationForm(para);

	            var objForm=new g740.Form(
					{
					    name: para.formName,
					    design: 'headline',
					    region: 'center',
					    style: 'height:100%; width:100%;'
					}, null
				);
				
				var res=objForm.sendRequestForm(para.G740params);
				if (!res) return false;
				
				if (objForm.isModal || (para.attr['modal']=='1')) {
					var isClosable=objForm.isClosable;
					if (isClosable && para.attr['closable']=='0') isClosable=false;

					var posBody={
						w: document.body.clientWidth,
						h: document.body.clientHeight
					};

					if (!objForm.g740Width && para.attr['width']) objForm.g740Width=para.attr['width'];
					if (!objForm.g740Height && para.attr['height']) objForm.g740Height=para.attr['height'];

					var w=objForm.g740Width;
					var h=objForm.g740Height;
					objForm.isModal=true;

					var objDialog=new g740.DialogModalForm({
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
					var oldForm=g740.application.getFocusedForm();
					if (oldForm && oldForm!=g740.application.objForm) {
						oldForm.fifoRequests=[];
					}
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
				
				if (g740.config.dialogLogin && g740.config.dialogLogin.isReloadBeforeLogin) {
					if (!this.getIsModeLoginDialog()) {
						this.goReload('login');
						return true;
					}
				}
				
	            if (g740.application.objDialogLogin && g740.application.objDialogLogin.isObjectDestroed) g740.application.objDialogLogin=null;
	            if (g740.config.dialogLogin && !g740.application.objDialogLogin) {
	                g740.application.clear();
	                if (g740.config.dialogLogin.loginUrl) {
	                    var objLoginPanel=new g740.PanelWebBrowser(
							{
							    region: 'center',
							    style: 'height:100%; width:100%; border-width: 0px',
							    url: g740.config.dialogLogin.loginUrl
							},
							null
						);
	                    g740.application.objPanel.addChild(objLoginPanel);
	                }
	                if (g740.config.dialogLogin && g740.config.dialogLogin.isEnabled) g740.application.objDialogLogin=new g740.DialogLogin();
	            }
	            if (g740.application.objDialogLogin) g740.application.objDialogLogin.show();
	        },

			_lockScreenIndex: 0,
			doLockScreenShow: function() {
				if (this._lockScreenIndex==0) {
					var domLockScreen=document.getElementById('g740-lockscreen');
					if (!domLockScreen) {
						var domLockScreen=document.createElement('div');
						domLockScreen.id='g740-lockscreen';
						domLockScreen.style.display='none';
						document.body.appendChild(domLockScreen);
					}
					domLockScreen.style.display='block';
				}
				this._lockScreenIndex++;
			},
			doLockScreenHide: function() {
				this._lockScreenIndex--;
				if (this._lockScreenIndex<0) this._lockScreenIndex=0;
				if (this._lockScreenIndex==0) {
					var domLockScreen=document.getElementById('g740-lockscreen');
					if (domLockScreen) {
						domLockScreen.style.display='none';
					}
				}
			},

			go: function() {
				dojo.addClass(document.body, 'g740');
				var urlParams=this.getUrlParams();
				if (urlParams['mode']=='login') this.isModeLoginDialog=true;
				
	            var mainFormDomNode='FormPanelMain';
	            if (g740.config.mainFormDomNode) mainFormDomNode=g740.config.mainFormDomNode;
	            g740.application.objPanel=new dijit.layout.BorderContainer(
					{
					    design: 'headline',
					    region: 'center',
					    style: 'height:100%; width:100%'
					},
					mainFormDomNode
				);
	            g740.application.objPanel.startup();

				
				var itemAppColorScheme=g740.appColorScheme.getItem();
				if (itemAppColorScheme)	dojo.addClass(document.body, itemAppColorScheme.className);
				
				g740.application.doG740ShowForm();
			}
		};

		g740.size={
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