/**
 * G740Viewer
 * Copyright 2017-2019 Galinsky Leonid lenq740@yandex.ru
 * Licensed under the BSD license
 */

define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
// Регистратор панелей
		g740.panels={
			_list: {},
			registrate: function(panelName, builder) {
				var procedureName='g740.panels.registrate';
				if (this._list[panelName]) g740.systemError(procedureName, 'errorBuilderNotUniqueClassName', panelName);
				this._list[panelName]=builder;
				return true;
			},
			getBuilder: function(panelName) {
				var result=null;
				if (this._list[panelName]) result=this._list[panelName];
				return result;
			},
// Создание панели экранной формы по xml описанию
//	xml	- xml описание панели
//	objForm - экранная форма
//	objParent - родительская панель
			buildPanel: function(xml, objForm, objParent) {
				var result=false;
				var procedureName='g740.panels.buildPanel';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorIncorrectPanelName', xml.nodeName);
				if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objForm');
				if (objForm.g740className!='g740.Form') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objForm');
				if (!objParent) g740.systemError(procedureName, 'errorValueUndefined', 'objParent');
				if (objParent!=objForm) {
					if (objParent.g740className!='g740.Panel') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objParent');
				}
				
				// Находим построитель панели
				var panelName=g740.xml.getAttrValue(xml, 'type', '');
				if (!panelName)	panelName=g740.xml.getAttrValue(xml, 'panel', 'panel');
				var builder=this.getBuilder(panelName);
				if (!builder) {
					g740.trace.goBuilder({
						formName: objForm.name,
						panelName: panelName,
						messageId: 'errorIncorrectPanelName'
					});
					return false;
				}
				// Проверяем на видимость
				if (g740.xml.getAttrValue(xml, 'visible', '1')!='1') return true;
				
				// Формируем универсальную часть параметров для конструктора виджета
				var p={};
				p.objForm=objForm;
				
				if (objParent.isG740BorderContainer) {
					var region=g740.xml.getAttrValue(xml, 'align', 'middle');
					if (region!='top' && region!='bottom' && region!='left' && region!='right' && region!='center') region='middle';
					p.region=region;
					if (region=='middle') p.region='center';
					if (region=='center') p.region='g740.PanelCenter';
				}
				
				p.splitter=false;
				if (g740.xml.getAttrValue(xml, 'splitter', '0')=='1') p.splitter=true;
				p.g740id=g740.xml.getAttrValue(xml, 'g740id', '');
				p.styleBorder='border: none; border-width: 0px;';
				if (g740.xml.getAttrValue(xml, 'border', '0')=='1') {
					p.styleBorder='border: solid 1px;border-color: lightgray;';
				}

				p.isFocusOnShow=(g740.xml.getAttrValue(xml, 'focus', '')=='1');
				p.best=(g740.xml.getAttrValue(xml, 'best', '')=='1');

				if (g740.xml.isAttr(xml,'onshow')) p.evt_onshow=g740.xml.getAttrValue(xml, 'onshow', '');
				if (g740.xml.isAttr(xml,'onaction')) p.evt_onaction=g740.xml.getAttrValue(xml, 'onaction', '');
				
				if (g740.xml.isAttr(xml,'js_visible')) p.js_visible=g740.xml.getAttrValue(xml, 'js_visible', '');
				if (g740.xml.isAttr(xml,'js_best')) p.js_visible=g740.xml.getAttrValue(xml, 'js_best', '');
				if (g740.xml.isAttr(xml,'js_onshow')) p.js_onshow=g740.xml.getAttrValue(xml, 'js_onshow', '');
				if (g740.xml.isAttr(xml,'js_onaction')) p.js_onaction=g740.xml.getAttrValue(xml, 'js_onaction', '');
				var xmlScripts=g740.xml.findFirstOfChild(xml, {nodeName: 'scripts'});
				if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xml;
				var lst=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
				for (var i=0; i <lst.length; i++) {
					var xmlScript=lst[i];
					var name=g740.xml.getAttrValue(xmlScript, 'name', '');
					if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
					if (name=='visible') p.js_visible=g740.panels.buildScript(xmlScript);
					if (name=='best') p.js_best=g740.panels.buildScript(xmlScript);
					if (name=='onshow') p.js_onshow=g740.panels.buildScript(xmlScript);
					if (name=='onaction') p.js_onaction=g740.panels.buildScript(xmlScript);
				}

				p.color=g740.xml.getAttrValue(xml, 'color', '');
				p.rowsetName=g740.xml.getAttrValue(xml, 'rowset', objParent.rowsetName);
				var nodeTypeDef='';
				if (p.rowsetName==objParent.rowsetName) nodeTypeDef=objParent.nodeType;
				p.nodeType=g740.xml.getAttrValue(xml, 'row.type', nodeTypeDef);
				p.title=g740.xml.getAttrValue(xml, 'caption', '');

				var w=g740.xml.getAttrValue(xml, 'width', '');
				var h=g740.xml.getAttrValue(xml, 'height', '');
				if (p.region=='top' || p.region=='bottom' || p.region=='center') w='';
				if (p.region=='left' || p.region=='right' || p.region=='center') h='';
				
				p.styleSize='';
				if (w) {
					p.styleSize+='width:'+w+';';
					p.width=w;
				}
				if (h) {
					p.styleSize+='height:'+h+';';
					p.height=h;
				}
				
				p.style='';
				if (p.styleSize) p.style+=p.styleSize;
				if (p.styleBorder) p.style+=p.styleBorder;
				if (p.title && !objParent.isG740CanShowChildsTitle) p.isShowTitle=true;
				
				if (g740.xml.isAttr(xml,'padding')) p.padding=g740.xml.getAttrValue(xml,'padding','');
				if (g740.xml.isAttr(xml,'fontsize')) p.fontsize=g740.xml.getAttrValue(xml,'fontsize','normal');
				
				var objPanel=builder(xml, p);
				
				// Формируем ToolBar
				if (objPanel.isG740CanToolBar) {
					var xmlToolbar = g740.xml.findFirstOfChild(xml, { nodeName: 'toolbar' });
					if (xmlToolbar) {
						this.buildToolbar(xmlToolbar, objPanel);
					}
				}

				if (objPanel.isG740BorderContainer || objPanel.isG740CanButtons) {
					var xmlMenuBar = g740.xml.findFirstOfChild(xml, { nodeName: 'menubar' });
					if (xmlMenuBar) {
						this.buildMenuBar(xmlMenuBar, objPanel);
					}
					var xmlButtons=g740.xml.findFirstOfChild(xml,{nodeName:'buttons'});
					if (xmlButtons) {
						this.buildButtons(xmlButtons, objPanel);
					}
				}

				// Вставляем панель в родительскую панель
				if (objParent.doG740AddChildPanel) {
					objParent.doG740AddChildPanel(objPanel);
				}
				else {
					objParent.addChild(objPanel);
				}
				// Формируем Menu
				var xmlMenu=g740.xml.findFirstOfChild(xml,{nodeName:'menu'});
				this.buildMenu(xmlMenu, objPanel);
				
				if (objPanel.postCreateBeforeChilds) objPanel.postCreateBeforeChilds();
		
				// Разбираемся с детьми
				var xmlPanels=g740.xml.findFirstOfChild(xml,{nodeName:'panels'});
				if (xmlPanels==null) xmlPanels=xml;
				var lstXmlPanels=g740.xml.findArrayOfChild(xmlPanels,{nodeName:'panel'});
				// Проверяем, могут ли у панели быть дети
				if (!objPanel.isG740CanChilds) {
					if (lstXmlPanels.length>0) {
						g740.trace.goBuilder({
							formName: objForm.name,
							panelName: panelName,
							messageId: 'errorPanelCanNotHasChilds'
						});
						return false;
					}
					return true;
				}

				for(var i=0; i<lstXmlPanels.length; i++) {
					var xmlChild=lstXmlPanels[i];
					this.buildPanel(xmlChild, objForm, objPanel);
				}
				return true;
			},
		    // Формируем MenuBar
			buildMenuBar: function (xml, objPanel) {
			    var result = false;
			    var procedureName = 'g740.panels.buildMenuBar';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (xml.nodeName != 'menubar') g740.systemError(procedureName, 'errorIncorrectValue', xml.nodeName);
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');
				var objForm = objPanel.objForm;
				if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel.objForm');
				var requests = this.getRequests(xml, objPanel);
				if (!requests || requests.length == 0) return false;

				var p = {};
				p.region = 'top';
				if (g740.xml.isAttr(xml,'login')) p.connectedUser=g740.xml.getAttrValue(xml,'login','');
				var objMenuBar = new g740.MenuBar(p, null);
				objPanel.addChild(objMenuBar);
				for (var i = 0; i < requests.length; i++) {
					this.buildToolbarMenuItem(requests[i], objPanel, objMenuBar);
				}
			    return true;
			},
			// Формируем Toolbar
			buildToolbar: function(xml, objPanel) {
				var result=false;
				var procedureName='g740.panels.buildToolbar';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (xml.nodeName!='toolbar') g740.systemError(procedureName, 'errorIncorrectValue', xml.nodeName);
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');
				var objForm=objPanel.objForm;
				if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel.objForm');
				var requests=this.getRequests(xml, objPanel);
				if (!requests || requests.length==0) return false;

				var p={};
				p.region='top';
				p.g740size=g740.xml.getAttrValue(xml, 'size', '');
				var objToolbar=new g740.Toolbar(p,null);
				objPanel.addChild(objToolbar);
				for(var i=0; i<requests.length; i++) {
					this.buildToolbarMenuItem(requests[i], objPanel, objToolbar);
				}
				return true;
			},
			// Формируем Buttons
			buildButtons: function(xml, objPanel) {
				var result=false;
				var procedureName='g740.panels.buildButtons';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (xml.nodeName!='buttons') g740.systemError(procedureName, 'errorIncorrectValue', xml.nodeName);
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');
				var objForm=objPanel.objForm;
				if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel.objForm');
				var requests=this.getRequests(xml, objPanel);
				if (!requests || requests.length==0) return false;
				
				var buttonCount=0;
				var isVertical=false;
				for(var i=0; i<requests.length; i++) {
					var xmlRequest=requests[i];
					var align=g740.xml.getAttrValue(xmlRequest,'align','left');
					if (align=='top') isVertical=true;
					buttonCount++;
				}
				
				var p={};
				p.region='bottom';
				p.style='padding-left:5px;padding-right:5px;height:30px;border-width:0px;';
				p.height='30px';
				if (isVertical) p.height=(buttonCount*30)+'px';
				p.style+='height:'+p.height+';';
				p.color=g740.xml.getAttrValue(xml, 'color', '');
				
				var objPanelButtons=new g740.Panel(p,null);
				objPanelButtons.isG740PanelButtons=true;
				objPanel.addChild(objPanelButtons);
				
				if (isVertical) {
					for(var i=0; i<requests.length; i++) {
						var xmlRequest=requests[i];
						xmlRequest.setAttribute('align','top');
						this.buildToolbarMenuItem(xmlRequest, objPanel, objPanelButtons);
					}
				}
				else {
					for(var i=0; i<requests.length; i++) {
						var xmlRequest=requests[i];
						var align=g740.xml.getAttrValue(xmlRequest,'align','left');
						if (align!='left') continue;
						this.buildToolbarMenuItem(requests[i], objPanel, objPanelButtons);
					}
					for(var i=requests.length-1; i>=0; i--) {
						var xmlRequest=requests[i];
						var align=g740.xml.getAttrValue(xmlRequest,'align','left');
						if (align!='right') continue;
						this.buildToolbarMenuItem(requests[i], objPanel, objPanelButtons);
					}
				}
				return true;
			},
			// Формируем Menu
			buildMenu: function(xml, objPanel) {
				var result=false;
				var procedureName='g740.panels.buildMenu';
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');
				var objForm=objPanel.objForm;
				if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel.objForm');

				if (xml) {
					if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
					if (xml.nodeName!='menu' && xml.nodeName!='toolbar') g740.systemError(procedureName, 'errorIncorrectValue', xml.nodeName);
					var requests=this.getRequests(xml, objPanel);
				}
				if (!requests) requests=[];
				
				if (objPanel.isG740Clipboard) {
					if (requests.length>0) {
						var xml=g740.xml.createElement('separator');
						requests.unshift(xml);
					}
					var xml=g740.xml.createElement('request');
					xml.setAttribute('caption',g740.getMessage('clipboardPaste'));
					xml.setAttribute('request','clipboard');
					xml.setAttribute('mode','paste');
					xml.setAttribute('icon','clipboard.paste');
					requests.unshift(xml);

					var xml=g740.xml.createElement('request');
					xml.setAttribute('caption',g740.getMessage('clipboardCut'));
					xml.setAttribute('request','clipboard');
					xml.setAttribute('mode','cut');
					xml.setAttribute('icon','clipboard.cut');
					requests.unshift(xml);

					var xml=g740.xml.createElement('request');
					xml.setAttribute('caption',g740.getMessage('clipboardCopy'));
					xml.setAttribute('request','clipboard');
					xml.setAttribute('mode','copy');
					xml.setAttribute('icon','clipboard.copy');
					requests.unshift(xml);
				}
				if (!requests.length) return false;

				var objMenu=new g740.Menu();
				objPanel.set('objMenu', objMenu);
				for(var i=0; i<requests.length; i++) {
					this.buildToolbarMenuItem(requests[i], objPanel, objMenu);
				}
				return true;
			},
			// Перегоняем XML описание requests в массив, пополняя значениями по умолчанию
			getRequests: function(xml, objPanel) {
				var result=[];
				var procedureName='g740.panels.getRequests';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');

				var isPredSeparator=false;
				if ((xml.nodeName=='toolbar' || xml.nodeName=='menu') && g740.xml.getAttrValue(xml,'default','0')=='1') {
					var lst=this.getRequestsDefa(objPanel);
					if (lst.length>0) {
						for (var i=0; i<lst.length; i++) result.push(lst[i]);
						isPredSeparator=true;
					}
				}

				var xmlRequests=g740.xml.findFirstOfChild(xml, {nodeName:'requests'});
				if (!xmlRequests) xmlRequests=xml;
				for(var xmlChild=xmlRequests.firstChild; xmlChild; xmlChild=xmlChild.nextSibling) {
					if (xmlChild.nodeName!='request' && xmlChild.nodeName!='separator') continue;
					if (!g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlChild,'enabled','1'),'check')) continue;

					if (isPredSeparator) {
						var xml=g740.xml.createElement('separator');
						result.push(xml);
						isPredSeparator=false;
					}
					result.push(xmlChild);
				}
				return result;
			},
			getRequestsDefa: function(objPanel) {
				var procedureName='g740.panels.getRequestsDefa';
				var result=[];
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');
				var objForm=objPanel.objForm;
				if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel.objForm');
				var objRowSet=objForm.rowsets[objPanel.rowsetName];
				var lstDefa=['undo','save','refresh','-','append','copy','-','append.into','move','link','-','delete','-','mark','unmarkall','-','shift.first','shift.before','shift.after','shift.last'];

				var isPredAction=false;
				var isPredSeparator=false;
				for(var i=0; i<lstDefa.length; i++) {
					var fullName=lstDefa[i];
					// Ставим признак, что нужен разделитель
					if (fullName=='-') {
						isPredSeparator=true;
						continue;
					}
					var r=fullName.split('.');
					while(r.length<2) r.push('');
					var requestName=r[0];
					var requestMode=r[1];
					if (objRowSet) {
						var r=objRowSet.getRequestForAnyNodeType(requestName, requestMode);
						if (!r) continue;
					}
					if (isPredAction && isPredSeparator) {
						var xml=g740.xml.createElement('separator');
						result.push(xml);
						isPredSeparator=false;
					}
					var xml=g740.xml.createElement('request');
					xml.setAttribute('name',requestName);
					if (requestMode) xml.setAttribute('mode',requestMode);
					var rowsetName='#focus';
					if (objRowSet) rowsetName=objRowSet.name;
					xml.setAttribute('rowset',rowsetName);
					result.push(xml);
					isPredAction=true;
				}
				return result;
			},
			// Формируем объект g740.Action для автоматизации работы кнопки и элемента меню
			buildAction: function(xml, objPanel) {
				var result=null;
				var procedureName='g740.panels.buildAction';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (xml.nodeName!='request') return null;
				if (!objPanel) g740.systemError(procedureName, 'errorValueUndefined', 'objPanel');
				var objForm=objPanel.objForm;
				
				var request={};
				this.buildRequestParams(xml, request);
				var rowsetName=objPanel.rowsetName;
				rowsetName=g740.xml.getAttrValue(xml,'rowset',rowsetName);
				if (rowsetName=='#this') rowsetName=objPanel.rowsetName;
				if (rowsetName=='#parent') {
					var objRowSet=objPanel.objForm.rowsets[objPanel.rowsetName];
					if (objRowSet && objRowSet.objParent) rowsetName=objRowSet.objParent.name;
				}
				var p={
					objForm: objForm,
					request: request,
					rowsetName: rowsetName
				};
				var result=new g740.Action(p);
				return result;
			},
			buildToolbarMenuItem: function(xml, objPanel, objParent) {
				var procedureName='g740.panels.buildToolbarMenuItem';
				if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
				if (xml.nodeName!='request' && xml.nodeName!='separator') return false;
				
				var result=null;
				if (xml.nodeName=='request') {
					var p={};
					p.objAction=this.buildAction(xml, objPanel);
					if (objParent.g740className == 'g740.MenuBar') {
						var style = g740.xml.getAttrValue(xml, 'style', 'icon');
						if (style == 'icon') p.showLabel = false;
						if (style == 'text') p.objAction.iconClass = '';
						var requests = this.getRequests(xml, objPanel);
						if (requests && requests.length > 0) {
							var objMenu = new g740.Menu({ style: "display: none;" });
							p.popup = objMenu;
							for (var i = 0; i < requests.length; i++) {
								this.buildToolbarMenuItem(requests[i], objPanel, objMenu);
							}
							result = new g740.PopupMenuBarItem(p, null);
						}
						else {
							result = new g740.MenuBarItem(p, null);
						}
					}
					if (objParent.g740className == 'g740.Toolbar') {
						p.g740size=objParent.g740size;
						var style=g740.xml.getAttrValue(xml,'style','icon');
						if (style=='icon') p.showLabel=false;
						if (style=='text') p.objAction.iconClass='';
						var requests=this.getRequests(xml, objPanel);
						if (requests && requests.length>0) {
							var objMenu=new g740.Menu({style: "display: none;"});
							p.dropDown=objMenu;
							for(var i=0; i<requests.length; i++) {
								this.buildToolbarMenuItem(requests[i], objPanel, objMenu);
							}
							result = new g740.ToolbarComboButton(p);
						}
						else {
							result = new g740.ToolbarButton(p);
						}
					}
					if (objParent.g740className=='g740.Panel') {
						var style=g740.xml.getAttrValue(xml,'style','icontext');
						if (style=='icon') p.showLabel=false;
						if (style=='text') p.objAction.iconClass='';
						p.region=g740.xml.getAttrValue(xml,'align','left');
						result = new g740.PanelButton(p);
					}
					if (objParent.g740className=='g740.Menu') {
						var style=g740.xml.getAttrValue(xml,'style','icontext');
						if (style=='icon') p.objAction.label='';
						if (style=='text') p.objAction.iconClass='';
						var requests=this.getRequests(xml, objPanel);
						if (requests && requests.length>0) {
							var objMenu=new g740.Menu({style: "display: none;"});
							p.popup=objMenu;
							for(var i=0; i<requests.length; i++) {
								this.buildToolbarMenuItem(requests[i], objPanel, objMenu);
							}
							result = new g740.PopupMenuItem(p);
						}
						else {
							result=new g740.MenuItem(p);
						}
					}
				}
				if (xml.nodeName=='separator') {
					if (objParent.g740className=='g740.Toolbar') {
						var result=new dijit.ToolbarSeparator();
					}
					if (objParent.g740className=='g740.Menu') {
						var result=new dijit.MenuSeparator();
					}
				}
				if (result) objParent.addChild(result);
				return result;
			},
			// Формируем облегченное описание поля, для панелей
			buildFldDef: function(xmlField, fldDef) {
				var procedureName='g740.panels.buildFldDef';
				if (!fldDef) fldDef={};
				var result={
					visible: true
				};
				for(var name in fldDef) result[name]=fldDef[name];

				if (g740.xml.isAttr(xmlField,'name')) result['name']=g740.xml.getAttrValue(xmlField,'name','');
				if (g740.xml.isAttr(xmlField,'field')) result['name']=g740.xml.getAttrValue(xmlField,'field','');

				if (g740.xml.isAttr(xmlField,'type')) result['type']=g740.xml.getAttrValue(xmlField,'type','');
				if (g740.xml.isAttr(xmlField,'size')) {
					var size=g740.xml.getAttrValue(xmlField,'size','');
					if (size=='small' || size=='medium' || size=='large') result['size']=size;
				}
				
				if (g740.xml.isAttr(xmlField,'basetype')) result['basetype']=g740.xml.getAttrValue(xmlField,'basetype','');
				if (g740.xml.isAttr(xmlField,'refid')) result['refid']=g740.xml.getAttrValue(xmlField,'refid','');
				if (g740.xml.isAttr(xmlField,'visible')) result['visible']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'visible',''),'check');
				if (g740.xml.isAttr(xmlField,'save')) result['save']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'save',''),'check');
				if (g740.xml.isAttr(xmlField,'caption')) result['caption']=g740.xml.getAttrValue(xmlField,'caption','');
				if (g740.xml.isAttr(xmlField,'readonly')) result['readonly']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'readonly',''),'check');
				if (g740.xml.isAttr(xmlField,'width')) result['width']=g740.xml.getAttrValue(xmlField,'width','');
				if (g740.xml.isAttr(xmlField,'dlgwidth')) result['dlgwidth']=g740.xml.getAttrValue(xmlField,'dlgwidth','');
				if (g740.xml.isAttr(xmlField,'len')) result['len']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'len',''),'num');
				if (g740.xml.isAttr(xmlField,'dec')) result['dec']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'dec',''),'num');
				if (g740.xml.isAttr(xmlField,'stretch')) result['stretch']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'stretch',''),'check');
				if (g740.xml.isAttr(xmlField,'nowrap')) result['nowrap']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'nowrap',''),'check');
				if (g740.xml.isAttr(xmlField,'captionup')) result['captionup']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'captionup',''),'check');
				if (g740.xml.isAttr(xmlField,'rows')) result['rows']=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField,'rows',''),'num');
				if (g740.xml.isAttr(xmlField,'list')) result['list']=g740.xml.getAttrValue(xmlField,'list','');
				if (g740.xml.isAttr(xmlField,'js_visible')) result['js_visible']=g740.xml.getAttrValue(xmlField,'js_visible','');
				if (g740.xml.isAttr(xmlField,'js_readonly')) result['js_readonly']=g740.xml.getAttrValue(xmlField,'js_readonly','');

				var t=result.type;
				if (t && !g740.fieldTypes[t]) {
					result.type='string';
					t='string';
				}
				
				fldDefType=fldDef.type;
				if (fldDefType && !result.basetype) {
					if (fldDefType=='num' && (t=='list' || t=='icons' || t=='radio')) result.basetype='num';
				}
				if (t=='memo') {
					if (g740.xml.isAttr(xmlField,'enter')) result.enter=g740.xml.getAttrValue(xmlField,'enter','0');
				}

				if (g740.xml.isAttr(xmlField,'onaction')) result.evt_onaction=g740.xml.getAttrValue(xmlField, 'onaction', '');
				if (g740.xml.isAttr(xmlField,'js_onaction')) result.js_onaction=g740.xml.getAttrValue(xmlField, 'js_onaction', '');
				var xmlScripts=g740.xml.findFirstOfChild(xmlField, {nodeName: 'scripts'});
				if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xmlField;
				var lstScript=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
				for (var indexScript=0; indexScript<lstScript.length; indexScript++) {
					var xmlScript=lstScript[indexScript];
					var name=g740.xml.getAttrValue(xmlScript, 'name', '');
					if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
					if (name=='readonly') result.js_readonly=g740.panels.buildScript(xmlScript);
					if (name=='visible') result.js_visible=g740.panels.buildScript(xmlScript);
					if (name=='onaction') result.js_onaction=g740.panels.buildScript(xmlScript);
				}
				return result;
			},
			// Формируем полное описание запроса, с параметрами
			buildRequestParams: function(xmlRequest, request) {
				var procedureName='g740.panels.buildRequestParams';
				if (!g740.xml.isXmlNode(xmlRequest)) g740.systemError(procedureName, 'errorNotXml', 'xmlRequest');

				request.name=g740.xml.getAttrValue(xmlRequest,'name',request.name);
				request.name=g740.xml.getAttrValue(xmlRequest,'request',request.name);
				request.mode=g740.xml.getAttrValue(xmlRequest,'mode',request.mode);
				if (request.name=='form') {
					request.mode=g740.xml.getAttrValue(xmlRequest,'form',request.mode);
					if (g740.xml.isAttr(xmlRequest,'modal')) request.modal=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'modal','0'),'check');
					if (g740.xml.isAttr(xmlRequest,'width')) request.width=g740.xml.getAttrValue(xmlRequest,'width','');
					if (g740.xml.isAttr(xmlRequest,'height')) request.height=g740.xml.getAttrValue(xmlRequest,'height','');
					request.closable=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'closable','1'),'check')?'1':'0';
				}
				if (request.name=='httpget') {
					request.url=g740.xml.getAttrValue(xmlRequest,'url','');
					if (request.mode=='open') request.windowName=g740.xml.getAttrValue(xmlRequest,'window','');
				}
				if (request.name=='httpput') {
					request.url=g740.xml.getAttrValue(xmlRequest,'url','');
					request.ext=g740.xml.getAttrValue(xmlRequest,'ext','');
				}
				
				if (g740.xml.isAttr(xmlRequest,'save')) {
					request.save=(g740.xml.getAttrValue(xmlRequest,'save','')==1);
				}
				if (g740.xml.isAttr(xmlRequest,'lock')) {
					request.lock=(g740.xml.getAttrValue(xmlRequest,'lock','')==1);
				}
				if (g740.xml.isAttr(xmlRequest,'confirm')) {
					request.confirm=g740.xml.getAttrValue(xmlRequest,'confirm','');
				}
				if (g740.xml.isAttr(xmlRequest,'enabled')) {
					request.enabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'enabled','1'),'check');
				}
				if (g740.xml.isAttr(xmlRequest,'caption')) {
					request.caption=g740.xml.getAttrValue(xmlRequest,'caption','');
				}
				if (g740.xml.isAttr(xmlRequest,'icon')) {
					request.icon=g740.xml.getAttrValue(xmlRequest,'icon','');
				}
				if (g740.xml.isAttr(xmlRequest,'timeout')) {
					request.timeout=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'timeout',''),'num');
				}
				if (g740.xml.isAttr(xmlRequest,'sync')) {
					request.sync=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'sync','0'),'check');
				}
				
				if (g740.xml.isAttr(xmlRequest,'js_enabled')) {
					request.js_enabled=g740.xml.getAttrValue(xmlRequest,'js_enabled','');
				}
				if (g740.xml.isAttr(xmlRequest,'js_icon')) {
					request.js_icon=g740.xml.getAttrValue(xmlRequest,'js_icon','');
				}
				if (g740.xml.isAttr(xmlRequest,'js_caption')) {
					request.js_icon=g740.xml.getAttrValue(xmlRequest,'js_caption','');
				}
				var xmlScripts=g740.xml.findFirstOfChild(xmlRequest, {nodeName: 'scripts'});
				if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xmlRequest;
				var lstScript=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
				for (var indexScript=0; indexScript<lstScript.length; indexScript++) {
					var xmlScript=lstScript[indexScript];
					var name=g740.xml.getAttrValue(xmlScript, 'name', '');
					if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
					if (name=='enabled') request.js_enabled=g740.panels.buildScript(xmlScript);
					if (name=='icon') request.js_icon=g740.panels.buildScript(xmlScript);
					if (name=='caption') request.js_caption=g740.panels.buildScript(xmlScript);
				}
				
				if (!request.params) request.params={};
				var xmlParams=g740.xml.findFirstOfChild(xmlRequest, {nodeName: 'params'});
				if (!xmlParams) xmlParams=xmlRequest;
				var lstParam=g740.xml.findArrayOfChild(xmlParams, {nodeName:'param'});
				for (var i=0; i<lstParam.length; i++) {
					var xmlParam=lstParam[i];
					if (!g740.xml.isXmlNode(xmlParam)) continue;
					
					var paramName=g740.xml.getAttrValue(xmlParam,'name','');
					if (!paramName) paramName=g740.xml.getAttrValue(xmlParam,'param','');
					if (paramName=='') continue;
					var p=request.params[paramName];
					if (!p) p={name: paramName, type:'string'};
					if (g740.xml.isAttr(xmlParam,'value')) {
						p.value=g740.xml.getAttrValue(xmlParam,'value','');
					}
					if (g740.xml.isAttr(xmlParam,'result')) {
						p.result=g740.xml.getAttrValue(xmlParam,'result','');
					}
					if (g740.xml.isAttr(xmlParam,'type')) {
						var t=g740.xml.getAttrValue(xmlParam,'type','');
						if (!g740.fieldTypes[t]) t='string';
						p.type=t;
					}
					if (g740.xml.isAttr(xmlParam,'notempty')) {
						p.notempty=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'notempty','0'),'check');
					}
					if (g740.xml.isAttr(xmlParam,'priority')) {
						p.priority=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'priority','0'),'check');
					}
					if (g740.xml.isAttr(xmlParam,'enabled')) {
						p.enabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'enabled','1'),'check');
					}
					if (g740.xml.isAttr(xmlParam,'js_enabled')) {
						p.js_enabled=g740.xml.getAttrValue(xmlParam,'js_enabled','');
					}
					if (g740.xml.isAttr(xmlParam,'js_value')) {
						p.js_value=g740.xml.getAttrValue(xmlParam,'js_value','');
					}
					var xmlScripts=g740.xml.findFirstOfChild(xmlParam, {nodeName: 'scripts'});
					if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xmlParam;
					var lstScript=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
					for (var indexScript=0; indexScript<lstScript.length; indexScript++) {
						var xmlScript=lstScript[indexScript];
						var name=g740.xml.getAttrValue(xmlScript, 'name', '');
						if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
						if (name=='enabled') p.js_enabled=g740.panels.buildScript(xmlScript);
						if (name=='value') p.js_value=g740.panels.buildScript(xmlScript);
					}
					request.params[paramName]=p;
				}
				return true;
            },
            createObjField: function (fld, p, domDiv) {
				var result = null;
                if (fld.refid && !fld.readonly) {
                    result = new g740.FieldEditor.Ref(p, domDiv);
                }
                else if (fld.type == 'string' || fld.type == 'num') {
                    result = new g740.FieldEditor.String(p, domDiv);
                }
                else if (fld.type == 'memo') {
                    result = new g740.FieldEditor.Memo(p, domDiv);
                }
                else if (fld.type == 'date' && !fld.readonly) {
					result = new g740.FieldEditor.Date(p, domDiv);
                }
                else if (fld.type == 'date' && fld.readonly) {
					p.len=10;
					result = new g740.FieldEditor.String(p, domDiv);
                }
                else if (fld.type == 'check') {
                    result = new g740.FieldEditor.Check(p, domDiv);
                }
                else if (fld.type == 'list') {
                    result = new g740.FieldEditor.List(p, domDiv);
                }
                else if (fld.type == 'icons') {
                    result = new g740.FieldEditor.Icons(p, domDiv);
                }
                else if (fld.type == 'radio') {
                    result = new g740.FieldEditor.RadioGroupBox(p, domDiv);
                }
                else if (fld.type == 'reflist') {
                    result = new g740.FieldEditor.RefList(p, domDiv);
                }
                else if (fld.type == 'reftree') {
                    result = new g740.FieldEditor.RefTree(p, domDiv);
                }
                else if (fld.type == 'button') {
                    result = new g740.FieldEditor.Button(p, domDiv);
                }
                else {
                    result = new g740.FieldEditor.String(p, domDiv);
                }
                return result;
            },
			buildScript: function(xmlScript) {
				var procedureName='g740.panels.buildScript';
				if (!g740.xml.isXmlNode(xmlScript)) g740.systemError(procedureName, 'errorNotXml', 'xmlScript');
				var result='';
				var script=dojo.trim(xmlScript.textContent);
				if (script.substr(0,9)=='function(') {
					try {
						result=eval('('+script+')');
					}
					catch(e) {
						result='';
					}
					if (typeof(result)!='function') result='';
				}
				else {
					result=script;
				}
				return result;
			}
		};

