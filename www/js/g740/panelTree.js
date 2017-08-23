//-----------------------------------------------------------------------------
// Панель Tree - самописная версия дерева
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
// Класс дерево
		dojo.declare(
			'g740.Tree',
			[g740._PanelAbstract, dijit._TemplatedMixin, dijit.layout._LayoutWidget],
			{
				isG740Tree: true,
				isG740CanToolBar: true,
				isG740CanButtons: true,
				templateString: '<div class="g740tree-panel">'+
					'<div data-dojo-attach-point="domNodeTitle"></div>'+
					'<div data-dojo-attach-point="domNodeToolbar"></div>'+
					'<input type="checkbox" class="g740-focused" data-dojo-attach-point="focusNode" data-dojo-attach-event="onkeypress: onKeyPress"></input>'+
					'<div class="g740tree-body" data-dojo-attach-point="domNodeBody">'+
					'</div>'+
					'<div data-dojo-attach-point="domNodeButtons"></div>'+
				'</div>',
				objTreeNodes: null,
				_objRowSet: null,
				_objNodes: null,
				_treeNodeFocused: null,
				objActionOnDblClick: null,
				objToolBar: null,
				objPanelButtons: null,
				isTreeMenuMode: false,
				set: function(name, value) {
					if (name=='focused' && value) {
						this.focusNode.focus();
						return true;
					}
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.Tree.constructor';
					this.objActionOnDblClick=para.objActionOnDblClick;
					this.objTreeNodes = new g740.TreeStorage();
				},
				destroy: function() {
					var procedureName='g740.Tree.destroy';
					if (this._treeParentNode) this._treeParentNode=null;
					if (this.objTreeNodes) {
						this.objTreeNodes.destroy();
						this.objTreeNodes = null;
					}
					if (this.objActionOnDblClick) {
						this.objActionOnDblClick.destroy();
						this.objActionOnDblClick=null;
					}
					if (this.objToolBar) {
						this.objToolBar.destroyRecursive();
						this.objToolBar=null;
					}
					if (this.objPanelButtons) {
						this.objPanelButtons.destroyRecursive();
						this.objPanelButtons=null;
					}
					if (this._objRowSet) this._objRowSet=null;
					if (this._objNodes) this._objNodes=null;
					if (this._treeNodeFocused) this._treeNodeFocused=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.domNode.title='';
					dojo.on(this.domNode, 'click', dojo.hitch(this, function(e){
						this.set('focused', true);
					}));
					this.inherited(arguments);
					this.domNodeTitle.innerHTML='';
					if (this.title && this.isShowTitle) {
						objDiv=document.createElement('div');
						objDiv.className='g74-paneltitle';
						var objText=document.createTextNode(this.title);
						objDiv.appendChild(objText);
						this.domNodeTitle.appendChild(objDiv);
					}
				},
				layout: function() {
					var h=this.domNode.offsetHeight-this.domNodeTitle.offsetHeight-this.domNodeToolbar.offsetHeight-this.domNodeButtons.offsetHeight;
					this.domNodeBody.style.height=h+'px';
					if (this.objPanelButtons) this.objPanelButtons.resize();
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
				getRowSet: function() {
					if (!this._objRowSet) this._objRowSet=this.inherited(arguments);
					return this._objRowSet;
				},
				getObjNodes: function() {
					if (!this._objNodes) {
						var objRowSet=this.getRowSet();
						if (objRowSet) this._objNodes=objRowSet.objTreeStorage;
					}
					return this._objNodes;
				},
				focus: function() {
					this.set('focused',true);
				},
				// Синхронизировать содержимое источника данных и дерева
				doSync: function(treeNode, node) {
					var objNodes=this.getObjNodes();
					if (!objNodes) return false;
					if (node.nodeType!='root') this._doUpdateTreeNode(treeNode, node);
		
					var firstNode=null;
					if (treeNode.childs) firstNode=treeNode.childs.firstNode;
					// Удаляем несуществующие дочерние элементы
					var lstRemove=[];
					for(var treeNodeChild=this.objTreeNodes.getFirstChildNode(treeNode); treeNodeChild; treeNodeChild=this.objTreeNodes.getNextNode(treeNodeChild)) {
						if (!treeNodeChild.info || !treeNodeChild.info.node) {
							lstRemove.push(treeNodeChild);
							continue;
						}
						var id=treeNodeChild.id;
						var nodeChild=objNodes.getNode(id, node);
						
						if (!nodeChild) {
							lstRemove.push(treeNodeChild);
							continue;
						}
						if (treeNodeChild.info.node!=nodeChild) {
							lstRemove.push(treeNodeChild);
							continue;
						}
					}
					for(var i=0; i<lstRemove.length; i++) {
						var treeNodeChild=lstRemove[i];
						this._doRemoveTreeNode(treeNodeChild);
					}


					
					// Добавляем новые дочерние элементы
					for(var nodeChild=objNodes.getFirstChildNode(node); nodeChild; nodeChild=objNodes.getNextNode(nodeChild)) {
						var id=nodeChild.id;
						var treeNodeChild=this.objTreeNodes.getNode(id, treeNode);
						if (!treeNodeChild) {
							treeNodeChild=this.objTreeNodes.appendNode(id,treeNode);
						}
						this.doSync(treeNodeChild,nodeChild);
					}
					
					// Сортируем дочерние элементы
					for(var nodeChild=objNodes.getFirstChildNode(node); nodeChild; nodeChild=objNodes.getNextNode(nodeChild)) {
						var id=nodeChild.id;
						var nodeChildPrev=objNodes.getPrevNode(nodeChild);
						var idPrev=nodeChildPrev?nodeChildPrev.id:null;
						
						var treeNodeChild=this.objTreeNodes.getNode(id, treeNode);
						var treeNodeChildPrev=this.objTreeNodes.getPrevNode(treeNodeChild);
						var idTreePrev=treeNodeChildPrev?treeNodeChildPrev.id:null;
						if (idPrev!=idTreePrev) {
							var treeNodeChildPrev=this.objTreeNodes.getNode(idPrev);
							this.objTreeNodes.cutNode(treeNodeChild);
							this.objTreeNodes.pasteNode(treeNodeChild, treeNode, treeNodeChildPrev);
							var domItemNext=null;
							var domItemParent=treeNodeChild.info.domItem.parentNode;
							if (treeNodeChildPrev) {
								domItemNext=treeNodeChildPrev.info.domItem.nextSibling;
							}
							domItemParent.insertBefore(treeNodeChild.info.domItem, domItemNext);
						}
					}
				},
				_doUpdateTreeNode: function(treeNode, node) {
					if (!treeNode.info) treeNode.info={};
					var info=treeNode.info;
					info.node=node;
					if (!info.domItem) {
						var domItem=document.createElement('div');
						domItem.className='g740tree-item';
						var domItemElement=document.createElement('div');
						domItemElement.className='g740tree-item-element';
						var domItemChilds=document.createElement('div');
						domItemChilds.className='g740tree-item-childs';
						domItem.appendChild(domItemElement);
						domItem.appendChild(domItemChilds);
						info.domItem=domItem;
						info.domItemElement=domItemElement;
						info.domItemChilds=domItemChilds;
						domItemElement.treeNode=treeNode;
						dojo.on(domItemElement, 'click', dojo.hitch(this, function(e){
							var d=e.target;
							var treeNode=null;
							while(d) {
								if (d.treeNode) {
									treeNode=d.treeNode;
									break;
								}
								d=d.parentNode;
								if (d==this.domNode) break;
							}
							if (treeNode) this.doNodeClick(treeNode);
						}));
						dojo.on(domItemElement, 'dblclick', dojo.hitch(this, function(e){
							var d=e.target;
							var treeNode=null;
							while(d) {
								if (d.treeNode) {
									treeNode=d.treeNode;
									break;
								}
								d=d.parentNode;
								if (d==this.domNode) break;
							}
							if (treeNode) this.doNodeDblClick(treeNode);
						}));
						
						var domParent=this.domNodeBody;
						if (treeNode.parentNode && treeNode.parentNode.info && treeNode.parentNode.info.domItemChilds) {
							domParent=treeNode.parentNode.info.domItemChilds;
						}
						var domNext=null;
						if (treeNode.nextNode && treeNode.nextNode.info && treeNode.nextNode.info.domItem) domNext=treeNode.nextNode.info.domItem;
						if (domNext) {
							domParent.insertBefore(domItem, domNext);
						}
						else {
							domParent.appendChild(domItem);
						}
					}
					this.buildDomItem(treeNode);
				},
				_doRemoveTreeNode: function(treeNode) {
					if (treeNode.info) {
						if (treeNode.info.domItem) {
							treeNode.info.domItem.innerHTML='';
							if (treeNode.info.domItem.parentNode) treeNode.info.domItem.parentNode.removeChild(treeNode.info.domItem);
						}
						for(var name in treeNode.info) treeNode.info[name]=null;
						treeNode.info={};
					}
					this.objTreeNodes.removeNode(treeNode);
				},
				_doSetFocusedNode: function(treeNode) {
					if (this._treeNodeFocused==treeNode) return true;
					if (this._treeNodeFocused && this._treeNodeFocused.info && this._treeNodeFocused.info.domItemElement) {
						dojo.removeClass(this._treeNodeFocused.info.domItemElement, 'selected');
					}
					this._treeNodeFocused=treeNode;
					if (this._treeNodeFocused && this._treeNodeFocused.info && this._treeNodeFocused.info.domItemElement) {
						dojo.addClass(this._treeNodeFocused.info.domItemElement, 'selected');
						g740.execDelay.go({
							delay: 100,
							obj: this,
							func: this._doScrollToNode,
							para: this._treeNodeFocused
						});
					}
				},
				_doScrollToNode: function(treeNode) {
					if (!treeNode) return;
					if (!treeNode.info) return;
					if (!treeNode.info.domItemElement) return;
					var t=this.domNodeBody.scrollTop;
					var h=this.domNodeBody.offsetHeight*0.9;
					var domItemElement=treeNode.info.domItemElement;
					if (domItemElement.offsetTop<t) {
						this.domNodeBody.scrollTop=domItemElement.offsetTop;
					}
					else if ((domItemElement.offsetTop+domItemElement.offsetHeight)>(t+h)) {
						this.domNodeBody.scrollTop=domItemElement.offsetTop+domItemElement.offsetHeight-h;
					}
				},
				buildDomItem: function(treeNode) {
					if (!treeNode) return false;
					if (!treeNode.info) return false;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					var info=treeNode.info;
					
					var domItemElement=info.domItemElement;
					if (!domItemElement) return false;
					if (!info.domItemExpander) {
						var domExpander=document.createElement('div');
						domExpander.className='g740tree-item-expander';
						domItemElement.appendChild(domExpander);
						info.domItemExpander=domExpander;
						dojo.on(domExpander, 'click', dojo.hitch(this, function(e){
							var d=e.target;
							var treeNode=null;
							while(d) {
								if (d.treeNode) {
									treeNode=d.treeNode;
									break;
								}
								d=d.parentNode;
								if (d==this.domNode) break;
							}
							if (treeNode) this.doNodeExpandCollapse(treeNode);
						}));
					}
					if (!info.domItemIcon) {
						var domItemIcon=document.createElement('div');
						domItemIcon.className='g740tree-item-icon';
						domItemElement.appendChild(domItemIcon);
						info.domItemIcon=domItemIcon;
						dojo.on(domItemIcon, 'click', dojo.hitch(this, function(e){
							var d=e.target;
							var treeNode=null;
							while(d) {
								if (d.treeNode) {
									treeNode=d.treeNode;
									break;
								}
								d=d.parentNode;
								if (d==this.domNode) break;
							}
							if (treeNode) this.doNodeIconClick(treeNode);
						}));
					}
					if (!info.domItemText) {
						var domItemText=document.createElement('div');
						domItemText.className='g740tree-item-text';
						domItemElement.appendChild(domItemText);
						info.domItemText=domItemText;
					}
					var node=info.node;
					if (!info.node) return false;

					var label='';
					var description='';
					var icon=node.nodeType;
					var isFinal=node.isFinal?true:false;
					var isEmpty=node.isEmpty?true:false;
					
					var fieldNameLabel = 'name';
					var fieldNameDescription = 'name';
					var nt = objRowSet.getNt(node.nodeType);
					if (nt.name) fieldNameLabel = nt.name;
					if (nt.description) fieldNameDescription = nt.description;
					if (node.info) {
						if (nt.fields[fieldNameLabel]) label=node.info[fieldNameLabel + '.value'];
						if (nt.fields[fieldNameDescription]) description=node.info[fieldNameDescription + '.value'];
						if (node.info['row.icon']) icon=node.info['row.icon'];
					}
					if (objRowSet.getIsNodeMarked(node)) icon='mark';
					if (isFinal || isEmpty) {
						info.domItemExpander.className='g740tree-item-expander g740tree-item-expander-final';
					}
					else if (node.childs) {
						info.domItemExpander.className='g740tree-item-expander g740tree-item-expander-minus';
					}
					else {
						info.domItemExpander.className='g740tree-item-expander g740tree-item-expander-plus';
					}
					
					if (label!=info.label) {
						info.label=label;
						info.domItemText.innerHTML='';
						var ddd=document.createTextNode(info.label);
						info.domItemText.appendChild(ddd);
					}
					if (description!=info.description) {
						info.description=description;
						info.domItemText.title=info.description;
					}
					if (icon!=info.icon) {
						info.icon=icon;
						var iconClass=g740.icons.getIconClassName(info.icon);
						if (!iconClass) {
							if (isFinal || isEmpty) {
								iconClass=g740.icons.getIconClassName('default');
							}
							else {
								iconClass=g740.icons.getIconClassName('folder');
							}
						}
						info.domItemIcon.className='g740tree-item-icon '+iconClass;
					}
				},
				// Ищет последнюю согласованную пару
				getNodes: function(node) {
					var objNodes=this.getObjNodes();
					if (!objNodes) return null;
					var result={
						node: objNodes.rootNode,
						treeNode: this.objTreeNodes.rootNode
					};
					var path=objNodes.getNodePath(node);
					for (var i=1; i<path.length; i++) {
						var id=path[i];
						var treeNode=this.objTreeNodes.getNode(id,result.treeNode);
						if (!treeNode) break;
						var node=objNodes.getNode(id,result.node);
						if (treeNode.info && treeNode.info.node!=node) break;
						result.treeNode=treeNode;
						result.node=node;
					}
					return result;
				},
				doNodeClick: function(treeNode) {
					if (!treeNode) return;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return;
					var info=treeNode.info;
					if (!info) return;
					objRowSet.setFocusedNode(info.node);
				},
				doNodeExpandCollapse: function(treeNode) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return;
					var node=objRowSet.getFocusedNode();
					if (treeNode) {
						if (!treeNode.info) return false;
						if (!treeNode.info.node) return false;
						if (node!=treeNode.info.node) {
							objRowSet.setFocusedNode(treeNode.info.node);
							node=treeNode.info.node;
						}
					}
					if (!node) return false;
					if (node.isFinal || node.isEmpty) return;
					if (node.childs) {
						objRowSet.exec({
							requestName: 'collapse'
						});
					}
					else {
						objRowSet.exec({
							requestName: 'expand'
						});
					}
				},
				doNodeDblClick: function(treeNode) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					var node=objRowSet.getFocusedNode();
					if (treeNode) {
						if (!treeNode.info) return false;
						if (!treeNode.info.node) return false;
						if (node!=treeNode.info.node) {
							objRowSet.setFocusedNode(treeNode.info.node);
							node=treeNode.info.node;
						}
					}
					if (!node) return false;
					if (this.objActionOnDblClick) {
						this.objActionOnDblClick.exec();
					}
					else if (this.isTreeMenuMode) {
						var row=node.info;
						if (!row) return false;
						var nt=objRowSet.getNt(node.nodeType);
						
						var fieldName='form';
						if (nt.treemenuForm) fieldName=nt.treemenuForm;
						var p={};
						p.formName=row[fieldName+'.value'];
						if (p.formName) {
							var G740params={};
							var fieldName='params';
							if (nt.treemenuParams) fieldName=nt.treemenuParams;
							var prm=row[fieldName+'.value'];
							if (prm) {
								var lst=prm.split('\n');
								for (var i=0; i<lst.length; i++) {
									var prmItem=lst[i];
									if (!prmItem) continue;
									var n=prmItem.indexOf('=');
									if (n<0) continue;
									var name=prmItem.substr(0,n);
									if (!name) continue;
									var value=prmItem.substr(n+1,prmItem.length);
									G740params[name]=value;
								}
								p.G740params=G740params;
							}
							g740.application.doG740ShowForm(p);
						}
						else {
							this.doNodeExpandCollapse();
						}
					}
					else {
						this.doNodeExpandCollapse();
					}
				},
				doNodeIconClick: function(treeNode) {
					if (!treeNode) return;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return;
					var info=treeNode.info;
					if (!info) return;
					objRowSet.setFocusedNode(info.node);
					objRowSet.exec({requestName: 'mark'});
				},
				
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					if (!dojo.hasClass(this.domNode,'focused')) dojo.addClass(this.domNode,'focused');
					return true;
				},
				onG740Blur: function() {
					if (dojo.hasClass(this.domNode,'focused')) dojo.removeClass(this.domNode,'focused');
					return true;
				},
				onKeyPress: function(e) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return;
					if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==40) {
						// Dn
						var node=objRowSet.getFocusedNode();
						if (node) {
							if (node.childs && node.childs.firstNode) {
								objRowSet.setFocusedNode(node.childs.firstNode);
							}
							else if (node.nextNode) {
								objRowSet.setFocusedNode(node.nextNode);
							}
							else {
								for(var p=node.parentNode; p; p=p.parentNode) {
									if (p.nextNode) {
										objRowSet.setFocusedNode(p.nextNode);
										break;
									}
								}
							}
						}
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==38) {
						// Up
						var node=objRowSet.getFocusedNode();
						if (node) {
							if (node.prevNode) {
								var p=node.prevNode;
								while(true) {
									if (!p.childs) break;
									if (!p.childs.lastNode) break;
									p=p.childs.lastNode;
								}
								objRowSet.setFocusedNode(p);
							}
							else if (node.parentNode && node.parentNode.nodeType!='root') {
								objRowSet.setFocusedNode(node.parentNode);
							}
						}
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyChar=='+') {
						var node=objRowSet.getFocusedNode();
						if (node && !node.childs) {
							objRowSet.exec({
								requestName: 'expand'
							});
						}
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyChar=='-') {
						var node=objRowSet.getFocusedNode();
						if (node && node.childs) {
							objRowSet.exec({
								requestName: 'collapse'
							});
						}
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==9) {
						// Tab
						dojo.stopEvent(e);
						var objParent=this.getParent();
						if (objParent && objParent.doG740FocusChildNext) {
							objParent.doG740FocusChildNext(this);
						}
					}
					else if (!e.ctrlKey && e.shiftKey && e.keyCode==9) {
						// Shift+Tab
						dojo.stopEvent(e);
						var objParent=this.getParent();
						if (objParent && objParent.doG740FocusChildPrev) {
							objParent.doG740FocusChildPrev(this);
						}
					}
					else if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==13) {
						// Enter
						this.doNodeDblClick();
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==45) {
						// Ins
						objRowSet.exec({
							requestName: 'append'
						});
						dojo.stopEvent(e);
					}
					else if (e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==45) {
						// Ctrl+Ins
						objRowSet.exec({
							requestName: 'append',
							requestMode: 'into'
						});
						dojo.stopEvent(e);
					}
					else if (e.ctrlKey && !e.altKey && !e.shiftKey && e.keyCode==46) {
						// Ctrl+Del
						objRowSet.execConfirmDelete();
						dojo.stopEvent(e);
					}
					else {
						//console.log(e);
					}
				},
				canFocused: function() {
					return true;
				},
				
				doG740Repaint: function(para) {
					if (!para) return true;
					if (!para.objRowSet) return true;
					if (para.objRowSet.name!=this.rowsetName) return true;

					if (para.isFull && para.parentNode) {
						var nn=this.getNodes(para.parentNode);
						if (nn && nn.treeNode && nn.node) this.doSync(nn.treeNode, nn.node);
						
						var nodeFocused=para.objRowSet.getFocusedNode();
						var nn=this.getNodes(nodeFocused);
						if (nn && nn.node==nodeFocused) this._doSetFocusedNode(nn.treeNode);
					}
					else {
						if (para.isRowUpdate) {
							var node=para.node;
							if (!node) node=para.objRowSet.getFocusedNode();
							var nn=this.getNodes(node);
							if (nn && nn.node==node) {
								this._doUpdateTreeNode(nn.treeNode, nn.node);
							}
							else {
								if (nn && nn.treeNode && nn.node) this.doSync(nn.treeNode, nn.node);
							}
						}
						if (para.isNavigate) {
							var nodeFocused=para.objRowSet.getFocusedNode();
							var nn=this.getNodes(nodeFocused);
							if (nn && nn.node==nodeFocused) this._doSetFocusedNode(nn.treeNode);
						}
					}
				}
			}
		);

		g740.panels._builderPanelTree=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelTree';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			var panelType=g740.xml.getAttrValue(xml, 'type', '');
			if (!panelType)	panelType=g740.xml.getAttrValue(xml, 'panel', 'tree');
			
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (!para.rowsetName) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: panelType,
					messageId: 'errorRowSetNameEmpty'
				});
				return null;
			}
			var objRowSet=para.objForm.rowsets[para.rowsetName];
			if (!objRowSet) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: panelType,
					rowsetName: para.rowsetName,
					messageId: 'errorRowSetNotFoundInForm'
				});
				return null;
			}
			
			var xmlRequests=g740.xml.findFirstOfChild(xml,{nodeName:'requests'});
			if (!g740.xml.isXmlNode(xmlRequests)) xmlRequests=xml;
			var lst=g740.xml.findArrayOfChild(xmlRequests,{nodeName:'request'});
			for(var i=0; i<lst.length; i++) {
				var xmlRequest=lst[i];
				var t=g740.xml.getAttrValue(xmlRequest,'on','dblclick');
				if (t!='dblclick') continue;
				var request={
					sync: true,
					params: {}
				}
				g740.panels.buildRequestParams(xmlRequest, request);
				if (!para.objActionOnDblClick) {
					var p={
						objForm: para.objForm
					};
					para.objActionOnDblClick=new g740.Action(p);
				}
				para.objActionOnDblClick.request=request;
			}

			if (panelType=='treemenu') para.isTreeMenuMode=true;
			var objTree=new g740.Tree(para, null);
			var result=objTree;
			return result;
		};
		g740.panels.registrate('tree', g740.panels._builderPanelTree);
		g740.panels.registrate('treemenu', g740.panels._builderPanelTree);


