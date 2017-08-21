//-----------------------------------------------------------------------------
// Виджеты для использования в панелях
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		

// Предок модального диалога, стандартный диалог dojo иногда зависает Internet Explorer 11
		dojo.declare(
			'g740.Dialog',
			dijit._Widget,
			{
				width: '50%',
				height: '50%',
				title: '',
				closable: true,
				lockscreenopacity: '0.7',
				hideonlockscreenclick: false,
				
				objBody: null,
				objTitle: null,

				domNodeDialog: null,
				domNodeLockScreen: null,
				domNodeCloseButton: null,
				domNodeCloseButton: null,
				
				signalWindowOnResize: null,
				signalCloseButtonClick: null,
				
				constructor: function(para) {
				},
				destroy: function() {
					if (this.signalWindowOnResize) {
						this.signalWindowOnResize.remove();
						this.signalWindowOnResize=null;
					}
					if (this.signalCloseButtonClick) {
						this.signalCloseButtonClick.remove();
						this.signalCloseButtonClick=null;
					}
					
					if (this.objTitle) {
						this.objTitle.destroyRecursive();
						this.objTitle=null;
					}
					if (this.objBody) {
						this.objBody.destroyRecursive();
						this.objBody=null;
					}
					if (this.domNodeCloseButton) {
						if (this.domNodeCloseButton.parentNode) this.domNodeCloseButton.parentNode.removeChild(this.domNodeCloseButton);
						this.domNodeCloseButton=null;
					}
					if (this.domNode) {
						if (this.domNode.parentNode) this.domNode.parentNode.removeChild(this.domNode);
						this.domNode=null;
					}
					if (this.domNodeLockScreen) {
						if (this.domNodeLockScreen.parentNode) this.domNodeLockScreen.parentNode.removeChild(this.domNodeLockScreen);
						this.domNodeLockScreen=null;
					}
					if (this.domNodeDialog) {
						if (this.domNodeDialog.parentNode) this.domNodeDialog.parentNode.removeChild(this.domNodeDialog);
						this.domNodeDialog=null;
					}
				},
				set: function(name, value) {
					if (name=='width') {
						this.width=value;
						this.doG740Resize();
						return true;
					}
					if (name=='height') {
						this.height=value;
						this.doG740Resize();
						return true;
					}
					if (name=='title') {
						this.title=value;
						this._refreshTitleVisible();
					}
					if (name=='closable') {
						this.closable=value;
						this._refreshTitleVisible();
					}
					if (name=='lockscreenopacity') {
						this.lockscreenopacity=value;
						if (this.domNodeLockScreen) dojo.style(this.domNodeLockScreen, 'opacity', this.lockscreenopacity);
					}
					this.inherited(arguments);
				},
				buildRendering: function() {
					this.domNodeDialog=document.createElement('div');
					this.domNodeDialog.className='g740-dialog';
					
					this.domNodeLockScreen=document.createElement('div');
					this.domNodeLockScreen.className='g740-dialog-lockscreen';
					dojo.style(this.domNodeLockScreen, 'opacity', this.lockscreenopacity);
					if (this.hideonlockscreenclick) {
						dojo.on(this.domNodeLockScreen, 'click', dojo.hitch(this, this.hide));
					}
					
					this.domNodeDialog.appendChild(this.domNodeLockScreen);

					this.domNode=document.createElement('div');
					this.domNode.className='g740-dialog-body';
					this.domNodeDialog.appendChild(this.domNode);
					
					this.objBody=new dijit.layout.BorderContainer(
						{
							design: 'headline',
							style: 'background-color: white'
						}, 
						this.domNode
					);
					
					this.objTitle=new g740.PanelTitle({
						title: this.title,
						region: 'top',
						'class': 'g740-dialog-title'
					}, null);
					
					this.domNodeCloseButton=document.createElement('span');
					this.domNodeCloseButton.className='dijitDialogCloseIcon';
					this.domNodeCloseButton.role='button';
					this.domNodeCloseButton.style='z-index:1';
					this._refreshTitleVisible();
					
					
					this.signalWindowOnResize=dojo.on(window, 'resize', dojo.hitch(this, this.doG740Resize));
					this.signalCloseButtonClick=dojo.on(this.domNodeCloseButton, 'click', dojo.hitch(this, this.hide));
				},
				addChild: function(obj) {
					if (!this.objBody) this.buildRendering();
					this.objBody.addChild(obj);
				},
				show: function() {
					if (!this.domNodeDialog) this.buildRendering();
					document.body.appendChild(this.domNodeDialog);
					this.domNode.title='';
					this.doG740Resize();
					this.onG740Show();
				},
				hide: function() {
					if (!this.domNodeDialog) return false;
					if (!this.canHide()) return false;
					if (this.domNodeDialog.parentNode) this.domNodeDialog.parentNode.removeChild(this.domNodeDialog);
					this.onG740Hide();
					return true;
				},
				
				_refreshTitleVisible: function() {
					if (this.objTitle) {
						this.objTitle.set('title', this.title);
						if (this.title && !this.objTitle.domNode.parentNode) {
							this.objBody.addChild(this.objTitle);
						}
						if (!this.title && this.objTitle.domNode.parentNode) {
							this.objBody.removeChild(this.objTitle);
						}
						if (this.closable && this.title) {
							if (!this.domNodeCloseButton.parentNode) this.domNode.appendChild(this.domNodeCloseButton);
						}
						else {
							if (this.domNodeCloseButton.parentNode) this.domNode.removeChild(this.domNodeCloseButton);
						}
					}
				},
				doG740Resize: function() {
					if (!this.domNode) return false;
					var size=this.getSize();
					this.objBody.resize(size);
					return true;
				},
				getSize: function() {
					var posScreen=dojo.geom.position(this.domNodeLockScreen, false);
					var w=this.width+'';
					if (w.indexOf('%')>=0) {
						w=Math.round(posScreen.w*parseFloat(w)/100);
					}
					w=parseInt(w);
					
					var h=this.height+'';
					if (h.indexOf('%')>=0) {
						h=Math.round(posScreen.h*parseFloat(h)/100);
					}
					h=parseInt(h);

					var result={
						w: w,
						h: h,
						l: Math.round((posScreen.w-w)/2),
						t: Math.round((posScreen.h-h)/2)
					}
					return result;
				},
				canHide: function() {
					return true;
				},
				onG740Show: function() {
				},
				onG740Hide: function() {
				}
			}
		);
		
