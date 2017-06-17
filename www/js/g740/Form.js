//-----------------------------------------------------------------------------
//	Экранная форма
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
// Экранная форма
		dojo.declare(
			"g740.Form",
			dijit.layout.BorderContainer,
			{
				g740className: 'g740.Form',
				name: '',
				isModal: false,
				isClosable: true,
				g740Width: '80%',
				g740Height: '80%',
				
				isG740BorderContainer: true,
				isG740CanChilds: true,
				
				isObjectDestroed: false,	// Объект уничтожен
				isFormCreated: false,		// Форма построена
				objPanelForm: null,			// Для формы приложения, панель, в которой отображается экранная форма
				objFocusedPanel: null,		// Панель, имеющая фокус ввода
				rowsets: {},				// Список RowSet
				modalResults: {},

// Список поддерживаемых запросов
//	r=this.requests[name], name - <имя запроса>
//		r.name		- запрос
//		r.mode
//		r.enabled
//		r.js_enabled
//		r.caption
//		r.icon
//		r.timeout
//		r.sync
//
//		p=r.params[name]
//			p.name
//			p.value
//			p.js_value
//			p.enabled
//			p.js_enabled
//			p.type
				requests: {},				// Список поддерживаемых запросов
				g740childs: [],				// Список дочерних панелек для поддержки visible
				
// Создание экземпляра объекта
//	para.name
				constructor: function(para) {
					var procedureName='g740.Form.constructor';
					this.g740className='g740.Form';
					if (!para) para={};
					this.isObjectDestroed=false;
					this.isFormCreated=false;
					this.rowsets={};
					this.requests={};
					if (para.name) this.name=para.name;
					this.objFocusedPanel=null;
					this.g740childs=[];
					this.modalResults={};
					//console.log(this);
				},
// Уничтожение экземпляра объекта
				destroy: function() {
					var procedureName='g740.Form.destroy';
					this.modalResults={};
					this.isObjectDestroed=true;
					this.objPanelForm=null;
					this.objFocusedPanel=null;
					if (this.requests) {
						for (var name in this.requests) {
							this.requests[name]=null;
						}
						this.requests={};
					}
					for (var name in this.rowsets) {
						var obj=this.rowsets[name];
						if (obj) obj.destroy();
						this.rowsets[name]=null;
					}
					this.rowsets={};

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
					this.inherited(arguments);
				},
// Задать значение св-ва
				set: function(name, value) {
					if (name=='name') {
						this.name=value;
						return true;
					}
					this.inherited(arguments);
				},
				getFocusedRowSet: function() {
					var result=null;
					if (this.objFocusedPanel) {
						result=this.rowsets[this.objFocusedPanel.rowsetName];
					}
					return result;
				},
			    getRequest: function (requestName, requestMode) {
					var fullName=requestName;
					if (requestMode) fullName+='.'+requestMode;
					return this.requests[fullName];
			    },
				
// Выполнить запрос по полному имени
//	requestExec = #form|#focus|<имя набора строк>.name.mode(param1;...;paramN)
				execByFullName: function(requestExec, attr) {
					if (!attr) attr={};
					var requestParams='';
					var n=requestExec.indexOf('(')
					if (n>=0) {
						requestParams=requestExec.substr(n+1);
						requestExec=requestExec.substr(0,n);
						n=requestParams.lastIndexOf(')');
						if (n>=0) requestParams=requestParams.substr(0,n);
					}
					
					var requestName='';
					var requestMode='';
					var rowsetName='#form';
					var p=requestExec.split('.');
					if (p.length==1) {
						requestName=p[0];
						requestMode='';
					}
					if (p.length==2) {
						var name=p[0];
						if (name=='#focus' || name=='#form' || this.rowsets[name]) {
							rowsetName=p[0];
							requestName=p[1];
							requestMode='';
						}
						else {
							requestName=p[0];
							requestMode=p[1];
						}
					}
					if (p.length>=3) {
						rowsetName=p[0];
						requestName=p[1];
						requestMode=p[2];
					}
					if (rowsetName=='#focus') {
						rowsetName='';
						var objRowSet=this.getFocusedRowSet();
						if (!objRowSet) return false;
						rowsetName=objRowSet.name;
					}
					var G740params={};
					if (rowsetName!='#form') {
						var objRowSet=this.rowsets[rowsetName];
						if (!objRowSet) return false;
						
						if (requestParams) G740params=objRowSet._getRequestG740params(requestParams);
						return objRowSet.exec({
							requestName: requestName,
							requestMode: requestMode,
							G740params: G740params,
							attr: attr
						});
					} else {
						if (requestParams) G740params=this._getRequestG740params(requestParams);
						return this.exec({
							requestName: requestName,
							requestMode: requestMode,
							G740params: G740params,
							attr: attr
						});
					}
				},
				
// Выполнить запрос
//	para.requestName
//	para.requestMode
//	para.G740params - ассоциативный массив значений параметров в формате G740
//	para.attr		- ассоциативный массив атрибутов, не передаваемый на сервер
				exec: function(para) {
					if (!para) return false;
					var requestName=para.requestName;
					var requestMode=para.requestMode;
					if (!this.getRequestEnabled(requestName, requestMode)) return false;

					var attr={};
					if (para.attr) attr=para.attr;
					
					var fullName=requestName;
					if (requestMode) fullName=requestName+'.'+requestMode;
					var r=this.requests[fullName];
					
					var isSave=attr['save'];
					var isClose=attr['close'];
					if (r) {
						if (r.save) isSave=true;
						if (r.close) isClose=true;
					}
					
					if (isSave) {
						var objFocusedRowSet=this.getFocusedRowSet();
						if (objFocusedRowSet && !objFocusedRowSet.isFilter && objFocusedRowSet.getExistUnsavedChanges()) {
							if (!objFocusedRowSet.exec({requestName: 'save'})) return false;
						}
					}

					var G740params={};
					if (r && r.params) {
						var p=this._getRequestG740params(r.params);
						for(var name in p) G740params[name]=p[name];
					}
					if (para.G740params) for(var name in para.G740params) G740params[name]=para.G740params[name];
					
					if (r && r.requests) {
						this._execResult='';
						for(var i=0; i<r.requests.length; i++) {
							if (this._execResult!='') break;
							
							var rr=r.requests[i];
							var rowsetName=rr.rowset;
							
							if (!rowsetName) rowsetName='#form';
							if (rr.name=='connect') rowsetName='#form';
							if (rr.name=='disconnect') rowsetName='#form';
							if (rr.name=='close') rowsetName='#form';
							if (rr.name=='result') rowsetName='#form';
							if (rr.name=='form') rowsetName='#form';
							if (rr.name=='httpget') rowsetName='#form';
							
							if (rowsetName=='#focus') {
								var objFocusedRowSet=this.getFocusedRowSet();
								if (!objFocusedRowSet) continue;
								rowsetName=objFocusedRowSet.name;
							}
							
							if (rowsetName=='#form') {
								if (!this.getRequestEnabled(rr.name, rr.mode)) continue;
								var p=this._getRequestG740params(rr.params);
								for(var name in G740params) p[name]=G740params[name];
								var attr={};
								if (rr.close) attr['close']=rr.close;
								if (rr.name=='form') {
									if (rr.modal) attr['modal']=rr.modal;
									if (rr.width) attr['width']=rr.width;
									if (rr.height) attr['height']=rr.height;
									if (rr.closable) attr['closable']=rr.closable;
								}
								if (rr.name=='httpget') {
									attr['url']=rr.url;
								}
								var result=this.exec({
									requestName: rr.name,
									requestMode: rr.mode,
									sync: true,
									G740params: p,
									attr: attr
								});
								if (!result) return false;
								if (this._execResult=='form') {
									var objDialog=g740.application.getModalDialog();
									if (objDialog && objDialog.attr && objDialog.attr.objForm==this) {
										var requests=[];
										for(var j=i+1; j<r.requests.length; j++) {
											var rrr=r.requests[j];
											requests.push(rrr);
										}
										if (requests.length>0) objDialog.attr['onafterclose']=requests;
									}
									break;
								}
							}
							else {
								var objRowSet=this.rowsets[rowsetName];
								if (!objRowSet) return false;
								if (!objRowSet.getRequestEnabled(rr.name, rr.mode)) continue;
								var p=objRowSet._getRequestG740params(rr.params);
								for(var name in G740params) p[name]=G740params[name];
								
								var result=objRowSet.exec({
									requestName: rr.name,
									requestMode: rr.mode,
									sync: true,
									G740params: p
								});
								if (!result) return false;
								if (rr.close) {
									isClose=true;
									break;
								}
							}
						}
					}
					else {
						var result=false;
						if (requestName=='result') {
							result=true;
						} 
						else if (requestName=='close') {
							result=true;
							isClose=true;
						}
						else if (requestName=='form') {
							attr.objForm=this;
							if (r) {
								if (r.modal) attr['modal']=r.modal;
								if (r.width) attr['width']=r.width;
								if (r.height) attr['height']=r.height;
								if (r.closable) attr['closable']=r.closable;
							}
							result=g740.application.doG740ShowForm({
								formName: requestMode, 
								G740params: G740params,
								attr: attr
							});
							this._execResult='form';
						} 
						else if (requestName=='httpget') {
							var url=attr.url;
							if (!url) url='';
							var urlParams='';
							var urlParamDelimiter='';
							for(var paramName in G740params) {
								if (G740params[paramName]=='') continue;
								if (paramName=='url') {
									url=G740params[paramName];
									continue;
								}
								urlParams+=urlParamDelimiter+encodeURIComponent(paramName)+'='+encodeURIComponent(G740params[paramName]);
								urlParamDelimiter='&';
							}
							if (urlParams) {
								urlParamDelimiter='?';
								if (url.indexOf('?')>=0) urlParamDelimiter='&';
								url+=urlParamDelimiter+urlParams;
							}
							if (requestMode=='open') {
								result=g740.request.httpOpen(url);
							}
							else {
								result=g740.request.httpGet(url);
							}
						}
						else {
							var xmlRequest=g740.xml.createElement('request');
							xmlRequest.setAttribute('name', requestName);
							if (requestMode) xmlRequest.setAttribute('mode', requestMode);
							xmlRequest.setAttribute('form', this.name);

							for(var name in G740params) {
								xmlParam=g740.xml.createElement('param');
								xmlParam.setAttribute('name', name);
								var xmlText=g740.xml.createTextNode(G740params[name]);
								xmlParam.appendChild(xmlText);
								xmlRequest.appendChild(xmlParam);
							}
							result=g740.request.send({
								arrayOfRequest: [xmlRequest],
								objOwner: this,
								sync: true
							});
						}
						if (!result) return false;
					}
					if (isClose && this.isModal) {
						_execResult='break';
						g740.execDelay.go({
							func: g740.application.closeModalForm
						});
					}
					return true;
				},
				_execResult: '',	// блокируем дальнейшее выполнение цепочки запросов
				
				getRequestEnabled: function(requestName, requestMode) {
					var procedureName='g740.Form['+this.name+'].getRequestEnabled';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var fullName=requestName;
					if (requestMode) fullName=requestName+'.'+requestMode;
					var r=this.requests[fullName];
					if (!r) {
						if (requestName=='connect' || requestName=='disconnect' || requestName=='httpget' || requestName=='close') return true;
						if (requestName=='form') return requestMode!='';
						if (requestName=='result') return true;
						return false;
					}
					if (r.enabled===false) return false;
					if (!g740.js_eval(this, r.js_enabled, true)) return false;
					return true;
				},
				sendRequestForm: function(G740params) {
					var procedureName='g740.Form['+this.name+'].sendRequestForm';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!G740params) G740params={};
					
					var xmlRequest=g740.xml.createElement('request');
					xmlRequest.setAttribute('name', 'form');
					xmlRequest.setAttribute('form', this.name);
					
					for(var name in G740params) {
						xmlParam=g740.xml.createElement('param');
						xmlParam.setAttribute('name', name);
						var xmlText=g740.xml.createTextNode(G740params[name]);
						xmlParam.appendChild(xmlText);
						xmlRequest.appendChild(xmlParam);
					}
	
					var result=g740.request.send({
						arrayOfRequest: [xmlRequest],
						objOwner: this,
						sync: true
					});
					return result;
				},
				// Возвращаем рассчитаные параметры в контексте выполнения запроса				
				_getRequestG740params: function(requestParams) {
					var procedureName='g740.Form['+this.name+']._getRequestG740params';
					var result={};
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!requestParams) return result;

					// Если параметры заданы строкой <имя>=<js_value>;...;<имя>=<js_value>
					if (typeof(requestParams)=='string') {
						if (requestParams.replaceAll(' ','').substr(0,4)=='get(') {
							requestParams=g740.js_eval(this, requestParams, '');
						}
						requestParams=requestParams.replaceAll('\u000A',';');
						requestParams=requestParams.replaceAll('\u000C','');
						var lst=requestParams.split(';');
						var lstResult={};
						for(var i in lst) {
							var item=lst[i];
							if (!item) continue;
							var lstitem=item.split('=');
							var name=lstitem[0].replaceAll(' ','');
							var p={};
							p['name']=name;
							if (lstitem.length==2) p['js_value']=lstitem[1];
							lstResult[name]=p;
						}
						requestParams=lstResult;
					}

					for(var paramName in requestParams) {
						var p=requestParams[paramName];
						if (!p) continue;
						if (p.enabled==false) continue;
						if (!g740.js_eval(this, p.js_enabled, true)) continue;
						var value='';
						if (typeof(p.value)!='undefined') {
							value=g740.convertor.toG740(p.value,p.type);
						} 
						else if (typeof(p.js_value)!='undefined') {
							var v=g740.js_eval(this, p.js_value, null);
							value=g740.convertor.toG740(v,p.type);
						}
						if (p.result) {
							var name=p.result;
							if (name==1) name=paramName;
							this.modalResults[name]=value;
						} 
						else {
							result[paramName]=value;
						}
					}
					return result;
				},