/*---------------------------------------------------------------------------
Важные свойства и методы классов панелей
	g740className					Имя базового класса, должно быть 'g740.Panel'
	isObjectDestroed				Признак - объект уничтожен
	isG740CanShowChildsTitle		Признак - класс умеет показывать заголовки дочерних панелей (g740.PanelTab, g740.PanelAccordion)
	isG740BorderContainer			Признак - класс умеет размещать дочерние панели по краям и центру (g740.Panel)
	isG740CanChilds					Признак - класс может содержать дочерние панели

	doG740AddChildPanel: function(objPanel)		Добавление дочерней панели, если нужно добавлять с прокладкой (g740.PanelAccordion)
---------------------------------------------------------------------------*/
// Класс предок панелей
		dojo.declare(
			'g740._PanelAbstract',
			dijit._Widget,
			{
				g740className: 'g740.Panel',		// Имя базового класса
				isObjectDestroed: false,			// Признак - объект уничтожен
				
				isG740CanShowChildsTitle: false,	// Признак - класс умеет показывать заголовки дочерних панелей (g740.PanelTab, g740.PanelAccordion)
				isG740BorderContainer: false,		// Признак - класс умеет размещать дочерние панели по краям и центру (g740.Panel)
				isG740CanChilds: false,				// Признак - класс может содержать дочерние панели
				isG740CanToolBar: false,			// Признак - поддерживает ToolBar
				isG740AutoMenu: false,				// Признак - принудительно формировать контекстное меню, если оно не описанно

				isG740Tree: false,					// Панель дерева
				isG740Grid: false,					// Панель таблицы grid
				isG740Fields: false,				// Панель списка полей
				isG740Clipboard: false,				// Доступны операции с буфером обмена
				
				objForm: null,
				rowsetName: null,
				nodeType: '',
				color: '',
				isShowTitle: false,
				js_visible: '',
				visible: true,
				width: '',
				height: '',
				isFocusOnShow: false,
				fontsize: 'normal',					// Размер шрифта normal|s|l|xl|xxl
				padding: '0px',						// Отступы внутри панели
				
				g740id: '',
			
				objMenu: null,
				
				// Обработчики событий
				evt_onshow: '',
				js_onshow: '',
				evt_onaction: '',
				js_onaction: '',
				
				set: function(name, value) {
					if (name=='objForm') {
						this.objForm=value;
						return true;
					}
					if (name=='rowsetName') {
						this.rowsetName=value;
						return true;
					}
					if (name=='nodeType') {
						this.nodeType=value;
						return true;
					}
					if (name=='color') {
						this.color=value;
						if (value!='') {
							var colorItem=g740.colorScheme.getColorItem(value);
							var className=colorItem.className;
							if (!dojo.hasClass(this.domNode, className)) dojo.addClass(this.domNode, className);
						}
						return true;
					}
					if (name=='isShowTitle') {
						this.isShowTitle=value;
						return true;
					}
					if (name=='isFocusOnShow') {
						this.isFocusOnShow=value;
						return true;
					}
					if (name=='js_visible') {
						this.js_visible=value;
						return true;
					}
					if (name=='js_onshow') {
						this.js_onshow=value;
						return true;
					}
					if (name=='js_onaction') {
						this.js_onaction=value;
						return true;
					}
					if (name=='evt_onshow') {
						this.evt_onshow=value;
						return true;
					}
					if (name=='evt_onaction') {
						this.evt_onaction=value;
						return true;
					}
					if (name=='width') {
						this.width=value;
						return true;
					}
					if (name=='height') {
						this.height=value;
						return true;
					}
					if (name=='g740id') {
						this.g740id=value;
						return true;
					}
					if (name=='objMenu') {
						this._setObjMenu(value);
						return true;
					}
					try {
						this.inherited(arguments);
					}
					catch (e) {
					}
				},
				constructor: function(para, domElement) {
					var procedureName='g740._PanelAbstract.constructor';
					this.on('Focus', this.onG740Focus);
					this.on('Blur', this.onG740Blur);
				},
				destroy: function() {
					var procedureName='g740._PanelAbstract.destroy';
					this.objForm=null;
					this.rowsetName=null;
					this.isObjectDestroed=true;
					if (this.objMenu) {
						this.objMenu.destroyRecursive();
						this.objMenu=null;
					}
					this.inherited(arguments);
				},
				_setObjMenu: function(objMenu) {
					if (this.objMenu) {
						this.objMenu.destroyRecursive();
						this.objMenu=null;
					}
					this.objMenu=objMenu;
					if (this.objMenu && this.objMenu.bindDomNode && this.domNode) this.objMenu.bindDomNode(this.domNode);
				},
				getRowSet: function() {
					if (!this.objForm) return null;
					if (this.objForm.isObjectDestroed) return null;
					return this.objForm.rowsets[this.rowsetName];
				},
				postCreate: function() {
					this.inherited(arguments);
				},
				postCreateBeforeChilds: function() {
				},
				onG740AfterBuild: function() {
					var lst=[];
					if (this.getChildren) lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var objChildPanel=lst[i];
						if (!objChildPanel) continue;
						objChildPanel.g740ChildOrder=i;
						if (objChildPanel.onG740AfterBuild) objChildPanel.onG740AfterBuild();
					}
				},
				doG740RepaintChildsVisible: function() {
					var isChanged=false;
					var lst=[];
					if (this.getChildren) lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var objPanel=lst[i];
						if (!objPanel) continue;
						if (objPanel.g740className=='g740.Panel' && objPanel.js_visible) {
							if (!objPanel.domNode) continue;

							var visible=false;
							var obj=null;
							if (objPanel.getRowSet) obj=objPanel.getRowSet();
							if (!obj) obj=objPanel.objForm;
							if (obj) {
								visible=g740.convertor.toJavaScript(g740.js_eval(obj, objPanel.js_visible, true),'check');
							}
							
							if (visible!=objPanel.visible) {
								if (visible) {
									dojo.style(objPanel.domNode,'display','block');
									if (objPanel.doG740Repaint) {
										objPanel.visible=visible;
										objPanel.doG740Repaint({isFull: true});
									}
								}
								else {
									dojo.style(objPanel.domNode,'display','none');
								}
								objPanel.visible=visible;
								isChanged=true;
							}
							if (!objPanel.visible) continue;
						}
					}
					if (isChanged) this.layout();
				},
				// Находим наиболее подходящую дочернюю панель
				getBestChild: function() {
					return null;
				},
				// Вариант поиска наиболее подходящей дочерней панали для наследников StackPanel
				_getBestChildStack: function(isBest) {
					if (!this.selectChild) return null; // Проверка на StackContainer
					
					var objRowSet=this.getRowSet();
					var childs=this.getChildren();
					var lstChildsNodeType=[];
					var lstChildsVisible=[];
					var childs=this.getChildren();
					var nodeType='';
					if (objRowSet) {
						var node=objRowSet.getFocusedNode();
						if (node && node.nodeType) nodeType=node.nodeType;
					}
					for (var i=0; i<childs.length; i++) {
						var objChild=childs[i];
						if (!objChild) continue;
						if (objChild.g740className!='g740.Panel') continue;
						if (!objChild.visible) continue;
						lstChildsVisible.push(objChild);
						if (nodeType && objChild.rowsetName==this.rowsetName && objChild.nodeType==nodeType) {
							lstChildsNodeType.push(objChild);
						}
					}
					var result=null;
					if (lstChildsNodeType.length>0) {
						result=lstChildsNodeType[0];
						for (var i=0; i<lstChildsNodeType.length; i++) {
							var objChild=lstChildsNodeType[i];
							if (objChild==this.selectedChildWidget) {
								result=objChild;
								break;
							}
						}
						return result;
					}
					
					if (lstChildsVisible.length>0) {
						var objBest=null;
						var objSelectedChild=null;
						for (var i=0; i<lstChildsVisible.length; i++) {
							var objChild=lstChildsVisible[i];
							if (isBest && !objBest) {
								if (objChild.best) {
									objBest=objChild;
								}
								else if (objChild.js_best) {
									var obj=null;
									if (objChild.getRowSet) obj=objChild.getRowSet();
									if (!obj) obj=objChild.objForm;
									var isBest=g740.convertor.toJavaScript(g740.js_eval(obj, objChild.js_best, true),'check');
									if (isBest) objBest=objChild;
								}
							}
							if (objChild==this.selectedChildWidget) {
								objSelectedChild=objChild;
								if (!isBest) break;
								if (objChild.best) {
									objBest=objChild;
								}
								else if (objChild.js_best) {
									var obj=null;
									if (objChild.getRowSet) obj=objChild.getRowSet();
									if (!obj) obj=objChild.objForm;
									var isBest=g740.convertor.toJavaScript(g740.js_eval(obj, objChild.js_best, true),'check');
									if (isBest) objBest=objChild;
								}
							}
						}
						if (!objBest && objSelectedChild) objBest=objSelectedChild;
						if (!objBest) objBest=lstChildsVisible[0];
						return objBest;
					}
					return null;
				},
				
				// Рекурсивная функция, проверяет вверх наличие контекстного меню
				getParentMenu: function() {
					var result=this._getParentMenu(this.rowsetName, this.nodeType);
					return result;
				},
				_getParentMenu: function(rowsetName, nodeType) {
					var objParent=this.getParent();
					if (this.objMenu) {
						if (this.rowsetName==rowsetName && this.nodeType==nodeType) return this.objMenu;
						if (this.rowsetName==rowsetName && !this.nodeType) return this.objMenu;
						if (!this.rowsetName) return this.objMenu;
					}
					if (!objParent) return null;
					if (!objParent._getParentMenu) return null;
					return objParent._getParentMenu(rowsetName, nodeType);
				},
				doG740Repaint: function(para) {
					if (!this.visible) return true;
					this.doG740RepaintChildsVisible();	// Персчитываем видимость дочерних панелей;
					if (this.getChildren) {
						var lst=this.getChildren();
						for (var i=0; i<lst.length; i++) {
							var obj=lst[i];
							if (!obj) continue;
							if (!obj.doG740Repaint) continue;
							obj.doG740Repaint(para);
						}
					}
					return true;
				},
				
				execEventOnShow: function() {
					if (!this.getEventOnShowEnabled()) return true;
					var obj=this.getRowSet();
					if (!obj) obj=this.objForm;
					if (!obj) return true;
					if (this.js_onshow) g740.js_eval(obj, this.js_onshow, true);
					if (this.evt_onshow && obj.exec) obj.exec({exec: this.evt_onshow});
					return true;
				},
				execEventOnAction: function() {
					if (!this.getEventOnActionEnabled()) return true;
					var obj=this.getRowSet();
					if (!obj) obj=this.objForm;
					if (!obj) return true;
					var result=true;
					if (this.js_onaction) result=g740.js_eval(obj, this.js_onaction, true);
					if (result && this.evt_onaction && obj.exec) obj.exec({exec: this.evt_onaction});
					return true;
				},
				getEventOnShowEnabled: function() {
					if (this.js_onshow) return true;
					if (this.evt_onshow) return true;
					return false;
				},
				getEventOnActionEnabled: function() {
					if (this.js_onaction) return true;
					if (this.evt_onaction) return true;
					return false;
				},
				

				// Передача фокуса ввода
				canFocused: function() {
					var result=false;
					if (this.getChildren) {
						var lst=this.getChildren();
						for (var i=0; i<lst.length; i++) {
							var obj=lst[i];
							if (!obj) continue;
							if (!obj.canFocused) continue;
							if (obj.canFocused()) {
								result=true;
								break;
							}
						}
					}
					return result;
				},
				doG740Focus: function() {
					var objParent=this.getParent();
					if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
					this.set('focused',true);
				},
				doG740FocusChildFirst: function() {
					var objChild=null;
					if (this.getChildren) {
						var lst=this.getChildren();
						for (var i=0; i<lst.length; i++) {
							var obj=lst[i];
							if (!obj) continue;
							if (!obj.doG740FocusChildNext) continue;
							if (!obj.canFocused || !obj.canFocused()) continue;
							if (!obj.visible) continue;
							objChild=obj;
							break;
						}
					}
					if (objChild) {
						if (objChild.doG740FocusChildFirst) objChild.doG740FocusChildFirst();
						else if (objChild.doG740Focus) objChild.doG740Focus();
						else {
							objChild.set('focused',true);
						}
					}
					else {
						this.doG740Focus();
					}
				},
				doG740FocusChildLast: function() {
					var objChild=null;
					if (this.getChildren) {
						var lst=this.getChildren();
						for (var i=lst.length-1; i>=0; i--) {
							var obj=lst[i];
							if (!obj) continue;
							if (!obj.doG740FocusChildNext) continue;
							if (!obj.canFocused || !obj.canFocused()) continue;
							if (!obj.visible) continue;
							objChild=obj;
							break;
						}
					}
					if (objChild) {
						if (objChild.doG740FocusChildFirst) objChild.doG740FocusChildLast();
						else if (objChild.doG740Focus) objChild.doG740Focus();
						else {
							objChild.set('focused',true);
						}
					}
					else {
						this.doG740Focus();
					}
				},
				doG740FocusChildNext: function(objChild) {
					var objChildNext=null;
					if (this.getChildren) {
						var lst=this.getChildren();
						var index=lst.length;
						for (var i=0; i<lst.length; i++) {
							if (lst[i]==objChild) {
								index=i;
								break;
							}
						}
						for (var i=index+1; i<lst.length; i++) {
							var obj=lst[i];
							if (!obj) continue;
							if (!obj.doG740FocusChildNext) continue;
							if (!obj.canFocused || !obj.canFocused()) continue;
							if (!obj.visible) continue;
							objChildNext=obj;
							break;
						}
					}
					if (objChildNext) {
						if (objChildNext.doG740FocusChildFirst) objChildNext.doG740FocusChildFirst();
						else if (objChildNext.doG740Focus) objChildNext.doG740Focus();
						else {
							objChildNext.set('focused',true);
						}
					}
					else {
						var objParent=this.getParent();
						if (objParent && objParent.doG740FocusChildNext) objParent.doG740FocusChildNext(this);
					}
				},
				doG740FocusChildPrev: function(objChild) {
					var objChildPrev=null;
					if (this.getChildren) {
						var lst=this.getChildren();
						var index=0;
						for (var i=0; i<lst.length; i++) {
							if (lst[i]==objChild) {
								index=i;
								break;
							}
						}
						for (var i=index-1; i>=0; i--) {
							var obj=lst[i];
							if (!obj) continue;
							if (!obj.doG740FocusChildPrev) continue;
							if (!obj.canFocused || !obj.canFocused()) continue;
							if (!obj.visible) continue;
							objChildPrev=obj;
							break;
						}
					}
					if (objChildPrev) {
						if (objChildPrev.doG740FocusChildLast) objChildPrev.doG740FocusChildLast();
						else if (objChildPrev.doG740Focus) objChildPrev.doG740Focus();
						else {
							objChildPrev.set('focused',true);
						}
					}
					else {
						var objParent=this.getParent();
						if (objParent && objParent.doG740FocusChildPrev) objParent.doG740FocusChildPrev(this);
					}
				},
				doG740SelectChild: function(objChild) {
					var objParent=this.getParent();
					if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
				},
				onG740Focus: function() {
					return true;
				},
				onG740Blur: function() {
					return true;
				}
			}
		);