// Контейнер модальной экранной формы
		dojo.declare(
			'g740.DialogModalForm',
			g740.Dialog,
			{
				isObjectDestroed: false,				// Признак - объект уничтожен
				attr: {},
				set: function(name, value) {
					if (name=='attr') {
						this.attr=value;
						return true;
					}
					this.inherited(arguments);
				},
				destroy: function() {
					var lst=g740.application.lstModalFormDialogs;
					if (lst.length>0 && lst[lst.length-1]==this) lst.pop();
					
					g740.application.modalResults={};
					var objForm=this.getObjForm();
					if (objForm) {
						for(var name in objForm.modalResults) {
							var value=objForm.modalResults[name];
							g740.application.modalResults[name]=value;
						}
						this._execOnAfterClose();
					}
					var objParentForm=this.attr.objForm;
					this.attr={};
					this.inherited(arguments);
					
					if (objParentForm && objParentForm.objFocusedPanel && objParentForm.objFocusedPanel.doG740Focus) {
						objParentForm.objFocusedPanel.doG740Focus();
					}
				},
				show: function() {
					this.inherited(arguments);
					g740.application.lstModalFormDialogs.push(this);
					this.doG740Resize();
				},
				_execOnAfterClose: function() {
					if (!this.attr || !this.attr.objForm || !this.attr.onafterclose) return true;
					var objForm=this.attr.objForm;
					for(var i=0; i<this.attr.onafterclose.length; i++) {
						var rr=this.attr.onafterclose[i];
						var obj=objForm;
						if (rr.rowset) {
							if (objForm.rowsets[rr.rowset]) obj=objForm.rowsets[rr.rowset];
						}
						if (!obj.getRequestEnabled(rr.name, rr.mode)) continue;
						var p=obj._getRequestG740params(rr.params);
						obj.exec({
							requestName: rr.name,
							requestMode: rr.mode,
							sync: true,
							G740params: p
						});
					}
					return true;
				},
				canHide: function() {
					var objForm=this.getObjForm();
					if (!objForm) return true;
					if (objForm.isObjectDestroed) return true;
					var r=objForm.requests['onclose'];
					if (!r) return true;
					var result=objForm.execByFullName('onclose');
					return result;
				},
				onG740Hide: function() {
					this.destroyRecursive();
				},
				getObjForm: function() {
					var lst=this.objBody.getChildren();
					
					for(var i=0; i<lst.length; i++) {
						var obj=lst[i];
						if (obj.g740className=='g740.Form' && !obj.isObjectDestroed) return obj;
					}
					return null;
				}
			}
		);
		
// Диалог редактора: абстрактный предок
		dojo.declare(
			'g740.DialogEditorAbstract',
			g740.Dialog,
			{
				g740className: 'g740.DialogEditor',		// Имя базового класса
				lockscreenopacity: '0.1',
				hideonlockscreenclick: true,
				isObjectDestroed: false,				// Признак - объект уничтожен
				objForm: null,							// Ссылка на экранную форму
				rowsetName: null,						// Ссылка на имя набора строк
				fieldName: null,						// Имя поля
				nodeType: '',							// Тип узла
				domNodeOwner: null,						// dom узел, относительно которого размещать диалог
				objOwner: null,							// dojo объект, из которого вызван диалог
				fieldDef: null,
				filter: '',
				_isSaveOnHide: false,
				_saveValueOnHide: null,
				_oldObjDialogEditor: null,
				set: function(name, value) {
					if (name=='objForm') {
						this.objForm=value;
						if (this.objForm) {
							if (this.objForm.objDialogEditor) this._oldObjDialogEditor=this.objForm.objDialogEditor;
							this.objForm.objDialogEditor=this;
						}
						return true;
					}
					if (name=='rowsetName') {
						this.rowsetName=value;
						return true;
					}
					if (name=='fieldName') {
						this.fieldName=value;
						return true;
					}
					if (name=='nodeType') {
						this.nodeType=value;
						return true;
					}
					if (name=='domNodeOwner') {
						this.domNodeOwner=value;
						return true;
					}
					if (name=='objOwner') {
						this.objOwner=value;
						return true;
					}
					if (name=='fieldDef') {
						this.fieldDef=value;
						return true;
					}
					this.inherited(arguments);
				},
				destroy: function() {
					var procedureName='g740.DialogEditorAbstract.destroy';
					try {
						if (this.objForm) this.objForm.objDialogEditor=this._oldObjDialogEditor;
						this._oldObjDialogEditor=null;
						this.objForm=null;
						this.domNodeOwner=null;
						this.objOwner=null;
						this.isObjectDestroed=true;
						this.inherited(arguments);
					}
					finally {
					}
				},
				postCreate: function() {
					this.build();
					this.inherited(arguments);
				},
				getSize: function() {
					var result=this.inherited(arguments);
					if (this.domNodeOwner && this.objForm && this.objForm.domNode) {
						var posOwner=dojo.geom.position(this.domNodeOwner, false);
						var posBody={
							x: 0,
							y: 0,
							w: document.body.clientWidth,
							h: document.body.clientHeight
						};
						result.l=posOwner.x;
						if ((result.l+result.w)>posBody.w) {
							result.l=posOwner.x+posOwner.w-result.w;
						}
						result.t=posOwner.y+posOwner.h;
						if ((result.t+result.h)>posBody.h) {
							result.t=posOwner.y-result.h;
						}
						if (result.t<5) result.t=5;
					}
					return result;
				},
				// Абстрактный метод, переопределить!!!
				doG740Repaint: function(para) {
				},
				// Абстрактный метод, переопределить!!!
				build: function() {
				},
				saveAndHide: function (value) {
					this._isSaveOnHide=true;
					this._saveValueOnHide=value;
					this.hide();
				},
				// Абстрактный метод, переопределить!!!
				_save: function(value) {
				},
				getRowSet: function() {
					if (!this.objForm) return null;
					var objRowSet=this.objForm.rowsets[this.rowsetName];
					return objRowSet;
				},
				// Абстрактный метод, переопределить!!!
				onG740Hide: function() {
					if (this._isSaveOnHide) {
						this._save(this._saveValueOnHide);
						this._saveValueOnHide=undefined;
						this._isSaveOnHide=false;
					}
					if (this.objOwner) {
						if (this.objOwner.doG740Focus) {
							this.objOwner.doG740Focus();
						}
						else {
							this.objOwner.set('focused',true);
						}
					}
					this.destroyRecursive();
				}
			}
		);
		