// Отправить запрос на первоначальную инициализацию наборов строк экранной формы
				sendRequestGetAllRowSetsDefinition: function() {
					var procedureName='g740.Form['+this.name+'].sendRequestGetAllRowSetsDefinition';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result=false;
					var arrayOfRequest=[];
					for(var name in this.rowsets) {
						var objRowSet=this.rowsets[name];
						if (!objRowSet) continue;
						if (objRowSet.isRef) continue;
						if (objRowSet.isFilter) continue;
						var message='<request name="definition" rowset="'+objRowSet.name+'" datasource="'+objRowSet.datasource+'"/>';
						arrayOfRequest.push(message);
					}
					if (arrayOfRequest.length>0) {
						var para={
							arrayOfRequest: arrayOfRequest,
							objOwner: this,
							sync: true
						};
						result=g740.request.send(para);
					}
					else {
						result=true;
					}
					return result;
				},
// Обработать ответ
				doResponse: function(para) {
					var procedureName='g740.Form['+this.name+'].doResponse';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result=false;
					var message='';
					if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
					var xmlResponse=para.xmlResponse;
					if (!g740.xml.isXmlNode(xmlResponse)) g740.responseError('errorNotXml', 'para.xmlResponse');
					if (xmlResponse.nodeName!='response') g740.responseError('errorXmlNodeNotFound', 'response');
					
					var name=g740.xml.getAttrValue(xmlResponse,'name','');
					if (name=='' || name=='ok') {
						var xmlForm=g740.xml.findFirstOfChild(xmlResponse,{nodeName: 'form'});
						if (xmlForm!=null) name='form';
						var xmlRowSet=g740.xml.findFirstOfChild(xmlResponse,{nodeName: 'rowset'});
						if (xmlRowSet!=null) name='definition';
					}
					if (name=='ok') {
						var rowsetName=g740.xml.getAttrValue(xmlResponse, 'rowset', '');
						result=true;
						if (rowsetName) {
							var objRowSet=this.rowsets[rowsetName];
							if (objRowSet) result=objRowSet.doResponse(para);
						}
					} 
					else if (name=='form') {
						isOkResponseName=true;
						var xmlForm=g740.xml.findFirstOfChild(xmlResponse,{nodeName: 'form'});
						if (!xmlForm) g740.responseError('errorXmlNodeNotFound', 'form');
						result=this.build(xmlForm);
					} 
					else if (name=='definition') {
						isOkResponseName=true;
						var xmlRowSet=g740.xml.findFirstOfChild(xmlResponse,{nodeName: 'rowset'});
						if (!xmlRowSet) g740.responseError('errorXmlNodeNotFound', 'rowset');
						result=this._buildRowSetFromDataSource(xmlRowSet);
					} 
					else if (name=='error') {
						result=false;
					} 
					else {
						g740.responseError('errorResponseName', name);
					}
					return result;
				},