// Класс Panel
		dojo.declare(
			'g740.Panel',
			[dijit.layout.BorderContainer, g740._PanelAbstract],
			{
				isG740BorderContainer: true,		// Класс умеет размещать дочерние панели по краям и центру
				isG740CanChilds: true,				// Класс может содержать дочерние панели
				isG740CanToolBar: true,				// Признак - поддерживает ToolBar
				
				isAutoHeight: false,
				postCreate: function() {
					if (this.isShowTitle && this.title) {
						var objTitle=new g740.PanelTitle({
							title: this.title,
							region: 'top'
						}, null);
						this.addChild(objTitle);
					}
					this.inherited(arguments);
				},
				
				postCreateBeforeChilds: function() {
					if (this.padding && this.padding!='0px') {
						if (this.isShowTitle && this.title) {
						}
						else {
							var objSeparatorTop=new g740.PanelSeparator({
								height: this.padding,
								region: 'top'
							},null);
							this.addChild(objSeparatorTop);
						}

						var objSeparatorBottom=new g740.PanelSeparator({
							height: this.padding,
							region: 'bottom'
						},null);
						this.addChild(objSeparatorBottom);
					}
					this.inherited(arguments);
				},

				getBestChild: function() {
					var result=null;
					var childs=this.getChildren();
					for (var i=0; i<childs.length; i++) {
						var objChild=childs[i];
						if (!objChild) continue;
						if (objChild.g740className=='g740.Panel') {
							if (objChild.visible===false) continue;
							if (!result) {
								result=objChild;
								continue;
							}
							if (objChild.region=='top' && result.region!='top') {
								result=objChild;
								continue;
							}
							if (objChild.region=='left' && result.region!='top' && result.region!='left') {
								result=objChild;
								continue;
							}
							if (objChild.region=='center' && result.region!='top' && result.region!='left' && result.region!='center') {
								result=objChild;
								continue;
							}
						}
					}
					return result;
				},
				doG740AddChildPanel: function(objChild) {
					if (objChild.region=='g740.PanelCenter') {
						var objPanelCenter=new g740.PanelCenter({region:'center', style:'border-style:none'},null);
						objPanelCenter.addChild(objChild);
						this.addChild(objPanelCenter);
					} else {
						this.addChild(objChild);
					}
				},

				onG740AfterBuild: function() {
					this.inherited(arguments);
					
					var isAutoHeight=false;
					if (!this.height && (this.region=='top' || this.region=='bottom')) {
						var isAutoHeight=true;
						var childs=this.getChildren();
						for (var i=0; i<childs.length; i++) {
							var objChild=childs[i];
							if (!objChild) continue;
							if (objChild.region!='top' && objChild.region!='bottom') {
								isAutoHeight=false;
								break;
							}
						}
					}
					this.isAutoHeight=isAutoHeight;
					this.layout();
					
					g740.execDelay.go({
						delay: 200,
						obj: this,
						func: this.layout
					});
				},
				
				_isLayoutProcess: false,
				layout: function() {
					if (this._isLayoutProcess) return;
					this._isLayoutProcess=true;
					try {
						this.inherited(arguments);
						
						if (this.isAutoHeight && this.domNode) {
							var h=1;
							var childs=this.getChildren();
							for (var i=0; i<childs.length; i++) {
								var objChild=childs[i];
								if (!objChild) continue;
								if (objChild.region!='top' && objChild.region!='bottom') continue;
								if (!objChild.domNode) continue;
								h+=objChild.domNode.offsetHeight;
							}
							this.resize({h: h});
						}
					}
					finally {
						this._isLayoutProcess=false;
					}
				}
			}
		);

// Класс PanelCenter - единственный дочерний элемент центрируется
		dojo.declare(
			'g740.PanelCenter',
			[dijit.layout._LayoutWidget, g740._PanelAbstract],
			{
				isG740CanChilds: true,				// Признак - класс может содержать дочерние панели
				addChild: function(obj, insertIndex) {
					var lst=this.getChildren();
					if (lst.length==0) this.inherited(arguments);
				},
				layout: function() {
					if (!this.domNode) return true;
					var lst=this.getChildren();
					if (lst.length==0) return true;
					var objChild=lst[0];
					if (!objChild.domNode) return true;
					var pos=dojo.geom.position(this.domNode, false);
					if (!objChild.w || !objChild.h) {
						var posChild=dojo.geom.position(objChild.domNode, false);
						objChild.w=posChild.w;
						objChild.h=posChild.h;
					}
					var p={
						l: (pos.w-objChild.w)/2, 
						t: (pos.h-objChild.h)/2,
						w: objChild.w,
						h: objChild.h
					};
					if (objChild.resize) objChild.resize(p);
				}
			}
		);
		