// Диалог редактора: справочник
		dojo.declare(
			'g740.DialogEditorRef',
			g740.DialogEditorAbstract,
			{
				objListRowSet: null,
				destroy: function() {
					var procedureName='g740.DialogEditorRef.destroy';
					try {
						this.objListRowSet=null;
						this.inherited(arguments);
					}
					finally {
					}
				},
				build: function() {
					var procedureName='g740.DialogEditorRef.build';
					var objRowSet=this.getRowSet();
					var objRefRowSet=this.getRefRowSet();
					var refIdFieldName=this.getRefIdFieldName();
					if (!objRowSet) return null;
					if (!objRefRowSet) return null;
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var fld=fields[this.fieldName];
					var fldRefId=fields[refIdFieldName];
					if (!fldRefId) return null;
					
					var refIdValue=objRowSet.getFieldProperty({fieldName: refIdFieldName});
					objRefRowSet.exec({
						requestName: 'refresh',
						sync: true
					});
					this.objListRowSet=new g740.ListRowSet(
						{
							objRowSet: objRefRowSet,
							value: refIdValue,
							fieldName: this.getRefNameFieldName(),
							isAddEmptyItem: !fldRefId.notnull,
							filter: this.filter,
							style: 'margin:0px; padding: 0px; border-style: none;',
							region: 'center'
						}, 
						null
					);
					var w=350+'px';
					if (fldRefId.dlgwidth) w=fldRefId.dlgwidth;
					if (fld.dlgwidth) w=fld.dlgwidth;
					if (this.domNodeOwner && this.domNodeOwner.clientWidth && !fld.dlgwidth && !fldRefId.dlgwidth) {
						w=this.domNodeOwner.clientWidth;
						if (w>(document.body.clientWidth*0.6)) w=parseInt(document.body.clientWidth*0.6);
						if (w<350) w=350;
						w+='px';
					}
					this.width=w;
					this.height=this.objListRowSet.getHeight()+'px';
					
					this.objListRowSet.on('KeyDown',dojo.lang.hitch(this,this.onListKeyDown));
					this.objListRowSet.on('DblClick',dojo.lang.hitch(this,this.onListDblClick));
					this.objListRowSet.on('Resize',dojo.lang.hitch(this,this.onListResize));
					this.addChild(this.objListRowSet);
				},

				_save: function(value) {
					var objRowSet=this.getRowSet();
					var objRefRowSet=this.getRefRowSet();
					var refIdFieldName=this.getRefIdFieldName();
					if (!objRowSet) return null;
					if (!objRefRowSet) return null;
					var isReadOnly=objRowSet.getFieldProperty({
						fieldName: refIdFieldName,
						propertyName: 'readonly'
					});
					if (!isReadOnly) {
						var row=null;
						var node=objRowSet.getFocusedNode();
						if (node) row=node.info;
						if (row) {
							var refRow={};
							var refNode=objRefRowSet.objTreeStorage.getNode(value);
							if (refNode) refRow=refNode.info;
							var fields=objRowSet.getFieldsByNodeType(this.nodeType);
							for(var fieldName in fields) {
								var fld=fields[fieldName];
								if (!fld) continue;
								if (!fld.refid) continue;
								if (fld.refid!=refIdFieldName) continue;
								var refname='name';
								if (fld.refname) refname=fld.refname;
								var v=refRow[refname+'.value'];
								if (typeof(v)=='undefined') v='';
								row[fieldName+'.value']=v;
							}
							objRowSet.doG740Repaint({ isRowUpdate: true });
							objRowSet.setFieldProperty({
								fieldName: refIdFieldName,
								value: value
							});
						}
					}
				},
				getRefIdFieldName: function() {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var fld=fields[this.fieldName];
					if (!fld) return null;
					if (!fld.refid) return null;
					if (!fields[fld.refid]) return null;
					return fld.refid;
				},
				getRefNameFieldName: function() {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var fld=fields[this.fieldName];
					if (!fld) return null;
					if (fld.reftext) return fld.reftext;
					if (fld.refname) return fld.refname;
					return 'name';
				},
				getRefRowSet: function() {
					if (!this.objForm) return null;
					var refIdFieldName=this.getRefIdFieldName();
					if (!refIdFieldName) return null;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var refRowSetName=objRowSet.getRefRowSetName(refIdFieldName, this.nodeType);
					return this.objForm.rowsets[refRowSetName];
				},
				getSize: function() {
					var result=this.inherited(arguments);
					if (this.domNodeOwner && this.objForm && this.objForm.domNode) {
						var posOwner=dojo.geom.position(this.domNodeOwner, false);
						var posBody={
							x: 0,
							y: 0,
							w: document.body.clientWidth,
							h: document.body.clientHeight
						};
						result.l=posOwner.x;
						if ((result.l+result.w)>posBody.w) {
							result.l=posOwner.x+posOwner.w-result.w;
						}
						result.t=posOwner.y;
						if ((result.t+result.h)>posBody.h) {
							result.t=posOwner.y-result.h;
						}
						if (result.t<5) result.t=5;
					}
					return result;
				},
				doG740Resize: function() {
					this.inherited(arguments);
					this.objListRowSet.layout();
				},
				onG740Show: function() {
					if (this.objListRowSet) {
						this.objListRowSet.set('focused',true);
					}
				},
				onListKeyDown: function(e) {
					if (e.keyCode==27) {
						dojo.stopEvent(e);
						if (this.hide) this.hide();
					}
					if (e.keyCode==9) {
						dojo.stopEvent(e);
					}
				},
				onListDblClick: function(e) {
					if (this.saveAndHide) this.saveAndHide(this.objListRowSet.value);
				},
				onListResize: function() {
					this.height=this.objListRowSet.getHeight()+'px';
					this.doG740Resize();
				}
			}
		);