// Построение формы по XML описанию
				build: function(xmlForm) {
					var procedureName='g740.Form['+this.name+'].build';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!g740.xml.isXmlNode(xmlForm)) g740.systemError(procedureName, 'errorNotXml', 'xmlForm');
					if (xmlForm.nodeName!='form') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'form');
					
					// Пускаем механизм логирования ошибок в описании экранной формы
					g740.trace.goBuilderStart();
					try {
						this.name=g740.xml.getAttrValue(xmlForm, 'name', '');
						this.title=g740.xml.getAttrValue(xmlForm, 'caption', '');
						
						this.isModal=g740.xml.getAttrValue(xmlForm, 'modal', '0')=='1';
						this.isClosable=g740.xml.getAttrValue(xmlForm, 'closable', '1')=='1';
						if (g740.xml.isAttr(xmlForm,'width')) this.g740Width=g740.xml.getAttrValue(xmlForm, 'width', this.g740Width);
						if (g740.xml.isAttr(xmlForm,'height')) this.g740Height=g740.xml.getAttrValue(xmlForm, 'height', this.g740Height);
						
						// Строим наборы строк
						var xmlRowSets=g740.xml.findFirstOfChild(xmlForm, {nodeName:'rowsets'});
						if (xmlRowSets) {
							var lstRowSets=g740.xml.findArrayOfChild(xmlRowSets, {nodeName:'rowset'});
						}
						else {
							var lstRowSets=g740.xml.findArrayOfChild(xmlForm, {nodeName:'rowset'});
						}

						// Создаем все наборы строк из XML описания экранной формы
						for (var i=0; i<lstRowSets.length; i++) {
							var xmlItem=lstRowSets[i];
							if (!g740.xml.isXmlNode(xmlItem)) continue;
							var objRowSet=this._buildCreateRowSet(xmlItem);
						}
						// Отправляем запрос на построение наборов строк по описанию из источника данных
						this.sendRequestGetAllRowSetsDefinition();

						// Подстраиваем наборы строк описаниями из экранной формы
						for (var name in this.rowsets) {
							var objRowSet=this.rowsets[name];
							if (!objRowSet) continue;
							if (objRowSet.isRef) continue;
							objRowSet.build(objRowSet._xmlRowSet);
							objRowSet._xmlRowSet=null;
						}
						// Строим связи между наборами строк
						this._buildRowSetLinks();
						
						// Строим описатели запросов экранной формы
						var xmlRequests=g740.xml.findFirstOfChild(xmlForm,{nodeName:'requests'});
						if (!g740.xml.isXmlNode(xmlRequests)) xmlRequests=xmlForm;
						var lstRequest=g740.xml.findArrayOfChild(xmlRequests, {nodeName:'request'});
						for (var i=0; i<lstRequest.length; i++) {
							var xmlRequest=lstRequest[i];
							if (!g740.xml.isXmlNode(xmlRequest)) continue;
							this._buildRequest(xmlRequest);
						}
						
						// Строим панели
						var xmlPanels=g740.xml.findFirstOfChild(xmlForm, {nodeName: 'panels'});
						if (!xmlPanels) xmlPanels=xmlForm;
						var lstXmlPanels=g740.xml.findArrayOfChild(xmlPanels,{nodeName:'panel'});
						for(var i=0; i<lstXmlPanels.length; i++) {
							var xmlChild=lstXmlPanels[i];
							g740.panels.buildPanel(xmlChild, this, this);
						}
						
						this.g740childs=[];
						var lst=this.getChildren();
						for (var i=0; i<lst.length; i++) {
							var objPanel=lst[i];
							if (!objPanel) continue;
							if (objPanel.onG740AfterBuild) {
								this.g740childs.push(objPanel);
								objPanel.onG740AfterBuild();
							}
						}
					}
					finally {
						g740.trace.goBuilderEnd();
					}
					this.doG740Repaint({});
					var bestPanel=this.getBestPanel();
					
					if (bestPanel && bestPanel.doG740Focus) {
						g740.execDelay.go({
							delay: 200,
							obj: bestPanel,
							func: bestPanel.doG740Focus
						});
					}
					this.doRefreshRowSets();
					return true;
				},