// Класс PanelScroll - все дочерние как top, с прокруткой
		dojo.declare(
			'g740.PanelScroll',
			[dijit.layout._LayoutWidget, g740._PanelAbstract],
			{
				isG740CanChilds: true,				// Признак - класс может содержать дочерние панели
				postCreate: function() {
					if (this.isShowTitle && this.title) {
						var objTitle=new g740.PanelTitle({
							title: this.title
						}, null);
						this.addChild(objTitle);
					}
					this.inherited(arguments);
					dojo.style(this.domNode, 'overflow-y', 'auto');
				},
				_isFirstLayoutExecuted: false,
				layout: function() {
					if (!this.domNode) return true;
					var w=this.domNode.clientWidth;
					var marginRight=0;
					if (this.domNode.scrollHeight>this.domNode.clientHeight) marginRight=25;
					
					var lst=[];
					if (this.getChildren) lst=this.getChildren();
					for(var i=0; i<lst.length; i++) {
						var objChild=lst[i];
						if (!objChild.domNode) continue;
						dojo.style(objChild.domNode, 'top', '0px');
						if (objChild.layout) objChild.layout();
						if (objChild.resize) {
							var p={
								l: 0,
								w: this.domNode.offsetWidth-marginRight
							};
							objChild.resize(p);
						}
					}
					if (!this._isFirstLayoutExecuted) {
						this._isFirstLayoutExecuted=true;
						this.doG740RepaintChildsVisible();
					}
				},
				resize: function(size) {
					this.inherited(arguments);
					this.layout();
				},
				doG740RepaintChildsVisible: function() {
					if (!this._isFirstLayoutExecuted) return;
					var isChanged=false;
					var lst=[];
					if (this.getChildren) lst=this.getChildren();
					for(var i=0; i<lst.length; i++) {
						var objChild=lst[i];
						if (!objChild.domNode) continue;
						if (objChild.g740className=='g740.Panel' && objChild.js_visible) {
							var visible=false;
							var obj=null;
							if (objChild.getRowSet) obj=objChild.getRowSet();
							if (!obj) obj=objChild.objForm;
							if (obj) {
								visible=g740.convertor.toJavaScript(g740.js_eval(obj, objChild.js_visible, true),'check');
							}
							if (visible!=objChild.visible) {
								objChild.visible=visible;
								isChanged=true;
								if (!visible) {
									var posChild=dojo.geom.position(objChild.domNode, false);
									dojo.style(objChild.domNode,'display','none');
									continue;
								}
								else {
									dojo.style(objChild.domNode,'display','block');
									if (objChild.layout) objChild.layout();
									if (objChild.doG740Repaint) objChild.doG740Repaint({isFull: true});
								}
							}
						}
					}
					if (isChanged) this.layout();
				},
				addChild: function(obj) {
					if (obj) obj.region='top';
					this.inherited(arguments);
				}
			}
		);
		
		dojo.declare(
			'g740.PanelExpander',
			[g740._PanelAbstract, dijit._TemplatedMixin, dijit.layout._LayoutWidget],
			{
				isG740BorderContainer: true,		// Класс умеет размещать дочерние панели по краям и центру
				isG740CanChilds: true,				// Класс может содержать дочерние панели
				
				templateString: '<div class="g740expander-panel">'+
					'<div class="g740expander-lockscreen" data-dojo-attach-point="domNodeLockScreen"></div>'+
					'<div class="g740expander-body" data-dojo-attach-point="domNodeBody">'+
						'<div class="g740expander-bodymax" data-dojo-attach-point="domNodeBodyMax">'+
							'<div class="g740expander-bodypanel" data-dojo-attach-point="domNodeBodyPanel"></div>'+
						'</div>'+
					'</div>'+
					'<div class="g740expander-icon" data-dojo-attach-point="domNodeIcon"></div>'+
					'<div class="g740expander-multitab" data-dojo-attach-point="domNodeMultiTab"></div>'+
				'</div>',
				isLayoutContainer: true,
				
				expanded: false,
				objPanel: null,
				objPanelButton: null,
				maxWidth: '350px',
				maxHeight: '350px',
				
				animationSpeed: 300,
				lockOpacityMax: 0.55,
				
				g740size: '',
				set: function(name, value) {
					if (name=='expanded') {
						if (value) {
							this.panelExpand();
						}
						else {
							this.panelCollapse();
						}
						return true;
					}
					else if (name=='maxWidth') {
						this.maxWidth=value;
						return true;
					}
					else if (name=='maxHeight') {
						this.maxHeight=value;
						return true;
					}
					else if (name=='lockOpacityMax') {
						this.lockOpacityMax=value;
						return true;
					}
					else if (name=='g740size') {
						if (value=='large') this.g740size=value;
						else if (value=='medium') this.g740size=value;
						else if (value=='small') this.g740size=value;
						else this.g740size=g740.config.iconSizeDefault;
						return true;
					}
					else {
						this.inherited(arguments);
					}
				},
				destroy: function() {
					if (this.objPanelButton) {
						this.objPanelButton.destroyRecursive();
						this.objPanelButton=null;
					}
					if (this.objPanel) {
						this.objPanel.destroyRecursive();
						this.objPanel=null;
					}
					this.inherited(arguments);
				},
				
				layout: function() {
					if (this._isAnimation) return;
					
					if (this.region=='left' || this.region=='right') {
						dojo.style(this.domNodeBodyMax,'width',this.maxWidth);
						if (this.objPanelButton) {
							dojo.style(this.objPanelButton.domNode,'width',this.domNode.offsetWidth+'px');
						}
					}
					if (this.region=='top' || this.region=='bottom') {
						dojo.style(this.domNodeBodyMax,'height',this.maxHeight);
						if (this.objPanelButton) {
							dojo.style(this.objPanelButton.domNode,'height',this.domNode.offsetHeight+'px');
						}
					}
					if (this.objPanel) {
						this.objPanel.resize({
							l: 0,
							t: 0,
							w: this.domNodeBodyMax.offsetWidth,
							h: this.domNodeBodyMax.offsetHeight
						});
					}
				},
				getChildren: function() {
					var result=[];
					if (this.objPanel) {
						result=this.objPanel.getChildren();
					}
					return result;
				},
				addChild: function(objChild, insertIndex) {
					var result=true;
					if (this.objPanel) {
						result=this.objPanel.addChild(objChild, insertIndex);
					}
					return result;
				},
				removeChild: function(objChild) {
					var result=false;
					if (this.objPanel)result=this.objPanel.removeChild(objChild);
					return result;
				},
				postCreate: function() {
					var appColorSchemeItem=g740.appColorScheme.getItem();
					if (appColorSchemeItem.panelExpanderLookOpacityMax) this.lockOpacityMax=appColorSchemeItem.panelExpanderLookOpacityMax;
					
					this.inherited(arguments);
					dojo.addClass(this.domNode,'collapsed');

					if (this.region=='left') dojo.addClass(this.domNode,'region-left');
					if (this.region=='right') dojo.addClass(this.domNode,'region-right');
					if (this.region=='top') dojo.addClass(this.domNode,'region-top');
					if (this.region=='bottom') dojo.addClass(this.domNode,'region-bottom');

					var p={
						style: 'width:100%;height:100%'
					};
					if (this.region=='left' || this.region=='right') {
						p.design='sidebar';
					}
					if (this.region=='top' || this.region=='bottom') {
						p.design='headline';
					}
						
					this.objPanel=new dijit.layout.BorderContainer(p, this.domNodeBodyPanel);

					dojo.on(this.domNodeLockScreen,'click',dojo.hitch(this,this.onLockScreenClick));
					dojo.on(this.domNodeLockScreen,'mouseover',dojo.hitch(this,this.onMouseOver));
					dojo.on(this.domNodeLockScreen,'mouseout',dojo.hitch(this,this.onMouseOut));
				},
				_isAnimation: false,
				panelExpand: function() {
					if (this._isAnimation) return;
					if (this.expanded) return;

					dojo.removeClass(this.domNode,'collapsed');
					dojo.addClass(this.domNode,'expanded');
					this._isAnimation=true;
					
					dojo.animateProperty({
						node: this.domNodeLockScreen,
						duration: this.animationSpeed,
						properties: {
							opacity: {start: 0, end: this.lockOpacityMax}
						}
					}).play();
					
					if (this.region=='left' || this.region=='right') {
						dojo.animateProperty({
							node: this.domNodeBody,
							duration: this.animationSpeed,
							onEnd: dojo.hitch(this, function(){
								this._isAnimation=false;
								this.layout();
								this.expanded=true;
							}),
							properties: {
								width: {start: this.domNode.offsetWidth, end: this.domNodeBodyMax.offsetWidth}
							}
						}).play();
					}
					else if (this.region=='top' || this.region=='bottom') {
						dojo.animateProperty({
							node: this.domNodeBody,
							duration: this.animationSpeed,
							onEnd: dojo.hitch(this, function(){
								this._isAnimation=false;
								this.layout();
								this.expanded=true;
							}),
							properties: {
								height: {start: this.domNode.offsetHeight, end: this.domNodeBodyMax.offsetHeight}
							}
						}).play();
					}
				},
				panelCollapse: function(isFast) {
					if (this._isAnimation) return;
					if (!this.expanded) return;

					var duration=this.animationSpeed;
					if (isFast) duration=20;
					this._isAnimation=true;

					dojo.animateProperty({
						node: this.domNodeLockScreen,
						duration: duration,
						properties: {
							opacity: {start: this.lockOpacityMax, end: 0}
						}
					}).play();

					if (this.region=='left' || this.region=='right') {
						dojo.animateProperty({
							node: this.domNodeBody,
							duration: duration,
							onEnd: dojo.hitch(this, function(){
								this._isAnimation=false;
								dojo.removeClass(this.domNode,'expanded');
								dojo.addClass(this.domNode,'collapsed');
								this.layout();
								this.expanded=false;
							}),
							properties: {
								width: {start: this.domNodeBodyMax.offsetWidth, end: this.domNode.offsetWidth}
							}
						}).play();
					}
					else if (this.region=='top' || this.region=='bottom') {
						dojo.animateProperty({
							node: this.domNodeBody,
							duration: duration,
							onEnd: dojo.hitch(this, function(){
								this._isAnimation=false;
								dojo.removeClass(this.domNode,'expanded');
								dojo.addClass(this.domNode,'collapsed');
								this.layout();
								this.expanded=false;
							}),
							properties: {
								height: {start: this.domNodeBodyMax.offsetHeight, end: this.domNode.offsetHeight}
							}
						}).play();
					}
				},

				onLockScreenClick: function() {
					if (this._isAnimation) return;
					this.set('expanded',!this.expanded);
				},
				onButtonPanelClick: function() {
					if (this._isAnimation) return;
					this._isButtonPanelClicked=true;
					this.set('expanded',false);
				},
				
				_isButtonPanelClicked: false,
				_isMouseOver: false,
				_isMouseOverExpanded: false,
				onMouseOver: function() {
					this._isMouseOver=true;
					this._isMouseOverExpanded=this.expanded;
					if (this._isAnimation) return;
					if (this._isButtonPanelClicked) return;
					if (!this.expanded) {
						g740.execDelay.go({
							delay: 60,
							obj: this,
							func: this._onMouseOverDelay
						});
					}
				},
				onMouseOut: function() {
					this._isMouseOver=false;
					this._isButtonPanelClicked=false;
				},
				_onMouseOverDelay: function() {
					if (!this._isMouseOver) return;
					if (this._isMouseOverExpanded!=this.expanded) return;
					if (this._isAnimation) return;
					this.set('expanded', !this.expanded);
				}
			}
		);
		
		
// Класс PanelTab
		dojo.declare(
			'g740.PanelTab',
			[dijit.layout.TabContainer, g740._PanelAbstract],
			{
				isG740CanShowChildsTitle: true,		// Класс умеет показывать заголовки дочерних панелей
				isG740CanChilds: true,				// Класс может содержать дочерние панели
				
				lstChildrenHidden: null,			// Список скрытых дочерних панелей

				destroy: function() {
					if (this.tablist) {
						this.tablist.TabContainer=null;
					}
					if (this.lstChildrenHidden) {
						for (var i=0; i<this.lstChildrenHidden.length; i++) {
							var objChild=this.lstChildrenHidden[i];
							if (objChild && objChild.destroyRecursive) objChild.destroyRecursive();
							this.lstChildrenHidden[i]=null;
						}
						this.lstChildrenHidden=null;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.tablist.TabContainer=this;
					this.tablist.on('KeyDown',function(e){
						if (this.TabContainer && this.TabContainer.onG740KeyDown) this.TabContainer.onG740KeyDown(e);
					});
					this.inherited(arguments);
				},
				getBestChild: function(isBest) {
					var objBestPanel=this._getBestChildStack(isBest);
					return objBestPanel;
				},
				onG740AfterBuild: function() {
					this.inherited(arguments);
					if (!this.selectedChildWidget) {
						g740.execDelay.go({
							delay: 50,
							obj: this,
							func: this.doG740FirstRefreshTabContainer
						});
					}
				},
				onG740KeyDown: function(e) {
					if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						this.doG740FocusChildFirst();
					}
					else {
						dojo.stopEvent(e);
					}
				},
				doG740FirstRefreshTabContainer: function() {
					if (this.selectedChildWidget) {
						this._showChild(this.selectedChildWidget);
						if (this.selectedChildWidget.layout) this.selectedChildWidget.layout();
					}
				},
				doG740Repaint: function(para) {
					this.inherited(arguments);
					if (!para) para={};
					var isBest=false;
					if (para.objRowSet && para.objRowSet==this.getRowSet() && (para.isFull || para.isNavigate)) {
						isBest=true;
					}
					var bestChild=this.getBestChild(isBest);
					if (bestChild && bestChild!=this.selectedChildWidget) {
						this.selectChild(bestChild);
					}
				},
				doG740RepaintChildsVisible: function() {
					var isChanged=false;
					
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var objPanel=lst[i];
						if (!objPanel) continue;
						if (!objPanel.js_visible) continue;
						var visible=false;
						var obj=null;
						if (objPanel.getRowSet) obj=objPanel.getRowSet();
						if (!obj) obj=objPanel.objForm;
						if (obj) {
							visible=g740.convertor.toJavaScript(g740.js_eval(obj, objPanel.js_visible, true),'check');
						}
						objPanel.visible=visible;
						if (!visible) {
							this.removeChild(objPanel);
							if (!this.lstChildrenHidden) this.lstChildrenHidden=[];
							this.lstChildrenHidden.push(objPanel);
							isChanged=true;
						}
					}
					if (this.lstChildrenHidden) {
						var lstShow=[];
						for (var i=0; i<this.lstChildrenHidden.length; i++) {
							var objPanel=this.lstChildrenHidden[i];
							if (!objPanel) continue;
							if (!objPanel.js_visible) continue;
							
							var visible=false;
							var obj=null;
							if (objPanel.getRowSet) obj=objPanel.getRowSet();
							if (!obj) obj=objPanel.objForm;
							if (obj) {
								visible=g740.convertor.toJavaScript(g740.js_eval(obj, objPanel.js_visible, true),'check');
							}
							objPanel.visible=visible;
							if (visible) {
								lstShow.push(objPanel);
								if (i<(this.lstChildrenHidden.length-1)) {
									var obj=this.lstChildrenHidden.pop();
									this.lstChildrenHidden[i]=obj;
									i--;
								}
								else {
									var obj=this.lstChildrenHidden.pop();
								}
								isChanged=true;
							}
						}
						
						for(var i=0; i<lstShow.length; i++) {
							var objPanel=lstShow[i];
							var insertIndex=0;
							var lst=this.getChildren();
							for(var k=0; k<lst.length; k++) {
								var obj=lst[k];
								if (obj && obj.g740ChildOrder>=objPanel.g740ChildOrder) break;
								insertIndex++;
							}
							this.addChild(objPanel, insertIndex);
							objPanel.doG740Repaint({isFull: true});
						}
					}
					if (isChanged) this.layout();
				},
				doG740SelectChild: function(objChild) {
					var objParent=this.getParent();
					if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
					if (objChild && objChild!=this.selectedChildWidget) this.selectChild(objChild);
				},
				selectChild: function(objPage) {
					var old=this.selectedChildWidget;
					this.inherited(arguments);
					if (old && old!=objPage && objPage && objPage.getChildren) {
						lst=objPage.getChildren();
						for (var i=0; i<lst.length; i++) {
							var objChildPanel=lst[i];
							if (!objChildPanel) continue;
							if (!objChildPanel.canFocused) continue;
							if (!objChildPanel.doG740Focus) continue;
							if (!objChildPanel.canFocused()) continue;
							{
								if (objChildPanel.doG740FocusChildFirst) {
									objChildPanel.doG740FocusChildFirst();
								}
								else {
									objChildPanel.doG740Focus();
								}
								break;
							}
						}
					}
					if (objPage) {
						if (objPage.doG740Repaint) objPage.doG740Repaint({
							isFull: true
						});
						if (objPage.execEventOnShow) objPage.execEventOnShow();
						if (objPage.layout) {
							g740.execDelay.go({
								delay: 50,
								obj: objPage,
								func: function() {
									var objPage=this;
									if (objPage.isObjectDestroed) return false;
									return objPage.layout();
								}
							});
						}
					}
				},
				doG740FocusChildFirst: function() {
					if (this.selectedChildWidget && this.selectedChildWidget.doG740FocusChildFirst) {
						this.selectedChildWidget.doG740FocusChildFirst();
					}
				},
				doG740FocusChildLast: function() {
					if (this.selectedChildWidget && this.selectedChildWidget.doG740FocusChildLast) {
						this.selectedChildWidget.doG740FocusChildLast();
					}
				},
				doG740FocusChildNext: function(objChild) {
					var objParent=this.getParent();
					if (objParent && objParent.doG740FocusChildNext) objParent.doG740FocusChildNext(this);
				},
				doG740FocusChildPrev: function(objChild) {
					var objParent=this.getParent();
					if (objParent && objParent.doG740FocusChildPrev) objParent.doG740FocusChildPrev(this);
				}
			}
		);
		
// Класс PanelAccordion
		dojo.declare(
			'g740.PanelAccordion',
			[g740._PanelAbstract, dijit.layout.AccordionContainer],
			{
				isG740CanShowChildsTitle: true,		// Класс умеет показывать заголовки дочерних панелей
				isG740CanChilds: true,				// Класс может содержать дочерние панели
				
				lstChildrenHidden: null,			// Список скрытых дочерних панелей
				
				destroy: function() {
					if (this.lstChildrenHidden) {
						for (var i=0; i<this.lstChildrenHidden.length; i++) {
							var objChild=this.lstChildrenHidden[i];
							if (objChild && objChild.destroyRecursive) objChild.destroyRecursive();
							this.lstChildrenHidden[i]=null;
						}
						this.lstChildrenHidden=null;
					}
					this.inherited(arguments);
				},
				doG740AddChildPanel: function(objPanel) {
					var objContentPane=new g740.AccordionContentPane(
						{
							objForm: objPanel.objForm,
							rowsetName: objPanel.rowsetName,
							nodeType: objPanel.nodeType,
							title: objPanel.title,
							js_visible: objPanel.js_visible,
							style: 'width: 100%;height:100%',
						},
						null
					);
					objContentPane.addChild(objPanel);
					this.addChild(objContentPane);
				},
				getBestChild: function() {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var node=objRowSet.getFocusedNode();
					if (!node) return null;
					return this._getBestChildStack();
				},
				doG740Repaint: function(para) {
					this.inherited(arguments);
					var bestChild=this.getBestChild();
					if (bestChild && bestChild!=this.selectedChildWidget) this.selectChild(bestChild);
					return true;
				},
				doG740RepaintChildsVisible: function() {
					var isChanged=false;
					
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var objPanel=lst[i];
						if (!objPanel) continue;
						if (!objPanel.js_visible) continue;
						var visible=false;
						var obj=null;
						if (objPanel.getRowSet) obj=objPanel.getRowSet();
						if (!obj) obj=objPanel.objForm;
						if (obj) {
							visible=g740.convertor.toJavaScript(g740.js_eval(obj, objPanel.js_visible, true),'check');
						}
						objPanel.visible=visible;
						if (!visible) {
							this.removeChild(objPanel);
							if (!this.lstChildrenHidden) this.lstChildrenHidden=[];
							this.lstChildrenHidden.push(objPanel);
							isChanged=true;
						}
					}
					if (this.lstChildrenHidden) {
						var lstShow=[];
						for (var i=0; i<this.lstChildrenHidden.length; i++) {
							var objPanel=this.lstChildrenHidden[i];
							if (!objPanel) continue;
							if (!objPanel.js_visible) continue;
							
							var visible=false;
							var obj=null;
							if (objPanel.getRowSet) obj=objPanel.getRowSet();
							if (!obj) obj=objPanel.objForm;
							if (obj) {
								visible=g740.convertor.toJavaScript(g740.js_eval(obj, objPanel.js_visible, true),'check');
							}
							objPanel.visible=visible;
							if (visible) {
								lstShow.push(objPanel);
								if (i<(this.lstChildrenHidden.length-1)) {
									var obj=this.lstChildrenHidden.pop();
									this.lstChildrenHidden[i]=obj;
									i--;
								}
								else {
									var obj=this.lstChildrenHidden.pop();
								}
								isChanged=true;
							}
						}
						
						for(var i=0; i<lstShow.length; i++) {
							var objPanel=lstShow[i];
							var insertIndex=0;
							var lst=this.getChildren();
							for(var k=0; k<lst.length; k++) {
								var obj=lst[k];
								if (obj && obj.g740ChildOrder>=objPanel.g740ChildOrder) break;
								insertIndex++;
							}
							this.addChild(objPanel, insertIndex);
							objPanel.doG740Repaint({isFull: true});
						}
					}
					if (isChanged) this.layout();
				},
				doG740SelectChild: function(objChild) {
					var objParent=this.getParent();
					if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
					if (objChild && objChild!=this.selectedChildWidget) this.selectChild(objChild);
				},
				selectChild: function(objPage) {
					var old=this.selectedChildWidget;
					this.inherited(arguments);
					if (this.selectedChildWidget && old!=this.selectedChildWidget) {
						//if (this.selectedChildWidget.doG740Focus) this.selectedChildWidget.doG740Focus();
					}
					if (objPage) {
						if (objPage.execEventOnShow) objPage.execEventOnShow();
					}
				}
			}
		);

// Класс AccordionContentPane - дочерняя панелька 1-го уровня для PanelAccordion
		dojo.declare(
			'g740.AccordionContentPane',
			[g740._PanelAbstract, dijit.layout.ContentPane],
			{
				isG740CanChilds: true,				// Класс может содержать дочерние панели
				getBestChild: function() {
					var result=null;
					var childs=this.getChildren();
					for (var i=0; i<childs.length; i++) {
						var objChild=childs[i];
						if (!objChild) continue;
						if (objChild.g740className!='g740.Panel') continue;
						if (objChild.visible) {
							result=objChild;
							break;
						}
					}
					return result;
				}
			}
		);
		
// Класс PanelBestChild
		dojo.declare(
			'g740.PanelBestChild',
			[dijit.layout.StackContainer, g740._PanelAbstract],
			{
				isG740CanChilds: true,				// Класс может содержать дочерние панели
				
				getBestChild: function() {
					return this._getBestChildStack();
				},
				doG740Repaint: function(para) {
					this.inherited(arguments);
					var bestChild=this.getBestChild();
					if (bestChild && bestChild!=this.selectedChildWidget) this.selectChild(bestChild);
				},
				doG740SelectChild: function(objChild) {
					var objParent=this.getParent();
					if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
					if (objChild && objChild!=this.selectedChildWidget) this.selectChild(objChild);
				},
				selectChild: function(objPage) {
					var old=this.selectedChildWidget;
					this.inherited(arguments);
					if (this.selectedChildWidget && old!=this.selectedChildWidget) {
						//if (this.selectedChildWidget.doG740Focus) this.selectedChildWidget.doG740Focus();
					}
				}
			}
		);
		
