//-----------------------------------------------------------------------------
// Панели Fields
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		dojo.declare(
			'g740.FieldsSimple',
			[g740._PanelAbstract, dijit._TemplatedMixin],
			{
				isG740Fields: true,
				isG740Clipboard: true,
				fields: [],
				_childs: [],
				title: '',
				padding: '2px',
				templateString: 
					'<div class="g740-fieldssimple">'+
					'<div data-dojo-attach-point="domNodeTitle"></div>'+
					'<div data-dojo-attach-point="domNodeDivTable">'+
					'<table class="g740-fieldssimple-table" data-dojo-attach-point="domNodeTable">'+
					'</table>'+
					'</div>'+
					'</div>',
				
				set: function(name, value) {
					if (name=='fields' && value) {
						this.fields=[];
						var objRowSet=this.getRowSet();
						if (!objRowSet) return false;
						if (!value) return false;
						if (typeof(value)!='object') return false;
						if (!value.length) return false;
						var rowsetFields=objRowSet.getFieldsByNodeType(this.nodeType);
						for(var i=0; i<value.length; i++) {
							var fldNew=value[i];
							if (!fldNew) continue;
							var fieldName=fldNew.name;
							var fldRowSet=rowsetFields[fieldName];
							if (!fldRowSet) continue;
							var fld={};
							for (var paramName in fldRowSet) fld[paramName]=fldRowSet[paramName];
							for (var paramName in fldNew) fld[paramName]=fldNew[paramName];
							this.fields.push(fld);
						}
						return true;
					}
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.FieldsSimple.constructor';
					this.fields={};
					this._childs=[];
					this.set('objForm',para.objForm);
					this.set('rowsetName',para.rowsetName);
					if (para.nodeType) this.set('nodeType', para.nodeType);
					this.set('fields', para.fields);
					
					this.on('Focus', this.onG740Focus);
					//console.log(this);
				},
				destroy: function() {
					var procedureName='g740.FieldsSimple.destroy';
					if (this.fields) {
						for(var i=0; i<this.fields.length; i++) this.fields[i]=null;
						this.fields=[];
					}
					if (this._childs) {
						for(var i=0; i<this._childs.length; i++) {
							var obj=this._childs[i];
							if (!obj) continue;
							obj.destroyRecursive();
							this._childs[i]=null;
						}
						this._childs=[];
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					this.domNode.title='';
					this.render();
				},
				render: function() {
					if (!this.domNodeTitle) return false;
					if (!this.domNodeDivTable) return false;
					if (!this.domNodeTable) return false;
					
					dojo.style(this.domNodeDivTable, 'padding-top', this.padding);
					dojo.style(this.domNodeDivTable, 'padding-bottom', this.padding);
					
					this.domNodeTitle.innerHTML='';
					if (this.title && this.isShowTitle) {
						objDiv=document.createElement('div');
						objDiv.className='g74-paneltitle';
						var objText=document.createTextNode(this.title);
						objDiv.appendChild(objText);
						this.domNodeTitle.appendChild(objDiv);
					}

					for(var i=0; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						obj.destroyRecursive();
						this._childs[i]=null;
					}
					this._childs=[];
					
					var lst=[];
					for(var i=0; i<this.domNodeTable.childNodes.length; i++) lst.push(this.domNodeTable.childNodes[i]);
					for(var i=0; i<lst.length; i++) this.domNodeTable.removeChild(lst[i]);
					var objTBody=document.createElement('tbody');
					this.domNodeTable.appendChild(objTBody);
					
					for(var i=0; i<this.fields.length; i++) {
						var fld=this.fields[i];
						if (!fld) continue;
						var objTr=document.createElement('tr');
						objTBody.appendChild(objTr);
						var objTdCaption=document.createElement('td');
						objTdCaption.className='g740-fieldssimple-td-caption';
						objTr.appendChild(objTdCaption);
						
						var caption='';
						if (fld.type!='check') {
							caption=fld.caption;
							if (!caption) caption=fld.name;
						}
						var objText=document.createTextNode(caption);
						objTdCaption.appendChild(objText);
						
						var objTdValue=document.createElement('td');
						objTdValue.className='g740-fieldssimple-td-value';
						objTr.appendChild(objTdValue);
						var objDiv=document.createElement('div');
						objTdValue.appendChild(objDiv);
						
						var p={
							objForm: this.objForm,
							objPanel: this,
							rowsetName: this.rowsetName,
							fieldName: fld.name,
							fieldDef: fld,
							nodeType: this.nodeType
                        };
                        var objEditor = g740.panels.createObjField(fld, p, objDiv);
                        this._childs.push(objEditor);
					}
				},
				doG740Repaint: function(para) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					for(var i=0; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (obj.doG740Repaint) obj.doG740Repaint();
					}
				},
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					return true;
				},
				
				canFocused: function() {
					var result=false;
					for(var i=0; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (!obj.getVisible()) continue;
						result=true;
						break;
					}
					return result;
				},
				doG740Focus: function() {
					this.inherited(arguments);
					this.doG740FocusChildFirst();
					
					for(var i=0; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (!obj.doG740Focus) continue;
						obj.doG740Focus();
						break;
					}
				},
				doG740FocusChildFirst: function() {
					var objChild=null;
					for(var i=0; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (!obj.getVisible()) continue;
						objChild=obj;
						break;
					}
					if (objChild) obj.set('focused',true);
				},
				doG740FocusChildLast: function() {
					var objChild=null;
					for(var i=this._childs.length-1; i>=0; i--) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (!obj.getVisible()) continue;
						objChild=obj;
						break;
					}
					if (objChild) obj.set('focused',true);
				},
				doG740FocusChildNext: function(objChild) {
					var index=this._childs.length;
					for(var i=0; i<this._childs.length; i++) {
						if (objChild==this._childs[i]) {
							index=i;
							break;
						}
					}
					var objChild=null;
					for(var i=index+1; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (!obj.getVisible()) continue;
						objChild=obj;
						break;
					}
					if (objChild) {
						obj.set('focused',true);
					}
					else {
						var objParent=this.getParent();
						if (objParent && objParent.doG740FocusChildNext) objParent.doG740FocusChildNext(this);
					}
				},
				doG740FocusChildPrev: function(objChild) {
					var index=-1;
					for(var i=0; i<this._childs.length; i++) {
						if (objChild==this._childs[i]) {
							index=i;
							break;
						}
					}
					var objChild=null;
					for(var i=index-1; i>=0; i--) {
						var obj=this._childs[i];
						if (!obj) continue;
						if (!obj.getVisible()) continue;
						objChild=obj;
						break;
					}
					if (objChild) {
						obj.set('focused',true);
					}
					else {
						var objParent=this.getParent();
						if (objParent && objParent.doG740FocusChildPrev) objParent.doG740FocusChildPrev(this);
					}
				}
			}
		);

		g740.panels._builderPanelFieldsSimple=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelFieldsSimple';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (!para.rowsetName) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'fields',
					messageId: 'errorRowSetNameEmpty'
				});
				return null;
			}
			var objRowSet=para.objForm.rowsets[para.rowsetName];
			if (!objRowSet) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'fields',
					rowsetName: para.rowsetName,
					messageId: 'errorRowSetNotFoundInForm'
				});
				return null;
			}
			
			var rowsetFields=objRowSet.getFieldsByNodeType(para.nodeType);
			var fields=[];
			var xmlFields=g740.xml.findFirstOfChild(xml,{nodeName:'fields'});
			if (!g740.xml.isXmlNode(xmlFields)) xmlFields=xml;
			var lst=g740.xml.findArrayOfChild(xmlFields,{nodeName:'field'});
			for(var i=0; i<lst.length; i++) {
				var xmlField=lst[i];
				var fld=g740.panels.buildFldDef(xmlField);
				if (!fld.name) continue;
				if (!fld.visible) continue;
				var fldRowSet=rowsetFields[fld.name];
				if (!fldRowSet) continue;
				if (fldRowSet.visible===false) continue;

				if (para.nodeType) fld.nodeType=para.nodeType;
				fields.push(fld);
			}
			para.fields=fields;
			var result=new g740.FieldsSimple(para, null);
			return result;
		};
		g740.panels.registrate('fieldssimple', g740.panels._builderPanelFieldsSimple);

		dojo.declare(
			'g740.Properties',
			[g740._PanelAbstract, dijit._TemplatedMixin],
			{
				isG740Fields: true,
				_childs: [],
				title: '',
				templateString: '<div class="g740-properties"><table class="g740-properties-table" data-dojo-attach-point="domNodeTable"></table></div>',
				constructor: function(para, domElement) {
					var procedureName='g740.Properties.constructor';
					this.fields={};
					this._childs=[];
					this.set('objForm',para.objForm);
					this.set('rowsetName',para.rowsetName);
					
					this.on('Focus', this.onG740Focus);
					//console.log(this);
				},
				destroy: function() {
					var procedureName='g740.Properties.destroy';
					if (this._childs) {
						for(var i=0; i<this._childs.length; i++) {
							var obj=this._childs[i];
							if (!obj) continue;
							obj.destroyRecursive();
							this._childs[i]=null;
						}
						this._childs=[];
					}
					this.inherited(arguments);
				},
				render: function() {
					if (!this.domNodeTable) return false;

					for(var i=0; i<this._childs.length; i++) {
						var obj=this._childs[i];
						if (!obj) continue;
						obj.destroyRecursive();
						this._childs[i]=null;
					}
					this._childs=[];
					
					var lst=[];
					for(var i=0; i<this.domNodeTable.childNodes.length; i++) lst.push(this.domNodeTable.childNodes[i]);
					for(var i=0; i<lst.length; i++) this.domNodeTable.removeChild(lst[i]);
					
					var objTBody=document.createElement('tbody');
					this.domNodeTable.appendChild(objTBody);

					if (this.title && this.isShowTitle) {
						var objTr=document.createElement('tr');
						objTBody.appendChild(objTr);
						var objTd=document.createElement('td');
						objTd.colSpan=3;
						objTd.className='g740-properties-td-title';
						objTr.appendChild(objTd);
						objDiv=document.createElement('div');
						objDiv.className='g74-paneltitle';
						objTd.appendChild(objDiv);
						var objText=document.createTextNode(this.title);
						objDiv.appendChild(objText);
					}
					
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					
					var nodes=objRowSet.objTreeStorage.getChildsOrdered(objRowSet.objTreeStorage.rootNode);
					for(var i=0; i<nodes.length; i++) {
						var node=nodes[i];
						if (!node) continue;
						var row=node.info;
						if (!row) continue;
						
						var objTr=document.createElement('tr');
						objTBody.appendChild(objTr);
						var objTdCaption=document.createElement('td');
						objTdCaption.className='g740-properties-td-caption';
						objTr.appendChild(objTdCaption);
						var caption=row['caption.value'];
						if (!caption) caption=row['name.value'];
						var objText=document.createTextNode(caption);
						objTdCaption.appendChild(objText);
						
						var objTdValue=document.createElement('td');
						objTdValue.className='g740-properties-td-value';
						objTr.appendChild(objTdValue);
						var objDiv=document.createElement('div');
						objTdValue.appendChild(objDiv);
						
						var fld={
							name: 'value',
							type: row['type.value'],
							stretch: 1
						}
						if (!fld.type) fld.type='string';
						var p={
							objForm: this.objForm,
							rowsetName: this.rowsetName,
							fieldName: 'value',
							rowId: node.id,
							fieldDef: fld,
							isSaveOnChange: true,
							value: row['value.value'],
							readOnly: objRowSet.getReadOnly() || row['row.readonly']==1
						};
						if (fld.type=='ref') {
							fld.name='rname';
							fld.type='string';
							fld.refid='rid';
							p.fieldName='rname';
							p.value=row['rname.value'];
                        } 
                        var objEditor = g740.panels.createObjField(fld, p, objDiv);
                        this._childs.push(objEditor);

						var objTdUnit=document.createElement('td');
						objTdUnit.className='g740-properties-td-unit';
						objTr.appendChild(objTdUnit);
						var objDiv=document.createElement('div');
						objTdUnit.appendChild(objDiv);
						
						if (row['units.value']) {
							var fld={
								name: 'unit',
								type: 'list',
								len: '20',
								list: row['units.value'],
								stretch: false
							}
							var p={
								objForm: this.objForm,
								rowsetName: this.rowsetName,
								fieldName: 'unit',
								rowId: node.id,
								fieldDef: fld,
								value: row['unit.value']
							};
							var objEditor=new g740.FieldEditor.List(p, objDiv);
							this._childs.push(objEditor);
						}
						
					}
				},
				focus: function() {
					if (this.domNode) this.domNode.focus();
				},
				doG740Repaint: function(para) {
					var objRowSet=this.getRowSet();
					if (!objRowSet) return false;
					if (!para) para={};
					if (para.objRowSet && para.objRowSet.name!=this.rowsetName) return true;
					if (para.isFull) {
						this.render();
						return true;
					}
					if (para.isRowUpdate) {
						var rowId=objRowSet.getFocusedId();
						for(var i=0; i<this._childs.length; i++) {
							var obj=this._childs[i];
							if (!obj) continue;
							if (obj.rowId==rowId) {
								if (obj.doG740Repaint) obj.doG740Repaint();
							}
						}
					}
				},
				onG740Focus: function() {
					if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
					return true;
				}
			}
		);

		g740.panels._builderPanelProperties=function(xml, para) {
			var result=null;
			var procedureName='g740.panels._builderPanelProperties';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName!='panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (!para.rowsetName) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'properties',
					messageId: 'errorRowSetNameEmpty'
				});
				return null;
			}
			var objRowSet=para.objForm.rowsets[para.rowsetName];
			if (!objRowSet) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'properties',
					rowsetName: para.rowsetName,
					messageId: 'errorRowSetNotFoundInForm'
				});
				return null;
			}
			
			// Проверяем в наборе строк наличие требуемых для корректной работы панели полей
			var fields=objRowSet.getFields('');
			var names=['name','caption','type','value','rid','rname'];
			var isErrorInFieldName=false;
			for (var i=0; i<names.length; i++) {
				var fieldName=names[i];
				if (!fields[fieldName]) {
					g740.trace.goBuilder({
						formName: para.objForm.name,
						panelType: 'properties',
						rowsetName: para.rowsetName,
						fieldName: fieldName,
						messageId: 'errorNotFoundFieldName'
					});
					isErrorInFieldName=true;
				}
			}
			if (isErrorInFieldName) return null;
			
			var result=new g740.Properties(para, null);
			return result;
		};
		g740.panels.registrate('properties', g740.panels._builderPanelProperties);
		
		return g740;
	}
);