// Создаем набор строк по XML описанию экранной формы
				_buildCreateRowSet: function(xmlRowSet) {
					var procedureName='g740.Form['+this.name+']._buildCreateRowSet';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var objRowSet=null;
					if (!g740.xml.isXmlNode(xmlRowSet)) g740.systemError(procedureName, 'errorNotXml', 'xmlRowSet');
					if (xmlRowSet.nodeName!='rowset') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'rowset');
					
					var name=g740.xml.getAttrValue(xmlRowSet,'name','');
					if (!name) name=g740.xml.getAttrValue(xmlRowSet,'rowset','');
					var datasource=g740.xml.getAttrValue(xmlRowSet,'datasource',name);
					if (!name) name=datasource;
					if (!name) {
						g740.trace.goBuilder({
							formName: this.name,
							rowsetName: name,
							messageId: 'errorRowSetNameEmpty'
						});
						return false;
					}
					if (this.rowsets[name]) {
						g740.trace.goBuilder({
							formName: this.name,
							rowsetName: name,
							messageId: 'errorRowSetNameNotUnique'
						});
						return false;
					}
					var objRowSet=new g740.RowSet({objForm: this, name: name, datasource: datasource});
					objRowSet._buildRowSetProperty(xmlRowSet);
					objRowSet._xmlRowSet=xmlRowSet;
					this.rowsets[name]=objRowSet;
					
					var xmlChilds=g740.xml.findFirstOfChild(xmlRowSet,{nodeName:'childs'});
					if (!g740.xml.isXmlNode(xmlChilds)) xmlChilds=g740.xml.findFirstOfChild(xmlRowSet,{nodeName:'rowsets'});
					if (!g740.xml.isXmlNode(xmlChilds)) xmlChilds=xmlRowSet;
					var lst=g740.xml.findArrayOfChild(xmlChilds,{nodeName:'rowset'});
					for (var i=0; i<lst.length; i++) {
						var xmlItem=lst[i];
						if (!g740.xml.isXmlNode(xmlItem)) continue;
						var objChild=this._buildCreateRowSet(xmlItem);
						if (!objChild) continue;
						objChild.objParent=objRowSet;
						objRowSet.childs[objChild.name]=objChild;
					}
					return objRowSet;
				},