// Класс PanelFormContainer
		dojo.declare(
			'g740.PanelFormContainer',
			[dijit.layout.BorderContainer, g740._PanelAbstract],
			{
				design: 'sidebar',
				defaultChildName: null,
				objStackContainer: null,
				objMultiTab: null,
				objTreeMenu: null,
				objTitlePanel: null,

				g740size: g740.config.iconSizeDefault,

				isTreeMenu: false,
				treeMenuAlign: 'left',

				treeMenuWidth: '46px',
				treeMenuHeight: '46px',

				treeMenuMaxWidth: '400px',
				treeMenuMaxHeight: '400px',
				treeMenuCaption: '',
				treeMenuShowOnEmpty: false,
				
				background: '',
				bgopacity: 0.3,
				bgsize: 'cover',
				
				isMultiTab: false,
				
				_isBackground: false,
				_isExpandedOnEmptyFirst: false,
				set: function(name, value) {
					if (name=='defaultChildName') {
						this.defaultChildName=value;
						return true;
					}
					else if (name=='background') {
						this.background=value;
						return true;
					}
					else if (name=='bgopacity') {
						this.bgopacity=value;
						return true;
					}
					else if (name=='bgsize') {
						this.bgsize=value;
						return true;
					}
					else if (name=='treeMenuShowOnEmpty') {
						this.treeMenuShowOnEmpty=value?true:false;
						return true;
					}
					else if (name=='isMultiTab') {
						this.isMultiTab=value;
						return true;
					}
					else if (name=='g740size') {
						if (value=='large') this.g740size=value;
						else if (value=='medium') this.g740size=value;
						else if (value=='small') this.g740size=value;
						else this.g740size=g740.config.iconSizeDefault;
						return true;
					}
					else if (name=='isTreeMenu') {
						this.isTreeMenu=value?true:false;
						return true;
					}
					else if (name=='treeMenuAlign') {
						if (value=='left') this.treeMenuAlign=value;
						else if (value=='right') this.treeMenuAlign=value;
						else if (value=='top') this.treeMenuAlign=value;
						else if (value=='bottom') this.treeMenuAlign=value;
						else this.treeMenuAlign='left';
						return true;
					}
					else if (name=='treeMenuWidth') {
						this.treeMenuWidth=value;
						return true;
					}
					else if (name=='treeMenuHeight') {
						this.treeMenuHeight=value;
						return true;
					}
					else if (name=='treeMenuMaxWidth') {
						this.treeMenuMaxWidth=value;
						return true;
					}
					else if (name=='treeMenuMaxHeight') {
						this.treeMenuMaxHeight=value;
						return true;
					}
					else if (name=='treeMenuCaption') {
						this.treeMenuCaption=value;
						return true;
					}
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.PanelFormContainer.constructor';
					this.set('objForm', para.objForm);
					if (this.objForm) {
						this.objForm.objPanelForm=this;
					}
					this.on('Focus', this.onG740Focus);
				},
				destroy: function() {
					var procedureName='g740.PanelFormContainer.destroy';
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						this.removeChild(obj);
						obj.destroyRecursive();
					}
					if (this.objForm) this.objForm.objPanelForm=null;
					this.objMultiTab=null;
					this.objTreeMenu=null;
					this.objTitlePanel=null;
					this.objStackContainer=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					dojo.addClass(this.domNode,'g740-panelmultitabsform');

					if (this.isTreeMenu) {
						var p={
							objForm: this.objForm,
							objFormContainer: this,
							rowsetName: this.rowsetName,
							region: this.treeMenuAlign,
							g740size: 'medium'
						};
						if (this.treeMenuAlign=='left' || this.treeMenuAlign=='right') {
							p.style='width: 38px';
							if (this.treeMenuWidth) p.style='width:'+this.treeMenuWidth;
							if (this.treeMenuMaxWidth) p.maxWidth=this.treeMenuMaxWidth;
						}
						else if (this.treeMenuAlign=='top' || this.treeMenuAlign=='bottom') {
							p.style='height: 38px';
							if (this.treeMenuHeight) p.style='height:'+this.treeMenuHeight;
							if (this.treeMenuMaxHeight) p.maxHeight=this.treeMenuMaxHeight;
						}
						if (this.treeMenuCaption) p.title=this.treeMenuCaption;
							
						this.objTreeMenu=new g740.WidgetFormContainerTreeMenu(p, null);
						this.addChild(this.objTreeMenu);
					}
					
					if (this.isMultiTab) {
						this.objMultiTab=new g740.WidgetPanelFormMultiTabs({
							region: 'top'
						},null);
						this.addChild(this.objMultiTab);
					}
					else {
						this.objTitlePanel=new g740.PanelHTML({
							region: 'top',
							g740style: 'formtitle',
							g740caption: ''
						},null);
						this.addChild(this.objTitlePanel);
						dojo.addClass(this.objTitlePanel.domNode,'icons-white');
					}

					var panelBody=new dijit.layout.BorderContainer({
						region: 'center'
					}, null);
					this.addChild(panelBody);
					
					
					this.objStackContainer=new dijit.layout.StackContainer({
						region: 'center'
					}, null);
					panelBody.addChild(this.objStackContainer);
					
					var panelSplitter=new dijit.layout.BorderContainer({
						region: 'left',
						style: 'width:5px',
						'class': 'g740-panelmultitabsform-margin'
					}, null);
					panelBody.addChild(panelSplitter);
					var panelSplitter=new dijit.layout.BorderContainer({
						region: 'right',
						style: 'width:5px',
						'class': 'g740-panelmultitabsform-margin'
					}, null);
					panelBody.addChild(panelSplitter);
					var panelSplitter=new dijit.layout.BorderContainer({
						region: 'bottom',
						style: 'height:5px',
						'class': 'g740-panelmultitabsform-margin'
					}, null);
					panelBody.addChild(panelSplitter);

					dojo.style(this.objStackContainer.domNode, 'background-size', this.bgsize);
					dojo.style(this.objStackContainer.domNode, 'background-repeat', 'no-repeat');
					dojo.style(this.objStackContainer.domNode, 'background-position', 'center');
					
					this.objStackContainer.onChangeFocusedForm=dojo.hitch(this, this.onChangeFocusedForm);
					this.objStackContainer._oldSelectChild=this.objStackContainer.selectChild;
					this.objStackContainer.selectChild=function(objChild) {
						var objOld=this.selectedChildWidget;
						if (objOld==objChild) return true;
						if (objOld && objOld.g740className=='g740.Form' && !objOld.isObjectDestroed) {
							var objRowSet=objOld.getFocusedRowSet();
							if (objRowSet && objRowSet.getRequestEnabled('save','') && objRowSet.getExistUnsavedChanges()) {
								if (!objRowSet.exec({requestName:'save', sync: true})) return false;
							}
						}
						this._oldSelectChild(objChild);
						if (objChild && objChild.g740className=='g740.Form' && !objChild.isObjectDestroed) {
							objChild.execEventOnShow();
						}
						this.onChangeFocusedForm();
						return true;
					};
					
					this.objStackContainer._oldRemoveChild=this.objStackContainer.removeChild;
					this.objStackContainer.removeChild=function(objChild) {
						if (objChild) {
							if (objChild.g740className=='g740.Form' && !objChild.isObjectDestroed) {
								var objRowSet=objChild.getFocusedRowSet();
								if (objRowSet && objRowSet.getRequestEnabled('save','') && objRowSet.getExistUnsavedChanges()) {
									if (!objRowSet.exec({requestName:'save', sync: true})) return false;
								}
							}
							this._oldRemoveChild(objChild);
						}
						this.onChangeFocusedForm();
						return true;
					};
					if (this.objMultiTab) this.objMultiTab.set('objStackContainer', this.objStackContainer);
					this.onChangeFocusedForm();
					
					if (this.defaultChildName) {
						g740.execDelay.go({
							delay: 0,
							obj: g740.application,
							func: g740.application.doG740ShowForm,
							para: {formName: this.defaultChildName}
						});
					}
				},
// Отобразить экранную форму
				doG740ShowForm: function(objForm) {
					var procedureName='g740.PanelFormContainer.doG740ShowForm';
					if (!objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objForm');
					if (objForm.g740className!='g740.Form') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objForm');
					if (objForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objForm');

					var oldOnSelect=null;
					if (objForm && objForm.requests && objForm.requests['onselect']) {
						oldOnSelect=objForm.requests['onselect'];
						delete objForm.requests['onselect'];
					}
					try {
						var isCanShowForm=true;
						var childs=this.objStackContainer.getChildren();
						for (var i=0; i<childs.length; i++) {
							var objChild=childs[i];
							if (!objChild) continue;
							if (objChild.g740className!='g740.Form') continue;
							if (objChild.name==objForm.name) {
								isCanShowForm=this.objStackContainer.removeChild(objChild);
								if (isCanShowForm) objChild.destroyRecursive();
								break;
							}
						}
						if (isCanShowForm) {
							this.objStackContainer.addChild(objForm);
							this.objStackContainer.selectChild(objForm);
						}
						if (this.objMultiTab) this.objMultiTab.doG740Repaint();
						
						g740.execDelay.go({
							delay: 50,
							obj: this,
							func: this.layout
						});
						
					}
					finally {
						if (oldOnSelect && objForm && objForm.requests) objForm.requests['onselect']=oldOnSelect;
					}
				},
				getFocusedForm: function() {
					return this.objStackContainer.selectedChildWidget;
				},
				doG740Repaint: function(para) {
					if (this.objTreeMenu) {
						this.objTreeMenu.doG740Repaint(para);
					}
				},
				onChangeFocusedForm: function() {
					var objForm=this.getFocusedForm();
					
					// Меняем заголовок
					if (this.objTitlePanel) {
						var title='';
						var icon='';
						if (objForm) {
							title=objForm.title;
							icon=objForm.icon;
						}
						this.objTitlePanel.set('g740caption',title);
						if (title) {
							dojo.style(this.objTitlePanel.domNode, 'visibility', 'inherit');
							for(var domChild=this.objTitlePanel.domNode.firstChild; domChild; domChild=domChild.nextSibling) {
								if (dojo.hasClass(domChild, 'formicon')) {
									var iconClass=g740.icons.getIconClassName(icon, 'large');
									domChild.innerHTML='<div class="itemicon '+iconClass+'"></div>';
									break;
								}
							}
						}
						else {
							dojo.style(this.objTitlePanel.domNode, 'visibility', 'hidden');
						}
					}
					// Меняем background, раскрываем меню
					if (!objForm) {
						if (this.background && !this._isBackground) {
							dojo.style(this.objStackContainer.domNode, 'background-image', "url('"+this.background+"')");
							dojo.style(this.objStackContainer.domNode, 'opacity', this.bgopacity);
							dojo.addClass(this.domNode,'margin-hidden');
							this._isBackground=true;
						}
						if (this.treeMenuShowOnEmpty && this.objTreeMenu && !this._isExpandedOnEmptyFirst) {
							g740.execDelay.go({
								delay: 300,
								obj: this,
								func: function() {
									var lst=this.objStackContainer.getChildren();
									if (lst.length>0) return;
									if (!this.objTreeMenu) return;
									this.objTreeMenu.panelExpand();
								}
							});
							this._isExpandedOnEmptyFirst=true;
						}
					}
					else {
						if (this.background && this._isBackground) {
							dojo.style(this.objStackContainer.domNode, 'background-image', 'inherit');
							dojo.style(this.objStackContainer.domNode, 'opacity', '1');
							dojo.removeClass(this.domNode,'margin-hidden');
							this._isBackground=false;
						}
						this._isExpandedOnEmptyFirst=false;
					}
					if (this.objMultiTab) this.objMultiTab.doG740Repaint();
					if (this.objTreeMenu && !this.isMultiTab) this.objTreeMenu.doRefreshMultiTab();
					g740.execDelay.go({
						delay: 10,
						obj: this,
						func: this.layout
					});
				},
				closeFocusedForm: function() {
					if (this.objStackContainer) {
						var objForm=this.getFocusedForm();
						if (objForm) this.objStackContainer.removeChild(objForm);
					}
				}
			}
		);

// Виджет табулятора для PanelFormContainer
		dojo.declare(
			'g740.WidgetPanelFormMultiTabs',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				templateString: '<div class="g74-multitabs">'+
					'<div class="g74-multitabs-items" data-dojo-attach-point="domNodeItems"></div>'+
				'</div>',
				objStackContainer: null,
				
				destroy: function() {
					this.objStackContainer=null;
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='objStackContainer') {
						this.objStackContainer=value;
						return true;
					}
					this.inherited(arguments);
				},
				
				doG740Repaint: function(para) {
					// Начитываем список существующих закладок
					var lstDiv={};
					for(var domChild=this.domNodeItems.firstChild; domChild; domChild=domChild.nextSibling) {
						if (domChild.tagName!='DIV') continue;
						var name=dojo.attr(domChild,'data-name');
						lstDiv[name]=domChild;
					}
					
					var lstChilds=[];
					var selectedForm=null;
					if (this.objStackContainer) {
						lstChilds=this.objStackContainer.getChildren();
						selectedForm=this.objStackContainer.selectedChildWidget;
					}
					
					// Начитываем список экранных форм
					var lstForms={};
					for (var i=0; i<lstChilds.length; i++) {
						var objChildForm=lstChilds[i];
						if (!objChildForm) continue;
						lstForms[objChildForm.name]=objChildForm;
					}
					// Уничтожаем лишние закладки
					var lstRemove=[];
					for(var name in lstDiv) {
						if (!lstForms[name]) lstRemove.push(name);
					}
					for (var i=0; i<lstRemove.length; i++) {
						var name=lstRemove[i];
						var objChild=lstDiv[name];
						objChild.parentNode.removeChild(objChild);
						delete lstDiv[name];
					}
					delete lstRemove;
					// Добавляем отсутствующие закладки
					for (var i=0; i<lstChilds.length; i++) {
						var objChildForm=lstChilds[i];
						if (!objChildForm) continue;
						var name=objChildForm.name;
						if (!lstDiv[name]) {
							var domChild=this.doCreateTabDiv(objChildForm);
							this.domNodeItems.appendChild(domChild);
							lstDiv[name]=domChild;
						}
					}
					
					// Обновляем заголовки
					for (var name in lstDiv) {
						var domChild=lstDiv[name];
						var objChildForm=lstForms[name];
						if (!domChild) continue;
						if (!objChildForm) continue;
						if (dojo.attr(domChild,'data-title')==objChildForm.title) continue;
						for(var dom=domChild.firstChild; dom; dom=dom.nextSibling) {
							if (dom.tagName=='SPAN') {
								dom.textContent=objChildForm.title;
								dojo.attr(domChild,'data-title',objChildForm.title);
								break;
							}
						}
					}
					
					// Сортируем элементы
					if (lstChilds.length>1) {
						var objFormPred=lstChilds[lstChilds.length-1];
						var name=objFormPred.name;
						var domPred=lstDiv[name];
						for (var i=lstChilds.length-2; i>=0; i--) {
							var objFormChild=lstChilds[i];
							var name=objFormChild.name;
							var domChild=lstDiv[name];
							this.domNodeItems.insertBefore(domChild, domPred);
							domPred=domChild;
						}
					}

					// Отмечаем текущий элемент
					for(var name in lstDiv) {
						var objChild=lstDiv[name];
						var objChildForm=lstForms[name];
						if (objChildForm==selectedForm) {
							if (!dojo.hasClass(objChild,'selected')) dojo.addClass(objChild,'selected');
						}
						else {
							if (dojo.hasClass(objChild,'selected')) dojo.removeClass(objChild,'selected');
						}
					}
				},
				doCreateTabDiv: function(objChildForm) {
					var procedureName='g740.WidgetPanelMultiFormTabs.doCreateTabDiv';
					if (!objChildForm) g740.systemError(procedureName, 'errorValueUndefined', 'objChildForm');
					if (objChildForm.g740className!='g740.Form') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objChildForm');
					if (objChildForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objChildForm');

					var result=document.createElement('div');
					dojo.attr(result,'data-name',objChildForm.name);
					dojo.attr(result,'data-title',objChildForm.title);
					
					var domSpan=document.createElement('span');
					var txt=document.createTextNode(objChildForm.title);
					domSpan.appendChild(txt);
					result.appendChild(domSpan);
					
					if (objChildForm.isClosable) {
						var domClose=document.createElement('div');
						dojo.addClass(domClose,'btnclose');
						dojo.on(domClose,'click',dojo.hitch(this,function(e) {
							var domNode=e.target;
							while(domNode) {
								if (dojo.hasClass(domNode,'tabitem')) break;
								domNode=domNode.parentNode;
							}
							var name=dojo.attr(domNode,'data-name');
							this.onRemoveChild(name);
						}));
						result.appendChild(domClose);
					}
					
					dojo.addClass(result,'tabitem');
					dojo.on(result,'click',dojo.hitch(this,function(e) {
						var domNode=e.target;
						while(domNode) {
							if (dojo.hasClass(domNode,'tabitem')) break;
							domNode=domNode.parentNode;
						}
						var name=dojo.attr(domNode,'data-name');
						this.onSelectChild(name);
					}));
					return result;
				},
				onSelectChild: function(name) {
					if (!this.objStackContainer) return;
					lstChilds=this.objStackContainer.getChildren();
					for(var i=0; i<lstChilds.length; i++) {
						var objChild=lstChilds[i];
						if (!objChild) continue;
						if (objChild.name==name) {
							this.objStackContainer.selectChild(objChild);
							this.doG740Repaint();
							break;
						}
					}
				},
				onRemoveChild: function(name) {
					if (!this.objStackContainer) return;
					var selectedForm=this.objStackContainer.selectedChildWidget;
					if (selectedForm && selectedForm.name==name) {
						if (selectedForm.execEventOnClose()) {
							if (this.objStackContainer.removeChild(selectedForm)) {
								selectedForm.destroyRecursive();
							}
							this.doG740Repaint();
						}
					}
				}
			}
		);