// Диалог редактора: список list
		dojo.declare(
			'g740.DialogEditorList',
			g740.DialogEditorAbstract,
			{
				objListItems: null,
				charHeight: 18,
				destroy: function() {
					var procedureName='g740.DialogEditorList.destroy';
					try {
						this.objListItems=null;
						this.inherited(arguments);
					}
					finally {
					}
				},
				build: function() {
					var procedureName='g740.DialogEditorList.build';
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					if (this.titleBar && this.titleBar.parentNode) this.titleBar.parentNode.removeChild(this.titleBar);
					var fieldDef=this.fieldDef;
					if (!fieldDef) {
						var fields=objRowSet.getFieldsByNodeType(this.nodeType);
						var fieldDef=fields[this.fieldName];
					}
					
					var baseType='string';
					if (fieldDef.basetype=='num') baseType=fieldDef.basetype;
					this.objListItems=new g740.ListItems(
						{
							list: fieldDef.list,
							value: objRowSet.getFieldProperty({fieldName: this.fieldName}),
							baseType: baseType,
							isAddEmptyItem: !fieldDef.notnull,
							filter: this.filter,
							style: 'margin:0px; padding: 0px; border-style: none;',
							region: 'center'
						}, 
						null
					);

					var w=200+'px';
					if (fieldDef.dlgwidth) w=fieldDef.dlgwidth;
					if (this.domNodeOwner && this.domNodeOwner.clientWidth && !fieldDef.dlgwidth) {
						w=this.domNodeOwner.clientWidth;
						if (w>(document.body.clientWidth*0.6)) w=parseInt(document.body.clientWidth*0.6);
						if (w<200) w=200;
						w+='px';
					}
					this.width=w;
					this.height=this.objListItems.getHeight()+'px';
					
					this.objListItems.on('KeyDown',dojo.lang.hitch(this,this.onListKeyDown));
					this.objListItems.on('DblClick',dojo.lang.hitch(this,this.onListDblClick));
					this.objListItems.on('Resize',dojo.lang.hitch(this,this.onListResize));
					this.addChild(this.objListItems);
				},
				onG740Show: function() {
					if (this.objListItems) {
						this.objListItems.set('focused',true);
					}
				},
				_save: function(value) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					objRowSet.setFieldProperty({
						fieldName: this.fieldName,
						value: value
					});
					objRowSet.doG740Repaint({ isRowUpdate: true });
				},
				getSize: function() {
					var result=this.inherited(arguments);
					if (this.domNodeOwner && this.objForm && this.objForm.domNode) {
						var posOwner=dojo.geom.position(this.domNodeOwner, false);
						var posBody={
							x: 0,
							y: 0,
							w: document.body.clientWidth,
							h: document.body.clientHeight
						};
						result.l=posOwner.x;
						if ((result.l+result.w)>posBody.w) {
							result.l=posOwner.x+posOwner.w-result.w;
						}
						result.t=posOwner.y;
						if ((result.t+result.h)>posBody.h) {
							result.t=posOwner.y-result.h;
						}
						if (result.t<5) result.t=5;
					}
					return result;
				},
				doG740Resize: function() {
					this.inherited(arguments);
					this.objListItems.layout();
				},
				onListKeyDown: function(e) {
					if (e.keyCode==32 || e.keyCode==13) {
						dojo.stopEvent(e);
						if (this.saveAndHide) this.saveAndHide(this.objListItems.value);
					}
					if (e.keyCode==27) {
						dojo.stopEvent(e);
						if (this.hide) this.hide();
					}
				},
				onListDblClick: function(e) {
					if (this.saveAndHide) this.saveAndHide(this.objListItems.value);
				},
				onListResize: function() {
					this.height=this.objListItems.getHeight()+'px';
					this.doG740Resize();
				}
			}
		);

// Диалог редактора: reflist
		dojo.declare(
			'g740.DialogEditorRefList',
			g740.DialogEditorAbstract,
			{
				objListCheckBox: null,
				charHeight: 18,
				_isSaveOnHide: true,
				destroy: function() {
					var procedureName='g740.DialogEditorRef.destroy';
					try {
						this.objListCheckBox=null;
						this.inherited(arguments);
					}
					finally {
					}
				},
				build: function() {
					var objRowSet=this.getRowSet();
					var objRefRowSet=this.getRefRowSet();
					if (!objRowSet) return null;
					if (!objRefRowSet) return null;
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var fld=fields[this.fieldName];
					if (!fld) return false;
					
					objRefRowSet.exec({
						requestName: 'refresh',
						sync: true
					});

					var lst=objRefRowSet.objTreeStorage.getChildsOrdered(objRefRowSet.objTreeStorage.rootNode);
					var count=lst.length;
					if (count<5) count=5;
					if (count>25) count=25;
					var h=(30+count*this.charHeight+5)+'px';
					
					var w=350+'px';
					if (fld.dlgwidth) w=fld.dlgwidth;
					if (this.domNodeOwner && this.domNodeOwner.clientWidth && !fld.dlgwidth) {
						w=this.domNodeOwner.clientWidth;
						if (w>(document.body.clientWidth*0.6)) w=parseInt(document.body.clientWidth*0.6);
						if (w<350) w=350;
						w+='px';
					}
					this.width=w;
					this.height=h;
					
					var reftext='name';
					if (fld.refname) reftext=fld.refname;
					if (fld.reftext) reftext=fld.reftext;
					this.objListCheckBox=new g740.ListCheckBox(
						{
							objRowSet: objRefRowSet,
							fieldName: reftext,
							value: objRowSet.getFieldProperty({fieldName: this.fieldName}),
							style: 'margin:2px',
							region: 'center'
						}, 
						null
					);
					this.addChild(this.objListCheckBox);

					var objPanelBottom=new dijit.layout.BorderContainer(
						{
							design: 'headline',
							region: 'bottom',
							style: 'height: 30px'
						},
						null
					);
					this.addChild(objPanelBottom);
						
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageBtnCancel'),
							onClick: dojo.hitch(this, this.onBtnCancelClick),
							iconClass: g740.icons.getIconClassName('cancel')
						},
						null
					);
					objPanelBottom.addChild(objBtn);
					
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageBtnOk'),
							onClick: dojo.hitch(this, this.onBtnOkClick),
							iconClass: g740.icons.getIconClassName('ok')
						},
						null
					);
					objPanelBottom.addChild(objBtn);

					var objBtn=new g740.PanelButton(
						{
							region: 'left',
							label: g740.getMessage('messageBrnClear'),
							onClick: dojo.hitch(this, this.onBtnClearClick),
							iconClass: g740.icons.getIconClassName('clear')
						},
						null
					);
					objPanelBottom.addChild(objBtn);
				},
				_save: function(value) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var isReadOnly=objRowSet.getFieldProperty({
						fieldName: this.fieldName,
						propertyName: 'readonly'
					});
					if (!isReadOnly) {
						var value='';
						if (this.objListCheckBox) value=this.objListCheckBox.getValue();
						objRowSet.setFieldProperty({
							fieldName: this.fieldName,
							value: value
						});
					}
				},
				getRefRowSet: function() {
					if (!this.objForm) return null;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var refRowSetName=objRowSet.getRefRowSetName(this.fieldName, this.nodeType);
					return this.objForm.rowsets[refRowSetName];
				},
				onG740Show: function() {
					if (this.objListCheckBox) this.objListCheckBox.focus();
				},
				getSize: function() {
					var result=this.inherited(arguments);
					if (this.domNodeOwner && this.objForm && this.objForm.domNode) {
						var posOwner=dojo.geom.position(this.domNodeOwner, false);
						var posBody={
							x: 0,
							y: 0,
							w: document.body.clientWidth,
							h: document.body.clientHeight
						};
						result.l=posOwner.x;
						if ((result.l+result.w)>posBody.w) {
							result.l=posOwner.x+posOwner.w-result.w;
						}
						result.t=posOwner.y;
						if ((result.t+result.h)>posBody.h) {
							result.t=posOwner.y-result.h;
						}
						if (result.t<5) result.t=5;
					}
					return result;
				},
				onBtnOkClick: function() {
					this.hide();
				},
				onBtnCancelClick: function() {
					this._isSaveOnHide=false;
					this.hide();
				},
				onBtnClearClick: function() {
					if (this.objListCheckBox) this.objListCheckBox.setValue('');
				}
			}
		);
		