// Построение набора строк по ответу, сформированному источником данных datasource
				_buildRowSetFromDataSource: function(xmlRowSet) {
					var procedureName='g740.Form['+this.name+']._buildRowSetFromDataSource';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result=false;
					if (!g740.xml.isXmlNode(xmlRowSet)) g740.systemError(procedureName, 'errorNotXml', 'xmlRowSet');
					if (xmlRowSet.nodeName!='rowset') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'rowset');
					
					var name=g740.xml.getAttrValue(xmlRowSet,'name','');
					if (!name) name=g740.xml.getAttrValue(xmlRowSet,'rowset','');
					var datasource=g740.xml.getAttrValue(xmlRowSet,'datasource',name);
					if (!name) name=datasource;
					if (!name) {
						g740.trace.goBuilder({
							formName: this.name,
							rowsetName: name,
							messageId: 'errorRowSetNameEmpty'
						});
						return false;
					}
					var objRowSet=this.rowsets[name];
					if (!objRowSet) {
						g740.trace.goBuilder({
							formName: this.name,
							rowsetName: name,
							messageId: 'errorRowSetNotFoundInForm'
						});
						return false;
					}
					result=objRowSet.build(xmlRowSet);
					return result;
				},
// Построение связей между наборами строк
				_buildRowSetLinks: function() {
					var procedureName='g740.Form['+this.name+']._buildRowSetLinks';
					var result=true;
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					for (var name in this.rowsets) {
						var objRowSet=this.rowsets[name];
						if (!objRowSet) continue;
						if (objRowSet.parentName) {
							var objParent=this.rowsets[objRowSet.parentName];
							if (!objParent) {
								g740.trace.goBuilder({
									formName: this.name,
									rowsetName: objRowSet.parentName,
									messageId: 'errorRowSetNotFoundInForm'
								});
								result=false;
								continue;
							}
							objRowSet.objParent=objParent;
						}
						objRowSet.doAfterBuild();
					}
					return result;
				},