// Виджет раскрывающегося дерева для PanelFormContainer
		dojo.declare(
			'g740.WidgetFormContainerTreeMenu',
			[g740.PanelExpander],
			{
				objTreeMenu: null,
				objFormContainer: null,
				destroy: function() {
					var procedureName='g740.WidgetFormContainerTreeMenu.destroy';
					if (this.objTreeMenu) {
						this.objTreeMenu.destroyRecursive();
						this.objTreeMenu=null;
					}
					this.objFormContainer=null;
					this.multiTabItems=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					var procedureName='g740.WidgetFormContainerTreeMenu.postCreate';
					this.inherited(arguments);
					
					dojo.addClass(this.domNode,'mainmenu');
					dojo.attr(this.domNodeIcon,'title',g740.getMessage('mainMenuCaption'));
					dojo.on(this.domNodeIcon,'click',dojo.hitch(this,this.onLockScreenClick));
					
					var p={
						region: this.region,
						g740style: 'mainmenu',
						g740size: this.g740size
					};
					if (this.region=='left' || this.region=='right') {
						p.style='width:10px';
					}
					if (this.region=='top' || this.region=='bottom') {
						p.style='height:10px';
					}
					
					this.objPanelButton=new g740.PanelHTML(p, null);
					this.objPanel.addChild(this.objPanelButton);
					dojo.on(this.objPanelButton.domNode,'click',dojo.hitch(this,this.onButtonPanelClick));

					var p={
						region: 'center',
						objForm: this.objForm,
						rowsetName: this.rowsetName,
						g740size: this.g740size
					};
					if (this.title) {
						p.title=this.title;
						p.isShowTitle=true;
					}
					this.objTreeMenu=new g740.TreeMenu(p, null);
					this.addChild(this.objTreeMenu);
					
					
					dojo.addClass(this.domNodeMultiTab,'icons-white');
					
				},
				onMouseOver: function() {
					dojo.addClass(this.domNode,'hover');
				},
				onMouseOut: function() {
					dojo.removeClass(this.domNode,'hover');
				},
				
				multiTabItems: null,
				_oldFocusedName: '',
				doRefreshMultiTab: function() {
					if (!this.objPanelButton) return;
					if (!this.objFormContainer) return;
					if (!this.objFormContainer.objStackContainer) return;
					
					if (!this.multiTabItems) this.multiTabItems={};
					
					// Добавляем новые
					var childs=this.objFormContainer.objStackContainer.getChildren();
					var names={};
					for(var i=0; i<childs.length; i++) {
						var objForm=childs[i];
						if (!objForm) continue;
						var name=objForm.name;
						if (!name) continue;
						names[name]=true;
						this.buildMultiTabItem(objForm);
					}
					
					// Удаляем лишние
					var lstDel={};
					for(var name in this.multiTabItems) {
						if (!names[name]) lstDel[name]=true;
					}
					for(var name in lstDel) {
						var item=this.multiTabItems[name];
						if (item && item.domNode) {
							this.domNodeMultiTab.removeChild(item.domNode);
							item.domNode=null;
						}
						delete this.multiTabItems[name];
					}
					
					// Сортируем
					if (childs.length>1) {
						var name='';
						var domNodeNext=null;
						var objForm=childs[childs.length-1];
						if (objForm && objForm.name) var name=objForm.name;
						var item=this.multiTabItems[name];
						if (item.domNode) domNodeNext=item.domNode;
						for(var i=childs.length-2; i>=0; i--) {
							var objForm=childs[i];
							var name='';
							if (objForm && objForm.name) var name=objForm.name;
							var item=this.multiTabItems[name];
							if (!item) continue;
							var domNode=item.domNode;
							if (!domNode) continue;
							if (domNode.nextSibling!=domNodeNext) {
								this.domNodeMultiTab.insertBefore(domNode, domNodeNext);
							}
							domNodeNext=domNode;
						}
					}
					
					var objForm=this.objFormContainer.objStackContainer.selectedChildWidget;
					var name='';
					if (objForm) name=objForm.name;
					if (name!=this._oldFocusedName) {
						var item=this.multiTabItems[this._oldFocusedName];
						if (item && item.domNode) dojo.removeClass(item.domNode, 'selected');
						var item=this.multiTabItems[name];
						if (item && item.domNode) dojo.addClass(item.domNode, 'selected');
						this._oldFocusedName=name;
					}
				},
				buildMultiTabItem: function(objForm) {
					if (!objForm) return;
					if (objForm.isObjectDestroed) return;
					if (!this.multiTabItems) this.multiTabItems={};
					
					var name=objForm.name;
					var icon=objForm.icon;
					var title=objForm.title;
					if (!this.multiTabItems[name]) {
						var item={
							icon: icon,
							title: title
						};
						var domNode=document.createElement('div');
						dojo.addClass(domNode,'item');
						dojo.on(domNode,'click',dojo.hitch(this, function(e){
							var domNode=e.target;
							while(domNode && domNode.parentNode!=this.domNodeMultiTab) domNode=domNode.parentNode;
							var name=domNode.getAttribute('data-name');
							this.doSetFocusedFormByName(name);
						}));
						
						var domNodeIcon=document.createElement('div');
						dojo.addClass(domNodeIcon,'itemicon');
						dojo.addClass(domNodeIcon,g740.icons.getIconClassName(item.icon,'medium'));
						domNode.appendChild(domNodeIcon);
						
						domNode.setAttribute('data-name',name);
						domNode.setAttribute('title',title);
						item.domNode=domNode;
						this.domNodeMultiTab.appendChild(domNode);
						this.multiTabItems[name]=item;
					}
					var item=this.multiTabItems[name];
					var domNode=item.domNode;
					if (item.icon!=icon) {
						domNode.innerHTML='';
						var domNodeIcon=document.createElement('div');
						dojo.addClass(domNodeIcon,'itemicon');
						dojo.addClass(domNodeIcon,g740.icons.getIconClassName(item.icon,'medium'));
						domNode.appendChild(domNodeIcon);
						item.icon=icon;
					}
					if (item.title!=title) {
						domNode.setAttribute('title',title);
						item.title=title;
					}
				},
				doSetFocusedFormByName: function(name) {
					if (!this.objFormContainer) return false;
					if (!this.objFormContainer.objStackContainer) return false;
					var childs=this.objFormContainer.objStackContainer.getChildren();
					for(i=0; i<childs.length; i++) {
						var objForm=childs[i];
						if (objForm.name==name) {
							this.objFormContainer.objStackContainer.selectChild(objForm);
							break;
						}
					}
				}
			}
		);
		
// Класс PanelWebBrowser
		dojo.declare(
			'g740.PanelWebBrowser',
			[g740._PanelAbstract, dijit.layout.ContentPane],
			{
				domIFrame: null,
				src: '',
				timestamp: 0,
				url: '',
				jsUrl: '',
				urlDefault: '',
				fieldName: '',
				isNoScript: false,
				repaintMode: 'auto',
				g740className: 'g740.PanelWebBrowser',
				g740params: {},
				constructor: function(para, domElement) {
					this.g740params={};
				},
				destroy: function() {
					var procedureName='g740.PanelWebBrowser.destroy';
					if (this.domIFrame) this.domIFrame=null;
					this.g740params={};
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='url') {
						this.url=value;
						if (!this.url) this.url='';
						return true;
					}
					else if (name=='jsUrl') {
						this.jsUrl=value;
						if (!this.jsUrl) this.jsUrl='';
						return true;
					}
					else if (name=='urlDefault') {
						this.urlDefault=value;
						if (!this.urlDefault) this.urlDefault='';
						return true;
					}
					else if (name=='fieldName') {
						this.fieldName=value;
						return true;
					}
					else if (name=='isNoScript') {
						this.isNoScript=value;
						return true;
					}
					else if (name=='g740params') {
						this.g740params=value;
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.domNode.style.margin='0px';
					this.domNode.style.padding='0px';
					this.domNode.style.borderWidth='0px';
					this.domNode.style.overflow='hidden';
					
					this.domIFrame=document.createElement('iframe');
					this.domIFrame.style.margin='0px';
					this.domIFrame.style.padding='0px';
					this.domIFrame.style.width='100%';
					this.domIFrame.style.height='100%';
					this.domIFrame.style.borderWidth='0px';
					this.domIFrame.style.borderStyle='none';
					this.domIFrame.border=0;
					if (this.isNoScript) dojo.attr(this.domIFrame,'sandbox','');
					this.set('content',this.domIFrame);
					this.inherited(arguments);
					if (this.repaintMode=='manual') {
						var objRowSet=this.getRowSet();
						if (!objRowSet || !objRowSet.isFilter) this.repaintMode='auto';
					}
					this.doG740Repaint();
				},
				doG740Repaint: function(para) {
					if (!this.domIFrame) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					var objRowSet=this.getRowSet();
					
					if (this.repaintMode=='manual' && objRowSet && !objRowSet.timestamp) {
						if (this.urlDefault) {
							this.timestamp=-1;
							g740.execDelay.go({
								delay: 50,
								obj: this,
								func: this._navigate,
								para: this.urlDefault
							});
						}
						return true;
					}
					
					var url=this.url;
					if (this.fieldName) {
						if (!objRowSet) return false;
						var node=objRowSet.getFocusedNode();
						var fields=objRowSet.getFields(node);
						if (!fields[this.fieldName]) return false;
						url=objRowSet.getFieldProperty({fieldName: this.fieldName});
					}
					else if (this.jsUrl) {
						var obj=this.getRowSet();
						if (!obj) obj=this.objForm;
						if (obj) var url=g740.js_eval(obj, this.jsUrl, this.url);
					}
					
					var g740params={};
					if (objRowSet) {
						g740params=objRowSet._getRequestG740params(this.g740params);
					}
					else if (this.objForm) {
						g740params=this.objForm._getRequestG740params(this.g740params);
					}
					
					var urlParams='';
					var urlParamDelimiter='';
					for(var paramName in g740params) {
						if (g740params[paramName]=='') continue;
						if (paramName=='http.url') url=g740params[paramName];
						urlParams+=urlParamDelimiter+paramName+'='+encodeURIComponent(g740params[paramName]);
						urlParamDelimiter='&';
					}
					if (urlParams) {
						urlParamDelimiter='?';
						if (url.indexOf('?')>=0) urlParamDelimiter='&';
						url+=urlParamDelimiter+urlParams;
					}
					
					if (url==null) url='';
					if (url=='') url='about:blank';
					
					g740.execDelay.go({
						delay: 50,
						obj: this,
						func: this._navigate,
						para: url
					});
					return true;
				},
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					return true;
				},
				doG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
				},
				canFocused: function() {
					return true;
				},
				_navigate: function(url) {
					if (!url) url='';
					var objRowSet=this.getRowSet();
					if (objRowSet && this.repaintMode=='manual') {
						if (this.timestamp!=objRowSet.timestamp) {
							this.timestamp=objRowSet.timestamp;
							if (this.timestamp && url.indexOf('&timestamp=')<0 && url.indexOf('?timestamp=')<0) {
								var urlParamDelimiter='?';
								if (url.indexOf('?')>=0) urlParamDelimiter='&';
								url+=urlParamDelimiter+'timestamp='+this.timestamp;
							}
							this.domIFrame.src=url;
							this.src=url;
						}
					}
					else {
						if (this.src==url) return true;
						this.domIFrame.src=url;
						this.src=url;
					}
				}
			}
		);

// Класс PanelExtControl
		dojo.declare(
			'g740.PanelExtControl',
			[g740._PanelAbstract, dijit.layout.ContentPane],
			{
				domIFrame: null,
				src: '',
				url: 'about:blank',
				params: {},
				constructor: function(para, domElement) {
					this.params={};
				},
				destroy: function() {
					var procedureName='g740.PanelExtControl.destroy';
					if (this.domIFrame) this.domIFrame=null;
					this.params={};
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='url') {
						if (this.url==value) return true;
						if (value) {
							this.url=value;
						}
						else {
							this.url='about:blank';
						}
						if (this.domIFrame) this.domIFrame.src=this.url;
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.domNode.style.margin='0px';
					this.domNode.style.padding='0px';
					this.domNode.style.borderWidth='0px';
					this.domNode.style.overflow='hidden';
					
					this.domIFrame=document.createElement('iframe');
					this.domIFrame.style.margin='0px';
					this.domIFrame.style.padding='0px';
					this.domIFrame.style.width='100%';
					this.domIFrame.style.height='100%';
					this.domIFrame.style.borderWidth='0px';
					this.domIFrame.style.borderStyle='none';
					this.domIFrame.border=0;
					this.set('content',this.domIFrame);
					this.domIFrame.src=this.url;
					dojo.on(this.domIFrame, 'load', dojo.hitch(this, this.onLoad));
					this.inherited(arguments);
				},
				onLoad: function() {
					var para={
						isFull: true,
						objRowSet: this.getRowSet()
					};
					this.doG740Repaint(para);
				},
				doG740Repaint: function(para) {
					if (!this.domIFrame) return false;
					var domIframeWindow=this.domIFrame.contentWindow;
					if (!domIframeWindow) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					if (!domIframeWindow.document) return false;
					if (domIframeWindow.document.readyState!='complete') return false;
					if (!domIframeWindow.doG740Repaint) return false;

					var p={};
					for(var paraName in para) p[paraName]=para[paraName];
					var g740params={};
					var objRowSet=this.getRowSet();
					if (objRowSet) {
						g740params=objRowSet._getRequestG740params(this.params);
					}
					else if (this.objForm) {
						g740params=this.objForm._getRequestG740params(this.params);
					}
					p.G740params=g740params;
					
					domIframeWindow.doG740Repaint(p);
					return true;
				}
			}
		);

// Класс PanelImg
		dojo.declare(
			'g740.PanelImg',
			[g740._PanelAbstract, dijit.layout.ContentPane],
			{
				domIMG: null,
				url: '',
				size: 'contain',
				fieldName: '',
				set: function(name, value) {
					if (name=='url') {
						if (this.url==value) return true;
						if (value) {
							this.url=value;
						}
						else {
							this.url='';
						}
						if (this.domIMG) this.domIMG.style.backgroundImage="url('"+this.url+"')";
						return true;
					}
					if (name=='fieldName') {
						this.fieldName=value;
						return true;
					}
					if (name=='size' && value!=this.size) {
						this.size=value;
						if (this.domIMG) this.domIMG.style.backgroundSize=value;
						return true;
					}
					this.inherited(arguments);
				},
				destroy: function() {
					var procedureName='g740.PanelWebBrowser.destroy';
					if (this.domIMG) this.domIMG=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.domIMG=document.createElement('div');
					this.domIMG.style.width='100%';
					this.domIMG.style.height='100%';
					this.domIMG.style.borderStyle='none';
					this.domIMG.style.backgroundRepeat='no-repeat';
					this.domIMG.style.backgroundPosition='center';
					this.domIMG.style.backgroundSize=this.size;
					if (this.url) this.domIMG.style.backgroundImage="url('"+this.url+"')";
					this.set('content',this.domIMG);
					this.inherited(arguments);
				},
				doG740Repaint: function(para) {
					if (!this.fieldName) return true;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					var node=objRowSet.getFocusedNode();
					var fields=objRowSet.getFields(node);
					if (!fields[this.fieldName]) return false;
					var url=objRowSet.getFieldProperty({fieldName: this.fieldName});
					this.set('url',url);
				}
			}
		);

// Класс PanelMemo
		dojo.declare(
			'g740.PanelMemo',
			[g740.Panel],
			{
				fieldName: '',
				rowId: null,
				nodeType: '',
				params: {},
				domTextArea: null,
				enter: false,
				destroy: function() {
					var procedureName='g740.PanelMemo.destroy';
					this.params={};
					if (this.domTextArea) {
						this.domTextArea=null;
					}
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='fieldName') {
						this.fieldName=value;
						return true;
					}
					if (name=='focused') {
						if (value && this.domTextArea) {
							this.domTextArea.focus();
						}
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					var objBody=new dijit.layout.BorderContainer(
						{
							region: 'center'
						},
						null
					);
					this.addChild(objBody);
					dojo.addClass(objBody.domNode, 'g740-panel-memo');
					this.domTextArea=document.createElement('textarea');
					this.domTextArea.objPanelMemo=this;
					dojo.addClass(this.domTextArea, 'g740-textarea');
					objBody.domNode.appendChild(this.domTextArea);
					this.domTextArea.onchange=function(){
						if (!this.objPanelMemo) return false;
						this.objPanelMemo.onG740Change(this.value);
					};
					dojo.on(this.domTextArea, 'keydown', dojo.hitch(this, this.onKeyDown));
					this.focusNode=this.domTextArea;
					
					var domBtn=document.createElement('div');
					dojo.addClass(domBtn,'btn');
					objBody.domNode.appendChild(domBtn);
					dojo.on(domBtn, 'click', dojo.hitch(this, this.onBtnClick));
					this.inherited(arguments);
				},
				onBtnClick: function() {
					var objRowSet=this.getRowSet();
					var readOnly=objRowSet.getFieldProperty({fieldName: this.fieldName, propertyName: 'readonly'});
					var objDialog=new g740.DialogEditorMemo(
						{ 
							objForm: this.objForm,
							rowsetName: this.rowsetName,
							fieldName: this.fieldName,
							nodeType: this.nodeType,
							domNodeOwner: this.domNode,
							objOwner: this,
							readOnly: readOnly,
							duration: 0, 
							draggable: false
						}
					);
					objDialog.show();
				},
				getNode: function() {
					var result=null;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return result;
					result=objRowSet.getFocusedNode();
					return result;
				},
				// Блокируем обратную отписку изменений в RowSet из перерисовки
				_repaint: {
					isRepaint: false,
					value: ''
				},
				doG740Repaint: function(para) {
					if (!this.domTextArea) return true;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					
					var readOnly=true;
					var value='';
					this.nodeType='';
					this.rowId=null;
					var node=this.getNode();
					if (node) {
						this.nodeType = node.nodeType;
						this.rowId=node.id;
						if (!this.nodeType) this.nodeType = '';
						var objRowSet=this.getRowSet();
						value=objRowSet.getFieldProperty({fieldName: this.fieldName});
						readOnly=objRowSet.getFieldProperty({fieldName: this.fieldName, propertyName: 'readonly'});
					}
					this._repaint.isRepaint=true;
					this._repaint.value=value;
					this.domTextArea.value=value;
					
					var oldReadOnly=this.domTextArea.readOnly;
					if (oldReadOnly!=readOnly) {
						this.domTextArea.readOnly=readOnly;
						if (readOnly) {
							if (!dojo.hasClass(this.domTextArea, 'dijitTextBoxReadOnly')) dojo.addClass(this.domTextArea, 'dijitTextBoxReadOnly');
						}
						else {
							if (dojo.hasClass(this.domTextArea, 'dijitTextBoxReadOnly')) dojo.removeClass(this.domTextArea, 'dijitTextBoxReadOnly');
						}
					}
				},
				onG740Change: function(newValue) {
					try {
						var objRowSet=this.getRowSet();
						if (!objRowSet) return false;
						var fld=this.getFldDef();
						if (!fld) return false;
						// Если onchange вызван из doG740Repaint, то отписывать обратно в RowSet не надо
						if (this._repaint.isRepaint) {
							if (g740.convertor.toG740(newValue,fld.type)==g740.convertor.toG740(this._repaint.value,fld.type)) {
								this._repaint.isRepaint=false;
								this._repaint.value='';
								return true;
							}
						}
						if (objRowSet.isTree && this.nodeType) {
							var nodeType='';
							var node=objRowSet.getFocusedNode();
							if (node) nodeType=node.nodeType;
							if (this.nodeType!=nodeType) return false;
						}

						objRowSet.setFieldProperty({fieldName: this.fieldName, propertyName: 'value', value: newValue, rowId: this.rowId});
						if (fld.save && this.objRowSet.getExistUnsavedChanges()) {
							objRowSet.exec({requestName: 'save'});
						}
					}
					finally {
					}
				},
				onG740Blur: function() {
					if (this.domTextArea) this.onG740Change(this.domTextArea.value);
					return true;
				},
				onKeyDown: function(e) {
					if (this.objForm && this.objForm.getRequestByKey) rr=this.objForm.getRequestByKey(e, this.rowsetName);
					if (rr) {
						dojo.stopEvent(e);
						if (this.domNodeInput) this.onG740Change(this.domNodeInput.value);
						this.objForm.exec({
							requestName: rr.name,
							requestMode: rr.mode
						});
						return;
					}

					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onBtnClick();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						var isEnter=this.enter;
						var fld=null;
						var fields=null;
						var objRowSet=this.getRowSet();
						if (objRowSet) fields=objRowSet.getFieldsByNodeType(this.nodeType);
						if (fields) fld=fields[this.fieldName];
						if (fld && fld.enter) isEnter=true;
						
						if (isEnter && e.keyCode==13) {
						}
						else {
							dojo.stopEvent(e);
							if (this.domTextArea) this.onG740Change(this.domTextArea.value);
							var objParent=this.getParent();
							if (objParent && objParent.doG740FocusChildNext) objParent.doG740FocusChildNext(this);
						}
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.domTextArea) this.onG740Change(this.domTextArea.value);
						var objParent=this.getParent();
						if (objParent) objParent.doG740FocusChildPrev(this);
					}
				},
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					return true;
				},
				doG740Focus: function() {
					if (this.domTextArea) this.domTextArea.focus();
				},
				canFocused: function() {
					return true;
				},
				doG740FocusChildFirst: function() {
					if (this.domTextArea) this.domTextArea.focus();
				},
				doG740FocusChildLast: function() {
					if (this.domTextArea) this.domTextArea.focus();
				},
				getFldDef: function() {
					var result=false;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var result=fields[this.fieldName];
					if (!result) return false;
					return result;
				}
			}
		);