// Диалог редактора: reftree
		dojo.declare(
			'g740.DialogEditorRefTree',
			g740.DialogEditorAbstract,
			{
				objTreeCheckBox: null,
				charHeight: 18,
				_isSaveOnHide: true,
				destroy: function() {
					var procedureName='g740.DialogEditorRef.destroy';
					try {
						this.objTreeCheckBox=null;
						this.inherited(arguments);
					}
					finally {
					}
				},
				build: function() {
					var objRowSet=this.getRowSet();
					var objRefRowSet=this.getRefRowSet();
					if (!objRowSet) return null;
					if (!objRefRowSet) return null;
					if (objRefRowSet.isRef) {
						objRefRowSet.isTree=true;
						objRefRowSet.isRef=false;
						objRefRowSet.isReadOnly=true;
						objRefRowSet.isRefTree=true;
						var arrayOfRequest=[];
						var message='<request name="definition" rowset="'+objRefRowSet.name+'" datasource="'+objRefRowSet.datasource+'"/>';
						arrayOfRequest.push(message);
						var para={
							arrayOfRequest: arrayOfRequest,
							objOwner: objRefRowSet,
							requestName: 'definition',
							sync: true
						};
						g740.request.send(para);
					}
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var fld=fields[this.fieldName];
					if (!fld) return false;

					var h=(20*this.charHeight+5)+'px';
					var w=350+'px';
					if (fld.dlgwidth) w=fld.dlgwidth;
					if (this.domNodeOwner && this.domNodeOwner.clientWidth && !fld.dlgwidth) {
						w=this.domNodeOwner.clientWidth;
						if (w>(document.body.clientWidth*0.6)) w=parseInt(document.body.clientWidth*0.6);
						if (w<350) w=350;
						w+='px';
					}
					this.width=w;
					this.height=h;
					
					objRefRowSet.exec({
						requestName: 'refresh',
						sync: true
					});
					var reftext='name';
					if (fld.refname) reftext=fld.refname;
					if (fld.reftext) reftext=fld.reftext;
					
					this.objTreeCheckBox=new g740.TreeCheckBox(
						{
							objForm: this.objForm,
							rowsetName: objRefRowSet.name,
							fieldName: reftext,
							nodeTypes: fld.refnodes,
							value: objRowSet.getFieldProperty({fieldName: this.fieldName}),
							style: 'margin:2px',
							region: 'center'
						},
						null
					);
					this.addChild(this.objTreeCheckBox);

					var objPanelBottom=new dijit.layout.BorderContainer(
						{
							design: 'headline',
							region: 'bottom',
							style: 'height: 30px'
						},
						null
					);
					this.addChild(objPanelBottom);
						
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageBtnCancel'),
							onClick: dojo.hitch(this, this.onBtnCancelClick),
							iconClass: g740.icons.getIconClassName('cancel')
						},
						null
					);
					objPanelBottom.addChild(objBtn);
					
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageBtnOk'),
							onClick: dojo.hitch(this, this.onBtnOkClick),
							iconClass: g740.icons.getIconClassName('ok')
						},
						null
					);
					objPanelBottom.addChild(objBtn);

					var objBtn=new g740.PanelButton(
						{
							region: 'left',
							label: g740.getMessage('messageBrnClear'),
							onClick: dojo.hitch(this, this.onBtnClearClick),
							iconClass: g740.icons.getIconClassName('clear')
						},
						null
					);
					objPanelBottom.addChild(objBtn);
					objRefRowSet.doG740Repaint({isFull: true, parentNode: objRefRowSet.objTreeStorage.rootNode});
				},
				doG740Repaint: function(para) {
					if (this.objTreeCheckBox && this.objTreeCheckBox.doG740Repaint) this.objTreeCheckBox.doG740Repaint(para);
				},
				_save: function(value) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var isReadOnly=objRowSet.getFieldProperty({
						fieldName: this.fieldName,
						propertyName: 'readonly'
					});
					if (!isReadOnly) {
						var value='';
						if (this.objTreeCheckBox) value=this.objTreeCheckBox.getValue();
						objRowSet.setFieldProperty({
							fieldName: this.fieldName,
							value: value
						});
					}
				},
				getRefRowSet: function() {
					if (!this.objForm) return null;
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var refRowSetName=objRowSet.getRefRowSetName(this.fieldName, this.nodeType);
					return this.objForm.rowsets[refRowSetName];
				},
				onG740Show: function() {
					if (this.objTreeCheckBox) this.objTreeCheckBox.focus();
				},
				getSize: function() {
					var result=this.inherited(arguments);
					if (this.domNodeOwner && this.objForm && this.objForm.domNode) {
						var posOwner=dojo.geom.position(this.domNodeOwner, false);
						var posBody={
							x: 0,
							y: 0,
							w: document.body.clientWidth,
							h: document.body.clientHeight
						};
						result.l=posOwner.x;
						if ((result.l+result.w)>posBody.w) {
							result.l=posOwner.x+posOwner.w-result.w;
						}
						result.t=posOwner.y;
						if ((result.t+result.h)>posBody.h) {
							result.t=posOwner.y-result.h;
						}
						if (result.t<5) result.t=5;
					}
					return result;
				},
				onBtnOkClick: function() {
					this.hide();
				},
				onBtnCancelClick: function() {
					this._isSaveOnHide=false;
					this.hide();
				},
				onBtnClearClick: function() {
					if (this.objTreeCheckBox) this.objTreeCheckBox.clearChecked();
				}
			}
		);
		
		