// Построение списка запросов уровня формы
				_buildRequest: function(xmlRequest) {
					var procedureName='g740.Form['+this.name+']._buildRequest';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!g740.xml.isXmlNode(xmlRequest)) g740.systemError(procedureName, 'errorNotXml', 'xmlRequest');
					if (xmlRequest.nodeName!='request') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'request');
					
					var requestName=g740.xml.getAttrValue(xmlRequest,'name','');
					requestName=g740.xml.getAttrValue(xmlRequest,'request',requestName);
					if (requestName=='close') requestName='onclose';
						
					var requestMode=g740.xml.getAttrValue(xmlRequest,'mode','');
					if (requestName=='form') var requestMode=g740.xml.getAttrValue(xmlRequest,'form',requestMode);
					
					var fullName=requestName;
					if (requestMode) fullName=requestName+'.'+requestMode;

					var request=this.requests[fullName];
					if (!request) {
						request={
							sync: true,
							name: requestName,
							mode: requestMode,
							enabled: true,
							params: {}
						};
					}
					g740.panels.buildRequestParams(xmlRequest, request);
					this.requests[fullName]=request;
					
					var xmlRequests=g740.xml.findFirstOfChild(xmlRequest,{nodeName:'requests'});
					if (!g740.xml.isXmlNode(xmlRequests)) xmlRequests=xmlRequest;
					var lstRequest=g740.xml.findArrayOfChild(xmlRequests, {nodeName:'request'});
					for (var i=0; i<lstRequest.length; i++) {
						var xmlR=lstRequest[i];
						var requestName=g740.xml.getAttrValue(xmlR,'name','');
						requestName=g740.xml.getAttrValue(xmlR,'request',requestName);
						var requestMode=g740.xml.getAttrValue(xmlR,'mode','');
						if (requestName=='form') var requestMode=g740.xml.getAttrValue(xmlR,'form',requestMode);
						
						r={
							sync: true,
							name: requestName,
							mode: requestMode,
							rowset: g740.xml.getAttrValue(xmlR,'rowset',''),
							enabled: true,
							params: {}
						};
						g740.panels.buildRequestParams(xmlR, r);
						
						if (!request.requests) request.requests=[];
						request.requests.push(r);
					}
					return true;
				},
				
				getBestPanel: function() {
					var objBestRowSet=this._getBestRowSet();
					var lst=this._getBestPanels(this, objBestRowSet);
					var objPanelFirst=null;
					var objPanelFocus=null;
					var objPanelTree=null;
					var objPanelGrid=null;
					var objPanelFields=null;
					for (var i=0; i<lst.length; i++) {
						var objPanel=lst[i];
						if (!objPanel) continue;
						if (!objPanelFocus && objPanel.isFocusOnShow) objPanelFocus=objPanel;
						if (!objPanelTree && objPanel.isG740Tree) objPanelTree=objPanel;
						if (!objPanelGrid && objPanel.isG740Grid) objPanelGrid=objPanel;
						if (!objPanelFields && objPanel.isG740Fields) objPanelFields=objPanel;
						if (!objPanelFirst) objPanelFirst=objPanel;
					}
					if (objPanelFocus) return objPanelFocus;
					if (objPanelTree) return objPanelTree;
					if (objPanelGrid) return objPanelGrid;
					if (objPanelFields) return objPanelFields;
					return this;
				},
				
				_getBestRowSet: function() {
					var result=null;
					for(var rowsetName in this.rowsets) {
						var objRowSet=this.rowsets[rowsetName];
						if (!objRowSet) continue;
						if (objRowSet.objParent) continue;
						if (objRowSet.isRef) continue;
						result=objRowSet;
						break;
					}
					return result;
				},
				_getBestPanels: function(objPanel, objBestRowSet) {
					var result=[];
					if (!objPanel) return result;
					if (objPanel.g740className=='g740.Panel') {
						if (objPanel.isFocusOnShow || (objBestRowSet && objBestRowSet.name==objPanel.rowsetName)) {
							result.push(objPanel);
						}
					}
					if (objPanel.getChildren) {
						var childs=objPanel.getChildren();
						for (var i=0; i<childs.length; i++) {
							var objChild=childs[i];
							if (!objChild) continue;
							var lst=this._getBestPanels(objChild, objBestRowSet);
							for (var k=0; k<lst.length; k++) result.push(lst[k]);
						}
					}
					return result;
				},
				