// Класс PanelHTML
		dojo.declare(
			'g740.PanelHTML',
			[g740._PanelAbstract, dijit._TemplatedMixin],
			{
				templateString: '<div class="g740html-panel">'+'</div>',
				
				backgroundColor: '',
				fontColor: '',
				backgroundImage: '',
				g740caption: '',
				g740size: '',
				g740style: '',
				g740login: '',

				js_tp: '',
				js_tpname: '',
				js_tpvalue: '',

				templates: {},
//	var template=this.templates[name]
//		template.js_enabled
//		template.html
				_html: '',
				_size: 0,

				constructor: function(para, domElement) {
					this.templates={};
				},
				destroy: function() {
					this.templates={};
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='backgroundColor') {
						this.backgroundColor=value;
						if (this.domNode) dojo.style(this.domNode,'background-color',this.backgroundColor);
						return true;
					}
					else if (name=='fontColor') {
						this.fontColor=value;
						if (this.domNode) dojo.style(this.domNode,'color',this.fontColor);
						return true;
					}
					else if (name=='backgroundImage') {
						this.backgroundImage=value;
						if (this.domNode) dojo.style(this.domNode,'background-image',this.backgroundImage);
						return true;
					}
					else if (name=='g740login') {
						this.g740login=value;
						return true;
					}
					else if (name=='g740caption') {
						this.g740caption=value;
						if (this.domNode) this.doG740Repaint();
						return true;
					}
					else if (name=='js_tp') {
						this.js_tp=value;
						return true;
					}
					else if (name=='js_tpname') {
						this.js_tpname=value;
						return true;
					}
					else if (name=='js_tpvalue') {
						this.js_tpvalue=value;
						return true;
					}
					else if (name=='templates') {
						this.templates=value;
						return true;
					}
					else if (name=='g740style') {
						this.g740style=value;
						return true;
					}
					else if (name=='g740size') {
						if (value=='large') this.g740size=value;
						else if (value=='medium') this.g740size=value;
						else if (value=='small') this.g740size=value;
						else this.g740size=g740.config.iconSizeDefault;
						return true;
					}
					else {
						this.inherited(arguments);
					}
				},
				postCreate: function() {
					this.inherited(arguments);
					if (this.backgroundColor) this.set('backgroundColor', this.backgroundColor);
					if (this.fontColor) this.set('fontColor', this.fontColor);
					if (this.backgroundImage) this.set('backgroundImage', this.backgroundImage);
					if (this.g740size) this.set('g740size', this.g740size);
					if (this.g740caption!='' && !this.g740style) this.g740style='caption';
					if (this.g740style) this.set('g740style', this.g740style);

					var className='g740html-panel';
					if (this.g740size) className+=' g740'+this.g740size;
					if (this.g740style) className+=' '+this.g740style;
					this.domNode.className=className;
					
					dojo.attr(this.domNode,'title','');
					this.doG740Repaint();
				},
				doG740Repaint: function(para) {
					if (!para) para={};
					var template=this.getTemplate();
					var html=this.getTemplateHtml(template);
					if (html!=this._html) {
						this._html=html;
						this.domNode.innerHTML=html;
						
						var pos=dojo.geom.position(this.domNode, false);
						var size=this._size;
						if (this.region=='left' || this.region=='right') size=pos.w;
						if (this.region=='top' || this.region=='bottom') size=pos.h;
						if (size!=this._size) {
							this._size=size;
							var objParent=this.getParent();
							if (objParent && objParent.layout) objParent.layout();
						}
					}
				},
				canFocused: function() {
					return false;
				},
				// Выбирает из списка шаблонов первый подходящий
				getTemplate: function() {
					var obj=this.getRowSet();
					if (!obj) obj=this.objForm;
					if (this.js_tp) {
						var result={};
						result.html=g740.js_eval(obj, this.js_tp, '');
						return result;
					}
					if (this.js_tpname) {
						var name=g740.js_eval(obj, this.js_tpname, '');
						if (name) {
							var result=this.templates[name];
							if (result) return result;
						}
					}
					var result={};
					for(var name in this.templates) {
						var template=this.templates[name];
						if (!template) continue;
						if (template.js_enabled) {
							if (!g740.js_eval(obj, template.js_enabled, true)) continue;
						}
						result=template;
						break;
					}
					return result;
				},
				getTemplateHtml: function(template) {
					var result='';
					if (template) result=template.html;
					if (!result) result=this.getTemplateDefault();
					
					var lst=result.split('%%');
					for (var i=1; i<lst.length; i+=2) {
						var name=lst[i];
						var value=this.getTemplateValue(name);
						lst[i]=value;
					}
					result=lst.join('');
					return result;
				},
				getTemplateDefault: function() {
					if (this.g740style=='title' || this.g740style=='formtitle') {
						var className='g740html-label';
						if (this.region=='left' || this.region=='right') {
							className+=' vertical';
						}
						var result='<div class="'+className+'">%%CAPTION%%</div>';
						if (this.g740login) result+='%%LOGIN%%';
						if (this.g740style=='formtitle') {
							result='<div class="formicon"></div>'+result+'%%CLOSE%%';
						}
					}
					else {
						result='%%CAPTION%%';
					}
					return result;
				},
				getTemplateValue: function(name) {
					if (name=='CAPTION') {
						var caption=this.g740caption;
						var lst=caption.split('%%');
						for (var i=1; i<lst.length; i+=2) {
							var name=lst[i];
							var value='';
							if (name!='CAPTION') value=this.getTemplateValue(name);
							lst[i]=value;
						}
						caption=lst.join('');
						
						var result='';
						if (this.region=='left' || this.region=='right') {
							if (caption) for(var i=0; i<caption.length; i++) {
								if (i!=0) result+='<br>';
								result+=caption[i].toHtml();
							}
						}
						else {
							if (caption) result=caption.toHtml();
						}
						return result;
					}
					if (name=='LOGIN') {
						var messageDisconnect=g740.getMessage('disconnect').toHtml();
						var messageLogin=this.g740login.toHtml();
						var result='<div class="connecteduser" title="'+messageDisconnect+'" onclick="execRequest('+"'disconnect'"+')">'+
							'<div class="label">'+messageLogin+'</div>'+
							'<div class="icon"></div>'+
							'</div>';
						return result;
					}
					if (name=='CLOSE') {
						var result='<div class="closeicon" onclick="g740.application.closeFocusedForm()"></div>';
						return result;
					}
					var obj=this.getRowSet();
					if (!obj) obj=this.objForm;
					if (this.js_tpvalue) {
						var result=g740.js_eval(obj, this.js_tpvalue,'');
						return result;
					}
					try {
						var oldEvalObj=g740.js_eval_obj;
						try {
							g740.js_eval_obj=obj;
							var result=g740.convertor.js2text(get(name,''));
						}
						finally {
							g740.js_eval_obj=oldEvalObj;
						}
					}
					catch (e) {
						var result='';
					}
					if (!result && result!==0) result='';
					result=result.toString().toHtml();
					return result;
				},
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					return true;
				}
			}
		);

