//-----------------------------------------------------------------------------
// Панель Tree
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};

// Класс дерево
		dojo.declare(
			'g740.Tree',
			[g740._PanelAbstract, dijit.Tree],
			{
				isG740Tree: true,
				objRowSet: null,
				objForm: null,
				isTreeMenuMode: false,
				isAutoHidePanel: false,
				objActionOnDblClick: null,
				constructor: function(para, domElement) {
					var procedureName='g740.Tree.constructor';
					this.objActionOnDblClick=para.objActionOnDblClick;
					if (para.objRowSet) {
						this.objRowSet=para.objRowSet;
						this.objForm=this.objRowSet.objForm;
						this.rowsetName=this.objRowSet.name;
					} else {
						this.objForm=para.objForm;
						this.rowsetName=para.rowsetName;
						if (this.objForm && this.objForm.rowsets) this.objRowSet=this.objForm.rowsets[this.rowsetName];
					}
					this.color=para.color;
					if (para.isTreeMenuMode) this.isTreeMenuMode=para.isTreeMenuMode;
					if (this.objRowSet) {
						this.set('model',this.objRowSet.objTreeModelApi);
					}
					this.on('Click', this.onG740Click);
					this.on('Open', this.onG740Open);
					this.on('Close', this.onG740Close);
					this.on('DblClick', this.onG740DblClick);
					this.on('Focus', this.onG740Focus);
					//console.log(this);
				},
				destroy: function() {
					var procedureName='g740.Tree.destroy';
					if (this.objActionOnDblClick) {
						this.objActionOnDblClick.destroy();
						this.objActionOnDblClick=null;
					}
					this.objRowSet=null;
					this.objForm=null;
					this.set('model',null);
					this.inherited(arguments);
				},
				postCreate: function() {
					dojo.addClass(this.domNode,'g740-tree');
					if (this.color) {
						var colorItem=g740.colorScheme.getColorItem(this.color);
						var className=colorItem.className;
						if (!dojo.hasClass(this.domNode, className)) dojo.addClass(this.domNode, className);
					}
					this.inherited(arguments);
					this.own(
						dojo.aspect.after(this.model, 'onIdChange', dojo.lang.hitch(this, '_onItemIdChange'), true)
					);
				},
				getIconClass: function(node, isExpanded) {
					var result=this.inherited(arguments);
					if (!this.objRowSet) return result;
					if (this.objRowSet.isObjectDestroed) return result;
					if (!this.objRowSet.objTreeStorage) return result;
					if (this.objRowSet.objTreeStorage.isObjectDestroed) return result;
					if (!this.objRowSet.objTreeStorage.isNode(node)) return result;
/*
					if (node==this.objRowSet.getMarkNode()) {
						result=g740.icons.getIconClassName('mark');
					} else 
*/
					if (node.info && node.info['row.icon']) {
						result=g740.icons.getIconClassName(node.info['row.icon']);
					}
					else {
						if (node.nodeType) {
							var iconClass=g740.icons.getIconClassName(node.nodeType);
							if (iconClass) result=iconClass;
						}
					}
					return result;
				},
				
				getRowClass: function(node, isExpanded) {
					var result=this.inherited(arguments);
					if (!this.objRowSet) return result;
					if (this.objRowSet.isObjectDestroed) return result;
					if (!this.objRowSet.objTreeStorage) return result;
					if (this.objRowSet.objTreeStorage.isObjectDestroed) return result;
					if (!this.objRowSet.objTreeStorage.isNode(node)) return result;
					var row = node.info;
					if (!row) return result;
					if (row['row.mark'] || node==this.objRowSet.getMarkNode()) {
					    result = result += ' g740-mark';
					}
					else {
					    var color = row['row.color'];
					    var fieldName = 'name';
					    var nt = this.objRowSet.getNt(node.nodeType);
					    if (nt.name) fieldName = nt.name;
					    if (row[fieldName + '.color']) color = row[fieldName + '.color'];
					    if (color) {
					        var colorItem = g740.colorScheme.getColorItem(color);
					        if (colorItem) result += ' ' + colorItem.className;
					    }
					}
					return result;
				},
				
				getTooltip: function(node) {
					var result='';
					if (!node) return '';
					var row=node.info;
					if (!row) return '';
					var fieldName='description';
					var nt=this.objRowSet.getNt(node.nodeType);
					if (nt.description) fieldName=nt.description;
					if (row[fieldName+'.value']) return row[fieldName+'.value'];
					return '';
				},
				getTreeNode: function(node) {
					var id=this.model.getIdentity(node);
					var lst=this._itemNodesMap[id];
					for (var i=0; i<lst.length; i++) {
						var treeNode=lst[i];
						if (!treeNode) continue;
						if (treeNode.item==node) return treeNode;
					}
					return null;
				},
				
				_onItemIdChange: function(node, oldId) {
					var newId=this.model.getIdentity(node);
					if (this._itemNodesMap[oldId] && !this._itemNodesMap[newId]) {
						this._itemNodesMap[newId]=this._itemNodesMap[oldId];
						delete this._itemNodesMap[oldId];
					}
				},
				_onItemChange: function(node) {
					var treeNode=this.getTreeNode(node);
					if (treeNode) {
						treeNode.set('item', node);
						treeNode.set('label', this.getLabel(node));
						treeNode.set('tooltip', this.getTooltip(node));
/*
						var nodeIsExpanded=!node.isEmpty && !node.isFinal;
						if (nodeIsExpanded!=treeNode.isExpanded) {
							treeNode.isExpanded=nodeIsExpanded;
							treeNode._setExpando(false);
						}
*/
						treeNode._updateItemClasses(node);
					}
				},
				_onItemChildrenChange: function(parentNode, newChildrenList) {
					var treeNode=this.getTreeNode(parentNode);
					if (parentNode && treeNode) {
						if (newChildrenList.length==0 && treeNode.isExpanded) {
							var ret = treeNode.collapse();
							this._state(treeNode, false);
							this._startPaint(ret);
							//dojo.domClass.remove(treeNode.expandoNode, 'dijitTreeExpandoOpened');
							//dojo.domClass.add(treeNode.expandoNode, 'dijitTreeExpandoClosed');
							//dijitInline dijitTreeExpando dijitTreeExpandoOpened
							//dijitInline dijitTreeExpando dijitTreeExpandoLeaf
						}
						treeNode.setChildItems(newChildrenList);
						if (newChildrenList.length==0) {
							if (!parentNode.isEmpty && !parentNode.isFinal) {
								treeNode.makeExpandable();
							}
							else {
								dojo.domClass.remove(treeNode.expandoNode, 'dijitTreeExpandoOpened');
								dojo.domClass.remove(treeNode.expandoNode, 'dijitTreeExpandoClosed');
								dojo.domClass.add(treeNode.expandoNode, 'dijitTreeExpandoLeaf');
							}
						}
						if (newChildrenList.length>0 && !treeNode.isExpanded) treeNode.expand();
					}
				},
				doG740Repaint: function(para) {
					var procedureName='g740.Tree.doG740Repaint';
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
			
					this.doG740setFocusedNode();

					g740.execDelay.go({
						delay: 50,
						obj: this,
						func: this.doG740ScrollToFocusedNode
					});
 				},
				doG740setFocusedNode: function() {
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					
					var p=[];
					for(var node=this.objRowSet.getFocusedNode(); node!=null; node=node.parentNode) {
						p.push(node);
					}
					
					var path=[];
					for (var i=p.length-1; i>=0; i--) {
						var node=p[i];
						var id=this.model.getIdentity(node);
						path.push(id);
					}
					this.set('path',path);
					return true;
				},
				doG740Focus: function() {
					var objParent=this.getParent();
					if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
					this.focus();
				},
				doG740ScrollToFocusedNode: function() {
					if (!this.domNode) return false;
					if (!this.selectedNode) return false;
					var domItem=this.selectedNode.domNode;
					if (!domItem) return false;
					var y=this.domNode.scrollTop;
					var h=this.domNode.offsetHeight;
					var delta=50;
					if (delta*4>h) delta=h/4;
					
					if (domItem.offsetTop<(y+delta)) {
						y=parseInt(domItem.offsetTop-h/2);
					}
					if ((domItem.offsetTop+domItem.offsetHeight)>(y+h-delta)) {
						y=parseInt(domItem.offsetTop-h/2);
					}
					if (y<0) y=0;
					this.domNode.scrollTop=y;
				},
				onG740Click: function(node, objNode, evt) {
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					if (!this.objForm) return false;
					if (this.objForm.getFocusedRowSet()!=this.objRowSet) return false;
					this.objRowSet.setFocusedNode(node);
				},
				onG740Open: function(node, objNode) {
					var procedureName='g740.Tree.onG740Open';
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					if (!node) return false;
					if (node.isEmpty || node.isFinal) return false;
					if (node.childs) return true;
					if (node!=this.objRowSet.getFocusedNode()) {
						if (!this.objRowSet.setFocusedNode(node)) return false;
					}
					this.objRowSet.exec({requestName:'expand'});
				},
				onG740Close: function(node, objNode) {
					var procedureName='g740.Tree.onG740Close';
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					if (!node) return false;
					if (!node.childs) return true;
					if (node!=this.objRowSet.getFocusedNode()) {
						if (!this.objRowSet.setFocusedNode(node)) return false;
					}
					this.objRowSet.exec({requestName:'collapse'});
				},
				onG740DblClick: function(node, objNode, evt) {
					var procedureName='g740.Tree.onG740DblClick';
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					if (!node) return false;
					
					if (this.objActionOnDblClick) {
						this.objActionOnDblClick.exec();
						return true;
					}
					var isModeExpandCollapse=true;
					if (this.isTreeMenuMode) {
						var row=node.info;
						if (!row) return false;
						var nt=this.objRowSet.getNt(node.nodeType);
						
						var fieldName='form';
						if (nt.treemenuForm) fieldName=nt.treemenuForm;
						var p={};
						p.formName=row[fieldName+'.value'];
						if (p.formName) {
							isModeExpandCollapse=false;

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
							if (this.isAutoHidePanel) this.doG740PanelHide();
							g740.application.doG740ShowForm(p);
						}
					}
					if (isModeExpandCollapse) {
						if (node.childs && node.childs.firstNode) {
							this.objRowSet.exec({requestName:'collapse'});
						}
						else {
							this.objRowSet.exec({requestName:'expand'});
						}
					}
				},
				_isPanelHide: false,
				doG740PanelHide: function() {
					if (this._isPanelHide) return true;
					this._isPanelHide=true;
					var objPanel=this.getParent();
					if (!objPanel) return;
					var objParent=objPanel.getParent();
					if (!objParent) return;
					if (objPanel.region=='left' || objPanel.region=='right') objPanel.domNode.style.width='6%';
					if (objPanel.region=='top' || objPanel.region=='bottom') objPanel.domNode.style.height='6%';
					objParent.layout();
				},
				doG740PanelShow: function() {
					if (!this._isPanelHide) return true;
					this._isPanelHide=false;
					var objPanel=this.getParent();
					if (!objPanel) return;
					var objParent=objPanel.getParent();
					if (!objParent) return;
					if (objPanel.region=='left' || objPanel.region=='right') {
						var w=objPanel.width;
						if (!w) w='25%';
						objPanel.domNode.style.width=w;
					}
					if (objPanel.region=='top' || objPanel.region=='bottom') {
						var h=objPanel.height;
						if (!h) h='25%';
						objPanel.domNode.style.height=h;
					}
					objParent.layout();
				},

				_onContainerKeydown: function(e){
					if (e && e.type=='keydown' && this.objRowSet && !this.objRowSet.isObjectDestroed) {
						if (e.keyCode==27) {
							// Esc
							this.objRowSet.undoUnsavedChanges();
							dojo.stopEvent(e);
							return true;
						}
						if (e.ctrlKey && e.keyCode==46) {
							// Ctrl+Del
							this.objRowSet.execConfirmDelete();
							dojo.stopEvent(e);
							return true;
						}
						if (e.keyCode==45) {
							// Ins, Ctrl+Ins
							this.objRowSet.exec({requestName: 'append'});
							dojo.stopEvent(e);
							return true;
						}
						if (!e.ctrlKey && e.keyCode==113) {
							// F2
							this.objRowSet.exec({requestName: 'save'});
							dojo.stopEvent(e);
							return true;
						}
						if (!e.ctrlKey && !e.shiftKey && e.keyCode==9) {
							// Tab
							dojo.stopEvent(e);
							var objParent=this.getParent();
							if (objParent && objParent.doG740FocusChildNext) objParent.doG740FocusChildNext();
						}
						if (!e.ctrlKey && e.shiftKey && e.keyCode==9) {
							// Shift+Tab
							dojo.stopEvent(e);
							var objParent=this.getParent();
							if (objParent && objParent.doG740FocusChildPrev) objParent.doG740FocusChildPrev();
						}
					}
					this.inherited(arguments);
				},

				onG740Focus: function() {
					if (this.objForm) {
						if (!this.objForm.onG740ChangeFocusedPanel(this)) {
							g740.execDelay.go({
								delay:50,
								obj: this,
								func: this.doG740setFocusedNode
							});
						}
					}
					dojo.addClass(this.domNode, 'g740-tree-focused');
					if (this.isTreeMenuMode && this.isAutoHidePanel) this.doG740PanelShow();
					return true;
				},
				onG740Blur: function() {
					dojo.removeClass(this.domNode, 'g740-tree-focused');
					if (this.isTreeMenuMode && this.isAutoHidePanel) {
						g740.execDelay.go({
							delay: 100,
							obj: this,
							func: this.doG740PanelHide
						});
					}
					return true;
				},
				canFocused: function() {
					return true;
				}
			}
		);

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
					this.set('showRoot',false);
					this.set('persist',false);
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
					for(var id in this._itemNodesMap) {
						var lst=this._itemNodesMap[id];
						if (!lst) continue;
						for(var i=0; i<lst.length; i++) {
							var treeNode=lst[i];
							if (!treeNode) continue;
							treeNode._updateItemClasses(treeNode.item);
						}
					}
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
				getIconClass: function(node, isExpanded) {
					var result=this.inherited(arguments);
					if (this.getIsNodeCheckable(node)) {
						if (this.getIsNodeChecked(node)) {
							return 'g740-grid-check-on';
						}
						else {
							return 'g740-grid-check-off';
						}
					}
					return result;
				},
				onG740Click: function(node, objNode, evt) {
					var dom=evt.target;
					if (!this.objRowSet) return false;
					if (this.objRowSet.isObjectDestroed) return false;
					this.objRowSet.setFocusedNode(node);
					if (dom && dom.nodeName=='SPAN' && dojo.hasClass(dom,'dijitIcon')) {
						if (this.getIsNodeCheckable(node)) {
							var id=node.info['id'];
							if (this.getIsNodeChecked(node)) {
								delete(this._itemsChecked[id]);
								if (node.nodeType) delete(this._itemsChecked[node.nodeType+'.'+id]);
							}
							else {
								if (node.nodeType) {
									this._itemsChecked[node.nodeType+'.'+id]=this.getLabel(node);
								}
								else {
									this._itemsChecked[id]=this.getLabel(node);
								}
							}
							this._onItemChange(node);
							this._value='-';
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
/*			
			if (g740.xml.isAttr(xml,'ondblclick')) {
				if (!para.objActionOnDblClick) {
					var p={
						objForm: para.objForm
					};
					para.objActionOnDblClick=new g740.Action(p);
				}
				para.objActionOnDblClick.request={
					sync: true,
					params: {},
					exec: g740.xml.getAttrValue(xml,'ondblclick','')
				};
			}
*/
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

			
			var isFocusOnShow=para.isFocusOnShow;
			para.isFocusOnShow=false;
			var objPanel=new g740.Panel(para, null);
			
			var isAutoHidePanel=false;
			if (panelType=='treemenu') isAutoHidePanel=g740.xml.getAttrValue(xml, 'collapse', '0')=='1';
			
			var objTree=new g740.Tree({
				objForm: para.objForm,
				rowsetName: para.rowsetName,
				objActionOnDblClick: para.objActionOnDblClick,
				color: para.color,
				region: 'center',
				isTreeMenuMode: (panelType=='treemenu'),
				showRoot: false,
				persist: false,
				isFocusOnShow: isFocusOnShow,
				isAutoHidePanel: isAutoHidePanel
			}, null);
			objPanel.addChild(objTree);
			if (panelType!='treemenu') objPanel.isG740AutoMenu=true;
			var result=objPanel;
			return result;
		};
		g740.panels.registrate('tree', g740.panels._builderPanelTree);
		g740.panels.registrate('treemenu', g740.panels._builderPanelTree);
		return g740;
	}
);