// Первоначальная начитка данных
				doRefreshRowSets: function() {
					var result=true;
					var procedureName='g740.Form['+this.name+'].doRefreshRowSets';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					for(var rowsetName in this.rowsets) {
						var objRowSet=this.rowsets[rowsetName];
						if (!objRowSet) continue;

						if (objRowSet.isRef) continue;
						if (objRowSet.isFilter) {
							objRowSet.doInitFilter();
							continue;
						}
						objRowSet.execAutoRefresh();	// запускаем цикл автомитической перечитки (если задан параметр autorefresh)
						if (objRowSet.objParent) continue;
						if (objRowSet.getRequestEnabled('refresh')) objRowSet.exec({requestName: 'refresh'});
					}
					return result;
				},
// Перерисовка экранных элементов, по умолчанию перерисовывается только текущая строка
//	objRowSet 		- набор строк
//	parentNode		- родительский узел для перерисовки дочерних элементов
//	isFull			- полная перерисовка всех дочерних элементов
//	isNavigate		- сменилась текущая строка
//	isRowUpdate		- изменения значений в текущей строке
				doG740Repaint: function(para) {
					this.doG740RepaintChildsVisible();
					// Перерисовываем детей
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						if (!obj) continue;
						if (!obj.doG740Repaint) continue;
						obj.doG740Repaint(para);
					}
				},
				doG740RepaintChildsVisible: function() {
					var index=0;
					for (var i=0; i<this.g740childs.length; i++) {
						var objPanel=this.g740childs[i];
						if (!objPanel) continue;
						if (objPanel.g740className=='g740.Panel' && objPanel.objForm && objPanel.js_visible) {
							var visible=g740.js_eval(objPanel.objForm, objPanel.js_visible, true);
							if (visible!=objPanel.visible) {
								if (visible) {
									this.addChild(objPanel, index);
									if (objPanel.doG740Repaint) objPanel.doG740Repaint({});
								}
								else {
									this.removeChild(objPanel);
								}
								objPanel.visible=visible;
							}
							if (!objPanel.visible) continue;
						}
						index++;
					}
				},

				doG740Get: function(name) {
					if (this.isObjectDestroed) return false;
					var p=name.split('.');

					var rowsetName=p[0];
					var n0=rowsetName.indexOf('[');
					var n1=rowsetName.indexOf(']');
					if (n0>=0 && n1>n0) {
						rowsetName=p[0].substr(0,n0);
					}
					if (rowsetName=='#result') {
						var name=p[1];
						var value=g740.application.modalResults[name];
						if (!value) value='';
						return value;
					}
					if (rowsetName=='#focus') {
						var objRowSet=this.getFocusedRowSet();
						if (!objRowSet) return false;
						rowsetName=objRowSet.name;
					}
					var objRowSet=this.rowsets[rowsetName];
					if (!objRowSet) return false;
					if (!objRowSet.doG740Get) return false;
					return objRowSet.doG740Get(name);
				},
				