// Класс PanelListHTML
		dojo.declare(
			'g740.PanelListHTML',
			[g740._PanelAbstract, dijit._TemplatedMixin],
			{
				isG740CanToolBar: true,
				isG740CanButtons: true,
				isLayoutContainer: true,
				
				templateString: '<div class="g740listhtml-panel">'+
					'<div data-dojo-attach-point="domNodeTitle"></div>'+
					'<div data-dojo-attach-point="domNodeToolbar"></div>'+
					'<div data-dojo-attach-point="domNodeDivBody"></div>'+
					'<div data-dojo-attach-point="domNodeButtons"></div>'+
					'<div data-dojo-attach-point="domNodePaginator"></div>'+
				'</div>',

				g740style: '',
				orientation: 'vertical',
				
				js_tp: '',
				js_tpname: '',
				js_tpvalue: '',
				
				title: '',

				htmlItems: {},
				templates: {},
				
				_size: 0,
				_oldId: '',
				objToolBar: null,
				objPanelButtons: null,
				objPaginator: null,
//	var template=this.templates[name]
//		template.js_enabled
//		template.html
				constructor: function(para, domElement) {
					this.templates={};
					this.htmlItems={};
				},
				destroy: function() {
					this.templates={};
					this.htmlItems={};
					if (this.objToolBar) {
						this.objToolBar.destroyRecursive();
						this.objToolBar=null;
					}
					if (this.objPanelButtons) {
						this.objPanelButtons.destroyRecursive();
						this.objPanelButtons=null;
					}
					if (this.objPaginator) {
						this.objPaginator.destroyRecursive();
						this.objPaginator=null;
					}
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='g740style') {
						this.g740style=value;
						return true;
					}
					else if (name=='js_tp') {
						this.js_tp=value;
						return true;
					}
					else if (name=='js_tpname') {
						this.js_tpname=value;
						return true;
					}
					else if (name=='js_tpvalue') {
						this.js_tpvalue=value;
						return true;
					}
					else if (name=='templates') {
						this.templates=value;
						return true;
					}
					else {
						this.inherited(arguments);
					}
				},
				addChild: function(obj) {
					if (!obj) return;
					if (obj.g740className=='g740.Toolbar') {
						if (this.objToolBar) this.objToolBar.destroyRecursive();
						this.objToolBar=obj;
						if (this.objToolBar.domNode && this.domNodeToolbar) this.domNodeToolbar.appendChild(this.objToolBar.domNode);
					} 
					else if (obj.isG740PanelButtons) {
						if (this.objPanelButtons) this.objPanelButtons.destroyRecursive();
						this.objPanelButtons=obj;
						if (this.objPanelButtons.domNode && this.domNodeButtons) this.domNodeButtons.appendChild(this.objPanelButtons.domNode);
					}
				},
				postCreate: function() {
					this.inherited(arguments);
					if (this.g740style) dojo.addClass(this.domNodeDivBody, this.g740style);
					if (this.orientation=='vertical') {
						dojo.addClass(this.domNode,'g740listhtml-vertical');
						dojo.style(this.domNodeDivBody,'overflow-y','auto');
						dojo.style(this.domNodeDivBody,'position','relative');
					}
					if (this.orientation=='horizontal') {
						dojo.addClass(this.domNode,'g740listhtml-horizontal');
					}
					dojo.attr(this.domNode,'title','');
					
					this.domNodeTitle.innerHTML='';
					if (this.title) {
						objDiv=document.createElement('div');
						objDiv.className='g74-paneltitle';
						var objText=document.createTextNode(this.title);
						objDiv.appendChild(objText);
						this.domNodeTitle.appendChild(objDiv);
					}
					
					var objRowSet=this.getRowSet();
					if (objRowSet && objRowSet.paginatorCount) {
						this.objPaginator=new g740.Paginator({
								rowsetName: objRowSet.name,
								objForm: this.objForm
							},
							null
						);
						this.domNodePaginator.appendChild(this.objPaginator.domNode);
					}
				},
				layout: function() {
					if (!this.domNode) return;
					if (this.orientation=='vertical') {
						var h=this.domNode.clientHeight-this.domNodeTitle.offsetHeight-this.domNodeToolbar.offsetHeight-this.domNodeButtons.offsetHeight-this.domNodePaginator.offsetHeight-1;
						if (this.title) h=h-2;
						if (this.objToolBar) h=h-6;
						if (h<0) h=0;
						dojo.style(this.domNodeDivBody,'height',h+'px');
					}
					if (this.objPanelButtons) this.objPanelButtons.resize();
				},
				resize: function(size) {
					if (!this.domNode) return false;
					if (!size) return true;
					dojo.style(this.domNode,'left',size.l+'px');
					dojo.style(this.domNode,'top',size.t+'px');
					dojo.style(this.domNode,'width',size.w+'px');
					dojo.style(this.domNode,'height',size.h+'px');
					this.layout();
				},

				doScrollToFocused: function() {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (objRowSet.isObjectDestroed) return false;
					if (!this.domNode) return false;
					var htmlItem=this.htmlItems[objRowSet.getFocusedId()];
					if (!htmlItem) return false;
					var domNode=htmlItem.domNode;
					if (!domNode) return false;
					if (this.orientation=='vertical') {
						var t=this.domNodeDivBody.scrollTop;
						var h=this.domNodeDivBody.offsetHeight;
						if (domNode.offsetTop<t) {
							this.domNodeDivBody.scrollTop=domNode.offsetTop;
						}
						else if ((domNode.offsetTop+domNode.offsetHeight)>(t+h)) {
							this.domNodeDivBody.scrollTop=domNode.offsetTop+domNode.offsetHeight-h;
						}
					}
				},

				canFocused: function() {
					return false;
				},
				doG740Repaint: function(para) {
					if (!para) para={};
					if (!para.objRowSet) return true;
					var objRowSet=this.getRowSet();
					if (para.objRowSet!=objRowSet) return true;
					if (objRowSet.isObjectDestroed) return false;
					if (this.objPaginator) {
						this.objPaginator.repaint();
					}
					if (para.isFull) {
						this.buildHtmlItemsFull();
					}
					else if (para.isRowUpdate) {
						this.buildHtmlItem(objRowSet.getFocusedId());
					}
					var newId=objRowSet.getFocusedId();
					if (newId!=this._oldId) {
						var htmlItem=this.htmlItems[this._oldId];
						if (htmlItem && htmlItem.domNode) {
							dojo.removeClass(htmlItem.domNode,'current');
						}
						var htmlItem=this.htmlItems[newId];
						if (htmlItem && htmlItem.domNode) {
							dojo.addClass(htmlItem.domNode,'current');
						}
						this._oldId=newId;
					}
					this.doScrollToFocused();
					
					if (this.orientation=='horizontal') {
						var pos=dojo.geom.position(this.domNode, false);
						if (this._size!=pos.h) {
							this._size=pos.h;
							var objParent=this.getParent();
							if (objParent && objParent.layout) objParent.layout();
						}
					}
					
					return true;
				},
				buildHtmlItemsFull: function() {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (objRowSet.isObjectDestroed) return false;
					var objTreeStorage=objRowSet.objTreeStorage;
					if (!objTreeStorage) return false;
					var parentNode=objRowSet.getFocusedParentNode();
					if (parentNode) {
						// Пересоздаем существующие
						var ordered=objTreeStorage.getChildsOrdered(parentNode);
						for(var i=0; i<ordered.length; i++) {
							var node=ordered[i];
							this.buildHtmlItem(node.id);
						}
						// Удаляем лишние
						var lstDel={};
						for(var id in this.htmlItems) {
							if (!objTreeStorage.getNode(id, parentNode)) lstDel[id]=true;
						}
						for(var id in lstDel) {
							var htmlItem=this.htmlItems[id];
							if (htmlItem && htmlItem.domNode) {
								this.domNodeDivBody.removeChild(htmlItem.domNode);
								htmlItem.domNode=null;
							}
							delete this.htmlItems[id];
						}
						
						// Сортируем
						var domNodeClearBoth=null;
						if (this.orientation=='horizontal') {
							var domNode=this.domNodeDivBody.lastChild;
							if (domNode && domNode.getAttribute('data-clearboth')=='1') {
								domNodeClearBoth=domNode;
							}
						}
						
						if (ordered.length<2) return true;
						var idNext=ordered[ordered.length-1].id;
						var domNodeNext=null;
						var htmlItemNext=this.htmlItems[idNext];
						if (htmlItemNext && htmlItemNext.domNode) domNodeNext=htmlItemNext.domNode;
						for(var i=ordered.length-2; i>=0; i--) {
							var id=ordered[i].id;
							var htmlItem=this.htmlItems[id];
							if (!htmlItem) continue;
							if (!htmlItem.domNode) continue;
							var domNode=htmlItem.domNode;
							if (domNodeNext) {
								if (domNode.nextSibling!=domNodeNext) this.domNodeDivBody.insertBefore(domNode, domNodeNext);
							}
							domNodeNext=domNode;
						}
						if (this.orientation=='horizontal') {
							if (!domNodeClearBoth) {
								for(var domNode=this.domNodeDivBody.firstChild; domNode; domNode=domNode.nextSibling) {
									if (domNode.getAttribute('data-clearboth')=='1') {
										domNodeClearBoth=domNode;
										break;
									}
								}
							}
							if (!domNodeClearBoth) {
								domNodeClearBoth=document.createElement('div');
								domNodeClearBoth.setAttribute('data-clearboth','1');
								dojo.addClass(domNodeClearBoth,'clearboth');
								dojo.style(domNodeClearBoth,'clear','both');
							}
							this.domNodeDivBody.appendChild(domNodeClearBoth);
						}
					}
					else {
						this.domNodeDivBody.innerHTML='';
						this.htmlItems={};
					}
				},
				buildHtmlItem: function(id) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (objRowSet.isObjectDestroed) return false;
					
					var oldFocusedPath=[];
					for (var i=0; i<objRowSet.focusedPath.length; i++) oldFocusedPath.push(objRowSet.focusedPath[i]);
					var html='';
					try {
						if (objRowSet.focusedPath.length>0) {
							objRowSet.focusedPath[objRowSet.focusedPath.length-1]=id;
						}
						else {
							objRowSet.focusedPath.push(id);
						}
						var template=this.getTemplate();
						var html=this.getTemplateHtml(template);
					}
					finally {
						objRowSet.focusedPath=oldFocusedPath;
					}
					var htmlItem=this.htmlItems[id];
					if (!htmlItem) {
						htmlItem={};
						var domNode=document.createElement('div');
						dojo.addClass(domNode,'item');
						domNode.setAttribute('data-id',id);
						this.domNodeDivBody.appendChild(domNode);
						htmlItem.domNode=domNode;

						dojo.on(domNode, 'click', dojo.hitch(this, function(e){
							var domNode=e.target;
							while(domNode && domNode.parentNode!=this.domNodeDivBody) domNode=domNode.parentNode;
							var id=domNode.getAttribute('data-id');
							if (id) this.onItemClick(id);
						}));
						dojo.on(domNode, 'dblclick', dojo.hitch(this, function(e){
							this.execEventOnAction();
						}));

						
						htmlItem.html='';
						htmlItem.id=id;
						this.htmlItems[id]=htmlItem;
					}
					if (htmlItem.html!=html) {
						htmlItem.html=html;
						htmlItem.domNode.innerHTML=html;
					}
					return true;
				},
				onItemClick: function(id) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (objRowSet.isObjectDestroed) return false;
					objRowSet.setFocusedId(id);
				},
				// Выбирает из списка шаблонов первый подходящий
				getTemplate: function() {
					var obj=this.getRowSet();
					if (!obj) obj=this.objForm;
					if (this.js_tp) {
						var result={};
						result.html=g740.js_eval(obj, this.js_tp, '');
						return result;
					}
					if (this.js_tpname) {
						var name=g740.js_eval(obj, this.js_tpname, '');
						if (name) {
							var result=this.templates[name];
							if (result) return result;
						}
					}
					var result={};
					for(var name in this.templates) {
						var template=this.templates[name];
						if (!template) continue;
						if (template.js_enabled) {
							if (!g740.js_eval(obj, template.js_enabled, true)) continue;
						}
						result=template;
						break;
					}
					return result;
				},
				getTemplateHtml: function(template) {
					var result='';
					if (template) result=template.html;
					var lst=result.split('%%');
					for (var i=1; i<lst.length; i+=2) {
						var name=lst[i];
						var value=this.getTemplateValue(name);
						lst[i]=value;
					}
					result=lst.join('');
					return result;
				},
				getTemplateValue: function(name) {
					var obj=this.getRowSet();
					if (!obj) obj=this.objForm;
					if (this.js_tpvalue) {
						var result=g740.js_eval(obj, this.js_tpvalue, '', name);
						if (result) return result;
					}
					try {
						var oldEvalObj=g740.js_eval_obj;
						try {
							g740.js_eval_obj=obj;
							var result=g740.convertor.js2text(get(name,''));
						}
						finally {
							g740.js_eval_obj=oldEvalObj;
						}
					}
					catch (e) {
						var result='';
					}
					if (!result && result!==0) result='';
					result=result.toString().toHtml();
					return result;
				},
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					return true;
				}
			}
		);
		
		g740.panels._builderPanel=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanel';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			var result=new g740.Panel(para, null);
			return result;
		};
		g740.panels.registrate('panel', g740.panels._builderPanel);

		g740.panels._builderPanelScroll=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelScroll';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			var result=new g740.PanelScroll(para, null);
			return result;
		};
		g740.panels.registrate('scroll', g740.panels._builderPanelScroll);

		g740.panels._builderPanelExpander=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelExpander';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');

			if (g740.xml.getAttrValue(xml, 'maxwidth', '')) para.maxWidth=g740.xml.getAttrValue(xml, 'maxwidth', '');
			if (g740.xml.getAttrValue(xml, 'maxheight', '')) para.maxWidth=g740.xml.getAttrValue(xml, 'maxheight', '');
			if (g740.xml.getAttrValue(xml, 'size', '')) para.g740size=g740.xml.getAttrValue(xml, 'size', '');

			if (!para.region) {
				para.region='left';
				para.style='width:150px';
			}
			
			var result=new g740.PanelExpander(para, null);
			return result;
		};
		g740.panels.registrate('expander', g740.panels._builderPanelExpander);

		g740.panels._builderPanelTab=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelTab';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.getAttrValue(xml, 'bestchild', '0')=='1') {
				var result=new g740.PanelBestChild(para, null);
			}
			else {
				var result=new g740.PanelTab(para, null);
			}
			return result;
		};
		g740.panels.registrate('tab', g740.panels._builderPanelTab);

		g740.panels._builderPanelAccordion=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelAccordion';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.getAttrValue(xml, 'bestchild', '0')=='1') {
				var result=new g740.PanelBestChild(para, null);
			}
			else {
				var result=new g740.PanelAccordion(para, null);
			}
			return result;
		};
		g740.panels.registrate('accordion', g740.panels._builderPanelAccordion);

		g740.panels._builderPanelWebBrowser=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelWebBrowser';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.isAttr(xml,'url')) para.url=g740.xml.getAttrValue(xml,'url','');
			if (g740.xml.isAttr(xml,'js_url')) para.jsUrl=g740.xml.getAttrValue(xml,'js_url','');
			if (g740.xml.isAttr(xml,'field')) para.fieldName=g740.xml.getAttrValue(xml,'field','');
			if (g740.xml.getAttrValue(xml,'noscript','')==1) para.isNoScript=true;
			if (g740.xml.getAttrValue(xml,'repaint','')=='manual') {
				para.repaintMode='manual';
				if (g740.xml.isAttr(xml,'default')) para.urlDefault=g740.xml.getAttrValue(xml,'default','');
			}

			var xmlParams=g740.xml.findFirstOfChild(xml, {nodeName: 'params'});
			if (!xmlParams) xmlParams=xml;
			var lstParam=g740.xml.findArrayOfChild(xmlParams, {nodeName:'param'});
			var panelParams={};
			for (var i=0; i<lstParam.length; i++) {
				var xmlParam=lstParam[i];
				if (!g740.xml.isXmlNode(xmlParam)) continue;
				var paramName=g740.xml.getAttrValue(xmlParam,'name','');
				if (!paramName) paramName=g740.xml.getAttrValue(xmlParam,'param','');
				if (paramName=='') continue;
				var isEnabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'enabled','1'),'check');
				if (!isEnabled) continue;
				var p={name: paramName, type:'string'};
				if (g740.xml.isAttr(xmlParam,'value')) {
					p.value=g740.xml.getAttrValue(xmlParam,'value','');
				}
				if (g740.xml.isAttr(xmlParam,'js_value')) {
					p.js_value=g740.xml.getAttrValue(xmlParam,'js_value','');
				}
				if (g740.xml.isAttr(xmlParam,'js_enabled')) {
					p.js_enabled=g740.xml.getAttrValue(xmlParam,'js_enabled','');
				}
				if (g740.xml.isAttr(xmlParam,'type')) {
					var t=g740.xml.getAttrValue(xmlParam,'type','');
					if (!g740.fieldTypes[t]) t='string';
					p.type=t;
				}
				if (g740.xml.isAttr(xmlParam,'notempty')) {
					p.notempty=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'notempty','0'),'check');
				}
				if (g740.xml.isAttr(xmlParam,'priority')) {
					p.priority=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'priority','0'),'check');
				}
				
				var xmlScripts=g740.xml.findFirstOfChild(xmlParam, {nodeName: 'scripts'});
				if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xmlParam;
				var lstScript=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
				for (var indexScript=0; indexScript<lstScript.length; indexScript++) {
					var xmlScript=lstScript[indexScript];
					var name=g740.xml.getAttrValue(xmlScript, 'name', '');
					if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
					if (name=='enabled') p.js_enabled=g740.panels.buildScript(xmlScript);
					if (name=='value') p.js_value=g740.panels.buildScript(xmlScript);
				}
				
				panelParams[paramName]=p;
			}
			para.g740params=panelParams;
			var result=new g740.PanelWebBrowser(para, null);
			return result;
		};
		g740.panels.registrate('webbrowser', g740.panels._builderPanelWebBrowser);

		g740.panels._builderPanelExtControl=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelExtControl';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.isAttr(xml,'url')) para.url=g740.xml.getAttrValue(xml,'url','');

			var xmlParams=g740.xml.findFirstOfChild(xml, {nodeName: 'params'});
			if (!xmlParams) xmlParams=xml;
			var lstParam=g740.xml.findArrayOfChild(xmlParams, {nodeName:'param'});
			var panelParams={};
			for (var i=0; i<lstParam.length; i++) {
				var xmlParam=lstParam[i];
				if (!g740.xml.isXmlNode(xmlParam)) continue;
				var paramName=g740.xml.getAttrValue(xmlParam,'name','');
				if (!paramName) paramName=g740.xml.getAttrValue(xmlParam,'param','');
				if (paramName=='') continue;
				var isEnabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'enabled','1'),'check');
				if (!isEnabled) continue;
				var p={name: paramName, type:'string'};
				if (g740.xml.isAttr(xmlParam,'value')) {
					p.value=g740.xml.getAttrValue(xmlParam,'value','');
				}
				if (g740.xml.isAttr(xmlParam,'js_value')) {
					p.js_value=g740.xml.getAttrValue(xmlParam,'js_value','');
				}
				if (g740.xml.isAttr(xmlParam,'type')) {
					var t=g740.xml.getAttrValue(xmlParam,'type','');
					if (!g740.fieldTypes[t]) t='string';
					p.type=t;
				}
				panelParams[paramName]=p;
			}
			var result=new g740.PanelExtControl(para, null);
			result.params=panelParams;
			return result;
		};
		g740.panels.registrate('extcontrol', g740.panels._builderPanelExtControl);
		
		g740.panels._builderPanelImg=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelImg';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.isAttr(xml,'url')) para.url=g740.xml.getAttrValue(xml,'url','');
			if (g740.xml.isAttr(xml,'size')) para.size=g740.xml.getAttrValue(xml,'size','');
			if (g740.xml.isAttr(xml,'field')) para.fieldName=g740.xml.getAttrValue(xml,'field','');
			var result=new g740.PanelImg(para, null);
			return result;
		};
		g740.panels.registrate('img', g740.panels._builderPanelImg);

		g740.panels._builderPanelHTML=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelHTML';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');

			if (g740.xml.isAttr(xml,'field')) para.fieldName=g740.xml.getAttrValue(xml,'field','');
			if (g740.xml.isAttr(xml,'bgcolor')) para.backgroundColor=g740.xml.getAttrValue(xml,'bgcolor','');
			if (g740.xml.isAttr(xml,'color')) para.fontColor=g740.xml.getAttrValue(xml,'color','');
			if (g740.xml.isAttr(xml,'bgimage')) para.backgroundImage=g740.xml.getAttrValue(xml,'bgimage','');
			if (g740.xml.isAttr(xml,'caption')) para.g740caption=g740.xml.getAttrValue(xml,'caption','');
			if (g740.xml.isAttr(xml,'size')) para.g740size=g740.xml.getAttrValue(xml,'size','');
			if (g740.xml.isAttr(xml,'style')) para.g740style=g740.xml.getAttrValue(xml,'style','');
			if (g740.xml.isAttr(xml,'login')) para.g740login=g740.xml.getAttrValue(xml,'login','');

			if (g740.xml.isAttr(xml,'js_tp')) para.js_tp=g740.xml.getAttrValue(xml,'js_tp','');
			if (g740.xml.isAttr(xml,'js_tpname')) para.js_tpname=g740.xml.getAttrValue(xml,'js_tpname','');
			if (g740.xml.isAttr(xml,'js_tpvalue')) para.js_tpvalue=g740.xml.getAttrValue(xml,'js_tpvalue','');

			var xmlScripts=g740.xml.findFirstOfChild(xml, {nodeName: 'scripts'});
			if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xml;
			var lstScript=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
			for (var indexScript=0; indexScript<lstScript.length; indexScript++) {
				var xmlScript=lstScript[indexScript];
				var name=g740.xml.getAttrValue(xmlScript, 'name', '');
				if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
				if (name=='tpname') para.js_tpname=g740.panels.buildScript(xmlScript);
				if (name=='tpvalue') para.js_tpvalue=g740.panels.buildScript(xmlScript);
				if (name=='tp') para.js_tp=g740.panels.buildScript(xmlScript);
			}

			
			var templates={};
			var isTemplates=false;
			var xmlTemplates=g740.xml.findFirstOfChild(xml, {nodeName: 'templates'});
			if (!g740.xml.isXmlNode(xmlTemplates)) xmlTemplates=xml;
			var lstTemplates=g740.xml.findArrayOfChild(xmlTemplates, {nodeName: 'template'});
			for (var indexTemplate=0; indexTemplate<lstTemplates.length; indexTemplate++) {
				var xmlTemplate=lstTemplates[indexTemplate];
				if (!g740.xml.isXmlNode(xmlTemplate)) continue;
				if (g740.xml.isAttr(xmlTemplate,'enabled')) {
					if (!g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlTemplate,'enabled',''),'check')) continue;
				}
				var template={};
				var name=g740.xml.getAttrValue(xmlTemplate,'name','');
				if (!name) name=g740.xml.getAttrValue(xmlTemplate,'template',(indexTemplate+1).toString());
				template.html=dojo.trim(xmlTemplate.textContent);
				if (!template.html) continue;
				if (g740.xml.isAttr(xmlTemplate,'js_enabled')) template.js_enabled=g740.xml.getAttrValue(xmlTemplate,'js_enabled','');
				templates[name]=template;
				isTemplates=true;
			}
			if (isTemplates) para.templates=templates;

			var result=new g740.PanelHTML(para, null);
			return result;
		};
		g740.panels.registrate('html', g740.panels._builderPanelHTML);

		g740.panels._builderPanelListHTML=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelListHTML';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');

			if (g740.xml.isAttr(xml,'style')) para.g740style=g740.xml.getAttrValue(xml,'style','');
			
			var orientation=g740.xml.getAttrValue(xml,'orientation','vertical');
			if (orientation=='vertical' || orientation=='horizontal') para.orientation=orientation;
			
			if (g740.xml.isAttr(xml,'js_tp')) para.js_tp=g740.xml.getAttrValue(xml,'js_tp','');
			if (g740.xml.isAttr(xml,'js_tpname')) para.js_tpname=g740.xml.getAttrValue(xml,'js_tpname','');
			if (g740.xml.isAttr(xml,'js_tpvalue')) para.js_tpvalue=g740.xml.getAttrValue(xml,'js_tpvalue','');

			var xmlScripts=g740.xml.findFirstOfChild(xml, {nodeName: 'scripts'});
			if (!g740.xml.isXmlNode(xmlScripts)) xmlScripts=xml;
			var lstScript=g740.xml.findArrayOfChild(xmlScripts, {nodeName: 'script'});
			for (var indexScript=0; indexScript<lstScript.length; indexScript++) {
				var xmlScript=lstScript[indexScript];
				var name=g740.xml.getAttrValue(xmlScript, 'name', '');
				if (!name) name=g740.xml.getAttrValue(xmlScript, 'script', '');
				if (name=='tpname') para.js_tpname=g740.panels.buildScript(xmlScript);
				if (name=='tpvalue') para.js_tpvalue=g740.panels.buildScript(xmlScript);
				if (name=='tp') para.js_tp=g740.panels.buildScript(xmlScript);
			}

			var templates={};
			var isTemplates=false;
			var xmlTemplates=g740.xml.findFirstOfChild(xml, {nodeName: 'templates'});
			if (!g740.xml.isXmlNode(xmlTemplates)) xmlTemplates=xml;
			var lstTemplates=g740.xml.findArrayOfChild(xmlTemplates, {nodeName: 'template'});
			for (var indexTemplate=0; indexTemplate<lstTemplates.length; indexTemplate++) {
				var xmlTemplate=lstTemplates[indexTemplate];
				if (!g740.xml.isXmlNode(xmlTemplate)) continue;
				if (g740.xml.isAttr(xmlTemplate,'enabled')) {
					if (!g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlTemplate,'enabled',''),'check')) continue;
				}
				var template={};
				var name=g740.xml.getAttrValue(xmlTemplate,'name','');
				if (!name) name=g740.xml.getAttrValue(xmlTemplate,'template',(indexTemplate+1).toString());
				template.html=dojo.trim(xmlTemplate.textContent);
				if (!template.html) continue;
				if (g740.xml.isAttr(xmlTemplate,'js_enabled')) template.js_enabled=g740.xml.getAttrValue(xmlTemplate,'js_enabled','');
				templates[name]=template;
				isTemplates=true;
			}
			if (isTemplates) para.templates=templates;

			var result=new g740.PanelListHTML(para, null);
			return result;
		};
		g740.panels.registrate('htmllist', g740.panels._builderPanelListHTML);

		g740.panels._builderPanelMemo=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelMemo';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.isAttr(xml,'field')) para.fieldName=g740.xml.getAttrValue(xml,'field','');
			if (g740.xml.isAttr(xml,'enter')) para.enter=g740.xml.getAttrValue(xml,'enter','0');
			var result=new g740.PanelMemo(para, null);
			return result;
		};
		g740.panels.registrate('memo', g740.panels._builderPanelMemo);

		g740.panels._builderPanelForm=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelForm';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (g740.xml.isAttr(xml, 'form')) para.defaultChildName=g740.xml.getAttrValue(xml, 'form', '');
			if (g740.xml.isAttr(xml, 'background')) para.background=g740.xml.getAttrValue(xml, 'background', '');
			if (g740.xml.isAttr(xml, 'bgopacity')) para.bgopacity=g740.xml.getAttrValue(xml, 'bgopacity', '0.3');
			if (g740.xml.isAttr(xml, 'bgsize')) para.bgsize=g740.xml.getAttrValue(xml, 'bgsize', 'cover');
			
			if (g740.xml.getAttrValue(xml, 'menu', '')==1) para.isTreeMenu=true;
			if (g740.xml.getAttrValue(xml, 'tab', '')==1) para.isMultiTab=true;
			//if (g740.xml.isAttr(xml, 'menu.size')) para.g740size=g740.xml.getAttrValue(xml, 'menu.size', '');
			if (g740.xml.isAttr(xml, 'menu.align')) para.treeMenuAlign=g740.xml.getAttrValue(xml, 'menu.align', 'left');
			if (g740.xml.isAttr(xml, 'menu.maxwidth')) para.treeMenuMaxWidth=g740.xml.getAttrValue(xml, 'menu.maxwidth', '');
			if (g740.xml.isAttr(xml, 'menu.maxheight')) para.treeMenuMaxHeight=g740.xml.getAttrValue(xml, 'menu.maxheight', '');
			if (g740.xml.isAttr(xml, 'menu.caption')) para.treeMenuCaption=g740.xml.getAttrValue(xml, 'menu.caption', '');
			if (g740.xml.getAttrValue(xml, 'menu.showonempty', '')==1) para.treeMenuShowOnEmpty=true;
			
			var result=new g740.PanelFormContainer(para, null);

			return result;
		};
		g740.panels.registrate('form', g740.panels._builderPanelForm);

		return g740;
	}
);