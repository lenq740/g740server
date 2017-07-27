//-----------------------------------------------------------------------------
// Отправка запросов на сервер и предварительная обработка ответов
//	адрес сервера: 						g740.config.urlServer
//	адрес phpinfo.php: 					g740.config.urlPhpInfo
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
		g740.request={
			session: '',
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
				if (this.session) para.message+='session="'+this.session+'" ';
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
					if (g740.xml.isAttr(xmlResponse,'session')) this.session=g740.xml.getAttrValue(xmlResponse,'session','');
					
					var lst=g740.xml.findArrayOfChild(xmlResponse, {nodeName:'response'});
					
					var isDisconnected=false;
					var lstErr=[];
					for (var i=0; i<lst.length; i++) {
						var xmlItem=lst[i];
						if (!g740.xml.isXmlNode(xmlItem)) continue;
						var name=g740.xml.getAttrValue(xmlItem,'name','');
						if (!name) name=g740.xml.getAttrValue(xmlItem,'response','');
						var mess=g740.xml.getAttrValue(xmlItem,'message','');
						if (name=='error' || name=='disconnected') {
							if (name=='disconnected') isDisconnected=true;
							lstErr.push(xmlItem);
							if (mess) {
								if (errMessage) errMessage+="\n";
								errMessage+=mess;
							}
						}
						else {
							if (mess) {
								if (message) message+="\n";
								message+=mess;
							}
						}
					}
					if (lstErr.length>0) {
						lst=lstErr;
						result=false;
						message='';
					}
					if (!isDisconnected) {
						var lstResponseExec=[];
						for (var i=0; i<lst.length; i++) {
							var xmlItem=lst[i];
							if (!g740.xml.isXmlNode(xmlItem)) continue;

							var responseExec=g740.xml.getAttrValue(xmlItem,'exec','');
							if (responseExec) lstResponseExec.push(responseExec);
							var lstXmlExec=g740.xml.findArrayOfChild(xmlItem, {nodeName:'exec'});
							for (var execIndex=0; execIndex<lstXmlExec.length; execIndex++) {
								var xmlExecItem=lstXmlExec[execIndex];
								var responseExec=g740.xml.getAttrValue(xmlExecItem,'exec','');
								if (!responseExec) responseExec=g740.xml.getAttrValue(xmlExecItem,'name','');
								if (responseExec) lstResponseExec.push(responseExec);
							}
							
							var name=g740.xml.getAttrValue(xmlItem,'name','');
							if (!name) name=g740.xml.getAttrValue(xmlItem,'response','');
							if (name=='exec') continue;
							
							if (para.objOwner.doResponse) {
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
								if (!para.objOwner.doResponse(p)) result=false;
							}
						}
						// Если ответы содержат запросы exec то последовательно выполняем их
						if (para.objOwner.execByFullName) for (var i=0; i<lstResponseExec.length; i++) {
							if (!para.objOwner.execByFullName(lstResponseExec[i])) {
								result=false;
								break;
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
					if (errMessage) {
						g740.showError(errMessage, para.objOwner);
					}
					if (message) g740.showMessage(message);
					this._indexExecuted--;
					this._sendLastFiFo();
				}
				return result;
			},
			httpGet: function(url) {
				var hiddenIFrameID = 'hiddenDownloader';
				var iframe = document.getElementById(hiddenIFrameID);
				if (iframe === null) {
					iframe = document.createElement('iframe');
					iframe.id = hiddenIFrameID;
					iframe.style.display = 'none';
					document.body.appendChild(iframe);
				}
				iframe.src=url;
				console.log(iframe.src);
				return true;
			},
			httpOpen: function(url) {
				console.log(url);
				window.open(url);
				return true;
			}
		}
		return g740;
	}
);