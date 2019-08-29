/**
 * G740Viewer
 * Copyright 2017-2019 Galinsky Leonid lenq740@yandex.ru
 * Licensed under the BSD license
 */

define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
		g740.request={
			_isError: false,
			_indexExecuted: 0,
			_fifo: [],
			
// Заявка на запрос, параметры в виде списка XML узлов или строк
//	para.arrayOfRequest	- Массив запросов в виде XML узлов или строк
//	para.url			- Адрес скрипта на сервере, если не задано - то из g740.config.urlServer
//	para.sync			- Синхронный запрос, ждем ответа
//
//	para.objOwner		- Ссылка на отправивший запрос объект
//	para.requestName	- Имя запроса (если запрос одиночный)
//	para.requestMode	- подрежим запроса (если запрос одиночный)
//	para.parentId		- id родительского источника данных, если запрос отправлен из источника данных
//	para.id				- id текущей строки
//	para.nodeType		- для дерева - тип текущего узла
//	para.parentNodeId	- для дерева - id родительского узла
//	para.parentNodeType	- для дерева - тип родительского узла
//
//	para.phpInfoMode	- режим служебного запроса, обращающегося к phpinfo.php и показывающего результат в отдельном окне
			send: function(para) {
				var result=false;
				var procedureName='g740.request.send';
				if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
				if (!para.objOwner) g740.systemError(procedureName, 'errorValueUndefined', 'para.objOwner');
				var message='';
				if (para.arrayOfRequest) {
					for (var index=0; index<para.arrayOfRequest.length; index++) {
						var item=para.arrayOfRequest[index];
						if (g740.xml.isXmlNode(item)) {
							message+="\n"+g740.xml.toStr(item);
							continue;
						}
						if (typeof(item)=='string') {
							message+="\n"+item;
							continue;
						}
						para.arrayOfRequest[index]=null;
					}
				}
				para.arrayOfRequest=null;
				para.message='<?xml version="1.0" encoding="UTF-8" ?>'+"\n"+'<root type="g740" version="1.0" ';
				if (g740.config.session) para.message+='session="'+g740.config.session+'" ';
				if (g740.config.csrfToken) para.message+='csrftoken="'+g740.config.csrfToken+'" ';
				para.message+='>'+message+"\n"+'</root>';
				if (!para.url) para.url=g740.config.urlServer;
				para.phpInfoMode=false;
				if (!para.sync) {
					var isFound=false;
					for (var i=0; i<this._fifo.length; i++) {
						var p=this._fifo[i];
						if (p.objOwner==para.objOwner && p.requestName==para.requestName && p.requestMode==para.requestMode) {
							this._fifo[i]=para;
							isFound=true;
							break;
						}
					}
					if (!isFound) this._fifo.push(para);
					result=this._sendLastFiFo();
				} 
				else {
					result=this._sendMessage(para);
				}
				return result;
			},
// Заявка на запрос к phpinfo.php
			sendPhpInfo: function(para) {
				var procedureName='g740.request.sendPhpInfo';
				if (!para) para={};
				if (!para.url) para.url=g740.config.urlPhpInfo;
				para.phpInfoMode=true;
				para.sync=true;
				return this._sendMessage(para);
			},
// Выполнение запроса
//	para.message		- передаваемое на сервер сообщение
//	para.url			- Адрес скрипта на сервере, если не задано - то из g740.config.urlServer
//	para.sync			- Синхронный запрос, ждем ответа

//	para.objOwner		- Ссылка на отправивший запрос объект
//	para.requestName	- Имя запроса (если запрос одиночный)
//	para.requestMode	- подрежим запроса (если запрос одиночный)
//	para.parentId		- id родительского источника данных, если запрос отправлен из источника данных
//	para.id				- id текущей строки
//	para.nodeType		- для дерева - тип текущего узла
//	para.parentNodeId	- для дерева - id родительского узла
//	para.parentNodeType	- для дерева - тип родительского узла

//	para.phpInfoMode	- режим служебного запроса к phpinfo.php
			_sendMessage: function(para) {
				var r={
					message: '',
					url: g740.config.urlServer,
					phpInfoMode: false,
				};
				if (para) {
					if (para.message) {
						r.message=para.message;
					}
					if (para.phpInfoMode) r.phpInfoMode=true;
					if (para.url) r.url=para.url;
					if (para.objOwner) r.objOwner=para.objOwner;
					if (para.sync) r.sync=true;
					if (para.requestName) r.requestName=para.requestName;
					if (para.requestMode) r.requestMode=para.requestMode;
					if (para.parentId) r.parentId=para.parentId;
					if (para.id) r.id=para.id;
					if (para.nodeType) r.nodeType=para.nodeType;
					if (para.parentNodeId) r.parentNodeId=para.parentNodeId;
					if (para.parentNodeType) r.parentNodeType=para.parentNodeType;
				}
				if (para.sync) this._isError=false;
				
				this._indexExecuted++;
				dojo.xhr(
					'POST',
					{
						url: r.url,
						timeout: g740.config.timeoutMaxRequest,
						handleAs: 'xml',
						sync: (para.sync==true),
						headers: {
							'Content-Type': 'text/xml; charset=utf-8',
							'Cache-Control': 'no-store, no-cache, must-revalidate'
						},
						postData: r.message,
						para: r,
						load: this.doResponseLoad,
						error: this.doResponseError
					},
					false
				);
				if (para.sync && this._isError) return false;
				return true;
			},
// Выполнение синхронных запросов из очереди
			_sendLastFiFo: function() {
				if (this._indexExecuted>0) return true;
				if (this._fifo.length==0) return true;
				var para=this._fifo.shift();
				if (!para) return this._sendLastFiFo();
				if (para.objOwner && para.objOwner.isObjectDestroed) return this._sendLastFiFo();
				return this._sendMessage(para);
			},
			doResponseLoad: function(response, ioArgs) {
				var procedureName='g740.request.doResponseLoad';
				var result=false;
				var para={};
				if (ioArgs && ioArgs.args && ioArgs.args.para) para=ioArgs.args.para;
				if (para.phpInfoMode) {
					var w=window.open('about:blank', '_blank');
					if (w) {
						w.document.write(ioArgs.xhr.responseText);
					}
				}
				else {
					var xml=null;
					if (g740.xml.isXmlNode(response)) {
						xml=response.documentElement;
					}
					if (xml==null) {
						var xml=g740.xml.createElement('root');
						xml.setAttribute('type', 'g740');
						var xmlResponse=g740.xml.createElement('response');
						xmlResponse.setAttribute('name', 'error');
						xmlResponse.setAttribute('message', g740.getMessage('errorResponseNotXml'));
						xml.appendChild(xmlResponse);
					}
					para.xmlResponse=xml;
					result=g740.request.doResponse(para);
					if (para.sync) g740.request._isError=!result;
				}
				return result;
			},
			doResponseError: function(response, ioArgs) {
				var procedureName='g740.request.doResponseError';
				var result=false;
				var para={};
				if (ioArgs && ioArgs.args && ioArgs.args.para) para=ioArgs.args.para;
				if (para.phpInfoMode) {
					var w=window.open('about:blank', '_blank');
					if (w) {
						w.document.write(ioArgs.xhr.responseText);
					}
				}
				else {
					var xml=g740.xml.createElement('root');
					xml.setAttribute('type', 'g740');
					var xmlResponse=g740.xml.createElement('response');
					xmlResponse.setAttribute('name', 'error');
					xmlResponse.setAttribute('message', g740.getMessage('errorResponseUndefined'));
					xml.appendChild(xmlResponse);
					para.xmlResponse=xml;
					result=g740.request.doResponse(para);
				}
				if (para.sync) g740.request._isError=true;
				return false;
			},
// Обработка ответа
//	para.objOwner - объект, пославший запрос
//	para.xmlResponse - XML ответ
			doResponse: function(para) {
				var procedureName='g740.request.doResponse';
				var result=true;
				var errMessage='';
				var message='';
				try {
					if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
					if (!para.objOwner) g740.systemError(procedureName, 'errorValueUndefined', 'para.objOwner');
					var xmlResponse=para.xmlResponse;
					if (!g740.xml.isXmlNode(xmlResponse)) g740.responseError('errorNotXml', 'para.xmlResponse');
					if (xmlResponse.nodeName!='root' || g740.xml.getAttrValue(xmlResponse,'type','')!='g740') g740.responseError('errorXmlNodeNotFound', 'root type="g740"');
					if (g740.xml.isAttr(xmlResponse,'session')) g740.config.session=g740.xml.getAttrValue(xmlResponse,'session','');
					if (g740.xml.isAttr(xmlResponse,'csrftoken')) g740.config.csrfToken=g740.xml.getAttrValue(xmlResponse,'csrftoken','');
					
					var lst=g740.xml.findArrayOfChild(xmlResponse, {nodeName:'response'});
					
					var isDisconnected=false;
					var isError=false;
					var lstResponseExec=[];
					for (var i=0; i<lst.length; i++) {
						var xmlItem=lst[i];
						if (!g740.xml.isXmlNode(xmlItem)) continue;
						var name=g740.xml.getAttrValue(xmlItem,'name','');
						if (!name) name=g740.xml.getAttrValue(xmlItem,'response','');
						if (!name) continue;
						var mess=g740.xml.getAttrValue(xmlItem,'message','');
						if (name=='error' || name=='disconnected') {
							if (name=='disconnected') isDisconnected=true;
							isError=true;
							if (mess) {
								if (errMessage) errMessage+="\n";
								errMessage+=mess;
							}
						}
						else {
							if (name=='exec') {
								var responseExec=g740.xml.getAttrValue(xmlItem,'exec','');
								if (responseExec) {
									var params={};
									var lstParams=g740.xml.findArrayOfChild(xmlItem, {nodeName:'param'});
									for(var k=0; k<lstParams.length; k++) {
										var xmlParam=lstParams[k];
										if (!g740.xml.isXmlNode(xmlParam)) continue;
										var paramName=g740.xml.getAttrValue(xmlParam,'name','');
										if (!paramName) paramName=g740.xml.getAttrValue(xmlParam,'param','');
										if (!paramName) continue;
										var paramValue='';
										if (g740.xml.isAttr(xmlParam,'value')) {
											var paramValue=g740.xml.getAttrValue(xmlParam,'value','');
										}
										else {
											var xmlValue=xmlParam.firstChild;
											if (xmlValue && xmlValue.nodeType==3) {
												paramValue=xmlValue.nodeValue;
											}
										}
										params[paramName]={
											name: paramName,
											value: paramValue
										};
									}
									lstResponseExec.push({
										exec: responseExec,
										params: params
									});
								}
							}
							if (mess) {
								if (message) message+="\n";
								message+=mess;
							}
						}
					}
					if (isError) {
						result=false;
						message='';
					}
					if (!isDisconnected && !g740.application.getIsModeLoginDialog()) {
						var isFirstOk=true;
						if (!isError) for (var i=0; i<lst.length; i++) {
							var xmlItem=lst[i];
							if (!g740.xml.isXmlNode(xmlItem)) continue;
							var name=g740.xml.getAttrValue(xmlItem,'name','');
							if (!name) name=g740.xml.getAttrValue(xmlItem,'response','');
							if (name=='ok' && para.objOwner.doResponse) {
								var p={};
								p.xmlResponse=xmlItem;
								if (para.requestName) p.requestName=para.requestName;
								if (para.requestMode) p.requestMode=para.requestMode;
								if (para.parentId) p.parentId=para.parentId;
								if (para.id) p.id=para.id;
								if (para.nodeType) p.nodeType=para.nodeType;
								if (para.parentNodeId) p.parentNodeId=para.parentNodeId;
								if (para.parentNodeType) p.parentNodeType=para.parentNodeType;
								if (para.xmlRequest) p.xmlRequest=para.xmlRequest;
								p.isFirstOk=isFirstOk;
								if (!para.objOwner.doResponse(p)) result=false;
								isFirstOk=false;
							}
						}
						if (lstResponseExec.length>0 && para.objOwner) {
							var obj=para.objOwner;
							if (obj.g740className=='g740.RowSet' && obj.objForm) obj=obj.objForm;
							if (obj.execListOfRequests) {
								obj.execListOfRequests(lstResponseExec);
							}
						}
					}
				}
				catch (e) {
					if (errMessage) errMessage+="\n";
					errMessage+=e.message;
					result=false;
					message='';
				}
				if (isDisconnected) {
					if (errMessage) {
						var objDialog=g740.showError(errMessage);
						objDialog.onCloseOk=g740.application.doG740ShowLoginForm;
						objDialog.closeObj=g740.application;
					} 
					else {
						g740.application.doG740ShowLoginForm();
					}
					this._fifo=[];
					this._indexExecuted=0;
				}
				else {
					if (g740.application.getIsModeLoginDialog()) {
						g740.application.goReload('');
						return true;
					}
					if (errMessage) {
						var isShowError=true;
						var obj=null;
						if (para && para.objOwner) {
							var obj=para.objOwner;
							if (obj.g740className=='g740.RowSet' && obj.isIgnoreRequestError) isShowError=false;
						}
						if (isShowError) g740.showError(errMessage, para.objOwner);
					}
					if (message) g740.showMessage(message, para.objOwner);
					this._indexExecuted--;
					this._sendLastFiFo();
				}
				return result;
			},
			httpGet: function(url) {
				var hiddenIFrameID='hiddenDownloader';
				var iframe=document.getElementById(hiddenIFrameID);
				if (iframe===null) {
					iframe=document.createElement('iframe');
					iframe.id=hiddenIFrameID;
					iframe.name=hiddenIFrameID;
					iframe.style.display='none';
					document.body.appendChild(iframe);
				}
				iframe.src=url;
				return true;
			},
			httpOpen: function(url, params) {
				if (!params) params={};
				if (params.windowName) {
					if (!params.windowWidth) params.windowWidth=parseInt(window.outerWidth*0.9);
					if (!params.windowHeight) params.windowHeight=parseInt(window.outerHeight*0.9);
					window.open(url, params.windowName, 'centerscreen=yes, width='+params.windowWidth+', height='+params.windowHeight+', menubar=no, toolbar=no, location=no, status=no, scrollbars=yes');
				}
				else {
					window.open(url);
				}
				return true;
			},
			httpPut: function(url, ext) {
				var formName='g740-requestPut-Form'+g740.request.httpPutInfo.formNameIndex++;
				var iframe=document.createElement('iframe');
				iframe.id=formName+'IFrame';
				iframe.name=formName+'IFrame';
				iframe.style.display='none';
				iframe.setAttribute('data-formname',formName);
				document.body.appendChild(iframe);
				iframe.src='about:blank';
				dojo.on(
					iframe,
					'load',
					function() {
						g740.request.httpPutInfo.onLoaded(this);
					}
				);
			
				var domFormPut=document.createElement('form');
				domFormPut.method='post';
				domFormPut.target=formName+'IFrame';
				domFormPut.enctype='multipart/form-data';
				domFormPut.id=formName;
				domFormPut.style.display='none';
				document.body.appendChild(domFormPut);
				var domInput=document.createElement('input');
				domInput.name='sourcefile';
				domInput.id=formName+'Input';
				domInput.setAttribute('data-formname',formName);
				domInput.type='file';
				dojo.on(
					domInput,
					'change',
					function() {
						var formName=this.getAttribute('data-formname');
						var domFormPut=document.getElementById(formName);
						domFormPut.submit();
						g740.request.httpPutInfo.onSubmit();
					}
				);
				domFormPut.appendChild(domInput);
				domFormPut.action=url;
				var accept='';
				var domInput=document.getElementById(formName+'Input');
				if (ext) {
					var lstExt=ext.split(';');
					for(var i=0; i<lstExt.length; i++) {
						var e=lstExt[i];
						if (e=='jpg') {
							if (accept) accept+=',';
							accept+='image/jpeg';
						}
						if (e=='png') {
							if (accept) accept+=',';
							accept+='image/png';
						}
						if (e=='gif') {
							if (accept) accept+=',';
							accept+='image/gif';
						}
						if (e=='pdf') {
							if (accept) accept+=',';
							accept+='application/pdf';
						}
						if (e=='doc') {
							if (accept) accept+=',';
							accept+='application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
						}
						if (e=='xls') {
							if (accept) accept+=',';
							accept+='application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
						}
						if (e=='zip') {
							if (accept) accept+=',';
							accept+='application/zip';
						}
						if (e=='xml') {
							if (accept) accept+=',';
							accept+='text/xml';
						}
					}
				}
				domInput.setAttribute('accept',accept);
				domInput.click();
				g740.request.httpPutInfo.signalDialogClosed=dojo.on(
					document.body,
					'mousemove',
					function() {
						if (g740.request.httpPutInfo.signalDialogClosed) {
							g740.request.httpPutInfo.signalDialogClosed.remove();
							g740.request.httpPutInfo.signalDialogClosed=null;
						}
						g740.execDelay.go({
							obj: g740.request.httpPutInfo,
							func: g740.request.httpPutInfo.onDialogClosed,
							delay: 50
						});
					}
				);
				return true;
			},
			httpPutInfo: {
				formNameIndex: 1,
				isProcessed: false,
				
				signalDialogClosed: null,
				onDialogClosed: function() {
					if (g740.request.httpPutInfo.isProcessed) return true;
					var objForm=g740.application.getFocusedForm();
					if (objForm && objForm.continueExecListOfRequest) {
						objForm.fifoRequests=[];
						objForm.continueExecListOfRequest();
					}
				},
				onLoaded: function(domNode) {
					if (!g740.request.httpPutInfo.isProcessed) return false;
					g740.request.httpPutInfo.isProcessed=false;
					g740.application.doLockScreenHide();
					var isLoaded=true;
					try {
						if (domNode && domNode.contentWindow) {
							var iframeDocument=domNode.contentWindow.document;
							var message=dojo.trim(iframeDocument.body.textContent);
							message=message.replaceAll("\n",' ');
							message=message.replaceAll("\r",'');
							if (message!='' && message!='ok') {
								var objForm=g740.application.getFocusedForm();
								g740.showError(message, objForm);
								isLoaded=false;
							}
						}
					}
					catch(e) {
					}
					if (domNode) {
						var formName=domNode.getAttribute('data-formname');
						if (formName) {
							var domForm=document.getElementById(formName);
							if (domForm && domForm.parentNode) domForm.parentNode.removeChild(domForm);
						}
						if (domNode.parentNode) domNode.parentNode.removeChild(domNode);
					}

					
					var objForm=g740.application.getFocusedForm();
					if (!isLoaded && objForm) {
						objForm.fifoRequests=[];
					}
					if (objForm && objForm.continueExecListOfRequest) {
						objForm.continueExecListOfRequest();
					}
				},
				onSubmit: function() {
					if (g740.request.httpPutInfo.signalDialogClosed) {
						g740.request.httpPutInfo.signalDialogClosed.remove();
						g740.request.httpPutInfo.signalDialogClosed=null;
					}
					g740.request.httpPutInfo.isProcessed=true;
					g740.application.doLockScreenShow();
				}
			}
		}
		return g740;
	}
);