// Диалог редактора: memo поле
		dojo.declare(
			'g740.DialogEditorMemo',
			g740.DialogEditorAbstract,
			{
				objTextarea: null,
				objBtnOk: null,
				readOnly: false,
				set: function(name, value) {
					if (name=='readOnly') {
						this.setReadOnly(value);
						return true;
					}
					this.inherited(arguments);
				},
				setReadOnly: function(value) {
					this.readOnly=value;
					if (this.objTextarea) {
						this.objTextarea.set('readOnly',this.readOnly);
						
					}
					if (this.objBtnOk) this.objBtnOk.set('disabled',this.readOnly);
				},
				destroy: function() {
					var procedureName='g740.DialogEditorMemo.destroy';
					try {
						this.objTextarea=null;
						this.objBtnOk=null;
						this.inherited(arguments);
					}
					finally {
					}
				},
				build: function() {
					var procedureName='g740.DialogEditorMemo.build';
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					var fields=objRowSet.getFieldsByNodeType(this.nodeType);
					var fld=fields[this.fieldName];
					var caption=this.fieldName;
					if (fld && fld.caption) caption=fld.caption;
					this.set('title',caption);
					this.width='95%';
					this.height='95%';

					this.objTextarea=new dijit.form.SimpleTextarea(
						{
							style: 'margin:0px;padding:0px;margin-left:1px;border-color:gray',
							region: 'center'
						},
						null
					);
					this.objTextarea.set('value',objRowSet.getFieldProperty({fieldName: this.fieldName}));
					this.objTextarea.ownerObj=this;
					this.objTextarea.on('KeyDown', this.onTextAreaKeyDown);
					dojo.addClass(this.objTextarea.domNode, 'g740-textarea');
					this.addChild(this.objTextarea);
					
					var objPanelBottom=new dijit.layout.BorderContainer(
						{
							design: 'headline',
							region: 'bottom',
							style: 'height: 30px'
						},
						null
					);
					this.addChild(objPanelBottom);
					
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageDlgMemoCancel'),
							onClick: this.onBtnCancelClick,
							iconClass: g740.icons.getIconClassName('cancel')
						},
						null
					);
					objBtn.ownerObj=this;
					
					objPanelBottom.addChild(objBtn);
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageDlgMemoOk'),
							onClick: this.onBtnOkClick,
							iconClass: g740.icons.getIconClassName('ok')
						},
						null
					);
					objBtn.ownerObj=this;
					objPanelBottom.addChild(objBtn);
					this.objBtnOk=objBtn;
					
					this.setReadOnly(this.readOnly);
				},
				getSize: function() {
					var posScreen=dojo.geom.position(this.domNodeLockScreen, false);
					var w=this.width+'';
					if (w.indexOf('%')>=0) {
						w=Math.round(posScreen.w*parseFloat(w)/100);
					}
					w=parseInt(w);
					
					var h=this.height+'';
					if (h.indexOf('%')>=0) {
						h=Math.round(posScreen.h*parseFloat(h)/100);
					}
					h=parseInt(h);

					var result={
						w: w,
						h: h,
						l: Math.round((posScreen.w-w)/2),
						t: Math.round((posScreen.h-h)/2)
					}
					return result;
				},
				_save: function(value) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return null;
					objRowSet.setFieldProperty({
						fieldName: this.fieldName,
						value: value
					});
				},
				onG740Show: function() {
					if (this.objTextarea) this.objTextarea.focus();
				},
				onTextAreaKeyDown: function(e) {
					if (this.ownerObj) {
						if (e.keyCode==27) {
							if (this.ownerObj.hide) this.ownerObj.hide();
						}
					}
				},
				onBtnOkClick: function(e) {
					if (this.ownerObj && this.ownerObj.saveAndHide && this.ownerObj.objTextarea) {
						this.ownerObj.saveAndHide(this.ownerObj.objTextarea.get('value'));
					}
				},
				onBtnCancelClick: function(e) {
					if (this.ownerObj && this.ownerObj.hide) {
						this.ownerObj.hide();
					}
				},
				onBtnClearClick: function(e) {
					if (this.ownerObj && this.ownerObj.saveAndHide) {
						this.ownerObj.saveAndHide('');
					}
				}
			}
		);

