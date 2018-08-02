//-----------------------------------------------------------------------------
// Панели
//-----------------------------------------------------------------------------
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
				p.js_visible=g740.xml.getAttrValue(xml, 'js_visible', '');
				p.g740id=g740.xml.getAttrValue(xml, 'g740id', '');
				p.styleBorder='border: none; border-width: 0px;';
				if (g740.xml.getAttrValue(xml, 'border', '0')=='1') {
					p.styleBorder='border: solid 1px;border-color: lightgray;';
				}
				
				p.isFocusOnShow=(g740.xml.getAttrValue(xml, 'focus', '')=='1');
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

				var p={};
				p.region='bottom';
				p.style='height:30px;border-width:0px;';
				p.height='30px';
				p.color=g740.xml.getAttrValue(xml, 'color', '');
				
				var objPanelButtons=new g740.Panel(p,null);
				objPanelButtons.isG740PanelButtons=true;
				objPanel.addChild(objPanelButtons);
				for(var i=0; i<requests.length; i++) {
					var xmlRequest=requests[i];
					var align=g740.xml.getAttrValue(xmlRequest,'align','');
					if (align=='right') continue;
					this.buildToolbarMenuItem(requests[i], objPanel, objPanelButtons);
				}
				for(var i=requests.length-1; i>=0; i--) {
					var xmlRequest=requests[i];
					var align=g740.xml.getAttrValue(xmlRequest,'align','');
					if (align!='right') continue;
					this.buildToolbarMenuItem(requests[i], objPanel, objPanelButtons);
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
						p.region='left';
						if (g740.xml.getAttrValue(xml,'align','')=='right') p.region='right';
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
				var fldDefType=fldDef.type;
				var result = g740.xml.objFromXml(
					xmlField, 
					{
						name: { name: '', field: '' },
						type: '',
						basetype: '',
						refid: '',
						visible: true,
						save: false,
						caption: '',
						readonly: false,
						width: '',
						dlgwidth: '',
						len: 0,
						dec: 0,
						stretch: false,
						captionup: false,
						rows: 0,
						list: '',
						js_visible: '',
						js_readonly: ''
					},
					fldDef
				);
				
				var t=result.type;
				if (t && !g740.fieldTypes[t]) {
					result.type='string';
					t='string';
				}
				if (fldDefType && !result.basetype) {
					if (fldDefType=='num' && (t=='list' || t=='icons' || t=='radio')) result.basetype='num';
				}
				if (t=='memo') {
					if (g740.xml.isAttr(xmlField,'enter')) result.enter=g740.xml.getAttrValue(xmlField,'enter','0');
				}

				var lstRequests=g740.xml.findArrayOfChild(xmlField, {nodeName: 'request'});
				for (var i=0; i<lstRequests.length; i++) {
					var xmlRequest=lstRequests[i];
					if (!g740.xml.isXmlNode(xmlRequest)) continue;
					var isEnabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'enabled','1'),'check');
					if (!isEnabled) continue;
					var t='dblclick';
					if (!result.on) result.on={};
					if (!result.on[t]) result.on[t]={
						sync: true,
						params: {}
					};
					this.buildRequestParams(xmlRequest, result.on[t]);
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
				}
				
				if (g740.xml.isAttr(xmlRequest,'save')) request.save=(g740.xml.getAttrValue(xmlRequest,'save','')==1);
				if (g740.xml.isAttr(xmlRequest,'close')) request.close=(g740.xml.getAttrValue(xmlRequest,'close','')==1);
				
				if (g740.xml.isAttr(xmlRequest,'confirm')) {
					request.confirm=g740.xml.getAttrValue(xmlRequest,'confirm','');
				}

				if (g740.xml.isAttr(xmlRequest,'enabled')) {
					request.enabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRequest,'enabled','1'),'check');
				}
				if (g740.xml.isAttr(xmlRequest,'js_enabled')) {
					request.js_enabled=g740.xml.getAttrValue(xmlRequest,'js_enabled','');
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
				if (g740.xml.isAttr(xmlRequest,'params')) {
					var exec=request.name;
					if (request.mode) exec+='.'+request.mode;
					exec+='('+g740.xml.getAttrValue(xmlRequest,'params','')+')';
					request.exec=exec;
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
					var isEnabled=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'enabled','1'),'check');
					if (isEnabled) {
						var p=request.params[paramName];
						if (!p) p={name: paramName, type:'string'};
						if (g740.xml.isAttr(xmlParam,'value')) {
							p.value=g740.xml.getAttrValue(xmlParam,'value','');
						}
						if (g740.xml.isAttr(xmlParam,'js_value')) {
							p.js_value=g740.xml.getAttrValue(xmlParam,'js_value','');
						}
						if (g740.xml.isAttr(xmlParam,'default')) {
							p.def=g740.xml.getAttrValue(xmlParam,'default','');
						}
						if (g740.xml.isAttr(xmlParam,'result')) {
							p.result=g740.xml.getAttrValue(xmlParam,'result','');
						}
						if (g740.xml.isAttr(xmlParam,'type')) {
							var t=g740.xml.getAttrValue(xmlParam,'type','');
							if (!g740.fieldTypes[t]) t='string';
							p.type=t;
						}
						if (g740.xml.isAttr(xmlParam,'js_enabled')) {
							p.js_enabled=g740.xml.getAttrValue(xmlParam,'js_enabled','');
						}
						if (g740.xml.isAttr(xmlParam,'notempty')) {
							p.notempty=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlParam,'notempty','0'),'check');
						}
						request.params[paramName]=p;
					}
					else {
						delete request.params[paramName];
					}
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
				g740childs: [],
				
				objMenu: null,
				
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
					this.g740childs=[];
					this.on('Focus', this.onG740Focus);
					this.on('Blur', this.onG740Blur);
				},
				destroy: function() {
					var procedureName='g740._PanelAbstract.destroy';
					this.objForm=null;
					this.rowsetName=null;
					this.isObjectDestroed=true;

					if (this.g740childs) {
						for (var i=0; i<this.g740childs.length; i++) {
							var obj=this.g740childs[i];
							if (obj && obj.g740className=='g740.Panel' && !obj.visible) {
								obj.destroyRecursive();
							}
							this.g740childs[i]=null;
						}
						this.g740childs=[];
					}
					
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
					this.g740childs=[];
					var lst=[];
					if (this.getChildren) lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var objChildPanel=lst[i];
						if (!objChildPanel) continue;
						this.g740childs.push(lst[i]);
						if (objChildPanel.onG740AfterBuild) objChildPanel.onG740AfterBuild();
					}
				},
				doG740RepaintChildsVisible: function() {
					var index=0;
					for (var i=0; i<this.g740childs.length; i++) {
						var objPanel=this.g740childs[i];
						if (!objPanel) continue;
						if (objPanel.g740className=='g740.Panel' && objPanel.objForm && objPanel.js_visible) {
							var visible=g740.convertor.toJavaScript(g740.js_eval(objPanel.objForm, objPanel.js_visible, true),'check');
							if (visible!=objPanel.visible) {
								if (visible) {
									try {
										if (objPanel.getParent()!=this) this.addChild(objPanel, index);
									}
									catch (e) {
									}
									if (objPanel.doG740Repaint) {
										objPanel.visible=visible;
										objPanel.doG740Repaint({isFull: true});
									}
								}
								else {
									try {
										if (objPanel.getParent()==this) this.removeChild(objPanel);
									}
									catch (e) {
									}
								}
								objPanel.visible=visible;
							}
							if (!objPanel.visible) continue;
						}
						index++;
					}
				},
				// Находим наиболее подходящую дочернюю панель
				getBestChild: function() {
					return null;
				},
				// Вариант поиска наиболее подходящей дочерней панали для наследников StackPanel
				_getBestChildStack: function() {
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
						if (nodeType) {
							if (
								(objChild.rowsetName==this.rowsetName && objChild.nodeType==nodeType) || 
								(objChild.rowsetName!=this.rowsetName)
							) {
								lstChildsNodeType.push(objChild);
							}
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
						for (var i=0; i<lstChildsVisible.length; i++) {
							var objChild=lstChildsVisible[i];
							if (objChild==this.selectedChildWidget) return objChild;
						}
						return lstChildsVisible[0];
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
						var objSeparatorTop=new g740.PanelSeparator({
							height: this.padding,
							region: 'top'
						},null);
						this.addChild(objSeparatorTop);

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
				startup: function() {
					this.inherited(arguments);
					this._g740AutoHeight();
					g740.execDelay.go({
						delay: 50,
						obj: this,
						func: this.layout
					});
				},
				_g740AutoHeight: function() {
					if (this.region!='top' && this.region!='bottom') return true;
					if (this.height) return true;
					if (!this.domNode) return true;
					
					var childs=this.getChildren();
					var h=1;
					var isTopBottomOnly=true;
					for (var i=0; i<childs.length; i++) {
						var objChild=childs[i];
						if (!objChild) continue;
						if (objChild.region!='top' && objChild.region!='bottom') {
							isTopBottomOnly=false;
							break;
						}
						if (!objChild.domNode) continue;
						h+=objChild.domNode.offsetHeight;
					}
					if (!isTopBottomOnly) return true;
					var p={
						l: this.domNode.offsetLeft, t: this.domNode.offsetTop, h: h, w: this.domNode.offsetWidth
					};
					this.resize(p);
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
					var top=0;
					for(var i=0; i<this.g740childs.length; i++) {
						var objChild=this.g740childs[i];
						if (!objChild.domNode) continue;

						if (objChild.g740className=='g740.Panel' && objChild.objForm && objChild.js_visible) {
							if (!objChild.visible) continue;
						}

						var posChild=dojo.geom.position(objChild.domNode, false);
						h=posChild.h;

						var p={
							x: 0,
							y: top,
							w: w,
							h: h
						};
						if (objChild.resize) objChild.resize(p);
						top+=h;
					}
					this._isFirstLayoutExecuted=true;
				},
				doG740RepaintChildsVisible: function() {
					if (!this._isFirstLayoutExecuted) return;
					var w=this.domNode.clientWidth;
					for(var i=0; i<this.g740childs.length; i++) {
						var objChild=this.g740childs[i];
						if (!objChild.domNode) continue;
						if (objChild.g740className=='g740.Panel' && objChild.objForm && objChild.js_visible) {
							var visible=g740.convertor.toJavaScript(g740.js_eval(objChild.objForm, objChild.js_visible, true),'check');
							if (visible!=objChild.visible) {
								objChild.visible=visible;
								if (!visible) {
									var posChild=dojo.geom.position(objChild.domNode, false);
									objChild.G740HiddenDisplay=dojo.style(objChild.domNode,'display');
									objChild.G740HiddenHeight=posChild.h;
									dojo.style(objChild.domNode,'display','none');
									continue;
								}
								else {
									dojo.style(objChild.domNode,'display',objChild.G740HiddenDisplay);
									var p={
										x: 0,
										y: 0,
										w: w,
										h: objChild.G740HiddenHeight
									};
									if (objChild.resize) objChild.resize(p);
									if (objChild.doG740Repaint) objChild.doG740Repaint({isFull: true});
								}
							}
						}
					}
				},
				getChildren: function() {
					var lst=this.inherited(arguments);
					var result=[];
					for (var i=0; i<lst.length; i++) {
						var objChild=lst[i];
						if (objChild.visible===false) {
							continue;
						}
						result.push(objChild);
					}
					return result;
				}
			}
		);
		
		dojo.declare(
			'g740.PanelHTML',
			[g740._PanelAbstract, dijit._TemplatedMixin],
			{
				templateString: '<div class="g740html-panel">'+
				'</div>',
				
				fieldName: '',
				backgroundColor: '',
				fontColor: '',
				backgroundImage: '',
				innerHTML: '',
				label: '',
				g740size: '',
				g740style: '',
				g740login: '',
				set: function(name, value) {
					if (name=='fieldName') {
						this.fieldName=value;
						return true;
					}
					else if (name=='backgroundColor') {
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
					else if (name=='innerHTML') {
						this.innerHTML=value;
						if (this.domNode) this.domNode.innerHTML=this.innerHTML;
						return true;
					}
					else if (name=='g740login') {
						this.g740login=value;
						return true;
					}
					else if (name=='g740style' || name=='g740size') {
						if (name=='g740style') {
							if (value=='title') this.g740style=value;
							else if (value=='mainmenu') this.g740style=value;
							else this.g740style='';
						}
						if (name=='g740size') {
							if (value=='large') this.g740size=value;
							else if (value=='medium') this.g740size=value;
							else if (value=='small') this.g740size=value;
							else this.g740size=g740.config.iconSizeDefault;
						}
						if (this.domNode) {
							var className='g740html-panel';
							if (this.g740size) className+=' g740'+this.g740size;
							if (this.g740style) className+=' '+this.g740style;
							this.domNode.className=className;
						}
						return true;
					}
					else if (name=='label') {
						this._setLabel(value);
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
					if (this.g740style) this.set('g740style', this.g740style);

					if (this.label) {
						this.set('label', this.label);
						this.innerHTML='';
					}
					else if (this.innerHTML) {
						this.set('innerHTML', this.innerHTML);
					}

					if (this.g740style=='mainmenu') {
/*
						var domNodeIcon=document.createElement('div');
						domNodeIcon.className='mainmenu-icon';
						this.domNode.appendChild(domNodeIcon);
*/
					}
					else if (this.g740style=='title') {
						if (this.g740login) {
							var domNodeConnectedUser=document.createElement('div');
							domNodeConnectedUser.className='connecteduser';
							domNodeConnectedUser.setAttribute('title',g740.getMessage('disconnect'));
							this.domNode.appendChild(domNodeConnectedUser);
							
							var domNodeLabel=document.createElement('div');
							domNodeLabel.className='label';
							var txt=document.createTextNode(this.g740login);
							domNodeLabel.appendChild(txt);
							domNodeConnectedUser.appendChild(domNodeLabel);

							var domNodeExit=document.createElement('div');
							domNodeExit.className='icon';
							domNodeConnectedUser.appendChild(domNodeExit);
							dojo.on(domNodeConnectedUser, 'click', function() {
								var objOwner=this;
								while(true) {
									if (objOwner.className=='g740.Form') break;
									if (!objOwner.getParent) break;
									objOwner=objOwner.getParent();
								}
								g740.request.send({
									objOwner: objOwner,
									arrayOfRequest:['<request name="disconnect"/>'],
									requestName: 'disconnect'
								});
							});
						}
					}
					dojo.attr(this.domNode,'title','');
				},
				_setLabel: function(label) {
					if (!label) label='';
					this.label=label;
					if (this.domNode) {
						this.domNode.innerHTML='';
					}
					if (!label) return;

					var domLabel=document.createElement('div');
					var className='g740html-label';
					if (this.region=='left' || this.region=='right') {
						className+=' vertical';
						for(var i=0; i<label.length; i++) {
							if (i!=0) {
								var domLabelBr=document.createElement('br');
								domLabel.appendChild(domLabelBr);
							}
							var domText=document.createTextNode(this.label.substr(i,1));
							domLabel.appendChild(domText);
						}
					}
					else {
						var domText=document.createTextNode(this.label);
						domLabel.appendChild(domText);
					}
					domLabel.className=className;
					this.domNode.appendChild(domLabel);
				},
				doG740Repaint: function(para) {
					if (!para) para={};
					if (!this.fieldName) return true;
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					var objRowSet=this.getRowSet();
					var value=objRowSet.getFieldProperty({fieldName: this.fieldName});
					this.set('label',value);
				},
				canFocused: function() {
					return false;
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
				g740style: '',
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
					else if (name=='g740style') {
						if (value=='mainmenu') this.g740style=value;
						else this.g740style='';
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
					//dojo.attr(this.domNode,'title',g740.getMessage('mainMenuCaption'));

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
					if (this.g740style=='mainmenu') {
						dojo.addClass(this.domNode,'mainmenu');
						dojo.attr(this.domNodeIcon,'title',g740.getMessage('mainMenuCaption'));
						dojo.on(this.domNodeIcon,'click',dojo.hitch(this,this.onLockScreenClick));
						
						var p={
							region: this.region,
							g740style: this.g740style,
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
					}

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

				destroy: function() {
					this.tablist.TabContainer=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.tablist.TabContainer=this;
					this.tablist.on('KeyDown',function(e){
						if (this.TabContainer && this.TabContainer.onG740KeyDown) this.TabContainer.onG740KeyDown(e);
					});
					this.inherited(arguments);
				},
				getBestChild: function() {
					return this._getBestChildStack();
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
					var bestChild=this.getBestChild();
					if (bestChild && bestChild!=this.selectedChildWidget) {
						this.selectChild(bestChild);
					}
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
					if (objPage && objPage.doG740Repaint) objPage.doG740Repaint({
						isFull: true
					});
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
		
// Класс PanelForm
		dojo.declare(
			'g740.PanelTabForm',
			[dijit.layout.TabContainer, g740._PanelAbstract],
			{
				defaultChildName: null,
				background: '',
				set: function(name, value) {
					if (name=='defaultChildName') {
						this.defaultChildName=value;
						return true;
					}
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.PanelTabForm.constructor';
					this.set('objForm', para.objForm);
					if (this.objForm) {
						this.objForm.objPanelForm=this;
					}
					this.on('Focus', this.onG740Focus);
				},
				destroy: function() {
					var procedureName='g740.PanelTabForm.destroy';
					this.tablist.TabContainer=null;
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						this.removeChild(obj);
						obj.destroyRecursive();
					}
					if (this.objForm) this.objForm.objPanelForm=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					dojo.style(this.containerNode, 'background-size', 'cover');
					dojo.style(this.containerNode, 'background-repeat', 'no-repeat');
					dojo.style(this.containerNode, 'background-position', 'center');
					this.tablist.TabContainer=this;
					this.tablist.on('KeyDown',function(e){
						if (this.TabContainer && this.TabContainer.onG740KeyDown) this.TabContainer.onG740KeyDown(e);
					});

					if (this.isShowTitle && this.title) {
						var objTitle=new g740.PanelTitle({
							title: this.title,
							region: 'top'
						},null);
						this.addChild(objTitle);
					}
					this.inherited(arguments);
					this.doG740RepaintBackground();
					if (this.defaultChildName) {
						g740.execDelay.go({
							delay: 50,
							obj: g740.application,
							func: g740.application.doG740ShowForm,
							para: {formName: this.defaultChildName}
						});
					}
				},
				// Блокируем кэширование дочерних элементов
				onG740AfterBuild: function() {
					this.g740childs=[];
				},
				onG740KeyDown: function(e) {
					if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.selectedChildWidget && this.selectedChildWidget.doG740FocusChildFirst) {
							this.selectedChildWidget.doG740FocusChildFirst();
						}
					}
					else {
						dojo.stopEvent(e);
					}
				},
				// Отобразить экранную форму
				doG740ShowForm: function(objForm) {
					var oldOnSelect=null;
					if (objForm && objForm.requests && objForm.requests['onselect']) {
						oldOnSelect=objForm.requests['onselect'];
						delete objForm.requests['onselect'];
					}
					try {
						var childs=this.getChildren();
						var addIndex=-1;
						for (var i=0; i<childs.length; i++) {
							var objChild=childs[i];
							if (!objChild) continue;
							if (objChild.g740className!='g740.Form') continue;
							if (objChild.name==objForm.name) {
								addIndex=this.getIndexOfChild(objChild);
								this.removeChild(objChild);
								objChild.destroyRecursive();
							}
						}
						objForm.closable=true;
						objForm.onClose=this.onFormClose;
						objForm.onHide=this.onFormHide;
						if (addIndex>=0) {
							this.addChild(objForm, addIndex);
						}
						else {
							this.addChild(objForm);
						}
						
						this.selectChild(objForm);
					}
					finally {
						if (oldOnSelect && objForm && objForm.requests) objForm.requests['onselect']=oldOnSelect;
					}
				},
				onFormClose: function() {
					var objForm=this;
					if (objForm.g740className=='g740.Form' && !objForm.isObjectDestroed) {
						var objRowSet=objForm.getFocusedRowSet();
						if (objRowSet && objRowSet.getRequestEnabled('save','') && objRowSet.getExistUnsavedChanges()) {
							objRowSet.exec({requestName:'save'});
							return false;
						}
					}
					return true;
				},
				onFormHide: function() {
					var objForm=this;
					if (objForm.g740className=='g740.Form' && !objForm.isObjectDestroed) {
						var objRowSet=objForm.getFocusedRowSet();
						if (objRowSet && objRowSet.getRequestEnabled('save','') && objRowSet.getExistUnsavedChanges()) {
							var objParent=objForm.getParent();
							if (objParent) {
								if (!objRowSet.exec({requestName:'save'})) {
									g740.execDelay.go({
										obj: objParent,
										func: objParent.selectChild,
										para: objForm
									});
								}
							}
						}
					}
					return false;
				},
				onG740FormSelect: function(objForm) {
					if (objForm && objForm.g740className=='g740.Form' && !objForm.isObjectDestroed && objForm.requests) {
						if (objForm.requests['onselect']) {
							objForm.exec({
								requestName: 'onselect'
							});
						}
					}
				},
				selectChild: function(objForm) {
					var old=this.selectedChildWidget;
					this.inherited(arguments);
					if (objForm && this.selectedChildWidget==objForm && old!=objForm && objForm.g740className=='g740.Form' && !objForm.isObjectDestroed) {
						this.onG740FormSelect(objForm);
					}
				},
				doG740Repaint: function(para) {
				},
				// Отображение форового рисунка при отсутствии панелей
				addChild: function (child,insertIndex) {
					this.inherited(arguments);
					this.doG740RepaintBackground();
				},
				removeChild: function(page) {
					this.inherited(arguments);
					this.doG740RepaintBackground();
				},
				_isBackground: false,
				doG740RepaintBackground: function() {
					var lst=this.getChildren();
					if (this.background) {
						if (lst.length==0 && !this._isBackground) {
							dojo.style(this.containerNode, 'background-image', "url('"+this.background+"')");
							dojo.style(this.containerNode, 'opacity', '0.3');
							this._isBackground=true;
						}
						else if (this._isBackground) {
							dojo.style(this.containerNode, 'background-image', 'inherit');
							dojo.style(this.containerNode, 'opacity', 'inherit');
							this._isBackground=false;
						}
					}
				}
			}
		);
		
		dojo.declare(
			'g740.PanelSingleForm',
			[dijit.layout.BorderContainer, g740._PanelAbstract],
			{
				defaultChildName: null,
				objTitle: null,
				set: function(name, value) {
					if (name=='defaultChildName') {
						this.defaultChildName=value;
						return true;
					}
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.PanelSingleForm.constructor';
					this.set('objForm', para.objForm);
					if (this.objForm) {
						this.objForm.objPanelForm=this;
					}
					this.on('Focus', this.onG740Focus);
				},
				destroy: function() {
					var procedureName='g740.PanelSingleForm.destroy';
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						this.removeChild(obj);
						obj.destroyRecursive();
					}
					if (this.objForm) this.objForm.objPanelForm=null;
					this.objTitle=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.objTitle=new g740.PanelTitle({
						title: '',
						region: 'top'
					},null);
					this.addChild(this.objTitle);
					if (this.defaultChildName) {
						g740.application.doG740ShowForm({formName: this.defaultChildName});
					}
				},
				// Блокируем кэширование дочерних элементов
				onG740AfterBuild: function() {
					this.g740childs=[];
				},
// Отобразить экранную форму
				doG740ShowForm: function(objForm) {
					var isCanShowForm=true;
					var childs=this.getChildren();
					for (var i=0; i<childs.length; i++) {
						var objChild=childs[i];
						if (!objChild) continue;
						if (objChild.g740className!='g740.Form') continue;
						if (objChild.g740className=='g740.Form' && !objChild.isObjectDestroed) {
							var objRowSet=objChild.getFocusedRowSet();
							if (objRowSet && objRowSet.getRequestEnabled('save','') && objRowSet.getExistUnsavedChanges()) {
								if (!objRowSet.exec({requestName:'save'})) isCanShowForm=false;
							}
						}
						if (isCanShowForm) {
							this.removeChild(objChild);
							objChild.destroyRecursive();
						}
					}
					if (isCanShowForm) {
						this.addChild(objForm);
						if (this.objTitle) this.objTitle.set('title', objForm.title);
					}
				},
				doG740Repaint: function(para) {
				}
			}
		);

		dojo.declare(
			'g740.PanelMultiTabsForm',
			[dijit.layout.BorderContainer, g740._PanelAbstract],
			{
				defaultChildName: null,
				objMultiTab: null,
				objStackContainer: null,
				background: '',
				bgopacity: 0.3,
				_isBackground: false,
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
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.PanelMultiTabsForm.constructor';
					this.set('objForm', para.objForm);
					if (this.objForm) {
						this.objForm.objPanelForm=this;
					}
					this.on('Focus', this.onG740Focus);
				},
				destroy: function() {
					var procedureName='g740.PanelMultiTabsForm.destroy';
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						this.removeChild(obj);
						obj.destroyRecursive();
					}
					if (this.objForm) this.objForm.objPanelForm=null;
					this.objMultiTab=null;
					this.objStackContainer=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					dojo.addClass(this.domNode,'g740-panelmultitabsform');
					this.objMultiTab=new g740.WidgetPanelFormMultiTabs({
						region: 'top'
					},null);
					this.addChild(this.objMultiTab);
					this.objStackContainer=new dijit.layout.StackContainer({
						region: 'center'
					}, null);
					this.addChild(this.objStackContainer);

					dojo.style(this.objStackContainer.domNode, 'background-size', 'cover');
					dojo.style(this.objStackContainer.domNode, 'background-repeat', 'no-repeat');
					dojo.style(this.objStackContainer.domNode, 'background-position', 'center');
					
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
						if (objChild && objChild.g740className=='g740.Form' && !objChild.isObjectDestroed && objChild.requests['onselect']) {
							objChild.exec({
								requestName: 'onselect'
							});
						}
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
						return true;
					};

					this.objStackContainer.doG740RepaintBackground=dojo.hitch(this, this.doG740RepaintBackground);
					
					this.objMultiTab.set('objStackContainer', this.objStackContainer);
					this.doG740RepaintBackground();
					
					if (this.defaultChildName) {
						g740.application.doG740ShowForm({formName: this.defaultChildName});
					}
				},
				// Блокируем кэширование дочерних элементов
				onG740AfterBuild: function() {
					this.g740childs=[];
				},
// Отобразить экранную форму
				doG740ShowForm: function(objForm) {
					var procedureName='g740.PanelMultiTabsForm.doG740ShowForm';
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
						this.objMultiTab.doG740Repaint();
					}
					finally {
						if (oldOnSelect && objForm && objForm.requests) objForm.requests['onselect']=oldOnSelect;
					}
				},
				doG740Repaint: function(para) {
				},
				doG740RepaintBackground: function() {
					if (!this.objStackContainer) return;
					var lst=this.objStackContainer.getChildren();
					if (this.background) {
						if (lst.length==0) {
							if (!this._isBackground) {
								dojo.style(this.objStackContainer.domNode, 'background-image', "url('"+this.background+"')");
								dojo.style(this.objStackContainer.domNode, 'opacity', this.bgopacity);
								this._isBackground=true;
							}
						}
						else if (this._isBackground) {
							dojo.style(this.objStackContainer.domNode, 'background-image', 'inherit');
							dojo.style(this.objStackContainer.domNode, 'opacity', '1');
							this._isBackground=false;
						}
					}
					this.layout();
				}
			}
		);
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
					
					if (this.objStackContainer && this.objStackContainer.doG740RepaintBackground) {
						this.objStackContainer.doG740RepaintBackground();
					}
				},
				doCreateTabDiv: function(objChildForm) {
					var procedureName='g740.WidgetPanelMultiFormTabs.doCreateTabDiv';
					if (!objChildForm) g740.systemError(procedureName, 'errorValueUndefined', 'objChildForm');
					if (objChildForm.g740className!='g740.Form') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objChildForm');
					if (objChildForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objChildForm');

					var result=document.createElement('div');
					dojo.attr(result,'data-name',objChildForm.name);
					
					var domSpan=document.createElement('span');
					var txt=document.createTextNode(objChildForm.title);
					domSpan.appendChild(txt);
					result.appendChild(domSpan);
					
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
						if (this.objStackContainer.removeChild(selectedForm)) {
							selectedForm.destroyRecursive();
						}
						this.doG740Repaint();
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
				url: '',
				fieldName: '',
				params: {},
				constructor: function(para, domElement) {
					this.params={};
				},
				destroy: function() {
					var procedureName='g740.PanelWebBrowser.destroy';
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
							this.url='';
						}
						return true;
					}
					if (name=='fieldName') {
						this.fieldName=value;
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
					this.inherited(arguments);
					this.doG740Repaint();
				},
				doG740Repaint: function(para) {
					if (!this.domIFrame) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					if (this.fieldName) {
						var objRowSet=this.getRowSet();
						if (!objRowSet) return false;
						var node=objRowSet.getFocusedNode();
						var fields=objRowSet.getFields(node);
						if (!fields[this.fieldName]) return false;
						var url=objRowSet.getFieldProperty({fieldName: this.fieldName});
					}
					else {
						var url=this.url;
					}
					
					var g740params={};
					var objRowSet=this.getRowSet();
					if (objRowSet) {
						g740params=objRowSet._getRequestG740params(this.params);
					}
					else if (this.objForm) {
						g740params=this.objForm._getRequestG740params(this.params);
					}
					
					var urlParams='';
					var urlParamDelimiter='';
					for(var paramName in g740params) {
						if (g740params[paramName]=='') continue;
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
						delay: 200,
						obj: this,
						func: this._navigate,
						para: url
					});
					return true;
				},
				_navigate: function(url) {
					if (this.src==url) return true;
					this.domIFrame.src=url;
					this.src=url;
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
			if (g740.xml.getAttrValue(xml, 'style', '')) para.g740style=g740.xml.getAttrValue(xml, 'style', '');
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
			if (g740.xml.isAttr(xml,'field')) para.fieldName=g740.xml.getAttrValue(xml,'field','');

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
			var result=new g740.PanelWebBrowser(para, null);
			result.params=panelParams;
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
			if (g740.xml.isAttr(xml,'caption')) para.label=g740.xml.getAttrValue(xml,'caption','');
			if (g740.xml.isAttr(xml,'size')) para.g740size=g740.xml.getAttrValue(xml,'size','');
			if (g740.xml.isAttr(xml,'style')) para.g740style=g740.xml.getAttrValue(xml,'style','');
			if (g740.xml.isAttr(xml,'login')) para.g740login=g740.xml.getAttrValue(xml,'login','');
			var txt=dojo.trim(xml.textContent);
			if (txt!='') para.innerHTML=txt;
			var result=new g740.PanelHTML(para, null);
			return result;
		};
		g740.panels.registrate('html', g740.panels._builderPanelHTML);
		
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
		
			if (g740.xml.getAttrValue(xml, 'tab', '')==1) {
				var result=new g740.PanelMultiTabsForm(para, null);
				//var result=new g740.PanelTabForm(para, null);
			} 
			else {
				var result=new g740.PanelSingleForm(para, null);
			}
			return result;
		};
		g740.panels.registrate('form', g740.panels._builderPanelForm);

		return g740;
	}
);