// Обработка событий
				// Событие - смена панели, принимающей фокус ввода
				onG740ChangeFocusedPanel: function(objPanel) {
					var procedureName='g740.Form['+this.name+'].onG740ChangeFocusedPanel';
					if (this._isChangeFocusedPanelDisabled) return false;
					if (this.isObjectDestroed) return false;
					if (this.objFocusedPanel==objPanel) return true;
					var oldRowSet=this.getFocusedRowSet();
					var newRowSet=null;
					if (objPanel) {
						if (objPanel.g740className!='g740.Panel') return false;
						if (objPanel.objForm!=this) return false;
						if (objPanel.isObjectDestroed) return false;
						var newRowSet=this.rowsets[objPanel.rowsetName];
					}
					if (newRowSet && newRowSet.isRef) return true;
					if (newRowSet && newRowSet.isRefTree) return true;
					
					if (oldRowSet && (newRowSet!=oldRowSet)) {
						if (!oldRowSet.isFilter && oldRowSet.getExistUnsavedChanges()) {
							if (!oldRowSet.exec({requestName: 'save'})) {
								this._isChangeFocusedPanelDisabled=true;
								g740.execDelay.go({
									delay:50,
									obj: this,
									func: this._setFocusedPanelToOldFocusedPanel
								});
								return false;
							}
						}
					}
					this.objFocusedPanel=objPanel;
					if (newRowSet!=oldRowSet) {
						this.doG740Repaint({objRowSet: newRowSet});
					}
					return true;
				},
				_isChangeFocusedPanelDisabled: false,
				_setFocusedPanelToOldFocusedPanel: function() {
					if (this.objFocusedPanel) {
						if (this.objFocusedPanel.doG740Focus) {
							this.objFocusedPanel.doG740Focus();
						}
						else {
							try {
								this.objFocusedPanel.set('focused',true);
							}
							catch(e) {
							}
						}
					}
					g740.execDelay.go({
						delay:50,
						obj: this,
						func: this._setFocusedPanelDisabledToOff
					});
				},
				_setFocusedPanelDisabledToOff: function() {
					this._isChangeFocusedPanelDisabled=false;
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
						this.doG740FocusChildFirst();
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
						this.doG740FocusChildLast();
					}
				}
			}
		);
		return g740;
	}
)