// Диалог: запрос на подтверждение
		dojo.declare(
			'g740.DialogConfirm',
			g740.Dialog,
			{
				messageId: '',
				messageText: '',
				titleMessageId: 'messageDlgConfirmTitle',
				btnOkMessageId: 'messageBtnOk',
				btnOkIcon: 'ok',
				btnCancelMessageId: 'messageBtnCancel',
				btnCancelIcon: 'cancel',
				objBtnOk: null,
				objOwner: null,

				onCloseOk: null,
				onClolseCancel: null,
				closePara: null,
				closeObj: null,
				mode: 'confirm',
				width: '450px',
				height: '250px',

				set: function(name, value) {
					if (name=='messageText') {
						this.messageText=value;
						return true;
					}
					if (name=='messageId') {
						if (this.messageId==value) return true;
						if (!value || !g740._messages[value]) g740.error('errorIncorrectValue',value);
						this.messageId=value;
						return true;
					}
					if (name=='btnOkMessageId') {
						if (this.btnOkMessageId==value) return true;
						if (!value || !g740._messages[value]) g740.error('errorIncorrectValue',value);
						this.btnOkMessageId=value;
						return true;
					}
					if (name=='btnCancelMessageId') {
						if (this.btnCancelMessageId==value) return true;
						if (!value || !g740._messages[value]) g740.error('errorIncorrectValue',value);
						this.btnCancelMessageId=value;
						return true;
					}
					if (name=='btnOkIcon') {
						if (this.btnOkIcon==value) return true;
						if (!value) value='';
						if (value && !g740.icons._items[value]) g740.error('errorIncorrectValue',value);
						this.btnOkIcon=value;
						return true;
					}
					if (name=='btnCancelIcon') {
						if (this.btnCancelIcon==value) return true;
						if (!value) value='';
						if (value && !g740.icons._items[value]) g740.error('errorIncorrectValue',value);
						this.btnCancelIcon=value;
						return true;
					}
					if (name=='onCloseOk') {
						if (this.onCloseOk==value) return true;
						if (!value) value=null;
						if (value && typeof(value)!='function') g740.error('errorIncorrectTypeOfValue',value);
						this.onCloseOk=value;
						return true;
					}
					if (name=='onCloseCancel') {
						if (this.onCloseCancel==value) return true;
						if (!value) value=null;
						if (value && typeof(value)!='function') g740.error('errorIncorrectTypeOfValue',value);
						this.onCloseCancel=value;
						return true;
					}
					if (name=='closePara') {
						if (this.closePara==value) return true;
						if (!value) value=null;
						this.closePara=value;
						return true;
					}
					if (name=='closeObj') {
						if (this.closeObj==value) return true;
						if (!value) value=null;
						this.closeObj=value;
						return true;
					}
					if (name=='objOwner') {
						this.objOwner=value;
						return true;
					}
					if (name=='mode') {
						if (this.mode==value) return true;
						if (value!='confirm' && value!='message' && value!='error') g740.error('errorIncorrectValue',value);
						if (value=='confirm') this.titleMessageId='messageDlgConfirmTitle';
						if (value=='message') this.titleMessageId='messageDlgMessageTitle';
						if (value=='error') this.titleMessageId='messageDlgErrorTitle';
						this.set('title',g740.getMessage(this.titleMessageId));
						this.mode=value;
						return true;
					}
					this.inherited(arguments);
					return true;
				},
				constructor: function(para) {
					if (para['mode']) this.set('mode',para['mode']);
				},
				preamble: function(para, domElement) {
					if (para && para.style) {
						this.bodyStyle=para.style;
						delete para['style'];
					}
				},
				destroy: function() {
					var procedureName='g740.DialogConfirm.destroy';
					try {
						this.objBtnOk=null;
						this.objOwner=null;
					}
					finally {
					}
				},
				postCreate: function() {
					this.inherited(arguments);
					this.build();
				},
				build: function() {
					var objPanelBottom=new dijit.layout.BorderContainer(
						{
							design: 'headline',
							region: 'bottom',
							style: 'height: 30px'
						},
						null
					);
					this.addChild(objPanelBottom);

					var p={
						region: 'center',
						style: ''
					};
					if (this.mode=='error') {
						p['style']+='background-color: red; color: white';
					}
					var objPanelContent=new dijit.layout.ContentPane(p,null);
					this.addChild(objPanelContent);
					var lstDom=[];
					if (this.messageId) {
						var domText=document.createTextNode(g740.getMessage(this.messageId));
						lstDom.push(domText);
					}
					if (this.messageText) {
						var lst=this.messageText.split("\n");
						for (var i=0; i<lst.length; i++) {
							if (lstDom.length>0) {
								var domBr=document.createElement('br');
								lstDom.push(domBr);
							}
							var domText=document.createTextNode(lst[i]);
							lstDom.push(domText);
						}
					}
					objPanelContent.set('content',lstDom);
					objPanelContent.domNode.className='g740-dialogconfirm-content';
					
					if (this.mode=='confirm') {
						var objBtn=new g740.PanelButton(
							{
								region: 'right',
								label: g740.getMessage(this.btnCancelMessageId),
								onClick: dojo.hitch(this, this.onBtnCancelClick),
								onKeyDown: dojo.hitch(this, this.onKeyDown),
								iconClass: g740.icons.getIconClassName(this.btnCancelIcon)
							},
							null
						);
						objPanelBottom.addChild(objBtn);
					}
					
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage(this.btnOkMessageId),
							onClick: dojo.hitch(this, this.onBtnOkClick),
							onKeyDown: dojo.hitch(this, this.onKeyDown),
							iconClass: g740.icons.getIconClassName(this.btnOkIcon)
						},
						null
					);
					objPanelBottom.addChild(objBtn);
					this.objBtnOk=objBtn;
					this.set('title',g740.getMessage(this.titleMessageId));
				},
				_isOk: false,
				onBtnOkClick: function(e) {
					if (this.hide) {
						this._isOk=true;
						this.hide();
					}
				},
				onBtnCancelClick: function(e) {
					if (this.hide) {
						this.hide();
					}
				},
				onKeyDown: function(e) {
					if (e.keyCode==27 && this.hide) this.hide();
				},
				onG740Hide: function() {
					if (this.objOwner) {
						if (this.objOwner.doG740Focus) {
							this.objOwner.doG740Focus();
						}
						else {
							this.objOwner.set('focused',true);
						}
					}
					if (this._isOk) {
						if (this.onCloseOk) {
							g740.execDelay.go({
								delay: 50,
								obj: this.closeObj,
								func: this.onCloseOk,
								para: this.closePara
							});
						}
					}
					else {
						if (this.onCloseCancel) {
							g740.execDelay.go({
								delay: 50,
								obj: this.closeObj,
								func: this.onCloseCancel,
								para: this.closePara
							});
						}
					}
					this.destroyRecursive();
				},
				onG740Show: function() {
					if (this.objBtnOk) {
						g740.execDelay.go({
							delay: 200,
							obj: this,
							func: this._doBtnOkFocus
						});
					}
				},
				_doBtnOkFocus: function() {
					if (this.objBtnOk) this.objBtnOk.set('focused',true);
				}
			}
		);
		