// Виджет: дерево с пометкой листьев
		dojo.declare(
			'g740.TreeCheckBox',
			[g740.Tree],
			{
				_nodeTypes: null,
				_nodeTypesCount: 0,
				
				_itemsChecked: null,
				_value: '',
				
				constructor: function(para, domElement) {
					this._nodeTypes={};
					this._nodeTypesCount=0;
					this._itemsChecked={};
					if (para.nodeTypes) {
						var nt=para.nodeTypes;
						if (typeof(nt)=='string') {
							nt=nt.split(';');
						}
						if (Array.isArray(nt)) {
							for(var i=0; i<nt.length; i++) {
								var s=nt[i];
								if (!s) continue;
								if (this._nodeTypes[s]) continue;
								this._nodeTypes[s]=true;
								this._nodeTypesCount++;
							}
						}
					}
					this._setValue(para.value);
				},
				destroy: function() {
					this._nodeTypes={};
					this._itemsChecked={};
					this.inherited(arguments);
				},
				clearChecked: function() {
					this._itemsChecked={};
					this._value='-';
					var objNodes=this.getObjNodes();
					if (!objNodes) return;
					
					this.doG740Repaint({isFull: true, parentNode:objNodes.rootNode});
				},
				_setValue: function(newValue) {
					this._itemsChecked={};
					if (!newValue) newValue='';
					if (!newValue.toString) newValue='';
					newValue=newValue.toString();
					var lst=newValue.split("\n");
					for(var i=0; i<lst.length; i++) {
						var item=lst[i];
						var n=item.indexOf('=');
						if (n<0) continue;
						var id=item.substr(0,n);
						var value=item.substr(n+1,999);
						if (!value) value='--//--';
						this._itemsChecked[id]=value;
					}
					this._value=newValue;
				},
				getValue: function() {
					if (this._value!='-') return this._value;
					var result='';
					var lst=[];
					for(var id in this._itemsChecked) {
						if (!id) continue;
						lst.push(id);
					}
					
					for(var i=0; i<lst.length; i++) {
						var id=lst[i];
						if (result) result+="\n";
						result+=id+'='+this._itemsChecked[id];
					}
					return result;
				},

				buildDomItem: function(treeNode) {
					if (!treeNode) return false;
					if (!treeNode.info) return false;
					var info=treeNode.info;
					
					var domItemElement=info.domItemElement;
					if (!domItemElement) return false;
					if (!info.domItemExpander) {
						var domExpander=document.createElement('div');
						domExpander.className='g740tree-item-expander';
						domItemElement.appendChild(domExpander);
						info.domItemExpander=domExpander;
						dojo.on(domExpander, 'click', dojo.hitch(this, function(e){
							var d=e.target;
							var treeNode=null;
							while(d) {
								if (d.treeNode) {
									treeNode=d.treeNode;
									break;
								}
								d=d.parentNode;
								if (d==this.domNode) break;
							}
							if (treeNode) this.doNodeExpandCollapse(treeNode);
						}));
					}
					if (!info.domItemIcon) {
						var domItemIcon=document.createElement('div');
						domItemIcon.className='g740tree-item-icon';
						domItemElement.appendChild(domItemIcon);
						info.domItemIcon=domItemIcon;
						dojo.on(domItemIcon, 'click', dojo.hitch(this, function(e){
							var d=e.target;
							var treeNode=null;
							while(d) {
								if (d.treeNode) {
									treeNode=d.treeNode;
									break;
								}
								d=d.parentNode;
								if (d==this.domNode) break;
							}
							if (treeNode) this.doNodeCheck(treeNode);
						}));
					}
					if (!info.domItemText) {
						var domItemText=document.createElement('div');
						domItemText.className='g740tree-item-text';
						domItemElement.appendChild(domItemText);
						info.domItemText=domItemText;
					}
					var node=info.node;
					if (!info.node) return false;

					var label='';
					var description='';
					var icon=node.nodeType;
					var isFinal=node.isFinal?true:false;
					var isEmpty=node.isEmpty?true:false;

					var objRowSet=this.getRowSet();
					var fieldNameLabel = 'name';
					var fieldNameDescription = 'name';
					var nt = objRowSet.getNt(node.nodeType);
					if (nt.name) fieldNameLabel = nt.name;
					if (nt.description) fieldNameDescription = nt.description;
					if (node.info) {
						if (nt.fields[fieldNameLabel]) label=node.info[fieldNameLabel + '.value'];
						if (nt.fields[fieldNameDescription]) description=node.info[fieldNameDescription + '.value'];
						if (node.info['row.icon']) icon=node.info['row.icon'];
						if (this.getIsNodeCheckable(node)) {
							icon=this.getIsNodeChecked(node)?'check-on':'check-off';
						}
					}
					
					if (isFinal || isEmpty) {
						info.domItemExpander.className='g740tree-item-expander g740tree-item-expander-final';
					}
					else if (node.childs) {
						info.domItemExpander.className='g740tree-item-expander g740tree-item-expander-minus';
					}
					else {
						info.domItemExpander.className='g740tree-item-expander g740tree-item-expander-plus';
					}
					
					if (label!=info.label) {
						info.label=label;
						info.domItemText.innerHTML='';
						var ddd=document.createTextNode(info.label);
						info.domItemText.appendChild(ddd);
					}
					if (description!=info.description) {
						info.description=description;
						info.domItemText.title=info.description;
					}
					if (icon!=info.icon) {
						info.icon=icon;
						var iconClass=g740.icons.getIconClassName(info.icon);
						if (!iconClass) {
							if (isFinal || isEmpty) {
								iconClass=g740.icons.getIconClassName('default');
							}
							else {
								iconClass=g740.icons.getIconClassName('folder');
							}
						}
						info.domItemIcon.className='g740tree-item-icon '+iconClass;
					}
				},
				getIsNodeCheckable: function(node) {
					if (!node) return false;
					if (!node.info) return false;
					if (this._nodeTypesCount==0) return true;
					return (this._nodeTypes[node.nodeType])?true:false;
				},
				getIsNodeChecked: function(node) {
					if (!this.getIsNodeCheckable(node)) return false;
					var id=node.info['id'];
					var nodeType=node.nodeType;
					if (node.nodeType && this._itemsChecked[node.nodeType+'.'+id]) return true;
					if (this._itemsChecked[id]) return true;
					return false;
				},
				doNodeCheck: function(treeNode) {
					if (!treeNode) return;
					if (!treeNode.info) return;
					var node=treeNode.info.node;
					if (!node) return;
					if (!this.getIsNodeCheckable(node)) return;
					var id=treeNode.id;
					
					if (this.getIsNodeChecked(node)) {
						delete(this._itemsChecked[id]);
						if (node.nodeType) delete(this._itemsChecked[node.nodeType+'.'+id]);
					}
					else {
						if (node.nodeType) {
							this._itemsChecked[node.nodeType+'.'+id]=treeNode.info.label;
						}
						else {
							this._itemsChecked[id]=treeNode.info.label;;
						}
					}
					this._doUpdateTreeNode(treeNode, node);
					this._value='-';
				}
			}
		);


	}
);