// Диалог: запрос Login
		dojo.declare(
			'g740.DialogLogin',
			g740.Dialog,
			{
				objTextBoxLogin: null,
				objTextBoxPassword: null,
				isLoginOk: false,
				isObjectDestroed: false,
				postCreate: function() {
					this.render();
				},
				render: function() {
					var confDialogLogin=g740.config['dialogLogin'];
					var title=confDialogLogin.title;
					if (!title) title=g740.getMessage('messageLoginTitle');
					var w=confDialogLogin.width;
					var h=confDialogLogin.height;
					this.set('closable', false);
					this.set('title',title);
					this.set('width',w);
					this.set('height',h);
					
					var objPanelBottom=new dijit.layout.BorderContainer(
						{
							design: 'headline',
							region: 'bottom',
							style: 'height: 30px; background-color: white; border-width: 0px'
						},
						null
					);
					this.objBody.addChild(objPanelBottom);
					var objBtn=new g740.PanelButton(
						{
							region: 'right',
							label: g740.getMessage('messageBtnOk'),
							onClick: this.onBtnOkClick,
							iconClass: g740.icons.getIconClassName('ok')
						},
						null
					);
					objBtn.ownerObj=this;
					objPanelBottom.addChild(objBtn);

					var iconStyle='border-width: 0px; background-color: white; background-position: top; background-repeat: no-repeat; background-size: contain';
					if (confDialogLogin.iconWidth) iconStyle+='; width:'+confDialogLogin.iconWidth;
					if (confDialogLogin.iconUrl) iconStyle+="; background-image: url('"+confDialogLogin.iconUrl+"')";
					var objPanelIcon=new dijit.layout.ContentPane(
						{
							region: 'left',
							style: iconStyle
						}
						,null
					);
					this.objBody.addChild(objPanelIcon);
					if (!confDialogLogin.iconUrl) objPanelIcon.domNode.className='g740-icons-login';

					var objPanelContent=new dijit.layout.ContentPane({region: 'center',style: 'border-width: 0px; margin: 5px'},null);
					this.objBody.addChild(objPanelContent);

					var objTable=document.createElement('table');
					objTable.className='g740-dialoglogin-table';
					var objTBody=document.createElement('tbody');
					objTable.appendChild(objTBody);

					var objTr=document.createElement('tr');
					objTBody.appendChild(objTr);
					var objTd=document.createElement('td');
					var domText=document.createTextNode(g740.getMessage('messageLogin'));
					objTd.appendChild(domText);
					objTr.appendChild(objTd);
					var objTd=document.createElement('td');
					var objDomLogin=document.createElement('span');
					objTd.appendChild(objDomLogin);
					objTr.appendChild(objTd);

					var objTr=document.createElement('tr');
					objTBody.appendChild(objTr);
					var objTd=document.createElement('td');
					var domText=document.createTextNode(g740.getMessage('messagePassword'));
					objTd.appendChild(domText);
					objTr.appendChild(objTd);
					var objTd=document.createElement('td');
					var objDomPassword=document.createElement('span');
					objTd.appendChild(objDomPassword);
					objTr.appendChild(objTd);
					
					objPanelContent.set('content',objTable);
					
					this.objTextBoxLogin=new dijit.form.TextBox({style: 'width: 100%'},objDomLogin);
					this.objTextBoxLogin.ownerObj=this;
					this.objTextBoxPassword=new dijit.form.TextBox({style: 'width: 100%', type: 'password'},objDomPassword);
					this.objTextBoxPassword.ownerObj=this;
					this.objTextBoxLogin.on('KeyDown',this.onG740KeyDown);
					this.objTextBoxPassword.on('KeyDown',this.onG740KeyDown);
				},
				onBtnOkClick: function(e) {
					var obj=this;
					if (this.ownerObj) obj=this.ownerObj;
					var xmlRequest=g740.xml.createElement('request');
					xmlRequest.setAttribute('name', 'connect');
					var xmlParam=g740.xml.createElement('param');
					xmlParam.setAttribute('name', 'login');
					var xmlText=g740.xml.createTextNode(obj.objTextBoxLogin.get('value'));
					xmlParam.appendChild(xmlText);
					xmlRequest.appendChild(xmlParam);
					var xmlParam=g740.xml.createElement('param');
					xmlParam.setAttribute('name', 'password');
					var xmlText=g740.xml.createTextNode(obj.objTextBoxPassword.get('value'));
					xmlParam.appendChild(xmlText);
					xmlRequest.appendChild(xmlParam);
					g740.request.send(
						{
							arrayOfRequest: [xmlRequest],
							sync: false,
							objOwner: obj,
							requestName: 'connect'
						}
					);
				},
				doResponse: function(para) {
					var procedureName='g740.DialogLogin.doResponse';
					try {
						if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
						var xmlResponse=para.xmlResponse;
						if (!g740.xml.isXmlNode(xmlResponse)) g740.responseError('errorNotXml', 'para.xmlResponse');
						if (xmlResponse.nodeName!='response') g740.responseError('errorXmlNodeNotFound', 'response');
						var name=g740.xml.getAttrValue(xmlResponse,'name','');
						if (name=='ok') {
							this.isLoginOk=true;
							this.hide();
						}
					}
					finally {
					}
					return true;
				},
				onG740KeyDown: function(e) {
					if (!e.ctrlKey && e.keyCode==13) {
						var obj=this;
						if (this.ownerObj) obj=this.ownerObj;
						if (obj.onBtnOkClick) obj.onBtnOkClick();
						dojo.stopEvent(e);
					}
				},
				onG740Show: function() {
					if (this.objTextBoxLogin) this.objTextBoxLogin.focus();
				},
				onG740Hide: function() {
					if (!this.isLoginOk) {
						this.show();
					}
					else {
						this.destroyRecursive();
						g740.application.doG740ShowApplicationForm();
					}
				}
			}
		);
		return g740;
	}
);